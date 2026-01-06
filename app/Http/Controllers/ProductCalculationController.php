<?php

namespace App\Http\Controllers;

use App\Models\{
    image_library,
    Labour,
    ProdTag,
    Product,
    Machinery,
    Packaging,
    Ingredient,
    ProdLabour,
    ProdMachinery,
    ProdPackaging,
    ProdIngredient,
    ClientProfile,
    Ing_country,
    Ing_allergen,
    Company,
    Client_company,
    ProdLabel,
    ProdFreight,
    Freight,
    ClientSubscription,
    Product_category,
    Product_tag,
    Recipe_component
};
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    DB,
    Log,
    Auth,
    Validator
};
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\Rule;

class ProductCalculationController extends Controller
{
    private $user_id;
    private $role_id;
    private $clientID;
    private $ws_id;

    public function __construct()
    {
        $this->user_id = session('user_id');
        $this->role_id = session('role_id');
        $this->clientID = session('client');
        $this->ws_id = session('workspace');
    }

    public static function calculate_price_analysis_componenet($product){
        $company_directcost = self::calculete_product_directcost($product);
        $directcost = (float) $company_directcost;

        // Product weights
        $pr_unit   = $product->weight_ind_unit_g;
        $pr_sell   = $product->weight_retail_unit_g;
        $pr_carton = $product->weight_carton_g;
        $pr_pallet = $product->weight_pallet_g;

        //Product Unit
        $ws_unit = $product->wholesale_price_sell ?? 0;
        $ds_unit = $product->distributor_price_sell ?? 0;
        $rrp_ex_unit = $product->rrp_ex_gst_sell ?? 0;
        $rrp_sell = $rrp_ex_unit* (1+ 0.1);
    
        // Pricing details
        // $retailer       = $product->retailer_charges;
        // $distkg         = number_format($ds_unit * (1000/ $pr_unit), 2, '.','');
        // $rrp_ex_price   = number_format($rrp_ex_unit * (1000/ $pr_unit), 2, '.','');
        // $rrp_inc_price  = number_format($rrp_sell * (1000/ $pr_unit), 2, '.','');
        // $wholesalekg    = number_format($ws_unit * (1000/ $pr_unit), 2, '.','');

        $retailer       = $product->retailer_charges;
        $distkg         = $product->distributor_price_kg_price;
        $rrp_ex_price   = $product->rrp_ex_gst_price;
        $rrp_inc_price  = $product->rrp_inc_gst_price;
        $wholesalekg    = $product->wholesale_price_kg_price;
        // dd($wholesalekg); 

        // Direct cost per unit
        $direct_unit   = number_format($directcost * ($pr_unit / 1000), 2, '.', '');
        $direct_sell   = number_format($directcost * ($pr_sell / 1000), 2, '.', '');
        $direct_carton = number_format($directcost * ($pr_carton / 1000), 2, '.', '');
        $direct_pallet = number_format($directcost * ($pr_pallet / 1000), 2, '.', '');

        // Retailer margin calculation
        $retail_kg      = (is_numeric($retailer) &&  floatval($retailer) != 0 ) ? number_format(($directcost / (1 - $retailer / 100)) - $directcost, 2, '.', '') : 0.00;
        $retail_unit    = (is_numeric($retailer) &&  floatval($retailer) != 0 ) ? number_format(($direct_unit / (1 - $retailer / 100)) - $direct_unit, 2, '.', '') : 0.00;
        $retail_sell    = (is_numeric($retailer) &&  floatval($retailer) != 0 ) ? number_format(($direct_sell / (1 - $retailer / 100)) - $direct_sell, 2, '.', '') : 0.00;
        $retail_carton  = (is_numeric($retailer) &&  floatval($retailer) != 0 ) ? number_format(($direct_carton / (1 - $retailer / 100)) - $direct_carton, 2, '.', '') : 0.00;
        $retail_pallet  = (is_numeric($retailer) &&  floatval($retailer) != 0 ) ? number_format(($direct_pallet / (1 - $retailer / 100)) - $direct_pallet, 2, '.', '') : 0.00;

        // Total with retail margin
        $totalkg     = number_format($directcost + $retail_kg, 2, '.', '');
        $total_unit  = number_format($direct_unit + $retail_unit, 2, '.', '');
        $total_sell  = number_format($direct_sell + $retail_sell, 2, '.', '');
        $total_cart  = number_format($direct_carton + $retail_carton, 2, '.', '');
        $total_pall  = number_format($direct_pallet + $retail_pallet, 2, '.', '');

        // Wholesale calculations
        $wholesale_unit   = (is_numeric($wholesalekg) &&  floatval($wholesalekg) != 0 ) ? number_format($wholesalekg * ($pr_unit / 1000), 2, '.', '') : 0.00;
        $wholesale_sell   = (is_numeric($wholesalekg) &&  floatval($wholesalekg) != 0 ) ? number_format($wholesalekg * ($pr_sell / 1000), 2, '.', '') : 0.00;
        $wholesale_carton = (is_numeric($wholesalekg) &&  floatval($wholesalekg) != 0 ) ? number_format($wholesalekg * ($pr_carton / 1000), 2, '.', '') : 0.00;
        $wholesale_pallet = (is_numeric($wholesalekg) &&  floatval($wholesalekg) != 0 ) ? number_format($wholesalekg * ($pr_pallet / 1000), 2, '.', '') : 0.00;

        // Wholesale margins
        $wsmargin         = number_format($wholesalekg - $totalkg, 2, '.', '');
        $wsmargin_unit    = number_format($wholesale_unit - $total_unit, 2, '.', '');
        $wsmargin_sell    = number_format($wholesale_sell - $total_sell, 2, '.', '');
        $wsmargin_carton  = number_format($wholesale_carton - $total_cart, 2, '.', '');
        $wsmargin_pallet  = number_format($wholesale_pallet - $total_pall, 2, '.', '');

        // Distributor calculations
        $dist_unit   = (is_numeric($distkg) &&  floatval($distkg) != 0 ) ? number_format($distkg * ($pr_unit / 1000), 2, '.', '') : 0.00;
        $dist_sell   = (is_numeric($distkg) &&  floatval($distkg) != 0 ) ? number_format($distkg * ($pr_sell / 1000), 2, '.', '') : 0.00;
        $dist_carton = (is_numeric($distkg) &&  floatval($distkg) != 0 ) ? number_format($distkg * ($pr_carton / 1000), 2, '.', '') : 0.00;
        $dist_pallet = (is_numeric($distkg) &&  floatval($distkg) != 0 ) ? number_format($distkg * ($pr_pallet / 1000), 2, '.', '') : 0.00;

        // Distributor margin
        $dist_margin         = number_format($distkg - $wholesalekg, 2, '.', '');
        $dist_margin_unit    = number_format($dist_unit - $wholesale_unit, 2, '.', '');
        $dist_margin_sell    = number_format($dist_sell - $wholesale_sell, 2, '.', '');
        $dist_margin_carton  = number_format($dist_carton - $wholesale_carton, 2, '.', '');
        $dist_margin_pallet  = number_format($dist_pallet - $wholesale_pallet, 2, '.', '');

        // RRP ex-GST pricing
        $rrp_ex_price_unit   = (is_numeric($rrp_ex_price) &&  floatval($rrp_ex_price) != 0 ) ? number_format($rrp_ex_price * ($pr_unit / 1000), 2, '.', '') : 0.00;
        $rrp_ex_price_sell   = (is_numeric($rrp_ex_price) &&  floatval($rrp_ex_price) != 0 ) ? number_format($rrp_ex_price * ($pr_sell / 1000), 2, '.', '') : 0.00;
        $rrp_ex_price_carton = (is_numeric($rrp_ex_price) &&  floatval($rrp_ex_price) != 0 ) ? number_format($rrp_ex_price * ($pr_carton / 1000), 2, '.', '') : 0.00;
        $rrp_ex_price_pallet = (is_numeric($rrp_ex_price) &&  floatval($rrp_ex_price) != 0 ) ? number_format($rrp_ex_price * ($pr_pallet / 1000), 2, '.', '') : 0.00;

        // Retail margin
        $retail_margin         = number_format($rrp_ex_price - $distkg, 2, '.', '');
        $retail_margin_unit    = number_format($rrp_ex_price_unit - $dist_unit, 2, '.', '');
        $retail_margin_sell    = number_format($rrp_ex_price_sell - $dist_sell, 2, '.', '');
        $retail_margin_carton  = number_format($rrp_ex_price_carton - $dist_carton, 2, '.', '');
        $retail_margin_pallet  = number_format($rrp_ex_price_pallet - $dist_pallet, 2, '.', '');

        // RRP inc-GST pricing
        $rrp_inc_price_unit   = (is_numeric($rrp_inc_price) &&  floatval($rrp_inc_price) != 0 ) ? number_format($rrp_inc_price * ($pr_unit / 1000), 2, '.', '') : 0.00;
        $rrp_inc_price_sell   = (is_numeric($rrp_inc_price) &&  floatval($rrp_inc_price) != 0 ) ? number_format($rrp_inc_price * ($pr_sell / 1000), 2, '.', '') : 0.00;
        $rrp_inc_price_carton = (is_numeric($rrp_inc_price) &&  floatval($rrp_inc_price) != 0 ) ? number_format($rrp_inc_price * ($pr_carton / 1000), 2, '.', '') : 0.00;
        $rrp_inc_price_pallet = (is_numeric($rrp_inc_price) &&  floatval($rrp_inc_price) != 0 ) ? number_format($rrp_inc_price * ($pr_pallet / 1000), 2, '.', '') : 0.00;

        // GST calculations
        $gst         = number_format($rrp_inc_price - $rrp_ex_price, 2, '.', '');
        $gst_unit    = number_format($rrp_inc_price_unit - $rrp_ex_price_unit, 2, '.', '');
        $gst_sell    = number_format($rrp_inc_price_sell - $rrp_ex_price_sell, 2, '.', '');
        $gst_carton  = number_format($rrp_inc_price_carton - $rrp_ex_price_carton, 2, '.', '');
        $gst_pallet  = number_format($rrp_inc_price_pallet - $rrp_ex_price_pallet, 2, '.', '');

        // Percentage calculations
        $directcost_percent     = (is_numeric($directcost) && is_numeric($rrp_ex_price) && floatval($directcost) != 0 && floatval($rrp_ex_price) != 0)? number_format(($directcost / $rrp_ex_price) * 100, 1, '.', '') : 'na';
        $retailcharge_percent   = (is_numeric($retail_kg) && is_numeric($rrp_ex_price) && floatval($retail_kg) != 0 && floatval($rrp_ex_price) != 0) ? number_format(($retail_kg / $rrp_ex_price) * 100, 1, '.', '') : 'na';
        $wscost_percent         = (is_numeric($totalkg) && is_numeric($rrp_ex_price) && floatval($totalkg) != 0 && floatval($rrp_ex_price) != 0) ? number_format(($totalkg / $rrp_ex_price) * 100, 1, '.', '') : 'na';
        $wsmargin_percent       = (is_numeric($wsmargin) && is_numeric($rrp_ex_price) && floatval($wsmargin) != 0 && floatval($rrp_ex_price) != 0) ? number_format(($wsmargin / $rrp_ex_price) * 100, 1, '.', '') : 'na';
        $wsp_percent            = (is_numeric($wholesalekg) && is_numeric($rrp_ex_price) && floatval($wholesalekg) != 0 && floatval($rrp_ex_price) != 0) ? number_format(($wholesalekg / $rrp_ex_price) * 100, 1, '.', '') : 'na';
        $distmargin_percent     = (is_numeric($dist_margin) && is_numeric($rrp_ex_price) && floatval($dist_margin) != 0 && floatval($rrp_ex_price) != 0) ? number_format(($dist_margin / $rrp_ex_price) * 100, 1, '.', '') : 'na';
        $distprice_percent      = (is_numeric($distkg) && is_numeric($rrp_ex_price) && floatval($distkg) != 0 && floatval($rrp_ex_price) != 0) ? number_format(($distkg / $rrp_ex_price) * 100, 1, '.', '') : 'na';
        $retailmargin_percent   = (is_numeric($retail_margin) && is_numeric($rrp_ex_price) && floatval($retail_margin) != 0 && floatval($rrp_ex_price) != 0) ? number_format(($retail_margin / $rrp_ex_price) * 100, 1, '.', '') : 'na';
        $rrp_percent            = (is_numeric($rrp_ex_price) && floatval($rrp_ex_price) != 0) ? number_format(($rrp_ex_price / $rrp_ex_price) * 100, 1, '.', '') : 'na';

        // Combined percentages
        $combineretail_percent  = (is_numeric($wsmargin) && is_numeric($wholesalekg) && floatval($wsmargin) != 0 && floatval($wholesalekg) != 0) ? number_format(($wsmargin / $wholesalekg) * 100, 1, '.', ''): 'na';
        $combinewhole_percent   = (is_numeric($dist_margin) && is_numeric($distkg) && floatval($dist_margin) != 0 && floatval($distkg) != 0) ? number_format(($dist_margin / $distkg) * 100, 1, '.', ''): 'na';
        $productioncost         = (is_numeric($retail_margin) && is_numeric($rrp_ex_price) && floatval($retail_margin) != 0 && floatval($rrp_ex_price) != 0) ? number_format(($retail_margin / $rrp_ex_price) * 100, 1, '.', ''): 'na';
        $data = [
                'direct_cost' => [
                    'per_kg'    => number_format($directcost,2, '.', ''),
                    'unit'      => $direct_unit,
                    'sell'      => $direct_sell,
                    'carton'    => $direct_carton,
                    'pallet'    => $direct_pallet,
                ],

                'retail_charges' => [
                    'per_kg'     => $retail_kg,
                    'unit'   => $retail_unit,
                    'sell'   => $retail_sell,
                    'carton' => $retail_carton,
                    'pallet' => $retail_pallet,
                ],

                'wholesale_cost' => [
                    'per_kg'     => $totalkg,
                    'unit'   => $total_unit,
                    'sell'   => $total_sell,
                    'carton' => $total_cart,
                    'pallet' => $total_pall,
                ],

                'wholesale_margin' => [
                    'per_kg'     => $wsmargin,
                    'unit'   => $wsmargin_unit,
                    'sell'   => $wsmargin_sell,
                    'carton' => $wsmargin_carton,
                    'pallet' => $wsmargin_pallet,
                ],

                'wholesale_price' => [
                    'per_kg' => $wholesalekg,
                    'unit'   => $wholesale_unit,
                    'sell'   => $wholesale_sell,
                    'carton' => $wholesale_carton,
                    'pallet' => $wholesale_pallet,
                ],

                'distributor_margin' => [
                    'per_kg'     => $dist_margin,
                    'unit'   => $dist_margin_unit,
                    'sell'   => $dist_margin_sell,
                    'carton' => $dist_margin_carton,
                    'pallet' => $dist_margin_pallet,
                ],

                'distributor_price' => [
                    'per_kg' => $distkg,
                    'unit'   => $dist_unit,
                    'sell'   => $dist_sell,
                    'carton' => $dist_carton,
                    'pallet' => $dist_pallet,
                ],

                'retailer_margin' => [
                    'per_kg' => $retail_margin,
                    'unit'   => $retail_margin_unit,
                    'sell'   => $retail_margin_sell,
                    'carton' => $retail_margin_carton,
                    'pallet' => $retail_margin_pallet,
                ],

                'rrp_ex_gst' => [
                    'per_kg' => $rrp_ex_price,
                    'unit'   => $rrp_ex_price_unit,
                    'sell'   => $rrp_ex_price_sell,
                    'carton' => $rrp_ex_price_carton,
                    'pallet' => $rrp_ex_price_pallet,
                ],

                'gst' => [
                    'per_kg'     => $gst,
                    'unit'   => $gst_unit,
                    'sell'   => $gst_sell,
                    'carton' => $gst_carton,
                    'pallet' => $gst_pallet,
                ],

                'rrp_inc_gst' => [
                    'per_kg' => $rrp_inc_price,
                    'unit'   => $rrp_inc_price_unit,
                    'sell'   => $rrp_inc_price_sell,
                    'carton' => $rrp_inc_price_carton,
                    'pallet' => $rrp_inc_price_pallet,
                ],

                'percentages' => [
                    'directcost'       => $directcost_percent,
                    'retailcharge'     => $retailcharge_percent,
                    'wscost'           => $wscost_percent,
                    'wsmargin'         => $wsmargin_percent,
                    'wholesaleprice'   => $wsp_percent,
                    'distmargin'       => $distmargin_percent,
                    'distprice'        => $distprice_percent,
                    'retailmargin'     => $retailmargin_percent,
                    'rrp'              => $rrp_percent,
                    'combine_retail'   => $combineretail_percent,
                    'combine_wholesale'=> $combinewhole_percent,
                    'production_cost'  => $productioncost,
                ],
                'products' => [
                    'unit' => $pr_unit,
                    'sell' => $pr_sell,
                    'carton' => $pr_carton,
                    'pallet' => $pr_pallet
                ],
                'product_unit' => [
                    'sell' => (floatval($pr_unit) != 0  && floatval($pr_sell) != 0) ? $pr_sell / $pr_unit : 0.00,
                    'carton' => (floatval($pr_carton) != 0  && floatval($pr_sell) != 0) ? $pr_carton / $pr_sell : 0.00,
                    'pallet' => (floatval($pr_pallet) != 0  && floatval($pr_carton) != 0) ? $pr_pallet / $pr_carton : 0.00
                ]
            ];
        return $data;
    }

