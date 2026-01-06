@extends('backend.master', [
'pageTitle' => $page->title,
'activeMenu' => [
'item' => 'Pages',
'subitem' => $page->title ,
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Pages', 'url' => '#'],
['label' => $page->title ]
],])

@push('styles')
<!-- Quill Editor's CSS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<style>
    .ql-snow .ql-editor .ql-code-block-container {
        background-color: transparent;
        color: var(--bs-body-color);
    }
</style>

@endpush

@section('content')
<div class="container-fluid my-4 products_details">
    <div class="row card-header">
        <div class="col-md-12">
            <h1 class="text-primary-dark-mud-lg">Edit: {{ $page->title }}</h1>
        </div>
    </div>
    <div class="card-body">
        <form id="editForm" action="{{ route('page.update', $page->slug) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $page->slug }}">

            <div class="form-group">
                <label for="title" class="text-primary-orange">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $page->title }}" required>
            </div>

            <div class="form-group">
                <label for="content" class="text-primary-orange">Content</label>
                <!-- Quill Editor's container -->
                <div id="editor" style="height: 400px;">
                    {!! $page->content !!}
                </div>
                <!-- Hidden input to store editor content -->
                <input type="hidden" name="content" id="content">
            </div>

            <button type="button" class="btn btn-secondary-white mt-3 me-2" onclick="saveForm()">Save</button>
            <a href="{{ route('page.show', $page->slug) }}" class="btn btn-secondary-blue mt-3">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Include Quill JS -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Quill editor
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{
                        'header': [1, 2, false]
                    }],
                    ['bold', 'italic', 'underline'],
                    ['image', 'code-block'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        // Function to handle form submission on button click
        function saveForm() {
            // Set Quill content to hidden input
            var content = document.querySelector('#content');
            content.value = quill.root.innerHTML;

            // Manually submit the form
            document.getElementById('editForm').submit();
        }

        window.saveForm = saveForm;
    });
</script>
@endpush