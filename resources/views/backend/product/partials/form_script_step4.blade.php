<script>
    /**
     * Drag and drop
     */
    $(function () {
        $('#dynamicLabours tbody').sortable({
            handle: '.drag-handle',
            items: 'tr.labour-row',
            placeholder: 'row-placeholder'
        });
    });
    // JavaScript for product form step 4
    $(document).ready(function() {
        let labourIndex = parseInt($('#labourIndex').val());

        function getParsedValue(row, inputName) {
            return parseFloat(removeCommas(row.find(`input[name$="${inputName}"]`).val())) || 0;
        }
        // Function to calculate labour details for a row
        function calculateLabourDetails(row) {
            const peopleCount = getParsedValue(row, "[people_count]");
            const hoursPerPerson = getParsedValue(row, "[hours_per_person]");
            const peopleHoursInput = row.find('input[name$="[people_hours]"]');
            const hourlyRateInput = row.find('input[name$="[hourly_rate]"]');
            const labourSelect = row.find('select[name$="[labour_id]"]');

            row.find('input[name$="[people_count]"]').val(formatWithCommas(peopleCount));
            row.find('input[name$="[hours_per_person]"]').val(formatWithCommas(hoursPerPerson.toFixed(2)));
            

            // Calculate and set people hours
            const peopleHours = peopleCount * hoursPerPerson;
            peopleHoursInput.val(formatWithCommas(peopleHours.toFixed(2)));

            // If we have a selected labour, trigger cost calculations
            if (labourSelect.val()) {
                calculateLabourCostPerKg(row);
            }
        }

        // Function to calculate cost per kg
        function calculateLabourCostPerKg(row) {
            const productUnitsSelect = row.find('select[name$="[labour_units]"]');
            const costPerKgInput = row.find('input[name$="[cost_per_kg]"]');
            const peopleHoursInput = row.find('input[name$="[people_hours]"]');
            const hourlyRateInput = row.find('input[name$="[hourly_rate]"]');
            const totCostInput = row.find('input[name$="[tot_cost]"]');
            const totWeightInput = row.find('input[name$="[tot_weight]"]');

            const productId = $('#product_id').val();
            const productUnits = productUnitsSelect.val();
            const peopleHours = parseFloat(removeCommas(peopleHoursInput.val())) || 0;
            const hourlyRate = parseFloat(removeCommas(hourlyRateInput.val())) || 0;            

            if (productId && productUnits) {
                $.ajax({
                    url: "{{route('products.get-product-weight')}}",
                    method: 'POST',
                    data: {
                        product_id: productId,
                        product_units: productUnits,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        const totalWeight = parseFloat(response.weight); // in grams
                        const totalLabourCost = peopleHours * hourlyRate;

                        // Set total labour cost in the input field
                        totCostInput.val(formatWithCommas(totalLabourCost.toFixed(2)));
                        totWeightInput.val(formatWithCommas(totalWeight.toFixed(2)));

                        // Calculate cost per kg (convert weight to kg)
                        const costPerKg = totalWeight > 0 ?
                            (totalLabourCost / (totalWeight / 1000)).toFixed(2) :
                            '0.00';
                        console.log(costPerKg);
                        
                        costPerKgInput.val(formatWithCommas(costPerKg));
                        updateLabourTotalRow();
                    }
                });
            }
        }

        // Updated function to calculate totals
        function updateLabourTotalRow() {
            //Show or hide the total row
            $('.labour-row').length > 0 ? $('#labour-total-row').show() : $('#labour-total-row').hide();

            let totalPeopleCount = 0;
            let totalHoursPerPerson = 0;
            let totalPeopleHours = 0;
            let totalHourlyRate = 0;
            let totalCostPerKg = 0;
            let rowCount = 0;

            $('.labour-row').each(function() {
                if ($(this).find('select[name$="[labour_id]"]').val()) {
                    const peopleCount = getParsedValue($(this), "[people_count]");
                    const hoursPerPerson = getParsedValue($(this), "[hours_per_person]");
                    const peopleHours = getParsedValue($(this), "[people_hours]");
                    const hourlyRate = getParsedValue($(this), "[hourly_rate]");
                    const costPerKg = getParsedValue($(this), "[cost_per_kg]");

                    totalPeopleCount += peopleCount;
                    totalHoursPerPerson += hoursPerPerson;
                    totalPeopleHours += peopleHours;
                    totalHourlyRate += hourlyRate;
                    totalCostPerKg += costPerKg;
                    rowCount++;
                }
            });

            $('#total-people-count').text(totalPeopleCount.toFixed(2));
            $('#total-hours-per-person').text(totalHoursPerPerson.toFixed(2));
            $('#total-people-hours').text(totalPeopleHours.toFixed(2));
            $('#avg-hourly-rate').text(rowCount > 0 ? (totalHourlyRate / rowCount).toFixed(2) : '0.00');
            $('#total-cost-per-kg').text(formatWithCommas(totalCostPerKg.toFixed(2)));
        }

        // Event handler for labour selection
        $(document).on('change', 'select[name$="[labour_id]"]', function() {
            const row = $(this).closest('tr');
            const labourId = $(this).val();

            if (labourId) {
                $.ajax({
                    url: "{{route('products.get-labour-details')}}",
                    method: 'POST',
                    data: {
                        labour_id: labourId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        row.find('input[name$="[hourly_rate]"]').val(response.hourly_rate);
                        calculateLabourDetails(row);
                    }
                });
            } else {
                // Clear fields if no labour is selected
                row.find('input[name$="[hourly_rate]"]').val('0');
                row.find('input[name$="[cost_per_kg]"]').val('0');
                updateLabourTotalRow();
            }
        });

        // Event handlers for input changes
        $(document).on('input', 'input[name$="[people_count]"]', function() {
            restrictNonNumeric($(this));
            calculateLabourDetails($(this).closest('tr'));
        });

        $(document).on('input', 'input[name$="[hours_per_person]"]', function() {
            restrictNonNumericValue($(this));
        });

        $(document).on('focusout', 'input[name$="[hours_per_person]"]', function() {
            calculateLabourDetails($(this).closest('tr'));
        });

        // Event handler for product units change
        $(document).on('change', 'select[name$="[labour_units]"]', function() {
            calculateLabourCostPerKg($(this).closest('tr'));
        });

        $(window).on('load', function () {
            $('.labour-row').each(function () {
                calculateLabourCostPerKg($(this));
                updateLabourTotalRow();
            });
        });


        // Add new labour row
        $("#add_labour").click(function() {
            $('#no_labour_row').remove();
            ++labourIndex;

            let newRow = `
            <tr class="labour-row">
                <td class="drag-handle" draggable="true">
                    <div style="display: flex;flex-direction: column;top: 20px; left: 10px;cursor: pointer;">   
                        <span class="material-symbols-outlined">drag_indicator</span>
                    </div>
                </td>
                <td>
                    <select name="LabourFields[${labourIndex}][labour_id]" class="form-select simple_select2 labour-selection" required>
                        <option value="">--Select Labour--</option>
                        @foreach($labours as $labour)
                        <option value="{{ $labour->id }}">
                            {{ $labour->labour_type }}
                        </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" name="LabourFields[${labourIndex}][people_count]" 
                           class="form-control text-end" min="1" required />
                </td>
                <td>
                    <input type="text" name="LabourFields[${labourIndex}][hours_per_person]" 
                           class="form-control text-end" step="0.01" min="0" required />
                </td>
                <td>
                    <input type="text" name="LabourFields[${labourIndex}][people_hours]" 
                           class="form-control text-end" step="0.01" min="0" readonly />
                </td>
                <td>
                    <input type="text" name="LabourFields[${labourIndex}][hourly_rate]" 
                           class="form-control text-end" step="0.01" min="0" readonly />
                </td>
                <td>
                    <input type="text" name="LabourFields[${labourIndex}][tot_cost]"
                        class="form-control text-end" step="0.01" min="0" readonly />
                </td>
                <td>
                    <select name="LabourFields[${labourIndex}][labour_units]" class="form-select" required>
                        <option value="1">Individual unit</option>
                        <option value="2">Selling unit</option>
                        <option value="3">Batch</option>
                        <option value="4">Carton</option>
                        <option value="5">Pallet</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="LabourFields[${labourIndex}][tot_weight]"
                        class="form-control text-end" step="0.01" min="0" readonly />
                </td>
                <td>
                    <input type="text" name="LabourFields[${labourIndex}][cost_per_kg]" 
                           class="form-control text-end" step="0.01" min="0" readonly />
                </td>
                <td class="text-center">
                    <div class="remove-labour delete-icon cursor-pointer text-danger">
                        <i class="material-symbols-outlined">delete</i>
                    </div>
                </td>
            </tr>
        `;

            const $newRow = $(newRow);
            $("#dynamicLabours tbody").append($newRow);
            // Initialize select2
            const select2Element = $newRow.find('.simple_select2');
            select2Element.select2();

            // Bind the event handler before opening
            select2Element.on('select2:open', function() {
                // Use requestAnimationFrame to ensure DOM is ready
                requestAnimationFrame(() => {
                    const searchField = $('.select2-container--open .select2-search__field');
                    if (searchField.length) {
                        searchField.focus();
                    }
                });
            });

            // Open the select2 dropdown after the row is added
            setTimeout(() => {
                select2Element.select2('open');

                // Wait a bit to ensure the dropdown is open, then focus the search field
                setTimeout(() => {
                    const searchField = $('.select2-container--open .select2-search__field');
                    if (searchField.length) {
                        searchField
                            .trigger('focus')
                            .trigger('click')
                            .trigger('select');
                    }
                }, 50);
            }, 200);
        });

        // Remove labour row
        $(document).on('click', '.remove-labour', function() {
            const row = $(this).closest('.labour-row');
            const prodLabourId = row.find('input[name$="[id]"]').val();

            if (prodLabourId) {
                // Show confirmation for existing entries
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to remove this labour entry?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('products.remove-labour')}}",
                            method: 'POST',
                            data: {
                                prod_labour_id: prodLabourId,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    row.remove();
                                    updateLabourTotalRow();

                                    // Show success message
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Labour entry has been removed.',
                                        icon: 'success',
                                        timer: 1500
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message || 'An error occurred while removing the labour entry.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            } else {
                // Remove new rows directly
                row.remove();
                updateLabourTotalRow();
            }
        });



        // Initial calculations for existing rows
        $('.labour-row').each(function() {
            if ($(this).find('select[name$="[labour_id]"]').val()) {
                calculateLabourDetails($(this));
            }
        });

        let inputFields = [
            "#contingency",
            "#retailer_charges",
            "#wholesale_margin",
            "#distributor_margin",
            "#retailer_margin", 
            "rrp_ex_gst_sell"
        ];

        $(inputFields.join(", ")).on("input", function () {
            let value = $(this).val().replace(/[^0-9.]/g, ""); // Allow numbers and a dot
            let parts = value.split(".");
            
            // Ensure only one decimal point
            if (parts.length > 2) {
                value = parts[0] + "." + parts.slice(1).join(""); 
            }

            // Prevent leading zeros unless it's "0." for decimals
            if (/^0[0-9]/.test(value)) {
                value = value.replace(/^0+/, ""); // Remove leading zeros
            }

            // Allow only one digit after the decimal
            if (value.includes(".")) {
                let [integerPart, decimalPart] = value.split(".");
                decimalPart = decimalPart.substring(0, 2); // Limit to 1 decimal place
                value = decimalPart !== undefined ? `${integerPart}.${decimalPart}` : integerPart;
            }

            let n = parseFloat(value) || 0;

            // Ensure value does not exceed 100
            if (n > 100) {
                $(this).val(value.slice(0, -1));
            } else {
                $(this).val(value);
            }
        });

        // Format input on blur to ensure 2 decimal places
        $(inputFields.join(", ")).on("blur", function () {
            let value = parseFloat($(this).val());
            if (!isNaN(value)) {
                $(this).val(value.toFixed(2)); // Ensures 2 decimal places
            }
        });

        // Initial update of totals
        updateLabourTotalRow();


        let product = "{{$product->id}}"
        let isFormChanged = false;
        let idleTime = 0;
        let ignorePopState = false;
        
        if (typeof product !== "undefined" && product !== null && product !== '') {
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

        $('#form_step_4, #form_step_5').on('change input', 'input, select, textarea', function () {
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
</script>