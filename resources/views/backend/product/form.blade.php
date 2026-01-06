@extends('backend.master', [
'pageTitle' => 'Products',
'activeMenu' => [
'item' => 'Products',
'subitem' => 'Products',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Data Entry', 'url' => '#'],
['label' => 'Products']
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

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="products form-wizard background-bg">
        <div class="title-add">
            {{-- Progress Bar Section --}}
            <div class="multisteps-form__progress mb-5">
                @php
                $titles = [
                1 => 'Description',
                2 => 'Recipe',
                3 => 'Labels',
                4 => 'Costing',
                5 => 'Pricing',
                ];
                $isEditRoute = Route::currentRouteName() == 'products.edit'; // Check if on 'edit' page
                @endphp

                @for ($i = 1; $i <= 5; $i++)
                    <button
                    class="multisteps-form__progress-btn {{ $step >= $i ? 'js-active' : '' }}"
                    type="button"
                    title="{{ $titles[$i] }}"
                    @if($isEditRoute)
                    onclick="goToStep({{ $i }})"
                    @endif>
                    {{ $titles[$i] }}
                    </button>
                    @endfor
            </div>

            {{-- Form Content Section --}}
            <div class="multisteps_form_content">
                @if(view()->exists("backend.product.partials.form_step{$step}"))
                @include("backend.product.partials.form_step{$step}")
                @else
                <div class="alert alert-danger">
                    Invalid step. Redirecting to step 1...
                    <script>
                        setTimeout(() => {
                            window.location.href = window.location.href.split('?')[0] + '?step=1';
                        }, 2000);
                    </script>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<script>
    const ProductForm = {
        files: [],
        maxStep: 6, // Define maximum allowed step

        init() {
            this.setupAjax();
            this.initializeComponents();
            this.attachEventListeners();
        },

        prepareFormData(form) {
            const formData = new FormData(form);

            // Get files array from the window scope
            const files = window.getProductFiles ? window.getProductFiles() : [];

            if (files.length > 0) {
                formData.delete('image_file[]');
                files.forEach(file => {
                    formData.append('image_file[]', file);
                });
            }

            return formData;
        },

        setupAjax() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        },

        initializeComponents() {
            this.initSelect2();
            this.initQuillEditors();
        },

        initSelect2() {
            $('.simple_select2').select2();
        },

        initQuillEditors() {
            const toolbarOptions = [
                ['bold', 'italic', 'underline'],
                [{
                    'list': 'ordered'
                }, {
                    'list': 'bullet'
                }],
                ['clean']
            ];

            $('.quill-editor').each(function() {
                const $editor = $(this);
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
            });
        },


        attachEventListeners() {
            $('.js-btn-save').click(e => {
                e.preventDefault();
                $('.js-btn-save, .js-btn-next').prop('disabled', true);
                this.submitForm($(e.target).closest('form').attr('id'), false);
            });

            $('.js-btn-next').click(e => {
                e.preventDefault();
                $('.js-btn-save, .js-btn-next').prop('disabled', true);
                this.submitForm($(e.target).closest('form').attr('id'), true);
            });

            $('.form-control').on('focus', function() {
                $(this).siblings('.invalid-feedback').hide();
            });

            $('form').on('submit', this.handleQuillSubmission);

            
        },

        handleQuillSubmission() {
            $('.quill-editor').each(function() {
                const quill = Quill.find(this);
                const $input = $(`input[name="${$(this).data('input')}"]`);
                $input.val(quill.root.innerHTML);
            });
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
                $('.js-btn-save, .js-btn-next').prop('disabled', false);
                this.handleFormSuccess(response, isNext);
            } catch (error) {
                $('.js-btn-save, .js-btn-next').prop('disabled', false);
                this.handleFormError(error);
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

        handleFormSuccess(response, isNext) {
            
            if(response.success){
                if (isNext) {
                    window.location.href = response.next_url;
                    return;
                }
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    }
                });
            }else{
                Swal.fire({
                    title: 'Warning!',
                    text: response.message,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                })
            }
        },
        handleFormError(error) {
            const message = error.status === 422 ?
                Object.values(error.responseJSON.errors).map(err => `<div>${err}</div>`).join('') :
                'Something went wrong. Please try again.';
            Swal.fire({
                title: error.status === 422 ? 'Validation Error' : 'Error',
                html: message,
                icon: error.status === 422 ? 'warning' : 'error',
                confirmButtonText: 'OK'
            });
        }
    };

    // Navigation function with step validation
    function goToStep(step) {
        const maxStep = 6;
        if (step < 1 || step > maxStep) {
            Swal.fire({
                title: 'Invalid Step',
                text: 'Redirecting to step 1...',
                icon: 'warning',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = window.location.href.split('?')[0] + '?step=1';
            });
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('step', step);
        window.location.href = url.toString();
    }

    // Initialize the form handler
    $(document).ready(() => ProductForm.init());

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

<script src="{{ asset('assets') }}/js/dropzone.js"></script>

@if(view()->exists("backend.product.partials.form_script_step{$step}"))

    @if(in_array($step, ["4","5"]))
        @include("backend.product.partials.form_script_step4")
        @include("backend.product.partials.form_script_step5")
        @include("backend.product.partials.form_script_step6")
        @include("backend.product.partials.form_script_step7")
    @else
        @include("backend.product.partials.form_script_step{$step}")
    @endif
@endif
@endpush