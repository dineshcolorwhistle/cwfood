<script>
    /**
     * Drag and drop
     */
    $(function () {
        $('#dynamicMachinery tbody').sortable({
            handle: '.drag-handle',
            items: 'tr.machinery-row',
            placeholder: 'row-placeholder'
        });
    });

    // JavaScript for product form step 5
    $(document).ready(function() {
        let machineryIndex = parseInt($('#machineryIndex').val());

        function getParsedValue(row, inputName) {
            return parseFloat(removeCommas(row.find(`input[name$="${inputName}"]`).val())) || 0;
        }

        // Function to calculate machinery details for a row
        function calculateMachineryDetails(row) {
            const hours = getParsedValue(row, "[hours]");
            const costPerHourInput = row.find('input[name$="[cost_per_hour]"]');
            const productUnitsSelect = row.find('select[name$="[machine_units]"]');
            const weightInput = row.find('input[name$="[weight]"]');
            const costPerKgInput = row.find('input[name$="[cost_per_kg]"]');
            const machinerySelect = row.find('select[name$="[machinery_id]"]');

            // Format hours with commas
            row.find('input[name$="[hours]"]').val(formatWithCommas(hours));

            // If we have a selected machinery, trigger cost calculations
            if (machinerySelect.val()) {
                calculateMachineryCostPerKg(row);
            }
        }

        // Function to calculate cost per kg
        function calculateMachineryCostPerKg(row) {
            const productId = $('#product_id').val();
            const productUnits = row.find('select[name$="[machine_units]"]').val();
            const hours = getParsedValue(row, "[hours]");
            const costPerHour = getParsedValue(row, "[cost_per_hour]");
            const totCostInput = row.find('input[name$="[tot_cost]"]');

            if (productId && productUnits) {
                $.ajax({
                    url: "{{route('products.get-machinery-weight')}}",
                    method: 'POST',
                    data: {
                        product_id: productId,
                        product_units: productUnits,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        const totalWeight = response.weight; // in grams
                        const totalMachineryCost = hours * costPerHour;

                        // Calculate cost per kg (convert weight to kg)
                        const costPerKg = totalWeight > 0 ? (totalMachineryCost / (totalWeight / 1000)).toFixed(2) : '0.00';

                        totCostInput.val(formatWithCommas(totalMachineryCost.toFixed(2)));

                        row.find('input[name$="[weight]"]').val(formatWithCommas(totalWeight));
                        row.find('input[name$="[cost_per_kg]"]').val(formatWithCommas(costPerKg));
                        updateMachineryTotalRow();
                    }
                });
            }
        }

        // Function to update totals row
        function updateMachineryTotalRow() {
            let totalHours = 0;
            let totalCostPerHour = 0;
            let totalCostPerKg = 0;
            let rowCount = 0;

            $('.machinery-row').each(function() {
                if ($(this).find('select[name$="[machinery_id]"]').val()) {
                    const hours = getParsedValue($(this), "[hours]");
                    const costPerHour = getParsedValue($(this), "[cost_per_hour]");
                    const costPerKg = getParsedValue($(this), "[cost_per_kg]");

                    totalHours += hours;
                    totalCostPerHour += costPerHour;
                    totalCostPerKg += costPerKg;
                    rowCount++;
                }
            });

            $('#total-hours').text(formatWithCommas(totalHours.toFixed(2)));
            $('#avg-cost-per-hour').text(formatWithCommas(rowCount > 0 ? (totalCostPerHour / rowCount).toFixed(2) : '0.00'));
            $('#total-machinery-cost-per-kg').text(formatWithCommas(totalCostPerKg.toFixed(2)));
        }

        // Add new machinery row
        $("#add_machinery").click(function() {
            $('#no_machinery_row').remove();
            ++machineryIndex;
            let newRow = `<tr class="machinery-row">
            <td class="drag-handle" draggable="true">
                <div style="display: flex;flex-direction: column;top: 20px; left: 10px;cursor: pointer;">   
                    <span class="material-symbols-outlined">drag_indicator</span>
                </div>
            </td>
            <td>
                <select name="MachineryFields[${machineryIndex}][machinery_id]" class="form-select simple_select2 machinery-selection" required>
                    <option value="">--Select Machinery--</option>
                    @foreach($machinery as $machine)
                        <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="MachineryFields[${machineryIndex}][hours]" class="form-control text-end" required />
            </td>
            <td>
                <input type="text" name="MachineryFields[${machineryIndex}][cost_per_hour]" class="form-control text-end" readonly />
            </td>
            <td>
                <input type="text" name="MachineryFields[${machineryIndex}][tot_cost]" class="form-control text-end" step="0.01" min="0" readonly />
            </td>
            <td>
                <select name="MachineryFields[${machineryIndex}][machine_units]" class="form-select" required>
                    <option value="1">Individual unit</option>
                    <option value="2">Selling unit</option>
                    <option value="3">Batch</option>
                    <option value="4">Carton</option>
                    <option value="5">Pallet</option>
                </select>
            </td>
            <td>
                <input type="text" name="MachineryFields[${machineryIndex}][weight]" class="form-control text-end" readonly />
            </td>
            <td>
                <input type="text" name="MachineryFields[${machineryIndex}][cost_per_kg]" class="form-control text-end" readonly />
            </td>
            <td class="text-center">
                <div class="remove-machinery delete-icon cursor-pointer text-danger">
                    <i class="material-symbols-outlined">delete</i>
                </div>
            </td>
        </tr>`;

            const $newRow = $(newRow);
            $("#dynamicMachinery tbody").append($newRow);

            // Initialize select2
            const select2Element = $newRow.find('.simple_select2');
            select2Element.select2();

            // Bind the event handler before opening to focus on search input
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

            calculateMachineryDetails($newRow);
        });

        // Event handlers for input changes
        $(document).on('input', 'input[name$="[hours]"]', function() {
            restrictNonNumericValue($(this));
        });

        $(document).on('focusout', 'input[name$="[hours]"]', function() {
            calculateMachineryDetails($(this).closest('tr'));
        });

        // Event handler for machinery selection
        $(document).on('change', 'select[name$="[machinery_id]"]', function() {
            const row = $(this).closest('tr');
            const machineryId = $(this).val();

            if (machineryId) {
                $.ajax({
                    url: "{{route('products.get-machinery-details')}}",
                    method: 'POST',
                    data: {
                        machinery_id: machineryId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        row.find('input[name$="[cost_per_hour]"]').val(formatWithCommas(response.cost_per_hour));
                        calculateMachineryDetails(row);
                    }
                });
            } else {
                row.find('input[name$="[cost_per_hour]"]').val('0.00');
                row.find('input[name$="[weight]"]').val('0.00');
                row.find('input[name$="[cost_per_kg]"]').val('0.00');
                updateMachineryTotalRow();
            }
        });

        // Event handler for product units change
        $(document).on('change', 'select[name$="[machine_units]"]', function() {
            calculateMachineryCostPerKg($(this).closest('tr'));
        });

        $(window).on('load', function () {
            $('.machinery-row').each(function () {
                calculateMachineryCostPerKg($(this));
                updateMachineryTotalRow();
            });
        });

        // Remove machinery row
        $(document).on('click', '.remove-machinery', function() {
            const row = $(this).closest('.machinery-row');
            const prodMachineryId = row.find('input[name$="[id]"]').val();

            if (prodMachineryId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to remove this machinery entry?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{route('products.remove-machinery')}}",
                            method: 'POST',
                            data: {
                                prod_machinery_id: prodMachineryId,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    row.remove();
                                    updateMachineryTotalRow();
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Machinery entry has been removed.',
                                        icon: 'success',
                                        timer: 1500
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message || 'An error occurred while removing the machinery entry.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            } else {
                row.remove();
                updateMachineryTotalRow();
            }
        });

        // Initialize existing rows
        $('.machinery-row').each(function() {
            calculateMachineryDetails($(this));
        });

        // Initial update of totals
        updateMachineryTotalRow();
    });
</script>