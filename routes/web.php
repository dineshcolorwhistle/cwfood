<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WIPController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FsanzController;
use App\Http\Controllers\FsanzFoodController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\LabourController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MachineryController;
use App\Http\Controllers\PackagingController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ImageOutPutController;
use App\Http\Controllers\ProductImportController;
use App\Http\Middleware\RoleBasedAccessMiddleware;
use App\Http\Controllers\IngredientImportController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\ClientSubscriptionController;
use App\Http\Controllers\ClientBillingController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\ImagemetaController;
use App\Http\Controllers\ClientRoleController;
use App\Http\Controllers\PreferencesController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\ClientPermissionController;
use App\Http\Controllers\FreightController;
use App\Http\Controllers\ProductIngredientImportController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ViewsController;
use App\Http\Controllers\Client_companyController;
use App\Http\Controllers\Client_contactController;
use App\Http\Middleware\CheckRoleAccess;
use App\Http\Controllers\CompanyImportController;
use App\Http\Controllers\ContactImportController;
use App\Http\Controllers\AdminSupportController;
use App\Http\Controllers\AdminAIPromptController;
use App\Http\Controllers\AdminTableschemaController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\XeroController;
use App\Http\Controllers\ClientIntegrationController;
use App\Http\Controllers\XeroSalesPerformanceController;
use App\Http\Controllers\Auth\CognitoAuthController;
use App\Http\Controllers\BatchabaseAgentController;
use App\Http\Controllers\ProductV2Controller;
use App\Http\Controllers\RawMaterialV2Controller;


// Redirect root to login for guests
// Route::get('/', function () {
//     return redirect('login');
// })->middleware('guest');

// // Authentication Routes
// Route::middleware('guest')->group(function () {
//     // Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
//     Route::post('authenticate', [AuthController::class, 'authenticate'])->name('authenticate');
//     Route::get('signup', [AuthController::class, 'showSignupForm'])->name('signup');
//     Route::post('authenticate/company', [AuthController::class, 'company_authenticate'])->name('company.authenticate');

//     Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
//     Route::post('/reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');
//     Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('password.request');
//     Route::post('/forgot-password/update', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('password.token.generate');
// });
// Route::middleware(['web'])->group(function () {
//     Route::get('/csrf-refresh', function () {
//         return response()->json(['token' => csrf_token()]);
//     })->name('csrf.refresh');
//     Route::get('/xero/callback', [XeroController::class, 'callback'])->name('xero.callback');
// });

// Authentication Routes (Cognito Hosted UI)
Route::middleware('web')
    ->withoutMiddleware([
        \App\Http\Middleware\CheckSubscriptionValidity::class,
        \App\Http\Middleware\SetSideMenuContext::class,
        // add any other custom "web" middlewares you’ve placed in the web group
    ])
    ->group(function () {
        
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
      	Route::post('authenticate', [AuthController::class, 'loginByEmail'])->name('authenticate');
      	Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('password.request');
      	Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/csrf-refresh', function () {
            return response()->json(['token' => csrf_token()]);
        })->name('csrf.refresh');

        Route::get('signup', [AuthController::class, 'showSignupForm'])->name('signup');
        Route::post('authenticate/company', [AuthController::class, 'company_authenticate'])->name('company.authenticate');

        // web.php
        Route::post('/session/ping', function () {
            session(['lastActivityTime' => now()]);
            return response()->json(['status' => 'ok']);
        })->name('session.ping')->middleware('auth');

        // Keep external OAuth callbacks public and clean too
        Route::get('/xero/callback', [XeroController::class, 'callback'])->name('xero.callback');
});
   
