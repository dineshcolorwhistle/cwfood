<div class="card batch_table_wrapper p-3 rounded-2 box-shadow pt-4 mb-3">
    <h5 class="batch_table_title text-primary-orange">Price and Margin Analysis</h5>
    <div class="batch_table_container">
        <table class="table costing_table">
            <thead>
                <tr>
                    <td></td>
                    <td class="text-end fw-bold">$ / kg</td>
                    <td class="text-end fw-bold">$ / Unit</td>
                    <td class="text-end fw-bold">$ / Sell Unit</td>
                    <td class="text-end fw-bold">$ / Carton</td>
                    <td class="text-end fw-bold">$ / Pallet</td>
                    <td class="text-end fw-bold">% of Retail</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="">Direct Costs</td>
                    <td class="text-end">{{$details['direct_cost']['per_kg']}}</td>
                    <td class="text-end">{{$details['direct_cost']['unit']}}</td>
                    <td class="text-end">{{$details['direct_cost']['sell']}}</td>
                    <td class="text-end">{{$details['direct_cost']['carton']}}</td>
                    <td class="text-end">{{number_format($details['direct_cost']['pallet'],2)}}</td>
                    <td class="text-end">@if($details['percentages']['directcost'] == "na") na @else {{$details['percentages']['directcost']}}% @endif</td>
                </tr>
                <tr class="total-row">
                    <td class="">Retailer charges</td>
                    <td class="text-end">{{$details['retail_charges']['per_kg']}}</td>
                    <td class="text-end">{{$details['retail_charges']['unit']}}</td>
                    <td class="text-end">{{$details['retail_charges']['sell']}}</td>
                    <td class="text-end">{{$details['retail_charges']['carton']}}</td>
                    <td class="text-end">{{number_format($details['retail_charges']['pallet'],2)}}</td>
                    <td class="text-end">@if($details['percentages']['retailcharge'] == "na") na @else {{$details['percentages']['retailcharge']}}% @endif</td>
                </tr>
                <tr>
                    <td class="fw-bold">Total Wholesale Cost</td>
                    <td class="text-end">{{$details['wholesale_cost']['per_kg']}}</td>
                    <td class="text-end">{{$details['wholesale_cost']['unit']}}</td>
                    <td class="text-end">{{$details['wholesale_cost']['sell']}}</td>
                    <td class="text-end">{{$details['wholesale_cost']['carton']}}</td>
                    <td class="text-end">{{number_format($details['wholesale_cost']['pallet'],2)}}</td>
                    <td class="text-end">@if($details['percentages']['wscost'] == "na") na @else {{$details['percentages']['wscost']}}% @endif</td>
                </tr>

                <tr class="total-row">
                    <td class="">Wholesale Margin</td>
                    <td class="text-end">{{$details['wholesale_margin']['per_kg']}}</td>
                    <td class="text-end">{{$details['wholesale_margin']['unit']}}</td>
                    <td class="text-end">{{$details['wholesale_margin']['sell']}}</td>
                    <td class="text-end">{{$details['wholesale_margin']['carton']}}</td>
                    <td class="text-end">{{number_format($details['wholesale_margin']['pallet'],2)}}</td>
                    <td class="text-end">@if($details['percentages']['wsmargin'] == "na") na @else {{$details['percentages']['wsmargin']}}% @endif</td>
                </tr>
                <tr>
                    <td class="fw-bold">Wholesale Price</td>
                    <td class="text-end">{{$details['wholesale_price']['per_kg']}}</td>
                    <td class="text-end">{{$details['wholesale_price']['unit']}}</td>
                    <td class="text-end">{{$details['wholesale_price']['sell']}}</td>
                    <td class="text-end">{{$details['wholesale_price']['carton']}}</td>
                    <td class="text-end">{{number_format($details['wholesale_price']['pallet'],2)}}</td>
                    <td class="text-end">@if($details['percentages']['wholesaleprice'] == "na") na @else {{$details['percentages']['wholesaleprice']}}% @endif</td>
                </tr>

                <tr class="total-row">
                    <td class="">Distributor Margin</td>
                    <td class="text-end">{{$details['distributor_margin']['per_kg']}}</td>
                    <td class="text-end">{{$details['distributor_margin']['unit']}}</td>
                    <td class="text-end">{{$details['distributor_margin']['sell']}}</td>
                    <td class="text-end">{{$details['distributor_margin']['carton']}}</td>
                    <td class="text-end">{{number_format($details['distributor_margin']['pallet'],2)}}</td>
                    <td class="text-end">@if($details['percentages']['distmargin'] == "na") na @else {{$details['percentages']['distmargin']}}% @endif</td>
                </tr>
                <tr>
                    <td class="fw-bold">Distributor Price</td>
                    <td class="text-end">{{$details['distributor_price']['per_kg']}}</td>
                    <td class="text-end">{{$details['distributor_price']['unit']}}</td>
                    <td class="text-end">{{$details['distributor_price']['sell']}}</td>
                    <td class="text-end">{{$details['distributor_price']['carton']}}</td>
                    <td class="text-end">{{number_format($details['distributor_price']['pallet'],2)}}</td>
                    <td class="text-end">@if($details['percentages']['distprice'] == "na") na @else {{$details['percentages']['distprice']}}% @endif</td>
                </tr>

                <tr class="total-row">
                    <td class="">Retailer Margin</td>
                    <td class="text-end">{{$details['retailer_margin']['per_kg']}}</td>
                    <td class="text-end">{{$details['retailer_margin']['unit']}}</td>
                    <td class="text-end">{{$details['retailer_margin']['sell']}}</td>
                    <td class="text-end">{{$details['retailer_margin']['carton']}}</td>
                    <td class="text-end">{{number_format($details['retailer_margin']['pallet'],2)}}</td>
                    <td class="text-end">@if($details['percentages']['retailmargin'] == "na") na @else {{$details['percentages']['retailmargin']}}% @endif</td>
                </tr>
                <tr>
                    <td class="fw-bold">RRP (exclusive of GST)</td>
                    <td class="text-end">{{$details['rrp_ex_gst']['per_kg']}}</td>
                    <td class="text-end">{{$details['rrp_ex_gst']['unit']}}</td>
                    <td class="text-end">{{$details['rrp_ex_gst']['sell']}}</td>
                    <td class="text-end">{{$details['rrp_ex_gst']['carton']}}</td>
                    <td class="text-end">{{number_format($details['rrp_ex_gst']['pallet'],2)}}</td>
                    <td class="text-end">@if($details['percentages']['rrp'] == "na") na @else {{$details['percentages']['rrp']}}% @endif</td>
                </tr>
                <tr class="total-row">
                    <td class="">GST</td>
                    <td class="text-end">{{$details['gst']['per_kg']}}</td>
                    <td class="text-end">{{$details['gst']['unit']}}</td>
                    <td class="text-end">{{$details['gst']['sell']}}</td>
                    <td class="text-end">{{$details['gst']['carton']}}</td>
                    <td class="text-end">{{number_format($details['gst']['pallet'],2)}}</td>
                    <td class="text-end">@if($details['percentages']['rrp'] == "na") na @else 10.0% @endif</td>
                </tr>
                <tr>
                    <td class="fw-bold">RRP (Inclusive of GST)</td>
                    <td class="text-end">{{$details['rrp_inc_gst']['per_kg']}}</td>
                    <td class="text-end">{{$details['rrp_inc_gst']['unit']}}</td>
                    <td class="text-end">{{$details['rrp_inc_gst']['sell']}}</td>
                    <td class="text-end">{{$details['rrp_inc_gst']['carton']}}</td>
                    <td class="text-end">{{number_format($details['rrp_inc_gst']['pallet'],2)}}</td>
                    <td class="text-end fw-bold">@if($details['percentages']['rrp'] == "na") na @else 110.0% @endif</td>
                </tr>
                <tr style="height: 40px;">
                    <td colspan="7"></td>
                </tr>
                <tr>
                    <td class="">Weight (nett g)</td>
                    <td class="text-end">1,000.0</td>
                    <td class="text-end">{{number_format($details['products']['unit'],1)}}</td>
                    <td class="text-end">{{number_format($details['products']['sell'],1)}}</td>
                    <td class="text-end">{{number_format($details['products']['carton'],1)}}</td>
                    <td class="text-end">{{number_format($details['products']['pallet'],1)}}</td>
                    <td class="text-end">{{ number_format($weightTotal, 1) }}</td>
                </tr>
                <tr>
                    <td class="">Units</td>
                    <td class="text-end"></td>
                    <td class="text-end">1</td>
                    <td class="text-end">{{ number_format($details['product_unit']['sell'],1)}}</td>
                    <td class="text-end">{{ number_format($details['product_unit']['carton'],1)}}</td>
                    <td class="text-end">{{ number_format($details['product_unit']['pallet'],1)}}</td>
                    <td class="text-end"></td>
                </tr>
                <tr style="height: 40px;">
                    <td colspan="7"></td>
                </tr>
                <tr>
                    <td class="">Wholesale Margin</td>
                    <td class="text-end">@if($details['percentages']['combine_retail'] == "na") na @else {{ number_format($details['percentages']['combine_retail'],1)}}% @endif</td>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td class="">Distributor Margin</td>
                    <td class="text-end">@if($details['percentages']['combine_wholesale'] == "na") na @else {{ number_format($details['percentages']['combine_wholesale'],1)}}% @endif</td>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td class="">Retail Margin</td>
                    <td class="text-end">@if($details['percentages']['production_cost'] == "na") na @else{{ number_format($details['percentages']['production_cost'],1)}}% @endif</td>
                    <td colspan="5"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>