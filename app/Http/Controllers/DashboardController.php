<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\{image_library,Product,Ingredient,support_ticket,support_ticket_comment,ProdIngredient};
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
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

    public function dashboard(Request $request){
        $clientID = $this->clientID;
        $ws_id = $this->ws_id;

        // ------- A) Active tab for the dashboard (default "overview")
        $activeTab = $request->query('tab', 'overview');
        // ------- B) Xero context for the Xero tab
        // tenants linked to *this user* and *this client*
        $xeroTenants = DB::table('xero_connections')
            ->select('tenant_id','tenant_name')
            ->where('client_id', $this->clientID)
            ->orderBy('tenant_name')
            ->get();

        $xeroDefaultFrom = now()->subMonths(11)->startOfMonth()->toDateString();
        $xeroDefaultTo   = now()->toDateString();
        // Last sync per-tenant is loaded by the JSON controller; optional here.
        
        $productList = Product::where('client_id',$clientID)->where('workspace_id',$ws_id)->whereNotNull('prod_image')
                ->get()
                ->map(function ($item) {
                    return image_library::where('module', 'product')
                        ->where('module_id', $item->id)
                        ->get()->toArray();
                })->flatten(1)->toArray();
               
        $productList = array_filter($productList, function ($var){
            return (sizeof($var) > 0);
        });

        $ingredients = Ingredient::where('client_id',$clientID)->where('workspace_id',$ws_id)->where('ing_image', '!=', null)
                ->get()
                ->map(function ($item) {  
                    return image_library::where('module', 'raw_material')
                        ->where('module_id', $item->id)
                        ->get()->toArray();
                })->flatten(1)->toArray();
        $ingredients = array_filter($ingredients, function ($var){
            return (sizeof($var) > 0);
        });

        $support_tickets = support_ticket::where('client_id',$clientID)->where('workspace_id',$ws_id)->where('ticket_image', '!=', 0)
                ->get()
                ->map(function ($item) {  
                    return image_library::where('module', 'support_ticket')
                        ->where('module_id', $item->id)
                        ->get()->toArray();
                })->flatten(1)->toArray();
        $support_tickets = array_filter($support_tickets, function ($var){
            return (sizeof($var) > 0);
        });

        $support_comments = [];
        $tickets = support_ticket::withCount('comments')->where('client_id',$clientID)->where('workspace_id',$ws_id)->get()->toArray();
        foreach ($tickets as $key => $ticket) {
            if($ticket['comments_count'] > 0){  
                $commentIDs = support_ticket_comment::where('ticket_id',$ticket['id'])->where('comment_image', '!=', 0)->pluck('id')->toArray();
                if(count($commentIDs) > 0){
                    $images = image_library::whereIn('module_id',$commentIDs)->where('module', 'support_comment')->get()->toArray();
                    $support_comments = array_merge($support_comments, $images);
                }
            }
        }
        return view('backend.analytics.dashboard', [
            'all'            => array_merge($productList,$ingredients,$support_tickets,$support_comments),
            // Xero tab needs:
            'activeTab'      => $activeTab,
            'xeroTenants'    => $xeroTenants,
            'xeroFrom'       => $xeroDefaultFrom,
            'xeroTo'         => $xeroDefaultTo,
        ]);
        return view('backend.analytics.dashboard', $data);
    }
    

    
    /**
     * Show the application dashboard.
     */
    public function index()
    {

        return view('backend.dashboard.index');
    }

    public function session_update(Request $request)
    {
        $client = $request->input('client');
        $ws = $request->input('ws');
        session()->put('client', $client);
        session()->put('workspace', $ws);
        return response()->json(['status' => true], 200);
    }


    
    public function calculateProductMargins()
    {
        $productList = Product::where('client_id', $this->clientID)->where('workspace_id', $this->ws_id)->where('archive',0)->with(['product_category'])->get();
        // safe division helper
        $safeDivide = function ($num, $den) {
            return ($den != 0) ? $num / $den : 0;
        };
        $products_analysis = [];
        foreach ($productList as $key => $product) {
            if (!empty($product->prod_tags)) {
                // Ensure it's always an array
                $ingArray = is_array($product->prod_tags) 
                    ? $product->prod_tags 
                    : explode(',', $product->prod_tags);
                $tags = DB::table('product_tags')->whereIn('id', $ingArray)->pluck('name');
                $productList[$key]->prod_tags = $tags;
            } else {
                $productList[$key]->prod_tags = [];
            }
            $priceAnalysis = ProductCalculationController::calculate_price_analysis_componenet($product);
            $dc_KG    = ($priceAnalysis['direct_cost']['per_kg']  != 'na') ? round((float)$priceAnalysis['direct_cost']['per_kg'],  2) : 0;
            $dc_Unit  = ($priceAnalysis['direct_cost']['sell']    != 'na') ? round((float)$priceAnalysis['direct_cost']['sell'],    2) : 0;
            $wsp_KG   = ($priceAnalysis['wholesale_price']['per_kg'] != 'na') ? round((float)$priceAnalysis['wholesale_price']['per_kg'], 2) : 0;
            $wsp_Unit = ($priceAnalysis['wholesale_price']['sell']   != 'na') ? round((float)$priceAnalysis['wholesale_price']['sell'],   2) : 0;
            $rrp_KG   = ($priceAnalysis['rrp_ex_gst']['per_kg']  != 'na') ? round((float)$priceAnalysis['rrp_ex_gst']['per_kg'],  2) : 0;
            $rrp_Unit = ($priceAnalysis['rrp_ex_gst']['sell']    != 'na') ? round((float)$priceAnalysis['rrp_ex_gst']['sell'],    2) : 0;
            $margin_KG   = $wsp_KG   - $dc_KG;
            $margin_Unit = $wsp_Unit - $dc_Unit;
            $products_analysis[] = [
                "name" => $product->prod_name,
                "sku" => $product->prod_sku,
                "category" => $product->prod_category? $product->product_category->name:'',
                "tags" => ($product->prod_tags == null || $product->prod_tags == "[]")? '': $product->prod_tags,
                "WholesalePricePerKG" => $wsp_KG,
                "CostPerKG" => $dc_KG,
                "ManufacturerMarginPerKG" => $margin_KG,
                "ManufacturerMarginPercentVsWholesale" => $safeDivide($margin_KG, $wsp_KG),
                "RRPPerKG" => $rrp_KG,
                "ManufacturerMarginPercentVsRRP" => $safeDivide($margin_KG, $rrp_KG),
                "wholesalePrice" => $wsp_Unit,
                "cost" => $dc_Unit,
                "ManufacturerMarginPerUnit" => $margin_Unit,
                "rrp" => $rrp_Unit,
                "ManufacturerMarginPercentVsWholesalePerUnit" => $safeDivide($margin_Unit, $wsp_Unit),
                "ManufacturerMarginPercentVsRRPPerUnit" => $safeDivide($margin_Unit, $rrp_Unit)
            ];
        }
        return view('backend.analytics.unitEconomicAnalytics', compact('products_analysis'));
    }




    public function analytics_old(Request $request)
    {
        $productList = Product::where('client_id', $this->clientID)
                            ->where('workspace_id', $this->ws_id)
                            ->get();

        $sales = []; // scatter plot data

        foreach ($productList as $product) {
            $wholesalePrice   = $product->wholesale_price_sell;
            $distributorPrice = $product->distributor_price_sell;
            $rrpPrice         = $product->rrp_ex_gst_sell;

            $pr_ing = ProdIngredient::where('product_id', $product->id)->get();
            $costingData = ProductCalculationController::calculate_direct_cost_componenet($product, $pr_ing);

            $mapKeys = [
                'ingredient'    => 'Ingredient',
                'packaging'     => 'Packaging',
                'machinery'     => 'Machinery',
                'labour'        => 'Labour',
                'freight'       => 'Freight',
                'total'         => 'Total',
                'total_direct'  => 'Total Direct',
            ];

            foreach ($mapKeys as $key => $label) {
                $xVal = $costingData[$key]['per_kg'] ?? 0;

                // Create 3 scatter points (one per sale price)
                $sales[] = [
                    'x' => $xVal, 
                    'y' => $wholesalePrice,   
                    'name' => $product->prod_name, 
                    'sale_type' => 'Wholesale'
                ];
                $sales[] = [
                    'x' => $xVal, 
                    'y' => $distributorPrice, 
                    'name' => $product->prod_name, 
                    'sale_type' => 'Distributor'
                ];
                $sales[] = [
                    'x' => $xVal, 
                    'y' => $rrpPrice,         
                    'name' => $product->prod_name, 
                    'sale_type' => 'RRP'
                ];
            }
        }
        
        return view('backend.analytics.unitEconomic', compact('sales'));
    }


}
