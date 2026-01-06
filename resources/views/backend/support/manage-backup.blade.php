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
    .btn-hidden {
        display: none !important;
    }
    /* Custom ColVis dropdown */
    .colvis-dropdown {
        position: absolute;
        background: #fff;
        border: 1px solid #ddd;
        padding: 8px;
        border-radius: 5px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        width: 200px;
    }

    /* Align checkboxes properly */
    .colvis-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px;
        cursor: pointer;
    }

    /* Ensure checkboxes are on the right */
    .colvis-checkbox {
        margin-left: auto;
        transform: scale(1.2); /* Slightly larger checkboxes */
        cursor: pointer;
    }
    table#dtRecordsView1 thead tr th.hide,table#dtRecordsView1 tbody tr td.hide{display: none !important;}
    span#custom-text p { font-size: 11px; color: #808080ab !important; }
    .hidden {display: none;}
    .visible {display: block;}
</style>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
@endpush



@section('content')
<div class="container-fluid labours my-4">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Support Management</h1>
            <input type="hidden" class="selectedCols" id="selectedCols">
            <div class="Export-btn">
                <div class="btn-group click-dropdown">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="Filter">
                        <span class="material-symbols-outlined" id="custom_search">filter_list</span>
                    </button>
                </div>
                <div class="btn-group click-dropdown">
                    <button type="button" class="btn btn-primary-orange plus-icon" title="List">
                        <span class="material-symbols-outlined">inventory</span>
                    </button>
                    <ul class="dropdown-menu">
                        <a class="dropdown-item sort_record" href="javascript:void(0)" data-value="all"><li >All</li></a>
                        <a class="dropdown-item sort_record" href="javascript:void(0)" data-value="1"><li>Resolved</li></a>
                        <a class="dropdown-item sort_record" href="javascript:void(0)" data-value="0"><li>Non Resolved</li></a>
                    </ul>
                    <input type="hidden" id="customFilter" value="0">
                </div>
                <button type="button" class="btn btn-primary-orange plus-icon" id="addSupportBtn" title="Add Ticket">
                    <span class="material-symbols-outlined">add</span>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table responsiveness custom-wrap" id="dtRecordsView1">
                <thead>
                    <tr>
                        <th class="text-primary-blue">Ticket#</th>
                        <th class="text-primary-blue">Summary</th>
                        <th class="text-primary-blue">Description</th>
                        <th class="text-primary-blue">Category</th>
                        <th class="text-primary-blue">Priority</th>
                        <th class="text-primary-blue">Status</th>
                        <th class="text-primary-blue">Requester</th>
                        <th class="text-primary-blue">CC's</th>
                        <th class="text-primary-blue">Created By</th>
                        <th class="text-primary-blue">Created On</th>
                        <th class="text-primary-blue"></th>
                        <th class="text-primary-blue"></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $statusArray = ['Received','In progress','Parked','Waiting for customer','Resolved'];
                    $categoryArray = ['Technical Bug','Account Access','Billing & Payments','Product Question','Feature Request','Integration/API','Security','Onboarding Help','Feedback'];
                    $priorityArray = ['Highest','High','Medium','Low','Lowest'];
                    $memberArray = [];
                    if($members != null){
                        $memberCollection = $members->toArray();
                        $memberArray = array_column($memberCollection, 'name');
                    }
                    @endphp
                    @foreach($tickets as $ticket)
                    <tr>
                        <td class="text-primary-dark-mud">{{ $ticket['ticket_number'] }}</td>
                        <td class="text-primary-dark-mud">{{ $ticket['topic'] }}</td>
                        <td class="text-primary-dark-mud">{{ \Illuminate\Support\Str::limit(strip_tags($ticket['description']), 100)  }}</td>
                        <td class="text-primary-dark-mud">{{ $ticket['category'] }}</td>
                        <td class="text-primary-dark-mud">{{ $ticket['priority'] }}</td>
                        <td class="text-primary-dark-mud">
                            <select name="status" id="status" class="form-control-select" data-ticket="{{$ticket['id']}}" required onchange="status_update(this)">
                                @foreach($statusArray as $status)
                                <option value="{{$status}}" @if($ticket['status'] == $status) selected @endif>{{$status}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-primary-dark-mud">{{($ticket['requester_details'])? $ticket['requester_details']['name']: '' }}</td>
                        <td class="text-primary-dark-mud">{{ ($ticket['cc_list'])?? '' }}</td>
                        <td class="text-primary-dark-mud">{{ $ticket['creator']['name'] ?? '' }}</td>
                        <td class="text-primary-dark-mud">{{ \Carbon\Carbon::parse($ticket['created_at'])->format('F j, Y') }}</td>
                        <td class="text-primary-dark-mud">{{ $ticket['status'] }}</td>
                        <td class="actions-menu-area">
                            <div class="">
                                <div class="dropdown d-flex justify-content-end">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <a href="{{route('view.ticket',['ticket' => $ticket['id']])}}" style="text-decoration: none;">
                                                <span class="dropdown-item text-primary-dark-mud me-2">
                                                    <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                                </span>
                                            </a>
                                        </li>

                                        @if($users->role_id == 4 && $users->id == $ticket['created_by'])
                                            <li>
                                                <span class="dropdown-item text-primary-dark-mud delete-row-data" data-id="{{ $ticket['id'] }}">
                                                    <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                                </span>
                                            </li>
                                        @elseif($users->role_id != 4)
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud delete-row-data" data-id="{{ $ticket['id'] }}">
                                                <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                            </span>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </td>
                        
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Labour Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title text-primary-orange" id="actionModalLabel">Add/Edit Labour</h2>
                </div>
                <form id="supportForm" enctype="multipart/form-data">
                    <input type="hidden" name="ticket_number" id="ticket_number" value="{{$ticket_count}}">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="topic">Summary<span class="text-danger">*</span></label>
                                <input type="text" name="topic" id="topic" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="col-12 quill-editor-wrapper">
                                    <div class="quill-editor-wrapper">
                                        <label class="text-primary-orange" for="description">Description<span class="text-danger">*</span></label>
                                        <div class="quill-editor" data-input="description"></div>
                                        <input type="hidden" name="description" value="">
                                    </div>
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
                                <label class="text-primary-orange" for="requester">Requester</label>
                                <select name="requester" id="requester" class="form-select js-example-basic-single" required>
                                    <option @if(Session::get('role_id') == 1) selected @endif disabled>Select Requester</option>
                                    @foreach($members as $member)
                                    <option value="{{$member->user_id}}" @if($member->user_id == $users->id) selected @endif>{{$member->name}}</option>
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
                                    <option value="{{$member->user_id}}" @if($member->user_id == $users->id) selected @endif>{{$member->name}}</option>
                                    @endforeach
                                </select>
                                <span id="custom-text"><p class="mb-0">Also notified; can follow the conversation.</p></span>
                            </div>
                        </div>
                        
                        <!-- <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="time_estimated">Time Estimated</label>
                                <select name="time_estimated" id="time_estimated" class="form-select js-example-basic-single" @if(Session::get('role_id') != 1) disabled @endif>
                                    <option selected disabled>Select Time Estimated</option>
                                    @for ($i = 0.5; $i <= 8.5; $i += 0.5)
                                    <option value="{{ number_format($i, 1) }}">{{ number_format($i, 1) }}h</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-primary-orange" for="time_spent">Time Spent</label>
                                <select name="time_spent" id="time_spent" class="form-select js-example-basic-single" @if(Session::get('role_id') != 1) disabled @endif>
                                    <option selected disabled>Select Time Spent</option>
                                    @for ($i = 0.5; $i <= 8.5; $i += 0.5)
                                    <option value="{{ number_format($i, 1) }}">{{ number_format($i, 1) }}h</option>
                                    @endfor
                                </select>
                            </div>
                        </div> -->

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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="saveSupportBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    let files = [];

    function updateFileLabel() {
        const label = files.length === 0 
            ? 'No file chosen' 
            : `${files.length} file${files.length > 1 ? 's' : ''} selected`;
        $('#fileLabel').text(label);
    }

    $(document).ready(function() {
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
    });


    $(document).ready(function() {
        var support_category = @json($categoryArray);
        var support_priority = @json($priorityArray);
        var support_status = @json($statusArray);
        var support_requester = @json($memberArray);

        const table = $('#dtRecordsView1').DataTable({
            "order": [],
            responsive: true,
            dom: "<'row mb-4'<'col-md-6 col-sm-6'fB><'col-md-6 col-sm-6 custom-dropdown'l>>" +
                "<'row table-responsiveness'<'col-sm-12'tr>>" +
                "<'row'<'col-md-5'i><'col-md-7'p>>",

            buttons: [
                
                {
                    extend: 'colvis',
                    columns: ':not(:last, :first)',
                    text: '<span class="material-symbols-outlined" style="font-size: 30px; margin-top: -6px;"> view_column </span>',
                    action: function (e, dt, button, config) {
                        // Override default action to prevent default dropdown
                        if ($('.colvis-dropdown').length === 0) {
                            createColVisDropdown(dt);
                        }
                    }
                }
            ],

            columnDefs: [{
                targets: -1,
                className: 'noVis always-visible',
                orderable: false
            },
                {
                    targets: [8,9,10], // Specify columns that should be hidden initially
                    visible: false
                }
        ],

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
                // Move the search box to the left and entries dropdown to the right
                const tableWrapper = $(this).closest('.dataTables_wrapper');
                const lengthDropdown = tableWrapper.find('.dataTables_length');
                const colvisButton = tableWrapper.find('.buttons-colvis');
                colvisButton.insertBefore(lengthDropdown); // Move the colvis button before the length dropdown (right side)
                // Create the new filter dropdown row
                const customFilterRow = `
                        <div class="row mt-4 me-1 hidden" id="customFilterRow">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Category</label>
                            <select name="support_category[]" id="filterCategory" class="form-control select2-tags"  data-module="category" onchange="custom_search(this)" multiple >
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Priority</label>
                            <select name="support_priority[]" id="filterPriority" class="form-control select2-tags"  data-module="priority" onchange="custom_search(this)" multiple >
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Status</label>
                            <select name="support_status[]" id="filterStatus" class="form-control select2-tags"  data-module="status" onchange="custom_search(this)" multiple >
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Requester</label>
                            <select name="support_requester[]" id="filterRequester" class="form-control select2-tags"  data-module="requester" onchange="custom_search(this)" multiple >
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <div class="Export-btn mt-2 text-end">
                                <div class="btn-group click-dropdown">
                                    <button type="button" class="btn btn-primary-orange plus-icon" title="Reset" id="reset_filters">
                                        <span class="material-symbols-outlined">refresh</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                tableWrapper.find('.dataTables_length').parent().after(customFilterRow);

                // Populate product status

                support_category.forEach(status => {
                    $('#filterCategory').append(`<option value="${status}">${status}</option>`);
                });

                support_priority.forEach(status => {
                    $('#filterPriority').append(`<option value="${status}">${status}</option>`);
                });

                support_status.forEach(status => {
                    $('#filterStatus').append(`<option value="${status}">${status}</option>`);
                });

                support_requester.forEach(status => {
                    $('#filterRequester').append(`<option value="${status}">${status}</option>`);
                });

                $('.js-example-basic-single, .select2-tags').select2({
                    width: '100%'
                });
              
                const searchBox = tableWrapper.find('.dataTables_filter');
                searchBox.css({
                    'float': 'left',
                    'margin-top': '0',
                    'margin-right': '20px'
                });
                // lengthDropdown.css('float', 'right');
                $('.custom-dropdown').css({
                    'display': 'flex',
                    'justify-content': 'flex-end',
                    'gap': '15px',
                    'align-items': 'center'
                });

                var defaultArray = ['Received', 'In progress', 'Parked', 'Waiting for customer'];

                var table = $('#dtRecordsView1').dataTable().api();
                const regex = defaultArray.join('|');
                table.columns(10).search(regex, true, false).draw();
            }
        });
    });
    function createColVisDropdown(dt) {
        let dropdownHtml = '<div class="colvis-dropdown">';  
        let initiallyCheckedColumns = [0,1,2,3,4,5,6,7]; // Define which columns should be checked by default
        dt.columns().every(function(idx) {
            let column = this;
            let columnTitle = column.header().textContent;
            if (columnTitle !== "") {
                dropdownHtml += `<label class="colvis-item">
                            <span>${columnTitle}</span>
                            <input type="checkbox" class="colvis-checkbox form-check-input" data-column="${idx}" 
                                ${initiallyCheckedColumns.includes(idx) ? 'checked' : ''}>
                        </label>`;
            }
        });
        dropdownHtml += '</div>';
        // Remove any existing dropdown before adding a new one
        $('.colvis-dropdown').remove();
        $('body').append(dropdownHtml);

        // Position the dropdown near the button
        let buttonOffset = $('.buttons-colvis').offset();
        $('.colvis-dropdown').css({
            position: 'absolute',
            top: buttonOffset.top + $('.buttons-colvis').outerHeight(),
            left: buttonOffset.left,
            background: '#fff',
            border: '1px solid #ddd',
            padding: '8px',
            borderRadius: '5px',
            boxShadow: '0px 4px 6px rgba(0, 0, 0, 0.1)',
            zIndex: 999
        });

        // Handle checkbox change
        $('.colvis-checkbox').on('change', function () {
            let columnIdx = $(this).data('column');
            let column = dt.column(columnIdx);
            column.visible($(this).prop('checked'));
        });

        // Close dropdown on outside click
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.colvis-dropdown, .buttons-colvis').length) {
                $('.colvis-dropdown').remove();
            }
        });
    }

    // Add Labour Modal Handling
    $(document).on('click','#addSupportBtn', function() {
        // Reset the form
        $('#supportForm')[0].reset();
        $('#actionModalLabel').text('Add Ticket');
        $('#saveSupportBtn').text('Save Ticket');
        $('.js-example-basic-single, .fa-basic-multiple').select2({
                dropdownParent: $('#actionModal'),
                width: '100%'
            });
        // Show the modal
        $('#actionModal').modal('show');
        initQuillEditors();
    });

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
        });
    }


    $(document).on('submit','#supportForm',function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $('#saveSupportBtn');
        // Disable submit button to prevent multiple submissions
        $submitButton.prop('disabled', true);
        const labourId = $('#labour_primary').val();
        const url = "{{ route('save.ticket') }}";
        const method = 'POST';
        const formData = new FormData(this);
        files.forEach((file, index) => {
            formData.append("image_file[]", file); // Append each file to FormData
        });
        
        $('.quill-editor').each(function() {
            const quill = Quill.find(this);
            const $input = $(`input[name="${$(this).data('input')}"]`);
            $input.val(quill.root.innerHTML);
        });

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            complete: function() {
                // Re-enable submit button
                $submitButton.prop('disabled', false);
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

    function status_update(_this) {

        let id = $(_this).data('ticket')       
        const url = "{{ route('update.ticket.status', ':id') }}".replace(':id', id);
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: $(_this).val()
            },
            success: function(response) {
                if (response.success) {
                     Swal.fire({
                        icon: 'success',
                        title: 'Status Updated!',
                        text: response.message,
                    });
                    // Swal.fire({
                    //     icon: 'success',
                    //     title: 'Status Updated!',
                    //     text: response.message,
                    //     timer: 2000
                    // }).then(() => {
                    //     location.reload();
                    // });
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

    $(document).on('click','#reset_filters',function(){
        $('#filterCategory,#filterPriority,#filterStatus,#filterRequester').val([]).trigger('change');
    })

    $(document).on('click', '#custom_search', function () {
        const $searchBtn = $(this);
        const $customFilter = $('#customFilterRow');

        $searchBtn.toggleClass('custom-search-style');
        $customFilter.toggleClass('hidden visible');
    });

    function custom_search(_this){
        let val = $(_this).val()
        let module = $(_this).data('module')
        let columnCount
        switch (module) {
            case 'category':
                columnCount = 3
                break;

            case 'status':
                columnCount = 10
                break;

            case 'priority':
                columnCount = 4
                break;

            case 'requester':
                columnCount = 6
                break;
        }
        var table = $('#dtRecordsView1').dataTable().api();
        console.log(val);
        
        if (val && val.length > 0) {
            if (Array.isArray(val)) {
                // Join values with pipe (|) for regex OR match, no ^$
                const regex = val.join('|');
                table.columns(columnCount).search(regex, true, false).draw();
            } else {
                // Use contains match for single value
                table.columns(columnCount).search(val, true, false).draw();
            }
        } else {
            const defaultValue = `Received|In progress|Parked|Waiting for customer`;
            table.columns(10).search(defaultValue, true, false).draw();
        }

    }

    let modal = document.getElementById('actionModal');
    let safeClose = false;
    modal.addEventListener('click', function(event) {    
        if (event.target === modal) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to close the modal?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, close it',
                cancelButtonText: 'No, stay'
            }).then((result) => {
                if (result.isConfirmed) {
                    let bootstrapModal = bootstrap.Modal.getInstance(modal);
                    bootstrapModal.hide();
                }
            });
        }
    });

    $(document).on('click','.sort_record',function(){
        let search_val = $(this).data('value')
        $('#customFilter').val(search_val)
        var table = $('#dtRecordsView1').dataTable().api();
        const defaultValue = `Received|In progress|Parked|Waiting for customer`;
        if(search_val == 0){
            table.columns(10).search(defaultValue, true, false).draw();
        }else if(search_val == 1){
            table.columns(10).search('Resolved', true, false).draw();
        }else if(search_val == "all"){
            table.columns().search('').draw(); 
        }
    })
</script>
@endpush