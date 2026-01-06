{{-- resources/views/components/raw-materials/confirm-modal.blade.php --}}

<div class="modal fade" id="rawMaterialConfirmModal" tabindex="-1" aria-labelledby="rawMaterialConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rawMaterialConfirmModalLabel">Confirm Raw Material Creation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Review the data from specification before creating</p>

                {{-- Basic Information --}}
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">Basic Information</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <span class="text-muted">Name:</span>
                            <span class="fw-medium" id="confirm-name"></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted">Code:</span>
                            <span class="fw-medium" id="confirm-code"></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted">Supplier:</span>
                            <span class="fw-medium" id="confirm-supplier"></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted">Data Source:</span>
                            <span class="fw-medium" id="confirm-source"></span>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Nutritional Information Panel --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <h6 class="fw-semibold mb-0">Nutritional Information (per 100g/100ml)</h6>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="bi bi-check-circle me-1"></i> Verified
                        </span>
                    </div>
                    
                    <div id="nip-data-container" class="bg-light p-3 rounded">
                        <div class="row g-3">
                            <div class="col-4 col-md-2">
                                <small class="text-muted d-block">Energy</small>
                                <span class="fw-medium" id="nip-energy">-</span> kJ
                            </div>
                            <div class="col-4 col-md-2">
                                <small class="text-muted d-block">Protein</small>
                                <span class="fw-medium" id="nip-protein">-</span> g
                            </div>
                            <div class="col-4 col-md-2">
                                <small class="text-muted d-block">Fat (Total)</small>
                                <span class="fw-medium" id="nip-fat-total">-</span> g
                            </div>
                            <div class="col-4 col-md-2">
                                <small class="text-muted d-block">Fat (Sat)</small>
                                <span class="fw-medium" id="nip-fat-sat">-</span> g
                            </div>
                            <div class="col-4 col-md-2">
                                <small class="text-muted d-block">Carbs</small>
                                <span class="fw-medium" id="nip-carbs">-</span> g
                            </div>
                            <div class="col-4 col-md-2">
                                <small class="text-muted d-block">Sugars</small>
                                <span class="fw-medium" id="nip-sugars">-</span> g
                            </div>
                            <div class="col-4 col-md-2">
                                <small class="text-muted d-block">Sodium</small>
                                <span class="fw-medium" id="nip-sodium">-</span> mg
                            </div>
                        </div>
                    </div>

                    <div id="nip-no-data" class="alert alert-warning d-none">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        No nutritional data available from source
                    </div>
                </div>

                <hr>

                {{-- Ingredients --}}
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">Ingredients</h6>
                    <div id="ingredients-container">
                        <div id="ingredients-list" class="d-flex flex-wrap gap-2"></div>
                        <div id="ingredients-no-data" class="alert alert-warning d-none">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            No ingredient information available
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Allergens --}}
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">Allergen Information</h6>
                    <div id="allergens-container">
                        <div id="allergens-contains" class="mb-3">
                            <small class="text-muted d-block mb-2">Contains:</small>
                            <div id="allergens-contains-list" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        <div id="allergens-may-contain" class="mb-3">
                            <small class="text-muted d-block mb-2">May Contain:</small>
                            <div id="allergens-may-contain-list" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        <div id="allergens-statement" class="bg-light p-3 rounded">
                            <small class="text-muted d-block mb-1">Allergen Statement:</small>
                            <em id="allergens-statement-text"></em>
                        </div>
                        <div id="allergens-no-data" class="alert alert-warning d-none">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            No allergen information available
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Dietary & Religious Claims --}}
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">Dietary & Religious Claims</h6>
                    <div id="dietary-claims-list" class="d-flex flex-wrap gap-2"></div>
                </div>

                <hr>

                {{-- Specific Gravity Override (for liquids) --}}
                <div class="mb-4" id="specific-gravity-section" style="display: none;">
                    <h6 class="fw-semibold mb-3">Specific Gravity</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="sg-override" class="form-label">
                                Override Value <small class="text-muted" id="sg-source-value"></small>
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="sg-override" 
                                       step="0.001" placeholder="Leave empty to use source value">                                
                            </div>
                            <small class="text-muted">Leave empty to use source data, or enter a value to override</small>
                        </div>
                    </div>
                </div>

                {{-- AI Data Warning (if from FSANZ) --}}
                <div id="ai-data-warning" class="alert alert-warning d-none">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>AI-Estimated Data:</strong> Ingredient, allergen, and dietary claim information is AI-generated and requires expert verification before use in production or compliance.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="backToDetails()">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="confirm-create-btn">
                    <span class="spinner-border spinner-border-sm me-1 d-none" id="create-spinner"></span>
                    <span id="confirm-create-text">Confirm & Create</span>
                </button>
            </div>
        </div>
    </div>
</div>
