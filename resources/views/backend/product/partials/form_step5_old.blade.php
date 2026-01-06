<!-- Form for updating product details in step 5 -->
<form id="form_step_5" action="{{ route('products.updateStep5', $product) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mt-3">
        <div class="col-lg-10 col-md-10 col-sm-12 col-12">
            <input type="hidden" name="product_id" id="product_id" value="{{ $product->id }}">
            <input type="hidden" id="machineryIndex" value="{{ count($prod_machinery) > 0 ? count($prod_machinery) - 1 : 0 }}">
            <table class="table responsiveness" id="dynamicMachinery">
                <thead>
                    <tr>
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
                            @if(!$loop->first)
                            <div class="remove-machinery delete-icon cursor-pointer text-danger"><i class="material-symbols-outlined">delete</i></div>
                            @endif
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
        <div class="col-lg-2 col-md-2 col-sm-12 col-12">
            <div class="button-row grid-btn">
                <a href="{{ route('products.edit', ['product' => $product->id, 'step' => 4]) }}"
                    class="btn btn-secondary-blue mb-0 me-1 js-btn-previous" title="Previous">Back</a>
                <button class="btn btn-secondary-blue mb-0 me-1 js-btn-save" type="button" title="Save">Save</button>
                <button class="btn btn-secondary-blue mb-0 js-btn-next" type="button" title="Next">Next</button>
            </div>
            <div class="mt-4">
                <x-product-gridcard :product="$product" />
                <x-recipe-details :product="$product" />
                <x-tags-card :product="$product" />
            </div>
        </div>
    </div>
</form>