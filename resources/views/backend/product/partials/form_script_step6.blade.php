<script>
    /**
     * Drag and drop
     */
    $(function () {
        $('#dynamicPackaging tbody').sortable({
            handle: '.drag-handle',
            items: 'tr.packaging-row',
            placeholder: 'row-placeholder'
        });
    });


    // Javascript for product form step 6
    $(document).ready(function() {
        let packagingIndex = parseInt($('#packagingIndex').val());

        function getParsedValue(row, inputName) {
            return parseFloat(removeCommas(row.find(`input[name$="${inputName}"]`).val())) || 0;
        }

        // Function to calculate packaging details
        function calculatePackagingDetails(row) {
            const packagingSelect = row.find('select[name$="[packaging_id]"]');
            const costPerSellUnitInput = row.find('input[name$="[cost_per_sell_unit]"]');
            const supplierName = row.find('input[name$="[supplier_name]"]');
            const weightPerSellUnitInput = row.find('input[name$="[weight_per_sell_unit]"]');
            const costPerKgInput = row.find('input[name$="[cost_per_kg]"]');
            const productId = $('#product_id').val();
            

            // Fetch packaging cost per sell unit via Ajax
            packagingSelect.on('change', function() {
                const packagingId = $(this).val();
                if (packagingId) {
                    $.ajax({
                        url: "{{ route('products.get-packaging-details') }}",
                        method: 'POST',
                        data: {
                            packaging_id: packagingId,
                            product_id: productId,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            console.log(response);
                            
                            let weight_kg = response['cost_per_sell_unit'];
                            let costper = response['packagings']['price_per_unit'];
                            let type = response['packagings']['type'];
                            weightPerSellUnitInput.val(formatWithCommas(weight_kg));
                            supplierName.val(response.packagings.supplier.company_name);
                            costPerSellUnitInput.val(formatWithCommas(costper));
                            calculatePackagingCostPerKg(row, type);
                        }
                    });
                }
            });
        }

        // Function to calculate cost per kg
        function calculatePackagingCostPerKg(row, type) {
            const productId = $('#product_id').val();
            // const productUnit = row.find('select[name$="[product_units]"]').val();
            const productUnit = type;
            const costPerSellUnit = getParsedValue(row, "[cost_per_sell_unit]");

            // Ajax call to get product's retail unit weight
            $.ajax({
                url: "{{ route('products.get-packaging-weight') }}", // Reuse the existing route
                method: 'POST',
                data: {
                    product_id: productId,
                    product_units: productUnit,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    const weightPerSellUnit = response.weight; // weight in grams
                    // Calculate cost per kg
                    const costPerKg = weightPerSellUnit > 0 ? ((costPerSellUnit / weightPerSellUnit) * 1000).toFixed(2) : '0.00';

                    row.find('input[name$="[weight_per_sell_unit]"]').val(formatWithCommas(weightPerSellUnit));
                    row.find('input[name$="[cost_per_kg]"]').val(formatWithCommas(costPerKg));
                    row.find('input[name$="[product_units]"]').val(type);
                    updatePackagingTotalRow();
                }
            });
        }

        function updatePackagingTotalRow() {
            let totalCostPerSellUnit = 0;
            let totalWeightPerSellUnit = 0;
            let totalCostPerKg = 0;

            // Iterate through all packaging rows
            $('.packaging-row').each(function() {
                const costPerSellUnit = getParsedValue($(this), "[cost_per_sell_unit]");
                const weightPerSellUnit = getParsedValue($(this), "[weight_per_sell_unit]");
                const costPerKg = getParsedValue($(this), "[cost_per_kg]");

                totalCostPerSellUnit += costPerSellUnit;
                totalWeightPerSellUnit += weightPerSellUnit;
                totalCostPerKg += costPerKg;
            });

            // Update total row with formatted values
            $('#total-cost-per-sell-unit').text(formatWithCommas(totalCostPerSellUnit.toFixed(2)));
            $('#total-weight-per-sell-unit').text(formatWithCommas(totalWeightPerSellUnit.toFixed(2)));
            $('#total-packaging-cost-per-kg').text(formatWithCommas(totalCostPerKg.toFixed(2)));
        }

        // Initial setup for existing rows
        $('#dynamicPackaging tbody tr.packaging-row').each(function() {
            calculatePackagingDetails($(this));
        });

        // Setup for newly added rows
        $("#add_packaging").click(function() {
            $('#no_packaging_row').remove();
            ++packagingIndex;
            let newRow = `
            <tr class="packaging-row">
                <td class="drag-handle" draggable="true">
                    <div style="display: flex;flex-direction: column;top: 20px; left: 10px;cursor: pointer;">   
                        <span class="material-symbols-outlined">drag_indicator</span>
                    </div>
                </td>
                <td>
                    <select name="PackagingFields[${packagingIndex}][packaging_id]" class="form-select simple_select2 packaging-selection" required>
                        <option value="">--Select Packaging--</option>
                        @foreach($packaging as $pack)
                            <option value="{{ $pack->id }}"  data-units="{{ $pack->type }}">{{ $pack->name }} ({{ $pack->type }})</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" name="PackagingFields[${packagingIndex}][supplier_name]" class="form-control" step="0.01" min="0" readonly />
                </td>
                <td>
                    <input type="text" name="PackagingFields[${packagingIndex}][cost_per_sell_unit]" class="form-control text-end" readonly />
                </td>
                <td>
                    <input type="text" name="PackagingFields[${packagingIndex}][product_units]" 
                                class="form-control" value="" readonly />
                        </td>
                </td>
                <td>
                    <input type="text" name="PackagingFields[${packagingIndex}][weight_per_sell_unit]" class="form-control text-end" readonly />
                </td>
                <td>
                    <input type="text" name="PackagingFields[${packagingIndex}][cost_per_kg]" class="form-control text-end" readonly />
                </td>
                <td class="text-center">
                    <div class="remove-packaging delete-icon cursor-pointer text-danger">
                        <i class="material-symbols-outlined">delete</i>
                    </div>
                </td>
            </tr>
        `;

            const $newRow = $(newRow);
            $("#dynamicPackaging tbody").append($newRow);
            
            // Initialize select2 on the new row
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
                }, 50);  // Adjust the delay as needed (50ms here)
            }, 200);

            // Calculate details for the new row
            calculatePackagingDetails($newRow);
        });

        // Remove packaging row
        $(document).on('click', '.remove-packaging', function() {
            const row = $(this).closest('.packaging-row');
            const prodPackagingId = row.find('input[name$="[id]"]').val();

            // Check if it's an existing saved entry or a new dynamically added row
            if (prodPackagingId) {
                // Show SweetAlert confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to remove this packaging entry?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Ajax call to remove packaging entry
                        $.ajax({
                            url: "{{ route('products.remove-packaging') }}",
                            method: 'POST',
                            data: {
                                prod_packaging_id: prodPackagingId,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    row.remove();
                                    updatePackagingTotalRow();

                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Packaging entry has been removed.',
                                        icon: 'success',
                                        timer: 1500
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message || 'An error occurred while removing the packaging entry.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            } else {
                // For dynamically added rows not yet saved to database
                row.remove();
                updatePackagingTotalRow();
            }
        });

        // Initial total row update (in case of existing data)
        updatePackagingTotalRow();

        // $(document).on('change', 'select[name$="[product_units]"]', function() {
        //     calculatePackagingCostPerKg($(this).closest('tr'));
        // });

        $(window).on('load', function () {
            $('.packaging-row').each(function () {
                let type = $(this).find('input[name$="[product_units]"]').val();
                console.log(type);
                calculatePackagingCostPerKg($(this), type);
                updatePackagingTotalRow();
            });
        });
    });
</script>