    public static function calculete_product_directcost($product)
    {
        $pr_ing = ProdIngredient::where('product_id',$product->id)->get();
        $ing_perkg = 1;
        $weight_total_after = 1;
        if($pr_ing->isNotEmpty()){
            $nutriotn_details = self::calculate_costing_nutrition($pr_ing, $product->batch_baking_loss_percent);
            $ing_perkg = $nutriotn_details['after_kg'] ?? 1;
        }
        $product->load([
            'creator',
            'updater',
            'prodLabours',
            'prodMachinery',
            'prodPackaging'
        ]);

        $costingData = [
            'ingredient' => ['per_kg' => $ing_perkg],
            'packaging' => ['per_kg' => $product->total_packaging_cost_per_kg],
            'machinery' => ['per_kg' => $product->total_machinery_cost_per_kg],
            'labour' => ['per_kg' => $product->total_labour_cost_per_kg],
            'freight' => ['per_kg' => $product->total_freight_cost_per_kg],
        ];
        $costingData['total'] = ['per_kg' => array_sum(array_column($costingData, 'per_kg'))];
        $costingData['contingency'] = [ 'per_kg' => ($costingData['total']['per_kg'] * $product->contingency) / 100];
        return $costingData['total']['per_kg'] + $costingData['contingency']['per_kg'];
    }

