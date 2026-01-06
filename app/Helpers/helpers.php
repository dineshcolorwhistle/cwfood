<?php

use App\Models\{
    User,
    Client,
    Workspace,
    ClientProfile,
    image_library,
    Labour,
    ProdLabour,
    ProdMachinery,
    Machinery,
    ProdPackaging,
    Packaging,
    ProdIngredient,
    Ingredient,
    ProdFreight,
    Freight,
    Member,
    Members_permission_group,
    Products_permission_group,
    Page,
    Ing_category,Ing_subcategory,Product,IngLabel,
    Client_company,Client_company_tag,Client_company_category,Client_contact,Client_contact_tag,Client_contact_category,
    Product_tag,Product_category,Rawmaterial_tag,Rawmaterial_category,Recipe_component,XeroConnection
};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use App\Http\Controllers\{CognitoUserController,ProductCalculationController};
use Illuminate\Support\Facades\DB;


if (!function_exists('format_content')) {
    /**
     * Format any text or HTML content with proper handling of entities and formatting
     *
     * @param string|null $content The content to be processed
     * @param bool $preserveLineBreaks Whether to preserve line breaks (default: true)
     * @return string
     */
    function format_content(?string $content, bool $preserveLineBreaks = true): string
    {
        if (empty($content)) {
            return '';
        }

        try {
            // Step 1: Clean the content
            $content = trim($content);

            // Step 2: Decode HTML entities
            $content = htmlspecialchars_decode($content, ENT_QUOTES | ENT_HTML5);

            // Step 3: Handle line breaks and formatting
            if ($preserveLineBreaks) {
                // Convert all types of line breaks to unified format
                $content = str_replace(["\r\n", "\r"], "\n", $content);

                // Convert numbered lists properly
                $content = preg_replace('/(\d+\.\s*)/', "<br>$1", $content);

                // Handle remaining line breaks
                $content = nl2br($content);

                // Clean up multiple breaks
                $content = preg_replace('/<br\s*\/?>\s*<br\s*\/?>/i', '<br>', $content);
            }

            // Step 4: Clean up extra whitespace
            $content = preg_replace('/\s+/', ' ', $content);
            $content = preg_replace('/>\s+</', '><', $content);

            return trim($content);
        } catch (\Exception $e) {
            logger()->error('Content formatting error: ' . $e->getMessage());
            // Fallback to basic safe output
            return htmlspecialchars($content, ENT_QUOTES | ENT_HTML5);
        }
    }
}

if (!function_exists('truncateDescription')) {
    function truncateDescription($description, $maxLength = 100)
    {
        $descriptionWithoutTags = strip_tags($description);

        // If the description length is less than or equal to maxLength, return it as is.
        if (strlen($descriptionWithoutTags) <= $maxLength) {
            return $descriptionWithoutTags;
        }

        // Truncate the description if it's longer than the maxLength
        $truncatedDescription = substr($descriptionWithoutTags, 0, $maxLength);

        // Find the last space to avoid cutting off a word
        $lastWhitespacePosition = strrpos($truncatedDescription, ' ');

        // If there's a space, truncate at the space to avoid cutting off the word
        if ($lastWhitespacePosition !== false) {
            $truncatedDescription = substr($truncatedDescription, 0, $lastWhitespacePosition);
        }

        // Add ellipsis if the description is truncated
        return $truncatedDescription . '...';
    }
}


function get_status_array()
{
    // $statusArray = ['Complete', 'Incomplete', 'Peer review', 'Needs Pricing', 'Needs PIF', 'Needs PIF + Pricing', 'Awaiting PIF', 'Awaiting PIF + Pricing', 'Awaiting Testing', 'Needs Country', 'Needs NIP', 'FSANZ'];
    $statusArray = ['In Development','Under Review','Complete','On Hold'];
    return $statusArray;
}

function get_active_array()
{
    // $activeArray = ['Active', 'Inactive', 'Testing', 'Out of date', 'Incomplete', 'FSANZ'];
    $activeArray = ['Active', 'Inactive'];
    return $activeArray;
}


function get_rawmaterial_status_array()
{
    $statusArray = ['New', 'Specifications','Costing','Approved'];
    return $statusArray;
}

function get_rawmaterial_range_array()
{
    $rangeArray = ['Available', 'Not Available','Discontinued'];
    return $rangeArray;
}

function get_products_status_array()
{
    $statusArray = ['Idea', 'Recipe','Costing','Development','Review','Finalised'];
    return $statusArray;
}

function get_products_range_array()
{
    $rangeArray = ['Ranged', 'Not Ranged','Discontinued'];
    return $rangeArray;
}


function convert_decimal($value, $sep)
{
    return round((float)$value, $sep); // Convert to a number
}


function upload_single_files($FILES, $makefilepath)
{
    try {
        $finalArray = [];
        $file_name = $FILES['name'];
        $file_tmp = $FILES['tmp_name'];
        $file_type = $FILES['type'];

        if (!is_dir($makefilepath)) {
            mkdir($makefilepath, 0777, true);
        }

        $filename = str_replace(' ', '_', pathinfo($file_name, PATHINFO_FILENAME));
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $final_name = "$filename.$ext";
        $upload_filename = "$makefilepath/$final_name";

        // 🔹 If file already exists, append _1, _2, etc.
        $counter = 1;
        while (file_exists($upload_filename)) {
            $final_name = "{$filename}_{$counter}.{$ext}";
            $upload_filename = "$makefilepath/$final_name";
            $counter++;
        }

        if (!move_uploaded_file($file_tmp, $upload_filename)) {
            throw new \Exception("Failed to move uploaded file.");
        }

        $finalArray['name'] = $final_name;
        $finalArray['type'] = $ext;
        $result['status'] = true;
        $result['final_array'] = $finalArray;
    } catch (\Exception $e) {
        $result['status'] = false;
        $result['message'] = $e->getMessage();
    }

    return $result;
}



