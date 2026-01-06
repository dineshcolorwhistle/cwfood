<div class="batch_table_wrapper p-3 rounded-2 box-shadow pt-4 mb-3">
    <h5 class="batch_table_title text-primary-orange">Advanced Cost Modelling</h5>
    <div class="batch_table_container">

        @php
            $directcost = number_format($directcost,2,'.', '');

            $pr_unit = $product->weight_ind_unit_g;
            $pr_sell = $product->weight_retail_unit_g;
            $pr_carton = $product->weight_carton_g;
            $pr_pallet = $product->weight_pallet_g;

            $direct_unit = number_format($directcost * ( $pr_unit / 1000),2,'.', '');
            $direct_sell = number_format($directcost * ( $pr_sell / 1000),2,'.', '');
            $direct_carton = number_format($directcost * ( $pr_carton / 1000),2,'.', '');
            $direct_pallet = number_format($directcost * ( $pr_pallet / 1000),2,'.', '');

            $retailer = $product->retailer_charges;
            $wholesale = $product->wholesale_margin;
            $distributor = $product->distributor_margin;
            $retailer_margin = $product->retailer_margin;

            $retail_kg = number_format(($directcost / ( 1- $retailer/ 100)) - $directcost,2,'.', '' );
            $retail_unit = number_format(($direct_unit / ( 1- $retailer/ 100)) - $direct_unit ,2,'.', '' );
            $retail_sell = number_format(($direct_sell / ( 1- $retailer/ 100)) - $direct_sell,2,'.', '');
            $retail_carton = number_format(($direct_carton / ( 1- $retailer/ 100)) - $direct_carton ,2,'.', '');
            $retail_pallet = number_format(($direct_pallet / ( 1- $retailer/ 100)) - $direct_pallet ,2,'.', '' );

            $totalkg = number_format($directcost + $retail_kg ,2,'.', '')  ;
            $total_unit = number_format($direct_unit + $retail_unit ,2,'.', '');
            $total_sell = number_format($direct_sell + $retail_sell,2,'.', '');
            $total_cart = number_format($direct_carton + $retail_carton ,2,'.', '');
            $total_pall = number_format($direct_pallet + $retail_pallet,2,'.', '');


            $wholesalekg = number_format(($totalkg / ( 1- $wholesale/ 100)) - $totalkg,2,'.', '' );
            $wholesale_unit = number_format(($total_unit / ( 1- $wholesale/ 100)) - $total_unit ,2,'.', '' );
            $wholesale_sell = number_format(($total_sell / ( 1- $wholesale/ 100)) - $total_sell,2,'.', '');
            $wholesale_cart = number_format(($total_cart / ( 1- $wholesale/ 100)) - $total_cart ,2,'.', '');
            $wholesale_pall = number_format(($total_pall / ( 1- $wholesale/ 100)) - $total_pall ,2,'.', '' );

            $wspkg = number_format($totalkg + $wholesalekg ,2,'.', '')  ;
            $wsp_unit = number_format($total_unit + $wholesale_unit ,2,'.', '');
            $wsp_sell = number_format($total_sell + $wholesale_sell,2,'.', '');
            $wsp_cart = number_format($total_cart + $wholesale_cart ,2,'.', '');
            $wsp_pall = number_format($total_pall + $wholesale_pall,2,'.', '');


            $distkg = number_format(($wspkg / ( 1- $distributor/ 100)) - $wspkg,2,'.', '' );
            $dist_unit = number_format(($wsp_unit / ( 1- $distributor/ 100)) - $wsp_unit ,2,'.', '' );
            $dist_sell = number_format(($wsp_sell / ( 1- $distributor/ 100)) - $wsp_sell,2,'.', '');
            $dist_cart = number_format(($wsp_cart / ( 1- $distributor/ 100)) - $wsp_cart ,2,'.', '');
            $dist_pall = number_format(($wsp_pall / ( 1- $distributor/ 100)) - $wsp_pall ,2,'.', '' );


            $distpricekg = number_format($wspkg + $distkg ,2,'.', '')  ;
            $distprice_unit = number_format($wsp_unit + $dist_unit ,2,'.', '');
            $distprice_sell = number_format($wsp_sell + $dist_sell,2,'.', '');
            $distprice_cart = number_format($wsp_cart + $dist_cart ,2,'.', '');
            $distprice_pall = number_format($wsp_pall + $dist_pall,2,'.', '');


            $retailmarginkg = number_format(($distpricekg / ( 1- $retailer_margin/ 100)) - $distpricekg,2,'.', '' );
            $retailmargin_unit = number_format(($distprice_unit / ( 1- $retailer_margin/ 100)) - $distprice_unit ,2,'.', '' );
            $retailmargin_sell = number_format(($distprice_sell / ( 1- $retailer_margin/ 100)) - $distprice_sell,2,'.', '');
            $retailmargin_cart = number_format(($distprice_cart / ( 1- $retailer_margin/ 100)) - $distprice_cart ,2,'.', '');
            $retailmargin_pall = number_format(($distprice_pall / ( 1- $retailer_margin/ 100)) - $distprice_pall ,2,'.', '' );

            $rrpkg = number_format($distpricekg + $retailmarginkg ,2,'.', '')  ;
            $rrp_unit = number_format($distprice_unit + $retailmargin_unit ,2,'.', '');
            $rrp_sell = number_format($distprice_sell + $retailmargin_sell,2,'.', '');
            $rrp_cart = number_format($distprice_cart + $retailmargin_cart ,2,'.', '');
            $rrp_pall = number_format($distprice_pall + $retailmargin_pall,2,'.', '');

            $directcost_percent =number_format(($directcost / $rrpkg)*100 ,2,'.', '');
            $retailcharge_percent =number_format(($retail_kg / $rrpkg)*100 ,2,'.', '');
            $wscost_percent =number_format(($totalkg / $rrpkg)*100 ,2,'.', '');
            $wsmargin_percent =number_format(($wholesalekg / $rrpkg)*100 ,2,'.', '');
            $wsp_percent =number_format(($wspkg / $rrpkg)*100 ,2,'.', '');
            $distmargin_percent =number_format(($distkg / $rrpkg)*100 ,2,'.', '');
            $distprice_percent =number_format(($distpricekg / $rrpkg)*100 ,2,'.', '');
            $retailmargin_percent =number_format(($retailmarginkg / $rrpkg)*100 ,2,'.', '');
            $rrp_percent =number_format(($rrpkg / $rrpkg)*100 ,2,'.', '');


            $combineretail_percent = number_format((($retailmarginkg + $distkg) / $rrpkg)*100 ,2,'.', '');
            $combinewhole_percent = number_format((($retailmarginkg + $distkg + $wholesalekg) / $rrpkg)*100 ,2,'.', '');
            $productioncost = number_format(($directcost / $rrpkg)*100 ,2,'.', '');
        @endphp

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
                <tr>
                    <td class="">Direct Costs</td>
                    <td class="text-end">{{$directcost}}</td>
                    <td class="text-end">{{$direct_unit}}</td>
                    <td class="text-end">{{$direct_sell}}</td>
                    <td class="text-end">{{$direct_carton}}</td>
                    <td class="text-end">{{number_format($direct_pallet,2)}}</td>
                    <td class="text-end">{{number_format($directcost_percent,1)}}%</td>
                </tr>
                <tr class="total-row">
                    <td class="">Retailer charges</td>
                    <td class="text-end">{{$retail_kg}}</td>
                    <td class="text-end">{{$retail_unit}}</td>
                    <td class="text-end">{{$retail_sell}}</td>
                    <td class="text-end">{{$retail_carton}}</td>
                    <td class="text-end">{{number_format($retail_pallet,2)}}</td>
                    <td class="text-end">{{number_format($retailcharge_percent,1)}}%</td>
                </tr>
                <tr>
                    <td class="fw-bold">Total Wholesale Cost</td>
                    <td class="text-end fw-bold">{{$totalkg}}</td>
                    <td class="text-end fw-bold">{{$total_unit}}</td>
                    <td class="text-end fw-bold">{{$total_sell}}</td>
                    <td class="text-end fw-bold">{{$total_cart}}</td>
                    <td class="text-end fw-bold">{{number_format($total_pall,2)}}</td>
                    <td class="text-end fw-bold">{{number_format($wscost_percent,1)}}%</td>
                </tr>
                <tr class="total-row">
                    <td class="">Wholesale Margin</td>
                    <td class="text-end">{{$wholesalekg}}</td>
                    <td class="text-end">{{$wholesale_unit}}</td>
                    <td class="text-end">{{$wholesale_sell}}</td>
                    <td class="text-end">{{$wholesale_cart}}</td>
                    <td class="text-end">{{number_format($wholesale_pall,2)}}</td>
                    <td class="text-end">{{number_format($wsmargin_percent,1)}}%</td>
                </tr>

                <tr>
                    <td class="fw-bold">Wholesale Price</td>
                    <td class="text-end fw-bold">{{$wspkg}}</td>
                    <td class="text-end fw-bold">{{$wsp_unit}}</td>
                    <td class="text-end fw-bold">{{$wsp_sell}}</td>
                    <td class="text-end fw-bold">{{$wsp_cart}}</td>
                    <td class="text-end fw-bold">{{number_format($wsp_pall,2)}}</td>
                    <td class="text-end fw-bold">{{number_format($wsp_percent,1)}}%</td>
                </tr>
                <tr class="total-row">
                    <td class="">Distributor Margin</td>
                    <td class="text-end">{{$distkg}}</td>
                    <td class="text-end">{{$dist_unit}}</td>
                    <td class="text-end">{{$dist_sell}}</td>
                    <td class="text-end">{{$dist_cart}}</td>
                    <td class="text-end">{{number_format($dist_pall,2)}}</td>
                    <td class="text-end">{{number_format($distmargin_percent,1)}}%</td>
                </tr>
                <tr>
                    <td class="fw-bold">Distributor Price</td>
                    <td class="text-end fw-bold">{{$distpricekg}}</td>
                    <td class="text-end fw-bold">{{$distprice_unit}}</td>
                    <td class="text-end fw-bold">{{$distprice_sell}}</td>
                    <td class="text-end fw-bold">{{$distprice_cart}}</td>
                    <td class="text-end fw-bold">{{number_format($distprice_pall,2)}}</td>
                    <td class="text-end fw-bold">{{number_format($distprice_percent,1)}}%</td>
                </tr>
                <tr class="total-row">
                    <td class="">Retailer Margin</td>
                    <td class="text-end">{{$retailmarginkg}}</td>
                    <td class="text-end">{{$retailmargin_unit}}</td>
                    <td class="text-end">{{$retailmargin_sell}}</td>
                    <td class="text-end">{{$retailmargin_cart}}</td>
                    <td class="text-end">{{number_format($retailmargin_pall,2)}}</td>
                    <td class="text-end">{{number_format($retailmargin_percent,1)}}%</td>
                </tr>
                <tr>
                    <td class="fw-bold">Implied RRP</td>
                    <td class="text-end fw-bold">{{$rrpkg}}</td>
                    <td class="text-end fw-bold">{{$rrp_unit}}</td>
                    <td class="text-end fw-bold">{{$rrp_sell}}</td>
                    <td class="text-end fw-bold">{{$rrp_cart}}</td>
                    <td class="text-end fw-bold">{{number_format($rrp_pall,2)}}</td>
                    <td class="text-end fw-bold">{{ number_format($rrp_percent,1)}}%</td>
                </tr>
                <tr style="height: 40px;">
                    <td colspan="7"></td>
                </tr>
                <tr>
                    <td class="">Combined Dist and Retailer</td>
                    <td class="text-end">{{ number_format($combineretail_percent,1)}}%</td>
                    <td colspan="5"></td>
                </tr>

                <tr>
                    <td class="">Combined Wholesale, Dist and Retailer Margin</td>
                    <td class="text-end">{{ number_format($combinewhole_percent,1)}}%</td>
                    <td colspan="5"></td>
                </tr>

                <tr>
                    <td class="">Production cost (% of Retail Price)</td>
                    <td class="text-end">{{ number_format($productioncost,1)}}%</td>
                    <td colspan="5"></td>
                </tr>
                <tr style="height: 40px;">
                    <td colspan="7"></td>
                </tr>
            <tbody> 
              
            </tbody>
        </table>
        <table class="table costing_table">
            <thead>
                <tr>
                    <td class="fw-bold">Product information</td>
                    <td class="text-end fw-bold">Nett Weight <br>(g)</td>
                    <td class="text-end fw-bold">Units <br> (#)</td>
                    <td colspan="4"></td>
                </tr>
            </thead>
                <tr>
                    <td>Individual Unit</td>
                    <td class="text-end">{{number_format($pr_unit,0)}}</td>
                    <td class="text-end">1</td>
                    <td colspan="4"></td>
                </tr>
                <tr>
                    <td>Sell Unit</td>
                    <td class="text-end">{{number_format($pr_sell,0)}}</td>
                    <td class="text-end">{{ $pr_sell/$pr_unit }}</td>
                    <td colspan="4"></td>
                </tr>
                <tr>
                    <td>Carton</td>
                    <td class="text-end">{{number_format($pr_carton,0)}}</td>
                    <td class="text-end">{{ $pr_carton/$pr_sell}}</td>
                    <td colspan="4"></td>
                </tr>
                <tr>
                    <td>Pallet</td>
                    <td class="text-end">{{number_format($pr_pallet,0)}}</td>
                    <td class="text-end">{{$pr_pallet/$pr_carton}}</td>
                    <td colspan="4"></td>
                </tr>
            <tbody> 
              
            </tbody>
        </table>
    </div>
</div>