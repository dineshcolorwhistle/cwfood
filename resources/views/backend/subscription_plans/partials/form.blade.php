<div class="form-group">
    <label class="text-primary-orange" for="plan_code">Plan Code <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('plan_code') is-invalid @enderror" id="plan_code" name="plan_code"
        value="{{ old('plan_code') }}" required maxlength="255">
    @error('plan_code')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label class="text-primary-orange" for="subscription_name">Subscription Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('subscription_name') is-invalid @enderror" id="subscription_name" name="subscription_name"
        value="{{ old('subscription_name') }}" required maxlength="255">
    @error('subscription_name')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label class="text-primary-orange" for="ideal_for">Ideal For</label>
    <input type="text" class="form-control @error('ideal_for') is-invalid @enderror" id="ideal_for" name="ideal_for"
        value="{{ old('ideal_for') }}">
    @error('ideal_for')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group d-flex gap-3">
    <div class="w-50">
        <label class="text-primary-orange" for="monthly_cost_per_user">Monthly Cost Per User <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('monthly_cost_per_user') is-invalid @enderror" id="monthly_cost_per_user" name="monthly_cost_per_user"
            value="{{ old('monthly_cost_per_user') }}" required min="0" step="0.01">
        @error('monthly_cost_per_user')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="w-50">
        <label class="text-primary-orange" for="annual_cost_per_user">Annual Cost Per User <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('annual_cost_per_user') is-invalid @enderror" id="annual_cost_per_user" name="annual_cost_per_user"
            value="{{ old('annual_cost_per_user') }}" required min="0" step="0.01">
        @error('annual_cost_per_user')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group d-flex gap-3">
    <div class="w-50">
        <label class="text-primary-orange" for="min_users">Min Users <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('min_users') is-invalid @enderror" id="min_users" name="min_users"
            value="{{ old('min_users') }}" required min="1">
        @error('min_users')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="w-50">
        <label class="text-primary-orange" for="max_users">Max Users <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('max_users') is-invalid @enderror" id="max_users" name="max_users"
            value="{{ old('max_users') }}" required min="1">
        @error('max_users')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group d-flex gap-3">
    <div class="w-50">
        <label class="text-primary-orange" for="max_raw_materials">Max Raw Materials <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('max_raw_materials') is-invalid @enderror" id="max_raw_materials" name="max_raw_materials"
            value="{{ old('max_raw_materials') }}" required min="1">
        @error('max_raw_materials')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="w-50">
        <label class="text-primary-orange" for="max_skus">Max SKUs <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('max_skus') is-invalid @enderror" id="max_skus" name="max_skus"
            value="{{ old('max_skus') }}" required min="1">
        @error('max_skus')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group d-flex gap-3">
    <div class="w-50">
        <label class="text-primary-orange" for="max_work_spaces">Max Workspaces <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('max_work_spaces') is-invalid @enderror" id="max_work_spaces" name="max_work_spaces"
            value="{{ old('max_work_spaces') }}" required min="1">
        @error('max_work_spaces')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <button type="button" class="btn btn-secondary-white me-3" data-bs-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-secondary-blue">{{ $buttonText }}</button>
</div>