function upload_multiple_files($FILES, $makefilepath)
{
    try {
        $finalArray = [];
        $file_name = $FILES['name'];
        $file_size = $FILES['size'];
        $file_tmp = $FILES['tmp_name'];
        $file_type = $FILES['type'];
        if (!is_dir($makefilepath)) {
            mkdir('./' . $makefilepath, 0777, TRUE);
        }
        foreach ($file_name as $key => $value) {
            $filename = str_replace(' ', '_', pathinfo($value, PATHINFO_FILENAME));
            $ext = pathinfo($value, PATHINFO_EXTENSION);
            $final_name = "$filename.$ext";
            $file_name[$key] = $final_name;
            $upload_filename = "$makefilepath/$final_name";
            move_uploaded_file($file_tmp[$key], $upload_filename);
            $finalArray[$key]['name'] = $final_name;
            $finalArray[$key]['type'] = $ext;
            $fileSizeInMB = $file_size[$key] / (1024 * 1024);
            $finalArray[$key]['size'] = round($fileSizeInMB, 2) . ' MB';
        }
        $result['status'] = true;
        $result['final_array'] = $finalArray;
    } catch (\Exception $e) {
        $result['status'] = false;
        $result['message'] = $e->getMessage();
    }
    return $result;
}

function get_images($module, $moduleID)
{
    return image_library::where('module', $module)->where('module_id', $moduleID)->get()->toArray();
}

function single_image_remove($dirPath, $filename)
{
    try {
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            $temp = explode('/', $file);
            $temp_file = end($temp);
            if ($temp_file == $filename) {
                unlink($file);
            }
        }
        $newfiles = glob($dirPath . '*', GLOB_MARK);
        if (sizeof($newfiles) == 0) {
            rmdir($dirPath);
        }
        $status = "success";
    } catch (\Exception $e) {
        $status = $e->getMessage();
    }
    return $status;
}

function all_image_remove($dirPath)
{
    try {

        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }

        if (!is_dir($dirPath)) {
            return "Provided path is not a directory: " . $dirPath;
        }

        $response = deleteDirectory($dirPath);
        if($response =="success"){
            $status = "success";
        }else{
            $status = $response;
        }

        // $files = glob($dirPath . '*', GLOB_MARK);
        // dd($files);
        // foreach ($files as $file) {
        //     if (is_dir($file)) {
        //         if (substr($file, strlen($file) - 1, 1) != '/') {
        //             $file .= '/';
        //         }
        //         $subfiles = glob($file . '*', GLOB_MARK);
        //         dd($subfiles);
        //         foreach ($subfiles as $subfile) {
        //             if (is_dir($subfile)) {
        //             }else{
        //                 unlink($subfile);
        //             }
        //         }
        //         rmdir($file);
        //     }else{
        //         unlink($file);
        //     }
        // }
        // rmdir($dirPath);
        
    } catch (\Exception $e) {
        $status = $e->getMessage();
    }
    return $status;
}

function deleteDirectory($dirPath) {
            // Get all contents of the directory
        $items = scandir($dirPath);

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue; // Skip special dirs
            }

            $fullPath = $dirPath . DIRECTORY_SEPARATOR . $item;

            if (is_dir($fullPath)) {
                // Recursively delete subdirectory
                deleteDirectory($fullPath);
            } else {
                // Delete file
                if (!unlink($fullPath)) {
                    return "Failed to delete file: " . $fullPath;                    
                }
            }
        }
        // Delete the now-empty directory
        if (!rmdir($dirPath)) {
            return "Failed to remove directory: " . $dirPath;
        }


    return "success";
}


function get_default_image_url($module, $img_number, $id)
{
    $url = "";
    if (in_array($module,["raw_material","product","specification_images"])) {
        $imgDetails = image_library::where('module', $module)->where('module_id', $id)->where('image_number', $img_number)->first();
        if (!$imgDetails) {
            $imgDetails = image_library::where('module', $module)->where('module_id', $id)->first();
        }
        if ($imgDetails) {
            $path = $imgDetails['folder_path'];
            $imgName = $imgDetails['image_name'];
            $url = env('APP_URL') . "/{$path}/{$imgName}";
        } else {
            $url = env('APP_URL') . "/assets/img/prod_default.png";
        }
    }
    return $url;
}

function get_default_image_details($module, $img_number, $id)
{
    $imgDetails = [
        'image_name' => '',
        'updated_at' => '',
        'file_size' => '',
        'last_modified_by' => ''
    ];

    if ($module == "raw_material" || $module == "product") {
        // Fetch the first result
        $image = image_library::where('module', $module)->where('module_id', $id)
            ->where('image_number', $img_number)->first();
        if (!$image) {
            $image = image_library::where('module', $module)->where('module_id', $id)->first();
        }
        if ($image) {
            $imgDetails = $image->toArray();
        }
    }

    return $imgDetails;
}

function get_user_name($id)
{
    $userDetails = User::where('id', $id)->pluck('name')->toArray();
    if(count($userDetails) > 0){
        return $userDetails[0];
    }
    return;
    
}

if (!function_exists('getModuleImage')) {
    function getModuleImage(string $module, int $moduleId, ?int $sequenceNumber = null): string
    {
        // Default image path
        $defaultImagePath = asset('assets/img/prod_default.png');  // Make sure this is correct

        // Check if sequence number is provided
        if (!empty($sequenceNumber)) {
            $imageLibraryRecord = image_library::where('module', $module)
                ->where('module_id', $moduleId)
                ->where('image_number', $sequenceNumber)
                ->first();                

            if ($imageLibraryRecord) {
                // Get the folder and image name from the record
                $folderPath = $imageLibraryRecord->folder_path;  // e.g., '1/1/product/456'
                $imageName = $imageLibraryRecord->image_name;    // e.g., 'image.jpg'
                if (empty($imageName) || $imageName === '.') {
                    // Log::warning("Invalid image name: " . $imageName);
                    return $defaultImagePath;  // Return default image if name is invalid
                }
                // Construct the full path to the image file
                $fullImagePath = $folderPath . '/' . $imageName;
                // Check if the folder exists
                if (File::isDirectory(public_path($folderPath))) {
                    // Check if the image file exists
                    if (File::exists(public_path($fullImagePath))) {                        
                        return asset($fullImagePath);  // Return the URL to the image
                    } else {
                        // Log::warning("Image not found: " . public_path($fullImagePath));
                    }
                } else {
                    //  Log::warning("Folder not found: " . public_path($folderPath));
                }
            }
        }

        // Return the default image if conditions are not met
        return $defaultImagePath;
    }
}

function get_allergen_list()
{
    $capitalizedItems = ['Gluten', 'Wheat', 'Fish', 'Crustacean', 'Mollusc', 'Egg', 'Milk', 'Lupin', 'Peanut', 'Soy', 'Soya', 'Soybean', 'Sesame', 'Almond', 'Brazil Nut', 'Cashew', 'Hazelnut', 'Macadamia', 'Pecan', 'Pistachio', 'Pine Nut', 'Walnut', 'Barley', 'Oats', 'Rye', 'Sulphites'];
    return $capitalizedItems;
}