    public static function calculate_advanced_cost_modelling_componenet($product){
        $company_directcost = self::calculete_product_directcost($product);
        $directcost = (float) $company_directcost; // Use float for calculations, not formatted string

        // Product weights - ensure they are numeric
        $pr_unit   = (float) ($product->weight_ind_unit_g ?? 0);
        $pr_sell   = (float) ($product->weight_retail_unit_g ?? 0);
        $pr_carton = (float) ($product->weight_carton_g ?? 0);
        $pr_pallet = (float) ($product->weight_pallet_g ?? 0);

        // Pricing details - ensure they are numeric
        $retailer = (float) ($product->retailer_charges ?? 0);
        $wholesale = (float) ($product->wholesale_margin ?? 0);
        $distributor = (float) ($product->distributor_margin ?? 0);
        $retailer_margin = (float) ($product->retailer_margin ?? 0);

        $direct_unit = number_format($directcost * ( $pr_unit / 1000),2,'.', '');
        $direct_sell = number_format($directcost * ( $pr_sell / 1000),2,'.', '');
        $direct_carton = number_format($directcost * ( $pr_carton / 1000),2,'.', '');
        $direct_pallet = number_format($directcost * ( $pr_pallet / 1000),2,'.', '');

        $retail_kg = number_format(($directcost / ( 1- $retailer/ 100)) - $directcost,2,'.', '' );
        $direct_unit_float = (float) str_replace(',', '', $direct_unit);
        $retail_unit = number_format(($direct_unit_float / ( 1- $retailer/ 100)) - $direct_unit_float ,2,'.', '' );
        $direct_sell_float = (float) str_replace(',', '', $direct_sell);
        $direct_carton_float = (float) str_replace(',', '', $direct_carton);
        $direct_pallet_float = (float) str_replace(',', '', $direct_pallet);
        $retail_sell = number_format(($direct_sell_float / ( 1- $retailer/ 100)) - $direct_sell_float,2,'.', '');
        $retail_carton = number_format(($direct_carton_float / ( 1- $retailer/ 100)) - $direct_carton_float ,2,'.', '');
        $retail_pallet = number_format(($direct_pallet_float / ( 1- $retailer/ 100)) - $direct_pallet_float ,2,'.', '' );

        $retail_kg_float = (float) str_replace(',', '', $retail_kg);
        $retail_unit_float = (float) str_replace(',', '', $retail_unit);
        $retail_sell_float = (float) str_replace(',', '', $retail_sell);
        $retail_carton_float = (float) str_replace(',', '', $retail_carton);
        $retail_pallet_float = (float) str_replace(',', '', $retail_pallet);
        $totalkg = number_format($directcost + $retail_kg_float ,2,'.', '')  ;
        $total_unit = number_format($direct_unit_float + $retail_unit_float ,2,'.', '');
        $total_sell = number_format($direct_sell_float + $retail_sell_float,2,'.', '');
        $total_cart = number_format($direct_carton_float + $retail_carton_float ,2,'.', '');
        $total_pall = number_format($direct_pallet_float + $retail_pallet_float,2,'.', '');

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

        $ing_gst = $rrpkg * 0.1;
        $unit_gst = $rrp_unit * 0.1;
        $sell_gst = $rrp_sell * 0.1;
        $carton_gst = $rrp_cart * 0.1;
        $pall_gst = $rrp_pall * 0.1;

        // Safely convert formatted values to floats for percentage calculations
        $rrpkg_float = (float) str_replace(',', '', $rrpkg);
        $wspkg_float = (float) str_replace(',', '', $wspkg);
        $wholesalekg_float = (float) str_replace(',', '', $wholesalekg);
        $distpricekg_float = (float) str_replace(',', '', $distpricekg);
        $retailmarginkg_float = (float) str_replace(',', '', $retailmarginkg);
        $totalkg_float = (float) str_replace(',', '', $totalkg);
        $distkg_float = (float) str_replace(',', '', $distkg);

        $directcost_percent = ($rrpkg_float != 0) ? number_format(($directcost / $rrpkg_float) * 100, 2, '.', '') : 'na';
        $retailcharge_percent = ($rrpkg_float != 0) ? number_format(($retail_kg_float / $rrpkg_float) * 100, 2, '.', '') : 'na';
        $wscost_percent = ($rrpkg_float != 0) ? number_format(($totalkg_float / $rrpkg_float) * 100, 2, '.', '') : 'na';
        $wsmargin_percent = ($rrpkg_float != 0) ? number_format(($wholesalekg_float / $rrpkg_float) * 100, 2, '.', '') : 'na';
        $wsp_percent = ($rrpkg_float != 0) ? number_format(($wspkg_float / $rrpkg_float) * 100, 2, '.', '') : 'na';
        $distmargin_percent = ($rrpkg_float != 0) ? number_format(($distkg_float / $rrpkg_float) * 100, 2, '.', '') : 'na';
        $distprice_percent = ($rrpkg_float != 0) ? number_format(($distpricekg_float / $rrpkg_float) * 100, 2, '.', '') : 'na';
        $retailmargin_percent = ($rrpkg_float != 0) ? number_format(($retailmarginkg_float / $rrpkg_float) * 100, 2, '.', '') : 'na';
        $rrp_percent = ($rrpkg_float != 0) ? number_format(($rrpkg_float / $rrpkg_float) * 100, 2, '.', '') : 'na';

        $combineretail_percent = ($wspkg_float != 0) ? number_format(($wholesalekg_float / $wspkg_float) * 100 ,2,'.', '') : 'na';
        $combinewhole_percent = ($distpricekg_float != 0) ? number_format(($distkg_float / $distpricekg_float) * 100 ,2,'.', '') : 'na';
        $productioncost = ($rrpkg_float != 0) ? number_format(($retailmarginkg_float / $rrpkg_float) * 100 ,2,'.', '') : 'na';

         $data = [
                'direct_cost' => [
                    'per_kg'    => $directcost,
                    'unit'      => $direct_unit,
                    'sell'      => $direct_sell,
                    'carton'    => $direct_carton,
                    'pallet'    => $direct_pallet,
                ],

                'retail_charges' => [
                    'per_kg'     => $retail_kg,
                    'unit'   => $retail_unit,
                    'sell'   => $retail_sell,
                    'carton' => $retail_carton,
                    'pallet' => $retail_pallet,
                ],

                'wholesale_cost' => [
                    'per_kg'     => $totalkg,
                    'unit'   => $total_unit,
                    'sell'   => $total_sell,
                    'carton' => $total_cart,
                    'pallet' => $total_pall,
                ],

                'wholesale_margin' => [
                    'per_kg' => $wholesalekg,
                    'unit'   => $wholesale_unit,
                    'sell'   => $wholesale_sell,
                    'carton' => $wholesale_cart,
                    'pallet' => $wholesale_pall,
                ],

                'wholesale_price' => [
                    'per_kg' => $wspkg,
                    'unit'   => $wsp_unit,
                    'sell'   => $wsp_sell,
                    'carton' => $wsp_cart,
                    'pallet' => $wsp_pall,
                ],

                'distributor_margin' => [
                    'per_kg' => $distkg,
                    'unit'   => $dist_unit,
                    'sell'   => $dist_sell,
                    'carton' => $dist_cart,
                    'pallet' => $dist_pall,
                ],

                'distributor_price' => [
                    'per_kg' => $distpricekg,
                    'unit'   => $distprice_unit,
                    'sell'   => $distprice_sell,
                    'carton' => $distprice_cart,
                    'pallet' => $distprice_pall,
                ],

                'retailer_margin' => [
                    'per_kg' => $retailmarginkg,
                    'unit'   =>$retailmargin_unit,
                    'sell'   =>$retailmargin_sell,
                    'carton' =>$retailmargin_cart,
                    'pallet' =>$retailmargin_pall,
                ],

                'rrp_ex_gst' => [
                    'per_kg' => $rrpkg,
                    'unit'   => $rrp_unit,
                    'sell'   => $rrp_sell,
                    'carton' => $rrp_cart,
                    'pallet' => $rrp_pall,
                ],

                'gst' => [
                    'per_kg'  => $ing_gst,
                    'unit'   => $unit_gst,
                    'sell'   => $sell_gst,
                    'carton' => $carton_gst,
                    'pallet' => $pall_gst,
                ],

                'rrp_inc_gst' => [
                    'per_kg' => $rrpkg + $ing_gst,
                    'unit'   => $rrp_unit + $unit_gst,
                    'sell'   => $rrp_sell + $sell_gst,
                    'carton' => $rrp_cart + $carton_gst,
                    'pallet' => $rrp_pall + $pall_gst,
                ],

                'percentages' => [
                    'directcost'       => $directcost_percent,
                    'retailcharge'     => $retailcharge_percent,
                    'wscost'           => $wscost_percent,
                    'wsmargin'         => $wsmargin_percent,
                    'wholesaleprice'   => $wsp_percent,
                    'distmargin'       => $distmargin_percent,
                    'distprice'        => $distprice_percent,
                    'retailmargin'     => $retailmargin_percent,
                    'rrp'              => $rrp_percent,
                    'combine_retail'   => $combineretail_percent,
                    'combine_wholesale'=> $combinewhole_percent,
                    'production_cost'  => $productioncost,
                ],
                'products' => [
                    'unit' => $pr_unit,
                    'sell' => $pr_sell,
                    'carton' => $pr_carton,
                    'pallet' => $pr_pallet
                ],
                'product_unit' => [
                    'sell' => (floatval($pr_unit) != 0  && floatval($pr_sell) != 0) ? $pr_sell / $pr_unit : 0.00,
                    'carton' => (floatval($pr_carton) != 0  && floatval($pr_sell) != 0) ? $pr_carton / $pr_sell : 0.00,
                    'pallet' => (floatval($pr_pallet) != 0  && floatval($pr_carton) != 0) ? $pr_pallet / $pr_carton : 0.00
                ]
            ];
        return $data;     
    }
   
