<?php

namespace App\Http\Controllers;

use App\Models\{Client,client_key_personnel,client_factory_location,ClientProfile};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientProfileController extends Controller
{
    public function show($client_id)
    {
        $client = Client::findOrFail($client_id);

        // Check if the profile exists for the given client_id, if not, create a new instance with default values
        $profile = ClientProfile::with(['FactoryDetails','KeypersonDetails'])->where('client_id', $client_id)->first();

        // If no profile is found, create a new instance with default values
        if (!$profile) {
            $profile = self::get_client_profile();
        }

        return view('backend.client_profiles.company-profile', compact('client', 'profile'));
    }

    public function show_with_ws($client_id,$ws_id){
        $client = Client::findOrFail($client_id);
        $profile = ClientProfile::where('client_id', $client_id)->first();
        if (!$profile) {
            $profile = self::get_client_profile();
        }
        return view('backend.client_profiles.company-profile', compact('client', 'profile'));
    }

    public function get_client_profile(){
        $profile = new ClientProfile([
            'company_name' => '',
            'company_email' => '',
            'phone_number' => '',
            'website_url' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
            'country' => '',
            'established_date' => null,
            'legal_structure' => '',
            'number_of_employees' => 0,
            'annual_revenue' => 0,
            'social_media_links' => '',
            'company_logo_url' => '',
            'company_description' => '',
            'business_hours' => ''
        ]);
        return $profile;
    }

    public function update(Request $request, $client_id)
    {
        // Fetch the client and their profile
        $client = Client::findOrFail($client_id);
        $profile = ClientProfile::firstOrNew(['client_id' => $client_id]); // Use firstOrNew to handle non-existent profiles

        // Validate the incoming request data
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'trading_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'website_url' => 'nullable|url',
            'phone_number' => 'required|string|max:15',
            'established_date' => 'nullable|date',
            'company_description' => 'required|string',
            'legal_structure' => 'nullable|string',
            'gstStatus' => 'nullable|string',
            'abn' => 'nullable|string',
            'acn' => 'nullable|string',
            'nzbn' => 'nullable|string',
            'intCompanyReg' => 'nullable|string',
            'number_of_employees' => 'nullable|string',
            'annualCountry' => 'nullable|string',
            'annual_revenue' => 'nullable|numeric',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string|max:10',
            'country' => 'nullable|string',
            'social_media_links' => 'nullable|string',
            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'business_hours' => 'nullable|string',
            'remove_logo' => 'nullable|boolean',
            'primaryColor'=> 'nullable|string',
            'secondaryColor'=> 'nullable|string',
            'accentColor'=> 'nullable|string',
            'timeZone'=> 'nullable|string',
            'dateFormat'=> 'nullable|string',
            'timeFormat'=> 'nullable|string',
            'numberFormat'=> 'nullable|string',
            'emailNotifications'=> 'nullable|string',
            'inAppNotifications'=> 'nullable|string',
            'notificationFrequency'=> 'nullable|string',
            'anzsicCode'=> 'nullable|string',
            'processingLevelPrimarySelect'=> 'nullable|string',
            'processingLevelSecondarySelect'=> 'nullable|string',
            'processingLevelTertiarySelect'=> 'nullable|string',
            'productCategorySectorSelect'=> 'nullable|string',
            'productionTypeSelect'=> 'nullable|string',
            'commonAllergens'=> 'nullable|string',
            'lastAuditDate'=> 'nullable|date',
            'nextAuditDate'=> 'nullable|date'
        ]);
        $social = $request->input('social', []);
        $validated['social_links'] = (count($social) > 0 )? json_encode($social) : null;
        $certifications = $request->input('certifications', []);
        $validated['certifications'] = (count($certifications) > 0 )? json_encode($certifications) : null;
        $client->name = $validated['company_name'];
        $client->save();
        // Update the profile with validated data
        $profile->fill($validated);


        $locations = $request->input('location', []);
        // Filter invalid entries
        $locations = array_filter($locations, function ($loc) {
            return !empty($loc['location_name']) && !empty($loc['street_address']);
        });
        if(count($locations) > 0){
            $existingIDs = [];
            foreach ($locations as $key => $value) {
                 $factoryData = [
                                    'client_id'        => $client_id,
                                    'location_name'    => $value['location_name'] ?? null,
                                    'factory_locations'=> json_encode($value),
                                ];
                if (empty($value['location_id'])) {
                    $factory = client_factory_location::create($factoryData);
                    $existingIDs[] = $factory->id;
                }else{
                    $factory = client_factory_location::find($value['location_id']);
                    if ($factory) {
                        $factory->fill($factoryData)->save();
                        $existingIDs[] = $factory->id;
                    }
                }
            }
            client_factory_location::where('client_id', $client_id)->whereNotIn('id', $existingIDs)->delete();
        }else{
            client_factory_location::where('client_id',$client_id)->delete();
        }

        $keypersons = $request->input('keyperson', []);
        // Filter invalid entries
        $keypersons = array_filter($keypersons, function ($per) {
            return !empty($per['full_name']) && !empty($per['email']);
        });

        if(count($keypersons) > 0){
            $existingKPIDs = [];
            foreach ($keypersons as $key => $value) {
                $keyperson = [
                                'client_id'        => $client_id,
                                'keyperson_name'    => $value['full_name'] ?? null,
                                'key_personnel'=> json_encode($value),
                            ];
                if (empty($value['keyperson_id'])) {
                    $KPID = client_key_personnel::create($keyperson);
                    $existingKPIDs[] = $KPID->id;
                }else{
                    $KP = client_key_personnel::find($value['keyperson_id']);
                    if ($KP) {
                        $KP->fill($keyperson)->save();
                        $existingKPIDs[] = $KP->id;
                    }
                }
                client_key_personnel::where('client_id', $client_id)->whereNotIn('id', $existingKPIDs)->delete();
            }
        }else{
            client_key_personnel::where('client_id',$client_id)->delete();
        }
    

        // Handle logo removal
        if ($request->has('remove_logo') && $request->remove_logo) {
            if ($profile->company_logo_url) {
                $oldLogoPath = public_path($profile->company_logo_url);
                if (file_exists($oldLogoPath)) {
                    unlink($oldLogoPath); // Delete the old logo file
                }
            }
            $profile->company_logo_url = null; // Reset logo
        }

        // Handle file upload for company logo
        if ($request->hasFile('company_logo')) {
            // Check if the file was uploaded correctly
            $file = $request->file('company_logo');

            if ($file->isValid()) {
                // Generate a unique file name
                $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Move the file to the public directory
                $file->move(public_path('assets/img/company-logos'), $fileName);
        
                // Update the profile with the new logo URL
                $profile->company_logo_url = 'assets/img/company-logos/' . $fileName;
            } else {
                return response()->json(['success' => false, 'message' => 'File upload failed.'], 400);
            }
        }

        // Save the profile
        $profile->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Company profile updated successfully!',
                'redirect_url' => route('client.company-profile', ['client_id' => $client_id]),
            ]);
        }
    }

}