function get_default_client_list(){
    $data['client_list'] =  Client::orderBy('name','asc')->get()->toArray();
    $first = reset($data['client_list']);
    $data['first_client'] = $first['id'];
    $data['ws_list'] = Workspace::where('client_id',$data['first_client'])->orderBy('name','asc')->get()->toArray();
    $data['first_ws'] = get_default_ws($data['first_client']);
    return $data;
}

function get_client_list_using_userID($userId){
    $clientIDs = Member::where('user_id',$userId)->pluck('client_id');  
    $data['client_list'] =  Client::whereIn('id',$clientIDs)->orderBy('name','asc')->get()->toArray();
    return $data;
}

function get_default_ws($id){
    $ws = Workspace::where('client_id',$id)->where('ws_primary',1)->pluck('id');
    if($ws->isEmpty()) {
        return;
    }else{
        return $ws[0];    
    }
}

function get_ws_list_based_clientID($id){
    return Workspace::where('client_id',$id)->orderBy('name','asc')->get()->toArray();
}

function get_workspace_scope_wslist($cid,$uid){
    $members = Member::where('user_id',$uid)->where('client_id',$cid)->first();
    if($members && $members->assign_workspace){
        $memberID = json_decode($members->assign_workspace);
        return Workspace::whereIn('id',$memberID)->orderBy('name','asc')->get()->toArray();
    }
    return;
}

function get_ws_list_based_usreID($cid,$uid){
    $members = Member::where('user_id',$uid)->first();
    if($members && $members->assign_workspace){
        $memberID = json_decode($members->assign_workspace);
        return Workspace::whereIn('id',$memberID)->orderBy('name','asc')->get()->toArray();
    }else{
        return Workspace::where('client_id',$cid)->orderBy('name','asc')->get()->toArray();
    }
}

function get_default_member_ws($cid,$uid){
    $members = Member::where('user_id',$uid)->first();
    if($members && $members->assign_workspace){
        $memberID = json_decode($members->assign_workspace);
        return $memberID[0];
    }else{
        $ws = Workspace::where('client_id',$cid)->where('ws_primary',1)->pluck('id');
        return $ws[0];
    }    
}

function finding_numbers($num){
    $num = (string)$num;
    $len = strlen($num);
    $final = "";
    if($len == 1){
        $final="00$num";    
    }else if($len == 2){
        $final="0$num";
    }else if($len == 3){
        $final="$num";
    }
    return $final;
}

function copy_files($source,$dest){
    try {
        if (!is_dir($dest)) {
            mkdir('./'. $dest, 0777, TRUE);
        }

        if (substr($dest, strlen($dest) - 1, 1) != '/') {
            $dest .= '/';
        }
        if (substr($source, strlen($source) - 1, 1) != '/') {
            $source .= '/';
        }
        $files = glob($source . '*', GLOB_MARK);
        foreach ($files as $key => $value) {
            $fileArray = explode('/',$value);
            $file = end($fileArray);
            copy("$source/$file", "$dest/$file");
        }
        $message = true;
    } catch (\Exception $e) {
        $message = $e->getMessage();
    }
   return $message;
}

if (!function_exists('get_client_logo')) {
    function get_client_logo()
    {
        $clientID = session('client'); // Fetch session data
        $profile = ClientProfile::where('client_id', $clientID)->first();
        
        return $profile && $profile->company_logo_url 
            ? asset($profile->company_logo_url) 
            : asset('assets/img/default-company-logo.png');
    }
}

function increment_numbers($string){
    $number = (int) $string + 1;
    if($number < 10){
        $num_text = "00$number";
    }elseif ($number > 9 && $number < 100) {
        $num_text = "0$number";
    }elseif ($number > 99 && $number < 1000) {
        $num_text = "$number";
    }
    return $num_text;
}


function get_product_tooltip($product){
    $tooltip = [];
    $ing = $product->productIngredients()->get();
    if($ing->isEmpty() == true){
        $tooltip[] = "Ingredient";
    }
    $lab = $product->prodLabours()->get();
    if($lab->isEmpty() == true){
        $tooltip[] = "Labour";
    }
    $mach = $product->prodMachinery()->get();
    if($mach->isEmpty() == true){
        $tooltip[] = "Machinery";
    }
    $pack = $product->prodPackaging()->get();
    if($pack->isEmpty() == true){
        $tooltip[] = "Packaging";
    }
    if(sizeof($tooltip) > 0){
        $string = "Following modules not complete. ".implode(',',$tooltip);
    }else{
        $string = "";
    }
    return $string;
}


function get_assign_workspace_names($assignArray){
    $nameArray = [];
    foreach ($assignArray as $key => $value) {
        $temp = Workspace::where('id',$value)->pluck('name');
        $nameArray[] = (sizeof($temp)>0)?$temp[0]:"";
    }
    $html = (sizeof($nameArray)>0)? implode(',',$nameArray):"";
    return $html;
}


function get_member_permission($userID,$cid,$pageArray){
    $member = Member::where('user_id',$userID)->first();
    $member_permission = Members_permission_group::where('member_id',$member->id)->first();
    $final = [];
    if($member_permission){
        if($member_permission->client_permission_group){
            $mem_permission = json_decode($member_permission->client_permission_group);
            $pageIDs =  Page::whereIn('title', $pageArray)->pluck('id')->toArray();
            foreach ($pageIDs as $key => $pageID) {
                $pr_perm = Products_permission_group::where('client_id',$cid)->where('product_id',$pageID)->first();                
                if($pr_perm){
                    if($pr_perm->product_permission_group){
                        $product_Array = json_decode($pr_perm->product_permission_group);
                        $matchingValues = array_intersect($mem_permission, $product_Array);
                        if(sizeof($matchingValues) > 0){
                            $final[$pageArray[$key]] = true;
                        }else{
                            $final[$pageArray[$key]] = false;    
                        }
                    }else{
                        $final[$pageArray[$key]] = false;    
                    }
                }else{
                    $final[$pageArray[$key]] = false;
                }
            }
            $result['status'] = true;
            $result['page'] = $final;
        }else{
            $result['status'] = false;
        }
    }else{
        $result['status'] = false;
    }
    return $result;
}

function get_category_name($id){
    $category = Ing_category::where('CID',$id)->select('name')->first();
    if($category){
        return $category->name;
    }else{
        return;
    }
    
}

function get_sub_category_name($id){
    $s_category = Ing_subcategory::where('SCID',$id)->select('name')->first();
    if($s_category){
        return $s_category->name;
    }else{
        return;
    }
    
}