    public static function calculate_direct_cost_componenet($product,$pr_ing){
        $ing_perkg = 0;
        // $weight_total_after = 1;
        $weight_total_after = ($product->batch_after_waste_g) ?? 1;
        if($pr_ing->isNotEmpty()){
            $nutriotn_details = self::calculate_costing_nutrition($pr_ing, $product->batch_baking_loss_percent);
            $ing_perkg = $nutriotn_details['after_kg']??0;
        }
        $product->load([
            'creator',
            'updater',
            'prodLabours',
            'prodMachinery',
            'prodPackaging'
        ]);

        $total_freight = $product->total_freight_cost_per_kg;
        $total_packaging = $product->total_packaging_cost_per_kg;
        $total_machinery = $product->total_machinery_cost_per_kg;
        $total_labour = $product->total_labour_cost_per_kg;
        $costingData = [
            'ingredient' => [
                'per_kg' => $ing_perkg,
                'per_batch' => ($ing_perkg * $weight_total_after) / 1000,
                'per_unit' =>   ($ing_perkg * $product->weight_ind_unit_g) / 1000,
                'per_sell_unit' => ($ing_perkg * $product->weight_retail_unit_g) / 1000,
                'per_carton' => ($ing_perkg * $product->weight_carton_g) / 1000,
                'per_pallet' => ($ing_perkg * $product->weight_pallet_g) / 1000,
            ],
            'packaging' => [
                'per_kg' => $total_packaging,
                'per_batch' => ($total_packaging * $weight_total_after) / 1000,
                'per_unit' => ($total_packaging * $product->weight_ind_unit_g) / 1000,
                'per_sell_unit' => ($total_packaging * $product->weight_retail_unit_g) / 1000,
                'per_carton' => ($total_packaging * $product->weight_carton_g) / 1000,
                'per_pallet' => ($total_packaging * $product->weight_pallet_g) / 1000,
            ],
            'machinery' => [
                'per_kg' => $total_machinery,
                'per_batch' => ($total_machinery * $weight_total_after) / 1000,
                'per_unit' => ($total_machinery * $product->weight_ind_unit_g) / 1000,
                'per_sell_unit' => ($total_machinery * $product->weight_retail_unit_g) / 1000,
                'per_carton' => ($total_machinery * $product->weight_carton_g) / 1000,
                'per_pallet' => ($total_machinery * $product->weight_pallet_g) / 1000,
            ],
            'labour' => [
                'per_kg' => $total_labour,
                'per_batch' => ($total_labour * $weight_total_after) / 1000,
                'per_unit' => ($total_labour * $product->weight_ind_unit_g) / 1000,
                'per_sell_unit' => ($total_labour * $product->weight_retail_unit_g) / 1000,
                'per_carton' => ($total_labour * $product->weight_carton_g) / 1000,
                'per_pallet' => ($total_labour * $product->weight_pallet_g) / 1000,
            ],
            'freight' => [
                'per_kg' => $total_freight,
                'per_batch' => ($total_freight * $weight_total_after) / 1000,
                'per_unit' => ($total_freight * $product->weight_ind_unit_g) / 1000,
                'per_sell_unit' => ($total_freight * $product->weight_retail_unit_g) / 1000,
                'per_carton' => ($total_freight * $product->weight_carton_g) / 1000,
                'per_pallet' => ($total_freight * $product->weight_pallet_g) / 1000,
            ],
        ];
        // Calculate totals
        $costingData['total'] = [
            'per_kg' => array_sum(array_column($costingData, 'per_kg')),
            'per_batch' => array_sum(array_column($costingData, 'per_batch')),
            'per_unit' => array_sum(array_column($costingData, 'per_unit')),
            'per_sell_unit' => array_sum(array_column($costingData, 'per_sell_unit')),
            'per_carton' => array_sum(array_column($costingData, 'per_carton')),
            'per_pallet' => array_sum(array_column($costingData, 'per_pallet')),
        ];

        // Calculate contingency
        $costingData['contingency'] = [
            'per_kg' => ($costingData['total']['per_kg'] * $product->contingency) / 100,
            'per_batch' => ($costingData['total']['per_batch'] * $product->contingency) / 100,
            'per_unit' => ($costingData['total']['per_unit'] * $product->contingency) / 100,
            'per_sell_unit' => ($costingData['total']['per_sell_unit'] * $product->contingency) / 100,
            'per_carton' => ($costingData['total']['per_carton'] * $product->contingency) / 100,
            'per_pallet' => ($costingData['total']['per_pallet'] * $product->contingency) / 100,
        ];

        // Calculate total direct costs
        $costingData['total_direct'] = [
            'per_kg' => $costingData['total']['per_kg'] + $costingData['contingency']['per_kg'],
            'per_batch' => $costingData['total']['per_batch'] + $costingData['contingency']['per_batch'],
            'per_unit' => $costingData['total']['per_unit'] + $costingData['contingency']['per_unit'],
            'per_sell_unit' => $costingData['total']['per_sell_unit'] + $costingData['contingency']['per_sell_unit'],
            'per_carton' => $costingData['total']['per_carton'] + $costingData['contingency']['per_carton'],
            'per_pallet' => $costingData['total']['per_pallet'] + $costingData['contingency']['per_pallet'],
        ];

        $costingData['rrp'] = [
            'per_kg' => $product->price_ind_unit > 0 && $product->weight_ind_unit_g > 0 ? ($product->price_ind_unit / $product->weight_ind_unit_g) * 1000 : 0,
            'per_batch' => ($product->batch_after_waste_g > 0) ? ($product->price_ind_unit > 0 && $product->weight_ind_unit_g > 0 ? ((($product->price_ind_unit / $product->weight_ind_unit_g) * 1000) * $product->batch_after_waste_g) / 1000 : 0) : 0,
        ];        

        // Calculate margin
        $costingData['margin'] = [
            'per_kg' => $costingData['rrp']['per_kg'] > 0 ? (($costingData['rrp']['per_kg'] - $costingData['total_direct']['per_kg']) / $costingData['rrp']['per_kg']) * 100 : 0,
            'per_batch' => $costingData['rrp']['per_batch'] > 0 ? (($costingData['rrp']['per_batch'] - $costingData['total_direct']['per_batch']) / $costingData['rrp']['per_batch']) * 100 : 0,
            'per_unit' => $product->price_ind_unit > 0 ? (($product->price_ind_unit - $costingData['total_direct']['per_unit']) / $product->price_ind_unit) * 100 : 0,
            'per_sell_unit' => $product->price_retail_unit > 0 ? (($product->price_retail_unit - $costingData['total_direct']['per_sell_unit']) / $product->price_retail_unit) * 100 : 0,
            'per_carton' => $product->price_carton > 0 ? (($product->price_carton - $costingData['total_direct']['per_carton']) / $product->price_carton) * 100 : 0,
            'per_pallet' => $product->price_pallet > 0 ? (($product->price_pallet - $costingData['total_direct']['per_pallet']) / $product->price_pallet) * 100 : 0,
        ];
        $costingData['loss_percent'] = $product->batch_baking_loss_percent;
        return $costingData;
    }

