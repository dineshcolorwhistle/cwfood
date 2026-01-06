<script>
    let isFormChanged = false;
    let idleTime = 0;
    let ignorePopState = false;


    //Javascript for product form step 1
    $(document).ready(function() {
        
        // Restrict input to only numbers and periods
        $('.unit_weight_input, .readonly_field').on('input', function() {
            const value = $(this).val();
            const sanitizedValue = value.replace(/[^0-9.]/g, ''); // Remove non-numeric and non-period characters
            $(this).val(sanitizedValue);
        });

        // Attach change event listener to editable fields
        $('.unit_weight_input').on('change', function() {
            calculateValues();
        });

        $('.js-example-basic-single').select2({
            width: '100%'
        });

        function calculateValues() {
            // Get input values, defaulting to 0 if fields are empty
            var weightIndUnitG = parseFloat(removeCommas($('#weight_ind_unit_g').val())) || 0;
            var countIndUnitsPerRetail = parseFloat(removeCommas($('#count_ind_units_per_retail').val())) || 0;
            var countRetailUnitsPerCarton = parseFloat(removeCommas($('#count_retail_units_per_carton').val())) || 0;
            var countCartonsPerPallet = parseFloat(removeCommas($('#count_cartons_per_pallet').val())) || 0;
            var priceRetailUnit = parseFloat(removeCommas($('#price_retail_unit').val())) || 0;

            // Perform calculations for weight
            var weightRetailUnitG = Math.round(weightIndUnitG * countIndUnitsPerRetail);
            var weightCartonG = weightRetailUnitG * countRetailUnitsPerCarton;
            var weightPalletG = weightCartonG * countCartonsPerPallet;

            // Update readonly weight fields with formatted values
            $('#weight_ind_unit_g').val(weightIndUnitG > 0 ? formatWithCommas(weightIndUnitG.toFixed(1)) : '');
            $('#weight_retail_unit_g').val(weightRetailUnitG > 0 ? formatWithCommas(weightRetailUnitG.toFixed(1)) : '');
            $('#weight_carton_g').val(weightCartonG > 0 ? formatWithCommas(weightCartonG.toFixed(1)) : 'NA ');
            $('#weight_pallet_g').val(weightPalletG > 0 ? formatWithCommas(weightPalletG.toFixed(1)) : 'NA ');

            // Perform calculations for price
            var priceIndUnit = priceRetailUnit / (countIndUnitsPerRetail || 1); // Avoid division by 0
            var priceCarton = priceRetailUnit * countRetailUnitsPerCarton;
            var pricePallet = priceRetailUnit * countRetailUnitsPerCarton * countCartonsPerPallet;

            // Update readonly price fields with formatted values
            $('#price_retail_unit').val(priceRetailUnit > 0 ? formatWithCommas(priceRetailUnit.toFixed(2)) : '');
            $('#price_ind_unit').val(priceIndUnit > 0 ? formatWithCommas(priceIndUnit.toFixed(2)) : '');
            $('#price_carton').val(priceCarton > 0 ? formatWithCommas(priceCarton.toFixed(2)) : 'NA ');
            $('#price_pallet').val(pricePallet > 0 ? formatWithCommas(pricePallet.toFixed(2)) : 'NA ');

            $('#count_ind_units_per_retail').val(countIndUnitsPerRetail > 0 ? formatWithCommas(countIndUnitsPerRetail.toFixed(0)) : '');
            $('#count_retail_units_per_carton').val(countRetailUnitsPerCarton > 0 ? formatWithCommas(countRetailUnitsPerCarton.toFixed(0)) : '');
            $('#count_cartons_per_pallet').val(countCartonsPerPallet > 0 ? formatWithCommas(countCartonsPerPallet.toFixed(0)) : '');
        }

        // Perform initial calculations and formatting on page load
        calculateValues();

        let product = "{{$product->id}}"
        if (typeof product !== "undefined" && product !== null && product !== '') {
            console.log('fsdfsdfsd');
            
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
                    inactivity_discard();
                }
            }
            editlock_update(); // edit lock update
        }

        function inactivity_discard(){
            let url = "{{ route('products.inactivity', ':id') }}".replace(':id', product);
            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    if (response.status) {
                       window.location.href = "{{route('products.index')}}";   
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

        function editlock_update(){
            let url = "{{ route('products.edit_lock_update', ':id') }}".replace(':id', product);
            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                },
                error: function(xhr) {
                }
            }); 
        }

        $('#form_step_1').on('change input', 'input, select, textarea', function () {
            isFormChanged = true;
        });

        $('.js-btn-save, .js-btn-next').on('click', function () {
            isFormChanged = false;
        });

        window.addEventListener("beforeunload", function (e) {
            if (isFormChanged) {
                e.preventDefault();
                e.returnValue = ''; // Required for Chrome
            }
        });

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
                        $('.js-btn-save').click();
                        setTimeout(() => {
                            window.location.href = "{{route('products.index')}}";    
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



    $(document).ready(function() {

        const currentPath = window.location.pathname;
        const isCreatePage = currentPath.endsWith('/create');

        // Only set up SKU generation if we're on the create page
        if (isCreatePage) {
            let typingTimer;
            const doneTypingInterval = 1000; // Wait for 1 second after user stops typing

            $('input[name="prod_name"]').on('keyup', function() {
                clearTimeout(typingTimer);
                const productName = $(this).val();
                const productId = $('input[name="id"]').val() || ''; // Get product ID if exists

                if (productName) {
                    typingTimer = setTimeout(function() {
                        generateSKU(productName, productId);
                    }, doneTypingInterval);
                }
            });
        }

        function generateSKU(productName, productId) {
            $.ajax({
                url: '{{ route("generate.sku") }}',
                type: 'POST',
                data: {
                    product_name: productName,
                    product_id: productId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('input[name="prod_sku"]').val(response.sku);
                },
                error: function(xhr) {
                    console.error('Error generating SKU:', xhr.responseText);
                }
            });
        }
    });

    $(document).ready(function() {
        var $tagSelect = $('.select2-tags');
        var productId = '{{ isset($product) ? $product->id : "" }}';

        // Fetch existing tags
        $.ajax({
            url: '{{ route("products.getTags") }}',
            type: 'GET',
            data: {
                product_id: productId
            },
            success: function(response) {
                // Clear existing options in case this is being run more than once
                $tagSelect.empty();
                $.each(response.tags, function(index, tag) {
                    var isSelected = response.selectedTags.includes(tag['id'].toString());
                    var option = new Option(tag['name'], tag['id'], isSelected, isSelected);
                    $tagSelect.append(option);
                });

                // Initialize Select2 or re-initialize it after adding the options
                $tagSelect.select2({
                    tags: false, // Only allow selection from existing tags
                    tokenSeparators: [',', ' ']
                });
            },
            error: function(xhr, status, error) {
                alert('Error fetching tags: ' + error);
            }
        });

        $('#add-tag-btn').click(function() {
            // Using SweetAlert to show an input dialog
            Swal.fire({
                title: 'Add New Tag',
                input: 'text', // This indicates an input field
                inputPlaceholder: 'Tag name', // Placeholder for input
                showCancelButton: true, // Show cancel button
                confirmButtonText: 'Submit', // Text on the confirm button
                cancelButtonText: 'Cancel', // Text on the cancel button
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to write something!';
                    }
                }
            }).then((result) => {
                // If the user confirmed the prompt and entered a tag
                if (result.isConfirmed && result.value) {
                    var newTag = result.value;

                    // Proceed with the AJAX request
                    $.ajax({
                        url: '{{ route("products.createTag") }}',
                        type: 'POST',
                        data: {
                            name: newTag,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            // On success, show a success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Tag Created!',
                                text: 'The new tag "' + response.name + '" was successfully created.',
                            });

                            var option = new Option(response.name, response.name, true, true);
                            $tagSelect.append(option).trigger('change');
                        },
                        error: function(xhr, status, error) {
                            // On error, show an error message with SweetAlert
                            Swal.fire({
                                icon: 'warning',
                                title: 'Error creating tag',
                                text: 'Please check the tag already exist?',
                            });
                        }
                    });
                }
            });
        });

    });

    $(document).ready(function() {
        // Add file upload initialization to the existing ProductForm object
        // Object.assign(ProductForm, {
        //     files: [],

        //     initializeFileUpload() {
        //         let self = this;
        //         $('#uploadimage').on('change', function() {
        //             const selectedFiles = this.files;
        //             let filelength = $('#fileList li').length;

        //             if (selectedFiles.length > 0) {
        //                 for (var i = 0; i < 9; i++) {
        //                     if (i >= selectedFiles.length) break;
        //                     var file = selectedFiles[i];
        //                     self.files.push(file);

        //                     var fileName = file.name;
        //                     var listItem = `<li class="list-group-item d-flex justify-content-between align-items-center text-primary-dark-mud"> ${fileName}`;

        //                     if (filelength == 0 && i == 0) {
        //                         listItem += `<div class="d-flex justify-content-between align-items-center gap-5">
        //                         <div class="form-check">
        //                             <input class="form-check-input" type="radio" name="productDefault" id="product_img_${i}" checked>
        //                             <label class="form-check-label text-primary-dark-mud" for="product_img_${i}">Make as Default</label>
        //                         </div>`;
        //                         $('#default_image').val(1);
        //                     } else {
        //                         listItem += `<div class="d-flex justify-content-between align-items-center gap-5">
        //                         <div class="form-check">
        //                             <input class="form-check-input" type="radio" name="productDefault" id="product_img_${filelength + i}">
        //                             <label class="form-check-label text-primary-dark-mud" for="product_img_${filelength + i}">Make as Default</label>
        //                         </div>`;
        //                     }

        //                     listItem += `<button type="button" class="btn icon-primary-orange deleteBtn" data-index="${self.files.length - 1}">
        //                     <span class="material-symbols-outlined">delete</span>
        //                 </button></div></li>`;

        //                     $('#fileList').append(listItem);
        //                 }
        //             }
        //             $(this).val('');
        //         });

        //         $(document).on('click', '.deleteBtn', function() {
        //             const index = $(this).data('index');
        //             self.files.splice(index, 1);
        //             $(this).closest('li').remove();

        //             // Update remaining delete buttons' indices
        //             $('.deleteBtn').each(function(i) {
        //                 $(this).data('index', i);
        //             });
        //         });

        //         // $(document).on('change', 'input[name="productDefault"]', function() {
        //         //     const default_image_val = parseInt($(this).attr('id').split('_').pop()) + 1;
        //         //     $('#default_image').val(default_image_val);
        //         // });
        //     }
        // });


        // Override the prepareFormData method to handle file uploads
        const originalPrepareFormData = ProductForm.prepareFormData;
        ProductForm.prepareFormData = function(form) {
            const formData = originalPrepareFormData.call(this, form);

            // Clear any existing image_file entries
            formData.delete('image_file[]');

            // Append each file to the FormData
            selectedFiles.forEach((item, index) => {
                formData.append('image_file[]', item.file);
            });
            return formData;
        };

        // Initialize file upload functionality
        // ProductForm.initializeFileUpload();
    });

    // Function to remove existing images from the product
    function remove_images(_this) {
        let imgID = $(_this).attr('data-id');

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                let data = {
                    '_token': $('meta[name="csrf-token"]').attr('content')
                };

                $.ajax({
                    type: "POST",
                    url: `/remove/images/${imgID}`,
                    dataType: 'json',
                    data: data,
                    success: function(response) {
                        if (response['status'] == false) {
                            show_swal(0, response.message);
                        } else {
                            $(_this).closest('li').remove();
                            show_swal(1, "Image has been deleted.");
                        }
                    }
                });
            }
        });
    }

    function show_swal(status, message) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });

        swalWithBootstrapButtons.fire({
            text: message,
            icon: status == 0 ? "warning" : "success"
        });
    }


</script>