function active_name($id){
    $status = get_active_array();
    return $status[$id];
}

function create_default_ingredients_tags($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace){
    $categories = Rawmaterial_category::where('client_id', $defaultClient)->where('workspace_id',$defaultClientWorkspace)->pluck('name')->toArray();
    $tags = Rawmaterial_tag::where('client_id', $defaultClient)->where('workspace_id',$defaultClientWorkspace)->pluck('name')->toArray();
    $components = Recipe_component::where('client_id', $defaultClient)->where('workspace_id',$defaultClientWorkspace)->pluck('name')->toArray();
    
    foreach ($categories as $key => $category) {
        $categoryData['client_id'] = $clientID;
        $categoryData['workspace_id'] = $workspaceID;
        $categoryData['name'] = $category;
        $categoryData['created_by'] = session('user_id');
        $categoryData['updated_by'] = session('user_id');
        Rawmaterial_category::create($categoryData); 
    }

    foreach ($tags as $key => $Tag) {
        $tagData['client_id'] = $clientID;
        $tagData['workspace_id'] = $workspaceID;
        $tagData['name'] = $Tag;
        $tagData['created_by'] = session('user_id');
        $tagData['updated_by'] = session('user_id'); 
        Rawmaterial_tag::create($tagData); 
    }

    foreach ($components as $key => $component) {
        $componentData['client_id'] = $clientID;
        $componentData['workspace_id'] = $workspaceID;
        $componentData['name'] = $component;
        $componentData['created_by'] = session('user_id');
        $componentData['updated_by'] = session('user_id'); 
        Recipe_component::create($componentData); 
    }
}

function create_default_ingredients($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace){
    create_default_ingredients_tags($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace);
    $Ingredient = Ingredient::where('client_id',$defaultClient)->where('workspace_id',$defaultClientWorkspace)->get()->toArray();
    $deleteItem = ['id','client_id','workspace_id','created_at','updated_at','created_by','updated_by'];
    $deleteImageItem = ['id','module_id','folder_path','uploaded_by','last_modified_by','created_at','updated_at'];
    foreach ($Ingredient as $key => $value) {
        $exstImages = image_library::where('module','raw_material')->where('module_id',$value['id'])->get()->toArray();
        $source = "assets/{$value['client_id']}/{$value['workspace_id']}/raw_material/{$value['id']}";
        $filteredData = Arr::except($value, $deleteItem);
        $filteredData['client_id'] = $clientID;
        $filteredData['workspace_id'] = $workspaceID;
        $filteredData['created_by'] = session('user_id');
        $filteredData['updated_by'] = session('user_id');
        if($filteredData['category']){
            $temp_cate = Rawmaterial_category::where('id', $filteredData['category'])->first();
            if($temp_cate){
                $new_cat = Rawmaterial_category::where('client_id',$clientID)->where('workspace_id',$workspaceID)->where('name',$temp_cate->name)->first();
                if($new_cat){
                    $filteredData['category'] = $new_cat->id;
                }
            }
        }

        if($filteredData['ing_tags']){
            $cmpArray = json_decode($filteredData['ing_tags']);
            $tags = Rawmaterial_tag::whereIn('id', $cmpArray)->pluck('name')->toArray();
            if(count($tags) > 0){
                $temp_tag = Rawmaterial_tag::where('client_id',$clientID)->where('workspace_id',$workspaceID)->whereIn('name',$tags)->pluck('id');
                if($temp_tag){
                    $filteredData['ing_tags'] = json_encode($temp_tag);
                }
            }
        }

        $newIng = Ingredient::create($filteredData);
        if($value['ing_image']){
            $dest =  "assets/{$clientID}/{$workspaceID}/raw_material/{$newIng->id}";
            foreach ($exstImages as $key => $exstImage) {
                $filteredImageData = Arr::except($exstImage, $deleteImageItem);
                $filteredImageData['module_id'] = $newIng->id;
                $filteredImageData['folder_path'] = "assets/$clientID/$workspaceID/raw_material/{$newIng->id}";
                $filteredImageData['uploaded_by'] = session('user_id');
                $filteredImageData['last_modified_by'] = session('user_id');
                image_library::create($filteredImageData);
            }
            copy_files($source, $dest);
        }
    }
    return;
}

function create_default_labours($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace){
    $Labours = Labour::where('client_id',$defaultClient)->where('workspace_id',$defaultClientWorkspace)->get()->toArray();
    $deleteItem = ['id','client_id','workspace_id','labour_id','created_at','updated_at','created_by','updated_by'];
    foreach ($Labours as $key => $Labour) {
        $labCode = ++$key;
        if($labCode < 10){
            $lab_id = "00$labCode";
        }elseif ($labCode > 9 && $labCode < 99) {
            $lab_id = "0$labCode";
        }elseif ($labCode > 99 && $labCode < 999) {
            $lab_id = "$labCode";
        }
        $filteredData = Arr::except($Labour, $deleteItem);
        $filteredData['labour_id'] = "{$clientID}{$workspaceID}_{$lab_id}";
        $filteredData['client_id'] = $clientID;
        $filteredData['workspace_id'] = $workspaceID;
        $filteredData['created_by'] = session('user_id');
        $filteredData['updated_by'] = session('user_id');
        Labour::create($filteredData);
    }
    return;
}

function create_default_machinery($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace){
    $Machinery = Machinery::where('client_id',$defaultClient)->where('workspace_id',$defaultClientWorkspace)->get()->toArray();
    $deleteItem = ['id','client_id','workspace_id','machinery_id','created_at','updated_at','created_by','updated_by'];
    foreach ($Machinery as $key => $Machine) {
        $machineCode = ++$key;
        if($machineCode < 10){
            $machine_id = "00$machineCode";
        }elseif ($machineCode > 9 && $machineCode < 99) {
            $machine_id = "0$machineCode";
        }elseif ($machineCode > 99 && $machineCode < 999) {
            $machine_id = "$machineCode";
        }
        $filteredData = Arr::except($Machine, $deleteItem);
        $filteredData['machinery_id'] = "{$clientID}{$workspaceID}_{$machine_id}";
        $filteredData['client_id'] = $clientID;
        $filteredData['workspace_id'] = $workspaceID;
        $filteredData['created_by'] = session('user_id');
        $filteredData['updated_by'] = session('user_id');
        Machinery::create($filteredData);
    }
    return;
}