    public static function calculate_nutrition_information_components($product,$ingredients){
        $batchLossPercent = $product->batch_baking_loss_percent; //Loss or gain
        $totalNetQuantity = 0;
        $nutritionData = $peelNameData = [];
        $totalAmount = 0;
        if($ingredients->isNotEmpty()){
            $ingredients = $ingredients->toArray();
            foreach ($ingredients as $ingredient) {
                $model = Ingredient::where('id',$ingredient['ing_id'])->first();
                $specific_gravity = $model->specific_gravity > 0 ? $model->specific_gravity : 1;
                if($ingredient['units_g_ml'] == "g"){
                    $quantity = $ingredient['quantity_weight'];
                }elseif($ingredient['units_g_ml'] == "kg"){
                    $quantity = $ingredient['quantity_weight'] * 1000;
                }elseif($ingredient['units_g_ml'] == "ml" || $ingredient['units_g_ml'] == "mL"){
                    $quantity = $ingredient['quantity_weight'] * $specific_gravity;
                }elseif($ingredient['units_g_ml'] == "l" || $ingredient['units_g_ml'] == "L"){
                    $quantity = ($ingredient['quantity_weight'] * $specific_gravity)*1000;
                }
                // $quantity = $ingredient['quantity_weight'];
                $netQuantity = round($quantity * (1 + ($batchLossPercent / 100))); //Loss or gain
                $totalNetQuantity += $netQuantity;
                $quantityBefore = round($quantity, 1);
                $amount = round($model->price_per_kg_l, 2);
                $tot_amount = $quantityBefore * $amount/ 1000;
                if (in_array($ingredient['units_g_ml'], ['ml','mL','l','L'])) {
                    $tot_amount = $tot_amount * $specific_gravity;
                }
                $totalAmount += $tot_amount;
                $nutritionData[] = [
                    'id' => $model->id,
                    'name' => $model->name_by_kitchen,
                    'cost_per_kg' => $model->price_per_kg_l,
                    'sku' => $model->ing_sku,
                    'image' => $model->ing_image ?: asset('assets/img/ing_default.png'),
                    'component' => $ingredient['component'],
                    'quantity' => $quantityBefore,
                    'net_quantity' => $netQuantity,
                    'amount' => $tot_amount,
                    'energy_kj' => round($model->energy_kj),
                    'protein_g' => round($model->protein_g, 1),
                    'fat_total_g' => round($model->fat_total_g, 1),
                    'fat_saturated_g' => round($model->fat_saturated_g, 1),
                    'carbohydrate_g' => round($model->carbohydrate_g, 1),
                    'sugars_g' => round($model->sugars_g, 1),
                    'sodium_mg' => round($model->sodium_mg),
                    'australian_percent' => $model->australian_percent ?? 0
                ];
            }
            // Calculate mix percentages after all quantities are known
            foreach ($nutritionData as &$item) {
                $item['mix_percent'] = $totalNetQuantity > 0 ? ($item['net_quantity'] / $totalNetQuantity * 100): 0;
            }

            $totals = array_reduce($nutritionData, function ($carry, $item) {
                $carry['quantity'] += $item['quantity'];
                $carry['net_quantity'] += $item['net_quantity'];
                $carry['amount'] += $item['amount'];
                $carry['energy_kj'] += $item['energy_kj'] * ($item['mix_percent'] / 100);
                $carry['protein_g'] += $item['protein_g'] * ($item['mix_percent'] / 100);
                $carry['fat_total_g'] += $item['fat_total_g'] * ($item['mix_percent'] / 100);
                $carry['fat_saturated_g'] += $item['fat_saturated_g'] * ($item['mix_percent'] / 100);
                $carry['carbohydrate_g'] += $item['carbohydrate_g'] * ($item['mix_percent'] / 100);
                $carry['sugars_g'] += $item['sugars_g'] * ($item['mix_percent'] / 100);
                $carry['sodium_mg'] += $item['sodium_mg'] * ($item['mix_percent'] / 100);
                $carry['australian_percent'] += $item['australian_percent'] * $item['mix_percent'];
                return $carry;
            }, [
                'quantity' => 0,
                'net_quantity' => 0,
                'amount' => 0,
                'energy_kj' => 0,
                'protein_g' => 0,
                'fat_total_g' => 0,
                'fat_saturated_g' => 0,
                'carbohydrate_g' => 0,
                'sugars_g' => 0,
                'sodium_mg' => 0,
                'australian_percent' => 0
            ]);

            $response['nutritionData'] = $nutritionData;
            $response['totals'] = $totals;
            $response['loss_percent'] = $batchLossPercent;
            $response['status'] = true;
        }else{
            $response['status'] = false;
        }
        return $response;
    }



