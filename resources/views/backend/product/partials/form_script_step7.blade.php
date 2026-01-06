<script>
    /**
     * Drag and drop
     */
    $(function () {
        $('#dynamicfreight tbody').sortable({
            handle: '.drag-handle',
            items: 'tr.freight-row',
            placeholder: 'row-placeholder'
        });
    });

    // Javascript for product form step 6
    $(document).ready(function() {

        let freightIndex = parseInt($('#freightIndex').val());
        function getParsedValue(row, inputName) {
            return parseFloat(removeCommas(row.find(`input[name$="${inputName}"]`).val())) || 0;
        }

        // Function to calculate packaging details
        function calculateFrefightDetails(row) {
            const freightSelect = row.find('select[name$="[freight_id]"]');
            const supplierName = row.find('input[name$="[freight_supplier]"]');
            const costPerSellUnitInput = row.find('input[name$="[freight_cost]"]');
            const freightUnit = row.find('input[name$="[freight_units]"]');
            const weightPerSellUnitInput = row.find('input[name$="[freight_weight]"]');
            const costPerKgInput = row.find('input[name$="[cost_per_kg]"]');
            const productId = $('#product_id').val();
        
            // Fetch packaging cost per sell unit via Ajax
            freightSelect.on('change', function() {
                const freightId = $(this).val();
                if (freightId) {
                    $.ajax({
                        url: "{{ route('products.get-freight-details') }}",
                        method: 'POST',
                        data: {
                            freight_id: freightId,
                            product_id: productId,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            let fre_cost = response['freights']['freight_price'];
                            let fre_weight = response['fr_weight'];
                            let cost_per_kg = response['cost_per_kg'].toFixed(2);
                            let company = (response.freights.supplier)? response.freights.supplier.company_name : ""
                            supplierName.val(company);
                            costPerSellUnitInput.val(formatWithCommas(fre_cost));
                            freightUnit.val(response.freights.freight_unit);
                            weightPerSellUnitInput.val(formatWithCommas(fre_weight));
                            costPerKgInput.val(formatWithCommas(cost_per_kg));
                            updateFrefightTotalRow();
                        }
                    });
                }
            });
        }

        // Setup for newly added rows
        $("#add_freight").click(function() {
            $('#no_freight_row').remove();
            ++freightIndex;
            let newRow = `
            <tr class="freight-row">
                <td class="drag-handle" draggable="true">
                    <div style="display: flex;flex-direction: column;top: 20px; left: 10px;cursor: pointer;">   
                        <span class="material-symbols-outlined">drag_indicator</span>
                    </div>
                </td>
                <td>
                    <select name="FreightFields[${freightIndex}][freight_id]" class="form-select simple_select2 packaging-selection" required>
                        <option value="">--Select Freight--</option>
                        @foreach($freights as $freight)
                            <option value="{{ $freight->id }}" data-units="{{ $freight->freight_unit }}">{{ $freight->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" name="FreightFields[${freightIndex}][freight_supplier]" class="form-control" step="0.01" min="0" readonly />
                </td>
                <td>
                    <input type="text" name="FreightFields[${freightIndex}][freight_cost]" class="form-control text-end" readonly />
                </td>
                <td>
                    <input type="text" name="FreightFields[${freightIndex}][freight_units]" 
                                class="form-control" value="" readonly />
                        </td>
                </td>
                <td>
                    <input type="text" name="FreightFields[${freightIndex}][freight_weight]" class="form-control text-end" readonly />
                </td>
                <td>
                    <input type="text" name="FreightFields[${freightIndex}][cost_per_kg]" class="form-control text-end" readonly />
                </td>
                <td class="text-center">
                    <div class="remove-freight delete-icon cursor-pointer text-danger">
                        <i class="material-symbols-outlined">delete</i>
                    </div>
                </td>
            </tr>`;

            const $newRow = $(newRow);
            $("#dynamicfreight tbody").append($newRow);
            
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
            calculateFrefightDetails($newRow);
        });

        function updateFrefightTotalRow() {
            let totalCostPerKg = 0;
            // Iterate through all packaging rows
            $('.freight-row').each(function() {
                const costPerKg = getParsedValue($(this), "[cost_per_kg]");
                totalCostPerKg += costPerKg;
            });
            // Update total row with formatted values
            $('#total-freight-cost-per-kg').text(formatWithCommas(totalCostPerKg.toFixed(2)));
        }

        $(document).on('click', '.remove-freight', function() {
            const row = $(this).closest('.freight-row');
            const prodFreightId = row.find('input[name$="[id]"]').val();

            // Check if it's an existing saved entry or a new dynamically added row
            if (prodFreightId) {
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
                            url: "{{ route('products.remove-freights') }}",
                            method: 'POST',
                            data: {
                                prod_freight_id: prodFreightId,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    row.remove();
                                    updateFrefightTotalRow();

                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Freight entry has been removed.',
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
                updateFrefightTotalRow();
            }
        });

        $(window).on('load', function () {
            $('.freight-row').each(function () {
                updateFrefightTotalRow();
            });

            let rrp_ex = $(`#rrp_ex_gst_sell`).val()
            let cost_weight = $(`#cost_weight_sell`).val()
            let cost_price = $(`#cost_weight_price`).val()
            let wholesale_price_sell = $(`#wholesale_price_sell`).val()
            let dist_price_sell = $(`#distributor_price_sell`).val()

            let rrp_kg =  rrp_ex*(cost_price/cost_weight)
            $(`#rrp_ex_gst_price`).val(rrp_kg.toFixed(2))

            let rrp_inc_gst = rrp_ex* (1+ 0.1)
            $(`#rrp_inc_gst_sell`).val(rrp_inc_gst.toFixed(2))

            let rrp_inc_price = rrp_inc_gst*(cost_price/cost_weight)
            $(`#rrp_inc_gst_price`).val(rrp_inc_price.toFixed(2))

            let wholesale_price = wholesale_price_sell*(cost_price/cost_weight)
            $(`#wholesale_price_kg_price`).val(wholesale_price.toFixed(2))

            let dist_price = dist_price_sell*(cost_price/cost_weight)
            $(`#distributor_price_kg_price`).val(dist_price.toFixed(2))
        });

        $(document).on('focusout','#wholesale_price_sell,#distributor_price_sell,#rrp_ex_gst_sell',function(){
            let cost_weight = $(`#cost_weight_sell`).val()
            let cost_price = $(`#cost_weight_price`).val()
            let value = $(this).val()
            if($(this).attr('id') =="wholesale_price_sell"){
                let wholesale_price = value*(cost_price/cost_weight)
                $(`#wholesale_price_kg_price`).val(wholesale_price.toFixed(2))
            }else if($(this).attr('id') =="distributor_price_sell"){
                let dist_price = value*(cost_price/cost_weight)
                $(`#distributor_price_kg_price`).val(dist_price.toFixed(2))
            }else{
                let value = $(this).val()
                let rrp_ex_price = value*(cost_price/cost_weight)
                $(`#rrp_ex_gst_price`).val(rrp_ex_price.toFixed(2))

                let rrp_inc_gst = value* (1+ 0.1)
                $(`#rrp_inc_gst_sell`).val(rrp_inc_gst.toFixed(2))

                let rrp_inc_price = rrp_inc_gst*(cost_price/cost_weight)
                $(`#rrp_inc_gst_price`).val(rrp_inc_price.toFixed(2))
            }
        })

        // Initial setup for existing rows
        $('#dynamicfreight tbody tr.freight-row').each(function() {
            calculateFrefightDetails($(this));
        });
    });
</script>