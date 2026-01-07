@extends('backend.master', [
'pageTitle' => 'Support Ticket',
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
['label' => 'Support Ticket']
],
])

@push('styles')
<style>
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
span#custom-text p { font-size: 11px; color: #808080ab !important; }
</style>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
@endpush
@section('content')
@php
use App\Models\{image_library}; 
$user_roleID = Session::get('role_id');
@endphp
<div class="container-fluid labours my-4">
    <div class="">
        <div class="card-header">
            <div class="top_title_wrapper d-flex justify-content-between">
                <div>
                    <h4>Ticket : #{{$ticket->ticket_number}}</h4>
                    <h3>{{$ticket->topic}} <span class="material-symbols-outlined summary-edit" data-content="{{ old('topic', $ticket->topic) }}" data-type="topic" style="font-size: 18px;">edit</span></h3>
                    <input type="hidden" name="topic" id="topic" value="{{ old('topic', $ticket->topic) }}">
                    <h4>{{ $ticket->creator->name }} on {{ $ticket->created_at->format('F j, Y \a\t h:i A') }}</h4>
                </div>  
                <div class="button-row">
                    <a href="{{ route('admin.support.manage') }}" class="btn btn-secondary-white me-3">Back</a>
                    <button type="button" class="btn btn-primary-orange plus-icon delete-row-data" title="Delete Ticket" data-id="{{$ticket->id}}"><span class="material-symbols-outlined">delete</span></button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="ticket-details-section">
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label class="text-primary-orange" for="description">Description <span class="material-symbols-outlined common-edit" style="font-size: 18px;" data-content="{{ old('description', $ticket->description) }}" data-type="description">edit</span> </label>
                        <p>{!! old('description', $ticket->description) !!}</p>
                        <input type="hidden" name="description" id="description" value="{{ old('description', $ticket->description) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="text-primary-orange" for="category">Category</label>
                        <select name="category" id="category" class="form-control js-example-basic-single">
                            <option value="Technical Bug">Technical Bug</option>
                            <option value="Account Access">Account Access</option>
                            <option value="Billing & Payments">Billing & Payments</option>
                            <option value="Product Question">Product Question</option>
                            <option value="Feature Request">Feature Request</option>
                            <option value="Integration/API">Integration/API</option>
                            <option value="Security">Security</option>
                            <option value="Onboarding Help">Onboarding Help</option>
                            <option value="Feedback">Feedback</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="text-primary-orange" for="status">Status</label>
                        <select name="status" id="status" class="form-control js-example-basic-single" onchange="update_ticket(this)">
                            <option value="Received">Received</option>
                            <option value="In progress">In progress</option>
                            <option value="Parked">Parked</option>
                            <option value="Waiting for customer">Waiting for customer</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="text-primary-orange" for="priority">Priority</label>
                        <select name="priority" id="priority" class="form-control js-example-basic-single">
                            <option value="" disabled></option>
                            <option value="Highest">Highest</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                            <option value="Lowest">Lowest</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="text-primary-orange" for="due_date">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', $ticket->due_date) }}" @if(!in_array($user_roleID,[1])) disabled @endif>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="text-primary-orange" for="status">Requester</label>
                        <select name="requester" id="requester" class="form-select js-example-basic-single" onchange="update_ticket(this)">
                            <option value="" disabled>Select Requester</option>
                            @foreach($members as $member)
                            <option value="{{$member->user_id}}">{{$member->name}}</option>
                            @endforeach
                        </select>
                        <span id="custom-text"><p class="mb-0">Receives updates and replies to the ticket.</p></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="text-primary-orange" for="ccs">CC's</label>
                        <select name="ccs[]" id="ccs" class="form-select fa-basic-multiple" multiple onchange="update_ticket(this)">
                            @foreach($members as $member)
                            <option value="{{$member->user_id}}">{{$member->name}}</option>
                            @endforeach
                        </select>
                        <span id="custom-text"><p class="mb-0">Also notified; can follow the conversation.</p></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="text-primary-orange" for="assignee">Assignee</label>
                        <select name="assignee" id="assignee" class="form-select js-example-basic-single" @if(!in_array($user_roleID,[1])) disabled @endif>
                            <option value="" disabled>Select Assignee</option>
                            @foreach($batchbase_admins as $admin)
                            <option value="{{$admin->id}}">{{$admin->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="text-primary-orange" for="time_estimated">Time Estimated</label>
                        <input type="text" class="form-control time-input" name="time_estimated" id="time_estimated" placeholder="HH:MM"maxlength="5" pattern="^([01]\d|2[0-3]):([0-5]\d)$" value="{{ old('time_estimated', $ticket->time_estimated) }}"  oninput="this.value = this.value.replace(/[^0-9:]/g, '')">    
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="text-primary-orange" for="time_spent">Time Spent</label>
                        <input type="text" class="form-control time-input" name="time_spent" id="time_spent" placeholder="HH:MM"maxlength="5" pattern="^([01]\d|2[0-3]):([0-5]\d)$"  value="{{ old('time_spent', $ticket->time_spent) }}" oninput="this.value = this.value.replace(/[^0-9:]/g, '')">
                    </div>
                </div>



                @if(sizeof($images) > 0)
                <h3 class="mt-5">Support Images</h3>
                <div class="ticket-images row ms-2">
                    @foreach($images as $image) 
                            @if(in_array($image['file_format'],['png','jpg','jpeg','svg']))
                            <div class="col-lg-2 ticket-images-wrap">
                                <div class="overlay"></div>
                                <img src="{{env('APP_URL')}}/{{$image['folder_path']}}/{{$image['image_name']}}" width="100%">
                                <div class="del-wrap">
                                    <a class="action" href="{{env('APP_URL')}}/{{$image['folder_path']}}/{{$image['image_name']}}" target="_blank"><span class="material-symbols-outlined">visibility</span></a>
                                    <a class="action" href="{{env('APP_URL')}}/{{$image['folder_path']}}/{{$image['image_name']}}" download><span class="material-symbols-outlined">download</span></a>
                                </div>
                            </div>
                            @elseif(in_array($image['file_format'],['doc','docx','xls','xlsx','csv','txt','ppt','pptx','pdf']))
                            <div style="display: flex ; gap: 5px;">
                                <p>{{$image['image_name']}}</p>
                                <div class="del-wrap">
                                    <a class="action" href="{{env('APP_URL')}}/{{$image['folder_path']}}/{{$image['image_name']}}" download><span class="material-symbols-outlined">download</span></a>
                                </div>
                            </div>
                            @endif
                    @endforeach
                </div>
                @endif
            </div>
            
            <div class="comments-section d-flex mt-5" style="display: flex ; align-items: center; gap: 11px;">
                <h3>Comments</h3> 
                <button type="button" class="btn btn-primary-orange plus-icon" id="addSupportBtn" title="Add Comment"><span class="material-symbols-outlined">add</span></button>
            </div>

            <div class="comment-add-section row p-4" style="display:none;">
                <div class="col-md-6"> 
                    <h2 class="text-primary-orange">Add Comment</h2>
                    <form id="CommentAddForm" enctype="multipart/form-data">
                    @csrf
                        <input type="hidden" name="ticket_id" id="ticket_id" value="{{$ticket->id}}">
                        <div class="col-md-12 form-group">
                            <label class="text-primary-orange" for="add_comment">Comment<span class="text-danger">*</span></label>
                            <div class="quill-editor-wrapper">
                                <div class="quill-editor" data-input="add_comment"></div>
                                <input type="hidden" name="add_comment" value="">
                            </div>
                        </div>
                        <div class="pt-3 pb-3">
                            <label for="support_documents" class="btn btn-outline-secondary">Choose Files</label>
                            <span id="fileLabel">No file chosen</span>
                            <input name="support_documents[]" id="support_documents" accept=".png,.jpg,jpeg,.svg,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx" type="file" multiple hidden />
                        </div>
                        <ul class="list-group" id="fileList" style="width: 70%;"></ul>

                        <div class="pt-4">
                            <button type="button" class="btn btn-secondary-white" id="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-secondary-blue" id="saveCommentBtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            @if($ticket->comments->isNotEmpty())
                <div class="comment-list-section mt-4">
                @foreach($ticket->comments as $comment)
                    <div class="row">
                        <div class="comment-author-section mb-2">
                            <span class="material-symbols-outlined">person</span> {{$comment->creator->name}}
                            <span class="material-symbols-outlined">calendar_clock</span> {{ $comment->created_at->format('F j, Y \a\t h:i A') }}
                        </div>
                        <div class="comment-body-section">
                            <input type="hidden" id="commID" value="{{$comment->id}}">
                            <input type="hidden" name="edit_comment_preview" value="{{$comment->description}}">
                            <p>{!! $comment->description !!}</p> 
                            @if($comment->comment_image > 0)
                                @php
                                    $comment_images = image_library::where('module_id',$comment->id)->where('module','support_comment')->get()->toArray();   
                                    $imageFiles = [];
                                    $documentFiles = [];
                                    foreach ($comment_images as $file) {
                                        $extension = strtolower(pathinfo($file['image_name'], PATHINFO_EXTENSION));
                                        if (in_array($extension, ['png','jpg','jpeg','svg'])) {
                                            $imageFiles[] = $file;
                                        } elseif (in_array($extension, ['doc','docx','xls','xlsx','csv','txt','ppt','pptx','pdf'])) {
                                            $documentFiles[] = $file;
                                        }
                                    }
                                @endphp
                                @if(count($imageFiles) > 0)
                                    <div class="Image_section">
                                        <strong> Images</strong>
                                        @foreach($imageFiles as $image) 
                                            @php $i = $loop->index + 1; @endphp
                                            <a class="action" href="{{env('APP_URL')}}/{{$image['folder_path']}}/{{$image['image_name']}}" target="_blank">{{$i}}.{{$image['image_name']}}</a>@if (!$loop->last), @endif
                                        @endforeach
                                    </div>
                                    
                                @endif
                                @if(count($documentFiles) > 0)
                                    <div class="Image_section">
                                        <strong> Documents</strong>
                                        @foreach($documentFiles as $doc)
                                            @php $i = $loop->index + 1; @endphp 
                                            <a class="action" href="{{env('APP_URL')}}/{{$doc['folder_path']}}/{{$doc['image_name']}}" download>{{$i}}.{{$doc['image_name']}}</a>@if (!$loop->last), @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="comment-action-section">
                            <span class="material-symbols-outlined comment-edit">edit</span>
                            <span class="material-symbols-outlined comment-delete" data-id="{{$comment->id}}" >delete</span>
                        </div>
                    </div>
                    <hr>
                @endforeach
                </div>
            @else
                <p>No comments available.</p>
            @endif

            

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
                            <input type="hidden" name="ticket_id" id="ticket_id" value="{{$ticket->id}}">
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
                        <button type="submit" class="btn btn-secondary-blue" id="saveSupportBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DescModal" tabindex="-1" role="dialog" aria-labelledby="descModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title text-primary-orange" id="descModalLabel">Update Description</h2>
                </div>
                <form id="CommonForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="col-md-12 form-group">
                            <label class="text-primary-orange" for="edit_description">Description<span class="text-danger">*</span></label>
                            <div class="quill-editor-wrapper">
                                <div class="quill-editor" data-input="edit_description"></div>
                                <input type="hidden" name="edit_description" value="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-secondary-blue" id="saveDescBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="TopicModal" tabindex="-1" role="dialog" aria-labelledby="TopicModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title text-primary-orange" id="TopicModalLabel">Update Summary</h2>
                </div>
                <form id="TopicForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="col-md-12 form-group">
                            <label class="text-primary-orange" for="edit_description">Summary<span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="summary" id="summary" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-secondary-blue" id="saveSummaryBtn">Save</button>
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
    let edit_files = [];

    function updateFileLabel() {
        const label = files.length === 0 
            ? 'No file chosen' 
            : `${files.length} file${files.length > 1 ? 's' : ''} selected`;
        $('#fileLabel').text(label);
    }

    function updateEditFileLabel() {
        const label = edit_files.length === 0 
            ? 'No file chosen' 
            : `${edit_files.length} file${edit_files.length > 1 ? 's' : ''} selected`;
        $('#fileLabel1').text(label);
    }

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


    $(window).on('load',function(){
        let status = "{{ $ticket->status }}"
        let category = "{{$ticket->category}}"
        let priority = ("{{ $ticket->priority }}")?? ""
        let requester = ("{{ $ticket->requester }}")?? ""
        let assignee = ("{{ $ticket->assignee }}")?? ""
        var ccs = @json($ticket->ccs);
        var array = JSON.parse(ccs); // Safely parse JSON
        $('#ccs').val(array).trigger('change');
        $('#status').val(status)
        $('#category').val(category)
        $('#priority').val(priority)
        $('#requester').val(requester)
        $('#assignee').val(assignee)
        $('.js-example-basic-single, .fa-basic-multiple').select2({
            width: '100%'
        });
    })
        
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
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                window.location.href="{{route('admin.support.manage')}}"
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

    
    // Add Labour Modal Handling
    $(document).on('click','#closeModal',function(){
        $('.comment-add-section').css('display','none')
    });
    $(document).on('click','#addSupportBtn', function() {
        $('.comment-add-section').css('display','block')
        initQuillEditors();
    });

    $(document).on('submit','#CommentAddForm',function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $('#saveCommentBtn');
        // Disable submit button to prevent multiple submissions
        $submitButton.prop('disabled', true);
        let url = "{{ route('admin.save.comment',['ticket' => $ticket->id]) }}";     
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
        formData.append('description', $('input[name="add_comment"]').val());
        common_ajax(url,method,formData,$submitButton);
    });

    window.quillInstances = window.quillInstances || {};
    function initQuillEditors() {
        const toolbarOptions = [
            ['bold', 'italic', 'underline'],
            [{'list': 'ordered'}, {'list': 'bullet'}],
            ['clean']
        ];

        $('.quill-editor').each(function () {
            const $editor = $(this);
            if ($editor.data('quill-initialized')) {
                return;
            }
            const inputName = $editor.data('input');
            const $input = $(`input[name="${inputName}"]`);
            const quill = new Quill($editor[0], {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions
                }
            });
            if ($input.val()) {
                quill.clipboard.dangerouslyPasteHTML($input.val());
            }
            quill.on('text-change', () => {
                $input.val(quill.root.innerHTML);
            });
            $editor.data('quill-initialized', true);
            window.quillInstances[inputName] = quill;
        });
    }


    $(document).on('submit','#supportCommentForm',function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $('#saveSupportBtn');
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
        common_ajax(url,method,formData,$submitButton);
    });


    function common_ajax(url,method,formData,$submitButton){
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
        return;
    }

    $(document).on('click','.comment-edit', function() {
        let desc = $(this).parent().parent().find('input[name="edit_comment_preview"]').val()
            
        let comID = $(this).parent().parent().find('#commID').val()
        // Update hidden inputs
        // $('form#supportCommentForm input[name="edit_comment"]').val("<p>This is test comment...</p>")
        $('form#supportCommentForm #comment_id').val(comID);

        // Update modal labels  
        $('#actionModalLabel').text('Update Comment');
        $('#saveSupportBtn').text('Update Comment');

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


    function update_ticket(_this) {
        // send_update_ajax();
    }

    $(document).on('click','.common-edit', function() {
        let content = $(this).data('content')  
        // Show the modal
        $('#DescModal').modal('show');
        setTimeout(() => {
            initQuillEditors(); // safe due to initialized check
            const quill = window.quillInstances['edit_description'];
            if (quill) {
                quill.setContents([]); // Clear previous
                quill.clipboard.dangerouslyPasteHTML(content); // ✅ Set new content
            } else {
                console.warn('Quill instance not found for edit_description');
            }
        }, 300);
    });

    $(document).on('click','.summary-edit', function() {
        let content = $(this).data('content')
        $('#summary').val(content)
        // Show the modal
        $('#TopicModal').modal('show');
    });

    $(document).on('click','#saveSummaryBtn',function(){
        let summary = $('#summary').val()
        $('#topic').val(summary)
        send_update_ajax();
    })
    
    $(document).on('click','#saveDescBtn',function(){
        let desc = $('input[name="edit_description"]').val();
        $('#description').val(desc);
        $('#DescModal').modal('hide');  
        send_update_ajax();
    })

    $(document).on('change','#due_date,#assignee,#priority,#category,#time_spent,#time_estimated',function(){
        send_update_ajax();
    })

    $(document).on('focusout','#time_spent,#time_estimated',function(){
        send_update_ajax(); 
    })

    function send_update_ajax(){
       $.ajax({
            url: "{{ route('admin.update.ticket', ['ticket' => $ticket->id]) }}", // The route URL
            type: "POST",
            data: {
                    _token: '{{ csrf_token() }}',
                    status: $('#status').val(),
                    priority: $('#priority').val(),
                    due_date: $('#due_date').val(),
                    category: $('#category').val(),
                    assignee: $('#assignee').val(),
                    description: $('#description').val(),
                    topic: $('#topic').val(),
                    time_estimated: $('#time_estimated').val(),
                    time_spent: $('#time_spent').val(),
                },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#4CAF50'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: response.message,
                        confirmButtonColor: '#F44336'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred: ' + xhr.responseText,
                    confirmButtonColor: '#F44336'
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
    

    let modal = document.getElementById('actionModal');
    let modal1 = document.getElementById('DescModal');
    let modal2 = document.getElementById('TopicModal');

    function attachBackdropConfirmation(modalElement) {
        modalElement.addEventListener('click', function (event) {
            if (event.target === modalElement) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to close the modal?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, close it',
                    cancelButtonText: 'No, stay'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let bootstrapModal = bootstrap.Modal.getInstance(modalElement);
                        bootstrapModal.hide();
                    }
                });
            }
        });
    }

    // Attach the confirmation to each modal
    attachBackdropConfirmation(modal);
    attachBackdropConfirmation(modal1);
    attachBackdropConfirmation(modal2);

  
    
    </script>
@endpush