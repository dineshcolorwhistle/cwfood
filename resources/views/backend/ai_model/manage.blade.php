@extends('backend.master', [
'pageTitle' => 'AI Models',
'activeMenu' => [
'item' => 'AI Models',
'subitem' => 'AI Models',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Models', 'url' => '#'],
['label' => 'Models']
]
])

@push('styles')
<style>
    ::placeholder {color: #c7ccd0 !important;opacity: 1;}
    .label-right {text-align: right;display: block;}
</style>
@endpush

@section('content')
<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <h1 class="page-title">AI Model</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
            </div>
        </div>
    </div>

    <div class="card-body">
        <form id="UpdateModels">   
        @csrf
        <div class="card p-4">
            <h5 class="mb-3 fw-semibold">AI Extraction Models</h5>
            <div class="row g-3">
                  
                <div class="col-6">
                    <label for="ai_extract_pdf" class="form-label">AI Extract PDF</label>
                    <input type="text" class="form-control" id="ai_extract_pdf" name="ai_extract_pdf" rows="3" placeholder="Enter Extract PDF model" value="{{ old('ai_extract_pdf', $models->ai_extract_pdf ?? '') }}">
                </div>

                <div class="col-6">
                    <label for="ai_extract_text" class="form-label">AI Extract Text</label>
                    <input type="text" class="form-control" id="ai_extract_text" name="ai_extract_text" rows="3" placeholder="Enter extract text model" value="{{ old('ai_extract_text', $models->ai_extract_text ?? '') }}">
                </div>

                <div class="col-6">
                    <label for="audit_summary" class="form-label">Audit Summary</label>
                    <input type="text" class="form-control" id="audit_summary" name="audit_summary" rows="3" placeholder="Enter text user prompt" value="{{ old('audit_summary', $models->audit_summary ?? '') }}">
                </div>

                <div class="col-6">
                    <label for="temprature" class="form-label">Temprature</label>
                    <input type="text" class="form-control" id="temprature" name="temprature" rows="3" placeholder="Enter Extract PDF model" value="{{ old('temprature', $models->temprature ?? '') }}">
                </div>

                <div class="col-6">
                    <label for="max_tokens" class="form-label">Max Token</label>
                    <input type="text" class="form-control" id="max_tokens" name="max_tokens" rows="3" placeholder="Enter extract text model" value="{{ old('max_tokens', $models->max_tokens ?? '') }}">
                </div>

                <div class="col-6">
                    <label for="top_p" class="form-label">TopP</label>
                    <input type="text" class="form-control" id="top_p" name="top_p" rows="3" placeholder="Enter text user prompt" value="{{ old('top_p', $models->top_p ?? '') }}">
                </div>
            </div>
        </div>

         <div class="text-end mt-3">
                <button type="submit" id="saveModel" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')

<script>

    $(document).on('submit', 'form#UpdateModels', function (e) {
        e.preventDefault(); // prevent normal form submission

        const btn = $('#saveModel');
        const cText = btn.text();



        // ✅ Create FormData object (this collects all form inputs automatically)
        const form = document.getElementById('UpdateModels');
        const data = new FormData(form);
        let id = @json($models->id);
        $.ajax({
            type: "POST",
            url: "{{ route('admin.ai_models.update', ':id') }}".replace(':id', id),
            data: data,
            processData: false, // prevent jQuery from processing data
            contentType: false, // prevent jQuery from setting incorrect headers
            dataType: 'json',

            beforeSend: function () {
                if (btn.length) {
                    btn.text('Processing...');
                    btn.prop('disabled', true);
                }
            },
            success: function (response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        text: response.message,
                        timer: 2000
                    }).then(() => {
                        window.location.href="{{ route('admin.ai_models.manage') }}"
                    });
                }else{
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: response.message
                    }); 
                    
                }
            },
            complete: function () {
                if (btn.length) {
                    btn.text(cText);
                    btn.prop('disabled', false);
                }
            }
        });
    });
</script>
@endpush