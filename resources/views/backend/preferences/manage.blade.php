{{-- manage.blade.php --}}
@extends('backend.master', [
'pageTitle' => 'Admin Preferences',
'activeMenu' => [
'item' => 'Admin Preferences',
'subitem' => 'Admin Preferences',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Admin', 'url' => '#'],
['label' => 'Preferences']
],
])

@push('styles')
<style>
    .dropdown-item.active {
        background-color: #f8f9fa !important;
        color: #ea580c !important;
    }
    .accordion-button {
        background-color: #f8f9fa;
        color: #333;
        font-weight: bold;
    }
    .btn-action {
        margin-left: 10px;
    }
</style>
@endpush

@section('content')
@php
use Carbon\Carbon;
@endphp
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">Admin Preferences</h1>
        </div>
    </div>
    <div class="card-body">
        <div class="accordion" id="commonAccordion">
            <!-- Categories Accordion -->
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="headingCategories">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategories" aria-expanded="false" aria-controls="collapseCategories">
                        Categories
                    </button>
                </h2>
                <div id="collapseCategories" class="accordion-collapse collapse" aria-labelledby="headingCategories" data-bs-parent="#commonAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            @php
                                $half = ceil(count($categories) / 2);
                                $categoriesChunk = array_chunk($categories, $half);
                            @endphp
                            @foreach($categoriesChunk as $chunk)
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($chunk as $category)
                                            <tr>
                                                <td>{{ $category['name'] }}</td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm icon-primary-blue m-0">
                                                        <span class="material-symbols-outlined" data-id="{{$category['CID']}}" data-module="Category" data-val="{{$category['name']}}" onclick="edit_preference(this)">edit</span>
                                                    </button>
                                                    <button class="btn btn-sm icon-primary-orange delete-plan m-0">
                                                        <span class="material-symbols-outlined" data-id="{{$category['CID']}}" data-module="Category" onclick="remove_preference(this)">delete</span>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-end">
                            <a href="javascript:void(0)" class="btn btn-primary-orange plus-icon" data-module="Category" onclick="create_preference(this)" title="Add Category">
                                <i class="material-symbols-outlined">add</i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SubCategories Accordion -->
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="headingSubCategories">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubCategories" aria-expanded="false" aria-controls="collapseSubCategories">
                        SubCategories
                    </button>
                </h2>
                <div id="collapseSubCategories" class="accordion-collapse collapse" aria-labelledby="headingSubCategories" data-bs-parent="#commonAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            @php
                                $half = ceil(count($sub_categories) / 2);
                                $sub_categoriesChunk = array_chunk($sub_categories, $half);
                            @endphp
                            @foreach($sub_categoriesChunk as $chunk)
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($chunk as $s_category)
                                            <tr>
                                                <td>{{ $s_category['name'] }}</td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm icon-primary-blue m-0">
                                                        <span class="material-symbols-outlined" data-id="{{$s_category['SCID']}}" data-module="SubCategory" data-val="{{$s_category['name']}}" onclick="edit_preference(this)">edit</span>
                                                    </button>
                                                    <button class="btn btn-sm icon-primary-orange delete-plan m-0">
                                                        <span class="material-symbols-outlined" data-id="{{$s_category['SCID']}}" data-module="SubCategory" onclick="remove_preference(this)">delete</span>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-end">
                            <a href="javascript:void(0)" class="btn btn-primary-orange plus-icon" data-module="SubCategory" onclick="create_preference(this)" title="Add Sub Category">
                                <i class="material-symbols-outlined">add</i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suppliers Accordion -->
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="headingSuppliers">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSuppliers" aria-expanded="false" aria-controls="collapseSuppliers">
                        Suppliers
                    </button>
                </h2>
                <div id="collapseSuppliers" class="accordion-collapse collapse" aria-labelledby="headingSuppliers" data-bs-parent="#commonAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            @php
                                $half = ceil(count($suppliers) / 2);
                                $suppliersChunk = array_chunk($suppliers, $half);
                            @endphp
                            @foreach($suppliersChunk as $chunk)
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($chunk as $supplier)
                                            <tr>
                                                <td>{{ $supplier['company_name'] }}</td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm icon-primary-blue m-0">
                                                        <span class="material-symbols-outlined" data-id="{{$supplier['id']}}" data-module="Supplier" data-val="{{$supplier['company_name']}}" onclick="edit_preference(this)">edit</span>
                                                    </button>
                                                    <button class="btn btn-sm icon-primary-orange delete-plan m-0">
                                                        <span class="material-symbols-outlined" data-id="{{$supplier['id']}}" data-module="Supplier" onclick="remove_preference(this)">delete</span>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-end">
                            <a href="javascript:void(0)" class="btn btn-primary-orange plus-icon" data-module="company" onclick="create_preference(this)" title="Supplier">
                                <i class="material-symbols-outlined">add</i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- Modal -->
     <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="export_column">
                    <div class="form-group d-flex flex-column">
                        <label class="text-primary-orange col-form-label" for="labour_type">Name</label>
                        <input type="text" class="form-control" name="pref_val" id="pref_val" >
                    </div>
                </div>
                <input type="hidden" id="pref_module">
                <input type="hidden" id="pref_id">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-blue" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-secondary-blue" onclick="save_preference();">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="prefModal" tabindex="-1" aria-labelledby="prefModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="prefModalLabel"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="export_column">
                    <div class="form-group d-flex flex-column">
                        <label class="text-primary-orange col-form-label" for="labour_type">Name</label>
                        <input type="text"  class="form-control" name="pref_create_val" id="pref_create_val" >
                    </div>
                </div>
                <input type="hidden" id="pref_cmodule">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-blue" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-secondary-blue" onclick="new_preference();">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function edit_preference(_this){
    let id = $(_this).attr('data-id')
    let module = $(_this).attr('data-module')
    let val = $(_this).attr('data-val')
    $('#pref_val').val(val)
    $('#exampleModalLabel').html(`Edit ${module}`)
    $('#pref_module').val(module)
    $('#pref_id').val(id)
    $('#exampleModal').modal('show')
}

