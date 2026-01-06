<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubscriptionPlanController extends Controller
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
     * Show the list of subscription plans.
     */
    public function index()
    {
        // $plans = SubscriptionPlan::all();
        $plans = SubscriptionPlan::get()->map(function ($p) {
            // MONTHLY: from column directly
            $monthly = (float) ($p->monthly_cost_per_user ?? 0);

            // ANNUAL display: use annual_cost_per_user / 12 (monthly-equivalent)
            // If annual_cost_per_user is null/0, fallback to monthly
            $annualTotal   = isset($p->annual_cost_per_user) ? (float)$p->annual_cost_per_user : null;
            $annualMonthly = ($annualTotal && $annualTotal > 0) ? ($annualTotal / 12) : $monthly;

            // Savings % compared to paying monthly for 12 months
            $fullYearAtMonthly = $monthly * 12;
            $savingsPct = 0;
            if ($annualTotal && $annualTotal > 0 && $fullYearAtMonthly > 0) {
                $savingsPct = (int) max(0, round((($fullYearAtMonthly - $annualTotal) / $fullYearAtMonthly) * 100));
            }

            $p->computed = [
                'monthly'       => $monthly,
                'annualMonthly' => $annualMonthly,
                'annualTotal'   => $annualTotal,
                'savingsPct'    => $savingsPct,
            ];
            return $p;
        });
        return view('backend.subscription_plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new subscription plan.
     */
    public function create()
    {
        return view('backend.subscription_plans.create');
    }

    /**
     * Store a newly created subscription plan in the database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), SubscriptionPlan::validationRules());

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $data = $validator->validated();
        $data['created_by'] = $this->user_id;
        $data['updated_by'] = $this->user_id;

        $plan = SubscriptionPlan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan created successfully.',
            'plan' => $plan
        ], 200);
    }

    /**
     * Show the form for editing the specified subscription plan.
     */
    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('backend.subscription_plans.edit', compact('subscriptionPlan'));
    }

    /**
     * Update the specified subscription plan in the database.
     **/
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validator = Validator::make($request->all(), SubscriptionPlan::validationRules($subscriptionPlan->id));

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $data = $validator->validated();
        $data['updated_by'] = $this->user_id;

        $subscriptionPlan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan updated successfully.',
            'plan' => $subscriptionPlan
        ], 200);
    }

    /**
     * Remove the specified subscription plan from the database.
     **/
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        try {
            $subscriptionPlan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Subscription plan deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }
}
