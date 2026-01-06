@extends('backend.master', [
'pageTitle' => 'Support Management',
'activeMenu' => [
'item' => 'Support',
'subitem' => 'Supports',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Nutriflow Admin', 'url' => '#'],
['label' => 'Support Management']
],
])

@push('styles')
<style>

    /** Status dropdown */
    #tickets-container select.inline-select-status-Resolved + .select2-container--default .select2-selection--single{ background-color: var(--bs-success) !important; }
    #tickets-container select.inline-select-status-Resolved + .select2-container--default .select2-selection--single .select2-selection__rendered{ color:var(--bs-white);}
    #tickets-container select.inline-select-status-InProgress + .select2-container--default .select2-selection--single{ background-color: var(--bs-warning) !important; }
    #tickets-container select.inline-select-status-InProgress + .select2-container--default .select2-selection--single .select2-selection__rendered{ color:var(--bs-white);}
    #tickets-container select.inline-select-status-Parked + .select2-container--default .select2-selection--single{ background-color: var(--bs-purple) !important; }
    #tickets-container select.inline-select-status-Parked + .select2-container--default .select2-selection--single .select2-selection__rendered{ color:var(--bs-white);}
    #tickets-container select.inline-select-status-WaitforCustomer + .select2-container--default .select2-selection--single{ background-color: var(--bs-info) !important; }
    #tickets-container select.inline-select-status-WaitforCustomer + .select2-container--default .select2-selection--single .select2-selection__rendered{ color:var(--bs-white);}
    #tickets-container select.inline-select-status-Received + .select2-container--default .select2-selection--single{ background-color: #6c757d !important; }
    #tickets-container select.inline-select-status-Received + .select2-container--default .select2-selection--single .select2-selection__rendered{color:var(--bs-white);}
    
    /** Priority dropdown */
    #tickets-container select.inline-select-priority-Highest + .select2-container--default .select2-selection--single{ background-color: var(--bs-danger) !important; }
    #tickets-container select.inline-select-priority-Highest + .select2-container--default .select2-selection--single .select2-selection__rendered{ color:var(--bs-white);}
    #tickets-container select.inline-select-priority-High + .select2-container--default .select2-selection--single{ background-color: var(--bs-warning) !important; }
    #tickets-container select.inline-select-priority-High + .select2-container--default .select2-selection--single .select2-selection__rendered{ color:var(--bs-white);}
    #tickets-container select.inline-select-priority-Medium + .select2-container--default .select2-selection--single {background-color: var(--bs-yellow) !important;}
    /* #tickets-container select.inline-select-priority-Medium + .select2-container--default .select2-selection--single .select2-selection__rendered{ color:var(--bs-white);} */
    #tickets-container select.inline-select-priority-Low + .select2-container--default .select2-selection--single{ background-color: var(--bs-success) !important; }
    #tickets-container select.inline-select-priority-Low + .select2-container--default .select2-selection--single .select2-selection__rendered{ color:var(--bs-white);}
    #tickets-container select.inline-select-priority-Lowest + .select2-container--default .select2-selection--single{ background-color: var(--bs-green) !important; }
    #tickets-container select.inline-select-priority-Lowest + .select2-container--default .select2-selection--single .select2-selection__rendered{ color:var(--bs-white);}

    #tickets-container select + .select2.select2-container{border:none;}
    span#custom-text p { font-size: 11px; color: #808080ab !important; }
    #tickets-container tr td.ticket-topic a{text-decoration:none}
    .stat-value{font-size:25px;font-weight:600;}
    .stat-label{color:var(--Primary-Dark-Mud)}
    .batchbase-ticket-container .select2-container--default .select2-results>.select2-results__options li {font-size: 13px;}
    .batchbase-ticket-container .select2-container .select2-selection--single{height: 34px;}
    .batchbase-ticket-container .select2-container--default .select2-selection--single .select2-selection__rendered{color: #5e5d5d; line-height: 35px; font-size: 13px;}
    .batchbase-ticket-container .select2-container--default .select2-selection--single .select2-selection__arrow{height: 28px;}
    .batchbase-ticket-container table select + .select2.select2-container{padding: 0px !important;}
    .support-filter-section .btn-secondary-blue{padding: 6px 10px;}
    button#new-ticket-btn { background-color: var(--primary-color) !important; color: white !important; border: 1px solid var(--primary-color) !important;}
    .batchbase-ticket-btn-filter.active {color: var(--bs-white)!important;background-color: var(--primary-color) !important;}
    .delete-row-data{cursor:pointer;}
    
    /* Backdrop behind drawer */
    .drawer-backdrop {position: fixed;inset: 0;background-color: rgba(0, 0, 0, 0.4);z-index: 1040;}
    /* Drawer itself */
    .ticket-drawer { position: fixed; top: 0; right: -400px; /* hidden initially */ width: 400px; height: 100vh; background: #fff; z-index: 1050; display: flex; flex-direction: column; transition: right 0.3s ease-in-out; }
    /* When visible, move into view */
    .ticket-drawer.show {right: 0;}
    .drawer-header{ background:var(--primary-color);color:white}
    /* Header and Footer Fixed */
    .drawer-header,.drawer-footer {flex-shrink: 0;}
    /* Scrollable body */
    .drawer-body {overflow-y: auto;flex-grow: 1;}

    .drawer-header .ticket-create-details p{font-size:12px;color:var(--bs-heading-color) !important;margin-bottom:0px;margin-top: 5px;}

    /* Drag and Drop Styles */
    .batchbase-ticket-table .drag-handle span{width: 20px;padding-right: 0;padding-left: 1rem;cursor: grab;opacity: 0;transition: opacity 0.2s ease-in-out;}
    .batchbase-ticket-table tbody tr:hover .drag-handle span {opacity: 1;} 
    .batchbase-ticket-table tbody tr:hover {background-color: var(--Menu-Back-Ground);}

    /** View */
    .ticket-images { display: flex; gap: 15px; flex-wrap: wrap; }
    .ticket-images-wrap { position: relative; padding: 0; } 
    .ticket-images-wrap img:hover { opacity: 0.5; cursor: pointer; } 
    .ticket-images-wrap .overlay { position: absolute; display: none; width: 100%; height: 100%; top: 0; left: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 1; cursor: pointer; } 
    .ticket-images-wrap:hover .overlay { display: block; } 
    .ticket-images-wrap .del-wrap { position: absolute; top: 5px; right: 25px; z-index: 3; display: flex; gap: 8px; } 
    .ticket-images-wrap a.action { opacity: 0; transition: opacity 0.3s; color: white; z-index: 3; } 
    .ticket-images-wrap:hover a.action { opacity: 1; }
    .comment-action-section span{cursor: pointer;}
    .comment-body-section h5{color:var(--secondary-color);}
    .comment-body-section a {text-decoration: none;}
    h4{color:black;font-size: 15px !important;}
    .ticket-details-section{border: 1px solid #c3c3c3; padding: 25px;}
</style>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
@endpush
@php
    use App\Models\User;
    use Carbon\Carbon;
    $statusArray = ['Received','In progress','Parked','Waiting for customer','Resolved'];
    $categoryArray = ['Technical Bug','Account Access','Billing & Payments','Product Question','Feature Request','Integration/API','Security','Onboarding Help','Feedback'];
    $priorityArray = ['Highest','High','Medium','Low','Lowest'];
    $userID = Session::get('user_id');
    $userDetails = User::findOrfail($userID);
    $user_name = $userDetails->name;
@endphp
@section('content')
<div class="container-fluid batchbase-ticket-container my-4">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <div>
                <h1 class="page-title">Support Tickets</h1>
                <p>An overview of all support requests.</p>
            </div>
            <div class="action-bar d-flex align-items-center" style="gap:25px;">

                <!-- Download Button -->
                <div class="btn-group click-dropdown">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Download Tickets">
                        <span class="material-symbols-outlined">download</span>
                    </button>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="https://app.batchbase.com.au/admin/export/tickets-csv">Download as CSV</a></li>
                        <li><a class="dropdown-item" href="https://app.batchbase.com.au/admin/export/tickets-excel">Download as Excel</a></li>
                    </ul>
                </div>

                <!-- Stats -->
                <div class="d-flex stats-wrapper" style="gap:25px;">
                    <div class="stat-item text-center">
                        <div class="stat-value open-count">22</div>
                        <div class="stat-label">Open</div>
                    </div>
                    <div class="stat-item text-center">
                        <div class="stat-value positive close-count">50</div>
                        <div class="stat-label">Resolved</div>
                    </div>
                    <div class="stat-item text-center">
                        <div class="stat-value total-count">72</div>
                        <div class="stat-label">Active Total</div>
                    </div>
                </div>

            </div>

            
        </div>

        <div class="card-body">

            

            <div class="support-filter-section ">
                <div class=" support-container row g-2 align-items-center pb-5">
                    <div class="col-12 col-lg-3">
                        <input type="text" id="topic_search" placeholder="Search by topic..." class="form-control">
                    </div>
                    <div class="col-6 col-lg">
                        <select id="company-filter" class="form-select js-example-basic-single" data-module="clients" onchange="custom_search(this)">
                            <option value="all" selected>All Company</option>
                            @foreach($clients as $key=> $client)
                                <option value="{{$client}}">{{$client}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg">
                        <select id="assignee-filter" class="form-select js-example-basic-single" data-module="assignee" onchange="custom_search(this)">
                            <option value="all" selected>All Assignee</option>
                            @foreach($uniqueAssignees as $key=> $Assignee)
                                <option value="{{$Assignee['name']}}">{{$Assignee['name']}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-lg">
                        <select id="status-filter" class="form-select js-example-basic-single" data-module="status" onchange="custom_search(this)">
                            <option value="all" selected>All Statuses</option>
                            @foreach($statusArray as $status)
                            <option>{{$status}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg">
                        <select id="priority-filter" class="form-select js-example-basic-single" data-module="priority" onchange="custom_search(this)">
                            <option value="all" selected>All Priorities</option>
                            @foreach($priorityArray as $priority)
                            <option>{{$priority}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg">
                        <select id="category-filter" class="form-select js-example-basic-single" data-module="category" onchange="custom_search(this)">
                            <option value="all" selected>All Categories</option>
                            @foreach($categoryArray as $category)
                            <option>{{$category}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg">
                         <select id="archive-filter" class="form-select js-example-basic-single" onchange="sort_active(this)">
                            <option value="active" selected>Active Tickets</option>
                            <option value="resolved">Resolved Tickets</option>
                            <option value="all">All Tickets</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-auto d-flex gap-2">
                        <button id="my-tickets-btn" class="btn btn-secondary-blue batchbase-ticket-btn-filter w-100 admin-only">My Tickets</button>
                        <button id="new-ticket-btn" class="btn btn-secondary-blue batchbase-ticket-btn-primary w-100">New Ticket</button>
                    </div>
                </div>
            </div>

            <!-- Loader -->
            <div id="tableSkeleton" class="skeleton-wrapper">
                @for($i=0;$i<6;$i++)
                <div class="skeleton-row"></div>
                @endfor
            </div>
            <table class="table responsiveness custom-wrap batchbase-ticket-table" id="adminSupport" style="display:none;">
                <thead>
                    <tr>
                        <th></th>
                        <th class="text-primary-blue">Topic</th>
                        <th class="text-primary-blue">Assignee</th>
                        <th class="text-primary-blue">Requester</th>
                        <th class="text-primary-blue">Category</th>
                        <th class="text-primary-blue">Time Spent</th>
                        <th class="text-primary-blue">Due Date</th>
                        <th class="text-primary-blue">Priority</th>
                        <th class="text-primary-blue">Status</th>
                        <th style="display:none;">Sort Order</th>
                        <th style="display:none;">Company</th>
                        <th style="display:none;">status</th>
                        <th style="display:none;">priority</th>
                        <th style="display:none;">myticket</th>
                        <th></th>
                    </tr>
                </thead>
                @php
                    $openCount = $openCount;
                    $closeCount = $closeCount;                    
                @endphp

                <tbody id="tickets-container">
                    @include('backend.admin-support.table', ['tickets' => $tickets])
                </tbody>
            </table>


            <!-- Drawer Backdrop -->
            <div id="ticketDrawerBackdrop" class="drawer-backdrop d-none"></div>
            <!-- Drawer -->
            <form id="supportForm" enctype="multipart/form-data">
                <input type="hidden" name="ticket_number" id="ticket_number" value="{{$ticket_count}}">
                <div id="ticketDrawer" class="ticket-drawer shadow-lg">
                    <div class="drawer-header p-3">
                        <h5 class="mb-0">New Ticket</h5>
                        <div class="ticket-create-details"></div>
                    </div>
                    <div class="drawer-body p-3">
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="client_id">Clients<span class="text-danger">*</span></label>
                                <select name="client_id" id="client_id" class="form-select js-example-basic-single" required onchange="get_client_details(this)">
                                    <option selected disabled>Select Client</option>
                                    @foreach($clients as $key=> $client)
                                        <option value="{{$key}}">{{$client}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3 hidden">
                            <div class="col-12">
                                <label class="text-primary-orange" for="workspace_id">Workspaces<span class="text-danger">*</span></label>
                                <select name="workspace_id" id="workspace_id" class="form-select js-example-basic-single">
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="topic">Summary<span class="text-danger">*</span></label>
                                <input type="text" name="topic" id="topic" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 quill-editor-wrapper">
                                <div class="quill-editor-wrapper">
                                    <label class="text-primary-orange" for="description">Description<span class="text-danger">*</span></label>
                                    <div class="quill-editor" data-input="description"></div>
                                    <input type="hidden" name="description" value="">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="category">Category<span class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-select js-example-basic-single" required>
                                    @foreach($categoryArray as $category)
                                        <option value="{{$category}}" @if($category == "Technical Bug") selected @endif>{{$category}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="priority">Priority</label>
                                <select name="priority" id="priority" class="form-select js-example-basic-single" required>
                                    @foreach($priorityArray as $priority)
                                        <option value="{{$priority}}" @if($priority =="Medium") selected @endif>{{$priority}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3 ticket-status">
                            <div class="col-12">
                                <label class="text-primary-orange" for="status">Status</label>
                                <select name="status" id="status" class="form-select js-example-basic-single">
                                    @foreach($statusArray as $status)
                                        <option value="{{$status}}" @if($status =="Received") selected @endif>{{$status}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="due_date">Due Date</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" value="">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="requester">Requester<span class="text-danger">*</span></label>
                                <select name="requester" id="requester" class="form-select js-example-basic-single">
                                </select>
                                <span id="custom-text"><p class="mb-0">Receives updates and replies to the ticket.</p></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="ccs">CC's</label>
                                <select name="ccs[]" id="ccs" class="form-select fa-basic-multiple" multiple>
                                </select>
                                <span id="custom-text"><p class="mb-0">Also notified; can follow the conversation.</p></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="assignee">Assignee</label>
                                <select name="assignee" id="assignee" class="form-select js-example-basic-single">
                                    <option value="" disabled>Select Assignee</option>
                                    @foreach($batchbase_admins as $key => $admin)
                                        <option value="{{$key}}">{{$admin}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="time_estimated">Time Estimated</label>
                                <input type="text" class="form-control time-input" name="time_estimated" placeholder="HH:MM"maxlength="5" pattern="^([01]\d|2[0-3]):([0-5]\d)$" value=""  oninput="this.value = this.value.replace(/[^0-9:]/g, '')">    
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="time_spent">Time Spent</label>
                                <input type="text" class="form-control time-input" name="time_spent" placeholder="HH:MM"maxlength="5" pattern="^([01]\d|2[0-3]):([0-5]\d)$"  value="" oninput="this.value = this.value.replace(/[^0-9:]/g, '')">
                            </div>
                        </div>

                        <div class="row mb-3" id="sup_documents">
                            <div class="col-12">
                                <div class="pt-3 pb-3">
                                    <div class="dropzone" id="dropzone">
                                        <span class="material-symbols-outlined upload-icon">upload</span>
                                        <p class="mt-1">Drag & drop files here or <span class="uploan-span">click to upload</span></p>
                                        <input type="file" id="fileInput" accept=".png,.jpg,jpeg,.svg,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx" multiple hidden>
                                        <span class="mt-1">Accepted file formats: ".png,jpg,jpeg,svg,pdf,<br>doc,docx,xls,xlsx,csv"</span>
                                    </div>
                                </div>
                                <ul class="list-group mt-2" id="fileList" style="width: 100%;"></ul>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-12" id="ticket_documents">
                            </div>
                        </div>

                        <div class="row mb-3" id="comment_section">
                            <div class="col-12">
                                <div class="comments-section d-flex mt-2" style="display: flex ; align-items: center; gap: 11px;">
                                    <h3>Comments</h3> 
                                    <!-- <button type="button" class="btn btn-primary-orange plus-icon" id="addSupportBtn" title="Add Comment"><span class="material-symbols-outlined">add</span></button> -->
                                </div>

                                <div class="comment-add-section row p-2">
                                    <div class="col-md-12"> 
                                        <h2 class="text-primary-orange">Add Comment</h2>
                                        <form id="CommentAddForm" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="ticket_id" id="ticket_id" value="">
                                            <div class="col-md-12 form-group">
                                                <label class="text-primary-orange" for="add_comment">Comment<span class="text-danger">*</span></label>
                                                <div class="quill-editor-wrapper">
                                                    <div class="quill-editor" data-input="add_comment"></div>
                                                    <input type="hidden" name="add_comment" value="">
                                                </div>
                                            </div>
                                            <div class="pt-3 pb-3">   
                                                <div class="dropzone" id="dropzone1">
                                                    <span class="material-symbols-outlined upload-icon">upload</span>
                                                    <p class="mt-1">Drag & drop files here or <span class="uploan-span">click to upload</span></p>
                                                    <input type="file" id="fileInput1" accept=".png,.jpg,jpeg,.svg,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx" multiple hidden>
                                                    <span class="mt-1">Accepted file formats: "png,jpg,jpeg,svg,pdf,<br>doc,docx,xls,xlsx,csv"</span>
                                                </div>
                                                <ul class="list-group mt-2" id="fileList1" style="width: 100%;"></ul>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12" id="comment_details">
                            </div>
                        </div>
                    </div>
                    <div class="drawer-footer p-3 bg-light text-end">
                        <button class="btn btn-secondary me-2" onclick="closeDrawer()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveSupportBtn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title text-primary-orange" id="actionModalLabel">Add/Edit Comment</h2>
            </div>
            <form id="supportCommentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="comment_id" id="comment_id" value="">
                        <div class="col-md-12 form-group">
                            <label class="text-primary-orange" for="edit_comment">Comment<span class="text-danger">*</span></label>
                            <div class="quill-editor-wrapper">
                                <div class="quill-editor" data-input="edit_comment"></div>
                                <input type="hidden" name="edit_comment" value="<p>This is test comment...</p>">
                            </div>
                        </div>
                        <div class="pt-3 pb-3">
                            <label for="support_edit_documents" class="btn btn-outline-secondary">Choose Files</label>
                            <span id="fileLabel1">No file chosen</span>
                            <input name="support_edit_documents[]" id="support_edit_documents" accept=".png,.jpg,jpeg,.svg,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx" type="file" multiple hidden />
                        </div>
                        <ul class="list-group" id="fileList1" style="width: 70%;"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                    <button type="submit" class="btn btn-secondary-blue" id="editCmtBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="cmptimeModal" tabindex="-1" role="dialog" aria-labelledby="cmptimeModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title text-primary-orange" id="cmptimeModalLabel">Enter Completion Time</h2>
            </div>
            <input type="hidden" name="ticket_uniq_id" id="ticket_uniq_id" value="">
            <input type="hidden" name="ticket_uniq_type" id="ticket_uniq_type" value="">
            <input type="hidden" name="ticket_uniq_status" id="ticket_uniq_status" value="">
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="text-primary-orange" for="time_complete">Time Spent</label>
                        <input type="text" class="form-control time-input" name="time_complete" placeholder="HH:MM"maxlength="5" pattern="^([01]\d|2[0-3]):([0-5]\d)$" value=""  oninput="this.value = this.value.replace(/[^0-9:]/g, '')">    
                    </div>
                </div>
                <div id="additionComment" class="d-none">
                    <div class="col-12 mb-3">
                        <label class="text-primary-orange" for="ticket_assignee">Assignee</label>
                        <input type="text" class="form-control" id="ticket_assignee"  value="" readonly>    
                    </div>
                    <div class="col-12">
                        <div class="col-md-12 form-group">
                            <label class="text-primary-orange" for="additional_comment">Comment</label>
                            <div class="quill-editor-wrapper">
                                <div class="quill-editor" data-input="additional_comment"></div>
                                <input type="hidden" name="additional_comment" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-white" data-bs-dismiss="modal" id="closecmptimeModal">Close</button>
                <button type="submit" class="btn btn-secondary-blue" id="saveTimeBtn">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css">
<script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
<script src="{{ asset('assets') }}/js/default-dropzone.js"></script>
<script>
    let files = [];
    let comment_files = [];
    let edit_files = [];
    const APP_URL = "{{ env('APP_URL') }}";
    let UserID = "{{$userID}}"
    let UserName = "{{$user_name}}"
    let batchbase_admins = @json($batchbase_admins);


    $(document).ready(function() {
        /**Ticket stats */
        let openCount = {{ $openCount }};   // number
        let closeCount = {{ $closeCount }}; // number
        let total = openCount + closeCount;
        $('.open-count').html(openCount);
        $('.close-count').html(closeCount);
        $('.total-count').html(total);

        initQuillEditors(); //quil editor initialize
        $('.js-example-basic-single, .fa-basic-multiple').select2({width: '100%'});
        let table = $('#adminSupport').DataTable({
            responsive: true,
            ordering: false,
            dom: "<'row mb-4'<'col-md-6 col-6 col-sm-6'f><'col-md-6 col-6 col-sm-6'l>>" +
                "<'row table-responsiveness'<'col-sm-12'tr>>" +
                "<'row'<'col-md-5'i><'col-md-7'p>>",
            language: {
                search: "",
                searchPlaceholder: "Search",
                lengthMenu: "_MENU_ per page",
                paginate: {
                    previous: "<i class='material-symbols-outlined'>chevron_left</i>",
                    next: "<i class='material-symbols-outlined'>chevron_right</i>"
                }
            },
            pageLength: 25,
            rowReorder: {
                selector: 'td.drag-handle',   // drag handle only
                dataSrc: 9,                  // we'll handle DB update manually
                update:false
            },
            columnDefs: [
                { targets: [9], visible: false, searchable: false } // hide sort_order column
            ],
            initComplete: function() {
                 $("#tableSkeleton").fadeOut(200, ()=>{
                    $("#adminSupport").fadeIn(250);
                });
                const tableWrapper = $(this).closest('.dataTables_wrapper');
                const searchBox = tableWrapper.find('.dataTables_filter');
                const lengthDropdown = tableWrapper.find('.dataTables_length');

                searchBox.css({
                    'float': 'left',
                    'margin-top': '0',
                    'opacity':'0'
                });
                lengthDropdown.css('float', 'right');

                var table = $('#adminSupport').dataTable().api();
                defaultValue = `Received|In progress|Parked|Waiting for customer`;
                table.columns(11).search(defaultValue, true, false).draw();
            }
        });

        // Capture drag & drop event
        table.on('row-reorder', function(e, diff, edit) {
            let order = [];
            diff.forEach(function(change) {  
                let rowData = table.row(change.node).data();
                order.push({
                    id: $(change.node).find('select[name="sort_status"]').data('ticket'), // ticket id from select
                    sort_order: change.newData
                });
            });
           
            if (order.length) {
                $.ajax({
                    url: "{{ route('admin.tickets.reorder') }}",
                    type: "POST",  
                    data: {
                        _token: "{{ csrf_token() }}",
                        order: order
                    },
                    success: function(res) {                        
                        if (res.html) {
                            // Replace tbody with fresh rows from server
                            $('#adminSupport tbody').html(res.html);

                            // Tell DataTable to re-read the DOM
                            table.clear();
                            table.rows.add($('#adminSupport tbody tr')).draw(false);

                            $('#adminSupport tbody').find('.js-example-basic-single, .fa-basic-multiple').select2({width: '100%'});

                        }
                    }
                });
            }
        });

        // Optional: trigger with your button
        document.getElementById('new-ticket-btn').addEventListener('click', function () {
            openDrawer();
            $(`#ticket_documents,#comment_section,#comment_details`).css('display','none');
            $(`#sup_documents`).css('display','block');
            $(`#ticket_id`).val('');
            $('#saveSupportBtn').html('Save')
            $(`.drawer-header h5`).html(`Create New Ticket`);
            $('#requester').val(UserID);
            $(`.drawer-header .ticket-create-details`).html(``);
            $(`.ticket-status`).css('display','none');
        });
        document.getElementById('ticketDrawerBackdrop').addEventListener('click', closeDrawer);

        const myTicketsBtn = document.getElementById('my-tickets-btn');
        let isMyTicketsActive = false;
        myTicketsBtn?.addEventListener('click', () => {
            isMyTicketsActive = !isMyTicketsActive;
            myTicketsBtn.classList.toggle('active', isMyTicketsActive);
            var table = $('#adminSupport').DataTable();
            if (isMyTicketsActive) {
                // Remove any previous filters
                $.fn.dataTable.ext.search = [];
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    let col2 = data[2] || '';
                    let col3 = data[3] || '';
                    return (col2 === UserName || col3 === UserName);
                });
                table.draw();
            } else {
                // Clear all custom filters
                $.fn.dataTable.ext.search = [];
                // Reset all column searches
                table.columns().search('').draw();
                // Apply your default filter
                let defaultValue = `Received|In progress|Parked|Waiting for customer`;
                table.column(11).search(defaultValue, true, false).draw();   
            }
        });
    });

    
    /** Side Drawer handling */
    function openDrawer() {
        document.getElementById('ticketDrawer').classList.add('show');
        document.getElementById('ticketDrawerBackdrop').classList.remove('d-none');
    }

    function closeDrawer() {
        resetForm();
        document.getElementById('ticketDrawer').classList.remove('show');
        document.getElementById('ticketDrawerBackdrop').classList.add('d-none');
    }

    function resetForm() {
        const form = document.getElementById("supportForm");

        // Reset native form fields
        form.reset();

        // Reset select2 single and multiple
        $(form).find('select').each(function () {
            $(this).val(null).trigger('change');
        });

        // Reset Quill editors
        form.querySelectorAll('.quill-editor').forEach(editorContainer => {
            const quillInstance = Quill.find(editorContainer);
            if (quillInstance) {
                quillInstance.setText('');
            }
        });
    }

    /**Quil editor */
    window.quillInstances = window.quillInstances || {};
    function initQuillEditors() {
        const toolbarOptions = [
            ['bold', 'italic', 'underline'],
            [{'list': 'ordered'}, {'list': 'bullet'}],
            ['clean']
        ];

        $('.quill-editor').each(function () {
            const $editor = $(this);

            // Prevent re-initialization
            if ($editor.data('quill-initialized')) {
                return;
            }

            const $input = $(`input[name="${$editor.data('input')}"]`);

            const quill = new Quill($editor[0], {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions
                }
            });

            if ($input.val()) {
                quill.root.innerHTML = $input.val();
            }

            quill.on('text-change', () => {
                $input.val(quill.root.innerHTML);
            });

        // Mark as initialized
            $editor.data('quill-initialized', true);

            window.quillInstances[$editor.data('input')] = quill;
        });
    }

    function get_client_details(_this){
        let selectValue = $(_this).val()
        $.ajax({
            url:  "{{ route('admin.get.client-details') }}",
            method: 'GET',
            data: {
                client: selectValue
            },
            success: function(response) {
                if (response.success) {
                    let workspaceArray = response.workspaces;
                    let membersArray = response.members;

                    $('#workspace_id').empty();
                    workspaceArray.forEach(workspace => {
                        const isPrimary = workspace.ws_primary === 1 ? 'selected' : '';
                        $('#workspace_id').append(`<option value="${workspace.id}" ${isPrimary}>${workspace.name}</option>`);
                    });
                    
                    $('#requester').empty();
                    $('#requester').append(`<option value="" selected disabled>Select Requester</option>`);
                    membersArray.forEach(member => {
                        $('#requester').append(`<option value="${member.user_id}">${member.name}</option>`);
                    });

                    $('#ccs').empty();
                    membersArray.forEach(member => {
                        $('#ccs').append(`<option value="${member.user_id}">${member.name}</option>`);
                    });
                    
                    $.each(batchbase_admins, function(key, value) {
                        $('#requester').append(`<option value="${key}">${value}</option>`);
                        $('#ccs').append(`<option value="${key}">${value}</option>`);
                    });

                }else{
                    Swal.fire({
                        title: 'Warning',
                        html: response.message,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON.message || 'An error occurred'
                });
            }
        });
    }

    function formatTime(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 3) {
            value = value.slice(0, 2) + ':' + value.slice(2, 4);
        }
        e.target.value = value.slice(0, 5);
    }

    // Apply to all elements with class `time-input`
    document.querySelectorAll('.time-input').forEach(function(input) {
        input.addEventListener('input', formatTime);
    });

    $(document).on('submit','#supportForm',function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $('#saveSupportBtn');
        // Disable submit button to prevent multiple submissions
        $submitButton.prop('disabled', true);
        let ticketID = $(`#ticket_id`).val()
        const url = (ticketID)? "{{ route('admin.update.ticket', ':id') }}".replace(':id', ticketID): "{{ route('admin.save.ticket') }}";
        const method = 'POST';
        const formData = new FormData(this);

        $('.quill-editor').each(function() {
            const quill = Quill.find(this);
            const $input = $(`input[name="${$(this).data('input')}"]`);
            $input.val(quill.root.innerHTML);
        });

        fileBuckets[0].forEach((item, index) => {
            formData.append("image_file[]", item.file); // Append each file to FormData
        });

        fileBuckets[1].forEach((item, index) => {
            formData.append("comment_file[]", item.file); // Append each file to FormData
        });

        formData.append('comment_description', $('input[name="add_comment"]').val());
  
        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#loader").removeClass("d-none"); // show loader
            },
            complete: function() {
                // Re-enable submit button
                $submitButton.prop('disabled', false);
                 $("#loader").addClass("d-none"); // hide loader
            },
            success: function(response) {
                if (response.success) {
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
                        title: 'Error',
                        html: response.message,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorList = '';
                    $.each(errors, function(key, value) {
                        $.each(value, function(index, message) {
                            errorList += `<div>${message}</div>`;
                        });
                    });
                    Swal.fire({
                        title: 'Validation Error',
                        html: `${errorList}`,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            }
        });
    });

    $(document).on('click','.edit-ticket',function(e){
        if ($(e.target).closest('.drag-handle').length || $(e.target).closest('.select2-container').length || $(e.target).closest('.delete-row-data').length) {
            return; // do nothing
        }

        let id = $(this).data('ticket');
        const url = "{{ route('admin.support.edit', ':id') }}".replace(':id', id);
        $.ajax({
            url:  url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $(`#ticket_id`).val(response.details.ticket.id)
                    let client_id = (response.details.ticket.client_id)?? ''; 
                    let topic = (response.details.ticket.topic)?? '';
                    let description = (response.details.ticket.description)?? '';
                    let category = (response.details.ticket.category)?? '';
                    let priority = (response.details.ticket.priority)?? '';
                    let status = (response.details.ticket.status)?? '';
                    let duedate = (response.details.ticket.due_date)?? '';
                    let requester = (response.details.ticket.requester)?? '';
                    let ccArray = (response.details.ticket.ccs)? JSON.parse(response.details.ticket.ccs):[];
                    let time_estimate = (response.details.ticket.time_estimated)?? '';
                    let time_spent = (response.details.ticket.time_spent)?? '';
                    let assignee = (response.details.ticket.assignee)??null;
                    let ticket_createdBy = (response.details.ticket.creator.name)??'';
                    let ticket_createdAt = (response.details.ticket.created_at_formatted)??'';

                    $('#client_id').val(client_id).select2();
                    $('#topic').val(topic);
                    $('#category').val(category).select2();
                    $('#priority').val(priority).select2();
                    $('#status').val(status).select2();
                    $('#due_date').val(duedate);
                    $('input[name="time_estimated"]').val(time_estimate);
                    $('input[name="time_spent"]').val(time_spent);

                    initQuillEditors();
                    const quill = window.quillInstances['description'];
                    if (quill) {
                        quill.setContents([]); // Clear previous
                        quill.clipboard.dangerouslyPasteHTML(description); // ✅ Set new content
                    } else {
                        console.warn('Quill instance not found for description');
                    }

                    if (response.details.members) {
                        let members = response.details.members;
                        // Loop through members
                        $('#requester,#ccs,#assignee').empty();
                        $.each(batchbase_admins, function(key, value) {
                            $('#requester').append(`<option value="${key}">${value}</option>`);
                            $('#ccs').append(`<option value="${key}">${value}</option>`);
                        });

                        $.each(members, function(index, member) {
                            // let selected = (member.user_id === requester) ? 'selected' : '';                            
                            $('#requester').append(
                                $('<option>', {
                                    value: member.user_id,  // or member.user_id if you prefer
                                    text: member.name,
                                    // selected: selected
                                })
                            );
                        });
                        
                        $('#requester').val(requester).trigger('change');

                        $.each(members, function(index, member) {
                            $('#ccs').append(
                                $('<option>', {
                                    value: member.user_id,   // or member.user_id
                                    text: member.name
                                })
                            );
                        });
                        $('#ccs').val(ccArray).trigger('change');
                    }

                    if (response.details.teammates) {
                        let teammates = response.details.teammates;
                        $.each(teammates, function(index, teammate) {
                            // let selected = (teammate.id === assignee) ? 'selected' : '';
                            $('#assignee').append(
                                $('<option>', {
                                    value: teammate.id,  // or teammate.user_id if you prefer
                                    text: teammate.name,
                                    // selected: selected
                                })
                            );
                        });
                        $('#assignee').val(assignee).trigger('change');

                    }

                    if (response.details.ticket.images) {
                        let images = response.details.ticket.images;
                        let support_doc_html = `
                            <h3 class="mt-2">Support Images</h3>
                            <div class="ticket-images row ms-2"></div>
                        `;
                        // Append container first
                        $('#ticket_documents').html(support_doc_html);
                        

                        // Loop images
                        $.each(images, function(index, image) {
                            let fileFormat = image.file_format.toLowerCase();
                            let filePath = `${image.folder_path}/${image.image_name}`;
                            let fullUrl = `${APP_URL}/${filePath}`;

                            if (['png', 'jpg', 'jpeg', 'svg'].includes(fileFormat)) {
                                $('.ticket-images').append(`
                                    <div class="col-lg-4 ticket-images-wrap">
                                        <div class="overlay"></div>
                                        <img src="${fullUrl}" width="100%">
                                        <div class="del-wrap">
                                            <a class="action" href="${fullUrl}" target="_blank">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </a>
                                            <a class="action" href="${fullUrl}" download>
                                                <span class="material-symbols-outlined">download</span>
                                            </a>
                                        </div>
                                    </div>
                                `);
                            } else if (['doc','docx','xls','xlsx','csv','txt','ppt','pptx','pdf'].includes(fileFormat)) {
                                $('.ticket-images').append(`
                                    <div style="display: flex; gap: 5px;">
                                        <p>${image.image_name}</p>
                                        <div class="del-wrap">
                                            <a class="action" href="${fullUrl}" download>
                                                <span class="material-symbols-outlined">download</span>
                                            </a>
                                        </div>
                                    </div>
                                `);
                            }
                        });
                    }

                    if (response.details.ticket.comments) {
                        let comments = response.details.ticket.comments;
                        let comment_html = ``;

                        $.each(comments, function(index, comment) {
                            // Format date like Blade
                            let created_at = new Date(comment.created_at);
                            let formattedDate = created_at.toLocaleString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });

                            comment_html += `
                                <div class="row">
                                    <div class="comment-author-section mb-2">
                                        <span class="material-symbols-outlined">person</span> ${comment.creator.name}
                                        <span class="material-symbols-outlined">calendar_clock</span> ${formattedDate}
                                    </div>
                                    <div class="comment-body-section">
                                        <input type="hidden" id="commID" value="${comment.id}">
                                        <input type="hidden" name="edit_comment_preview" value="${comment.description.replace(/"/g, '&quot;')}">
                                        <p>${comment.description}</p>
                            `;

                            // Handle images & documents if they exist
                            if (comment.comment_image && comment.images && comment.images.length > 0) {
                                let imageFiles = [];
                                let documentFiles = [];

                                $.each(comment.images, function(i, file) {
                                    let extension = file.image_name.split('.').pop().toLowerCase();
                                    if (['png','jpg','jpeg','svg'].includes(extension)) {
                                        imageFiles.push(file);
                                    } else if (['doc','docx','xls','xlsx','csv','txt','ppt','pptx','pdf'].includes(extension)) {
                                        documentFiles.push(file);
                                    }
                                });

                                // Images
                                if (imageFiles.length > 0) {
                                    comment_html += `<div class="Image_section"><strong>Images</strong> `;
                                    $.each(imageFiles, function(i, img) {
                                        comment_html += `<a class="action" href="${APP_URL}/${img.folder_path}/${img.image_name}" target="_blank">${i+1}.${img.image_name}</a>`;
                                        if (i < imageFiles.length - 1) comment_html += ', ';
                                    });
                                    comment_html += `</div>`;
                                }

                                // Documents
                                if (documentFiles.length > 0) {
                                    comment_html += `<div class="Image_section"><strong>Documents</strong> `;
                                    $.each(documentFiles, function(i, doc) {
                                        comment_html += `<a class="action" href="${APP_URL}/${doc.folder_path}/${doc.image_name}" download>${i+1}.${doc.image_name}</a>`;
                                        if (i < documentFiles.length - 1) comment_html += ', ';
                                    });
                                    comment_html += `</div>`;
                                }
                            }

                            comment_html += `
                                    </div>
                                    <div class="comment-action-section">
                                        <span class="material-symbols-outlined comment-edit">edit</span>
                                        <span class="material-symbols-outlined comment-delete" data-id="${comment.id}">delete</span>
                                    </div>
                                </div>
                                <hr>
                            `;
                        });

                        $('#comment_details').html(comment_html);
                    }

                    $('#ticket_documents,#comment_section,#comment_details').css('display','block');
                    $('#sup_documents').css('display','none');
                    $(`.ticket-status`).css('display','block');
                    $('#saveSupportBtn').html('update')
                    $(`.drawer-header h5`).html(`Edit Ticket #${response.details.ticket.ticket_number}`)
                    $(`.drawer-header .ticket-create-details`).html(`<p>Created At: ${ticket_createdAt} <br> Created By: ${ticket_createdBy}</p>`)
                    openDrawer();
 
                }else{
                    Swal.fire({
                        title: 'Warning',
                        html: response.message,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            }
        });
    })



    $(document).on('click','#closeModal',function(){
        $('.comment-add-section').css('display','none')
    });

    $(document).on('click','#addSupportBtn', function() {
        $('.comment-add-section').css('display','block')
        initQuillEditors();
    });

    $(document).on('click','#saveCommentBtn',function(e) {
        e.preventDefault();
        let ticketID = $(`#ticket_id`).val()
        
        const $submitButton = $('#saveCommentBtn');
        // Disable submit button to prevent multiple submissions
        $submitButton.prop('disabled', true);

        let formData = new FormData();
        formData.append('ticket_id', ticketID);
        
        const url = "{{ route('admin.save.comment', ':id') }}".replace(':id', ticketID);  
        const method = 'POST';

        comment_files.forEach((file, index) => {
            formData.append("image_file[]", file); // Append each file to FormData
        });
        $('.quill-editor').each(function() {
            const quill = Quill.find(this);
            const $input = $(`input[name="${$(this).data('input')}"]`);
            $input.val(quill.root.innerHTML);
        });
        formData.append('description', $('input[name="add_comment"]').val());
        common_ajax(url,method,formData,$submitButton);
    });

    function common_ajax(url,method,formData,$submitButton){
        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#loader").removeClass("d-none"); // show loader
            },
            complete: function() {
                // Re-enable submit button
                $submitButton.prop('disabled', false);
                $("#loader").addClass("d-none"); // hide loader
            },
            success: function(response) {
                if (response.success) {
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
                        title: 'Error',
                        html: response.message,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorList = '';
                    $.each(errors, function(key, value) {
                        $.each(value, function(index, message) {
                            errorList += `<div>${message}</div>`;
                        });
                    });
                    Swal.fire({
                        title: 'Validation Error',
                        html: `${errorList}`,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            }
        });
        return;
    }

    $(document).on('click','.comment-edit', function() {
        let desc = $(this).parent().parent().find('input[name="edit_comment_preview"]').val()
            
        let comID = $(this).parent().parent().find('#commID').val()
        // Update hidden inputs
        $('form#supportCommentForm #comment_id').val(comID);

        // Update modal labels  
        $('#actionModalLabel').text('Update Comment');
        $('#editCmtBtn').text('Update Comment');

        // Show modal
        $('#actionModal').modal('show');

        setTimeout(() => {
            initQuillEditors(); // safe due to initialized check
            const quill = window.quillInstances['edit_comment'];
        if (quill) {
            quill.setContents([]); // Clear previous
            quill.clipboard.dangerouslyPasteHTML(desc); // ✅ Set new content
        } else {
            console.warn('Quill instance not found for edit_comment');
        }
        }, 300);
    });

    $(document).on('click', '.comment-delete', function() {  
        const id = $(this).data('id');
        const url = "{{ route('admin.comment.destroy', ':id') }}".replace(':id', id);
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                               location.reload()
                            });
                        }else{
                            Swal.fire({
                                title: 'Error',
                                html: response.message,
                                icon: 'warning',
                                confirmButtonText: 'OK',
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.message || 'An error occurred'
                        });
                    }
                });
            }
        });
    });

    $(document).on('submit','#supportCommentForm',function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $('#editCmtBtn');
        // Disable submit button to prevent multiple submissions
        $submitButton.prop('disabled', true);
        let id = $('#comment_id').val()
        let url = "{{ route('admin.update.comment', ':comment') }}".replace(':comment', id);
        const method = 'POST';
        const formData = new FormData(this);
        edit_files.forEach((file, index) => {
            formData.append("image_file[]", file); // Append each file to FormData
        });
        $('.quill-editor').each(function() {
            const quill = Quill.find(this);
            const $input = $(`input[name="${$(this).data('input')}"]`);
            $input.val(quill.root.innerHTML);
        });
        formData.append('description', $('input[name="edit_comment"]').val());
        formData.append('ticket_id', $(`#ticket_id`).val());
        common_ajax(url,method,formData,$submitButton);
    });

    function custom_search(_this){
        let val = $(_this).val()
        let module = $(_this).data('module')
        let columnCount
        switch (module) {
            case 'assignee':
                columnCount = 2
                break;
            case 'category':
                columnCount = 4
                break;            
            case 'status':
                columnCount = 11
                break;
            case 'priority':
                columnCount = 12
                break;
            case 'clients':
                columnCount = 19
                break;
                
        }
        var table = $('#adminSupport').dataTable().api();
        if (val && val.length > 0) {
            if (Array.isArray(val)) {
                // Join values with pipe (|) for regex OR match, no ^$
                const regex = val.join('|');
                table.columns(columnCount).search(regex, true, false).draw();
            } else {
                // Use contains match for single value
                if(val == "all"){
                    table.columns().search('').draw();
                    defaultValue = `Received|In progress|Parked|Waiting for customer`;
                    table.columns(11).search(defaultValue, true, false).draw(); 
                }else{
                    table.columns(columnCount).search(val, true, false).draw();
                }
                
            }
        } 
        // else {        
        //     const defaultValue = `Received|In progress|Parked|Waiting for customer`;
        //     table.columns(18).search(defaultValue, true, false).draw();
        // }

    }

    // Listen for input in the topic search field
    $('#topic_search').on('input', function() {
         var table = $('#adminSupport').DataTable();
        let searchValue = $(this).val(); // get input value
        table
            .columns(1)        // column index for 'topic'
            .search(searchValue, true, false) // regex = true, smart = false
            .draw();           // redraw table with filtered results
    });


    // function status_update(_this) {
    //     let id = $(_this).data('ticket');
    //     let val = $(_this).val();
    //     if(val == "Resolved"){
    //         $(`#ticket_uniq_id`).val(id);
    //         $(`#cmptimeModal`).modal('show');
    //     }else{
    //         const url = "{{ route('admin.update.ticket.status', ':id') }}".replace(':id', id);
    //         common_update_ajax(url,val);
    //     }
    // }

    function priority_update(_this) {
        let id = $(_this).data('ticket');
        let val = $(_this).val();
        const url = "{{ route('admin.update.ticket.priority', ':id') }}".replace(':id', id);
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                priority: val,
            },
            beforeSend: function () {
                $("#loader").removeClass("d-none"); // show loader
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Priority Updated!',
                        text: response.message,
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                    
                }
            },
            complete: function () {
                $("#loader").addClass("d-none"); // hide loader
            }
        });
    }

    $(document).on('click','#saveTimeBtn',function(){
        let time = $(`input[name='time_complete']`).val()
        if(time){
            let type = $(`#ticket_uniq_type`).val();
            if(type == 'edit'){
                $(`input[name='time_spent']`).val(time)
                $(`#cmptimeModal`).modal('hide');
            }else{
                let status = $(`#ticket_uniq_status`).val();
                let id = $(`#ticket_uniq_id`).val();
                const url = "{{ route('admin.update.ticket.status', ':id') }}".replace(':id', id);
                
                $('.quill-editor').each(function() {
                    const quill = Quill.find(this);
                    const $input = $(`input[name="${$(this).data('input')}"]`);
                    $input.val(quill.root.innerHTML);
                });
                let desc = $('input[name="additional_comment"]').val().trim();
                if (desc === "<p><br></p>" || desc === "") {
                    desc = null;
                }

                common_update_ajax(url,status,time,desc);
            }
        }else{
            Swal.fire({
                title: 'Error',
                html: "Enter Completion time",
                icon: 'warning',
                confirmButtonText: 'OK',
            });
        }
    })

    function common_update_ajax(url,val,time="",desc=""){
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: val,
                time:time,
                desc:desc
            },
            beforeSend: function () {
                $("#loader").removeClass("d-none"); // show loader
            },
            success: function(response) {
                if (response.success) {
                     Swal.fire({
                        icon: 'success',
                        title: 'Status Updated!',
                        text: response.message,
                    });
                    $(`#cmptimeModal`).modal('hide');
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }else{
                    Swal.fire({
                        title: 'Error',
                        html: response.message,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            },
            complete: function () {
                $("#loader").addClass("d-none"); // hide loader
            }
        });
    }

    function sort_active(_this){
        let val = $(_this).val()
        var table = $('#adminSupport').dataTable().api();
        var defaultValue = ``;
        if(val == "resolved"){
            defaultValue = `Resolved`;
        }else if(val == "all"){
            defaultValue = `Received|In progress|Parked|Waiting for customer|Resolved`;
        }else{
            defaultValue = `Received|In progress|Parked|Waiting for customer`;
        }
        table.columns(11).search(defaultValue, true, false).draw();
    }

    let previousVal = null;
    let previousTableVal = null;
    $('#status').on('select2:selecting', function (e) {
        previousVal = $(this).val();
    });

    $('#status').on('change', function (e) {
        let newVal = $(this).val();
        if(newVal == "Resolved"){
            let id = $(`#ticket_id`).val()
            $(`#ticket_uniq_id`).val(id);
            $(`#ticket_uniq_type`).val('edit');
            $(`#cmptimeModal`).modal('show');
        }
    });

    $(document).on('click', '#closecmptimeModal',function(){
        let val = $(`#ticket_uniq_type`).val();
        if(val == 'edit'){
            $('#status').val(previousVal).select2();
        }else{
            let ticketId = $(`#ticket_uniq_id`).val();
            $('.status_update').each(function () {
                let $select = $(this);
                let rowTicketId = $select.data('ticket');
                if (rowTicketId == ticketId) {
                    $select.val(previousTableVal).select2();
                }
            });
        }
    });


    $(document).on('select2:selecting', '.status_update', function () {
        previousTableVal = $(this).val();
    });

    $(document).on('change', '.status_update', function () {
        let val  = $(this).val();
        let id = $(this).data('ticket');
        let timeSpend = $(this).data('timespend');
        let assignee = $(this).data('assignee');
        if(val == "Resolved"  || val == "Waiting for customer"){
            $(`input[name='time_complete']`).val(timeSpend);
            $(`#ticket_uniq_id`).val(id);
            $(`#ticket_uniq_type`).val('table');
            $(`#ticket_uniq_status`).val(val);
            if(val == "Waiting for customer"){
                $('#ticket_assignee').val(assignee);
                $('#additionComment').removeClass('d-none');
            }
            $(`#cmptimeModal`).modal('show');
        }else{
            const url = "{{ route('admin.update.ticket.status', ':id') }}".replace(':id', id);
            common_update_ajax(url,val);
        }
    });

     // Delete  Handling
    $(document).on('click', '.delete-row-data', function() {
        const id = $(this).data('id');
        const url = "{{ route('admin.support.destroy', ':id') }}".replace(':id', id);
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                        $("#loader").removeClass("d-none"); // show loader
                    },
                    complete: function() {
                        $("#loader").addClass("d-none"); // hide loader
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        }else{
                            Swal.fire({
                                title: 'Error',
                                html: response.message,
                                icon: 'warning',
                                confirmButtonText: 'OK',
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.message || 'An error occurred'
                        });
                    }
                });
            }
        });
    });

</script>
@endpush