<style>
    table.pricing_table{width:50% !important}
    
</style>
<!-- Form for updating product details in step 5 -->
<form id="form_step_5" action="{{ route('products.updateStep5', $product) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mt-3">
        <div class="col-lg-10 col-md-10 col-sm-12 col-12">
            <div class="row">
                <h4 style="color: var(--secondary-color);">Pricing</h4>
                <div class="col-lg-12 col-md-12 col-sm-12 col-12 mt-2">
                    <label class="form-label">Price and Margin Analysis</label>
                    <div id="custom-text"><p>Method One: Calculates Margins in supply chain based on Wholesale Price, Distributor Price and RRP.</p></div>
                    <div class="input-group input-group-dynamic">
                        <table class="table table-borderless responsiveness no-border pricing_table" id="cost_marign">
                            <thead>
                                <th class="text-primary-dark-mud"></th>
                                <th class="text-primary-dark-mud text-end">$/ Sell Unit</th>
                                <th class="text-primary-dark-mud text-end">$ / KG</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><label class="primary-text-dark">Wholesale Price (exclusive of GST)</label></td>
                                    <td>
                                        <input type="text" name="wholesale_price_sell" id="wholesale_price_sell" value="{{ old('wholesale_price_sell', $product->wholesale_price_sell ?? '') }}" class="form-control numeric-input text-end" placeholder="">
                                    </td>
                                    <td class="table-active-readonly">
                                        <input type="text" name="wholesale_price_kg_price" id="wholesale_price_kg_price" value="{{ old('wholesale_price_kg_price', $product->wholesale_price_kg_price ?? '') }}" class="form-control numeric-input text-end" placeholder="" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label class="primary-text-dark">Distributor Price (exclusive of GST)</label></td>
                                    <td>
                                        <input type="text" name="distributor_price_sell" id="distributor_price_sell" value="{{ old('distributor_price_sell', $product->distributor_price_sell ?? '') }}" class="form-control numeric-input text-end" placeholder="">
                                    </td>
                                    <td class="table-active-readonly">
                                        <input type="text" name="distributor_price_kg_price" id="distributor_price_kg_price" value="{{ old('distributor_price_kg_price', $product->distributor_price_kg_price ?? '') }}" class="form-control numeric-input text-end" placeholder="" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label class="primary-text-dark">RRP (exclusive of GST)</label></td>
                                    <td>
                                        <input type="text" name="rrp_ex_gst_sell" id="rrp_ex_gst_sell" value="{{ old('rrp_ex_gst_sell', $product->rrp_ex_gst_sell ?? '') }}" class="form-control numeric-input text-end" placeholder="">
                                    </td>
                                    <td class="table-active-readonly">
                                        <input type="text" name="rrp_ex_gst_price" id="rrp_ex_gst_price" value="{{ old('rrp_ex_gst_price', $product->rrp_ex_gst_price ?? '') }}" class="form-control numeric-input text-end" placeholder="" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label class="primary-text-dark">RRP (inclusive of GST)</label></td>
                                    <td class="table-active-readonly">
                                        <input type="text" name="rrp_inc_gst_sell" id="rrp_inc_gst_sell" value="{{ old('rrp_inc_gst_sell', $product->rrp_inc_gst_sell ?? '') }}" class="form-control numeric-input text-end" placeholder="" readonly>
                                    </td>
                                    <td class="table-active-readonly">
                                        <input type="text" name="rrp_inc_gst_price" id="rrp_inc_gst_price" value="{{ old('rrp_inc_gst_price', $product->rrp_inc_gst_price ?? '') }}" class="form-control numeric-input text-end" placeholder="" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label class="primary-text-dark">Weight (nett g)</label></td>
                                    <td class="table-active-readonly">
                                        <input type="text" name="cost_weight_sell" id="cost_weight_sell" value="{{ old('weight_retail_unit_g', $product->weight_retail_unit_g ?? '') }}" class="form-control numeric-input text-end" placeholder="0" readonly>
                                    </td>
                                    <td class="table-active-readonly">
                                        <input type="text" name="cost_weight_price" id="cost_weight_price" value="1000" class="form-control numeric-input text-end" placeholder="" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label class="primary-text-dark">GST</label></td>
                                    <td class="table-active-readonly">
                                        <input type="text" name="GST" id="GST" value="10.0" class="form-control numeric-input text-end" readonly>
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 col-12 mt-4">
                <div id="prince_analysis_component"></div>
            </div>

            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-12 mt-4 mb-4">
                    <label class="form-label">Advanced Costing Model</label>
                    <div id="custom-text"><p>Method Two: Calculates price points in the supply chain baseed on target Wholesale, Distributor and Retailer Margins</p></div>
                    <div class="input-group input-group-dynamic">
                        <table class="table table-borderless responsiveness no-border pricing_table">
                            <tbody>
                            <tr>
                                <td><label class="primary-text-dark">Wholesale Margin</label></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" name="wholesale_margin" id="wholesale_margin" value="{{ old('wholesale_margin', $product->wholesale_margin ?? '') }}" class="form-control numeric-input text-end" placeholder="">
                                    </div>
                                </td>
                                <td>
                                    <p class="m-0">% of Wholesale Price</p>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="primary-text-dark">Distributor Margin</label></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" name="distributor_margin" id="distributor_margin" value="{{ old('distributor_margin', $product->distributor_margin ?? '') }}" class="form-control numeric-input text-end" placeholder="">
                                    </div>
                                </td>
                                <td>
                                    <p class="m-0">% of Distributor Price</p>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="primary-text-dark">Retailer Margin</label></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" name="retailer_margin" id="retailer_margin" value="{{ old('retailer_margin', $product->retailer_margin ?? '') }}" class="form-control numeric-input text-end" style="width: 127%;" placeholder="">
                                    </div>
                                </td>
                                <td>
                                    <p class="m-0">% of RRP (Exclusive of GST)</p>
                                </td>
                            </tr> 
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 col-12 mt-4">
                <div id="cost_modelling_component"></div> 
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 col-12">
            <div class="button-row d-flex flex-column gap-2">
                <button class="btn btn-secondary-blue mb-0 js-btn-save" type="button" title="Save"><i class="material-symbols-outlined me-1" style="font-size: 18px;">save</i>Save</button>
                <button class="btn btn-secondary-blue mb-0 js-btn-finish" type="button" title="Finish"><i class="material-symbols-outlined me-1" style="font-size: 18px;">check_circle</i>Finish</button>
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
            url: "{{ route('products.component.price-analysis', ':id') }}".replace(':id', id),
            method: 'GET',
            success: function(response) {
                if(response.success){
                    $('#prince_analysis_component').html(response.prince_analysis_html)
                    $('#cost_modelling_component').html(response.cost_html)
                }
            }
        });
    }
</script>
@endpush