    public static function calculate_costing_nutrition($ingredients,$batchLossPercent){
        $totalNetQuantity = 0;  
        $nutritionData = $peelNameData = [];
        $totalAmount = 0;
        foreach ($ingredients as $ingredient) {
            $model = Ingredient::where('id',$ingredient['ing_id'])->first();
            $specific_gravity = $model->specific_gravity > 0 ? $model->specific_gravity : 1;
            if($ingredient['units_g_ml'] == "g"){
                $quantity = $ingredient['quantity_weight'];
            }elseif($ingredient['units_g_ml'] == "kg"){
                $quantity = $ingredient['quantity_weight'] * 1000;
            }elseif($ingredient['units_g_ml'] == "ml" || $ingredient['units_g_ml'] == "mL"){
                $quantity = $ingredient['quantity_weight'] * $specific_gravity;
            }elseif($ingredient['units_g_ml'] == "l" || $ingredient['units_g_ml'] == "L"){
                $quantity = ($ingredient['quantity_weight'] * $specific_gravity)*1000;
            }

            // if(in_array($ingredient['units_g_ml'], ['kg','l','L'])){
            //     $quantity = $ingredient['quantity_weight'] * 1000;
            // }else{
            //     $quantity = $ingredient['quantity_weight'];
            // }
            $netQuantity = round($quantity * (1 + $batchLossPercent / 100)); //Loss or gain
            $totalNetQuantity += $netQuantity;
            $quantityBefore = round($quantity, 1);
            $amount = round($model->price_per_kg_l, 2);
            $tot_amount = $quantityBefore * $amount/ 1000;
            if(in_array($ingredient['units_g_ml'], ['ml','mL','l','L'] )) {
                $tot_amount = $tot_amount * $specific_gravity;
            }
            $totalAmount += $tot_amount;
            $nutritionData[] = [
                'name' => $model->name_by_kitchen,
                'cost_per_kg' => $model->price_per_kg_l,
                'quantity' => $quantityBefore,
                'net_quantity' => $netQuantity,
                'amount' => $tot_amount                
            ];
            $peelNameData[] = [
                'quantity' => $quantity,
                'peel_name' => $model->ingredients_list_supplier ?? ''
            ];
        }

        foreach ($nutritionData as &$item) {
            $item['mix_percent'] = $totalNetQuantity > 0
                ? ($item['net_quantity'] / $totalNetQuantity * 100)
                : 0;   
        }
        $totals = array_reduce($nutritionData, function ($carry, $item) {
            $carry['quantity'] += $item['quantity'];
            $carry['net_quantity'] += $item['net_quantity'];
            $carry['amount'] += $item['amount'];
            return $carry;
        }, [
            'quantity' => 0,
            'net_quantity' => 0,
            'amount' => 0
        ]);

        $final['nutrition'] = $nutritionData;
        $final['totals'] = $totals;
        $final['loss_percent'] = $batchLossPercent;
        if((is_numeric($totals['amount']) &&  floatval($totals['amount']) != 0 )){
            $final['after_loss_percent'] = 100 + ($batchLossPercent);
            $final['afterWeight'] = ($totals['quantity'] * $final['after_loss_percent']) / 100;
            $final['after_kg'] = number_format(($totals['amount'] / $final['afterWeight']) * 1000, 2);    
        }
        return $final;
    }
}