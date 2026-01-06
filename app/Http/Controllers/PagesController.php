<?php

namespace App\Http\Controllers;

use App\Models\{Page,Products_permission_group};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PagesController extends Controller
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
     * Display a listing of the page resource.
     */
    public function index()
    {
        // $pages = Page::all();
        $pages = Page::where('id', '>', 3)->get();
        return view('backend.pages.pages', compact('pages'));
    }

    /**
     * Store a newly created page resource in database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), Page::validationRules());

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['created_by'] = $this->user_id;
            $data['updated_by'] = $this->user_id;

            $page = Page::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Page created successfully',
                'page' => $page
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified page resource in database.
     */
    public function update(Request $request, Page $page)
    {
        try {
            $validator = Validator::make($request->all(), Page::validationRules($page->id));

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['updated_by'] = $this->user_id;

            $page->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Page updated successfully',
                'page' => $page
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the specified page resource from database.
     */
    public function destroy(Page $page)
    {
        try {

            $productPermission = Products_permission_group::where('product_id',$page->id)->whereNotnull('product_permission_group')->count();
            if($productPermission == 0){
                $page->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Page deleted successfully'
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to delete this page. Its already assigned'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->first();

        if (!$page) {
            return abort(404, 'Page not found');
        }

        return view('backend.pages.show', compact('page'));
    }

    public function edit($slug)
    {
        $page = Page::where('slug', $slug)->first();

        if (!$page) {
            return abort(404, 'Page not found');
        }

        return view('backend.pages.edit', compact('page'));
    }

    public function update_page(Request $request, $slug)
    {
        try {
            $page = Page::where('slug', $slug)->firstOrFail();

            $validator = Validator::make($request->all(), Page::validationRules($page->id));

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $data = $validator->validated();
            $data['updated_by'] = $this->user_id;

            $page->update($data);

            return redirect()->route('page.show', ['slug' => $page->slug])
                ->with('success', 'Page updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
