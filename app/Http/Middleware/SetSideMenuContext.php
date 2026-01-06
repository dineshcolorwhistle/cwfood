<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;

class SetSideMenuContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $menuType = $this->determineMenuType($request);

        // Share the menu type with all views
        View::share('sideMenuType', $menuType);

        return $next($request);
    }

    protected function determineMenuType($request)
    {
        // User Profile
        if ($request->is('user-profile*')) {
            return 'profile';
        }

        $platformAdminSections = [
            'clients*',
            'roles*',
            'pages*',
            'users/team*',
            'team-member-roles*',
            'subscription-plans*',
            'notifications*',
            'admin/manage*'
        ];

        foreach ($platformAdminSections as $section) {
            if ($request->is($section)) {
                return 'nutriflow_admin';
            }
        }

        $clientSuperAdminSections = [
            'client/*/company-profile*',
            'client/*/workspaces*',
            'client/role*',
            'manage/members*',
            'admin/company-profile*',
            'admin/members*',
            'admin/permissions*',
            'admin/companies*',
            'admin/contacts*',
            'admin/client-integrations*',
        ];

        foreach ($clientSuperAdminSections as $section) {
            if ($request->is($section)) {
                return 'client_super_admin';
            }
        }

        $AdminBillingSections = [
            'admin/subscription*',
            'admin/billing*',
        ];

        foreach ($AdminBillingSections as $section) {
            if ($request->is($section)) {
                return 'admin_billing';
            }
        }

        // Default menu
        return 'default';
    }
}