function save_preference(){
    let id = $('#pref_id').val()
    let module = $('#pref_module').val()
    let val = $('#pref_val').val()
    let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'id':id,'module':module,'value':val};	
    $.ajax({
        type: "POST",
        url: "{{route('preference.update')}}",
        dataType: 'json',
        data: data,
        beforeSend: function () {
        },
        success: function (response) {             
            $('#exampleModal').modal('hide')
            if(response.status == true){
                show_swal(1, `${module} updated.`);
                setTimeout(() => {
                    window.location.href="{{route('preference.manage')}}"
                }, 1000); 
            }else{
                show_swal(0, response.message);
            }
        },
        complete: function(){}
    });
}

function remove_preference(_this){
    let id = $(_this).attr('data-id')
    let module = $(_this).attr('data-module')
    Swal.fire({
        title: "Are you sure?",
        text: "Want to delete this.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Delete it!"
      }).then((result) => {
        if (result.isConfirmed) {
            let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'module':module,'id':id};	
            $.ajax({
                type: "POST",
                url: "{{route('preference.delete')}}",
                dataType: 'json',
                data: data,
                beforeSend: function () {
                },
                success: function (response) {    
                    if(response.status == true){
                        show_swal(1, `${module} deleted.`);
                        setTimeout(() => {
                            window.location.href="{{route('preference.manage')}}"
                        }, 1000); 
                    }else{
                        show_swal(0, response.message);
                    }
                },
                complete: function(){}
            });
        }
    });  
}

function create_preference(_this){

    let module = $(_this).data('module')
    console.log(module);
    
    $('#prefModalLabel').html(`Add ${module}`)
    $('#pref_cmodule').val(module)
    $('#prefModal').modal('show')
}

function new_preference(_this){
    let val = $('#pref_create_val').val()
    let module = $('#pref_cmodule').val()
    let data = {'_token':$('meta[name="csrf-token"]').attr('content'),'module':module,'value':val};	
    $.ajax({
        type: "POST",
        url: "{{route('preference.store')}}",
        dataType: 'json',
        data: data,
        beforeSend: function () {
        },
        success: function (response) {
            let val = $('#pref_create_val').val('')             
            $('#prefModal').modal('hide')
            if(response.status == true){
                show_swal(1, `${module} Added.`);
                setTimeout(() => {
                    window.location.href="{{route('preference.manage')}}"
                }, 1000); 
            }else{
                show_swal(0, response.message);
            }
        },
        complete: function(){}
    });
}

</script>
@endpush