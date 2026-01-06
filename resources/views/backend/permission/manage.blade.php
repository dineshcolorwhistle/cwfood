{{-- manage.blade.php --}}
@extends('backend.master', [
'pageTitle' => 'Permissions',
'activeMenu' => [
'item' => 'Permissions',
'subitem' => 'Permissions',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Admin', 'url' => '#'],
['label' => 'Product']
],
])

@push('styles')
<style>
    table#userPermission thead th.text-primary-orange, table#productPermission thead th.text-primary-orange {color: var(--primary-color) !important;}
    div#custom-text p {font-size: 11px;color: #808080ab !important;}
</style>
@endpush

@section('content')
@php
use Carbon\Carbon;
@endphp
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Permissions</h1>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="resultsContainer" class="scrollable-results">
            <h5> User Permission</h5>
            <table class="table responsiveness" id="userPermission">
                <thead>
                    <th></th>
                    @foreach($roles as $role)
                        <th class="text-primary-orange">{{$role['name']}}</th>
                    @endforeach
                </thead>
                <tbody id="resultsBody">
                    @foreach($users as $user)
                    <tr>
                        @php $i = $loop->index +1; @endphp
                        <td><span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"data-bs-title="@if($user->workspace_names != '') {{$user->workspace_names}} @endif">{{$user->name}}</span></td>
                        @foreach($roles as $role)
                            @php $j = $loop->index +1; @endphp
                            @if(array_key_exists('client_id',$role))
                                @if($user->role_id == 4)
                                <td>
                                    <div class="form-check-temp p-1">
                                        <input class="form-check-input wordpress_check" type="checkbox" id="user{{$i}}{{$j}}" data-type="client" data-user-role="{{$user->role_id}}" data-role="{{$role['id']}}" data-user="{{$user->id}}" @if(sizeof($user->client_permissionArray) > 0 && in_array($role['id'],$user->client_permissionArray)) checked @endif>
                                    </div>
                                </td>
                                @else
                                <td>
                                    <div class="form-check-temp p-1">
                                        <input class="form-check-input wordpress_check" type="checkbox" id="user{{$i}}{{$j}}" data-type="client" data-user-role="{{$user->role_id}}" data-role="{{$role['id']}}" data-user="{{$user->id}}"  checked  disabled>
                                    </div>
                                </td>
                                @endif
                            @else
                                <td>
                                    <div class="form-check-temp p-1">
                                        <input class="form-check-input wordpress_check" type="checkbox" id="user{{$i}}{{$j}}" data-type="nutriflow" data-user-role="{{$user->role_id}}" data-role="{{$role['id']}}" data-user="{{$user->id}}" @if(sizeof($user->nutriflow_permissionArray) > 0 && in_array($role['id'],$user->nutriflow_permissionArray)) checked @endif>
                                    </div>
                                </td>
                            @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div id="custom-text">
                <p>Each Member can be assigned to multiple User Roles, with each role granting access to specific pages. For clarity, both Super Admins and Admins have full access to all pages.</p>
            </div>
            <button type="button"  style="float:right;" class="btn btn-secondary-blue" onclick="userPermission()" >Save User permission</button>
        </div>

        <div class="scrollable-results pt-5">
            <h5> Product Permission</h5>
            <table class="table responsiveness dataTable" id="productPermission">
                <thead>
                    <th></th>
                    <th></th>
                    @foreach($roles as $role)
                        <th class="text-primary-orange">{{$role['name']}}</th>
                    @endforeach
                </thead>
                <tbody id="resultsBody">
                    @foreach($pages as $page)
                    <tr>
                        @php $i = $loop->index +1; @endphp
                        <td>{{$page->title}}</td>
                        <td>{{$page->scope}}</td>
                        @foreach($roles as $role)
                            @php $j = $loop->index +1; @endphp
                            <td>
                                <div class="form-check-temp p-1">
                                    <input class="form-check-input wordpress_check" type="checkbox" id="page{{$i}}{{$j}}" data-product-id="{{$page->id}}" data-role-id="{{$role['id']}}" @if(sizeof($page->client_productArray) > 0 && in_array($role['id'],$page->client_productArray)) checked @endif>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button"  style="float:right;" class="btn btn-secondary-blue mt-3" onclick="productPermission()" >Save Product permission</button>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>

    function userPermission() {
        let clientobj = []; let nutriflowobj = []; let membersobj = [];
        $('table#userPermission').DataTable().destroy();
        $('table#userPermission').dataTable({ paging: false, ordering: false });
        $("table#userPermission tbody tr td").each(function () {
            if($(this).find(':input').length == 0){
            }else{
                var id = $(this).find(':input').attr('id');
                var type = $(this).find(':input').attr('data-type');            
                var user_id = $(this).find(':input').attr('data-user');
                var role_id = $(this).find(':input').attr('data-role');
                var user_role = $(this).find(':input').attr('data-user-role');
                if(user_role == "4"){
                    membersobj.push(user_id)
                    if(type == "client" && $('#'+id).is(':checked')){
                        clientobj.push({'member_id': user_id, 'role_id': role_id});
                    }
                    if(type == "nutriflow" && $('#'+id).is(':checked')){
                        nutriflowobj.push({'member_id': user_id, 'role_id': role_id});
                    }
                }
            }
        });
        var members = JSON.stringify(membersobj);
        var client = JSON.stringify(clientobj);
        var nutriflow = JSON.stringify(nutriflowobj);
        let data = {'client':client,'nutriflow':nutriflow,'members':members,'_token':$('meta[name="csrf-token"]').attr('content')}
        let url = "{{route('update.user_permission')}}";
        permission_group_commonAjax(data,url,'user');
    }

    function productPermission() {
        let rolesobj = []; let productobj = [];
        $('table#productPermission').DataTable().destroy();
        $('table#productPermission').dataTable({ paging: false, ordering: false });
        $("table#productPermission tbody tr td").each(function () {
            if($(this).find(':input').length == 0){
            }else{
                var id = $(this).find(':input').attr('id');
                var product_id = $(this).find(':input').data('product-id');            
                var role_id = $(this).find(':input').data('role-id');
                productobj.push(product_id)
                if($('#'+id).is(':checked')){
                    rolesobj.push({'product_id': product_id, 'role_id': role_id});
                }
            }
        });
        var products = JSON.stringify(productobj);
        var roles = JSON.stringify(rolesobj);
        let data = {'roles':roles,'products':products,'_token':$('meta[name="csrf-token"]').attr('content')}
        let url = "{{route('update.product_permission')}}";
        permission_group_commonAjax(data,url,'product');
    }

    function permission_group_commonAjax(data,url,action) {
    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        data: data,
        beforeSend: function () {
    },
    success: function (response) {
        if(response.status == true){
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message,
                timer: 2000
            }).then(() => {
                location.reload();
            });
        }else{
            Swal.fire({
                icon: 'warning',
                title: 'Validation Errors',
                text: response.message
            });
        }
    },
    complete: function () {
        if (action == 'user') {
        $('.userButton').css('display','none');
        } else {
        $('.productButton').css('display','none');
        }
    }
    });
    }
</script>
@endpush
