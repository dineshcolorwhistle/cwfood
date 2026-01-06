<!-- Form for updating product details in step 6 -->
<form id="form_step_6" action="{{ route('products.updateStep6', $product) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mt-3">
        <div class="col-lg-10 col-md-10 col-sm-12 col-12 mobile-margin">
            <input type="hidden" name="product_id" id="product_id" value="{{ $product->id }}">
            <input type="hidden" id="packagingIndex" value="{{ count($prod_packaging) > 0 ? count($prod_packaging) - 1 : 0 }}">
            <table class="table responsiveness" id="dynamicPackaging">
                <thead>
                    <tr>
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
                            @if(!$loop->first)
                            <div class="remove-packaging delete-icon cursor-pointer text-danger"><i class="material-symbols-outlined">delete</i></div>
                            @endif
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
        <div class="col-lg-2 col-md-2 col-sm-12 col-12">
            <div class="button-row d-flex">
                <a href="{{ route('products.edit', ['product' => $product->id, 'step' => 5]) }}"
                    class="btn btn-secondary-blue mb-0 js-btn-previous" title="Previous">Back</a>
                <button class="btn btn-secondary-blue mb-0 js-btn-save" type="button" title="Save">Finish</button>
            </div>
            <div class="mt-4">
                <x-product-gridcard :product="$product" />
                <x-recipe-details :product="$product" />
                <x-tags-card :product="$product" />
            </div>
        </div>
    </div>
</form>