Route::middleware(['web', 'auth','cognito.id'])->group(function () {

    Route::get('/member/subscription/expire', function () {
        return view('backend.subscription-expired'); // resources/views/welcome.blade.php
    })->name('member.subscription.show');

    Route::get('/member/access', function () {
        return view('backend.no-access'); // resources/views/welcome.blade.php
    })->name('member.no-aceess');


    // User Profile
    Route::prefix('user-profile')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('user-profile');
        Route::post('/update', [UserController::class, 'profileUpdate'])->name('profile.update');
        Route::post('/password', [UserController::class, 'passwordUpdate'])->name('password.change');
        Route::post('/remove-picture', [UserController::class, 'removePicture'])->name('profile.remove-picture');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::post('/session/update', 'session_update')->name('session.update');  
        Route::get('/ana/dashboard', 'dashboard')->name('ana.dashboard');
        Route::get('/ana/unit-analytics', 'calculateProductMargins')->name('ana.unit_analysis');
    });


    Route::controller(ViewsController::class)->group(function () {
        Route::get('/product-views', 'product_views')->name('views.products');  
        Route::get('/raw-material-views', 'rawmaterial_views')->name('views.raw-materials');
        Route::get('/product-views/search', 'search')->name('products-view.search');
        Route::get('/rawmaterial-views/search', 'rawmaterial_search')->name('rawmaterials-view.search');
        Route::get('/search/products', 'product_search')->name('products-custom.search');
        Route::get('/search/rawmaterial', 'ingredient_search')->name('ingredient-custom.search');
    });

    Route::controller(BatchabaseAgentController::class)->group(function () {
        Route::get('/batchbase_agent/specifications', 'specifications')->name('batchbase_agent.specifications');  
        Route::get('/batchbase_agent/specifications/add', 'add_specifications')->name('specifications.add');
        Route::get('/batchbase_agent/specifications/manual', 'add_manual')->name('specifications.manual');
        Route::post('/specifications/store', 'store')->name('specifications.store');
        Route::post('/specifications/preview', 'preview')->name('specifications.preview');
        Route::get('/specifications/edit/{specification}', 'edit')->name('specifications.edit');
        Route::post('/specifications/update/{specification}', 'update')->name('specifications.update');
        Route::delete('/specifications/delete/{specification}', 'delete')->name('specifications.delete');
        Route::post('/specifications/bulk/delete', 'bulk_delete')->name('specifications.bulk_delete');
        Route::post('/specifications/unarchive/{specification}', 'unarchive')->name('specifications.unarchive');
        Route::get('/specifications/import_data', 'show_import')->name('specifications.import');
        Route::get('/specifications/template/download', 'download_template')->name('specifications.template.download');
        Route::post('/specifications/details/preview', 'upload_preview')->name('specifications.details.preview');
        Route::post('/specifications/store/details', 'store_upload')->name('specifications.details.store');
        Route::post('/specifications/audit/{specification}', 'audit')->name('specifications.audit');
        Route::post('/specification/update-filename', 'updateFileName')->name('specification.updateFileName');  
        Route::post('/specifications/delete-file', 'deleteFile')->name('specification.deleteFile');
        Route::post('/specifications/file-upload/{specification}', 'fileUpload')->name('specification.fileUpload');  
        Route::post('/specifications/re-run/{specification}', 'reRun')->name('specification.rerun');
        Route::post('/specifications/re-run-update/{specification}', 'reRun_update')->name('specification.rerun_update');  
        Route::post('/specifications/compare', 'compare')->name('specification.compare');
        Route::post('/specifications/restore', 'restore')->name('specification.restore');
        Route::post('/specifications/create-from-fsanz', 'createFromFSANZ')->name('specification.create.fsanz');
        Route::get('/specifications/search', 'search');
        Route::get('/specifications/fsanz/{id}', 'show');  
    });

    Route::prefix('ana')->group(function () {
        // Sales Performance page
        Route::get('/analytics/xero/sales-performance', [XeroSalesPerformanceController::class, 'index'])
            ->name('ana.xero.sales_performance');

        // Data feed for the page (tenanted & date-bounded)
        Route::get('/analytics/xero/sales-performance/data', [XeroSalesPerformanceController::class, 'data'])
            ->name('ana.xero.sales_performance.data');
        
            Route::get('/analytics/xero/sales-performance/customer', [XeroSalesPerformanceController::class, 'customer'])
            ->name('ana.xero.sales_performance.customer');


        // Options (tenants) – moved here from XeroDashboardController
        Route::get('/xero/options', [XeroSalesPerformanceController::class, 'options'])
            ->name('ana.xero.options');
    });

    Route::middleware([RoleBasedAccessMiddleware::class . ':platform'])->group(function () {
        Route::resource('subscription-plans', SubscriptionPlanController::class);
        Route::resource('roles', RoleController::class)
            ->except(['show'])
            ->names([
                'index' => 'roles.index',
                'create' => 'roles.create',
                'store' => 'roles.store',
                'edit' => 'roles.edit',
                'update' => 'roles.update',
                'destroy' => 'roles.destroy'
            ]);

        Route::resource('team-member-roles', RoleController::class)
            ->except(['show'])
            ->names([
                'index' => 'team-member-roles.index',
                'create' => 'team-member-roles.create',
                'store' => 'team-member-roles.store',
                'edit' => 'team-member-roles.edit',
                'update' => 'team-member-roles.update',
                'destroy' => 'team-member-roles.destroy'
            ]);

        Route::prefix('users')->group(function () {
            // Team Members Routes (role_id = 1)
            Route::get('/team', [UserController::class, 'index'])
                ->name('team-members.index');
            Route::post('/team', [UserController::class, 'store'])
                ->name('team-members.store');
            Route::put('/team/{user}', [UserController::class, 'update'])
                ->name('team-members.update');
            Route::delete('/team/{user}', [UserController::class, 'destroy'])
                ->name('team-members.destroy');
        });

        Route::resource('clients', ClientController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names([
                'index' => 'clients.index',
                'store' => 'clients.store',
                'update' => 'clients.update',
                'destroy' => 'clients.destroy'
            ]);

        Route::get('/page/{slug}', [PagesController::class, 'show'])->name('page.show');
        Route::get('/page/{slug}/edit', [PagesController::class, 'edit'])->name('page.edit');
        Route::put('/page/{slug}', [PagesController::class, 'update_page'])->name('page.update');
        Route::group(['prefix' => 'pages'], function () {
            Route::get('/', [PagesController::class, 'index'])->name('pages.index');
            Route::post('/', [PagesController::class, 'store'])->name('pages.store');
            Route::put('/{page}', [PagesController::class, 'update'])->name('pages.update');
            Route::delete('/{page}', [PagesController::class, 'destroy'])->name('pages.destroy');
        });

        Route::controller(AdminSupportController::class)->group(function () {
            Route::get('/admin/manage/support', 'index')->name('admin.support.manage');
            Route::get('/admin/manage/ticket/{ticket}', 'view')->name('admin.view.ticket');
            Route::post('/admin/update/ticket/{ticket}', 'update')->name('admin.update.ticket');
            Route::delete('/admin/comment/{comment}', 'comment_destroy')->name('admin.comment.destroy');
            Route::post('/admin/update/comment/{comment}', 'update_comment')->name('admin.update.comment'); 
            Route::post('/admin/save/comment/{ticket}', 'save_comment')->name('admin.save.comment');
            Route::delete('/admin/ticket/{ticket}', 'destroy')->name('admin.support.destroy');
            Route::post('/admin/update/ticket-status/{ticket}', 'update_status')->name('admin.update.ticket.status'); 
            Route::get('/admin/client/details', 'client_details')->name('admin.get.client-details');
            Route::post('/admin/save/ticket', 'save')->name('admin.save.ticket');
            Route::post('/admin/update/ticket-priority/{ticket}', 'update_priority')->name('admin.update.ticket.priority'); 
            Route::post('/admin/ticket/reorder', 'reorder')->name('admin.tickets.reorder');
            Route::post('/admin/ticket/edit/{ticket}', 'edit')->name('admin.support.edit');
            Route::get('/admin/export/tickets-excel', 'exportExcelTicketsWithComments')->name('admin.ticket-excel-download');
            Route::get('/admin/export/tickets-csv', 'exportCSVTicketsWithComments')->name('admin.ticket-csv-download');

        });

        Route::controller(AdminAIPromptController::class)->group(function () {
            Route::get('/admin/manage/ai_prompt', 'index')->name('admin.ai_prompt.manage');
            Route::post('/admin/ai_prompt/update/{prompt}', 'update')->name('admin.ai_prompt.update');
        });  
        

        Route::controller(AdminTableschemaController::class)->group(function () {
            Route::get('/admin/manage/table_schema', 'index')->name('admin.table_schema.manage');
            Route::post('/admin/table_schema/update/{prompt}', 'update')->name('admin.table_schema.update');
            Route::get('/admin/table_schema/csv/{slug}', 'download_csv');
            Route::get('/admin/table_schema/excel/{slug}', 'download_excel');
        });

        
    });

    Route::middleware([RoleBasedAccessMiddleware::class . ':client'])->prefix('client/{client_id}')->name('client.')->group(function () {
        Route::prefix('workspaces')->name('workspaces.')->group(function () {
            Route::get('/', [WorkspaceController::class, 'index'])->name('index');
            Route::post('/', [WorkspaceController::class, 'store'])->name('store');
            Route::put('/{workspace}', [WorkspaceController::class, 'update'])->name('update');
            Route::delete('/{workspace}', [WorkspaceController::class, 'destroy'])->name('destroy');
        });
    });


    Route::middleware([CheckRoleAccess::class . ':manage'])->group(function () {
        Route::prefix('data/labours')->name('labours.')->group(function () {
            Route::get('/', [LabourController::class, 'index'])->name('index');
            Route::post('/', [LabourController::class, 'store'])->name('store');
            Route::put('/{labour}', [LabourController::class, 'update'])->name('update');
            Route::delete('/{labour}', [LabourController::class, 'destroy'])->name('destroy');
            Route::get('/import_data', [LabourController::class, 'show_import'])->name('import');
            Route::get('/template/download', [LabourController::class, 'download_template'])->name('template.download');
            Route::post('/details/preview', [LabourController::class, 'preview'])->name('details.preview');
            Route::post('/store/details', [LabourController::class, 'store_upload'])->name('details.store');
            Route::post('/favorite/{id}', [LabourController::class, 'make_favorite'])->name('favorite');
            Route::post('/delete/labours', [LabourController::class, 'labour_delete'])->name('delete');
            Route::post('/unarchive/{labour}', [LabourController::class, 'unarchive'])->name('unarchive');
        });

        Route::prefix('data/machinery')->name('machinery.')->group(function () {
            Route::get('/', [MachineryController::class, 'index'])->name('index');
            Route::post('/', [MachineryController::class, 'store'])->name('store');
            Route::put('/{machinery}', [MachineryController::class, 'update'])->name('update');
            Route::delete('/{machinery}', [MachineryController::class, 'destroy'])->name('destroy');
            Route::get('/import-form', [MachineryController::class, 'import_form'])->name('import');
            Route::get('/template/download', [MachineryController::class, 'download_template'])->name('template.download');
            Route::post('/details/preview', [MachineryController::class, 'preview'])->name('details.preview');
            Route::post('/store/details', [MachineryController::class, 'store_upload'])->name('details.store');
            Route::post('/favorite/{id}', [MachineryController::class, 'make_favorite'])->name('favorite');
            Route::post('/delete/machines', [MachineryController::class, 'machine_delete'])->name('delete');
            Route::post('/unarchive/{machinery}', [MachineryController::class, 'unarchive'])->name('unarchive');
        });

        Route::prefix('data/packaging')->name('packaging.')->group(function () {
            Route::get('/', [PackagingController::class, 'index'])->name('index');
            Route::post('/', [PackagingController::class, 'store'])->name('store');
            Route::put('/{packaging}', [PackagingController::class, 'update'])->name('update');
            Route::delete('/{packaging}', [PackagingController::class, 'destroy'])->name('destroy');
            Route::post('/generate-sku', [PackagingController::class, 'generateSKU'])->name('generate_sku');
            Route::get('/import-form', [PackagingController::class, 'import_form'])->name('import');
            Route::get('/template/download', [PackagingController::class, 'download_template'])->name('template.download');
            Route::post('/details/preview', [PackagingController::class, 'preview'])->name('details.preview');
            Route::post('/store/details', [PackagingController::class, 'store_upload'])->name('details.store');
            Route::post('/favorite/{id}', [PackagingController::class, 'make_favorite'])->name('favorite');
            Route::post('/delete/packaging', [PackagingController::class, 'packaging_delete'])->name('delete');
            Route::post('/unarchive/{packaging}', [PackagingController::class, 'unarchive'])->name('unarchive');
        });
        Route::prefix('data/freight')->name('freight.')->group(function () {
            Route::controller(FreightController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{freight}', 'update')->name('update');
                Route::delete('/{freight}', 'destroy')->name('destroy');
                Route::get('/import_data', 'show_import')->name('import');
                Route::get('/template/download', 'download_template')->name('template.download');
                Route::post('/details/preview', 'preview')->name('details.preview');
                Route::post('/store/details', 'store_upload')->name('details.store');
                Route::post('/favorite/{id}', 'make_favorite')->name('favorite');
                Route::post('/delete/freight', 'freight_delete')->name('delete');
                Route::post('/unarchive/{freight}', 'unarchive')->name('unarchive');
            });
        });

        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('products.index');
            Route::get('/grid', [ProductController::class, 'grid_view'])->name('products.grid');
            Route::get('/search', [ProductController::class, 'search'])->name('products.search');
            Route::get('/custom-search', [ProductController::class, 'custom_search'])->name('products.custom_search');
            Route::get('/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/', [ProductController::class, 'store'])->name('products.store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::post('/duplicate/{id}', [ProductController::class, 'duplicate'])->name('products.duplicate');
            Route::post('/favorite/{id}', [ProductController::class, 'make_favorite'])->name('products.favorite'); 
            Route::post('/delete', [ProductController::class, 'product_delete'])->name('products.delete'); 
            Route::put('/{product}/step2', [ProductController::class, 'updateStep2'])->name('products.updateStep2');
            Route::put('/{product}/step3', [ProductController::class, 'updateStep3'])->name('products.updateStep3');
            Route::put('/{product}/step4', [ProductController::class, 'updateStep4'])->name('products.updateStep4');
            Route::put('/{product}/step5', [ProductController::class, 'updateStep5'])->name('products.updateStep5');
            Route::put('/{product}/step6', [ProductController::class, 'updateStep6'])->name('products.updateStep6');
            Route::post('/calculate-nutrition', [ProductController::class, 'calculateNutritionTable'])->name('products.calculateNutrition');
            Route::post('/display-nutrition', [ProductController::class, 'displayNutritionTable'])->name('products.displayNutrition');
            Route::post('/remove-ingredient', [ProductController::class, 'removeIngredient'])->name('products.remove-ingredient');
            Route::post('/getIngredientUnits', [ProductController::class, 'getIngredientUnits'])->name('products.getIngredientUnits');
            Route::post('/generate-sku', [ProductController::class, 'generateSKU'])->name('generate.sku');
            Route::get('/list', [ProductController::class, 'list'])->name('products.list');
            Route::get('/{product}/recipe', [ProductController::class, 'recipe'])->name('products.recipe');
            Route::get('/{product}/spec', [ProductController::class, 'spec'])->name('products.spec');
            Route::get('/{product}/labelling', [ProductController::class, 'labelling'])->name('products.labelling');
            Route::get('/{product}/costing', [ProductController::class, 'costing'])->name('products.costing');
            Route::post('/create-tag', [ProductController::class, 'createTag'])->name('products.createTag');
            Route::get('/get-tags', [ProductController::class, 'getTags'])->name('products.getTags');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
            Route::post('/get-labour-details', [ProductController::class, 'getLabourDetails'])->name('products.get-labour-details');
            Route::post('/get-product-weight', [ProductController::class, 'getProductWeight'])->name('products.get-product-weight');
            Route::post('/remove-labour', [ProductController::class, 'removeLabour'])->name('products.remove-labour');
            Route::post('/get-machinery-details', [ProductController::class, 'getMachineryDetails'])->name('products.get-machinery-details');
            Route::post('/get-machinery-weight', [ProductController::class, 'getMachineryWeight'])->name('products.get-machinery-weight');
            Route::post('/remove-machinery', [ProductController::class, 'removeMachinery'])->name('products.remove-machinery');
            Route::post('/get-packaging-details', [ProductController::class, 'getPackagingDetails'])->name('products.get-packaging-details');
            Route::post('/get-packaging-weight', [ProductController::class, 'getPackagingWeight'])->name('products.get-packaging-weight');
            Route::post('/remove-packaging', [ProductController::class, 'removePackaging'])->name('products.remove-packaging');
            Route::post('/get-freight-details', [ProductController::class, 'getFreightDetails'])->name('products.get-freight-details');
            Route::post('/remove-freights', [ProductController::class, 'removeFreights'])->name('products.remove-freights');
            Route::post('/unarchive/{product}', [ProductController::class, 'unarchive'])->name('products.unarchive');
            Route::get('/get/allergen_summary/{product}', [ProductController::class, 'get_allergen_summary'])->name('products.get-allergen-summary');
            Route::get('/price_analysis/component/{product}', [ProductController::class, 'price_analysis_componenet'])->name('products.component.price-analysis');
            Route::get('/direct_cost/component/{product}', [ProductController::class, 'direct_cost_componenet'])->name('products.component.direct-cost');
            Route::get('/inactivity/{product}', [ProductController::class, 'inactivity_update'])->name('products.inactivity');
            Route::get('/edit_lock_update/{product}', [ProductController::class, 'edit_lock_update'])->name('products.edit_lock_update');
        });
   
        Route::prefix('product_v2')->name('product_v2.')->group(function () {     
            Route::controller(ProductV2Controller::class)->group(function () {
                Route::get('/manage', 'index')->name('manage');
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{product}/edit', 'edit')->name('edit');
                Route::get('/grid', 'grid_view')->name('grid');
            });            
        });

        Route::prefix('rawmaterial_v2')->name('rawmaterial_v2.')->group(function () {
            Route::controller(RawMaterialV2Controller::class)->group(function () {
                Route::get('/manage', 'manage')->name('manage');
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::get('/grid', 'grid_view')->name('grid');   
            });
        });

        Route::controller(ExportController::class)->group(function () {
            Route::get('/{product}/{slug}/download-specs', 'download_specs')->name('products.download_specs');
            Route::get('/{product}/{slug}/download-recipe', 'download_recipe')->name('products.download_recipe');
            Route::get('/{product}/{slug}/download-labelling', 'download_labelling')->name('products.download_labelling');
            Route::get('/{product}/{slug}/download-costing', 'download_costing')->name('products.download_costing');
        });


        Route::controller(CommonController::class)->group(function () {
            Route::get('/download/csv/{slug}', 'download_csv');
            Route::get('/download/excel/{slug}', 'download_excel');
            Route::post('/save/download/attributes', 'save_download_attribute')->name('save.download.attr');
        });

        Route::prefix('data')->group(function () {
            Route::controller(IngredientController::class)->group(function () {
                Route::get('/raw-materials', 'index')->name('manage.raw-materials');
                Route::get('/raw-materials/grid', 'grid_view')->name('manage.grid.raw-materials');
                Route::get('/raw-materials/create', 'add')->name('add.raw-materials');
                Route::post('/save/source', 'save_source')->name('save.source');
                Route::post('/save/ingredient', 'save')->name('save.ingredient');
                Route::post('/validate/ing_sku', 'validate_sku');
                Route::get('/raw-materials/edit/{id}', 'edit')->name('edit.raw-materials');
                Route::post('/raw-materials/update/{id}', 'update')->name('update.raw-materials');
                Route::post('/raw-materials/destroy/{id}', 'destroy')->name('destroy.raw-materials');
                Route::get('/ingredient/search', 'search')->name('ingredient.search');
                Route::get('/ingredient/custom-search', 'custom_search')->name('ingredient.custom_search');
                Route::post('/raw-materials/duplicate/{id}', 'duplicate')->name('duplicate.raw-materials');
                Route::post('/raw-materials/favorite/{id}', 'make_favorite')->name('favorite.raw-materials');
                Route::post('/export/columns', 'export_columns')->name('export.raw-materials');
                Route::get('/ingredient/filter', 'filter')->name('ingredient.filter');
                Route::post('/raw-materials/delete', 'rawmaterial_delete')->name('delete.raw-materials');
                Route::post('/unarchive/{ingredient}', 'unarchive')->name('unarchive.raw-materials');
                Route::get('/inactivity/{ingredient}', 'inactivity_update')->name('raw-materials.inactivity');
                Route::post('/store/spec', 'storeSpec');  
            });
        });
    });

    Route::middleware([CheckRoleAccess::class . ':admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::controller(ImageOutPutController::class)->group(function () {
                Route::get('/image_library', 'index')->name('manage.image_library');
                Route::get('/image_library/{id}/show', 'show_images')->name('show.images');
                Route::post('/download/images', 'download_images');
            });
        });

        Route::controller(PreferencesController::class)->group(function () {
            Route::get('/admin/preferences', 'index')->name('preference.manage');
            Route::post('/admin/preferences/store', 'store')->name('preference.store');
            Route::post('/admin/preferences/update', 'update')->name('preference.update');
            Route::delete('/admin/preferences/delete', 'delete')->name('preference.delete');
            Route::post('/admin/preferences/default/{preference}', 'make_default')->name('preference.default');
        });
    });

    Route::middleware([CheckRoleAccess::class . ':billing'])->group(function () {
        Route::controller(ClientSubscriptionController::class)->group(function () {
            Route::get('/admin/subscription', 'show')->name('client.subscription.show');
            Route::post('/admin/subscription/update', 'update')->name('client.subscription.update');
        });

        // Client Billing
        Route::controller(ClientBillingController::class)->group(function () {
            Route::get('/admin/billing', 'show')->name('client.billing.show');
            Route::post('/admin/billing/update', 'update')->name('client.billing.update');
            Route::post('/save-card', 'saveCard')->name('billing.saveCard');  
            Route::post('/update-paymentmethod', 'updatePaymentMethod')->name('billing.updatePaymentmethod');
            Route::get('/admin/billing/add-card', 'GetCard')->name('billing.getCard');
            Route::post('/update-card', 'UpdateCard')->name('billing.updateCard');
            Route::post('/stripe/webhook', 'handleWebhook');
            Route::post('/subscription-cancel', 'subscription_cancel')->name('subscription.cancel');
            Route::post('/subscription-resume', 'subscription_resume')->name('subscription.resume'); 
        });
    });

    Route::middleware([CheckRoleAccess::class . ':upload'])->group(function () {

        Route::controller(ProductImportController::class)->group(function () {
            Route::post('/export/product/columns', 'export_columns')->name('export.product_column');
            Route::get('product/import', 'showImportForm')->name('product.import.form');
            Route::get('/product/template/download', 'downloadTemplate')->name('products.template.download');
            Route::post('/product/preview', 'preview')->name('products.preview');
            Route::post('/product/upload', 'upload')->name('products.upload');
        });
        
        Route::controller(ProductIngredientImportController::class)->group(function () {
            Route::get('product/ingredient/import', 'showImportForm')->name('product.ingredinet.import.form');
            Route::get('/product/ingredient/template/download', 'downloadTemplate')->name('product.ingredinet.template.download');
            Route::post('/product/ingredient/upload', 'upload')->name('products.ingredient.upload');
            Route::post('/product/ingredient/preview', 'preview')->name('products.ingredient.preview');
        });

        Route::prefix('ingredients')->group(function () {
            Route::get('/import', [IngredientImportController::class, 'index'])->name('ingredients.import');
            Route::get('/template/download', [IngredientImportController::class, 'downloadTemplate'])->name('ingredients.template.download');
            Route::post('/preview', [IngredientImportController::class, 'preview'])->name('ingredients.preview');
            Route::post('/store', [IngredientImportController::class, 'store'])->name('ingredients.store');
        });
    });

    Route::middleware([RoleBasedAccessMiddleware::class . ':client'])->group(function () {
        Route::controller(MembersController::class)->group(function () {
            Route::get('/manage/members', 'index')->name('members.manage');
            Route::post('/manage/members', 'store')->name('members.store');
            Route::post('/members/{member}', 'update')->name('members.update');
            Route::delete('/members/{member}', 'destroy')->name('members.destroy');
        });

        Route::prefix('admin')->group(function () {
            Route::controller(ClientPermissionController::class)->group(function () {
                Route::get('/permissions', 'index')->name('permission.manage');            
                Route::post('/user/permission', 'user_permission')->name('update.user_permission');
                Route::post('/product/permission', 'product_permission')->name('update.product_permission');
            });
        });

        Route::controller(ClientRoleController::class)->group(function () {
            Route::get('/client/role', 'index')->name('client.role.index');
            Route::post('/client/role', 'store')->name('client.role.store');
            Route::put('/client/role/{role}', 'update')->name('client.role.update');
            Route::delete('/client/role/{role}', 'destroy')->name('client.role.destroy'); 
        });

        Route::controller(WorkspaceController::class)->group(function () {
            Route::get('/client/{client_id}/{ws_id}/workspaces', 'show_with_ws')->name('client.workspaces-w-ws');
        });

        Route::prefix('client')->group(function () {
            // Route to view the client's company profile
            Route::get('{client_id}/company-profile', [ClientProfileController::class, 'show'])
                ->name('client.company-profile');
            
            Route::get('{client_id}/{ws_id}/company-profile', [ClientProfileController::class, 'show_with_ws'])
            ->name('client.company-w-ws');
    
            // Route to update the client's company profile
            Route::post('{client_id}/company-profile/update', [ClientProfileController::class, 'update'])
                ->name('client.company-profile.update');
        });

        Route::controller(Client_companyController::class)->group(function () {
            Route::get('/admin/companies', 'index')->name('manage.client_company');
            Route::post('/save-comanytag', 'save_tag')->name('save.company-tag');
            Route::put('/update-comanytag/{client_company_tag}', 'update_tag')->name('update.company-tag');
            Route::delete('/delet-comanytag/{client_company_tag}', 'delete_tag')->name('delete.company-tag');
            Route::post('/save-comany', 'save_company')->name('save.company');
            Route::put('/update-comany/{client_company}', 'update_company')->name('update.company');
            Route::delete('/delet-comany/{client_company}', 'delete_company')->name('delete.company');
            Route::post('/save-comanycategory', 'save_category')->name('save.company-category');
            Route::put('/update-comanycategory/{client_company_category}', 'update_category')->name('update.company-category');
            Route::delete('/delet-comanycategory/{client_company_category}', 'delete_category')->name('delete.company-category');
            Route::post('/bulk_delete-company', 'bulk_delete_company')->name('bulk-delete.company');
            Route::post('/company/unarchive/{client_company}', 'unarchive')->name('unarchive.company');  
        });

        Route::controller(CompanyImportController::class)->group(function () {  
            Route::get('/admin/companies/import-form', 'import_form')->name('companies.import');
            Route::get('/companies/template/download','download_template')->name('companies.template.download');
            Route::post('/companies/details/preview','preview')->name('companies.details.preview');
            Route::post('/companies/store/details','store_upload')->name('companies.details.store');
        });

        Route::controller(Client_contactController::class)->group(function () {
            Route::get('/admin/contacts', 'index')->name('manage.client_contact');
            Route::post('/save-contact-tag', 'save_tag')->name('save.contact-tag');
            Route::put('/update-contact-tag/{client_contact_tag}', 'update_tag')->name('update.contact-tag');
            Route::delete('/delet-contact-tag/{client_contact_tag}', 'delete_tag')->name('delete.contact-tag');
            Route::post('/save-contact', 'save_contact')->name('save.contact');
            Route::put('/update-contact/{client_contact}', 'update_contact')->name('update.contact');
            Route::delete('/delet-contact/{client_contact}', 'delete_contact')->name('delete.contact');
            Route::post('/primary-contact/update/{client_contact}', 'update_primary_contact')->name('update.primary.contact'); 
            Route::post('/save-contactcategory', 'save_category')->name('save.contact-category');
            Route::put('/update-contactcategory/{client_contact_category}', 'update_category')->name('update.contact-category');
            Route::delete('/delet-contactcategory/{client_contact_category}', 'delete_category')->name('delete.contact-category');
            Route::get('/contact/search', 'contact_search')->name('contact.search');
            Route::post('/bulk_delete-contact', 'bulk_delete_contact')->name('bulk-delete.contact');
            Route::post('/contact/unarchive/{client_contact}', 'unarchive')->name('unarchive.contact');  
        });

        Route::controller(ContactImportController::class)->group(function () {
            Route::get('/admin/contacts/import-form', 'import_form')->name('contacts.import');
            Route::get('/contacts/template/download','download_template')->name('contacts.template.download');
            Route::post('/contacts/details/preview','preview')->name('contacts.details.preview');
            Route::post('/contacts/store/details','store_upload')->name('contacts.details.store');
        }); 

        //Integrations
        Route::get('/admin/client-integrations', [ClientIntegrationController::class, 'show'])
        ->name('client.integrations.show');


        // Xero Integration (user must be logged in)
        Route::prefix('xero')->name('xero.')->group(function () {
            Route::get('/connect', [XeroController::class, 'connect'])->name('connect');          // starts OAuth
            Route::post('/disconnect', [XeroController::class, 'disconnect'])->name('disconnect');
   
            // Manual sync triggers   
            Route::post('/sync/xero',     [XeroController::class, 'syncXero'])->name('sync');
            Route::post('/sync/contacts',     [XeroController::class, 'syncContacts'])->name('sync.contacts');
            Route::post('/sync/invoices',     [XeroController::class, 'syncInvoices'])->name('sync.invoices');
            Route::post('/sync/credit-notes', [XeroController::class, 'syncCreditNotes'])->name('sync.credit_notes');
        });

    });

    // FSANZ Routes
    Route::prefix('fsanz')->name('fsanz.')->group(function () {
        Route::get('/nutrition', [FsanzController::class, 'nutrition'])->name('nutrition');
        Route::get('/properties', [FsanzController::class, 'properties'])->name('properties');
        Route::get('/details/{id}', [FsanzController::class, 'details'])->name('details');

        
    });   


    Route::prefix('fsanz_weight')->name('fsanz_weight.')->group(function () {
        Route::get('/nutrition', [FsanzController::class, 'nutrition_weight'])->name('nutrition');
        Route::get('/properties', [FsanzController::class, 'properties_weight'])->name('properties');
        Route::get('/details/{id}', [FsanzController::class, 'details_weight'])->name('details');
    });

    Route::prefix('fsanz_food')->name('fsanz_food.')->group(function () {
        Route::get('/food', [FsanzFoodController::class, 'index'])->name('manage');
        Route::get('/data', [FsanzFoodController::class, 'getData'])->name('data');
        Route::get('/api/{id}', [FsanzFoodController::class, 'getFoodData'])->name('api.get');
        Route::get('/{id}', [FsanzFoodController::class, 'view'])->name('view');     
    });

    Route::controller(ImageController::class)->group(function () {
        Route::post('/remove/images/{id}', 'remove_images');
    });
    Route::controller(ImagemetaController::class)->group(function () {
        Route::get('/data/image-meta', 'index')->name('manage.image_meta');
    });
    Route::controller(WorkspaceController::class)->group(function () {
        Route::post('/get/ws_based_clientID', 'get_ws_list')->name('default-client'); 
        Route::post('/workspaces/make-primary', 'make_primary')->name('make_primary');
    });
    Route::controller(SupportController::class)->group(function () {
        Route::get('/manage/support', 'index')->name('support.manage');
        Route::post('/save/ticket', 'save')->name('save.ticket');
        Route::post('/edit/ticket/{ticket}', 'edit')->name('edit.ticket');
        Route::delete('/ticket/{ticket}', 'destroy')->name('support.destroy');
        // Route::get('/view/ticket/{ticket}', 'view_old')->name('view.ticket.old');
        Route::get('/view/ticket/{CID}/{WSID}', 'view')->name('view.ticket');
        Route::post('/update/ticket/{ticket}', 'update')->name('update.ticket');
        Route::post('/save/comment/{ticket}', 'save_comment')->name('save.comment');
        Route::post('/update/comment/{comment}', 'update_comment')->name('update.comment');  
        Route::delete('/comment/{comment}', 'comment_destroy')->name('comment.destroy');
        Route::post('/update/ticket-status/{ticket}', 'update_status')->name('update.ticket.status');  

    });

    Route::get('/plan/remainder', [ScheduleController::class, 'index'])->name('plan.remainder');
    

    

});

Route::get('/cognito/token', [CognitoAuthController::class, 'ensureFreshToken']);