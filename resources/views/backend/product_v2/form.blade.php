@extends('backend.master', [
    'pageTitle' => 'products',
    'activeMenu' => [
        'item' => 'products',
        'subitem' => 'products',
        'additional' => '',
    ],
    'breadcrumbItems' => [
        ['label' => 'Data Entry', 'url' => '#'],
        ['label' => 'products']
    ],
    'pageActions' => $product->id ? [
        'created' => $product->creator ? [
            'user' => $product->creator,
            'date' => $product->created_at,
            'model' => 'Product'
        ] : null,
        'updated' => $product->created_at && $product->created_at != $product->updated_at ? [
            'user' => $product->updater,
            'date' => $product->updated_at,
            'model' => 'Product'
        ] : null,
        'version' => $product->version ?? null
    ] : null
])

@php
    $tabs = [
        1 => 'Description',
        2 => 'Recipe',
        3 => 'Labels',
        4 => 'Costing',
        5 => 'Pricing'
    ];
    $hasProduct = (bool) $product->id;
    $currentTab = (int) request()->query('step', 1);
    if (!$hasProduct && $currentTab > 1) {
        $currentTab = 1;
    }
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="products form-wizard background-bg">
        <div class="title-add">
            <ul class="nav nav-tabs" id="productV2Tabs" role="tablist">
                @foreach($tabs as $stepNumber => $label)
                    @php $disabled = !$hasProduct && $stepNumber > 1; @endphp
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link {{ $currentTab === $stepNumber ? 'active' : '' }} {{ $disabled ? 'disabled' : '' }}"
                            data-bs-toggle="tab"
                            type="button"
                            role="tab"
                            data-step="{{ $stepNumber }}"
                            data-bs-target="#tab-step-{{ $stepNumber }}"
                            aria-controls="tab-step-{{ $stepNumber }}"
                            aria-selected="{{ $currentTab === $stepNumber ? 'true' : 'false' }}"
                            @if($disabled) tabindex="-1" aria-disabled="true" @endif
                        >
                            {{ $label }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content mt-4" id="productV2TabContent">
                <div class="tab-pane fade {{ $currentTab === 1 ? 'show active' : '' }}" id="tab-step-1" role="tabpanel">
                    @include('backend.product_v2.partials.form_step1')
                </div>

                @if($hasProduct)
                    <div class="tab-pane fade {{ $currentTab === 2 ? 'show active' : '' }}" id="tab-step-2" role="tabpanel">
                        @include('backend.product_v2.partials.form_step2')
                    </div>
                    <div class="tab-pane fade {{ $currentTab === 3 ? 'show active' : '' }}" id="tab-step-3" role="tabpanel">
                        @include('backend.product_v2.partials.form_step3')
                    </div>
                    <div class="tab-pane fade {{ $currentTab === 4 ? 'show active' : '' }}" id="tab-step-4" role="tabpanel">
                        @include('backend.product_v2.partials.form_step4')
                    </div>
                    <div class="tab-pane fade {{ $currentTab === 5 ? 'show active' : '' }}" id="tab-step-5" role="tabpanel">
                        @include('backend.product_v2.partials.form_step5')
                    </div>
                @else
                    @foreach(array_keys($tabs) as $stepNumber)
                        @continue($stepNumber === 1)
                        <div class="tab-pane fade {{ $currentTab === $stepNumber ? 'show active' : '' }}" id="tab-step-{{ $stepNumber }}" role="tabpanel">
                            <div class="alert alert-info mt-3">
                                Save the Description tab first to unlock the remaining steps.
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="{{ asset('assets') }}/js/dropzone.js"></script>
<script>
    const ProductFormV2 = {
        files: [],
        maxStep: 6,
        activeTab: {{ $currentTab }},
        hasProduct: {{ $hasProduct ? 'true' : 'false' }},

        init() {
            this.setupAjax();
            this.initializeComponents();
            this.attachEventListeners();
            this.applyTabFromQuery();
            this.injectVariantFlag();
            // Initialize scripts for the current active tab
            this.initializeTabScripts(this.activeTab);
        },

        injectVariantFlag() {
            $('form').each(function() {
                if (!$(this).find('input[name="tab_variant"]').length) {
                    $('<input>', { type: 'hidden', name: 'tab_variant', value: 'product_v2' }).appendTo($(this));
                }
            });
        },

        setupAjax() {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
        },

        initializeComponents() {
            this.initSelect2();
            this.initQuillEditors();
        },

        initSelect2() {
            $('.simple_select2').select2();
            $('.select2-tags').select2({
                tags: true,
                tokenSeparators: [',', ' ']
            });
        },

        initQuillEditors() {
            const toolbarOptions = [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['clean']
            ];

            $('.quill-editor').each(function() {
                const $editor = $(this);
                const $input = $(`input[name="${$editor.data('input')}"]`);

                const quill = new Quill($editor[0], {
                    theme: 'snow',
                    modules: { toolbar: toolbarOptions }
                });

                if ($input.val()) {
                    quill.root.innerHTML = $input.val();
                }

                quill.on('text-change', () => {
                    $input.val(quill.root.innerHTML);
                });
            });
        },

        attachEventListeners() {
            const self = this;

            // Use Bootstrap tab events instead of page reload
            $('#productV2Tabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                const step = parseInt($(e.target).data('step'), 10);
                
                if (step) {
                    self.activeTab = step;
                    self.initializeTabScripts(step);
                    
                    // Update URL without reload
                    const url = new URL(window.location.href);
                    url.searchParams.set('step', step);
                    window.history.replaceState({}, '', url);
                }
            });

            $('#productV2Tabs button').on('click', function (event) {
                if ($(this).hasClass('disabled')) {
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }
                // Let Bootstrap handle the tab switching
                // Scripts will be initialized via shown.bs.tab event
            });

            $('.js-btn-save').off('click').on('click', function (e) {
                e.preventDefault();
                $('.js-btn-save, .js-btn-next, .js-btn-finish').prop('disabled', true);
                self.submitForm($(e.target).closest('form').attr('id'), 'save');
            });

            $('.js-btn-next').off('click').on('click', function (e) {
                e.preventDefault();
                $('.js-btn-save, .js-btn-next, .js-btn-finish').prop('disabled', true);
                self.submitForm($(e.target).closest('form').attr('id'), 'next');
            });

            $('.js-btn-finish').off('click').on('click', function (e) {
                e.preventDefault();
                $('.js-btn-save, .js-btn-next, .js-btn-finish').prop('disabled', true);
                self.submitForm($(e.target).closest('form').attr('id'), 'finish');
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
            $('#productV2Tabs button').removeClass('active').attr('aria-selected', 'false');
            $(`#productV2Tabs button[data-step="${this.activeTab}"]`).addClass('active').attr('aria-selected', 'true');

            $('.tab-pane').removeClass('show active');
            $(`#tab-step-${this.activeTab}`).addClass('show active');

            // Initialize scripts for the active tab
            this.initializeTabScripts(this.activeTab);

            if (updateQuery) {
                const url = new URL(window.location.href);
                url.searchParams.set('step', this.activeTab);
                window.history.replaceState({}, '', url);
            }
        },

        initializeTabScripts(step) {
            // Initialize scripts based on active tab
            switch(step) {
                case 1:
                    if (typeof window.initStep1Scripts === 'function') {
                        window.initStep1Scripts();
                    }
                    break;
                case 2:
                    if (typeof window.initStep2Scripts === 'function') {
                        window.initStep2Scripts();
                    }
                    break;
                case 3:
                    if (typeof window.initStep3Scripts === 'function') {
                        window.initStep3Scripts();
                    }
                    break;
                case 4:
                case 5:
                    if (typeof window.initStep4Scripts === 'function') {
                        window.initStep4Scripts();
                    }
                    if (typeof window.initStep5Scripts === 'function') {
                        window.initStep5Scripts();
                    }
                    if (typeof window.initStep6Scripts === 'function') {
                        window.initStep6Scripts();
                    }
                    if (typeof window.initStep7Scripts === 'function') {
                        window.initStep7Scripts();
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
            const formData = new FormData(form);
            if (this.files.length > 0) {
                formData.delete('image_file[]');
                this.files.forEach(file => {
                    formData.append('image_file[]', file);
                });
            }
            return formData;
        },

        async submitForm(formId, action = 'save') {
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
                $('.js-btn-save, .js-btn-next, .js-btn-finish').prop('disabled', false);
                this.handleFormSuccess(response, action);
            } catch (error) {
                $('.js-btn-save, .js-btn-next, .js-btn-finish').prop('disabled', false);
                this.handleFormError(error);
            }
        },

        handleFormSuccess(response, action) {
            if (!response.success) {
                Swal.fire({
                    title: 'Warning!',
                    text: response.message || 'Please try again.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // New product: redirect to edit page to load full dataset after showing success
            if (!this.hasProduct && response.redirect_url) {
                window.location.href = response.redirect_url;
                return;
            }

            // Handle different button actions
            if (action === 'finish' && response.manage_url) {
                // Finish button: redirect to manage page
                window.location.href = response.manage_url;
                return;
            }

            if (action === 'next' && response.next_url) {
                // Next button: redirect to next step
                window.location.href = response.next_url;
                return;
            }


            Swal.fire({
                title: 'Success!',
                text: response.message,
                icon: 'success',
                confirmButtonText: 'OK',
                timer: 2500,
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                
                if (action === 'save' && response.redirect_url) {
                    // Save button: reload current page to show updated data
                    window.location.href = response.redirect_url;
                    return;
                }

                // Fallback logic for backward compatibility
                if (response.manage_url) {
                    window.location.href = response.manage_url;
                    return;
                }

                if (response.next_url) {
                    window.location.href = response.next_url;
                    return;
                }

                if (response.redirect_url) {
                    window.location.href = response.redirect_url;
                    return;
                }

                // Fallback to SPA tab switch
                this.nextTab();
            });

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

    window.ProductForm = ProductFormV2;
    $(document).ready(() => ProductFormV2.init());

    function formatWithCommas(value) {
        if (!value) return '';
        value = value.toString().replace(/,/g, ''); // Remove existing commas
        const parts = value.split('.');
        let integerPart = parts[0];
        const decimalPart = parts[1];

        // Add commas to integer part
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        // Combine integer and decimal parts
        return decimalPart ? `${integerPart}.${decimalPart}` : integerPart;
    }

    function removeCommas(value) {
        if (value == null || value === '') return 0; // Return 0 if the value is falsy (like null, undefined, or an empty string)

        // Remove commas and convert the value to a number
        const number = value.toString().replace(/,/g, '');

        // Check if the result is a valid number
        const validNumber = parseFloat(number);

        // If the result is a valid number, return it; otherwise, return 0
        return isNaN(validNumber) ? 0 : validNumber;
    }


    function restrictNonNumeric(field) {
        const value = field.val().replace(/\D/g, ''); // Remove all non-numeric characters
        field.val(value);
    }

    function NonNumericDecimal(field) {
        let value = field.val().replace(/[^0-9.]/g, ''); // Remove non-numeric and non-period characters
        let parts = value.split('.');

        // Ensure only one decimal point
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        // Prevent leading zeros unless it's "0."
        if (/^0[0-9]/.test(value)) {
            value = value.replace(/^0+/, '');
        }

        field.val(value);
    }

    function restrictNonNumericValue(field) {
        let value = field.val();

        // Remove all non-numeric characters except for the decimal point
        value = value.replace(/[^0-9.]/g, '');

        // Ensure only one decimal point is allowed
        let parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('').replace(/\./g, ''); // Keep only the first decimal point
        }

        // Restrict decimal digits to 2
        if (value.includes('.')) {
            let splitValue = value.split('.');
            if (splitValue[1].length > 2) {
                splitValue[1] = splitValue[1].substring(0, 2); // Keep only first two decimal places
            }
            value = splitValue.join('.');
        }

        field.val(value);
    }

    function restrictNonNumericOne(field) {
        let value = field.val();

        // Remove all non-numeric characters except for the decimal point
        value = value.replace(/[^0-9.]/g, '');

        // Ensure only one decimal point is allowed
        let parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('').replace(/\./g, ''); // Keep only the first decimal point
        }

        // Restrict decimal digits to 2
        if (value.includes('.')) {
            let splitValue = value.split('.');
            if (splitValue[1].length > 1) {
                splitValue[1] = splitValue[1].substring(0, 1); // Keep only first two decimal places
            }
            value = splitValue.join('.');
        }

        field.val(value);
    }

    $(".numeric-input").on("input", function () {
        restrictNonNumericValue($(this));
    });


     $(document).ready(function() {
        // Select the table
        var table = $('.ingredient_nutri_table');
        
        // Get all rows inside tbody
        var rows = table.find('tbody tr').get();
        
        // Sort the rows based on quantity weight in descending order
        rows.sort(function(a, b) {
            var quantityA = parseFloat($(a).find('.quantity-weight').text().replace(/,/g, '').trim());
            var quantityB = parseFloat($(b).find('.quantity-weight').text().replace(/,/g, '').trim());
            
            return quantityB - quantityA; // Sorting in descending order
        });
        
        // Append the sorted rows back to the tbody
        $.each(rows, function(index, row) {
            table.find('tbody').append(row);
        });

        table.find('tbody tr').slice(-2).addClass('total-row');
    });
</script>

{{-- Load all scripts upfront but wrap initialization in functions --}}
@if(view()->exists("backend.product_v2.partials.form_script_step1"))
    @include("backend.product_v2.partials.form_script_step1")
@endif

@if($hasProduct)
    @if(view()->exists("backend.product_v2.partials.form_script_step2"))
        @include("backend.product_v2.partials.form_script_step2")
    @endif
    @if(view()->exists("backend.product_v2.partials.form_script_step3"))
        @include("backend.product_v2.partials.form_script_step3")
    @endif
    @if(view()->exists("backend.product_v2.partials.form_script_step4"))
        @include("backend.product_v2.partials.form_script_step4")
    @endif
    @if(view()->exists("backend.product_v2.partials.form_script_step5"))
        @include("backend.product_v2.partials.form_script_step5")
    @endif
    @if(view()->exists("backend.product_v2.partials.form_script_step6"))
        @include("backend.product_v2.partials.form_script_step6")
    @endif
    @if(view()->exists("backend.product_v2.partials.form_script_step7"))
        @include("backend.product_v2.partials.form_script_step7")
    @endif
@endif
@endpush

