<style>
    table#cost_marign thead th,table#dynamicfreight thead th,table#dynamicLabours thead th,table#dynamicMachinery thead th, table#dynamicPackaging thead th,.products.form-wizard .input-group label.form-label{color:var(--bs-black) !important}
    div#custom-text p { font-size: 11px; color: #808080ab !important; }
    .drag-handle {cursor: move;}
    .row-placeholder {background: var(--secondary-light-primary-color-10);border: 2px dashed var(--secondary-primary-color);height: 50px;}
</style>

<!-- Form for updating product details in step 4 -->
<form id="form_step_4" action="{{ route('products.updateStep4', $product) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mt-3">
        <div class="col-lg-10 col-md-10 col-sm-12 col-12">
            <div class="row">
                <h4 style="color: var(--secondary-color);">Costing</h4>
                <div class="col-lg-6 col-md-6 col-sm-12 col-12 mt-4">
                    <label class="form-label">Manufacturer Costs</label>
                    <div class="input-group input-group-dynamic">
                        <table class="table table-borderless responsiveness no-border">
                            <tbody>
                                <tr>
                                    <td><label class="primary-text-dark">Direct Cost Contingency</label></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="text" name="contingency" id="contingency" value="{{ old('contingency', $product->contingency ?? '') }}" class="form-control numeric-input text-end" placeholder="">
                                            <p class="m-0">%</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label class="primary-text-dark">Retailer Charges</label></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="text" name="retailer_charges" id="retailer_charges" value="{{ old('retailer_charges', $product->retailer_charges ?? '') }}" class="form-control numeric-input text-end" placeholder="">
                                            <p class="m-0">%</p>
                                        </div>
                                    </td>
                                </tr>   
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <input type="hidden" name="product_id" id="product_id" value="{{ $product->id }}">
            <input type="hidden" id="labourIndex" value="{{ count($prod_labours) > 0 ? count($prod_labours) - 1 : 0 }}">
            <label class="form-label mt-3">Labour</label>
            <div class="Labour_table mt-3">
                <table class="table responsiveness" id="dynamicLabours">
                    <thead>
                        <tr>
                            <th class="text-primary-dark-mud" width="3%"></th>
                            <th class="text-primary-dark-mud" width="15%">Labour</th>
                            <th class="text-primary-dark-mud text-end" width="8%">People <br>(#)</th>
                            <th class="text-primary-dark-mud text-end" width="10%">Hours</th>
                            <th class="text-primary-dark-mud text-end" width="10%">People Hours</th>
                            <th class="text-primary-dark-mud text-end" width="8%">$/Hour</th>
                            <th class="text-primary-dark-mud text-end" width="12%">Total Cost</th>
                            <th class="text-primary-dark-mud" width="10%">Product Units</th>
                            <th class="text-primary-dark-mud text-end" width="11%">Weight</th>
                            <th class="text-primary-dark-mud text-end" width="10%">Cost <br>($ / kg)</th>
                            <th class="text-primary-dark-mud text-center" width="6%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prod_labours as $index => $prod_labour)
                        <tr class="labour-row">
                            <td class="drag-handle" draggable="true">
                                <div style="display: flex;flex-direction: column;top: 20px; left: 10px;cursor: pointer;">   
                                    <span class="material-symbols-outlined">drag_indicator</span>
                                </div>
                            </td>
                            <td>
                                <input type="hidden" name="LabourFields[{{ $index }}][id]" value="{{ $prod_labour->id }}" />
                                <select name="LabourFields[{{ $index }}][labour_id]" class="form-select simple_select2 labour-selection" required>
                                    <option value="">--Select Labour--</option>
                                    @foreach($labours as $labour)
                                    <option value="{{ $labour->id }}" {{ $prod_labour->labour_id == $labour->id ? 'selected' : '' }}>
                                        {{ $labour->labour_type }}
                                    </option>
                                    @endforeach
                                </select>

                            </td>
                            <td>
                                <input type="text" name="LabourFields[{{ $index }}][people_count]"
                                    class="form-control text-end" min="1"
                                    value="{{ $prod_labour->people_count }}" required />
                            </td>
                            <td>
                                <input type="text" name="LabourFields[{{ $index }}][hours_per_person]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_labour->hours_per_person && $prod_labour->hours_per_person != 0 ? $prod_labour->hours_per_person : '' }}"
                                    placeholder="0.00" required />
                                    
                            </td>
                            <td>
                                <input type="text" name="LabourFields[{{ $index }}][people_hours]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_labour->people_hours }}" readonly />
                            </td>
                            <td>
                                <input type="text" name="LabourFields[{{ $index }}][hourly_rate]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_labour->hourly_rate }}" readonly />
                            </td>
                            <td>
                                <input type="text" name="LabourFields[{{ $index }}][tot_cost]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="" readonly />
                            </td>
                            <td>
                                <select name="LabourFields[{{ $index }}][labour_units]" class="form-select" required>
                                    <option value="1" {{ $prod_labour->product_units == '1' ? 'selected' : '' }}>Individual unit</option>
                                    <option value="2" {{ $prod_labour->product_units == '2' ? 'selected' : '' }}>Selling unit</option>
                                    <option value="3" {{ $prod_labour->product_units == '3' ? 'selected' : '' }}>Batch</option>
                                    <option value="4" {{ $prod_labour->product_units == '4' ? 'selected' : '' }}>Carton</option>
                                    <option value="5" {{ $prod_labour->product_units == '5' ? 'selected' : '' }}>Pallet</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="LabourFields[{{ $index }}][tot_weight]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="" readonly />
                            </td>
                            <td>
                                <input type="text" name="LabourFields[{{ $index }}][cost_per_kg]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_labour->cost_per_kg }}" readonly />
                            </td>
                            <td class="text-center">
                                <div class="remove-labour delete-icon cursor-pointer text-danger"><i class="material-symbols-outlined">delete</i></div>
                            </td>
                        </tr>
                        @empty
                        <tr id="no_labour_row">
                            <td colspan="7" class="text-center">No labour added yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr id="labour-total-row" class="table-secondary">
                            <td></td>
                            <td class="fw-bold primary-text-dark text-center">Total</td>
                            <td class="fw-bold text-end primary-text-dark">
                                <div id="total-people-count" class="hidden">0.00</div>
                            </td>
                            <td class="fw-bold text-end primary-text-dark">
                                <div id="total-hours-per-person" class="hidden">0.00</div>
                            </td>
                            <td class="fw-bold text-end primary-text-dark">
                                <div id="total-people-hours" class="hidden">0.00</div>
                            </td>
                            <td class="fw-bold text-end primary-text-dark">
                                <div id="avg-hourly-rate" class="hidden">0.00</div>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="fw-bold text-end primary-text-dark">
                                <div id="total-cost-per-kg">0.00</div>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>

                </table>
                <div class="text-end mt-3">
                    <button type="button" id="add_labour" class="btn btn-sm btn-primary-orange plus-icon">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>
            <!-- Machinery -->
            <input type="hidden" name="product_id" id="product_id" value="{{ $product->id }}">
            <input type="hidden" id="machineryIndex" value="{{ count($prod_machinery) > 0 ? count($prod_machinery) - 1 : 0 }}">
            <label class="form-label mt-3">Machinery</label>
            <div class="Machinery-table mt-3">
                <table class="table responsiveness" id="dynamicMachinery">
                    <thead>
                        <tr>
                            <th class="text-primary-orange" width="3%"></th>
                            <th class="text-primary-orange" width="22%">Machine Type</th>
                            <th class="text-primary-orange text-end" width="12%">Hours</th>
                            <th class="text-primary-orange text-end" width="12%">$/Hour</th>
                            <th class="text-primary-orange text-end" width="12%">Total Cost</th>
                            <th class="text-primary-orange" width="12%">Product Units</th>
                            <th class="text-primary-orange text-end" width="12%">Weight</th>
                            <th class="text-primary-orange text-end" width="12%">Cost <br>($ / kg)</th>
                            <th class="text-primary-orange text-center" width="6%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prod_machinery as $index => $prod_machine)
                        <tr class="machinery-row">
                            <td class="drag-handle" draggable="true">
                                <div style="display: flex;flex-direction: column;top: 20px; left: 10px;cursor: pointer;">   
                                    <span class="material-symbols-outlined">drag_indicator</span>
                                </div>
                            </td>
                            <td>
                                <input type="hidden" name="MachineryFields[{{ $index }}][id]" value="{{ $prod_machine->id }}" />
                                <select name="MachineryFields[{{ $index }}][machinery_id]" class="form-select simple_select2 machinery-selection" required>
                                    <option value="">--Select Machinery--</option>
                                    @foreach($machinery as $machine)
                                    <option value="{{ $machine->id }}"
                                        {{ $prod_machine->machinery_id == $machine->id ? 'selected' : '' }}>
                                        {{ $machine->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="MachineryFields[{{ $index }}][hours]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_machine->hours }}" required />
                            </td>
                            <td>
                                <input type="text" name="MachineryFields[{{ $index }}][cost_per_hour]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_machine->cost_per_hour }}" readonly />
                            </td>
                            <td>
                                <input type="text" name="MachineryFields[{{ $index }}][tot_cost]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="" readonly />
                            </td>
                            <td>
                                <select name="MachineryFields[{{ $index }}][machine_units]" class="form-select" required>
                                    <option value="1" {{ $prod_machine->product_units == '1' ? 'selected' : '' }}>Individual unit</option>
                                    <option value="2" {{ $prod_machine->product_units == '2' ? 'selected' : '' }}>Selling unit</option>
                                    <option value="3" {{ $prod_machine->product_units == '3' ? 'selected' : '' }}>Batch</option>
                                    <option value="4" {{ $prod_machine->product_units == '4' ? 'selected' : '' }}>Carton</option>
                                    <option value="5" {{ $prod_machine->product_units == '5' ? 'selected' : '' }}>Pallet</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="MachineryFields[{{ $index }}][weight]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_machine->weight }}" readonly />
                            </td>
                            <td>
                                <input type="text" name="MachineryFields[{{ $index }}][cost_per_kg]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_machine->cost_per_kg }}" readonly />
                            </td>
                            <td class="text-center">
                                <div class="remove-machinery delete-icon cursor-pointer text-danger"><i class="material-symbols-outlined">delete</i></div>
                            </td>
                        </tr>
                        @empty
                        <tr id="no_machinery_row">
                            <td colspan="7" class="text-center">No machinery added yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr id="machinery-total-row" class="table-secondary">
                            <td></td>
                            <td class="fw-bold primary-text-dark text-center">Total</td>
                            <td class="fw-bold text-end primary-text-dark">
                                <div id="total-hours" class="hidden">0.00</div>
                            </td>
                            <td class="fw-bold text-end primary-text-dark">
                                <div id="avg-cost-per-hour" class="hidden">0.00</div>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="fw-bold text-end primary-text-dark">
                                <div id="total-machinery-cost-per-kg">0.00</div>
                            </td>
                        </tr>

                    </tfoot>
                </table>
                <div class="text-end mt-3">
                    <button type="button" id="add_machinery" class="btn btn-sm btn-primary-orange plus-icon">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>
            <!-- Packaging -->
            <input type="hidden" name="product_id" id="product_id" value="{{ $product->id }}">
            <input type="hidden" id="packagingIndex" value="{{ count($prod_packaging) > 0 ? count($prod_packaging) - 1 : 0 }}">
            <label class="form-label mt-3">Packaging</label>
            <div class="packaging-table mt-3">
                <table class="table responsiveness" id="dynamicPackaging">
                    <thead>
                        <tr>
                            <th class="text-primary-orange" width="3%"></th>
                            <th class="text-primary-orange" width="22%">Packaging</th>
                            <th class="text-primary-orange" width="14%">Supplier</th>
                            <th class="text-primary-orange text-end" width="14%">Packaging Cost <br> ($ / Unit)</th>
                            <th class="text-primary-orange" width="14%">Packaging Type</th>
                            <th class="text-primary-orange text-end" width="14%">Weight <br> (g)</th>
                            <th class="text-primary-orange text-end" width="14%">Product Cost <br>($ / kg)</th>
                            <th class="text-primary-orange text-center" width="8%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prod_packaging as $index => $prod_pack)
                        @php 
                        $prod_pack = (object) $prod_pack;
                        @endphp
                        <tr class="packaging-row">
                            <td class="drag-handle" draggable="true">
                                <div style="display: flex;flex-direction: column;top: 20px; left: 10px;cursor: pointer;">   
                                    <span class="material-symbols-outlined">drag_indicator</span>
                                </div>
                            </td>
                            <td>
                                <input type="hidden" name="PackagingFields[{{ $index }}][id]" value="{{ $prod_pack->id }}" />
                                <select name="PackagingFields[{{ $index }}][packaging_id]" class="form-select simple_select2 packaging-selection" required>
                                    <option value="">--Select Packaging--</option>
                                    @foreach($packaging as $pack)
                                    <option value="{{ $pack->id }}" data-units="{{ $pack->type }}"
                                        {{ $prod_pack->packaging_id == $pack->id ? 'selected' : '' }}>
                                        {{ $pack->name }} ({{ $pack->type }})
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="PackagingFields[{{ $index }}][supplier_name]"
                                        class="form-control" step="0.01" min="0"
                                        value="{{ $prod_pack->supplier }}" readonly />
                            </td>
                            <td>
                                <input type="text" name="PackagingFields[{{ $index }}][cost_per_sell_unit]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_pack->cost_per_sell_unit }}" readonly />
                            </td>
                            <td>
                            <input type="text" id="product_units_{{ $index }}" name="PackagingFields[{{ $index }}][product_units]" 
                                    class="form-control" value="{{ $prod_pack->packaging_type }}" readonly />
                            </td>
                            <td>
                                <input type="text" name="PackagingFields[{{ $index }}][weight_per_sell_unit]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_pack->weight_per_sell_unit }}" readonly />
                            </td>
                            <td>
                                <input type="text" name="PackagingFields[{{ $index }}][cost_per_kg]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_pack->cost_per_kg }}" readonly />
                            </td>
                            <td class="text-center">
                                <div class="remove-packaging delete-icon cursor-pointer text-danger"><i class="material-symbols-outlined">delete</i></div>
                            </td>
                        </tr>
                        @empty
                        <tr id="no_packaging_row">
                            <td colspan="7" class="text-center">No packaging added yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr id="packaging-total-row" class="table-secondary">
                            <td></td>
                            <td class="fw-bold primary-text-dark text-center">Total</td>
                            <td></td>
                            <td id="total-cost-per-sell-unit" class="fw-bold text-end primary-text-dark">0.00</td>
                            <td></td>
                            <td id="total-weight-per-sell-unit" class="fw-bold text-end primary-text-dark">0.00</td>
                            <td id="total-packaging-cost-per-kg" class="fw-bold text-end primary-text-dark">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="text-end mt-3">
                    <button type="button" id="add_packaging" class="btn btn-sm btn-primary-orange plus-icon">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>

            <!-- Freight -->
            <input type="hidden" id="freightIndex" value="{{ count($prod_freights) > 0 ? count($prod_freights) - 1 : 0 }}">
            <label class="form-label mt-3">Freight</label>
            <div class="freight-table mt-3">
                <table class="table responsiveness" id="dynamicfreight">
                    <thead>
                        <tr>
                            <th class="text-primary-orange" width="3%"></th>
                            <th class="text-primary-orange" width="22%">Freight</th>
                            <th class="text-primary-orange" width="14%">Supplier</th>
                            <th class="text-primary-orange text-end" width="14%">Freight Cost <br> ($ / Unit)</th>
                            <th class="text-primary-orange" width="14%">Freight Unit</th>
                            <th class="text-primary-orange text-end" width="14%">Freight Weight <br> (g)</th>
                            <th class="text-primary-orange text-end" width="14%">Product Cost <br>($ / kg)</th>
                            <th class="text-primary-orange text-center" width="8%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prod_freights as $index => $prod_freight)
                        @php 
                        $prod_freight = (object) $prod_freight;
                        @endphp
                        <tr class="freight-row">
                            <td class="drag-handle" draggable="true">
                                <div style="display: flex;flex-direction: column;top: 20px; left: 10px;cursor: pointer;">   
                                    <span class="material-symbols-outlined">drag_indicator</span>
                                </div>
                            </td>
                            <td>
                                <input type="hidden" name="FreightFields[{{ $index }}][id]" value="{{ $prod_freight->id }}" />
                                <select name="FreightFields[{{ $index }}][freight_id]" class="form-select simple_select2 packaging-selection" required>
                                    <option value="">--Select Freight--</option>
                                    @foreach($freights as $freight)
                                    <option value="{{ $freight->id }}" data-units="{{ $freight->freight_unit }}"
                                        {{ $prod_freight->freight_id == $freight->id ? 'selected' : '' }}>
                                        {{ $freight->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="FreightFields[{{ $index }}][freight_supplier]"
                                        class="form-control" step="0.01" min="0"
                                        value="{{ $prod_freight->freight_supplier }}" readonly />
                            </td>
                            <td>
                                <input type="text" name="FreightFields[{{ $index }}][freight_cost]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_freight->freight_cost }}" readonly />
                            </td>
                            <td>
                            <input type="text" name="FreightFields[{{ $index }}][freight_units]" 
                                    class="form-control" value="{{ $prod_freight->freight_units }}" readonly />
                            </td>
                            <td>
                                <input type="text" name="FreightFields[{{ $index }}][freight_weight]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_freight->freight_weight }}" readonly />
                            </td>
                            <td>
                                <input type="text" name="FreightFields[{{ $index }}][cost_per_kg]"
                                    class="form-control text-end" step="0.01" min="0"
                                    value="{{ $prod_freight->cost_per_kg }}" readonly />
                            </td>
                            <td class="text-center">
                                <div class="remove-freight delete-icon cursor-pointer text-danger"><i class="material-symbols-outlined">delete</i></div>
                            </td>
                        </tr>
                        @empty
                        <tr id="no_freight_row">
                            <td colspan="7" class="text-center">No freight added yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr id="freight-total-row" class="table-secondary">
                            <td></td>
                            <td class="fw-bold primary-text-dark text-center">Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td id="total-freight-cost-per-kg" class="fw-bold text-end primary-text-dark">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="text-end mt-3">
                    <button type="button" id="add_freight" class="btn btn-sm btn-primary-orange plus-icon">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 col-12 mt-4">
                <div id="rawmaterial_costing_component"></div>
                <div id="direct_cost_component"></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 col-12">
            <div class="button-row d-flex flex-column gap-2">
                <button class="btn btn-secondary-blue mb-0 js-btn-save" type="button" title="Save"><i class="material-symbols-outlined me-1" style="font-size: 18px;">save</i>Save</button>
                <button class="btn btn-secondary-blue mb-0 js-btn-next" type="button" title="Next"><i class="material-symbols-outlined me-1" style="font-size: 18px;">arrow_forward</i>Next</button>
            </div>
            <div class="mt-4">
                <x-product-gridcard :product="$product" />
                <x-recipe-details :product="$product" />
                <x-tags-card :product="$product" />
                <x-product-weight :product="$product" />
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    $(document).ready(function() {
        get_priceanalysis_component();
    });
    function get_priceanalysis_component() {
        let id = "{{$product->id}}"
        $.ajax({
            url: "{{ route('products.component.direct-cost', ':id') }}".replace(':id', id),
            method: 'GET',
            success: function(response) {
                if(response.success){
                    $('#direct_cost_component').html(response.direct_cost_html)
                    if(response.rawmaterial_costing_html != ""){
                        $('#rawmaterial_costing_component').html(response.rawmaterial_costing_html)
                    }
                }
            }
        });
    }
</script>
@endpush