function create_default_packaging($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace){
    $Packaging = Packaging::where('client_id',$defaultClient)->where('workspace_id',$defaultClientWorkspace)->get()->toArray();
    $deleteItem = ['id','client_id','workspace_id','package_id','created_at','updated_at','created_by','updated_by'];
    foreach ($Packaging as $key => $pack) {
        $packageCode = ++$key;
        if($packageCode < 10){
            $package_id = "00$packageCode";
        }elseif ($packageCode > 9 && $packageCode < 99) {
            $package_id = "0$packageCode";
        }elseif ($packageCode > 99 && $packageCode < 999) {
            $package_id = "$packageCode";
        }
        $filteredData = Arr::except($pack, $deleteItem);
        $filteredData['package_id'] = "{$clientID}{$workspaceID}_{$package_id}";
        $filteredData['client_id'] = $clientID;
        $filteredData['workspace_id'] = $workspaceID;
        $filteredData['created_by'] = session('user_id');
        $filteredData['updated_by'] = session('user_id');
        Packaging::create($filteredData);
    }
    return;
}

function create_default_freight($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace){
    $Freights = Freight::where('client_id',$defaultClient)->where('workspace_id',$defaultClientWorkspace)->get()->toArray();
    $deleteItem = ['id','client_id','workspace_id','created_at','updated_at','created_by','updated_by'];
    foreach ($Freights as $key => $Freight) {
        $filteredData = Arr::except($Freight, $deleteItem);
        $filteredData['client_id'] = $clientID;
        $filteredData['workspace_id'] = $workspaceID;
        $filteredData['created_by'] = session('user_id');
        $filteredData['updated_by'] = session('user_id');
        Freight::create($filteredData);
    }
    return;
}

function create_default_product_tags($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace){
    $categories = Product_category::where('client_id', $defaultClient)->where('workspace_id',$defaultClientWorkspace)->pluck('name')->toArray();
    $tags = Product_tag::where('client_id', $defaultClient)->where('workspace_id',$defaultClientWorkspace)->pluck('name')->toArray();
    
    foreach ($categories as $key => $category) {
        $categoryData['client_id'] = $clientID;
        $categoryData['workspace_id'] = $workspaceID;
        $categoryData['name'] = $category;
        $categoryData['created_by'] = session('user_id');
        $categoryData['updated_by'] = session('user_id');
        Product_category::create($categoryData); 
    }

    foreach ($tags as $key => $Tag) {
        $tagData['client_id'] = $clientID;
        $tagData['workspace_id'] = $workspaceID;
        $tagData['name'] = $Tag;
        $tagData['created_by'] = session('user_id');
        $tagData['updated_by'] = session('user_id'); 
        Product_tag::create($tagData); 
    }
    return;
}


function create_default_products($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace){
    try {
        create_default_product_tags($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace);
        $Products = Product::where('client_id',$defaultClient)->where('workspace_id',$defaultClientWorkspace)->get()->toArray();
        $deleteItem = ['id','client_id','workspace_id','created_at','updated_at','created_by','updated_by'];
        foreach ($Products as $key => $Product) {
            $pid = $Product['id'];
            $filteredData = Arr::except($Product, $deleteItem);
            $filteredData['prod_sku'] = $Product['prod_sku']."_$clientID";
            $filteredData['client_id'] = $clientID;
            $filteredData['workspace_id'] = $workspaceID;
            $filteredData['created_by'] = session('user_id');
            $filteredData['updated_by'] = session('user_id');  
            if($filteredData['prod_category']){
                $temp_cate = Product_category::where('id', $filteredData['prod_category'])->first();
                if($temp_cate){
                    $new_cat = Product_category::where('client_id',$clientID)->where('workspace_id',$workspaceID)->where('name',$temp_cate->name)->first();
                    if($new_cat){
                        $filteredData['prod_category'] = $new_cat->id;
                    }
                }
            }
            if($filteredData['prod_tags']){
                $cmpArray = $filteredData['prod_tags'];
                $tags = Product_tag::whereIn('id', $cmpArray)->pluck('name')->toArray();
                if(count($tags) > 0){
                    $temp_tag = Product_tag::where('client_id',$clientID)->where('workspace_id',$workspaceID)->whereIn('name',$tags)->pluck('id');
                    if($temp_tag){
                        $temp_tag = $temp_tag->map(fn($item) => (string) $item)->toArray();
                        $filteredData['prod_tags'] = $temp_tag;
                    }
                }
            }
            $new_product = Product::create($filteredData);
            $new_productID = $new_product->id;
            if($filteredData['prod_image'] != null){
                duplicate_product_image($filteredData,$pid,$new_productID,$defaultClient,$defaultClientWorkspace);
            }
            duplicate_product_ingredients($pid,$new_productID,$clientID,$workspaceID);
            duplicate_product_labor($pid,$new_productID,$clientID,$workspaceID);
            duplicate_product_machinery($pid,$new_productID,$clientID,$workspaceID);
            duplicate_product_packaging($pid,$new_productID,$clientID,$workspaceID);
            duplicate_product_freight($pid,$new_productID,$clientID,$workspaceID);
            
        }
    } catch (\Exception $e) {
        dd($e->getMessage());
    }

    return;
}

function duplicate_product_image($product,$pid,$new_productID,$defaultClient,$defaultClientWorkspace){
    $imgDetails  = image_library::where('module', 'product')->where('module_id', $pid)->get()->toArray();
    $filepath = "assets/{$product['client_id']}/{$product['workspace_id']}/product/{$new_productID}";
    foreach($imgDetails as $img){
        $img_item = new image_library;
        $img_item->SKU = $img['SKU']."_{$new_productID}";
        $img_item->module =  $img['module'];
        $img_item->module_id = $new_productID;
        $img_item->image_number =  $img['image_number'];
        $img_item->image_name =  $img['image_name'];
        $img_item->default_image =  $img['default_image'];
        $img_item->file_format =  $img['file_format'];
        $img_item->file_size =  $img['file_size'];
        $img_item->folder_path = $filepath;
        $img_item->uploaded_by = session('user_id');
        $img_item->last_modified_by = session('user_id');
        $img_item->save();
    }
    $source = "assets/{$defaultClient}/{$defaultClientWorkspace}/product/{$pid}";
    $dest =  "assets/{$product['client_id']}/{$product['workspace_id']}/product/{$new_productID}";
    return copy_files($source, $dest);
}

