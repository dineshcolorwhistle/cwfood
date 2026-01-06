@extends('backend.master', [
'pageTitle' => 'Web Pages Management',
'activeMenu' => [
'item' => 'Pages',
'subitem' => 'Pages',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Batchbase Admin', 'url' => '#'],
['label' => 'Web Pages']
],])

@push('styles')
@endpush

@section('content')
<div class="container-fluid pages px-0">
    <div class="">
        <div class="card-header d-flex justify-content-between">
            <h1 class="page-title">Batchbase Admin - Web Pages</h1>
            <button type="button" class="btn btn-primary-orange plus-icon" data-toggle="modal" data-target="#actionModal" id="addPageBtn">
                <span class="material-symbols-outlined">add</span>
            </button>
        </div>
        <div class="card-body">
            <table class="table" id="dtRecordsView">
                <thead>
                    <tr>
                        <th class="text-primary-orange">Title</th>
                        <th class="text-primary-orange hidden">Slug</th>
                        <th class="text-primary-orange hidden">URL</th>
                        <th class="text-primary-orange">Access</th>
                        <th class="text-primary-orange">Description</th>
                        <th class="text-primary-orange"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pages as $page)
                    <tr data-id="{{ $page->id }}">
                        <td class="text-primary-dark-mud">{{ $page->title }}</td>
                        <td class="text-primary-dark-mud hidden">{{ $page->slug }}</td>
                        <td class="text-primary-dark-mud hidden">{{ $page->url }}</td>
                        <td class="text-primary-dark-mud">{{ $page->scope }}</td>
                        <td class="text-primary-dark-mud">{{ $page->description }}</td>
                        <td class="actions-menu-area">
                            <div class="">
                                <!-- 3-Dot Icon Menu for Grid View -->
                                <div class="dropdown d-flex justify-content-end">
                                    <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud me-2 edit-page"
                                                data-id="{{ $page->id }}"
                                                data-title="{{ $page->title }}"
                                                data-slug="{{ $page->slug }}"
                                                data-url="{{ $page->url }}"
                                                data-description="{{ $page->description }}"
                                                data-scope="{{ $page->scope }}"
                                                data-content="{{ $page->content }}">
                                                <span class="sidenav-normal ms-2 ps-1">Edit</span>
                                            </span>
                                        </li>
                                        <li>
                                            <span class="dropdown-item text-primary-dark-mud delete-page" data-id="{{ $page->id }}">
                                                <span class="sidenav-normal ms-2 ps-1">Delete</span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                        {{--
                        <td>
                            <span class="icon-primary-orange edit-page"
                                data-id="{{ $page->id }}"
                        data-title="{{ $page->title }}"
                        data-slug="{{ $page->slug }}"
                        data-url="{{ $page->url }}"
                        data-description="{{ $page->description }}"
                        data-content="{{ $page->content }}">
                        <span class="material-symbols-outlined">edit</span>
                        </span>
                        <span class="icon-primary-orange delete-page" data-id="{{ $page->id }}">
                            <span class="material-symbols-outlined">delete</span>
                        </span>
                        </td>
                        --}}
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Page Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-primary-orange" id="actionModalLabel">Add/Edit Web Page</h4>
                </div>

                <form id="pageForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="page_id" id="page_id">

                        <div class="form-group">
                            <label class="text-primary-orange" for="title">Page Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>

                        <div class="form-group hidden">
                            <label class="text-primary-orange" for="slug">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control" readonly>
                        </div>

                        <div class="form-group hidden">
                            <label class="text-primary-orange" for="url">URL</label>
                            <input type="text" name="url" id="url" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label class="text-primary-orange" for="description">Description</label>
                            <textarea name="description" id="description" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="text-primary-orange" for="Access">Access</label>
                            <select class="form-select" id="scope" name="scope">
                                <option value="Full">Full</option>
                                <option value="Read">Read</option>
                            </select>
                        </div>

                        <div class="form-group hidden">
                            <label class="text-primary-orange" for="content">Content</label>
                            <textarea name="content" id="content" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary-white" data-dismiss="modal" id="closeActionModal">Close</button>
                        <button type="submit" class="btn btn-secondary-blue" id="savePageBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        $(document).on('focusout','#title',function(){
            $('#slug').val($(this).val())
        })

        // Add Page Button Click
        $(document).on('click', '#addPageBtn', function() {
            // Reset the form
            $('#pageForm')[0].reset();
            $('#page_id').val('');
            $('#actionModalLabel').text('Add Web Page');
            $('#savePageBtn').text('Create');

            // Show the modal
            $('#actionModal').modal('show');
        });

        // Edit Page Button Click (using event delegation)
        $(document).on('click', '.edit-page', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const slug = $(this).data('slug');
            const url = $(this).data('url');
            const description = $(this).data('description');
            const content = $(this).data('content');
            const scope = $(this).data('scope');

            $('#page_id').val(id);
            $('#title').val(title);
            $('#slug').val(slug);
            $('#url').val(url);
            $('#description').val(description);
            $('#content').val(content);
            $('#scope').val(scope)


            $('#actionModalLabel').text('Edit Web Page');
            $('#savePageBtn').text('Update');
            $('#actionModal').modal('show');
        });

        // Save/Update Page Form Submit
        $('#pageForm').on('submit', function(e) {
            e.preventDefault();
            const pageId = $('#page_id').val();
            const url = pageId ?
                "{{ route('pages.update', ':id') }}".replace(':id', pageId) :
                "{{ route('pages.store') }}";
            const method = pageId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
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
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessage = '';

                        for (const [field, messages] of Object.entries(errors)) {
                            errorMessage += `${field}: ${messages.join(', ')}\n`;
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: 'Validation Errors',
                            text: errorMessage
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON.message || 'An error occurred'
                        });
                    }
                }
            });
        });

        // Delete Page Button Click (using event delegation)
        $(document).on('click', '.delete-page', function() {
            const id = $(this).data('id');
            const url = "{{ route('pages.destroy', ':id') }}".replace(':id', id);

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
                                icon: 'warning',
                                title: 'Warning!',
                                text: response.message
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
    });
</script>
@endpush