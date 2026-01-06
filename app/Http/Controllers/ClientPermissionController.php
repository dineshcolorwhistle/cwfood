<?php
namespace App\Http\Controllers;
use App\Models\{Role,Client_role,Member,Members_permission_group,Page,Products_permission_group};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientPermissionController extends Controller
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
    public function index(Request $request)
    {
        
        $cid = $this->clientID;
        $ws_id = $this->ws_id;
        // $client_roles = Client_role::where('client_id',$cid)->where('workspace_id',$ws_id)->get()->toArray();
        // $na_roles = Role::where('scope', 'workspace')->get()->toArray();
        // $roles = array_merge($na_roles,$client_roles);
        $roles = Client_role::where('client_id',$cid)->where('workspace_id',$ws_id)->get()->toArray();
        $users = Member::where('client_id',$cid)->get();
        $users = $users->map(function ($item) {
            if ($item->assign_workspace !== null) {
                $assignArray = json_decode($item->assign_workspace);
                $item->workspace_names = get_assign_workspace_names($assignArray);
            }else{
                $item->workspace_names = '';
            }
            $temp_group = Members_permission_group::where('member_id',$item->id)->select('client_permission_group','nutriflow_permission_group')->first();
            if($temp_group && $temp_group->client_permission_group != null){
                $item->client_permissionArray = json_decode($temp_group->client_permission_group);
            }else{
                $item->client_permissionArray = [];
            }
            if($temp_group && $temp_group->nutriflow_permission_group != null){
                $item->nutriflow_permissionArray = json_decode($temp_group->nutriflow_permission_group);
            }else{
                $item->nutriflow_permissionArray = [];
            }
            return $item;
        });

        $pages = Page::where('id','>', 3)->get();
        $pages = $pages->map(function ($item) use($cid) {
            $temp_group = Products_permission_group::where('client_id',$cid)->where('product_id',$item->id)->select('product_permission_group')->first();
            if($temp_group && $temp_group->product_permission_group != null){
                $item->client_productArray = json_decode($temp_group->product_permission_group);
            }else{
                $item->client_productArray = [];
            }
            return $item;
        });
        return view('backend.permission.manage', compact('roles','users','pages'));
    }

    public function user_permission(Request $request)
    {
        try {
            $members = array_unique(json_decode($request->input('members')));
            if($request->input('client') != "[]"){
                $client = json_decode($request->input('client'));
                $client_arrays = array_map(fn($obj) => (array) $obj, $client);
                $clientFinal = $this->get_assign_list($client_arrays);
                $clientKeys = array_keys($clientFinal);
                foreach ($clientFinal as $key => $value) {
                    $temp = Members_permission_group::where('member_id',$key)->first();
                    if($temp){
                        Members_permission_group::where('id',$temp->id)->update(['client_permission_group' =>json_encode($value)]);
                    }else{
                        $item = new Members_permission_group;
                        $item->member_id = $key;
                        $item->client_permission_group = json_encode($value);
                        $item->save();
                    }
                }
                $diffArray = array_diff($members,$clientKeys);
                Members_permission_group::whereIn('member_id',$diffArray)->delete();
            }else{
                Members_permission_group::whereIn('member_id',$members)->delete();
            }

            // if($request->input('nutriflow') != "[]"){
            //     $nutriflow = json_decode($request->input('nutriflow')); 
            //     $nutriflow_arrays = array_map(fn($obj) => (array) $obj, $nutriflow);
            //     $nutriFinal =  $this->get_assign_list($nutriflow_arrays);
            //     $nutriKeys = array_keys($nutriFinal);
            //     foreach ($nutriFinal as $key => $value) {
            //         $temp = Members_permission_group::where('member_id',$key)->first();
            //         if($temp){
            //             Members_permission_group::where('id',$temp->id)->update(['nutriflow_permission_group' =>json_encode($value)]);
            //         }else{
            //             $item = new Members_permission_group;
            //             $item->member_id = $key;
            //             $item->nutriflow_permission_group = json_encode($value);
            //             $item->save();
            //         }
            //     }
            //     $diffArray = array_diff($members,$nutriKeys);
            //     Members_permission_group::whereIn('member_id',$diffArray)->update(['nutriflow_permission_group' => null]);
            // }else{
            //     Members_permission_group::whereIn('member_id',$members)->update(['nutriflow_permission_group' => null]);
            // }
            $result['status'] = true;
            $result['message'] = "Uesr Permission updated";
            
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function get_assign_list($assignArray){
        $result = [];
        foreach ($assignArray as $item) {
            $memberId = $item['member_id'];
            $roleId = $item['role_id'];
            if (!isset($result[$memberId])) {
                $result[$memberId] = [];
            }
            $result[$memberId][] = $roleId;
        }
        return $result;
    }

    public function product_permission(Request $request)
    {
        try {
            $cid = $this->clientID;
            $products = array_unique(json_decode($request->input('products')));
            if($request->input('roles') != "[]"){
                $role = json_decode($request->input('roles'));
                $role_arrays = array_map(fn($obj) => (array) $obj, $role);
                $roleFinal = $this->get_product_assign_list($role_arrays);
                $roleKeys = array_keys($roleFinal);
                foreach ($roleFinal as $key => $value) {
                    $temp = Products_permission_group::where('product_id',$key)->where('client_id',$cid)->first();
                    if($temp){
                        Products_permission_group::where('id',$temp->id)->update(['product_permission_group' =>json_encode($value)]);
                    }else{
                        $item = new Products_permission_group;
                        $item->product_id = $key;
                        $item->client_id = $cid;
                        $item->product_permission_group = json_encode($value);
                        $item->save();
                    }
                }
                $diffArray = array_diff($products,$roleKeys);
                Products_permission_group::whereIn('product_id',$diffArray)->where('client_id',$cid)->delete();
            }else{
                Products_permission_group::whereIn('product_id',$products)->where('client_id',$cid)->delete();
            }
            $result['status'] = true;
            $result['message'] = "Product Permission updated";
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        return response()->json($result);
    }

    public function get_product_assign_list($assignArray){
        $result = [];
        foreach ($assignArray as $item) {
            $productId = $item['product_id'];
            $roleId = $item['role_id'];
            if (!isset($result[$productId])) {
                $result[$productId] = [];
            }
            $result[$productId][] = $roleId;
        }
        return $result;
    }


}