function duplicate_product_ingredients($pid,$new_productID,$clientID,$workspaceID){
    $prod_ings = ProdIngredient::where('product_id',$pid)->get()->toArray();
    $deleteItem = ['id','product_id','ing_id','created_at','updated_at'];
    foreach ($prod_ings as $key => $value) {
        $ingId = get_ingredient_id($value['ing_id'],$clientID,$workspaceID);
        if($ingId > 0){
            $filteredData = Arr::except($value, $deleteItem);
            $filteredData['product_id'] = $new_productID;
            $filteredData['ing_id'] = $ingId;
            ProdIngredient::create($filteredData);
        }
    }
    return;
}

function get_ingredient_id($ing_id,$clientID,$workspaceID){
    $exst_ing = Ingredient::where('id',$ing_id)->first();
    $ingId = 0;
    if($exst_ing){
        $new_ing = Ingredient::where('client_id',$clientID)->where('workspace_id',$workspaceID)->where('ing_sku',$exst_ing->ing_sku)->first();
        if($new_ing){
            $ingId = $new_ing->id;
        }
    }
    return $ingId;
}

function duplicate_product_labor($pid,$new_productID,$clientID,$workspaceID){
    $prod_labour = ProdLabour::where('product_id',$pid)->get()->toArray();
    $deleteItem = ['id','product_id','labour_id','created_at','updated_at','created_by','updated_by'];
    foreach ($prod_labour as $key => $value) {
        $labId = get_labour_id($value['labour_id'],$clientID,$workspaceID);
        if($labId > 0){
            $filteredData = Arr::except($value, $deleteItem);
            $filteredData['product_id'] = $new_productID;
            $filteredData['labour_id'] = $labId;
            $filteredData['created_by'] = session('user_id');
            $filteredData['updated_by'] = session('user_id'); 
            ProdLabour::create($filteredData);
        }
    }
    return;
}

function get_labour_id($lab_id,$clientID,$workspaceID){
    $exst_lab = Labour::where('id',$lab_id)->first();
    $labId = 0;
    if($exst_lab){
        $new_lab = Labour::where('client_id',$clientID)->where('workspace_id',$workspaceID)->where('labour_type',$exst_lab->labour_type)->first();
        if($new_lab){
            $labId = $new_lab->id;
        }
    }
    return $labId;
}

function duplicate_product_machinery($pid,$new_productID,$clientID,$workspaceID){
    $prod_machines = ProdMachinery::where('product_id',$pid)->get()->toArray();
    $deleteItem = ['id','product_id','machinery_id','created_at','updated_at','created_by','updated_by'];
    foreach ($prod_machines as $key => $value) {
        $macId = get_machine_id($value['machinery_id'],$clientID,$workspaceID);
        if($macId > 0){
            $filteredData = Arr::except($value, $deleteItem);
            $filteredData['product_id'] = $new_productID;
            $filteredData['machinery_id'] = $macId;
            $filteredData['created_by'] = session('user_id');
            $filteredData['updated_by'] = session('user_id'); 
            ProdMachinery::create($filteredData);
        }
    }
    return;
}

function get_machine_id($machine_id,$clientID,$workspaceID){
    $exst_machines = Machinery::where('id',$machine_id)->first();
    $macId = 0;
    if($exst_machines){
        $new_mach = Machinery::where('client_id',$clientID)->where('workspace_id',$workspaceID)->where('name',$exst_machines->name)->first();
        if($new_mach){
            $macId = $new_mach->id;
        }
    }
    return $macId;
}

function duplicate_product_packaging($pid,$new_productID,$clientID,$workspaceID){
    $prod_packs = ProdPackaging::where('product_id',$pid)->get()->toArray();
    $deleteItem = ['id','product_id','packaging_id','created_at','updated_at','created_by','updated_by'];
    foreach ($prod_packs as $key => $value) {
        $packId = get_packing_id($value['packaging_id'],$clientID,$workspaceID);
        if($packId > 0){
            $filteredData = Arr::except($value, $deleteItem);
            $filteredData['product_id'] = $new_productID;
            $filteredData['packaging_id'] = $packId;
            $filteredData['created_by'] = session('user_id');
            $filteredData['updated_by'] = session('user_id'); 
            ProdPackaging::create($filteredData);
        }
    }
    return;
}

function get_packing_id($pack_id,$clientID,$workspaceID){
    $exst_pack = Packaging::where('id',$pack_id)->first();
    $packId = 0;
    if($exst_pack){
        $new_pack = Packaging::where('client_id',$clientID)->where('workspace_id',$workspaceID)->where('name',$exst_pack->name)->first();
        if($new_pack){
            $packId = $new_pack->id;
        }
    }
    return $packId;
}

function duplicate_product_freight($pid,$new_productID,$clientID,$workspaceID){
    $prod_packs = ProdFreight::where('product_id',$pid)->get()->toArray();
    $deleteItem = ['id','product_id','freight_id','created_at','updated_at','created_by','updated_by'];
    foreach ($prod_packs as $key => $value) {
        $packId = get_freight_id($value['freight_id'],$clientID,$workspaceID);
        if($packId > 0){
            $filteredData = Arr::except($value, $deleteItem);
            $filteredData['product_id'] = $new_productID;
            $filteredData['freight_id'] = $packId;
            $filteredData['created_by'] = session('user_id');
            $filteredData['updated_by'] = session('user_id'); 
            ProdFreight::create($filteredData);
        }
    }
    return;
}

function get_freight_id($freight_id,$clientID,$workspaceID){
    $exst_freight = Freight::where('id',$freight_id)->first();
    $FreightId = 0;
    if($exst_freight){
        $new_freight = Freight::where('client_id',$clientID)->where('workspace_id',$workspaceID)->where('name',$exst_freight->name)->first();
        if($new_freight){
            $FreightId = $new_freight->id;
        }
    }
    return $FreightId;
}

// Remove workspace related details
function delete_workspace_details($wsId,$clID,$type=''){
    try {
        if($type == ''){
            remove_workspace_users($wsId,$clID);
        }
        remove_workspace_resources($wsId,$clID);
        remove_workspace_ingredients($wsId,$clID);
        remove_workspace_products($wsId,$clID);
        $result['status'] = true;
    } catch (\Exception $e) {
        $result['status'] = false;
        $result['message'] = $e->getMessage();
    }
    return $result;
}

