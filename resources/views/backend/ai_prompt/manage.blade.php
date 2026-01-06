@extends('backend.master', [
'pageTitle' => 'AI Prompts',
'activeMenu' => [
'item' => 'AI Prompts',
'subitem' => 'AI Prompts',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Prompts', 'url' => '#'],
['label' => 'Prompts']
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
            <h1 class="page-title">AI Prompt</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
            </div>
        </div>
    </div>

    <div class="card-body">
        <form id="UpdatePrompts">
        @csrf
        <div class="card p-4">
            <h5 class="mb-3 fw-semibold">Extraction Prompt</h5>
            <div class="row g-3">
                
                <div class="col-12">
                    <label for="system_prompt" class="form-label">System Prompt</label>
                    <textarea class="form-control" id="system_prompt" name="system_prompt" rows="3" placeholder="Enter system prompt">{{ old('system_prompt', $prompts->system_prompt ?? '') }}</textarea>
                </div>

                <div class="col-12">
                    <label for="upload_user_prompt" class="form-label">File Upload User Prompt</label>
                    <textarea class="form-control" id="upload_user_prompt" name="upload_user_prompt" rows="3" placeholder="Enter upload user prompt">{{ old('upload_user_prompt', $prompts->upload_user_prompt ?? '') }}</textarea>
                </div>

                <div class="col-12">
                    <label for="text_user_prompt" class="form-label">Parse Text User Prompt</label>
                    <textarea class="form-control" id="text_user_prompt" name="text_user_prompt" rows="3" placeholder="Enter text user prompt">{{ old('text_user_prompt', $prompts->text_user_prompt ?? '') }}</textarea>
                </div>

                <div class="col-12">
                    <label for="extraction_schema" class="form-label">Extraction Schema</label>
                    <textarea class="form-control" id="extraction_schema" name="extraction_schema" rows="8" 
                  placeholder="Enter extraction schema">{{ old('extraction_schema', json_encode($prompts->extraction_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) }}</textarea>
        <small class="text-muted">Ensure this is valid JSON format.</small>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <h5 class="mb-3 fw-semibold">Audit Prompt</h5>
            <div class="row g-3">
                <div class="col-12">
                    <label for="audit_system_prompt" class="form-label">System Prompt</label>
                    <textarea class="form-control" id="audit_system_prompt" name="audit_system_prompt" rows="3" placeholder="Enter system prompt">{{ old('audit_system_prompt', $prompts->audit_system_prompt ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <label for="audit_user_prompt" class="form-label">User Prompt</label>
                    <textarea class="form-control" id="audit_user_prompt" name="audit_user_prompt" rows="3" placeholder="Enter user prompt">{{ old('audit_user_prompt', $prompts->audit_user_prompt ?? '') }}</textarea>
                </div>
            </div>
        </div>

         <div class="card p-4">
            <h5 class="mb-3 fw-semibold">AI Extraction Models</h5>
            <div class="row g-3">
                    
                <div class="col-6">
                    <label for="ai_extract_pdf" class="form-label">AI Extract PDF</label>
                    <select class="form-select" id="ai_extract_pdf" name="ai_extract_pdf">
                        <option value="" disabled {{ old('ai_extract_pdf', $prompts->ai_extract_pdf ?? '') ? '' : 'selected' }}>Select a model</option>
                        <option value="gpt-4-turbo" {{ old('ai_extract_pdf', $prompts->ai_extract_pdf ?? '') == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo (Vision)</option>
                        <option value="gpt-4.1" {{ old('ai_extract_pdf', $prompts->ai_extract_pdf ?? '') == 'gpt-4.1' ? 'selected' : '' }}>GPT-4.1</option>
                    </select>
                    <small class="text-muted">Models capable of Vision/File parsing</small>
                </div>

                <div class="col-6">
                    <label for="ai_extract_text" class="form-label">AI Extract Text</label>
                    <select class="form-select" id="ai_extract_text" name="ai_extract_text">
                        <option value="" disabled {{ old('ai_extract_text', $prompts->ai_extract_text ?? '') ? '' : 'selected' }}>Select a model</option>
                        <option value="gpt-4.1-mini" {{ old('ai_extract_text', $prompts->ai_extract_text ?? '') == 'gpt-4.1-mini' ? 'selected' : '' }}>GPT-4.1 Mini</option>
                        <option value="gpt-4-turbo" {{ old('ai_extract_text', $prompts->ai_extract_text ?? '') == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>  
                        <option value="gpt-3.5-turbo" {{ old('ai_extract_text', $prompts->ai_extract_text ?? '') == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                    </select>
                </div>

                <div class="col-6">
                    <label for="audit_summary" class="form-label">Audit Summary</label>
                     <select class="form-select" id="audit_summary" name="audit_summary">
                        <option value="" disabled {{ old('audit_summary', $prompts->audit_summary ?? '') ? '' : 'selected' }}>Select a model</option>
                        <option value="gpt-4.1-mini" {{ old('audit_summary', $prompts->audit_summary ?? '') == 'gpt-4.1-mini' ? 'selected' : '' }}>GPT-4.1 Mini</option>
                        <option value="gpt-4-turbo" {{ old('audit_summary', $prompts->audit_summary ?? '') == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                        <option value="gpt-3.5-turbo" {{ old('audit_summary', $prompts->audit_summary ?? '') == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                    </select>
                </div>

                <div class="col-6">
                    <label for="temprature" class="form-label">Temperature</label>
                    <input type="number" class="form-control" id="temprature" name="temprature" rows="3" placeholder="Enter temperature" value="{{ old('temprature', $models->temprature ?? '') }}">
                </div>

                <div class="col-6">
                    <label for="max_tokens" class="form-label">Max Token</label>
                    <input type="number" class="form-control" id="max_tokens" name="max_tokens" rows="3" placeholder="Enter max tokens" value="{{ old('max_tokens', $models->max_tokens ?? '') }}">
                </div>

                <div class="col-6">
                    <label for="top_p" class="form-label">TopP</label>
                    <input type="number" class="form-control" id="top_p" name="top_p" rows="3" placeholder="Enter top p" value="{{ old('top_p', $models->top_p ?? '') }}">
                </div>
            </div>
        </div>

         <div class="text-end mt-3">
                <button type="submit" id="savePrompt" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')

<script>

    $(document).on('submit', 'form#UpdatePrompts', function (e) {
        e.preventDefault(); // prevent normal form submission

        const btn = $('#savePrompt');
        const cText = btn.text();

        const textarea = document.getElementById('extraction_schema');
        // ✅ Step 1: Validate JSON before sending
        if (textarea && textarea.value.trim() !== '') {
            try {
                JSON.parse(textarea.value);
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid JSON Format',
                    text: err.message
                });
                return; // stop submission
            }
        }


        // ✅ Create FormData object (this collects all form inputs automatically)
        const form = document.getElementById('UpdatePrompts');
        const data = new FormData(form);
        let id = @json ($prompts->id);
        $.ajax({
            type: "POST",
            url: "{{ route('admin.ai_prompt.update', ':id') }}".replace(':id', id),
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
                        window.location.href="{{ route('admin.ai_prompt.manage') }}"
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