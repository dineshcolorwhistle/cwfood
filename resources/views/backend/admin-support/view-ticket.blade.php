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

 
        .ticket-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #fff;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .description-area {
            white-space: pre-line;
            line-height: 1.6;   
        }
        .description-area p span, .description-area p strong{background:none !important;}
        .label-title {
            font-weight: 600;
            color: #555;
        }
        .value-text {
            color: #212529;
        }
    
         /* ===== Comment UI Styling ===== */
    .comment-card {
        border-left: 4px solid #0d6efd;
        border-radius: 6px;
        background: #fff;
    }

    .comment-header {
        display: flex;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .comment-avatar {
        width: 40px;
        height: 40px;
        background: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-right: 12px;
    }

    .comment-meta {
        font-size: 13px;
        color: #6c757d;
    }

    .comment-actions span {
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: 0.2s;
    }

    .comment-actions span:hover {
        background: #f1f1f1;
    }

    .comment-image-preview img {
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .comment-doc-item {
        padding: 4px 0;
    }

    .comment-doc-item span {
        font-size: 20px;
        vertical-align: middle;
    }

    
</style>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
@endpush
@section('content')
@php
use App\Models\{image_library}; 
$user_roleID = Session::get('role_id');
@endphp
<div class="container-fluid labours my-4">
    <!-- Sticky Header -->

    <div class="card-header">
        <div class="top_title_wrapper d-flex justify-content-between">
            <div>
                <h4>Ticket : #{{$ticket->ticket_number}}</h4>
                <h3>{{$ticket->topic}}</h3>
                <input type="hidden" name="topic" id="topic" value="{{ old('topic', $ticket->topic) }}">
                <h4>{{ $ticket->creator->name }} on {{ $ticket->created_at->format('F j, Y \a\t h:i A') }}</h4>
            </div>  
            <div class="button-row">
                <a href="{{ route('admin.support.manage') }}" class="btn btn-secondary-white me-3">Back</a>
                <button type="button" class="btn btn-primary-orange plus-icon delete-row-data" title="Delete Ticket" data-id="{{$ticket->id}}"><span class="material-symbols-outlined">delete</span></button>
            </div>
        </div>
    </div>

    <div class="row mt-4">

        <!-- Left Section -->
        <div class="col-lg-8">

            <!-- Description -->
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Description</strong>
                </div>
                <div class="card-body description-area">
                    <p>{!! old('description', $ticket->description) !!}</p>
                </div>
            </div>

            <!-- Comments -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Comments</strong>
                    <button class="btn btn-sm btn-primary" id="addSupportBtn">Add Comment +</button>
                </div>

                <div class="card-body">
                    @if($ticket->comments->isNotEmpty())
                        <div class="comment-list-section mt-4">
                            @foreach($ticket->comments as $comment)
                                <div class="card shadow-sm mb-4 comment-card">

                                    <div class="card-body">

                                        <!-- ===== Header: Avatar + Name + Timestamp + Actions ===== -->
                                        <div class="comment-header">
                                            <div class="comment-avatar">
                                                <span class="material-symbols-outlined text-secondary">person</span>
                                            </div>

                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $comment->creator->name }}</div>
                                                <div class="comment-meta">
                                                    {{ $comment->created_at->format('F j, Y \a\t h:i A') }}
                                                </div>
                                            </div>

                                            <div class="comment-actions">
                                                <span class="material-symbols-outlined text-primary me-2 comment-edit" data-desc="{{$comment->description ?? ''}}" data-id="{{ $comment->id }}">edit</span>
                                                <span class="material-symbols-outlined text-danger comment-delete" data-id="{{ $comment->id }}">delete</span>
                                            </div>
                                        </div>


                                        <!-- ===== Comment Text ===== -->
                                        <div class="mt-2 mb-3">
                                            <p class="mb-0">{!! $comment->description !!}</p>
                                        </div>


                                        <!-- ===== Attachments Handling ===== -->
                                        @if($comment->comment_image > 0)
                                            @php
                                                $attachments = image_library::where('module_id', $comment->id)
                                                    ->where('module', 'support_comment')
                                                    ->get()
                                                    ->toArray();

                                                $images = [];
                                                $docs = [];

                                                foreach ($attachments as $file) {
                                                    $ext = strtolower(pathinfo($file['image_name'], PATHINFO_EXTENSION));

                                                    if (in_array($ext, ['png','jpg','jpeg','svg','gif','webp'])) {
                                                        $images[] = $file;
                                                    } else {
                                                        $docs[] = $file;
                                                    }
                                                }
                                            @endphp


                                            <!-- ===== Image Preview Section ===== -->
                                            @if(count($images) > 0)
                                                <div class="mb-3">
                                                    <strong class="d-block mb-2">Images</strong>

                                                    <div class="d-flex flex-wrap gap-2 comment-image-preview">
                                                        @foreach($images as $img)
                                                            <a href="{{ env('APP_URL') }}/{{ $img['folder_path'] }}/{{ $img['image_name'] }}" target="_blank">
                                                                <img src="{{ env('APP_URL') }}/{{ $img['folder_path'] }}/{{ $img['image_name'] }}">
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif


                                            <!-- ===== Document Links Section ===== -->
                                            @if(count($docs) > 0)
                                                <div>
                                                    <strong class="d-block mb-2">Documents</strong>

                                                    @foreach($docs as $doc)
                                                        <div class="comment-doc-item">
                                                            <a href="{{ env('APP_URL') }}/{{ $doc['folder_path'] }}/{{ $doc['image_name'] }}" 
                                                            target="_blank"
                                                            class="text-decoration-none">
                                                                <span class="material-symbols-outlined text-primary">description</span>
                                                                {{ $doc['image_name'] }}
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                        @endif

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No comments available.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Section (View Only Info) -->
        <div class="col-lg-4">

            <!-- Ticket Info -->
            <div class="card mb-4">
                <div class="card-header"><strong>Ticket Info</strong></div>
                <div class="card-body">

                    <div class="mb-3">
                        <div class="label-title">Category</div>
                        <div class="value-text">{{$ticket->category}}</div>
                    </div>

                    <div class="mb-3">
                        <div class="label-title">Status</div>
                        <div class="value-text">{{ $ticket->status }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="label-title">Priority</div>
                        <div class="value-text">{{ $ticket->priority }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="label-title">Due Date</div>
                        <div class="value-text">{{ $ticket->due_date }}</div>
                    </div>

                </div>
            </div>

            <!-- Assignment -->
            <div class="card mb-4">
                <div class="card-header"><strong>Assignment</strong></div>
                <div class="card-body">

                    <div class="mb-3">
                        <div class="label-title">Requester</div>   
                        <div class="value-text">{{ $ticket->RequesterDetails->name ?? '' }}</div>
                    </div>  

                    <div class="mb-3">
                        <div class="label-title">Assignee</div>
                        <div class="value-text">{{ $ticket->AssigneeDetails->name ?? '' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="label-title">CC's</div>
                        <div class="value-text">{{ $ccsName }}</div>
                    </div>
                </div>
            </div>

            <!-- Time Tracking -->
            <div class="card mb-4">
                <div class="card-header"><strong>Time Tracking</strong></div>
                <div class="card-body">

                    <div class="mb-3">
                        <div class="label-title">Time Estimated</div>
                        <div class="value-text">{{ $ticket->time_estimated ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="label-title">Time Spent</div>
                        <div class="value-text">{{ $ticket->time_spent ?? '-' }}</div>
                    </div>

                </div>
            </div>

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
                            <input type="hidden" name="ticket_id" id="ticket_id" value="{{$ticket->id}}">
                            <input type="hidden" name="comment_id" id="comment_id" value="">
                            <div class="col-md-12 form-group">
                                <label class="text-primary-orange" for="edit_comment">Comment<span class="text-danger">*</span></label>
                                <div class="quill-editor-wrapper">
                                    <div class="quill-editor" data-input="edit_comment"></div>
                                    <input type="hidden" name="edit_comment" value="<p>This is test comment...</p>">
                                </div>
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

     <div class="modal fade" id="DescModal" tabindex="-1" role="dialog" aria-labelledby="descModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title text-primary-orange" id="descModalLabel">Add comment</h2>
                </div>
                <form id="CommentAddForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="col-md-12 form-group">
                            <label class="text-primary-orange" for="add_comment">Comment<span class="text-danger">*</span></label>
                            <div class="quill-editor-wrapper">
                                <div class="quill-editor" data-input="add_comment"></div>
                                <input type="hidden" name="add_comment" value="">
                            </div>
                        </div>

                        <div class="col-md-12 dropzone" id="dropzone">
                            <span class="material-symbols-outlined upload-icon">upload</span>
                            <p class="mt-1">Drag & drop files here or <span class="uploan-span">click to upload</span></p>
                            <input type="file" id="fileInput" accept=".png,.jpg,jpeg,.svg,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx" hidden>
                            <span class="mt-1">Accepted file formats: "png,jpg,jpeg,svg,pdf,doc,docx,xls,xlsx,csv,txt,ppt,pptx"</span>
                        </div>
                        <ul class="list-group mt-2" id="fileList" style="width: 100%;"></ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="saveCommentBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="{{ asset('assets') }}/js/default-dropzone.js"></script>

<script>




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
        $('#DescModal').modal('show')
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

        fileBuckets[0].forEach((item, index) => {
            formData.append("image_file[]", item.file); // Append each file to FormData
        });


        $('.quill-editor').each(function() {
            const quill = Quill.find(this);
            const $input = $(`input[name="${$(this).data('input')}"]`);
            $input.val(quill.root.innerHTML);
        });
        formData.append('description', $('input[name="add_comment"]').val());
        formData.append('ticket_id', "{{$ticket->id}}");
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
        let desc = $(this).data('desc');
            
        let comID = $(this).data('id')
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




    // $(document).on('click','.common-edit', function() {
    //     let content = $(this).data('content')  
    //     // Show the modal
    //     $('#DescModal').modal('show');
    //     setTimeout(() => {
    //         initQuillEditors(); // safe due to initialized check
    //         const quill = window.quillInstances['edit_description'];
    //         if (quill) {
    //             quill.setContents([]); // Clear previous
    //             quill.clipboard.dangerouslyPasteHTML(content); // ✅ Set new content
    //         } else {
    //             console.warn('Quill instance not found for edit_description');
    //         }
    //     }, 300);
    // });

    // $(document).on('click','.summary-edit', function() {
    //     let content = $(this).data('content')
    //     $('#summary').val(content)
    //     // Show the modal
    //     $('#TopicModal').modal('show');
    // });

    // $(document).on('click','#saveSummaryBtn',function(){
    //     let summary = $('#summary').val()
    //     $('#topic').val(summary)
    //     send_update_ajax();
    // })
    
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