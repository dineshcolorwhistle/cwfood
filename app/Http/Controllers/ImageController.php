<?php

namespace App\Http\Controllers;

use App\Models\ProdTag;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Specification;
use App\Models\Ing_category;
use App\Models\Ing_subcategory;
use App\Models\company;
use App\Models\Ing_country;
use App\Models\Ing_allergen;
use App\Models\image_library;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{

    /**
     * Remove an image from the image library
     */
    public function remove_images($id)
    {
        try {
            $imageDeatils = image_library::where('id', $id)->first()->toArray();

            // if ($imageDeatils['default_image'] == 1) {
            //     $result['status'] = false;
            //     $result['message'] = "Can't delete primary image.";
            //     return response()->json($result);
            // }

            if ($imageDeatils['module'] == "product") {
                $commonDetails = Product::where('id', $imageDeatils['module_id'])
                    ->select('id', 'client_id', 'workspace_id')
                    ->first()
                    ->toArray();
                $client_id = $commonDetails['client_id'];
                $workspace_id = $commonDetails['workspace_id'];
                $dirPath = "assets/{$client_id}/{$workspace_id}/{$imageDeatils['module']}/{$commonDetails['id']}";
            } else if ($imageDeatils['module'] == "raw_material") {
                $commonDetails = Ingredient::where('id', $imageDeatils['module_id'])
                    ->select('id', 'client_id', 'workspace_id')
                    ->first()
                    ->toArray();
                $client_id = $commonDetails['client_id'];
                $workspace_id = $commonDetails['workspace_id'];
                $dirPath = "assets/{$client_id}/{$workspace_id}/{$imageDeatils['module']}/{$commonDetails['id']}";
            }else if ($imageDeatils['module'] == "specification") {
                $commonDetails = Specification::where('id', $imageDeatils['module_id'])
                    ->select('id', 'client_id', 'workspace_id')
                    ->first()
                    ->toArray();
                $client_id = $commonDetails['client_id'];
                $workspace_id = $commonDetails['workspace_id'];
                $dirPath = "assets/{$client_id}/{$workspace_id}/specification_images/{$commonDetails['id']}";
            } else {
                $result['status'] = false;
                $result['message'] = "Invalid module.";
                return response()->json($result);
            }

            $imgname = $imageDeatils['image_name'];
            $response = single_image_remove($dirPath, $imgname);

            if ($response == "success") {
                image_library::where('id', $id)->delete();
                $result['status'] = true;
            } else {
                $result['status'] = false;
                $result['message'] = $response;
            }
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }

        return response()->json($result);
    }
}