function remove_workspace_users($wsId,$clID){
    $members = Member::where('client_id',$clID)->pluck('user_id')->toArray();
    if(sizeof($members) > 0){
        foreach ($members  as $key => $value) {
            try {
                $userDetails = User::where('id',$value)->first();
                $controller = app(\App\Http\Controllers\CognitoUserController::class);
                $response = $controller->destroy($userDetails->email);

                if(Member::where('user_id',$value)->count() == 1){
                    User::where('id',$value)->delete();
                }
            } catch (\Exception $e) {
                //throw $th;
                continue;
            }
        }
        // User::whereIn('id',$members)->delete();
        Member::where('client_id',$clID)->delete();
    }
    return;
}

function remove_workspace_resources($wsId,$clID){
    Labour::where('client_id', $clID)->where('workspace_id', $wsId)->delete();
    Machinery::where('client_id', $clID)->where('workspace_id', $wsId)->delete();
    Packaging::where('client_id', $clID)->where('workspace_id', $wsId)->delete();
    Freight::where('client_id', $clID)->where('workspace_id', $wsId)->delete();
    return;
}

function remove_workspace_ingredients($wsId,$clID){
    $ings = Ingredient::where('client_id', $clID)->where('workspace_id', $wsId)->get()->toArray();
    if(sizeof($ings) > 0){
        foreach ($ings as $key => $ingDetails) {
            if($ingDetails['ing_image']){
                $dirPath = "assets/{$ingDetails['client_id']}/{$ingDetails['workspace_id']}/raw_material/{$ingDetails['id']}";
                $response = all_image_remove($dirPath);
                
                IngLabel::where('rawmaterial_id', $ingDetails['id'])->delete();
                image_library::where('module', 'raw_material')->where('module_id', $ingDetails['id'])->delete();
                Ingredient::where('id', $ingDetails['id'])->delete();
                
            }else{
                IngLabel::where('rawmaterial_id', $ingDetails['id'])->delete();
                Ingredient::where('id', $ingDetails['id'])->delete();
            }
        }
    }

    Rawmaterial_category::where('client_id', $clID)->where('workspace_id', $wsId)->delete();
    Rawmaterial_tag::where('client_id', $clID)->where('workspace_id', $wsId)->delete();
    Recipe_component::where('client_id', $clID)->where('workspace_id', $wsId)->delete();
    return;
}

function remove_workspace_products($wsId,$clID){
    $products = Product::where('client_id', $clID)->where('workspace_id', $wsId)->get();
    if($products->count() > 0){
        foreach ($products as $key => $product) {
            $product->productIngredients()->delete();
            $product->prodLabours()->delete();
            $product->prodMachinery()->delete();
            $product->prodPackaging()->delete();
            $product->productLabels()->delete();
            $product->prodFreights()->delete();
            $images = $product->imageLibrary()->get();
            foreach ($images as $image) {
                $imageName = $image->image_name;
                if ($image->folder_path && !empty($imageName) && $imageName != '.') {
                    $fullPath = public_path($image->folder_path . '/' . $image->image_name);
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                    $image->delete();
                }
            }
            $product->delete();
        }
    }
    Product_category::where('client_id', $clID)->where('workspace_id', $wsId)->delete();
    Product_tag::where('client_id', $clID)->where('workspace_id', $wsId)->delete();
    return;
}

function get_subreceipe_details($id){
    return Ingredient::where('id',$id)->pluck('ing_sku')->toArray();
}

function create_comapny_default_category($clientID,$defaultClient){
    $CompanyCategory = Client_company_category::where('client_id',$defaultClient)->pluck('name')->toArray();
    foreach ($CompanyCategory as $key => $category) {
        $categoryData['client_id'] = $clientID;
        $categoryData['name'] = $category;
        $categoryData['created_by'] = session('user_id');
        $categoryData['updated_by'] = session('user_id');
        Client_company_category::create($categoryData); 
    }
    return;
}

function create_comapny_default_tags($clientID,$defaultClient){
    $CompanyTag =Client_company_tag::where('client_id',$defaultClient)->pluck('name')->toArray();
    foreach ($CompanyTag as $key => $Tag) {
        $tagData['client_id'] = $clientID;
        $tagData['name'] = $Tag;
        $tagData['created_by'] = session('user_id');
        $tagData['updated_by'] = session('user_id'); 
        Client_company_tag::create($tagData); 
    }
    return;
}

function create_contact_default_category($clientID,$defaultClient){
    $ContactCategory = Client_contact_category::where('client_id',$defaultClient)->pluck('name')->toArray();
    foreach ($ContactCategory as $key => $category) {
        $categoryData['client_id'] = $clientID;
        $categoryData['name'] = $category;
        $categoryData['created_by'] = session('user_id');
        $categoryData['updated_by'] = session('user_id'); 
        Client_contact_category::create($categoryData); 
    }
    return;
}

function create_contact_default_tags($clientID,$defaultClient){
    $ContactTag = Client_contact_tag::where('client_id',$defaultClient)->pluck('name')->toArray();
    foreach ($ContactTag as $key => $Tag) {
        $tagData['client_id'] = $clientID;
        $tagData['name'] = $Tag;
        $tagData['created_by'] = session('user_id');
        $tagData['updated_by'] = session('user_id'); 
        Client_contact_tag::create($tagData); 
    }
    return;
}

