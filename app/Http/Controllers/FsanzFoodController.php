<?php

namespace App\Http\Controllers;

use App\Models\FsanzFood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FsanzFoodController extends Controller
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

    // /**
    //  * Returns a view with the nutrition data of all FSANZ foods.
    //  */
    // public function index(Request $request)
    // {
    //     $foods = FsanzFood::orderBy('name', 'asc')->get();

    //     $first = $foods->first();
    //     $keys  = array_keys($first->getAttributes());

    //     $foodGroups = $foods->map(fn($r) => $r->food_group)->unique()->values();

    //     return view('backend.fsanz_food.manage', compact('foods','foodGroups','keys'));
    // }

    
    /**
     * Returns a view with the FSANZ food page (no data - loaded via AJAX)
     */
    public function index(Request $request)
    {
        // Only fetch food groups for filter dropdown (lightweight query)
        $foodGroups = FsanzFood::select('food_group')
            ->distinct()
            ->whereNotNull('food_group')
            ->where('food_group', '!=', '')
            ->orderBy('food_group')
            ->pluck('food_group');

        return view('backend.fsanz_food.manage', compact('foodGroups'));
    }

    /**
     * Server-side DataTables AJAX endpoint
     * Handles pagination, sorting, searching, and custom filters
     */
    public function getData(Request $request)
    {
        // DataTables parameters
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        $searchValue = $request->input('search.value', '');
        
        // Sorting
        $orderColumnIndex = $request->input('order.0.column', 1);
        $orderDirection = $request->input('order.0.dir', 'asc');
        
        // Map column index to database column name
        $columns = [
            0 => 'id',           // checkbox
            1 => 'name',
            2 => 'fsanz_key',
            3 => 'food_group',
            4 => 'energy_kj',
            5 => 'measurement_basis',
            6 => 'primary_origin_country',
            7 => 'ai_estimation_status',
            8 => 'id',           // actions
        ];
        
        $orderColumn = $columns[$orderColumnIndex] ?? 'name';
        
        // Custom filters
        $measurementFilter = $request->input('measurement_basis', 'all');
        $foodGroupFilter = $request->input('food_group', 'all');
        $originFilter = $request->input('origin', 'all');
        $aiStatusFilter = $request->input('ai_status', 'all');
        
        // Build query
        $query = FsanzFood::query();
        
        // Apply global search
        if (!empty($searchValue)) {
            // Split search value into individual words
            $searchWords = array_filter(array_map('trim', explode(' ', $searchValue)));
            
            $query->where(function($q) use ($searchValue, $searchWords) {
                // Exact phrase match (for backward compatibility and single words)
                $q->where(function($subQ) use ($searchValue) {
                    $subQ->where('name', 'LIKE', "%{$searchValue}%")
                          ->orWhere('fsanz_key', 'LIKE', "%{$searchValue}%")
                          ->orWhere('food_group', 'LIKE', "%{$searchValue}%")
                          ->orWhere('primary_origin_country', 'LIKE', "%{$searchValue}%");
                });
                
                // Multi-word search: all words must be present (can be in different fields or same field)
                if (count($searchWords) > 1) {
                    $q->orWhere(function($subQ) use ($searchWords) {
                        // All words must appear somewhere in searchable fields
                        foreach ($searchWords as $word) {
                            $subQ->where(function($wordQ) use ($word) {
                                $wordQ->where('name', 'LIKE', "%{$word}%")
                                      ->orWhere('fsanz_key', 'LIKE', "%{$word}%")
                                      ->orWhere('food_group', 'LIKE', "%{$word}%")
                                      ->orWhere('primary_origin_country', 'LIKE', "%{$word}%");
                            });
                        }
                    });
                }
            });
        }
        
        // Apply custom filters
        if ($measurementFilter !== 'all' && !empty($measurementFilter)) {
            $query->where('measurement_basis', $measurementFilter);
        }
        
        if ($foodGroupFilter !== 'all' && !empty($foodGroupFilter)) {
            $query->where('food_group', $foodGroupFilter);
        }
        
        if ($originFilter !== 'all' && !empty($originFilter)) {
            if ($originFilter === 'australia') {
                $query->where('primary_origin_country', 'Australia');
            } elseif ($originFilter === 'imported') {
                $query->where('primary_origin_country', '!=', 'Australia')
                      ->whereNotNull('primary_origin_country');
            }
        }
        
        if ($aiStatusFilter !== 'all' && !empty($aiStatusFilter)) {
            if ($aiStatusFilter === 'completed') {
                $query->whereNotNull('ai_estimation_status')
                      ->where('ai_estimation_status', '!=', '');
            } elseif ($aiStatusFilter === 'incomplete') {
                $query->where(function($q) {
                    $q->whereNull('ai_estimation_status')
                      ->orWhere('ai_estimation_status', '');
                });
            }
        }
        
        // Get total count (without filters for recordsTotal)
        $recordsTotal = FsanzFood::count();
        
        // Get filtered count
        $recordsFiltered = $query->count();
        
        // Apply sorting and pagination
        $data = $query->orderBy($orderColumn, $orderDirection)
                      ->skip($start)
                      ->take($length)
                      ->get();
        
        // Format data for DataTables
        $formattedData = $data->map(function($food) {
            return [
                'id' => $food->id,
                'checkbox' => '<div class="form-check-temp p-1">
                    <input class="form-check-input food_check" data-labour="'.$food->id.'" type="checkbox" id="food_'.$food->id.'">
                </div>',
                'name' => $food->name ?? '',
                'fsanz_key' => $food->fsanz_key ?? '',
                'food_group' => $food->food_group ?? '',
                'energy_kj' => rtrim(rtrim(number_format((float)($food->energy_kj ?? 0), 2), '0'), '.') . ' KJ',
                'measurement_basis' => $food->measurement_basis ?? '',
                'primary_origin_country' => $food->primary_origin_country ?? '',
                'ai_estimation_status' => $food->ai_estimation_status ?? '',
                'actions' => $this->getActionsHtml($food),
                'DT_RowId' => 'row_' . $food->id,
            ];
        });
        
        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData,
        ]);
    }
    
    /**
     * Generate actions dropdown HTML for a food item
     */
    private function getActionsHtml($food): string
    {
        $viewUrl = route('fsanz_food.view', ['id' => $food->id]);
        $foodJson = htmlspecialchars(json_encode($food), ENT_QUOTES, 'UTF-8');
        
        return '<div class="dropdown d-flex justify-content-end">
            <button class="icon-primary-orange me-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-symbols-outlined">more_vert</span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a href="'.$viewUrl.'" style="text-decoration:none;">
                    <li>
                        <span class="dropdown-item text-primary-dark-mud me-2 edit-row-data">
                            <span class="sidenav-normal ms-2 ps-1">View Details</span>
                        </span>
                    </li>
                </a>
                <li>
                    <span class="dropdown-item text-primary-dark-mud create-spec-btn" data-food=\''.$foodJson.'\'>
                        <span class="sidenav-normal ms-2 ps-1">Create Specification</span>
                    </span>
                </li>
            </ul>
        </div>';
    }

    /**
     * Display the FSANZ food detail page
     */
    public function view($id)
    {
        $food = FsanzFood::find($id);  

        // Parse JSON fields if stored as strings
        $food->estimated_ingredients = $this->parseJson($food->estimated_ingredients);
        $food->estimated_allergens = $this->parseJson($food->estimated_allergens);
        $food->estimated_dietary_status = $this->parseJson($food->estimated_dietary_status);
        $food->estimated_hazards = $this->parseJson($food->estimated_hazards);
        $food->estimated_processing_info = $this->parseJson($food->estimated_processing_info);
        $food->estimated_regulatory_info = $this->parseJson($food->estimated_regulatory_info);
        $food->estimated_typical_uses = $this->parseJson($food->estimated_typical_uses);
        $food->estimated_origin = $this->parseJson($food->estimated_origin);
        $food->alternative_origin_sources = $this->parseJson($food->alternative_origin_sources);
        $food->functional_category = $this->parseJson($food->functional_category);

        // dd($food);
        return view('backend.fsanz_food.show', compact('food'));

    }

    /**
     * Get FSANZ food data as JSON (API endpoint)
     */
    public function getFoodData($id)
    {
        $food = FsanzFood::find($id);
        
        if (!$food) {
            return response()->json([
                'success' => false,
                'message' => 'Food not found'
            ], 404);
        }

        // Parse JSON fields if stored as strings
        $food->estimated_ingredients = $this->parseJson($food->estimated_ingredients);
        $food->estimated_allergens = $this->parseJson($food->estimated_allergens);
        $food->estimated_dietary_status = $this->parseJson($food->estimated_dietary_status);
        $food->estimated_hazards = $this->parseJson($food->estimated_hazards);
        $food->estimated_processing_info = $this->parseJson($food->estimated_processing_info);
        $food->estimated_regulatory_info = $this->parseJson($food->estimated_regulatory_info);
        $food->estimated_typical_uses = $this->parseJson($food->estimated_typical_uses);
        $food->estimated_origin = $this->parseJson($food->estimated_origin);
        $food->alternative_origin_sources = $this->parseJson($food->alternative_origin_sources);
        $food->functional_category = $this->parseJson($food->functional_category);

        return response()->json([
            'success' => true,
            'data' => $food
        ]);
    }


    /**
     * Parse JSON field safely
     */
    private function parseJson($value)
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * Get confidence badge class based on score
     */
    public static function getConfidenceBadgeClass($score): string
    {
        if (!$score) return 'badge-secondary';
        $percentage = round($score * 100);
        if ($percentage >= 80) return 'badge-success';
        if ($percentage >= 60) return 'badge-warning';
        return 'badge-secondary';
    }
}
