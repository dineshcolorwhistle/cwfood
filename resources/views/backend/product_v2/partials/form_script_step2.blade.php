<script>
    $(function () {
        $('#dynamicIngredients tbody').sortable({
            handle: '.drag-handle',
            items: 'tr.ingredient-row',
            placeholder: 'row-placeholder'
        });
    });

    // JavaScript for product form step 2
    $(document).ready(function() {
        //let i = 0;
        let default_component = $(`input[name="default_component"]`).val();
        let i = $('#dynamicIngredients tbody tr.ingredient-row').length - 1;
        $("#add_ingredient").click(function() {
            ++i;
            $('#no_ingredients_row').remove();
            
            let newRow = `
                <tr class="ingredient-row">
                    <td class="drag-handle" draggable="true">
                     <input type="hidden" name="IngFields[${i}][id]" id="id${i}" />   
                        <div style="display: flex;flex-direction: column;position: absolute;z-index: 1;top: 20px; left: 10px;cursor: pointer;">   
                            <span class="material-symbols-outlined">drag_indicator</span>
                        </div>
                        <select name="IngFields[${i}][ing_id]" id="ing_id${i}" class="form-select select2input ingname_selection" required>
                           <option value="">--Select Ingredient--</option>
                                        @foreach($ingredients as $ingredient)
                                        @php
                                        $sequenceNumber = is_numeric($ingredient->ing_image) ? (int)$ingredient->ing_image : null;
                                        $ing_image = getModuleImage('raw_material', $ingredient->id, $sequenceNumber);                                        
                                        @endphp
                                        <option
                                            value="{{ $ingredient->id }}"
                                            data-ingredient-name="{{ $ingredient->name_by_kitchen }}"
                                            data-ingredient-sku="{{ $ingredient->ing_sku }}"
                                            data-ingredient-image="{{ $ing_image }}"
                                            data-ingredient-unit="{{ $ingredient->purchase_unit}}"
                                            data-specific-gravity="{{ $ingredient->specific_gravity}}"
                                            data-ingredient-supplier = "{{ $ingredient->supplier->company_name ?? "" }}"
                                            >
                                             {{ $ingredient->name_by_kitchen }} {{$ingredient->ing_sku }}
                                        </option>
                                        @endforeach
                        </select>
                    </td>

                    <td class="text-right">
                        <p></p>
                    </td>

                    <td class="text-right">
                        <input type="text" name="IngFields[${i}][quantity_weight]" data-ing-value="" class="form-control text-end" step="0.01" min="0" required />
                    </td>
                    <td>
                        <select class="ingunit_section form-select" name="IngFields[${i}][units_g_ml]"  id="units_g_ml_${i}">
                            <option value="g">g</option>
                            <option value="kg">kg</option>
                            <option value="ml">mL</option>
                            <option value="l">L</option>
                        </select>
                    </td>
                    <td>
                        <select name="IngFields[${i}][component]" id="component_${i}" class="form-select" required>
                            <option @if(old('IngFields.0.component') == null) selected @endif disabled>Select Component</option>
                            @foreach($recipe_components as $component)
                                <option value="{{$component->name}}" @if(old('IngFields.0.component') == $component->name) @endif  @if($component->id == $default_component) selected @endif>{{$component->name}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" name="IngFields[${i}][kitchen_comments]" id="kitchen_comments_${i}" class="form-control" />
                    </td>
                    <td class="text-center">
                        <div class="remove-ingredient delete-icon cursor-pointer text-danger"><i class="material-symbols-outlined">delete</i></div>
                    </td>
                </tr>
            `;

            $("#dynamicIngredients tbody").append(newRow);
            
            // Initialize select2 only for the new element
            const select2Element = $(`#ing_id${i}`);
            
            // Destroy existing select2 if it exists
            if (select2Element.hasClass('select2-hidden-accessible')) {
                select2Element.select2('destroy');
            }
            
            // Initialize select2 for the new element
            select2Element.select2({
                width: '100%',
                dropdownParent: $('#form_step_2'),
                templateResult: formatResult,
                templateSelection: formatResult
            });

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

            setTimeout(() => {
                select2Element.select2('open');

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


        function initializeSelect2() {
            // Only initialize select2 for elements that haven't been initialized yet
            $('.select2input').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        width: '100%',
                        dropdownParent: $('#form_step_2'),
                        templateResult: formatResult,
                        templateSelection: formatResult
                    });
                }
            });
        }

        function formatResult(state) {
            if (!state.id) {
                return state.text;
            }
            var ingredientName = $(state.element).data('ingredient-name');
            var ingredientSku = $(state.element).data('ingredient-sku');
            var ingredientImage = $(state.element).data('ingredient-image');
            var $container = $(
                "<div class='ingimagesection'>" +
                "<img src='" + ingredientImage + "' alt='image' />" +
                "</div>" +
                "<div class='inginfo_section'>" +
                "<div class='ingname'>" + ingredientName + "</div>" +
                "<div class='ingrsku'>" + ingredientSku + "</div>" +
                "</div>"
            );
            return $container;
        }

        function formatSelection(state) {
            if (!state.id) {
                return state.text;
            }
            return $(state.element).data('ingredient-name');
        }

        // Initialize Select2 for existing rows (only if tab is active)
        if ($('#tab-step-2').hasClass('active')) {
            initializeSelect2();
        }
        
        // Create initialization function for step 2
        window.initStep2Scripts = function() {
            initializeSelect2();
        };

        // Attach 'change' event listener to all input elements with class 'unit_weight_input'

        let product = "{{$product->id}}";
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

        $('#form_step_2').on('change input', 'input, select, textarea', function () {
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
        $(document).on('input', '#recipe_oven_temp , #serv_per_package', function() {
            restrictNonNumeric($(this));
        });
        $(document).on('change', '#recipe_oven_temp, #serv_per_package', function() {
            var recipe_oven_temp = parseFloat(removeCommas($('#recipe_oven_temp').val())).toFixed(0) || 0;
            var serv_per_package = parseFloat(removeCommas($('#serv_per_package').val())).toFixed(0) || 0;
            $('#recipe_oven_temp').val(formatWithCommas(recipe_oven_temp));
            $('#serv_per_package').val(formatWithCommas(serv_per_package));
        });

        $(document).on('input', 'input[name$="[quantity_weight]"], #serv_size_g', function() {
            NonNumericDecimal($(this));
        });

        // Handle ingredient selection or quantity change
        $(document).on('change', '.ingname_selection, input[name$="[quantity_weight]"], #batch_baking_loss_percent, #serv_per_package ,#serv_size_g', function() {
            var serv_per_package = parseFloat(removeCommas($('#serv_per_package').val())) || 0;
            $('#serv_per_package').val(formatWithCommas(serv_per_package));
            $('.serv_per_package_text').text(formatWithCommas(serv_per_package));

            var serv_size_g = parseFloat(removeCommas($('#serv_size_g').val())) || 0;
            $('#serv_size_g').val(formatWithCommas(serv_size_g));
            $('.serv_size_g_text').text(formatWithCommas(serv_size_g));
            
            if ($(this).is('input[name$="[quantity_weight]"]')) {
                let quantityWeightValue = $(this).val(); // Get the value of the input
                $(this).data('ing-value',quantityWeightValue)
            }

            if ($(this).hasClass('ingname_selection')) {
                let supplier = $(this).find('option:selected').data('ingredient-supplier')
                $(this).closest('td').next('td').find('p').text(supplier);
            }
            
            updateNutritionTable();
        });

        $(document).on("focus", 'select[name$="[units_g_ml]"]',function() {
            previousValue = $(this).val();
        }).on('change', 'select[name$="[units_g_ml]"]', function() {
            let currentValue = $(this).val();
            let ing_input = $(this).closest('tr').find('input[name$="[quantity_weight]"]');
            let input_val = parseFloat(removeCommas(ing_input.val()))
            const specGrav = $(this).closest('tr').find(':selected').data('specific-gravity');
            const specificGravity = (specGrav && specGrav != 0) ? specGrav : 1;
            if(currentValue == "kg"){
                ing_input.data('ing-value',(input_val*1000))
            }else if(currentValue == "ml"){
                console.log(input_val);
                console.log(specificGravity);
                ing_input.data('ing-value',(input_val*specificGravity))
            }else if(currentValue == "l"){
                ing_input.data('ing-value',((input_val*specificGravity)*1000))
            }else if(currentValue == "g"){
                ing_input.data('ing-value',input_val)
            }

            // if(previousValue == "g"){
            //     if(currentValue == "kg"){
            //         ing_input.data('ing-value',(input_val*1000))
            //     }else if(currentValue == "ml"){
            //         ing_input.data('ing-value',(input_val*specificGravity))
            //     }else if(currentValue == "l"){
            //         ing_input.data('ing-value',((input_val*specificGravity)*1000))
            //     }
            // }else if(previousValue == "kg"){
            //     if(currentValue == "g"){                    
            //         ing_input.data('ing-value',(input_val/1000))
            //     }else if(currentValue == "ml"){
            //         ing_input.data('ing-value',((input_val*specificGravity)/1000))
            //     }else if(currentValue == "l"){
            //         ing_input.data('ing-value',(input_val*specificGravity))
            //     }
            // }else if(previousValue == "ml"){
            //     if(currentValue == "g"){
            //         ing_input.data('ing-value',(input_val/specificGravity))
            //     }else if(currentValue == "kg"){
            //         ing_input.data('ing-value',((input_val*1000)/specificGravity))
            //     }else if(currentValue == "l"){
            //         ing_input.data('ing-value',(input_val*1000))
            //     }
            // }else if(previousValue == "l"){
            //     if(currentValue == "g"){
            //         ing_input.data('ing-value',((input_val/specificGravity)*1000))
            //     }else if(currentValue == "kg"){
            //         ing_input.data('ing-value',(input_val/specificGravity))
            //     }else if(currentValue == "ml"){
            //         ing_input.data('ing-value',(input_val/1000))
            //     }
            // }
            previousValue = currentValue;
            updateNutritionTable();
        });


        function collectIngredientData() {
            const ingredients = [];
            $('.ingname_selection').each(function(index) {
                const ingId = $(this).val();
                const quantity_input = $(this).closest('tr').find('input[name$="[quantity_weight]"]');
                const quantity = quantity_input.val();
                const quantity_data_attr = quantity_input.data('ing-value');
                const unit_input = $(this).closest('tr').find('select[name$="[units_g_ml]"]');
                const unit = $(this).closest('tr').find('select[name$="[units_g_ml]"] option:selected').val();
                const component = $(this).closest('tr').find('select[name$="[component]"]').val();
                const specificGravity = $(this).find(':selected').data('specific-gravity');                
                if (ingId) {
                    ingredients.push({
                        id: ingId,
                        index: index, // Store the order of appearance
                        quantity: parseFloat(removeCommas(quantity_data_attr)) || 0,
                        unit: unit,
                        specific_gravity: specificGravity,
                        component: component || ''
                    });
                }
                quantity_input.val(formatWithCommas(removeCommas(quantity)));
            });
            return ingredients;
        }

        function updateNutritionTable() {
            const ingredientData = collectIngredientData();
            let product_id = $(`input[name="product_id"]`).val()
            // console.log('ingredient data', ingredientData);

            if (ingredientData.length === 0) return;

            $.ajax({
                url: '{{ route("products.calculateNutrition") }}',
                type: 'POST',
                data: {
                    'product_id':product_id,
                    ingredients: ingredientData,
                    batch_baking_loss_percent: $('#batch_baking_loss_percent').val() || 0,
                },
                success: function(response) {
                    if (response.success) {
                        renderNutritionTable(response.data);
                        updateNutritionDisplayArea(response.data.totals);
                        // $(`#costing_component_container`).html(response.data.costing_html);
                        // $(`#analysis_component_container`).html(response.data.analysis_html);
                    } else {
                        alert('Error calculating nutrition data: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    // console.error('AJAX Error:', status, error);
                    alert('Error calculating nutrition data. Please try again.');
                }
            });
        }

        function renderNutritionTable(data) {
            console.log(data);
            $('#labelling_ingredients').val(data.labelling_ingredients);
            $('#labelling_allergens').val(data.labelling_allergens);
            $('#labelling_may_contain').val(data.labelling_may_contain);

            // Number formatting helper function
            const formatNumber = (value, type) => {
                if (typeof value !== 'number') return '-';

                switch (type) {
                    case 'weight': // For gram values
                        return value >= 100 ?
                            value.toLocaleString('en-US', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }) :
                            value.toLocaleString('en-US', {
                                minimumFractionDigits: 1,
                                maximumFractionDigits: 1
                            });
                    case 'percentage':
                        return value.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    case 'energy':
                        return value.toLocaleString('en-US', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        });
                    case 'sodium':
                        return value.toLocaleString('en-US', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        });
                    default:
                        return value.toLocaleString('en-US', {
                            minimumFractionDigits: 1,
                            maximumFractionDigits: 1
                        });
                }
            };

            const tableHtml = `
        <table class="table ingredient_nutri_table">
        <thead>
            <tr>
                <th class="align-bottom text-primary-blue" width="15%">Ingredient Name</th>
                <th class="align-bottom text-primary-blue text-end">Quantity Before Loss/Gain <br>(g)</th>
                <th class="align-bottom text-primary-blue text-end">Quantity After Loss/Gain <br>(g)</th>
                <th class="align-bottom text-primary-blue text-end">Mix <br>(%)</th>
                <th class="align-bottom text-primary-blue text-end">Cost <br>($)</th>
                <th class="align-bottom text-primary-blue text-end">Australian <br>(%)</th>
                <th class="align-bottom text-primary-blue text-end">Energy <br>(kJ)</th>
                <th class="align-bottom text-primary-blue text-end">Protein <br>(g)</th>
                <th class="align-bottom text-primary-blue text-end">Total Fat <br>(g)</th>
                <th class="align-bottom text-primary-blue text-end">Saturated Fat <br>(g)</th>
                <th class="align-bottom text-primary-blue text-end">Carbohydrate <br>(g)</th>
                <th class="align-bottom text-primary-blue text-end">Total Sugar <br>(g)</th>
                <th class="align-bottom text-primary-blue text-end">Sodium <br>(mg)</th>
            </tr>
        </thead>
        <tbody>
            ${data.rows.map(row => `
                <tr>
                    <td class="align-middle text-primary-dark-mud">
                        <div class="ingredient_nutri_table_image">
                        {{--
                            <div class="ing_image_area">
                                <img src="${row.image}" alt="Ingredient Image" class="ing-image">
                            </div>
                            --}}
                            <div class="ing_info_area align-middle">   
                                <div class="ing_name_area">
                                ${row.name}
                                </div> 
                                {{--
                                <div class="ing_sku_area">
                                ${row.sku}
                                </div> 
                                --}}
                            </div>
                        </div>
                    </td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.quantity, 'weight')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.net_quantity, 'weight')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.mix_percent, 'percentage')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.amount, 'percentage')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(parseFloat(row.australian_percent) || 0, 'percentage')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.energy_kj, 'energy')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.protein_g, 'weight')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.fat_total_g, 'weight')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.fat_saturated_g, 'weight')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.carbohydrate_g, 'weight')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.sugars_g, 'weight')}</td>
                    <td class="align-middle text-primary-dark-mud" style="text-align: right;">${formatNumber(row.sodium_mg, 'sodium')}</td>
                </tr>
            `).join('')}
            <tr class="table-secondary">
                <td class="align-middle fw-bold primary-text-dark" >Total per Recipe</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.totals.quantity, 'weight')}</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.totals.net_quantity, 'weight')}</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">100.00</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.total_final_value, 'percentage')}</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.total_aus_percent, 'percentage')}</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
            </tr>
            <tr class="table-secondary">
                <td class="align-middle fw-bold primary-text-dark" >Nutrition per 100g</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;"></td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.totals.energy_kj, 'energy')}</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.totals.protein_g, 'weight')}</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.totals.fat_total_g, 'weight')}</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.totals.fat_saturated_g, 'weight')}</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.totals.carbohydrate_g, 'weight')}</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.totals.sugars_g, 'weight')}</td>
                <td class="align-middle fw-bold primary-text-dark" style="text-align: right;">${formatNumber(data.totals.sodium_mg, 'sodium')}</td>
            </tr>
        </tbody>
        </table>
        `;

            $('.nutrition_table_section').html(tableHtml);
        }

        function updateNutritionDisplayArea(totals) {

            $('#energy_per_100g').text(formatWithCommas(totals.energy_kj.toFixed(0)));
            $('#energy_kJ_per_100g').val(totals.energy_kj.toFixed(0));

            $('#protein_per_100g').text(formatWithCommas(totals.protein_g.toFixed(1)));
            $('#protein_g_per_100g').val(totals.protein_g.toFixed(1));

            $('#fat_total_per_100g').text(formatWithCommas(totals.fat_total_g.toFixed(1)));
            $('#fat_total_g_per_100g').val(totals.fat_total_g.toFixed(1));

            $('#fat_saturated_per_100g').text(formatWithCommas(totals.fat_saturated_g.toFixed(1)));
            $('#fat_saturated_g_per_100g').val(totals.fat_saturated_g.toFixed(1));

            $('#carbohydrate_per_100g').text(formatWithCommas(totals.carbohydrate_g.toFixed(1)));
            $('#carbohydrate_g_per_100g').val(totals.carbohydrate_g.toFixed(1));

            $('#sugars_per_100g').text(formatWithCommas(totals.sugars_g.toFixed(1)));
            $('#sugar_g_per_100g').val(totals.sugars_g.toFixed(1));

            $('#sodium_per_100g').text(formatWithCommas(totals.sodium_mg.toFixed(0)));
            $('#sodium_mg_per_100g').val(totals.sodium_mg.toFixed(0));

            const serv_size_g = parseFloat(removeCommas($('#serv_size_g').val())) || 0;

            // Update per serving values and hidden fields
            $('#energy_per_serving').text(formatWithCommas((totals.energy_kj * serv_size_g / 100).toFixed(0)));
            $('#energy_kJ_per_serve').val((totals.energy_kj * serv_size_g / 100).toFixed(0));

            $('#protein_per_serving').text(formatWithCommas((totals.protein_g * serv_size_g / 100).toFixed(1)));
            $('#protein_g_per_serve').val((totals.protein_g * serv_size_g / 100).toFixed(1));

            $('#fat_total_per_serving').text(formatWithCommas((totals.fat_total_g * serv_size_g / 100).toFixed(1)));
            $('#fat_total_g_per_serve').val((totals.fat_total_g * serv_size_g / 100).toFixed(1));

            $('#fat_saturated_per_serving').text(formatWithCommas((totals.fat_saturated_g * serv_size_g / 100).toFixed(1)));
            $('#fat_saturated_g_per_serve').val((totals.fat_saturated_g * serv_size_g / 100).toFixed(1));

            $('#carbohydrate_per_serving').text(formatWithCommas((totals.carbohydrate_g * serv_size_g / 100).toFixed(1)));
            $('#carbohydrate_g_per_serve').val((totals.carbohydrate_g * serv_size_g / 100).toFixed(1));

            $('#sugars_per_serving').text(formatWithCommas((totals.sugars_g * serv_size_g / 100).toFixed(1)));
            $('#sugar_g_per_serve').val((totals.sugars_g * serv_size_g / 100).toFixed(1));

            $('#sodium_per_serving').text(formatWithCommas((totals.sodium_mg * serv_size_g / 100).toFixed(0)));
            $('#sodium_mg_per_serve').val((totals.sodium_mg * serv_size_g / 100).toFixed(0));

            $('#batch_after_waste_g').val(totals.net_quantity.toFixed(0));

        }

        // Initialize table on page load if ingredients exist
        if ($('.ingname_selection').length > 0) {
            updateNutritionTable();
            displayNutritionComponent();
        }

        $(document).on('click', '.remove-ingredient', function() {
            const row = $(this).closest('.ingredient-row');
            const prodIngredientId = row.find('input[name$="[id]"]').val();

            // Check if it's an existing saved entry or a new dynamically added row
            if (prodIngredientId) {
                // Show SweetAlert confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to remove this ingredient entry?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Ajax call to remove ingredient entry
                        $.ajax({
                            url: "{{ route('products.remove-ingredient') }}",
                            method: 'POST',
                            data: {
                                prod_ingredient_id: prodIngredientId,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Remove the row from the table
                                    row.remove();

                                    updateNutritionTable();
                                } else {
                                    console.log(response.message);
                                }
                            },
                            error: function(xhr) {
                                console.log(xhr.responseJSON.message);
                            }
                        });
                    }
                });
            } else {
                // For dynamically added rows not yet saved to database
                row.remove();
                setTimeout(function() {
                    updateNutritionTable();
                }, 500);
            }
        });

        $(".number-input").on("input", function () {
            restrictNonNumericOne($(this));
        });

        $("#batch_baking_loss_percent").on("input", function () {
            let value = $(this).val();

            // Allow only numbers, a single dot (.), and a minus (-) at the start
            value = value.replace(/[^0-9.-]/g, "");  // Remove invalid characters
            value = value.replace(/(?!^)-/g, "");   // Allow only one minus at the beginning

            // Ensure only one decimal point
            let parts = value.split(".");
            if (parts.length > 2) {
                value = parts[0] + "." + parts.slice(1).join(""); 
            }

            // Ensure only one digit after the decimal
            if (value.includes(".")) {
                let [integerPart, decimalPart] = value.split(".");
                decimalPart = decimalPart.substring(0, 1); // Limit to 1 decimal place
                value = `${integerPart}.${decimalPart}`;
            }

            // Prevent the user from only entering "-"
            if (value === "-") {
                $(this).val(value);
                return;
            }

            // Ensure value is within the allowed range (-100 to 100) **without modifying it**
            let n = parseFloat(value);
            if (!isNaN(n) && (n < -100 || n > 100)) {
                value = value.slice(0, -1); // Remove last entered character
            }

            $(this).val(value);
        });

        function displayNutritionComponent() {
            const ingredientData = collectIngredientData();
            let product_id = $(`input[name="product_id"]`).val()
            if (ingredientData.length === 0) return;
            $.ajax({
                url: '{{ route("products.displayNutrition") }}',
                type: 'POST',
                data: {
                    'product_id':product_id,
                    ingredients: ingredientData,
                    batch_baking_loss_percent: $('#batch_baking_loss_percent').val() || 0,
                    
                },
                success: function(response) { 
                    if (response.success) {
                        $(`#costing_component_container`).html(response.data.costing_html);
                        $(`#analysis_component_container`).html(response.data.analysis_html);
                    } else {
                        console.error('Error:', response.message);
                        alert('Error calculating nutrition data: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Error calculating nutrition data. Please try again.');
                }
            });
        }
    });


    // $('.recipe_oven_time_input').on('input', function(e) {
    //     let value = $(this).val().replace(/\D/g, '');

    //     if (value.length > 4) {
    //         value = value.substr(0, 4);
    //     }

    //     if (value.length >= 2) {
    //         const minutes = value.substr(0, value.length - 2);
    //         const seconds = value.substr(value.length - 2);
    //         value = minutes + ':' + seconds;
    //     }

    //     $(this).val(value);
    // });

    // $('.recipe_oven_time_input').on('blur', function(e) {
    //     let value = $(this).val();
    //     if (value) {
    //         let parts = value.split(':');
    //         if (parts.length === 2) {
    //             let minutes = parts[0].padStart(2, '0');
    //             let seconds = parts[1].padStart(2, '0');
    //             $(this).val(minutes + ':' + seconds);
    //         }
    //     }
    // });

    function formatTime(timeInput) {

        intValidNum = timeInput.value;

        if (intValidNum < 24 && intValidNum.length == 2) {
            timeInput.value = timeInput.value + ":";
            return false;  
        }
        if (intValidNum == 24 && intValidNum.length == 2) {
            timeInput.value = timeInput.value.length - 2 + "0:";
            return false;
        }
        if (intValidNum > 24 && intValidNum.length == 2) {
            timeInput.value = "";
            return false;
        }

        if (intValidNum.length == 5 && intValidNum.slice(-2) < 60) {
        timeInput.value = timeInput.value + ":";
        return false;
        }
        if (intValidNum.length == 5 && intValidNum.slice(-2) > 60) {
        timeInput.value = timeInput.value.slice(0, 2) + ":";
        return false;
        }
        if (intValidNum.length == 5 && intValidNum.slice(-2) == 60) {
        timeInput.value = timeInput.value.slice(0, 2) + ":00:";
        return false;
        }


        if (intValidNum.length == 8 && intValidNum.slice(-2) > 60) {
        timeInput.value = timeInput.value.slice(0, 5) + ":";
        return false;
        }
        if (intValidNum.length == 8 && intValidNum.slice(-2) == 60) {
        timeInput.value = timeInput.value.slice(0, 5) + ":00";
        return false;
        }
    }


    $(document).on('click', '.ingr_top', function () {
    var row = $(this).closest('.ingredient-row');
    var prevRow = row.prev('.ingredient-row');
    if (prevRow.length) {
        row.insertBefore(prevRow);
    }
});

$(document).on('click', '.ingr_down', function () {
    var row = $(this).closest('.ingredient-row');
    var nextRow = row.next('.ingredient-row');
    if (nextRow.length) {
        row.insertAfter(nextRow);
    }
});


    // $(document).ready(function () {
    //     function updateUnitSelection(row) {
    //         let selectedOption = row.find(".ingname_selection option:selected");
    //         let ingredientUnit = selectedOption.data("ingredient-unit");
    //         let unitSelect = row.find(".ingunit_section");

    //         if (ingredientUnit) {
    //             unitSelect.val(ingredientUnit).change();
    //         }
    //     }

    //     // Run on page load for each row
    //     $(".ingredient-row").each(function () {
    //         updateUnitSelection($(this));
    //     });

    //     // Run on ingredient selection change
    //     $(document).on("change", ".ingname_selection", function () {
    //         let row = $(this).closest("tr");
    //         updateUnitSelection(row);
    //     });
    // });
</script>