<?php
// ImageOutPutController.php
namespace App\Http\Controllers;

use ZipArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\{Product, Ingredient, image_library};

class ImageOutPutController extends Controller
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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clientID = $this->clientID;
        $ws_id = $this->ws_id;
        $module = $request->get('module', 'all');
        $data = [
            'selectedModule' => $module,
            'lists' => [],
            'viewType' => 'list'
        ];

        if ($module === 'all' || $module === 'raw_material') {
            $ingredients = Ingredient::where('client_id',$clientID)->where('workspace_id',$ws_id)->where('ing_image', '!=', null)
                ->get()
                ->map(function ($item) {
                    return array_merge($item->toArray(), ['type' => 'raw_material']);
                });
            $data['lists'] = array_merge($data['lists'], $ingredients->toArray());
        }

        if ($module === 'all' || $module === 'product') {
            $products = Product::where('client_id',$clientID)->where('workspace_id',$ws_id)->whereNotNull('prod_image')
                ->get()
                ->map(function ($item) {
                    $images = image_library::where('module', 'product')
                        ->where('module_id', $item->id)
                        ->first();
                    return array_merge($item->toArray(), [
                        'type' => 'product',
                        'image_details' => $images ? $images->toArray() : null
                    ]);
                });
            $data['lists'] = array_merge($data['lists'], $products->toArray());
        }

        return view('backend.image_library.manage', $data);
    }

    public function show_images($id, Request $request)
    {

        $module = $request->get('module', 'raw_material');

        // Get image records from image_library table
        $images = image_library::where('module', $module)
            ->where('module_id', $id)
            ->get();

        // Get module-specific details
        if ($module === 'product') {
            $moduleDetails = Product::where('id', $id)
                ->select('client_id', 'workspace_id as workspace', 'id')
                ->first();
        } else {
            $moduleDetails = Ingredient::where('id', $id)
                ->select('client_id', 'workspace_id as workspace', 'id')
                ->first();
        }

        if (!$moduleDetails) {
            abort(404);
        }

        // Map images with full URLs
        $imagesList = $images->map(function ($image) use ($moduleDetails, $module) {
            $folderPath = "assets/{$moduleDetails->client_id}/{$moduleDetails->workspace}/{$module}/{$moduleDetails->id}";
            $imageUrl = env('APP_URL') . "/{$folderPath}/{$image->image_name}";

            return [
                'id' => $image->id,
                'image_name' => $image->image_name,
                'image_url' => $imageUrl,
                'file_size' => $image->file_size,
                'updated_at' => $image->updated_at,
                'folder_path' => $folderPath
            ];
        })->toArray();

        return view('backend.image_library.show_images', [
            'lists' => $imagesList,
            'module' => $module,
            'moduleDetails' => $moduleDetails,
            'viewType' => 'list'
        ]);
    }

    public function download_images(Request $request)
    {
        try {
            $selectArray = json_decode($request->input('details'));
            $zipFileName = "module_images.zip";
            $zipFilePath = storage_path($zipFileName);
            $zip = new ZipArchive();

            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                foreach ($selectArray as $value) {
                    if ($value->module == "raw_material") {
                        $item = Ingredient::where('id', $value->moduleid)
                            ->select('client_id', 'workspace_id')
                            ->first();

                        if ($item) {
                            $folderPath = "assets/{$item->client_id}/{$item->workspace_id}/raw_material/{$value->moduleid}";

                            if (File::exists($folderPath)) {
                                $images = File::files($folderPath);

                                foreach ($images as $image) {
                                    $relativePath = "Raw_Material_{$value->moduleid}/" . basename($image);
                                    $zip->addFile($image->getPathname(), $relativePath);
                                }
                            }
                        }
                    } elseif ($value->module == "product") {
                        $item = Product::where('id', $value->moduleid)
                            ->select('client_id', 'workspace_id')
                            ->first();

                        if ($item) {
                            $folderPath = "assets/{$item->client_id}/{$item->workspace_id}/product/{$value->moduleid}";

                            if (File::exists($folderPath)) {
                                $images = File::files($folderPath);

                                foreach ($images as $image) {
                                    $relativePath = "Product_{$value->moduleid}/" . basename($image);
                                    $zip->addFile($image->getPathname(), $relativePath);
                                }
                            }
                        }
                    }
                }

                $zip->close();

                return response()->download($zipFilePath, $zipFileName, [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
                ])->deleteFileAfterSend(true);
            }

            return response()->json([
                'status' => false,
                'message' => "Failed to create ZIP file."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
