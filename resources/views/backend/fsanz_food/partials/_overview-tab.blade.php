<div class="row g-4">
    {{-- Basic Information Card --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-muted">FSANZ Key</dt>
                            <dd class="col-sm-8">{{ $food->fsanz_key }}</dd>
                            
                            <dt class="col-sm-4 text-muted">Name</dt>
                            <dd class="col-sm-8">{{ $food->name }}</dd>
                            
                            @if($food->description)
                                <dt class="col-sm-4 text-muted">Description</dt>
                                <dd class="col-sm-8">{{ $food->description }}</dd>
                            @endif
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            @if($food->measurement_basis)
                                <dt class="col-sm-4 text-muted">Measurement Basis</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-light text-dark border">{{ $food->measurement_basis }}</span>
                                </dd>
                            @endif
                            
                            @if($food->data_source)
                                <dt class="col-sm-4 text-muted">Data Source</dt>
                                <dd class="col-sm-8">{{ $food->data_source }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Official Nutritional Data Card --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <h5 class="card-title mb-0">Official FSANZ Nutritional Data</h5>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                    <i class="bi bi-check-circle me-1"></i>Verified FSANZ Data
                </span>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        @if($food->energy_kj !== null)
                            <tr>
                                <td class="text-muted" style="width: 140px;">Energy</td>
                                <td class="fw-medium">{{ number_format($food->energy_kj, 1) }} kJ</td>
                            </tr>
                        @endif
                        @if($food->protein_g !== null)
                            <tr>
                                <td class="text-muted">Protein</td>
                                <td class="fw-medium">{{ number_format($food->protein_g, 1) }}g</td>
                            </tr>
                        @endif
                        @if($food->fat_total_g !== null)
                            <tr>
                                <td class="text-muted">Total Fat</td>
                                <td class="fw-medium">{{ number_format($food->fat_total_g, 1) }}g</td>
                            </tr>
                        @endif
                        @if($food->fat_saturated_g !== null)
                            <tr>
                                <td class="text-muted">Saturated Fat</td>
                                <td class="fw-medium">{{ number_format($food->fat_saturated_g, 1) }}g</td>
                            </tr>
                        @endif
                        @if($food->carbohydrate_g !== null)
                            <tr>
                                <td class="text-muted">Carbohydrate</td>
                                <td class="fw-medium">{{ number_format($food->carbohydrate_g, 1) }}g</td>
                            </tr>
                        @endif
                        @if($food->sugars_g !== null)
                            <tr>
                                <td class="text-muted">Sugars</td>
                                <td class="fw-medium">{{ number_format($food->sugars_g, 1) }}g</td>
                            </tr>
                        @endif
                        @if($food->sodium_mg !== null)
                            <tr>
                                <td class="text-muted">Sodium</td>
                                <td class="fw-medium">{{ number_format($food->sodium_mg, 1) }}mg</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Food Classification Card --}}
    <div class="col-lg-6">
        <div class="card h-100 bg-light bg-opacity-50">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Food Classification
                    <small class="text-muted fw-normal">*</small>
                </h5>
            </div>
            <div class="card-body">
                <dl class="row mb-3">
                    @if($food->food_group)
                        <dt class="col-sm-4 text-muted">Food Group</dt>
                        <dd class="col-sm-8">{{ $food->food_group }}</dd>
                    @endif
                    
                    @if($food->food_subgroup)
                        <dt class="col-sm-4 text-muted">Food Subgroup</dt>
                        <dd class="col-sm-8">{{ $food->food_subgroup }}</dd>
                    @endif
                    
                    @if($food->food_category_code)
                        <dt class="col-sm-4 text-muted">Category Code</dt>
                        <dd class="col-sm-8">{{ $food->food_category_code }}</dd>
                    @endif
                    
                    @if($food->food_category_name)
                        <dt class="col-sm-4 text-muted">Category Name</dt>
                        <dd class="col-sm-8">{{ $food->food_category_name }}</dd>
                    @endif
                </dl>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if($food->is_raw_ingredient)
                        <span class="badge bg-primary">Raw Ingredient</span>
                    @endif
                    @if($food->is_additive)
                        <span class="badge bg-primary">Additive</span>
                    @endif
                    @if($food->is_processing_aid)
                        <span class="badge bg-primary">Processing Aid</span>
                    @endif
                </div>

                <hr>
                <p class="text-muted small mb-0">* AI-generated classification</p>
            </div>
        </div>
    </div>
</div>
