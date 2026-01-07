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
['label' => 'CW Food Admin', 'url' => '#'],
['label' => 'Support Management']
],
])

@push('styles')
<style>
    span#custom-text p { font-size: 11px; color: #808080ab !important; }
    .select2-container--default.select2-container--disabled .select2-selection--single {cursor: not-allowed;}
    #supportForm input.time-input{background-color:var(--bs-dark-snow) !important;cursor: not-allowed;}
    .batchbase-ticket-container .select2-container--default .select2-results>.select2-results__options li {font-size: 13px;}
    .batchbase-ticket-container .select2-container .select2-selection--single{height: 34px;}
    .batchbase-ticket-container .select2-container--default .select2-selection--single .select2-selection__rendered{color: #5e5d5d; line-height: 35px; font-size: 13px;}
    .batchbase-ticket-container .select2-container--default .select2-selection--single .select2-selection__arrow{height: 28px;}
    .batchbase-ticket-container table select + .select2.select2-container{padding: 0px !important;}
    .stat-value{font-size:25px;font-weight:600;}
    .stat-label{color:var(--Primary-Dark-Mud)}
    button#new-ticket-btn { background-color: var(--primary-color) !important; color: white !important; border: 1px solid var(--primary-color) !important;}
    #adminSupport tbody tr:hover {background-color: var(--Menu-Back-Ground);}
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
</style>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
@endpush

@php
    use Carbon\Carbon;
    $statusArray = ['Received','In progress','Parked','Waiting for customer','Resolved'];
    $categoryArray = ['Technical Bug','Account Access','Billing & Payments','Product Question','Feature Request','Integration/API','Security','Onboarding Help','Feedback'];
    $priorityArray = ['Highest','High','Medium','Low','Lowest'];
    $userID = Session::get('user_id');
@endphp

@section('content')
<div class="container-fluid batchbase-ticket-container my-4">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <div>
                <h1 class="page-title">Support Tickets</h1>
                <p>An overview of all support requests.</p>
            </div>
            <div class="Export-btn d-flex" style="gap:25px;">
                <div class="stat-item"><div class="stat-value open-count">0</div><div class="stat-label">Open</div></div>
                <div class="stat-item"><div class="stat-value positive close-count">0</div><div class="stat-label">Resolved</div></div>
                <div class="stat-item"><div class="stat-value total-count">0</div><div class="stat-label">Active Total</div></div>
            </div>
        </div>
        <div class="card-body">
            <div class="support-filter-section ">
                <div class=" support-container row g-2 align-items-center pb-5">
                    <div class="col-12 col-lg-3">
                        <input type="text" id="topic_search" placeholder="Search by topic..." class="form-control">
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
                        <button id="new-ticket-btn" class="btn btn-secondary-blue batchbase-ticket-btn-primary w-100">New Ticket</button>
                    </div>
                </div>
            </div>

            <x-shimmer />

            <table class="table responsiveness custom-wrap" id="adminSupport" style="display:none;">
                <thead>
                    <tr>
                        <th class="text-primary-blue">Topic</th>
                        <th class="text-primary-blue">Requester</th>
                        <th class="text-primary-blue">Category</th>
                        <th class="text-primary-blue">Due Date</th>
                        <th class="text-primary-blue">Priority</th>
                        <th class="text-primary-blue">Status</th>
                        <th class="text-primary-blue"></th>
                    </tr>
                </thead>
                <tbody id="tickets-container">
                    @php
                        $openCount = 0;
                        $closeCount = 0;                    
                    @endphp
                    @foreach($tickets as $ticket)
                     @php
                        if($ticket['status'] == "Resolved"){
                            ++$closeCount;
                        }else{
                            ++$openCount;
                        }
                    @endphp
                    <tr class="ticket-edit" data-ticket="{{ $ticket['id'] }}">
                        <td class="text-primary-dark-mud">{{ $ticket['topic'] }}</td>
                        <td class="text-primary-dark-mud">{{($ticket['requester_details'])? $ticket['requester_details']['name']: 'N/A' }}</td>
                        <td class="text-primary-dark-mud">{{ $ticket['category'] }}</td>
                        <td class="text-primary-dark-mud">{{ ($ticket['due_date'])? Carbon::parse($ticket['due_date'])->format('j M Y'): 'N/A' }}</td>
                        <td class="text-primary-dark-mud">{{ $ticket['priority'] }}</td>
                        <td class="text-primary-dark-mud">{{ $ticket['status'] ?? 'Received' }}</td>
                        <td><span class="material-symbols-outlined delete-row-data" data-id="{{ $ticket['id'] }}">delete</span></td>
                    </tr>
                    @endforeach
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
                    </div>
                    <div class="drawer-body p-3">
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="topic">Topic<span class="text-danger">*</span></label>
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
                                    <option>Select Requester</option>
                                    @foreach($members as $member)
                                    <option value="{{$member->user_id}}" @if($member->user_id == $users->id) selected @endif>{{$member->name}}</option>
                                    @endforeach

                                    @foreach($batchbase_admins as $key => $admin)
                                    <option value="{{$key}}" @if($key == $users->id) selected @endif>{{$admin}}</option>
                                    @endforeach
                                </select>
                                <span id="custom-text"><p class="mb-0">Receives updates and replies to the ticket.</p></span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="ccs">CC's</label>
                                <select name="ccs[]" id="ccs" class="form-select fa-basic-multiple" multiple>
                                    @foreach($members as $member)
                                    <option value="{{$member->user_id}}">{{$member->name}}</option>
                                    @endforeach
                                </select>
                                <span id="custom-text"><p class="mb-0">Also notified; can follow the conversation.</p></span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="time_estimated">Time Estimated</label>
                                <input type="text" class="form-control time-input" name="time_estimated" placeholder="HH:MM"maxlength="5" pattern="^([01]\d|2[0-3]):([0-5]\d)$" value=""  oninput="this.value = this.value.replace(/[^0-9:]/g, '')" readonly>    
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="time_spent">Time Spent</label>
                                <input type="text" class="form-control time-input" name="time_spent" placeholder="HH:MM"maxlength="5" pattern="^([01]\d|2[0-3]):([0-5]\d)$"  value="" oninput="this.value = this.value.replace(/[^0-9:]/g, '')" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="pt-3 pb-3">
                                    <label for="support_documents" class="btn btn-outline-secondary">Choose Files</label>
                                    <span id="fileLabel">No file chosen</span>
                                    <input name="support_documents[]" id="support_documents" accept=".png,.jpg,jpeg,.svg,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx" type="file" multiple hidden />
                                </div>
                                <ul class="list-group" id="fileList" style="width: 70%;"></ul>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12" id="ticket_documents">
                            </div>
                        </div>

                        <div class="row mb-3" id="comment_section">
                            <div class="col-12">
                                <div class="comments-section d-flex mt-2" style="display: flex ; align-items: center; gap: 11px;">
                                    <h3>Comments</h3> 
                                    <button type="button" class="btn btn-primary-orange plus-icon" id="addSupportBtn" title="Add Comment"><span class="material-symbols-outlined">add</span></button>
                                </div>

                                <div class="comment-add-section row p-4" style="display:none;">
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
                                                <label for="support_comment_documents" class="btn btn-outline-secondary">Choose Files</label>
                                                <span id="fileLabel">No file chosen</span>
                                                <input name="support_comment_documents[]" id="support_comment_documents" accept=".png,.jpg,jpeg,.svg,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx" type="file" multiple hidden />
                                            </div>
                                            <ul class="list-group" id="CommentfileList" style="width: 70%;"></ul>

                                            <div class="pt-4">
                                                <button type="button" class="btn btn-secondary-white" id="closeModal">Cancel</button>
                                                <button type="button" class="btn btn-secondary-blue" id="saveCommentBtn">Save</button>
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

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    let files = [];
    let comment_files = [];
    let edit_files = [];
    const APP_URL = "{{ env('APP_URL') }}";
    
    $(document).ready(function() {
        $('#requester').select2();
        $('#requester').prop('disabled', true);  // readonly effect

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
                table.columns(5).search(defaultValue, true, false).draw();
            }
        });

        // Optional: trigger with your button
        document.getElementById('new-ticket-btn').addEventListener('click', function () {
            openDrawer();
            $(`#ticket_documents,#comment_section,#comment_details`).css('display','none');
            $(`#ticket_id`).val('');
            $('#saveSupportBtn').html('Save')
            $(`.drawer-header h5`).html(`Create New Ticket`);
        });
        document.getElementById('ticketDrawerBackdrop').addEventListener('click', closeDrawer);
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

    /** Ticket image upload handling */
    $('#support_documents').on('change', function() {
        var selectedFiles = this.files;
        if (selectedFiles.length > 0) {
            for (var i = 0; i < selectedFiles.length; i++) {
                var file = selectedFiles[i];
                files.push(file);

                var listItem = `<li class="list-group-item d-flex justify-content-between align-items-center">
                    ${file.name}
                    <button class="btn btn-danger btn-sm deleteBtn" data-index="${files.length - 1}">Delete</button>
                </li>`;
                $('#fileList').append(listItem);
            }
        }
        updateFileLabel();
        $(this).val('');
    });

    $(document).on('click', '.deleteBtn', function() {
        var index = $(this).data('index');
        files.splice(index, 1);
        $(this).closest('li').remove();
        // Reindex all delete buttons
        $('#fileList .deleteBtn').each(function(i) {
            $(this).data('index', i);
        });
        updateFileLabel();
    });

    function updateFileLabel() {
        const label = files.length === 0 
            ? 'No file chosen' 
            : `${files.length} file${files.length > 1 ? 's' : ''} selected`;
        $('#fileLabel').text(label);
    }

    /** Ticket Comment image upload handling */
    $('#support_comment_documents').on('change', function() {
        var selectedFiles = this.files;
        if (selectedFiles.length > 0) {
            for (var i = 0; i < selectedFiles.length; i++) {
                var file = selectedFiles[i];
                comment_files.push(file);
                var listItem = `<li class="list-group-item d-flex justify-content-between align-items-center">
                    ${file.name}
                    <button class="btn btn-danger btn-sm deleteCommentBtn" data-index="${comment_files.length - 1}">Delete</button>
                </li>`;
                $('#CommentfileList').append(listItem);
            }
        }
        updateCommentLabel();
        $(this).val('');
    });
    
    function updateCommentLabel() {
        const label = comment_files.length === 0 
            ? 'No file chosen' 
            : `${comment_files.length} file${comment_files.length > 1 ? 's' : ''} selected`;
        $('#fileLabel1').text(label);
    }

    $(document).on('click', '.deleteCommentBtn', function() {
        var index = $(this).data('index');
        comment_files.splice(index, 1);
        $(this).closest('li').remove();
        // Reindex all delete buttons
        $('#CommentfileList .deleteCommentBtn').each(function(i) {
            $(this).data('index', i);
        });
        updateCommentLabel();
    });

    /** Ticket edit comment image upload handling */
    $('#support_edit_documents').on('change', function() {
        var selectedFiles = this.files;
        if (selectedFiles.length > 0) {
            for (var i = 0; i < selectedFiles.length; i++) {
                var file = selectedFiles[i];
                edit_files.push(file);

                var listItem = `<li class="list-group-item d-flex justify-content-between align-items-center">
                    ${file.name}
                    <button class="btn btn-danger btn-sm deleteEditBtn" data-index="${edit_files.length - 1}">Delete</button>
                </li>`;
                $('#fileList1').append(listItem);
            }
        }
        updateEditFileLabel();
        $(this).val('');
    });

    $(document).on('click', '.deleteEditBtn', function() {
        var index = $(this).data('index');
        edit_files.splice(index, 1);
        $(this).closest('li').remove();

        // Reindex all delete buttons
        $('#fileList1 .deleteEditBtn').each(function(i) {
            $(this).data('index', i);
        });

        updateEditFileLabel();
    });

    function updateEditFileLabel() {
        const label = edit_files.length === 0 
            ? 'No file chosen' 
            : `${edit_files.length} file${edit_files.length > 1 ? 's' : ''} selected`;
        $('#fileLabel1').text(label);
    }

    // Apply to all elements with class `time-input`
    document.querySelectorAll('.time-input').forEach(function(input) {
        input.addEventListener('input', formatTime);
    });

    function formatTime(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 3) {
            value = value.slice(0, 2) + ':' + value.slice(2, 4);
        }
        e.target.value = value.slice(0, 5);
    }

    $(document).on('submit','#supportForm',function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $('#saveSupportBtn');          
        // Disable submit button to prevent multiple submissions
        $submitButton.prop('disabled', true);
        let ticketID = $(`#ticket_id`).val()
        const url = (ticketID)? "{{ route('update.ticket', ':id') }}".replace(':id', ticketID): "{{ route('save.ticket') }}";
        const method = 'POST';
        const formData = new FormData(this);
        files.forEach((file, index) => {
            formData.append("image_file[]", file); // Append each file to FormData
        });

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
            }
        });
    });

    function custom_search(_this){
        let val = $(_this).val()
        let module = $(_this).data('module')
        let columnCount
        switch (module) {
            case 'category':
                columnCount = 2
                break;
            case 'status':
                columnCount = 5
                break;
            case 'priority':
                columnCount = 4
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
                    table.columns(5).search(defaultValue, true, false).draw(); 
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
        table.columns(0).search(searchValue, true, false).draw();
    });

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
        table.columns(5).search(defaultValue, true, false).draw();
    }

    $(document).on('click','.ticket-edit',function(e){
        if ($(e.target).closest('.delete-row-data').length) {
            return; // do nothing
        }

        let id = $(this).data('ticket');
        const url = "{{ route('edit.ticket', ':id') }}".replace(':id', id);
         $.ajax({
            url:  url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $(`#ticket_id`).val(response.details.ticket.id)
                    let topic = (response.details.ticket.topic)?? '';
                    let description = (response.details.ticket.description)?? '';
                    let category = (response.details.ticket.category)?? '';
                    let priority = (response.details.ticket.priority)?? '';
                    let duedate = (response.details.ticket.due_date)?? '';
                    let requester = (response.details.ticket.requester)?? '';
                    let ccArray = (response.details.ticket.ccs)? JSON.parse(response.details.ticket.ccs):[];
                    let time_estimate = (response.details.ticket.time_estimated)?? '';
                    let time_spent = (response.details.ticket.time_spent)?? '';
                                
                    $('#topic').val(topic);
                    $('#category').val(category).select2();
                    $('#priority').val(priority).select2();
                    $('#status').val(status).select2();
                    $('#due_date').val(duedate);
                    $('input[name="time_estimated"]').val(time_estimate);
                    $('input[name="time_spent"]').val(time_spent);
                    $('#requester').val(requester).select2();
                    $('#ccs').val(ccArray).trigger('change');


                    initQuillEditors();
                    const quill = window.quillInstances['description'];
                    if (quill) {
                        quill.setContents([]); // Clear previous
                        quill.clipboard.dangerouslyPasteHTML(description); // ✅ Set new content
                    } else {
                        console.warn('Quill instance not found for description');
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
                    $('#saveSupportBtn').html('update')
                    $(`.drawer-header h5`).html(`Edit Ticket #${response.details.ticket.ticket_number}`)
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
    });

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
        
        const url = "{{ route('save.comment', ':id') }}".replace(':id', ticketID);  
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
        const url = "{{ route('comment.destroy', ':id') }}".replace(':id', id);
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
        let url = "{{ route('update.comment', ':comment') }}".replace(':comment', id);
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

    // Delete  Handling
    $(document).on('click', '.delete-row-data', function() {
        const id = $(this).data('id');
        const url = "{{ route('support.destroy', ':id') }}".replace(':id', id);
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