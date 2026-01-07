@extends('backend.master', [
    'pageTitle' => 'Company Profile',
    'activeMenu' => [
        'item' => 'Client',
        'subitem' => 'Company',
        'additional' => '',
    ],
    'breadcrumbItems' => [
        ['label' => 'CW Food Admin', 'url' => '#'],
        ['label' => 'Company Profile']
    ],
])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .company-profile-card{border-radius: 0.5rem; background-color: var(--bs-white); margin-bottom: 1rem; border: none; padding: 20px;}
    
</style>
@endpush

@section('content')
    <div class="container-fluid company-profile px-0">
        <div class="">  
            <div class="card-header d-flex justify-content-between">
                <h1 class="page-title">Company Profile Settings</h1>
                <div class="right-side content d-flex flex-row-reverse align-items-center">
                    <div class="text-end">
                        <button type="button" class="btn btn-primary-orange plus-icon" id="editIcon" title="Edit Profile">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="companyProfileForm" enctype="multipart/form-data" method="POST">
                    @csrf
                    @method('POST')
                    <div class="company-profile-card">
                        <p class="card-title text-secondary">General Company Information</p>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="company_name">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name', $client->name) }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="trading_name">Trading Name <span class="text-danger">*</span></label>
                                <input type="text" name="trading_name" id="trading_name" class="form-control" value="{{ old('trading_name', $profile->trading_name) }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="company_email">Company Email <span class="text-danger">*</span></label>
                                <input type="email" name="company_email" id="company_email" class="form-control" value="{{ old('company_email', $profile->company_email) }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="website_url">Website URL</label>
                                <input type="url" name="website_url" id="website_url" class="form-control" value="{{ old('website_url', $profile->website_url) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="phone_number">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number', $profile->phone_number) }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="established_date">Established Date</label>
                                <input type="date" name="established_date" id="established_date" class="form-control" value="{{ old('established_date', $profile->established_date) }}">
                            </div>
                            <div class="col-md-12 form-group">
                                <label class="text-primary-orange" for="company_description">Company Description <span class="text-danger">*</span></label>
                                <textarea name="company_description" id="company_description" class="form-control" rows="3" required>{{ old('company_description', $profile->company_description) }}</textarea>
                            </div>
                            <div class="col-md-6 form-group">
                                @php
                                    $legal_structures = [
                                        'sole_trader_au' => 'Sole Trader (AU)',
                                        'partnership_au' => 'Partnership (AU)',
                                        'company_pty_ltd_au' => 'Company (Pty Ltd) (AU)',
                                        'trust_au' => 'Trust (AU)',
                                        'inc_association_au' => 'Incorporated Association (AU)',
                                        'cooperative_au' => 'Cooperative (AU)',
                                        'sole_trader_nz' => 'Sole Trader (NZ)',
                                        'partnership_nz' => 'Partnership (NZ)',
                                        'limited_company_nz' => 'Limited Company (NZ)',
                                        'trust_nz' => 'Trust (NZ)',
                                        'inc_society_nz' => 'Incorporated Society (NZ)',
                                    ];
                                    $selected = old('legal_structure', $profile->legal_structure ?? '');
                                @endphp
                                <label class="text-primary-orange" for="legal_structure">Legal Structure</label>
                                <select class="form-select" name="legal_structure" id="legal_structure">
                                    <option value="" disabled>Select</option>
                                    @foreach($legal_structures as $value => $label)
                                        <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-primary-orange" for="gstStatus">GST Registered?</label>
                                <select class="form-select" name="gstStatus" id="gstStatus">
                                    <option value="" disabled>Select</option>
                                    <option value="yes" {{ old('gstStatus', $profile->gstStatus ?? '') == 'yes' ? 'selected' : '' }}>Yes</option>
                                    <option value="no" {{ old('gstStatus', $profile->gstStatus ?? '') == 'no' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="abn" class="form-label text-primary">Australian Business Number (ABN)</label>
                                <input type="text" class="form-control" name="abn" id="abn" placeholder="12 345 678 901" value="{{ old('abn', $profile->abn) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="acn" class="form-label text-primary">Australian Company Number (ACN)</label>
                                <input type="text" class="form-control" name="acn" id="acn" placeholder="123 456 789" value="{{ old('acn', $profile->acn) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="nzbn" class="form-label text-primary">New Zealand Business Number (NZBN)</label>
                                <input type="text" class="form-control" name="nzbn" id="nzbn" placeholder="9429031234567" value="{{ old('nzbn', $profile->nzbn) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="intCompanyReg" class="form-label text-primary">Company Registration Number (International)</label>
                                <input type="text" class="form-control" name="intCompanyReg" id="intCompanyReg" placeholder="Your international registration number" value="{{ old('intCompanyReg', $profile->intCompanyReg) }}">
                            </div>

                            <div class="col-md-6 form-group">
                                @php
                                    $numberof_Employees = [
                                        '1-10' => '1-10 employees',
                                        '11-50' => '11-50 employees',
                                        '51-20' => '51-200 employees',
                                        '201-500' => '201-500 employees',
                                        '501-1000' => '501-1000 employees',
                                        '1001-5000' => '1001-5000 employees',
                                        '5001+' => '5001+ employees',
                                        
                                    ];
                                    $selected = old('number_of_employees', $profile->number_of_employees ?? '');
                                @endphp

                                <label for="number_of_employees" class="form-label text-primary">Number of Employees</label>
                                <select class="form-select" name="number_of_employees" id="number_of_employees">
                                    <option value="" disabled>Select</option>
                                    @foreach($numberof_Employees as $value => $label)
                                        <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="annual_revenue" class="form-label text-primary">Annual Revenue (optional)</label>
                                <div class="input-group">
                                    <select class="form-select" name="annualCountry" id="annualCountry" style="max-width: 100px;">
                                        <option value="AUD" {{ old('annualCountry', $profile->annualCountry ?? '') == 'AUD' ? 'selected' : '' }}>AUD</option>
                                        <option value="NZD" {{ old('annualCountry', $profile->annualCountry ?? '') == 'NZD' ? 'selected' : '' }}>NZD</option>
                                    </select>
                                    <input type="number" class="form-control" name="annual_revenue" id="annual_revenue" placeholder="1000000" step="0.01" value="{{ old('annual_revenue', $profile->annual_revenue) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="company-profile-card address-section">
                        <p class="card-title text-secondary">Main Office Address</p>
                        <span>This address will be used for official forms, correspondence, and letterheads.</span>
                        <div class="row">
                            <div class="form-group">
                                <label for="autocomplete" class="form-label text-primary">Search Address</label>
                                <input type="text" class="form-control autocomplete-address" id="autocomplete" placeholder="Start typing your address...">
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="address" class="form-label text-primary">Street Address</label>
                                    <input type="text" class="form-control address" name="address" id="address" data-field="street_number,route" placeholder="123 Main St" value="{{ old('address', $profile->address) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="city" class="form-label text-primary">Suburb</label>
                                    <input type="text" class="form-control city" name="city" id="city" data-field="locality" placeholder="Sydney CBD" value="{{ old('city', $profile->city) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="state" class="form-label text-primary">State / Region</label>
                                    <input type="text" class="form-control state" name="state" id="state" data-field="administrative_area_level_1" placeholder="NSW" value="{{ old('state', $profile->state) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="zip_code" class="form-label text-primary">Postcode / Zip Code</label>
                                    <input type="text" class="form-control zip_code" name="zip_code" id="zip_code" data-field="postal_code" placeholder="2000" value="{{ old('zip_code', $profile->zip_code) }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="country" class="form-label text-primary">Country</label>
                                <input type="text" class="form-control country" name="country" id="country" data-field="country" placeholder="Australia" value="{{ old('country', $profile->country) }}">
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 border">
                        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#factorylocationCollapse" aria-expanded="true" aria-controls="factorylocationCollapse" style="cursor: pointer;">
                            <h5 class="card-title text-secondary mb-0">Factory / Additional Locations</h5>
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                        <div class="collapse" id="factorylocationCollapse" style="">
                            <div class="card-body">
                                <small class="form-text text-muted">These locations will be used for disclosure in product specifications and equivalent documents.</small>
                                <div class="d-flex justify-content-end mb-3">
                                    <button type="button" class="btn btn-primary btn-sm" id="addLocationBtn">
                                        <span class="material-symbols-outlined">add</span> Add Location
                                    </button>
                                </div>
                                <div id="locationsContainer"> </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title text-secondary">Factory / Additional Locations</h5>
                            <small class="form-text text-muted">These locations will be used for disclosure in product specifications and equivalent documents.</small>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary btn-sm" id="addLocationBtn">
                                    <span class="material-symbols-outlined">add</span> Add Location
                                </button>
                            </div>
                            <div id="locationsContainer"> </div>
                        </div>
                    </div> -->


                    <div class="card mb-4 border">
                        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#keyPersonCollapse" aria-expanded="true" aria-controls="keyPersonCollapse" style="cursor: pointer;">
                            <h5 class="card-title text-secondary mb-0">Key Personnel</h5>
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                        <div class="collapse" id="keyPersonCollapse" style="">
                            <div class="card-body">
                                <p class="form-text text-muted mb-3">Use this section to add key individuals in your company beyond primary contacts, and to designate Compliance Officers.</p>
                                <div id="keyPersonnelContainer"> </div>
                                <button type="button" class="btn btn-primary btn-sm mt-3" id="addKeyPersonnelBtn">
                                    <span class="material-symbols-outlined">add</span> Add Key Personnel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title text-secondary">Key Personnel</h5>
                        </div>
                        <div class="card-body">
                            <p class="form-text text-muted mb-3">Use this section to add key individuals in your company beyond primary contacts, and to designate Compliance Officers.</p>
                            <div id="keyPersonnelContainer"> </div>
                            <button type="button" class="btn btn-primary btn-sm mt-3" id="addKeyPersonnelBtn">
                                <span class="material-symbols-outlined">add</span> Add Key Personnel
                            </button>
                        </div>
                    </div> -->
                    
                    <!-- Branding & Visual Identity Card -->
                    <div class="card mb-4 border">
                        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#advancedSettingsCollapse" aria-expanded="true" aria-controls="advancedSettingsCollapse" style="cursor: pointer;">
                            <h5 class="card-title text-secondary mb-0">Branding &amp; Visual Identity</h5>
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                        <div class="collapse" id="advancedSettingsCollapse" style="">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="profile-picture-container d-flex align-items-center mb-5">
                                                <img 
                                                    src="{{ isset($profile) && $profile->company_logo_url 
                                                        ? asset($profile->company_logo_url) 
                                                        : asset('assets/img/default-company-logo.png') }}" 
                                                    class="img-fluid company-profile-picture me-4" 
                                                    alt="Company Logo"
                                                    data-default-avatar="{{ asset('assets/img/default-company-logo.png') }}"
                                                >

                                                <input type="file" name="company_logo" id="logoInput" class="d-none" accept="image/*">
                                                <div id="changeremovelogo" class="mt-2" style="display: none;">
                                                    <a type="button" class="text-primary-blue text-decoration-none hover me-1" id="changeLogoBtn">
                                                        Change Logo
                                                    </a>
                                                    <input type="hidden" name="remove_logo" id="removeLogo" value="0">
                                                    <a type="button" class="text-primary-orange text-decoration-none hover" id="removeLogoBtn"
                                                        {{ isset($profile) && $profile->company_logo_url ? '' : 'style=display:none;' }}>
                                                        Remove Logo
                                                    </a>
                                                </div>
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-6 mb-3">
                                        <label for="favicon" class="form-label text-primary">Favicon</label>
                                        <input type="file" class="form-control" id="favicon" accept="image/*">
                                        <small class="form-text text-muted">Recommended: ICO or PNG (16x16px, 32x32px).</small>
                                    </div> -->
                                </div>
                                <h6 class="text-primary mt-3">Brand Colors</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="primaryColor" class="form-label text-primary">Primary Color</label>
                                        <p class="form-label text-primary" style=" font-size: 9px !important; ">*Used for Reports &amp; Downloadables</p>
                                        <input type="color" class="form-control form-control-color" name="primaryColor" id="primaryColor" value="{{ old('primaryColor', $profile->primaryColor ?? '#328678') }}">
                                        <span id="primaryColorHex" class="form-text text-muted">{{ old('primaryColor', $profile->primaryColor ?? '#328678') }}</span>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="secondaryColor" class="form-label text-primary">Secondary Color</label>
                                        <p class="form-label text-primary" style=" font-size: 9px !important; ">*Used for Reports &amp; Downloadables</p>
                                        <input type="color" class="form-control form-control-color" id="secondaryColor" name="secondaryColor" value="{{ old('secondaryColor', $profile->secondaryColor ?? '#009fff') }}">
                                        <span id="secondaryColorHex" class="form-text text-muted">{{ old('secondaryColor', $profile->secondaryColor ?? '#009fff') }}</span>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="accentColor" class="form-label text-primary">Accent Color</label>
                                        <p></p>
                                        <input type="color" class="form-control form-control-color" id="accentColor" name="accentColor" value="{{ old('accentColor', $profile->accentColor ?? '#FFB1A0') }}">
                                        <span id="accentColorHex" class="form-text text-muted">{{ old('accentColor', $profile->accentColor ?? '#FFB1A0') }}</span>
                                    </div>
                                </div>
                                <div class="mb-3" style="display:none;">
                                    <label class="form-label text-primary">Color Preview:</label>
                                    <div class="d-flex gap-2">
                                        <div id="colorPreviewPrimary" style="width: 50px; height: 50px; border-radius: 8px; border: 1px solid var(--bs-dark-snow); background-color: {{ old('primaryColor', $profile->primaryColor ?? '#328678'); }};"></div>
                                        <div id="colorPreviewSecondary" style="width: 50px; height: 50px; border-radius: 8px; border: 1px solid var(--bs-dark-snow); background-color: {{ old('secondaryColor', $profile->secondaryColor ?? '#009fff') }};"></div>
                                        <div id="colorPreviewAccent" style="width: 50px; height: 50px; border-radius: 8px; border: 1px solid var(--bs-dark-snow); background-color: {{ old('accentColor', $profile->accentColor ?? '#FFB1A0') }};"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Links Card (Moved) -->
                    <div class="card mb-4 border">
                        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#advancedSettingsCollapse2" aria-expanded="true" aria-controls="advancedSettingsCollapse2" style="cursor: pointer;">
                            <h5 class="card-title text-secondary mb-0">Social Media Links</h5>
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                        <div class="collapse" id="advancedSettingsCollapse2" style="">
                            <div class="card-body">
                                    @php 
                                    $socialArray = [];
                                    if($profile->social_links){
                                        $socialArray = json_decode($profile->social_links, true); // decode as assoc array
                                    }
                                @endphp

                                <div class="mb-3">
                                    <label for="facebookURL" class="form-label text-primary">Facebook URL</label>
                                    <input type="url" class="form-control" name="social[facebookURL]" id="facebookURL" placeholder="https://www.facebook.com/yourcompany" value="{{ $socialArray['facebookURL'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="instagramURL" class="form-label text-primary">Instagram URL</label>
                                    <input type="url" class="form-control" name="social[instagramURL]" id="instagramURL" placeholder="https://www.instagram.com/yourcompany" value="{{ $socialArray['instagramURL'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="linkedinURL" class="form-label text-primary">LinkedIn URL</label>
                                    <input type="url" class="form-control" name="social[linkedinURL]" id="linkedinURL" placeholder="https://www.linkedin.com/company/yourcompany" value="{{ $socialArray['linkedinURL'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="twitterURL" class="form-label text-primary">X (Twitter) URL</label>
                                    <input type="url" class="form-control" name="social[twitterURL]" id="twitterURL" placeholder="https://twitter.com/yourcompany" value="{{ $socialArray['twitterURL'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="youtubeURL" class="form-label text-primary">YouTube URL</label>
                                    <input type="url" class="form-control" name="social[youtubeURL]" id="youtubeURL" placeholder="https://www.youtube.com/yourchannel" value="{{ $socialArray['youtubeURL'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="pinterestURL" class="form-label text-primary">Pinterest URL</label>
                                    <input type="url" class="form-control" name="social[pinterestURL]" id="pinterestURL" placeholder="https://www.pinterest.com/yourcompany" value="{{ $socialArray['pinterestURL'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="tiktokURL" class="form-label text-primary">TikTok URL</label>
                                    <input type="url" class="form-control" name="social[tiktokURL]" id="tiktokURL" placeholder="https://www.tiktok.com/@yourcompany" value="{{ $socialArray['tiktokURL'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SaaS Specific Settings Card -->
                    <div class="card mb-4 border">
                        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#advancedSettingsCollapse3" aria-expanded="true" aria-controls="advancedSettingsCollapse3" style="cursor: pointer;">
                            <h5 class="card-title text-secondary mb-0">SaaS Specific Settings</h5>
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                        <div class="collapse" id="advancedSettingsCollapse3" style="">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="timeZone" class="form-label text-primary">Local Time Zone</label>
                                        <select class="form-select" id="timeZone" name="timeZone">
                                            <option value="" disabled>Select Time Zone</option>
                                            <optgroup label="Australia">
                                                <option value="Australia/Sydney" @selected(old('timeZone', $profile->timeZone) === 'Australia/Sydney')>Sydney (AEST)</option>
                                                <option value="Australia/Melbourne" @selected(old('timeZone', $profile->timeZone) === 'Australia/Melbourne')>Melbourne (AEST)</option>
                                                <option value="Australia/Brisbane" @selected(old('timeZone', $profile->timeZone) === 'Australia/Brisbane')>Brisbane (AEST)</option>
                                                <option value="Australia/Adelaide" @selected(old('timeZone', $profile->timeZone) === 'Australia/Adelaide')>Adelaide (ACST)</option>
                                                <option value="Australia/Perth" @selected(old('timeZone', $profile->timeZone) === 'Australia/Perth')>Perth (AWST)</option>
                                                <option value="Australia/Darwin" @selected(old('timeZone', $profile->timeZone) === 'Australia/Darwin')>Darwin (ACST)</option>
                                                <option value="Australia/Hobart" @selected(old('timeZone', $profile->timeZone) === 'Australia/Hobart')>Hobart (AEST)</option>
                                            </optgroup>

                                            <optgroup label="New Zealand">
                                                <option value="Pacific/Auckland" @selected(old('timeZone', $profile->timeZone) === 'Pacific/Auckland')>Auckland (NZST)</option>
                                                <option value="Pacific/Chatham" @selected(old('timeZone', $profile->timeZone) === 'Pacific/Chatham')>Chatham (CHAST)</option>
                                            </optgroup>

                                            <optgroup label="Asia">
                                                <option value="Asia/Tokyo" @selected(old('timeZone', $profile->timeZone) === 'Asia/Tokyo')>Tokyo (JST)</option>
                                                <option value="Asia/Shanghai" @selected(old('timeZone', $profile->timeZone) === 'Asia/Shanghai')>Shanghai (CST)</option>
                                                <option value="Asia/Dubai" @selected(old('timeZone', $profile->timeZone) === 'Asia/Dubai')>Dubai (GST)</option>
                                                <option value="Asia/Kolkata" @selected(old('timeZone', $profile->timeZone) === 'Asia/Kolkata')>Kolkata (IST)</option>
                                                <option value="Asia/Singapore" @selected(old('timeZone', $profile->timeZone) === 'Asia/Singapore')>Singapore (SGT)</option>
                                                <option value="Asia/Hong_Kong" @selected(old('timeZone', $profile->timeZone) === 'Asia/Hong_Kong')>Hong Kong (HKT)</option>
                                            </optgroup>

                                            <optgroup label="Europe">
                                                <option value="Europe/London" @selected(old('timeZone', $profile->timeZone) === 'Europe/London')>London (GMT/BST)</option>
                                                <option value="Europe/Paris" @selected(old('timeZone', $profile->timeZone) === 'Europe/Paris')>Paris (CET/CEST)</option>
                                                <option value="Europe/Berlin" @selected(old('timeZone', $profile->timeZone) === 'Europe/Berlin')>Berlin (CET/CEST)</option>
                                                <option value="Europe/Rome" @selected(old('timeZone', $profile->timeZone) === 'Europe/Rome')>Rome (CET/CEST)</option>
                                                <option value="Europe/Moscow" @selected(old('timeZone', $profile->timeZone) === 'Europe/Moscow')>Moscow (MSK)</option>
                                                <option value="Europe/Dublin" @selected(old('timeZone', $profile->timeZone) === 'Europe/Dublin')>Dublin (GMT/IST)</option>
                                            </optgroup>

                                            <optgroup label="North America">
                                                <option value="America/New_York" @selected(old('timeZone', $profile->timeZone) === 'America/New_York')>New York (EST/EDT)</option>
                                                <option value="America/Chicago" @selected(old('timeZone', $profile->timeZone) === 'America/Chicago')>Chicago (CST/CDT)</option>
                                                <option value="America/Denver" @selected(old('timeZone', $profile->timeZone) === 'America/Denver')>Denver (MST/MDT)</option>
                                                <option value="America/Los_Angeles" @selected(old('timeZone', $profile->timeZone) === 'America/Los_Angeles')>Los Angeles (PST/PDT)</option>
                                                <option value="America/Vancouver" @selected(old('timeZone', $profile->timeZone) === 'America/Vancouver')>Vancouver (PST/PDT)</option>
                                                <option value="America/Toronto" @selected(old('timeZone', $profile->timeZone) === 'America/Toronto')>Toronto (EST/EDT)</option>
                                                <option value="America/Mexico_City" @selected(old('timeZone', $profile->timeZone) === 'America/Mexico_City')>Mexico City (CST/CDT)</option>
                                            </optgroup>

                                            <optgroup label="South America">
                                                <option value="America/Sao_Paulo" @selected(old('timeZone', $profile->timeZone) === 'America/Sao_Paulo')>Sao Paulo (BRT)</option>
                                                <option value="America/Buenos_Aires" @selected(old('timeZone', $profile->timeZone) === 'America/Buenos_Aires')>Buenos Aires (ART)</option>
                                                <option value="America/Santiago" @selected(old('timeZone', $profile->timeZone) === 'America/Santiago')>Santiago (CLT)</option>
                                            </optgroup>

                                            <optgroup label="Africa">
                                                <option value="Africa/Johannesburg" @selected(old('timeZone', $profile->timeZone) === 'Africa/Johannesburg')>Johannesburg (SAST)</option>
                                                <option value="Africa/Cairo" @selected(old('timeZone', $profile->timeZone) === 'Africa/Cairo')>Cairo (EET)</option>
                                                <option value="Africa/Casablanca" @selected(old('timeZone', $profile->timeZone) === 'Africa/Casablanca')>Casablanca (WET)</option>
                                            </optgroup>

                                            <optgroup label="Pacific">
                                                <option value="Pacific/Fiji" @selected(old('timeZone', $profile->timeZone) === 'Pacific/Fiji')>Fiji (FJT)</option>
                                                <option value="Pacific/Honolulu" @selected(old('timeZone', $profile->timeZone) === 'Pacific/Honolulu')>Honolulu (HST)</option>
                                                <option value="Pacific/Noumea" @selected(old('timeZone', $profile->timeZone) === 'Pacific/Noumea')>Noumea (NCT)</option>
                                            </optgroup>
                                        </select>

                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="dateFormat" class="form-label text-primary">Date Format</label>
                                        <select class="form-select" name="dateFormat" id="dateFormat">
                                            <option value="DD/MM/YYYY" @selected(old('dateFormat', $profile->dateFormat) === 'DD/MM/YYYY')>DD/MM/YYYY (e.g., 25/12/2023)</option>
                                            <option value="MM/DD/YYYY" @selected(old('dateFormat', $profile->dateFormat) === 'MM/DD/YYYY')>MM/DD/YYYY (e.g., 12/25/2023)</option>
                                            <option value="YYYY-MM-DD" @selected(old('dateFormat', $profile->dateFormat) === 'YYYY-MM-DD')>YYYY-MM-DD (e.g., 2023-12-25)</option>
                                            <option value="DD-MMM-YYYY" @selected(old('dateFormat', $profile->dateFormat) === 'DD-MMM-YYYY')>DD-MMM-YYYY (e.g., 25-Dec-2023)</option>
                                            <option value="D MMMM YYYY" @selected(old('dateFormat', $profile->dateFormat) === 'D MMMM YYYY')>D MMMM YYYY (e.g., 25 December 2023)</option>
                                            <option value="YYYY/MM/DD" @selected(old('dateFormat', $profile->dateFormat) === 'YYYY/MM/DD')>YYYY/MM/DD (e.g., 2023/12/25)</option>
                                            <option value="MMM DD, YYYY" @selected(old('dateFormat', $profile->dateFormat) === 'MMM DD, YYYY')>MMM DD, YYYY (e.g., Dec 25, 2023)</option>
                                            <option value="DD.MM.YYYY" @selected(old('dateFormat', $profile->dateFormat) === 'DD.MM.YYYY')>DD.MM.YYYY (e.g., 25.12.2023)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="timeFormat" class="form-label text-primary">Time Format</label>
                                        <select class="form-select" name="timeFormat" id="timeFormat">
                                            <option value="12hr" @selected(old('timeFormat', $profile->timeFormat) === '12hr')>12-hour (AM/PM)</option>
                                            <option value="24hr" @selected(old('timeFormat', $profile->timeFormat) === '24hr')>24-hour</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="numberFormat" class="form-label text-primary">Number Format</label>
                                        <select class="form-select" id="numberFormat" name="numberFormat">
                                            <option value="1,234.56" @selected(old('numberFormat', $profile->numberFormat) === '1,234.56')>1,234.56 (Comma for thousands, dot for decimal)</option>
                                            <option value="1.234,56" @selected(old('numberFormat', $profile->numberFormat) === '1.234,56') >1.234,56 (Dot for thousands, comma for decimal)</option>
                                        </select>
                                    </div>
                                </div>
                                <h6 class="text-primary mt-3">Notification Preferences</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="mb-1">
                                            <input class="form-check-input" type="checkbox" name="emailNotifications" id="emailNotifications" checked="">
                                            <label class="form-check-label" for="emailNotifications">Receive Email Notifications</label>
                                        </div>
                                        <div class="mb-1">
                                            <input class="form-check-input" type="checkbox" name="inAppNotifications" id="inAppNotifications" checked="">
                                            <label class="form-check-label" for="inAppNotifications">Receive In-App Notifications</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="notificationFrequency" class="form-label text-primary">Notification Frequency</label>
                                        <select class="form-select" id="notificationFrequency" name="notificationFrequency">
                                            <option value="immediate" @selected(old('notificationFrequency', $profile->notificationFrequency) === 'immediate')>Immediate</option>
                                            <option value="daily" @selected(old('notificationFrequency', $profile->notificationFrequency) === 'daily')>Daily Digest</option>
                                            <option value="weekly" @selected(old('notificationFrequency', $profile->notificationFrequency) === 'weekly')>Weekly Summary</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 

                    <!-- Food Manufacturing Details Card -->
                    <div class="card mb-4 border">
                        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#advancedSettingsCollapse4" aria-expanded="true" aria-controls="advancedSettingsCollapse4" style="cursor: pointer;">
                            <h5 class="card-title text-secondary mb-0">Food Manufacturing Details</h5>
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                        <div class="collapse" id="advancedSettingsCollapse4" style="">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="anzsicCode" class="form-label text-primary">ANZSIC Code (Australia/NZ Standard Industrial Classification)</label>
                                    <input type="text" class="form-control" id="anzsicCode" name="anzsicCode" placeholder="1174 (Bread and Cake Manufacturing)" value="{{ old('anzsicCode', $profile->anzsicCode ?? '') }}">
                                    <small class="form-text text-muted">Refer to ABS (AU) or Stats NZ for codes.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="processingLevelPrimarySelect" class="form-label text-primary">Primary Food Processing</label>
                                    <select class="form-select tag-select" id="processingLevelPrimarySelect" name="processingLevelPrimarySelect">
                                        <option value="" disabled>Add a selection</option>
                                        <option value="Milling" @selected(old('processingLevelPrimarySelect', $profile->processingLevelPrimarySelect) === 'Milling')>Milling (Grains into flour, semolina)</option>
                                        <option value="Slaughterhouses" @selected(old('processingLevelPrimarySelect', $profile->processingLevelPrimarySelect) === 'Slaughterhouses')>Slaughterhouses/Meat Processing (Butchering, mincemeat)</option>
                                        <option value="DairyPrimary" @selected(old('processingLevelPrimarySelect', $profile->processingLevelPrimarySelect) === 'DairyPrimary')>Dairy Primary (Raw milk collection, pasteurization, homogenization)</option>
                                        <option value="FruitVegBasic" @selected(old('processingLevelPrimarySelect', $profile->processingLevelPrimarySelect) === 'FruitVegBasic')>Fruit &amp; Vegetable Washing/Sorting/Basic Cutting</option>
                                        <option value="SugarRefining" @selected(old('processingLevelPrimarySelect', $profile->processingLevelPrimarySelect) === 'SugarRefining')>Sugar Refining</option>
                                        <option value="OilSeedCrushing" @selected(old('processingLevelPrimarySelect', $profile->processingLevelPrimarySelect) === 'OilSeedCrushing')>Oil Seed Crushing</option>
                                        <option value="FishFilleting" @selected(old('processingLevelPrimarySelect', $profile->processingLevelPrimarySelect) === 'FishFilleting')>Fish Filleting/Freezing</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="processingLevelSecondarySelect" class="form-label text-primary">Secondary Food Processing</label>
                                    <select class="form-select tag-select" id="processingLevelSecondarySelect" name="processingLevelSecondarySelect">
                                        <option value="" disabled>Add a selection</option>
                                        <option value="Bakeries" @selected(old('processingLevelSecondarySelect', $profile->processingLevelSecondarySelect) === 'Bakeries')>Bakeries (Bread, pastries, cakes, biscuits)</option>
                                        <option value="DairySecondary" @selected(old('processingLevelSecondarySelect', $profile->processingLevelSecondarySelect) === 'DairySecondary')>Dairy Secondary (Cheese, yogurt, butter, ice cream)</option>
                                        <option value="PastaProduction" @selected(old('processingLevelSecondarySelect', $profile->processingLevelSecondarySelect) === 'PastaProduction')>Pasta Production</option>
                                        <option value="BeverageProduction" @selected(old('processingLevelSecondarySelect', $profile->processingLevelSecondarySelect) === 'BeverageProduction')>Beverage Production (Wine, beer, juices, soft drinks)</option>
                                        <option value="Confectionery" @selected(old('processingLevelSecondarySelect', $profile->processingLevelSecondarySelect) === 'Confectionery')>Confectionery (Chocolates, candies)</option>
                                        <option value="Preserving" @selected(old('processingLevelSecondarySelect', $profile->processingLevelSecondarySelect) === 'Preserving')>Preserving (Jams, pickles, sauces)</option>
                                        <option value="CerealProduction" @selected(old('processingLevelSecondarySelect', $profile->processingLevelSecondarySelect) === 'CerealProduction')>Cereal Production (Breakfast cereals)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="processingLevelTertiarySelect" class="form-label text-primary">Tertiary Food Processing (Ultra-Processed Foods)</label>
                                    <select class="form-select tag-select" id="processingLevelTertiarySelect" name="processingLevelTertiarySelect">
                                        <option value="" disabled>Add a selection</option>
                                        <option value="FrozenMeals" @selected(old('processingLevelTertiarySelect', $profile->processingLevelTertiarySelect) === 'FrozenMeals')>Frozen Meals/Ready Meals</option>
                                        <option value="SnackFoods" @selected(old('processingLevelTertiarySelect', $profile->processingLevelTertiarySelect) === 'SnackFoods')>Snack Foods (Chips, extruded snacks, processed crackers)</option>
                                        <option value="ProcessedMeats" @selected(old('processingLevelTertiarySelect', $profile->processingLevelTertiarySelect) === 'ProcessedMeats')>Processed Meats (Sausages, ham, bacon)</option>
                                        <option value="PackagedDesserts" @selected(old('processingLevelTertiarySelect', $profile->processingLevelTertiarySelect) === 'PackagedDesserts')>Packaged Desserts (Instant puddings, pre-made cakes)</option>
                                        <option value="InfantFormula" @selected(old('processingLevelTertiarySelect', $profile->processingLevelTertiarySelect) === 'InfantFormula')>Infant Formula</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="productCategorySectorSelect" class="form-label text-primary">Product Category / Sector</label>
                                    <select class="form-select tag-select" id="productCategorySectorSelect" name="productCategorySectorSelect">
                                        <option value="" disabled>Add a selection</option>
                                        <option value="MeatPoultry" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'MeatPoultry')>Meat &amp; Poultry Processing</option>
                                        <option value="DairyProduct" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'DairyProduct')>Dairy Product Manufacturing</option>
                                        <option value="BakedGoodsCereals" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'BakedGoodsCereals')>Baked Goods &amp; Cereals</option>
                                        <option value="FruitVegetable" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'FruitVegetable')>Fruit &amp; Vegetable Processing</option>
                                        <option value="Beverage" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'Beverage')>Beverage Manufacturing</option>
                                        <option value="Seafood" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'Seafood')>Seafood Processing</option>
                                        <option value="ConfectionerySnacks" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'ConfectionerySnacks')>Confectionery &amp; Snack Foods</option>
                                        <option value="OilsFats" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'OilsFats')>Oils &amp; Fats Manufacturing</option>
                                        <option value="SugarSweeteners" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'SugarSweeteners')>Sugar &amp; Sweeteners</option>
                                        <option value="SpecialtyNiche" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'SpecialtyNiche')>Specialty &amp; Niche Foods</option>
                                        <option value="IngredientsAdditives" @selected(old('productCategorySectorSelect', $profile->productCategorySectorSelect) === 'IngredientsAdditives')>Ingredients &amp; Food Additives Manufacturing</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="productionTypeSelect" class="form-label text-primary">Production Type</label>
                                    <select class="form-select tag-select" id="productionTypeSelect" name="productionTypeSelect">
                                        <option value="" disabled>Add a selection</option>
                                        <option value="Artisan" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'Artisan')>Artisan / Small Batch</option>
                                        <option value="Automated" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'Automated')>Automated Production</option>
                                        <option value="Batch" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'Batch')>Batch Production</option>
                                        <option value="Continuous" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'Continuous')>Continuous Production</option>
                                        <option value="Custom" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'Custom')>Custom Order / Made-to-Order</option>
                                        <option value="DryBlending" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'DryBlending')>Dry Blending</option>
                                        <option value="Extrusion" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'Extrusion')>Extrusion</option>
                                        <option value="Fermentation" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'Fermentation')>Fermentation</option>
                                        <option value="FillingPackaging" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'FillingPackaging')>Filling &amp; Packaging</option>
                                        <option value="FreezingChilling" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'FreezingChilling')>Freezing / Chilling</option>
                                        <option value="HighVolume" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'HighVolume')>High Volume Production</option>
                                        <option value="MixingBlending" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'MixingBlending')>Mixing &amp; Blending</option>
                                        <option value="Pasteurization" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'Pasteurization')>Pasteurization</option>
                                        <option value="RoastingBaking" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'RoastingBaking')>Roasting / Baking</option>
                                        <option value="SmokingCuring" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'SmokingCuring')>Smoking / Curing</option>
                                        <option value="ThermalProcessing" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'ThermalProcessing')>Thermal Processing (e.g., Canning)</option>
                                        <option value="WetProcessing" @selected(old('productionTypeSelect', $profile->productionTypeSelect) === 'WetProcessing')>Wet Processing</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Compliance and Certifications Card -->
                    @php 
                        $selectedCerts = [];
                        if($profile->certifications){
                            $selectedCerts = json_decode($profile->certifications, true); // decode as assoc array
                        }
                    @endphp
                    <div class="card mb-4 border">
                        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#advancedSettingsCollapse5" aria-expanded="true" aria-controls="advancedSettingsCollapse5" style="cursor: pointer;">
                            <h5 class="card-title text-secondary mb-0">Compliance and Certifications</h5>
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                        <div class="collapse" id="advancedSettingsCollapse5" style="">
                            <div class="card-body">
                                <h6 class="text-primary mt-4">General Food Safety Certifications</h6>
                                <div class="mb-3">
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="HACCP" id="certHACCP" @checked(in_array('HACCP', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certHACCP">HACCP</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="ISO22000" id="certISO22000" @checked(in_array('ISO22000', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certISO22000">ISO 22000</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="SQF" id="certSQF" @checked(in_array('SQF', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certSQF">SQF (Safe Quality Food)</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="BRCGS" id="certBRCGS" @checked(in_array('BRCGS', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certBRCGS">BRCGS (Brand Reputation Compliance Global Standards)</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Halal" id="certHalal" @checked(in_array('Halal', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certHalal">Halal Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Kosher" id="certKosher" @checked(in_array('Kosher', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certKosher">Kosher Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="OrganicACO" id="certOrganicACO" @checked(in_array('OrganicACO', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certOrganicACO">Organic (ACO - Australia)</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="OrganicBioGroNZ" id="certOrganicBioGroNZ" @checked(in_array('OrganicBioGroNZ', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certOrganicBioGroNZ">Organic (BioGro NZ)</label>
                                    </div>
                                </div>

                                <h6 class="text-primary mt-4">Environmental &amp; Sustainability Certifications</h6>
                                <div class="mb-3">
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Carbon" id="certCarbonNeutral" @checked(in_array('Carbon', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certCarbonNeutral">Carbon Neutral / Climate Neutral Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Waste" id="certWasteManagement" @checked(in_array('Waste', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certWasteManagement">Waste Management / Recycling Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Water" id="certWaterStewardship" @checked(in_array('Water', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certWaterStewardship">Water Usage / Stewardship Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="GlobalGAP" id="certGlobalGAP" @checked(in_array('GlobalGAP', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certGlobalGAP">GlobalGAP (Good Agricultural Practices)</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Rainforest" id="certRainforestAlliance" @checked(in_array('Rainforest', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certRainforestAlliance">Rainforest Alliance Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="MSC" id="certMSC" @checked(in_array('MSC', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certMSC">MSC (Marine Stewardship Council) Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="ASC" id="certASC" @checked(in_array('ASC', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certASC">ASC (Aquaculture Stewardship Council) Certified</label>
                                    </div>
                                </div>

                                <h6 class="text-primary mt-4">Ethical Sourcing &amp; Social Compliance</h6>
                                <div class="mb-3">
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Fair" id="certFairTrade" @checked(in_array('Fair', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certFairTrade">Fair Trade Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Slavery" id="complianceModernSlavery" @checked(in_array('Slavery', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="complianceModernSlavery">Modern Slavery Act Compliance (AU)</label>
                                        <small class="form-text text-muted">Mandatory for businesses with revenue over A$100 million.</small>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Labour" id="auditLabourStandards" @checked(in_array('Labour', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="auditLabourStandards">Labour Standards Audits (e.g., SMETA/SEDEX)</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Animal" id="certAnimalWelfare" @checked(in_array('Animal', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certAnimalWelfare">Animal Welfare Certified (e.g., RSPCA Approved, SPCA Certified)</label>
                                    </div>
                                </div>

                                <h6 class="text-primary mt-4">Niche/Specialty Food Certifications</h6>
                                <div class="mb-3">
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Gluten" id="certGlutenFree" @checked(in_array('Gluten', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certGlutenFree">Gluten-Free Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Vegan" id="certVeganVegetarian" @checked(in_array('Vegan', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certVeganVegetarian">Vegan/Vegetarian Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="FODMAP" id="certFODMAPFriendly" @checked(in_array('FODMAP', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certFODMAPFriendly">FODMAP Friendly Certified</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Diabetic" id="certDiabeticFriendly" @checked(in_array('Diabetic', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certDiabeticFriendly">Diabetic Friendly / Low GI</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="GMO" id="certNonGMO" @checked(in_array('GMO', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certNonGMO">Non-GMO Project Verified</label>
                                    </div>
                                </div>

                                <h6 class="text-primary mt-4">Business &amp; Operational Excellence</h6>
                                <div class="mb-3">
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="ISO" id="certISO9001" @checked(in_array('ISO', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="certISO9001">ISO 9001 (Quality Management Systems)</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Supplier" id="programSupplierApproval" @checked(in_array('Supplier', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="programSupplierApproval">Supplier Approval Program/Framework</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Internal" id="programInternalAudit" @checked(in_array('Internal', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="programInternalAudit">Internal Audit Program</label>
                                    </div>
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Complaint" id="systemComplaintManagement" @checked(in_array('Complaint', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="systemComplaintManagement">Complaint Management System</label>
                                    </div>
                                </div>

                                <h6 class="text-primary mt-4">Food Safety Management Systems</h6>
                                <div class="mb-3">
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Allergen" id="hasAllergenPlan" @checked(in_array('Allergen', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="hasAllergenPlan">Has Allergen Management Plan?</label>
                                    </div>
                                    <label for="commonAllergens" class="form-label text-primary mt-2">Common Allergens Handled (if applicable)</label>
                                    <textarea class="form-control" id="commonAllergens" name="commonAllergens" rows="2" placeholder="Gluten, Dairy, Nuts, Soy">{{ old('commonAllergens', $profile->commonAllergens) }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="food" id="hasFoodSafetyProgram" @checked(in_array('food', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="hasFoodSafetyProgram">Has Food Safety Program?</label>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <label for="lastAuditDate" class="form-label text-primary">Last Food Safety Audit Date</label>
                                            <input type="date" class="form-control" id="lastAuditDate" name="lastAuditDate" value="{{ old('lastAuditDate', $profile->lastAuditDate) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="nextAuditDate" class="form-label text-primary">Next Food Safety Audit Date</label>
                                            <input type="date" class="form-control" id="nextAuditDate" name="nextAuditDate" value="{{ old('nextAuditDate', $profile->nextAuditDate) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="mb-1">
                                        <input class="form-check-input" type="checkbox" name="certifications[]" value="Recall" id="hasRecallPlan" @checked(in_array('Recall', old('certifications', $selectedCerts)))>
                                        <label class="form-check-label" for="hasRecallPlan">Has Product Recall Plan?</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12 form-group mt-4" id="saveCancelButtons" style="display: none;">
                        <button type="submit" class="btn btn-secondary-blue" id="updateCompanyProfileBtn">
                            Save Changes
                        </button>
                        <button type="button" class="btn btn-secondary-blue ms-2" id="cancelBtn">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>  
@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let locationIndex = 0; // keep track of added locations
    const locations = {!! json_encode($profile->FactoryDetails) !!}; // safer way  
    let locationData = [];
    if (locations) {
        try {
            $.each(locations, function(index, item) {
                if (item.factory_locations) {
                    let factoryData = JSON.parse(item.factory_locations);
                    factoryData.id = item.id;
                    locationData.push(factoryData);
                }
            });
        } catch (e) {
            console.error("JSON parse error:", e);
        }
    }

    let keyPersonIndex = 0; // keep track of added locations
    const keyPersons = {!! json_encode($profile->KeypersonDetails) !!}; // safer way
    let keyPersonData = [];
    if (keyPersons) {
        try {
            $.each(keyPersons, function(index, item) {
                if (item.key_personnel) {
                    let keyPersonDetails = JSON.parse(item.key_personnel);
                    keyPersonDetails.id = item.id;
                    keyPersonData.push(keyPersonDetails);
                }
            });
        } catch (e) {
            console.error("JSON parse error:", e);
        }
    }


    $(document).ready(function() {
        // If no locations exist in DB, create the first one
        if (!locationData || locationData.length === 0) {
            addLocation();
        } else {
            // If locations exist, you can loop and render them
            locationData.forEach((loc, i) => {
                addLocation(i + 1, loc);
            });
        }

        // If no key person exist in DB, create the first one
        if (!keyPersonData || keyPersonData.length === 0) {
            addKeyperson();
        } else {
            // If locations exist, you can loop and render them
            keyPersonData.forEach((person, i) => {
                addKeyperson(i + 1, person);
            });
        }
        

        // On click Add Location
        $("#addLocationBtn").on("click", function () {
            addLocation();
        });

        // Remove Location
        $(document).on("click", ".remove-location-btn", function () {
            $(this).closest(".location-item").remove();
        });


        // On click Add Keyperson
        $("#addKeyPersonnelBtn").on("click", function () {
            addKeyperson();
        });

        // Remove Keyperson
        $(document).on("click", ".remove-key-personnel-btn", function () {
            $(this).closest(".key-personnel-item").remove();
        });


        // Initialize color picker functionality
        const primaryColorInput = document.getElementById('primaryColor');
        const primaryColorHex = document.getElementById('primaryColorHex');
        const colorPreviewPrimary = document.getElementById('colorPreviewPrimary');

        const secondaryColorInput = document.getElementById('secondaryColor');
        const secondaryColorHex = document.getElementById('secondaryColorHex');
        const colorPreviewSecondary = document.getElementById('colorPreviewSecondary');

        const accentColorInput = document.getElementById('accentColor');
        const accentColorHex = document.getElementById('accentColorHex');
        const colorPreviewAccent = document.getElementById('colorPreviewAccent');

        function updateColorDisplay(input, hexSpan, previewDiv) {
            const color = input.value;
            hexSpan.textContent = color.toUpperCase();
            previewDiv.style.backgroundColor = color;
        }

        // Perform initial updates for color previews based on default values
        updateColorDisplay(primaryColorInput, primaryColorHex, colorPreviewPrimary);
        updateColorDisplay(secondaryColorInput, secondaryColorHex, colorPreviewSecondary);
        updateColorDisplay(accentColorInput, accentColorHex, colorPreviewAccent);

        // Add event listeners for color input changes to update display in real-time
        primaryColorInput.addEventListener('input', () => updateColorDisplay(primaryColorInput, primaryColorHex, colorPreviewPrimary));
        secondaryColorInput.addEventListener('input', () => updateColorDisplay(secondaryColorInput, secondaryColorHex, colorPreviewSecondary));
        accentColorInput.addEventListener('input', () => updateColorDisplay(accentColorInput, accentColorHex, colorPreviewAccent));

        // Handle form submission (AJAX)
        $('#companyProfileForm').submit(function(e) {
            e.preventDefault(); // Prevent the default form submission

            var formData = new FormData(this); // Create FormData object from form data
            console.log(formData);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: "{{ route('client.company-profile.update', ['client_id' => $client->id]) }}", // The route URL
                type: "POST",
                data: formData,
                processData: false, // Required for file uploads
                contentType: false, // Required for file uploads
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Profile updated successfully!',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#4CAF50'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = response.redirect_url; // Redirect to the same page or another page
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: 'Something went wrong. Please try again.',
                            confirmButtonColor: '#F44336'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred: ' + xhr.responseText,
                        confirmButtonColor: '#F44336'
                    });
                }
            });
        });

        // Change logo button action
        $('#changeLogoBtn').click(function() {
            $('#logoInput').click();
        });

        // Handle logo input change
        $('#logoInput').change(function() {
            if (this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('.company-profile-picture').attr('src', e.target.result);
                    $('#removeLogoBtn').show();
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        $('#removeLogoBtn').click(function() {
            const defaultAvatar = $('.company-profile-picture').data('default-avatar');
            
            // Set the default avatar as the image source
            $('.company-profile-picture').attr('src', defaultAvatar);
            
            // Hide the remove button and clear the file input
            $('#removeLogoBtn').hide();
            $('#logoInput').val('');

            // Set the hidden field to indicate the logo should be removed
            $('#removeLogo').val('1');
        });

        // When the Edit icon is clicked
        $('#editIcon').click(function() {
            // Switch to edit mode
            toggleFormFields(false);

            // Hide Edit icon and show Save/Cancel buttons
            $('#editIcon').hide();
            $('#saveCancelButtons').show();
            $('#changeremovelogo').show();
        });

        // When the Cancel button is clicked
        $('#cancelBtn').click(function() {
            // Reset form fields to readonly (view mode)
            toggleFormFields(true);

            // Hide Save/Cancel buttons and show Edit icon
            $('#editIcon').show();
            $('#saveCancelButtons').hide();
            $('#changeremovelogo').hide();
        });


        toggleFormFields(true);
    });

    
    function addLocation(index = null, data = {}) {
        locationIndex++; // increase counter each time
        const i = index ?? locationIndex;
        let html = `
            <div class="location-item card mb-3 p-3 border address-section">
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-sm btn-danger remove-location-btn">
                        <span class="material-symbols-outlined">close</span> Remove
                    </button>
                </div>
                <div class="mb-3">
                    <label class="form-label text-primary">Location Name</label>
                    <input type="hidden" name="location[${i}][location_id]" value="${data.id ?? ''}">
                    <input type="text" class="form-control" 
                        name="location[${i}][location_name]" 
                        value="${data.location_name ?? ''}" 
                        placeholder="Sydney Production Facility">
                </div>
                <div class="mb-3">
                    <label class="form-label text-primary">Search Location Address</label>
                    <input type="text" class="form-control location-autocomplete autocomplete-address" 
                        placeholder="Start typing address...">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-primary">Street Address</label>
                        <input type="text" class="form-control location-street address" 
                            name="location[${i}][street_address]" 
                            value="${data.street_address ?? ''}" 
                            placeholder="123 Industrial Ave">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-primary">Suburb</label>
                        <input type="text" class="form-control location-suburb city" 
                            name="location[${i}][suburb]" 
                            value="${data.suburb ?? ''}" 
                            placeholder="Industrial Park">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-primary">State / Region</label>
                        <input type="text" class="form-control location-state state" 
                            name="location[${i}][state]" 
                            value="${data.state ?? ''}" 
                            placeholder="QLD">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-primary">Postcode / Zip Code</label>
                        <input type="text" class="form-control location-postcode zip_code" 
                            name="location[${i}][zip_code]" 
                            value="${data.zip_code ?? ''}" 
                            placeholder="4000">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-primary">Country</label>
                        <input type="text" class="form-control location-country" 
                            name="location[${i}][country]" 
                            value="${data.country ?? ''}" 
                            placeholder="Australia">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-primary">Contact Person / Manager</label>
                        <input type="text" class="form-control" 
                            name="location[${i}][contact_person]" 
                            value="${data.contact_person ?? ''}" 
                            placeholder="John Manager">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-primary">Location Email</label>
                        <input type="email" class="form-control country" 
                            name="location[${i}][location_email]" 
                            value="${data.location_email ?? ''}" 
                            placeholder="location@yourcompany.com">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-primary">Location Phone</label>
                    <input type="text" class="form-control" 
                        name="location[${i}][location_phone]" 
                        value="${data.location_phone ?? ''}" 
                        placeholder="+61 7 1234 5678">
                </div>
                <div class="mb-3">
                    <label class="form-label text-primary">Description / Purpose</label>
                    <textarea class="form-control" rows="2" 
                            name="location[${i}][purpose]" 
                            placeholder="Secondary distribution hub.">${data.purpose ?? ''}</textarea>
                </div>
            </div>
        `;
        const $newSection = $(html).appendTo("#locationsContainer");
        if (typeof google !== "undefined" && google.maps && google.maps.places) {
            initializeAddressSection($newSection[0]);
        } else {
            // Wait for it if still loading
            const interval = setInterval(() => {
                if (typeof google !== "undefined" && google.maps && google.maps.places) {
                    clearInterval(interval);
                    initializeAddressSection($newSection[0]);
                }
            }, 300);
        }
        // $("#locationsContainer").append(html);
        // initializeAddressSection(html);
    }

    function addKeyperson(index = null, data = {}) {
        keyPersonIndex++; // increment global counter
        const i = index ?? keyPersonIndex;

        let html = `
            <div class="key-personnel-item card mb-3 p-3 border">
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-sm btn-danger remove-key-personnel-btn">
                        <span class="material-symbols-outlined">close</span> Remove
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-primary">Full Name</label>
                        <input type="hidden" name="keyperson[${i}][keyperson_id]" value="${data.id ?? ''}">
                        <input type="text" class="form-control"
                            name="keyperson[${i}][full_name]"
                            value="${data.full_name ?? ''}"
                            placeholder="Alex Green">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-primary">Role / Title</label>
                        <input type="text" class="form-control"
                            name="keyperson[${i}][role]"
                            value="${data.role ?? ''}"
                            placeholder="Food Safety &amp; Quality Manager">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-primary">Email Address</label>
                        <input type="email" class="form-control"
                            name="keyperson[${i}][email]"
                            value="${data.email ?? ''}"
                            placeholder="alex.g@yourcompany.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-primary">Phone Number</label>
                        <input type="text" class="form-control"
                            name="keyperson[${i}][phone]"
                            value="${data.phone ?? ''}"
                            placeholder="04XX XXX XXX">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-primary">Areas of Responsibility</label>
                    <select class="form-select tag-select"
                            id="areasOfResponsibilitySelect_${i}"
                            name="keyperson[${i}][responsibility]">
                        <option value="">Add a responsibility...</option>
                        <option value="FoodSafety" ${data.responsibility === 'FoodSafety' ? 'selected' : ''}>Food Safety</option>
                        <option value="OHS" ${data.responsibility === 'OHS' ? 'selected' : ''}>OH&amp;S</option>
                        <option value="Privacy" ${data.responsibility === 'Privacy' ? 'selected' : ''}>Privacy</option>
                        <option value="RegulatoryAffairs" ${data.responsibility === 'RegulatoryAffairs' ? 'selected' : ''}>Regulatory Affairs</option>
                        <option value="QualityAssurance" ${data.responsibility === 'QualityAssurance' ? 'selected' : ''}>Quality Assurance</option>
                        <option value="EnvironmentalCompliance" ${data.responsibility === 'EnvironmentalCompliance' ? 'selected' : ''}>Environmental Compliance</option>
                    </select>
                    <div class="selected-tags-container mt-2" id="areasOfResponsibilityTags_${i}"></div>
                </div>

                <div class="mb-3">
                    <input class="form-check-input key_check" type="checkbox" id="isComplianceOfficer_${i}" name="keyperson[${i}][is_compliance_officer]" value="1" ${data.is_compliance_officer ? 'checked' : ''}>
                    <label class="form-check-label" for="isComplianceOfficer_${i}">
                        Is this person a Compliance Officer?
                    </label>
                    <small class="form-text text-muted compliance-officer-note" style="${data.is_compliance_officer ? 'display:block;' : 'display:none;'}">
                        This person has been provided with the authority to issue product specifications.
                    </small>
                </div>
            </div>
        `;

        $("#keyPersonnelContainer").append(html);
    }


    $(document).on('change','.key_check',function(event){
        const note = event.target.closest('.mb-3').querySelector('.compliance-officer-note');
        if (event.target.checked) {
            note.style.display = 'block';
        } else {
            note.style.display = 'none';
        }
    });

    function toggleFormFields(isReadonly) {
        $("#companyProfileForm").find("input, textarea, select, button").prop("disabled", isReadonly);

        // Also toggle button visibility if form is readonly
        if (isReadonly) {
            $('#saveCancelButtons').hide();
        }
    }
</script>

<script>
function initializeAddressSection(section) {
    const input = section.querySelector('.autocomplete-address');
        if (!input) return;

        const country = section.dataset.country || 'au';

        // Prevent duplicate initialization
        if (input.dataset.autocompleteAttached) return;
        input.dataset.autocompleteAttached = true;

        // Initialize autocomplete for this section
        const autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country },
            fields: ["address_components", "geometry", "formatted_address"],
            types: ["address"]
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place.address_components) return;

            // Clear existing values
            section.querySelector('.address').value = '';
            section.querySelector('.city').value = '';
            section.querySelector('.state').value = '';
            section.querySelector('.zip_code').value = '';
            section.querySelector('.country').value = '';

            // Fill fields
            let streetNumber = '';
            let route = '';

            for (const comp of place.address_components) {
                const type = comp.types[0];

                switch (type) {
                    case 'street_number':
                        streetNumber = comp.long_name;
                        break;
                    case 'route':
                        route = comp.long_name;
                        break;
                    case 'locality':
                        section.querySelector('.city').value = comp.long_name;
                        break;
                    case 'administrative_area_level_1':
                        section.querySelector('.state').value = comp.long_name;
                        break;
                    case 'postal_code':
                        section.querySelector('.zip_code').value = comp.long_name;
                        break;
                    case 'country':
                        section.querySelector('.country').value = comp.long_name;
                        break;
                }
            }

            // Combine street number + route
            section.querySelector('.address').value = `${streetNumber} ${route}`.trim();

            console.log('Selected address:', place.formatted_address);
        });
}

function initAutocomplete() {
    // Initialize autocomplete for all existing sections
    document.querySelectorAll('.address-section').forEach(section => {
        initializeAddressSection(section);
    });
}

// Make the initAutocomplete available globally for callback
window.initAutocomplete = initAutocomplete;

</script>

<!-- Load Google Maps API async (best practice) -->
<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA9TcalC1Y0fdR_BJSRh6wvmZL4dbOMoeo&libraries=places&region=AU&callback=initAutocomplete"
    async
    defer>
</script>
@endpush