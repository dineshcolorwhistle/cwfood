<?php

namespace App\Http\Controllers;

use App\Models\{Role,User,Client,Member,ClientSubscription,support_ticket,image_library,support_ticket_comment};
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

class SupportController extends Controller
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

        
        $last_ticket = support_ticket::orderBy('id','desc')->first();
        $ticket_count = ($last_ticket == null)? 1001: $last_ticket->ticket_number + 1;

        if(in_array($this->role_id,[4,7])){
            $tickets = support_ticket::with(['creator:id,name','RequesterDetails'])->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->where('created_by',$this->user_id)->latest()->get()->toArray();
        }else{
            $tickets = support_ticket::with(['creator:id,name','RequesterDetails'])->where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->latest()->get()->toArray();
        }
        
        foreach ($tickets as $key => $ticket) {
            $ids = json_decode($ticket['ccs'], true);
            if (!is_array($ids) || empty($ids)) {
               $tickets[$key]['cc_list'] = null;
            }else{
                $userNames = User::whereIn('id', $ids)->pluck('name')->toArray();
                $tickets[$key]['cc_list'] = implode(', ', $userNames);
            }
        }
        
        $members = Member::where('client_id',$this->clientID)->get();        
        $users = User::findOrfail($this->user_id);
        $batchbase_admins = User::where('role_id',1)->pluck('name','id')->toArray();        
        return view('backend.support.manage', compact('tickets','ticket_count','users','members','batchbase_admins'));
    }

       

    public function save(Request $request){
        
        try {
            $validationRules = support_ticket::validationRules();
            $validationMessages = support_ticket::validationMessages();
            $validator = Validator::make($request->all(), $validationRules, $validationMessages);
            if ($validator->fails()) {
                return response()->json(['success' => false,'errors' => $validator->errors()], 422);
            }
            $data = $validator->validated();
            $maxOrder = support_ticket::max('sort_order') ?? 0;
            $ccsArray = ($request->input('ccs'))? $request->input('ccs'):[];
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;
            $data['requester'] = $this->user_id;
            $data['client_id'] = $this->clientID;  
            $data['workspace_id'] = $this->ws_id;
            $data['ccs'] = (count($ccsArray))? json_encode($ccsArray):null;
            $data['sort_order'] = $maxOrder + 1;
            $support = support_ticket::create($data);
            $attachArray = [];
            if (isset($_FILES['image_file'])) {
                $filepath = "assets/{$this->clientID}/{$this->ws_id}/support_ticket/{$support->id}";
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
            $client = Client::findOrfail($this->clientID);
            $this->send_mail_to_admins($data,$attachArray,$users,$client,$supID); //Send mail to admin
            $this->send_mail_to_user($data,$attachArray,$users,$client,$supID); //Send mail to user
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

    public function send_mail_to_admins($data,$attachArray,$users,$client,$supID){
        // $nutriflow_admins = User::where('role_id',1)->pluck('name','email')->toArray();
        $nutriflow_admins = User::where('role_id',1)->pluck('email')->toArray();
        Mail::send('email.create-ticket-admin', ['details' => $data,'images'=>$attachArray,'user_name' => $users->name, 'client_name' => $client->name, 'ticket_id' => $supID], function($message) use($data,$attachArray,$supID,$client,$nutriflow_admins,$users){
            $message->to($nutriflow_admins);
            $message->subject("New Batchbase Support Ticket #{$data['ticket_number']} submitted by {$client->name}");
            if(sizeof($attachArray) > 0){
                foreach ($attachArray as $key => $value) {
                    $filePath = public_path("assets/{$this->clientID}/{$this->ws_id}/support_ticket/{$supID}/{$value['name']}");
                    if (file_exists($filePath)) {
                        $message->attach($filePath);
                    }
                }
            } 
        });
        return;
    }

    public function send_mail_to_user($data,$attachArray,$users,$client,$supID){
        $requester = !empty($data['requester']) ? User::find($data['requester']) : User::find($this->user_id);
        $ccsArray = !empty($data['ccs']) ? json_decode($data['ccs']) : [];
        $ccsArray[] = $this->user_id;
        $mainArray = array_unique($ccsArray);
        $ccs_details = User::whereIn('id', $mainArray)->pluck('email')->toArray();
        $ticketLink = route('view.ticket', [
                        'CID' => $this->clientID, 
                        'WSID' => $this->ws_id
                    ]);
        Mail::send('email.create-ticket-user', ['details' => $data,'images'=>$attachArray,'user_name' => $users->name,'ticket_id' => $supID,'url' => $ticketLink], function($message) use($data,$attachArray,$supID,$client,$requester,$ccs_details){
            if ($requester) {
                $message->to($requester->email);
            }
            if (!empty($ccs_details)) {
                $message->cc($ccs_details);
            }
            $message->subject("New Batchbase Support Ticket #{$data['ticket_number']} submitted by {$client->name}");
            if(sizeof($attachArray) > 0){
                foreach ($attachArray as $key => $value) {
                    $filePath = public_path("assets/{$this->clientID}/{$this->ws_id}/support_ticket/{$supID}/{$value['name']}");
                    if (file_exists($filePath)) {
                        $message->attach($filePath);
                    }
                }
            } 
        });
        return;
    }

    // public function view_old(support_ticket $ticket){
    //     $ticket->load([
    //         'creator',           // Loads the ticket creator (User)
    //         'comments.creator'   // Loads all comments, and each comment’s creator
    //     ]);
    //     $images = [];
    //     if($ticket->ticket_image > 0){
    //         $images = image_library::where('module_id',$ticket->id)->where('module','support_ticket')->get()->toArray();
    //     }
    //     $members = Member::where('client_id',$this->clientID)->get();
    //     $batchbase_admins = User::where('role_id',1)->get();
    //     return view('backend.support.view-ticket', compact('ticket','images','members','batchbase_admins'));
    // }

    public function view($cid,$ws_id){
        session()->put('client', $cid);
        session()->put('workspace', $ws_id);

        $last_ticket = support_ticket::orderBy('id','desc')->first();
        $ticket_count = ($last_ticket == null)? 1001: $last_ticket->ticket_number + 1;

        if(in_array($this->role_id,[4,7])){
            $tickets = support_ticket::with(['creator:id,name','RequesterDetails'])->where('client_id', $cid)->where('workspace_id', $ws_id)->where('created_by',$this->user_id)->latest()->get()->toArray();
        }else{
            $tickets = support_ticket::with(['creator:id,name','RequesterDetails'])->where('client_id', $cid)->where('workspace_id', $ws_id)->latest()->get()->toArray();
        }
        
        foreach ($tickets as $key => $ticket) {
            $ids = json_decode($ticket['ccs'], true);
            if (!is_array($ids) || empty($ids)) {
               $tickets[$key]['cc_list'] = null;
            }else{
                $userNames = User::whereIn('id', $ids)->pluck('name')->toArray();
                $tickets[$key]['cc_list'] = implode(', ', $userNames);
            }
        }
        // $tickets = support_ticket::where('client_id',$this->clientID)->where('workspace_id',$this->ws_id)->get()->toArray();
        $members = Member::where('client_id',$this->clientID)->get();        
        $users = User::findOrfail($this->user_id);
        $batchbase_admins = User::where('role_id',1)->pluck('name','id')->toArray();        
        return view('backend.support.manage', compact('tickets','ticket_count','users','members','batchbase_admins'));
    }

    public static function edit(support_ticket $ticket, Request $request)
    {
       try {
            $ticket->load([
                'creator',           // Loads the ticket creator (User)
                'comments.creator',   // Loads all comments, and each comment’s creator
            ]);

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
            return response()->json(['success' => true,'details' => $response]);
       } catch (\Exception $e) {
            return response()->json(['success' => false,'message' => $e->getMessage()]);
       }
    }

    public function update(support_ticket $ticket, Request $request){  
        // dd($request->all());
        try {   
            $ccsArray = ($request->input('ccs'))? $request->input('ccs'):[];
            $ticket->update([
                'status' => $request->input('status'),
                'priority' => $request->input('priority'),
                'due_date' => $request->input('due_date'),
                'category' => $request->input('category'),
                'assignee' => $request->input('assignee'),
                'requester' => $request->input('requester'),
                'ccs' => (count($ccsArray))? json_encode($ccsArray):null,
                'description' => $request->input('description'),
                'topic' => $request->input('topic'),
                'time_estimated'=> $request->input('time_estimated'),
                'time_spent'=> $request->input('time_spent')
            ]);
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
                $dirPath = "assets/{$this->clientID}/{$this->ws_id}/support_ticket/{$ticket->id}";
                $response = all_image_remove($dirPath);
                if ($response == "success") {
                    $ticket->delete();
                    $ticket->comments()->delete();
                    image_library::where('module', 'support_ticket')->where('module_id', $ticket->id)->delete();
                    image_library::where('module', 'support_comment')->where('SKU', $ticket->ticket_number)->delete();
                }else{
                    return response()->json(['success' => false,'errors' => $response], 422);
                }
            }else{
                $ticket->delete();
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
        $requester = !empty($data['requester']) ? User::find($data['requester']) : null;
        $ccsArray = !empty($data['ccs']) ? json_decode($data['ccs']) : [];
        $ccsArray[] = $data['created_by'];
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
                $filepath = "assets/{$this->clientID}/{$this->ws_id}/support_ticket/{$ticket->id}/comments/{$support->id}";
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
            $client = Client::findOrfail($this->clientID);
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
        $requester = !empty($data['requester']) ? User::find($data['requester']) : User::find($data['created_by']);
        $ccsArray = !empty($data['ccs']) ? json_decode($data['ccs']) : [];
        $ccsArray[] = $data['created_by'];
        $mainArray = array_unique($ccsArray);
        $ccs_details = User::whereIn('id', $mainArray)->pluck('email')->toArray();
        $users = User::findOrfail($data['created_by']);
        $ticketLink = route('view.ticket', [
                        'CID' => $this->clientID, 
                        'WSID' => $this->ws_id
                    ]);
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
                    $filePath = public_path("assets/{$this->clientID}/{$this->ws_id}/support_ticket/{$ticket->id}/comments/{$commentID}/{$value['name']}");
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
            if (isset($_FILES['image_file'])) {
                $ticket = support_ticket::findOrfail($data['ticket_id']);
                $filepath = "assets/{$this->clientID}/{$this->ws_id}/support_ticket/{$ticket->id}/comments/{$comment->id}";
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
            $this->send_comment_mail_to_user($data,$attachArray,$users,$client,$ticket,$commentID); //Send mail to user
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
                $dirPath = "assets/{$this->clientID}/{$this->ws_id}/support_ticket/{$comment->ticket_id}/comments/{$comment->id}";
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
            $status = $request->input('status');
            $ticket->update(['status' => $status]);
            // $this->send_status_mail_to_admins($ticket,$status); //Send mail to admin
            $this->send_status_mail_to_user($ticket,$status); //Send mail to user
            return response()->json(['success' => true,'message' => "Ticket status updated."]);
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
        $requester = !empty($data['requester']) ? User::find($data['requester']) : User::find($data['created_by']);
        $ccsArray = !empty($data['ccs']) ? json_decode($data['ccs']) : [];
        $ccsArray[] = $data['created_by'];
        $mainArray = array_unique($ccsArray);
        $ccs_details = User::whereIn('id', $mainArray)->pluck('email')->toArray();
        $users = User::findOrfail($data['created_by']);
        Mail::send('email.status-update-user', ['details' =>$data, 'status' => $status, 'user_name' => $users->name], function($message) use($data,$requester,$ccs_details){
            if ($requester) {
                $message->to($requester->email);
            }
            if (!empty($ccs_details)) {
                $message->cc($ccs_details);
            }
            $message->subject("Support ticket status updated | Ticket #{$data['ticket_number']}");
        });
    }










}