function create_default_companies($clientID,$workspaceID,$defaultClient,$defaultClientWorkspace){
    create_comapny_default_category($clientID,$defaultClient);
    create_comapny_default_tags($clientID,$defaultClient);
    create_contact_default_category($clientID,$defaultClient);
    create_contact_default_tags($clientID,$defaultClient);
    $Companies = Client_company::with(['primaryContact','Category'])->where('client_id',$defaultClient)->get()->toArray();
    foreach ($Companies as $key => $value) {
        $ClientContactID = 0;
        if($value['primary_contact']){
            $contactData['first_name'] = $value['primary_contact']['first_name'];
            $contactData['last_name'] = $value['primary_contact']['last_name'];
            $contactData['email'] = $value['primary_contact']['email'];
            $contactData['phone'] = $value['primary_contact']['phone'];
            $contactData['primary_contact'] = $value['primary_contact']['primary_contact'];
            $contactData['notes'] = $value['primary_contact']['notes'];
            if($value['primary_contact']['contact_category']){
                $Cont_category = DB::table('client_contact_categories')->where('id', $value['primary_contact']['contact_category'])->first();
                if($Cont_category){
                    $temp_cate = DB::table('client_contact_categories')->where('client_id',$clientID)->where('name',$Cont_category->name)->first();
                    if($temp_cate){
                        $contactData['contact_category'] = $temp_cate->id;
                    }
                }
            }
            if($value['primary_contact']['contact_tags']){
                $cmpArray = json_decode($value['primary_contact']['contact_tags']);
                $tags = DB::table('client_contact_tags')->whereIn('id', $cmpArray)->pluck('name')->toArray();
                if(count($tags) > 0){
                    $temp_tag = DB::table('client_contact_tags')->where('client_id',$clientID)->whereIn('name',$tags)->pluck('id');
                    if($temp_tag){
                        $contactData['contact_tags'] = json_encode($temp_tag);
                    }
                }
            }
            $contactData['created_by'] = session('user_id');
            $contactData['updated_by'] = session('user_id');
            $contactData['client_id'] = $clientID;
            $ClientContact = Client_contact::create($contactData);
            $ClientContactID =$ClientContact->id; 
        }

        $companyData['company_name'] = $value['company_name'];
        $companyData['website'] = $value['website'];
        $companyData['ABN'] = $value['ABN'];
        $companyData['ACN'] = $value['ACN'];
        $companyData['billing_address'] = $value['billing_address'];
        $companyData['delivery_address'] = $value['delivery_address'];
        $companyData['notes'] = $value['notes'];
        $companyData['contact_id'] = ($value['contact_id'] && $ClientContactID > 0)? $ClientContactID : null;
        if($value['category']){
            $Compnay_category = DB::table('client_company_categories')->where('client_id',$clientID)->where('name', $value['category']['name'])->first();
            if($Compnay_category){
                $companyData['company_category'] =  $Compnay_category->id;
            }
        }
        if($value['company_tags']){
            $cmpArray = json_decode($value['company_tags']);
            $cont_tags = DB::table('client_company_tags')->whereIn('id', $cmpArray)->pluck('name')->toArray();
            if(count($cont_tags) > 0){
                $temp_cont_tag = DB::table('client_company_tags')->where('client_id',$clientID)->whereIn('name',$cont_tags)->pluck('id');
                if($temp_cont_tag){
                    $companyData['company_tags'] = json_encode($temp_cont_tag);
                }
            }
        }

        $companyData['created_by'] = session('user_id');
        $companyData['updated_by'] = session('user_id');
        $companyData['client_id'] = $clientID;
        $ClientCompany= Client_company::create($companyData);
        if (isset($value['primary_contact']['company']) && $value['primary_contact']['company'] && $ClientContactID > 0) {
            Client_contact::where('id', $ClientContactID)->update(['company' => $ClientCompany->id]);
        }
    }

    $Contacts =Client_contact::where('client_id',$defaultClient)->get()->toArray();
    foreach ($Contacts as $key => $value) {
        if( Client_contact::where('client_id',$clientID)->where('email', $value['email'])->exists() == false){
            $new_contactData['first_name'] = $value['first_name'];
            $new_contactData['last_name'] = $value['last_name'];
            $new_contactData['email'] = $value['email'];
            $new_contactData['phone'] = $value['phone'];
            $new_contactData['primary_contact'] = $value['primary_contact'];
            $new_contactData['notes'] = $value['notes'];
            if($value['contact_category']){
                $Cont_category = DB::table('client_contact_categories')->where('id', $value['contact_category'])->first();
                if($Cont_category){
                    $temp_cate = DB::table('client_contact_categories')->where('client_id',$clientID)->where('name',$Cont_category->name)->first();
                    if($temp_cate){
                        $new_contactData['contact_category'] = $temp_cate->id;
                    }
                }
            }

            if($value['contact_tags']){
                $cmpArray = json_decode($value['contact_tags']);
                $tags = DB::table('client_contact_tags')->whereIn('id', $cmpArray)->pluck('name')->toArray();
                if(count($tags) > 0){
                    $temp_tag = DB::table('client_contact_tags')->where('client_id',$clientID)->whereIn('name',$tags)->pluck('id');
                    if($temp_tag){
                        $new_contactData['contact_tags'] = json_encode($temp_tag);
                    }
                }
            }
            $new_contactData['created_by'] = session('user_id');
            $new_contactData['updated_by'] = session('user_id');
            $new_contactData['client_id'] = $clientID;
            $ClientContact = Client_contact::create($new_contactData);
                            
        }
    }
    return;
}

function get_xeroconnection_count($clientID){
    return XeroConnection::where('client_id', $clientID)->count();
}

  
function updateNutritional_value(array $productIDs){
    $uniqueIDs = array_unique($productIDs);

    foreach ($uniqueIDs as $value) {
        $product = Product::findOrFail($value);
        $ingredients = ProdIngredient::where('product_id', $value)->get();

        $NutritionResponse = ProductCalculationController::calculate_nutrition_information_components($product, $ingredients);
        $total = $NutritionResponse['totals'];

        $serv_size_g = $product->serv_size_g ?? 0;

        // Helper closure for safe formatting
        $format = function ($value, $decimals = 1) {
            return isset($value) ? (float) number_format($value, $decimals, '.', '') : null;
        };

        // Build update array
        $updateData = [
            'energy_kJ_per_100g'       => $format($total['energy_kj'], 0),
            'protein_g_per_100g'      => $format($total['protein_g'], 1),
            'fat_total_g_per_100g'    => $format($total['fat_total_g'], 1),
            'fat_saturated_g_per_100g'=> $format($total['fat_saturated_g'], 1),
            'carbohydrate_g_per_100g' => $format($total['carbohydrate_g'], 1),
            'sugar_g_per_100g'        => $format($total['sugars_g'], 1),
            'sodium_mg_per_100g'      => $format($total['sodium_mg'], 0),
            'batch_after_waste_g'    => $format($total['net_quantity'], 0),
        ];

        // Per-serve calculations (scaled by serving size)
        $updateData += [
            'energy_kJ_per_serve'       => $format($total['energy_kj'] * $serv_size_g / 100, 0),
            'protein_g_per_serve'      => $format($total['protein_g'] * $serv_size_g / 100, 1),
            'fat_total_g_per_serve'    => $format($total['fat_total_g'] * $serv_size_g / 100, 1),
            'fat_saturated_g_per_serve'=> $format($total['fat_saturated_g'] * $serv_size_g / 100, 1),
            'carbohydrate_g_per_serve' => $format($total['carbohydrate_g'] * $serv_size_g / 100, 1),
            'sugar_g_per_serve'        => $format($total['sugars_g'] * $serv_size_g / 100, 1),
            'sodium_mg_per_serve'      => $format($total['sodium_mg'] * $serv_size_g / 100, 0),
        ];

        $product->update($updateData);
    }
}
  