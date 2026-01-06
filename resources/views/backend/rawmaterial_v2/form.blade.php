@extends('backend.master', [
    'pageTitle' => 'Raw Material',
    'activeMenu' => [
        'item' => 'Rawmaterial',  
        'subitem' => 'Rawmaterial',
        'additional' => '',
    ],
    'breadcrumbItems' => [
        ['label' => 'Data Entry', 'url' => '#'],
        ['label' => 'Raw Materials']
    ],
])

@php
    $tabs = [
        1 => 'Description',
        2 => 'Specification'
    ];
    $hasIngredient = isset($ingredient) && $ingredient->id;
    $currentTab = (int) request()->query('step', 1);
    $ingredient = $hasIngredient ? $ingredient : new \App\Models\Ingredient();
    $details = isset($details) ? $details : [];
    $prod_labels = isset($prod_labels) ? $prod_labels : null;
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    .products.form-wizard .input-group select.form-control {
        background-image: none;
        border: 1px solid lightgrey;
        border-radius: 4px !important;
    }
    div#custom-text p {font-size: 11px;color: #808080ab !important;}
    
    .form-action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    @media (max-width: 768px) {
        .d-flex.justify-content-between.align-items-center {
            flex-direction: column;
            align-items: stretch !important;
        }
        
        .form-action-buttons {
            margin-top: 1rem;
            width: 100%;
            justify-content: flex-end;
        }
        
        #rawmaterialV2Tabs {
            width: 100%;
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="products form-wizard background-bg">
        <div class="title-add">
            <form id="rawmaterial_v2_form" action="{{ $hasIngredient ? route('update.raw-materials', $ingredient->id) : route('save.ingredient') }}" method="POST">
                @csrf
                <input type="hidden" name="ing_form" value="{{ $hasIngredient ? 'edit' : 'add' }}">
                <input type="hidden" name="ing_form_id" id="ing_form_id" value="{{ $hasIngredient ? $ingredient->id : '' }}">
                <input type="hidden" name="form_source" value="rawmaterial_v2">
                @if($hasIngredient)
                    <input type="hidden" name="client_id" value="{{ $ingredient->client_id }}">
                    <input type="hidden" name="workspace" value="{{ $ingredient->workspace_id }}">
                    <input type="hidden" name="ing_img" value="{{ $ingredient->ing_image ?? '' }}">
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <ul class="nav nav-tabs mb-0" id="rawmaterialV2Tabs" role="tablist">
                        @foreach($tabs as $stepNumber => $label)
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link {{ $currentTab === $stepNumber ? 'active' : '' }}"
                                    data-bs-toggle="tab"
                                    type="button"
                                    role="tab"
                                    data-step="{{ $stepNumber }}"
                                    data-bs-target="#tab-step-{{ $stepNumber }}"
                                    aria-controls="tab-step-{{ $stepNumber }}"
                                    aria-selected="{{ $currentTab === $stepNumber ? 'true' : 'false' }}"
                                >
                                    {{ $label }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    
                    <div class="form-action-buttons">
                        <a href="{{ route('rawmaterial_v2.manage') }}" class="btn btn-secondary-white">
                            Cancel
                        </a>
                        <button type="button" class="btn btn-secondary-blue js-btn-save">
                            <i class="material-symbols-outlined me-1" style="font-size: 18px;">save</i>
                            Save
                        </button>
                    </div>
                </div>

                <div class="tab-content mt-4" id="rawmaterialV2TabContent">
                    <div class="tab-pane fade {{ $currentTab === 1 ? 'show active' : '' }}" id="tab-step-1" role="tabpanel">
                        @include('backend.rawmaterial_v2.partials.form_step1')
                    </div>

                    <div class="tab-pane fade {{ $currentTab === 2 ? 'show active' : '' }}" id="tab-step-2" role="tabpanel">
                        @include('backend.rawmaterial_v2.partials.form_step2')
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="{{ asset('assets') }}/js/dropzone.js"></script>
<script src="{{ asset('assets') }}/js/ingredient.js"></script>
<script>
    let isFormChanged = false;
    let idleTime = 0;
    let ignorePopState = false;
    let ingredient_id = $('#ing_form_id').val(); // Blank for Create mode

    $(document).ready(function () {
        let idleInterval = setInterval(timerIncrement, 60000); // 1 minute
        $(this).mousemove(resetTimer);
        $(this).keypress(resetTimer);
        $(this).scroll(resetTimer);
        $(this).click(resetTimer);

        function resetTimer() {
            idleTime = 0;
        }

        function timerIncrement() {
            idleTime++;
            if (idleTime == 15) { // 15 minutes
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: 'You’ve been inactive for a while. This session will close in 1 minute unless you take action'
                });
            }else if(idleTime == 16){
                if (ingredient_id) {
                    // Only in Edit mode discard changes remotely
                    inactivityDiscard();
                } else {
                    // Create mode behaviour (choose one based on UX)
                    window.location.href = "{{route('manage.raw-materials')}}";
                }
            }
        }

        function inactivity_discard(){
            if (!ingredient_id) return;

            let ingredient= $('#ing_form_id').val()
            let url = "{{ route('raw-materials.inactivity', ':id') }}".replace(':id', ingredient);
            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    if (response.status) {
                        window.location.href = "{{route('manage.raw-materials')}}";   
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred'
                    });
                }
            }); 
        }

        if(ingredient_id){
            $('#rawmaterial_v2_form').on('change input', 'input, select, textarea', function () {
                isFormChanged = true;
            });

            $(document).on('click', '.js-btn-save', function () {
                isFormChanged = false;
                ignorePopState = true;
            });


            window.addEventListener("beforeunload", function (e) {
                if (isFormChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        }

        window.history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function (event) {
            if (ignorePopState) return;
            if (isFormChanged) {
                // Push back state again to stop browser back
                window.history.pushState(null, null, window.location.href);
                Swal.fire({
                    title: 'Are you sure you want to exit?',
                    text: "You have unsaved changes. What would you like to do?",
                    icon: 'warning',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'Save and Exit',
                    denyButtonText: 'Discard Changes and Exit',
                    cancelButtonText: 'Continue Editing',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        ignorePopState = true;
                        let custom_button = $('#ing_form')
                        form_submit(custom_button) 
                        setTimeout(() => {
                            window.location.href = "{{route('manage.raw-materials')}}";    
                        }, 1000);
                    } else if (result.isDenied) {
                        isFormChanged = false;
                        ignorePopState = true;
                        inactivity_discard();
                    } else {
                        // Stay on the page
                    }
                });
            }
        });

    });   

    const RawMaterialFormV2 = {
        files: [],
        maxStep: 2,
        activeTab: {{ $currentTab }},
        hasIngredient: {{ $hasIngredient ? 'true' : 'false' }},

        init() {
            this.setupAjax();
            this.initializeComponents();
            this.attachEventListeners();
            this.applyTabFromQuery();
            this.initializeTabScripts(this.activeTab);
        },

        setupAjax() {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
        },

        initializeComponents() {
            this.initSelect2();
        },

        initSelect2() {
            $('.simple_select2').select2();
            $('.select2-tags').select2({
                tags: true,
                tokenSeparators: [',', ' ']
            });
            $('.js-example-basic-single').select2();
            $('.fa-basic-multiple').select2();
        },

        attachEventListeners() {
            const self = this;

            $('#rawmaterialV2Tabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                const step = parseInt($(e.target).data('step'), 10);
                
                if (step) {
                    self.activeTab = step;
                    self.initializeTabScripts(step);
                    
                    const url = new URL(window.location.href);
                    url.searchParams.set('step', step);
                    window.history.replaceState({}, '', url);
                }
            });

            $('.js-btn-save').off('click').on('click', function (e) {
                e.preventDefault();
                $('.js-btn-save').prop('disabled', true);
                isFormChanged = false;
                ignorePopState = true;
                self.submitForm('rawmaterial_v2_form', false);
            });

            $('.form-control').on('focus', function() {
                $(this).siblings('.invalid-feedback').hide();
            });
        },

        applyTabFromQuery() {
            this.setActiveTab(this.activeTab, false);
        },

        setActiveTab(step, updateQuery = true) {
            this.activeTab = Math.max(1, Math.min(step, this.maxStep));
            $('#rawmaterialV2Tabs button').removeClass('active').attr('aria-selected', 'false');
            $(`#rawmaterialV2Tabs button[data-step="${this.activeTab}"]`).addClass('active').attr('aria-selected', 'true');

            $('.tab-pane').removeClass('show active');
            $(`#tab-step-${this.activeTab}`).addClass('show active');

            this.initializeTabScripts(this.activeTab);

            if (updateQuery) {
                const url = new URL(window.location.href);
                url.searchParams.set('step', this.activeTab);
                window.history.replaceState({}, '', url);
            }
        },

        initializeTabScripts(step) {
            switch(step) {
                case 1:
                    if (typeof window.initRawMaterialStep1Scripts === 'function') {
                        window.initRawMaterialStep1Scripts();
                    }
                    break;
                case 2:
                    if (typeof window.initRawMaterialStep2Scripts === 'function') {
                        window.initRawMaterialStep2Scripts();
                    }
                    break;
            }
        },

        nextTab() {
            if (this.activeTab < this.maxStep) {
                this.setActiveTab(this.activeTab + 1);
            }
        },

        prepareFormData(form) {
            const formData = new FormData();
            const form_data = $('#rawmaterial_v2_form').serializeArray();
            
            // Append all form fields except allergens (they will be handled separately)
            $.each(form_data, function (key, input) {
                if (input.name !== 'ing_allergen[]') {
                    formData.append(input.name, input.value);
                }
            });
            
            // Handle image files from dropzone
            if (typeof selectedFiles !== 'undefined' && selectedFiles.length > 0) {
                selectedFiles.forEach((item, index) => {
                    formData.append("image_file[]", item.file);
                });
            }
            
            // Append allergens in correct order (from select2 order tracking)
            if (typeof selectedOrder !== 'undefined' && selectedOrder.length > 0) {
                selectedOrder.forEach(val => formData.append('ing_allergen[]', val));
            } else {
                // Fallback if user didn't select anything or selectedOrder not set
                const selectedVals = $('#ing_allergen').val() || [];
                selectedVals.forEach(val => formData.append('ing_allergen[]', val));
            }
            
            return formData;
        },

        async submitForm(formId, isNext = false) {
            const form = $(`#${formId}`);
            const formData = this.prepareFormData(form[0]);

            try {
                const response = await $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false
                });
                $('.js-btn-save').prop('disabled', false);
                this.handleFormSuccess(response, isNext);
            } catch (error) {
                $('.js-btn-save').prop('disabled', false);
                this.handleFormError(error);
            }
        },

        handleFormSuccess(response, isNext) {
            if (!response.status && !response.success) {
                Swal.fire({
                    title: 'Warning!',
                    text: response.message || 'Please try again.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                title: 'Success!',
                text: response.message || 'Form saved successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                setTimeout(() => {
                    window.location.href = response.url;
                }, 1000); // 0.5 sec delay
            });

        
            // if (!this.hasIngredient && (response.redirect_url || response.edit_url)) {
                //     window.location.href = response.redirect_url || response.edit_url;
                //     return;
                // }

            // Swal.fire({
            //     title: 'Success!',
            //     text: response.message || 'Form saved successfully',
            //     icon: 'success',
            //     confirmButtonText: 'OK'
            // }).then(() => {
            //     if (isNext && this.activeTab < this.maxStep) {
            //         this.setActiveTab(this.activeTab + 1);
            //     }
            // });
        },

        handleFormError(error) {
            const message = error.status === 422
                ? Object.values(error.responseJSON.errors).map(err => `<div>${err}</div>`).join('')
                : 'Something went wrong. Please try again.';
            Swal.fire({
                title: error.status === 422 ? 'Validation Error' : 'Error',
                html: message,
                icon: error.status === 422 ? 'warning' : 'error',
                confirmButtonText: 'OK'
            });
        }
    };

    window.RawMaterialForm = RawMaterialFormV2;
    
    // Initialize selectedOrder for allergen tracking (if not already defined)
    if (typeof selectedOrder === 'undefined') {
        window.selectedOrder = [];
    }
    
    $(document).ready(() => {
        RawMaterialFormV2.init();
        
        // Initialize allergen select2 with order tracking
        if ($('#ing_allergen').length) {
            $('#ing_allergen').select2({
                width: '100%',
                multiple: true
            }).on('select2:select', function (e) {
                const id = e.params.data.id;
                if (!window.selectedOrder.includes(id)) {
                    window.selectedOrder.push(id);
                }
            }).on('select2:unselect', function (e) {
                const id = e.params.data.id;
                window.selectedOrder = window.selectedOrder.filter(v => v !== id);
            });
            
            // Initialize selectedOrder with preselected values
            const preselected = $('#ing_allergen').val() || [];
            if (preselected.length > 0) {
                window.selectedOrder = preselected.slice();
            }
        }
    });
</script>

@if(view()->exists("backend.rawmaterial_v2.partials.form_script_step1"))
    @include("backend.rawmaterial_v2.partials.form_script_step1")
@endif

@if(view()->exists("backend.rawmaterial_v2.partials.form_script_step2"))
    @include("backend.rawmaterial_v2.partials.form_script_step2")
@endif
@endpush

