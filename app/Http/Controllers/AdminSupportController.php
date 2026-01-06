<?php

namespace App\Http\Controllers;

use App\Models\{Role,User,Client,Member,ClientSubscription,support_ticket,image_library,support_ticket_comment,Workspace};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Exports\TicketsWithCommentsExport;
use App\Exports\TicketsMultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminSupportController extends Controller
{
    private $user_id;
    private $role_id;
    private $clientID;
    private $ws_id;

    public function __construct()
    {
        $this->user_id = session('user_id');
        $this->role_id = session('role_id');
        $this->clientID = session('client');
        $this->ws_id = session('workspace');
    } 

    public function index(){
        $clients = Client::pluck('name','id')->toArray();
        $batchbase_admins = User::where('role_id',1)->pluck('name','id')->toArray();        
        // $tickets = support_ticket::withCount('comments')->with(['creator:id,name','RequesterDetails','ClientDetails','WorkspaceDetails','AssigneeDetails'])->latest('updated_at')->get()->toArray();
        $tickets = support_ticket::withCount('comments')
                ->with(['creator:id,name','RequesterDetails','ClientDetails','WorkspaceDetails','AssigneeDetails'])
                ->orderBy('sort_order', 'asc')     // primary order by drag-drop
                ->orderBy('updated_at', 'desc')    // fallback
                ->get()
                ->toArray();

        $uniqueAssignees = support_ticket::with('AssigneeDetails:id,name')->get()->pluck('AssigneeDetails')
        ->filter()->unique('id')->toArray();


        $last_ticket = support_ticket::orderBy('id','desc')->first();
        $ticket_count = ($last_ticket == null)? 1001: $last_ticket->ticket_number + 1;
        // Count open and closed tickets
        $openCount = collect($tickets)->where('status', '!=', 'Resolved')->count();
        $closeCount = collect($tickets)->where('status', 'Resolved')->count();
        return view('backend.admin-support.manage', compact('tickets','clients','batchbase_admins','ticket_count','openCount','closeCount','uniqueAssignees'));
    }

    public function client_details(Request $request){
        try {
            $client = $request->client;
            $workspaces = Workspace::where('client_id',$client)->get()->toArray();
            $members = Member::where('client_id',$client)->get();
            return response()->json(['success' => true,'workspaces' =>$workspaces,'members' =>$members]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false,'errors' => $e->getMessage()], 422);
        }
    }

    public function view(support_ticket $ticket){
        $ticket->load([
            'creator',           // Loads the ticket creator (User)
            'comments.creator',   // Loads all comments, and each comment’s creator
            'RequesterDetails',
            'AssigneeDetails'

        ]);
        $images = [];
        if($ticket->ticket_image > 0){
            $images = image_library::where('module_id',$ticket->id)->where('module','support_ticket')->get()->toArray();
        }

        $ccsName = null;
        if (!empty($ticket->ccs)) {
            $ccIds = json_decode($ticket->ccs, true);

            if (is_array($ccIds) && count($ccIds) > 0) {

                $ccsArray = User::where('role_id', 1)
                    ->whereIn('id', $ccIds)
                    ->pluck('name')
                    ->toArray();

                $ccsName = !empty($ccsArray) ? implode(', ', $ccsArray) : null;
                
            }
        }

        return view('backend.admin-support.view-ticket', compact('ticket','images','ccsName'));
    }

    public function update(support_ticket $ticket, Request $request){  
        try {
            $status = $request->input('status');
            if($status == "Resolved"){
                // $this->send_status_mail_to_admins($ticket,$status); //Send mail to admin
                $this->send_status_mail_to_user($ticket,$status); //Send mail to user
            }
            $ccsArray = ($request->input('ccs'))? $request->input('ccs'):[];
            $ticket->update([
                'status' => $request->input('status'),
                'priority' => $request->input('priority'),
                'due_date' => $request->input('due_date'),
                'category' => $request->input('category'),
                'assignee' => $request->input('assignee'),
                'requester' => $request->input('requester'), 
                'ccs' => (count($ccsArray)>0)? json_encode($ccsArray):null,
                'description' => $request->input('description',$ticket->description),
                'topic' => $request->input('topic', $ticket->topic),
                'time_estimated'=> $request->input('time_estimated'),
                'time_spent'=> $request->input('time_spent')
            ]);

            if($request->input('add_comment')){
                $data['description'] = $request->input('comment_description');
                $data['ticket_id'] = $ticket->id;
                $data['created_by'] = $this->user_id;
                $data['updated_by'] = $this->user_id;
                $support = support_ticket_comment::create($data);
                $attachArray = [];
                if (isset($_FILES['comment_file'])) {
                    $filepath = "assets/{$ticket->client_id}/{$ticket->workspace_id}/support_ticket/{$ticket->id}/comments/{$support->id}";
                    $image_response = upload_multiple_files($_FILES['comment_file'], $filepath);
                    if ($image_response['status'] == true) {
                        $imageArray = $image_response['final_array'];
                        self::insert_images($ticket->ticket_number,$filepath, $imageArray, $support->id,'support_comment');
                        support_ticket_comment::where('id', $support->id)->update(['comment_image' => sizeof($imageArray)]);
                        $attachArray = $imageArray;
                    }
                }
                $users = User::findOrfail($this->user_id);
                $client = Client::findOrfail($ticket->client_id);
                $this->send_comment_mail_to_user($data,$attachArray,$users,$client,$ticket,$support->id); //Send mail to user
            }
            return response()->json(['success' => true,'message' => "Ticket Updated"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'errors' => $e->getMessage()], 422);
        }
    }

    public function destroy(support_ticket $ticket){
        try {
            // $this->sent_delete_notification_to_admins($ticket);
            $this->sent_delete_notification_to_users($ticket);
            if($ticket->ticket_image > 0){
                $dirPath = "assets/{$ticket->client_id}/{$ticket->workspace_id}/support_ticket/{$ticket->id}";
                $response = all_image_remove($dirPath);
                if ($response == "success") {
                    $ticket->comments()->delete();
                    $ticket->delete();
                    image_library::where('module', 'support_ticket')->where('module_id', $ticket->id)->delete();
                    image_library::where('module', 'support_comment')->where('SKU', $ticket->ticket_number)->delete();
                }else{
                    return response()->json(['success' => false,'errors' => $response], 422);
                }
            }else{
                $ticket->delete();
                $ticket->comments()->delete();
            }
            return response()->json(['success' => true,'message' => "Ticket deleted"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'errors' => $e->getMessage()], 422);
        }
    }

    public function sent_delete_notification_to_admins($ticket){
        $data = $ticket->toArray();
        $nutriflow_admins = User::where('role_id',1)->pluck('email')->toArray();
        Mail::send('email.delete-ticket-admin', ['details' => $data], function($message) use($data,$nutriflow_admins){
            $message->to($nutriflow_admins);
            $message->subject("Support ticket deleted | Ticket #{$data['ticket_number']}");
        });
        return;
    }

    public function sent_delete_notification_to_users($ticket){
        $data = $ticket->toArray();
        if(empty($data['requester'])){
            return;
        }
        $requester = User::find($data['requester']);
        $ccsArray = !empty($data['ccs']) ? json_decode($data['ccs']) : [];
        if (!empty($data['assignee'])) {
            $ccsArray[] = $data['assignee'];
        }
        $mainArray = array_unique($ccsArray);
        $ccs_details = User::whereIn('id', $mainArray)->pluck('email')->toArray();
        $users = User::findOrfail($data['created_by']);
        Mail::send('email.delete-ticket-user', ['details' => $data,'user_name' => $users->name], function($message) use($data,$requester,$ccs_details){
            if ($requester) {
                $message->to($requester->email);
            }
            if (!empty($ccs_details)) {
                $message->cc($ccs_details);
            }
            $message->subject("Support ticket deleted | Ticket #{$data['ticket_number']}");
        });
        return;
    }

    public function save_comment(support_ticket $ticket, Request $request){
        try {
            $validationRules = support_ticket_comment::validationRules();
            $validationMessages = support_ticket_comment::validationMessages();
            $validator = Validator::make($request->all(), $validationRules, $validationMessages);
            if ($validator->fails()) {
                return response()->json(['success' => false,'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();

            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $support = support_ticket_comment::create($data);
            $attachArray = [];
            if (isset($_FILES['image_file'])) {
                $filepath = "assets/{$ticket->client_id}/{$ticket->workspace_id}/support_ticket/{$ticket->id}/comments/{$support->id}";
                $image_response = upload_multiple_files($_FILES['image_file'], $filepath);
                if ($image_response['status'] == true) {
                    $imageArray = $image_response['final_array'];
                    self::insert_images($ticket->ticket_number,$filepath, $imageArray, $support->id,'support_comment');
                    support_ticket_comment::where('id', $support->id)->update(['comment_image' => sizeof($imageArray)]);
                    $attachArray = $imageArray;
                }
            }
            $commentID = $support->id;
            $users = User::findOrfail($this->user_id);
            $client = Client::findOrfail($ticket->client_id);
            // $this->send_comment_mail_to_admins($data,$attachArray,$users,$client,$ticket,$commentID); //Send mail to admin
            $this->send_comment_mail_to_user($data,$attachArray,$users,$client,$ticket,$commentID); //Send mail to user
            $result['success'] = true;
            $result['message'] = "A ticket comment has been sent to the Nutriflow admin. You will also receive a confirmation email.";
        } catch (\Exception $e) {
           $result['success'] = false;
           $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }
    
    public function send_comment_mail_to_admins($comments,$attachArray,$users,$client,$ticket,$commentID){
        $data = $ticket->toArray();
        $nutriflow_admins = User::where('role_id',1)->pluck('email')->toArray();
        Mail::send('email.support-comment-ticket-admin', ['details' => $data,'comment' => $comments['description'],'images'=>$attachArray,'user_name' => $users->name, 'client_name' => $client->name], function($message) use($data,$attachArray,$commentID,$nutriflow_admins,$ticket){
            $message->to($nutriflow_admins);
            $message->subject("New comment | Ticket #{$ticket->ticket_number}");
            if(sizeof($attachArray) > 0){
                foreach ($attachArray as $key => $value) {
                    $filePath = public_path("assets/{$this->clientID}/{$this->ws_id}/support_ticket/{$ticket->id}/comments/{$commentID}/{$value['name']}");
                    if (file_exists($filePath)) {
                        $message->attach($filePath);
                    }
                }
            } 
        });
        return;
    }

    public function send_comment_mail_to_user($comments,$attachArray,$users,$client,$ticket,$commentID){
        $data = $ticket->toArray();
        if(empty($data['requester'])){
            return;
        }
        $requester = User::find($data['requester']);
        $ccsArray = !empty($data['ccs']) ? json_decode($data['ccs']) : [];
        if (!empty($data['assignee'])) {
            $ccsArray[] = $data['assignee'];
        }
        $mainArray = array_unique($ccsArray);
        $ccs_details = User::whereIn('id', $mainArray)->pluck('email')->toArray();
        $users = User::findOrfail($data['created_by']);
        $ticketLink = route('admin.view.ticket',['ticket' => $ticket->id]);
        Mail::send('email.support-comment-ticket-user', ['details' => $data,'comment' => $comments['description'],'user_name' => $users->name,'url' => $ticketLink], function($message) use($data,$attachArray,$requester,$ccs_details,$commentID,$ticket){
            if ($requester) {
                $message->to($requester->email);
            }
            if (!empty($ccs_details)) {
                $message->cc($ccs_details);
            }
            $message->subject("New comment | Ticket #{$ticket->ticket_number}");
            if(sizeof($attachArray) > 0){
                foreach ($attachArray as $key => $value) {
                    $filePath = public_path("assets/{$ticket->client_id}/{$ticket->workspace_id}/support_ticket/{$ticket->id}/comments/{$commentID}/{$value['name']}");
                    if (file_exists($filePath)) {
                        $message->attach($filePath);
                    }
                }
            } 
        });
        return;
    }

    public function update_comment(support_ticket_comment $comment, Request $request){
        try {
            $validationRules = support_ticket_comment::validationRules();
            $validationMessages = support_ticket_comment::validationMessages();
            $validator = Validator::make($request->all(), $validationRules, $validationMessages);
            if ($validator->fails()) {
                return response()->json(['success' => false,'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            $comment->update($data);
            $attachArray = [];
            $ticket = support_ticket::findOrfail($data['ticket_id']);
            if (isset($_FILES['image_file'])) {
                $filepath = "assets/{$ticket->client_id}/{$ticket->workspace_id}/support_ticket/{$ticket->id}/comments/{$comment->id}";
                $image_response = upload_multiple_files($_FILES['image_file'], $filepath);
                if ($image_response['status'] == true) {
                    $imageArray = $image_response['final_array'];
                    self::insert_images($ticket->ticket_number,$filepath, $imageArray, $comment->id,'support_comment');
                    $count = $comment->comment_image + sizeof($imageArray);
                    support_ticket_comment::where('id', $comment->id)->update(['comment_image' => $count]);
                    $attachArray = $imageArray;
                }
            }
            $commentID = $comment->id;
            $users = User::findOrfail($this->user_id);
            $client = Client::findOrfail($this->clientID);
            // $this->send_comment_mail_to_admins($data,$attachArray,$users,$client,$ticket,$commentID); //Send mail to admin
            // $this->send_comment_mail_to_user($data,$attachArray,$users,$client,$ticket,$commentID); //Send mail to user
            $result['success'] = true;
            $result['message'] = "A ticket comment has been updated.";
        } catch (\Exception $e) {
           $result['success'] = false;
           $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function comment_destroy(support_ticket_comment $comment){
        try {
            if($comment->comment_image > 0){
                $ticket = support_ticket::where('id',$comment->ticket_id)->select('client_id','workspace_id')->first();
                $dirPath = "assets/{$ticket->client_id}/{$ticket->workspace_id}/support_ticket/{$comment->ticket_id}/comments/{$comment->id}";
                $response = all_image_remove($dirPath);
                if ($response == "success") {
                    $comment->delete();
                    image_library::where('module', 'support_comment')->where('module_id', $comment->id)->delete();
                }else{
                    return response()->json(['success' => false,'errors' => $response], 422);
                }
            }else{
                $comment->delete();
            }
            return response()->json(['success' => true,'message' => "Comments deleted"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'errors' => $e->getMessage()], 422);
        }
    }

    public function update_status(support_ticket $ticket, Request $request){
        try {
            /**
             * ticket status check
             */
            $status = $request->input('status');
            $update_data['status'] = $status;
            if (in_array($request->input('status'), ["Resolved", "Waiting for customer"]) ) {
                $update_data['time_spent'] = $request->input('time');
            }
            $ticket->update($update_data);
            if (in_array($request->input('status'), ["Resolved", "Waiting for customer"]) ) {
                // $this->send_status_mail_to_admins($ticket,$status); //Send mail to admin
                $this->send_status_mail_to_user($ticket,$status); //Send mail to user
            }

            if (in_array($request->input('status'), ["Waiting for customer"]) && $request->input('desc') != null) {
                $data['description'] = $request->input('desc');
                $data['ticket_id'] = $ticket->id;
                $data['created_by'] = $this->user_id;
                $data['updated_by'] = $this->user_id;
                $support = support_ticket_comment::create($data);
                $attachArray = [];
                $users = User::findOrfail($this->user_id);
                $client = Client::findOrfail($ticket->client_id);
                $this->send_comment_mail_to_user($data,$attachArray,$users,$client,$ticket,$support->id); //Send mail to user
            }

            return response()->json(['success' => true,'message' => "Ticket status updated."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'errors' => $e->getMessage()], 422);
        }
    }

    public function update_priority(support_ticket $ticket, Request $request){
        try {
            $priority = $request->input('priority');
            $ticket->update(['priority' => $priority]);
            return response()->json(['success' => true,'message' => "Ticket priority updated."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'errors' => $e->getMessage()], 422);
        }
    }

    public function send_status_mail_to_admins($ticket,$status){
        $data = $ticket->toArray();
        $nutriflow_admins = User::where('role_id',1)->pluck('email')->toArray();
        Mail::send('email.status-update-admin', ['details' => $data,'status' => $status], function($message) use($data,$nutriflow_admins){
            $message->to($nutriflow_admins);
             $message->subject("Support ticket status updated | Ticket #{$data['ticket_number']}");
            
        });
        return;
    }

    public function send_status_mail_to_user($ticket,$status){
        $data = $ticket->toArray();
        if(empty($data['requester'])){
            return;
        }
        $requester = User::find($data['requester']);
        $ccsArray = !empty($data['ccs']) ? json_decode($data['ccs']) : [];
        if (!empty($data['assignee'])) {
            $ccsArray[] = $data['assignee'];
        }
        $mainArray = array_unique($ccsArray);
        $ccs_details = User::whereIn('id', $mainArray)->pluck('email')->toArray();
        $users = User::findOrfail($data['created_by']);
        $ticketLink = route('admin.view.ticket',['ticket' => $ticket->id]);
        Mail::send('email.status-update-user', ['details' =>$data, 'status' => $status, 'user_name' => $users->name,'url' => $ticketLink], function($message) use($data,$requester,$ccs_details){
            if ($requester) {
                $message->to($requester->email);
            }
            if (!empty($ccs_details)) {
                $message->cc($ccs_details);
            }
            $message->subject("Support ticket status updated | Ticket #{$data['ticket_number']}");
        });
    }

    public function save(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'client_id' => 'required',
                'workspace_id' => 'required',
                'topic' => 'required|string|max:100',
                'category' => 'required|string|max:100',
                'description' => 'required|string|max:3000',
                'ticket_number' => 'required',
                'status' => 'nullable',
                'priority' => 'nullable',
                'due_date' => 'nullable',
                'requester' => 'required',
                'ccs' => 'nullable',
                'assignee' => 'nullable',
                'time_estimated' => 'nullable',
                'time_spent' => 'nullable'
            ], [
                // Custom error messages
                'client_id.required' => 'Client name is required.',
                'workspace_id.required' => 'Workspace is required.',
                'topic.required' => 'Topic is required.',
                'requester.required' => 'Requester is required.',
                'topic.max' => 'Topic may not be greater than 100 characters.',
                'category.required' => 'Category is required.',
                'category.max' => 'Category may not be greater than 100 characters.',
                'description.required' => 'Description is required.',
                'description.max' => 'Description may not be greater than 100 characters.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $data = $validator->validated();
            $clientID = $request->client_id;
            $ws_id = $request->workspace_id;
            $maxOrder = support_ticket::max('sort_order') ?? 0;
            $ccsArray = ($request->input('ccs'))? $request->input('ccs'):[];
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['client_id'] = $clientID;
            $data['workspace_id'] = $ws_id;
            $data['ccs'] = (count($ccsArray)>0)? json_encode($ccsArray):null;
            $data['sort_order'] = $maxOrder + 1;
            $support = support_ticket::create($data);
            $attachArray = [];
            if (isset($_FILES['image_file'])) {
                $filepath = "assets/{$clientID}/{$ws_id}/support_ticket/{$support->id}";
                $image_response = upload_multiple_files($_FILES['image_file'], $filepath);
                if ($image_response['status'] == true) {
                    $imageArray = $image_response['final_array'];
                    self::insert_images($data['ticket_number'],$filepath, $imageArray, $support->id,'support_ticket');
                    support_ticket::where('id', $support->id)->update(['ticket_image' => sizeof($imageArray)]);
                    $attachArray = $imageArray;
                }
            }
            $supID = $support->id;
            $users = User::findOrfail($this->user_id);
            $client = Client::findOrfail($clientID);
            // $this->send_mail_to_admins($data,$attachArray,$users,$client,$supID,$clientID,$ws_id); //Send mail to admin
            $this->send_mail_to_user($data,$attachArray,$users,$client,$supID,$clientID,$ws_id); //Send mail to user
            $result['success'] = true;
            $result['message'] = "A ticket has been sent to the Batchbase admin. You will also receive a confirmation email.";
        } catch (\Exception $e) {
           $result['success'] = false;
           $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function insert_images($sku,$filepath, $imageArray, $supportID,$module)
    {
        foreach ($imageArray as $key => $value) {
            $item = new image_library;
            $item->SKU = $sku;
            $item->module = $module;
            $item->module_id = $supportID;
            $item->image_number = ++$key;
            $item->image_name =  $value['name'];
            $item->default_image = false;
            $item->file_format = $value['type'];
            $item->file_size = $value['size'];
            $item->uploaded_by = $this->user_id;
            $item->last_modified_by = $this->user_id;
            $item->folder_path = $filepath;
            $item->save();
        }
        return;
    }

    public function send_mail_to_admins($data,$attachArray,$users,$client,$supID,$clientID,$ws_id){
        $nutriflow_admins = User::where('role_id',1)->pluck('email')->toArray();
        Mail::send('email.create-ticket-admin', ['details' => $data,'images'=>$attachArray,'user_name' => $users->name, 'client_name' => $client->name, 'ticket_id' => $supID], function($message) use($data,$attachArray,$supID,$client,$nutriflow_admins,$users,$clientID,$ws_id){
            $message->to($nutriflow_admins);
            $message->subject("New Batchbase Support Ticket #{$data['ticket_number']} submitted by {$client->name}");
            if(sizeof($attachArray) > 0){
                foreach ($attachArray as $key => $value) {
                    $filePath = public_path("assets/{$clientID}/{$ws_id}/support_ticket/{$supID}/{$value['name']}");
                    if (file_exists($filePath)) {
                        $message->attach($filePath);
                    }
                }
            } 
        });
        return;
    }

    public function send_mail_to_user($data,$attachArray,$users,$client,$supID,$clientID,$ws_id){
        if(empty($data['requester'])){
            return;
        }
        $requester = User::find($data['requester']);
        $ccsArray = !empty($data['ccs']) ? json_decode($data['ccs']) : [];
        if (!empty($data['assignee'])) {
            $ccsArray[] = $data['assignee'];
        }
        $mainArray = array_unique($ccsArray);
        $ccs_details = User::whereIn('id', $mainArray)->pluck('email')->toArray();
        $ticketLink = route('admin.view.ticket',['ticket' => $supID]);
        Mail::send('email.create-ticket-user', ['details' => $data,'images'=>$attachArray,'user_name' => $users->name,'ticket_id' => $supID,'url' => $ticketLink], function($message) use($data,$attachArray,$supID,$client,$requester,$ccs_details,$clientID,$ws_id){
            if ($requester) {
                $message->to($requester->email);
            }
            if (!empty($ccs_details)) {
                $message->cc($ccs_details);
            }
            $message->subject("New Batchbase Support Ticket #{$data['ticket_number']} submitted by {$client->name}");
            if(sizeof($attachArray) > 0){
                foreach ($attachArray as $key => $value) {
                    $filePath = public_path("assets/{$clientID}/{$ws_id}/support_ticket/{$supID}/{$value['name']}");
                    if (file_exists($filePath)) {
                        $message->attach($filePath);
                    }
                }
            } 
        });
        return;
    }

    public static function reorder(Request $request)
    {
        foreach ($request->order as $item) {
            support_ticket::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        // Get tickets again in new order

         $tickets = support_ticket::withCount('comments')
                    ->with(['creator:id,name','RequesterDetails','ClientDetails','WorkspaceDetails','AssigneeDetails'])
                    ->orderBy('sort_order', 'asc')     // primary order by drag-drop
                    ->get()
                    ->toArray();
                    
        // Render only table rows
        $html = view('backend.admin-support.table', compact('tickets'))->render();
    
        return response()->json(['status' => 'success','html' => $html]);
    }

    public static function edit(support_ticket $ticket, Request $request)
    {
       try {
            $ticket->load([
                'creator',           // Loads the ticket creator (User)
                'comments.creator',   // Loads all comments, and each comment’s creator
            ]);
            $ticket->created_at_formatted =  Carbon::parse($ticket->created_at)->format('j M Y'); //Edit date format
            
            // Load all comment images in one query
            $commentImages = image_library::where('module', 'support_comment')
                ->whereIn('module_id', $ticket->comments->pluck('id'))
                ->get()
                ->groupBy('module_id');
  
            // Attach images manually
            $ticket->comments->each(function ($comment) use ($commentImages) {
                $comment->setRelation('images', $commentImages->get($comment->id, collect()));
            });

            // Ticket images
            $ticketImages = image_library::where('module', 'support_ticket')
                ->where('module_id', $ticket->id)
                ->get();

            $ticket->setRelation('images', $ticketImages);

            $response['ticket'] = $ticket;
            $response['members'] = Member::where('client_id',$ticket->client_id)->get();
            $response['teammates'] = User::where('role_id',1)->get();
            return response()->json(['success' => true,'details' => $response]);
       } catch (\Exception $e) {
            return response()->json(['success' => false,'message' => $e->getMessage()]);
       }
    }


    public function PendingTicketUpdate(){
        try {
                $batchbase_admins = User::where('role_id',1)->pluck('id')->toArray();
                $mailSend_IDs = [];
                $updateTicketIDs = [];
                support_ticket::whereNotIn('created_by', $batchbase_admins)
                                ->where('status', '!=', 'Resolved')
                                ->chunk(100, function ($tickets) use (&$mailSend_IDs, &$updateTicketIDs) {
                                    foreach ($tickets as $ticket) {
                                        try {
                                            $createdDate = Carbon::parse($ticket->created_at); 
                                            $daysDiff = (int) floor($createdDate->diffInRealDays(Carbon::now()));
                                            if ($daysDiff === 13) {
                                                $mailSend_IDs[] = $ticket->id;
                                            } elseif ($daysDiff > 13) {
                                                $updateTicketIDs[] = $ticket->id;
                                            }
                                        } catch (\Throwable $th) {
                                            continue;
                                        }
                                    }
                                });

                if(!empty($updateTicketIDs)){
                    support_ticket::whereIn('id', $updateTicketIDs)->update([
                        'status' => 'Resolved',
                        'time_spent' => '01:00'
                    ]);
                }

                if(!empty($mailSend_IDs)){
                    foreach ($mailSend_IDs as $key => $ticketID) {
                        $ticket = ticket::findOrfail($ticketID);
                        if($ticket){
                            $this->send_pending_ticket_update_mail_to_user($ticket);
                        }
                    }
                }
                return response()->json(['status' => 'success', 'message' => 'Success'], 200);
        } catch (\Exception $e) {
           Log::error('Support Ticket global failure: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function send_pending_ticket_update_mail_to_user($ticket){
        $data = $ticket->toArray();
        if(empty($data['requester'])){
            return;
        }

        $requester = User::find($data['requester']);
        if (!$requester) {
            return;
        }

        $ccs_details = User::whereIn('id', $mainArray)->pluck('email')->toArray();
        Mail::send('email.pending-user-ticket-update', ['details' =>$data, 'user_name' => $requester->name], function($message) use($data,$requester){
            if ($requester) {
                $message->to($requester->email);
            }
            $message->subject("Reminder: Your Batchbase Ticket Will Close Soon | Ticket #{$data['ticket_number']}");
        });
    }



    public function exportExcelTicketsWithComments()
    {
        return Excel::download(new TicketsMultiSheetExport(), 'tickets_with_comments.xlsx');
    }

    public function exportCSVTicketsWithComments()
    {
        // CSV doesn't support multiple sheets, use single sheet export
        return Excel::download(new TicketsWithCommentsExport(), 'tickets_with_comments.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
