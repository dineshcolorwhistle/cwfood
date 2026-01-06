{{-- Create Specification from FSANZ Food Modal --}}
<div class="modal fade" id="createSpecificationModal" tabindex="-1" aria-labelledby="createSpecificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="createSpecificationModalLabel">Create Specification from FSANZ Food</h5>
                    <p class="text-muted small mb-0">
                        Create a new raw material specification using data from the FSANZ Food Database.
                        All mandatory fields will be automatically populated.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <form id="createSpecificationForm">
                    @csrf
                    <input type="hidden" name="fsanz_food_id" id="fsanz_food_id" value="">
                    
                    {{-- Food Preview Section --}}
                    <div class="bg-light border rounded p-3 mb-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-semibold" id="preview-food-name">-</h6>
                                <p class="text-muted small mb-0" id="preview-food-description"></p>
                            </div>
                            <span class="badge bg-secondary ms-2" id="preview-fsanz-key">-</span>
                        </div>
                    </div>
                    
                    {{-- Validation Errors Alert (initially hidden) --}}
                    <div class="alert alert-danger d-none" id="validation-errors">
                        <h6 class="alert-heading"><i class="bi bi-exclamation-circle-fill me-2"></i>Cannot Create Specification</h6>
                        <ul class="mb-0" id="error-list"></ul>
                    </div>
                    
                    {{-- Validation Warnings Alert (initially hidden) --}}
                    <div class="alert alert-warning d-none" id="validation-warnings">
                        <h6 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Data Quality Warnings</h6>
                        <ul class="mb-0" id="warning-list"></ul>
                    </div>

                    {{-- Category 1: FSANZ Official Data --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <h6 class="mb-0 fw-semibold">Category 1: FSANZ Official Data</h6>
                            <span class="badge bg-primary ms-auto">Mandatory</span>
                        </div>
                        <div class="ps-4 text-muted small">
                            <p class="mb-1">✓ Verified nutritional values (energy, protein, fats, carbs, sodium)</p>
                            <p class="mb-1">✓ Physical properties (specific gravity)</p>
                            <p class="mb-0">✓ Measurement basis (<span id="preview-measurement-basis">per 100g</span>)</p>
                        </div>
                    </div>

                    {{-- Category 2: AI - Compliance Critical --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-check-circle-fill text-primary fs-5"></i>
                            <h6 class="mb-0 fw-semibold">Category 2: AI - Compliance Critical</h6>
                            <span class="badge bg-primary ms-auto">Mandatory</span>
                        </div>
                        <div class="ps-4 text-muted small">
                            <p class="mb-1">✓ Estimated ingredients list</p>
                            <p class="mb-1">✓ Allergen information (contains/may contain)</p>
                            <p class="mb-1">✓ Country of origin data</p>
                            <p class="mb-0">✓ Dietary certifications (vegan, vegetarian, halal, kosher, etc.)</p>
                        </div>
                        <div class="alert alert-info mt-3 mb-0 ms-4 py-2">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>AI-estimated compliance data should be verified by a qualified food technologist before use in production.</small>
                        </div>
                    </div>

                    {{-- Category 3: AI - Supplementary (Optional) --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="form-check-checkbox">
                                <input class="form-check-input" type="checkbox" id="include_supplementary" name="include_supplementary" value="1">
                                <label class="form-check-label fw-semibold" for="include_supplementary">
                                    Category 3: AI - Supplementary Information
                                </label>
                            </div>
                            <span class="badge bg-secondary ms-auto">Optional</span>
                        </div>
                        <div class="ps-4 text-muted small">
                            <p class="mb-1">• Food safety hazards (biological, chemical, physical)</p>
                            <p class="mb-1">• Processing characteristics</p>
                            <p class="mb-1">• Regulatory compliance information</p>
                            <p class="mb-0">• Typical manufacturing uses</p>
                        </div>
                    </div>

                    {{-- Category 4: Classification (Optional) --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="form-check-checkbox">
                                <input class="form-check-input" type="checkbox" id="include_classification" name="include_classification" value="1" checked>
                                <label class="form-check-label fw-semibold" for="include_classification">
                                    Category 4: Codex Classification
                                </label>
                            </div>
                            <span class="badge bg-secondary ms-auto">Optional</span>
                        </div>
                        <div class="ps-4 text-muted small">
                            <p class="mb-1">• Codex food category code and name</p>
                            <p class="mb-1">• Food group and subgroup</p>
                            <p class="mb-1">• Functional category (emulsifier, stabilizer, etc.)</p>
                            <p class="mb-0">• Product type flags (raw ingredient, additive, processing aid)</p>
                        </div>
                    </div>

                    {{-- Result Preview --}}
                    <div class="bg-light border rounded p-3">
                        <p class="fw-medium mb-2">Specification will be created with:</p>
                        <ul class="list-unstyled text-muted small mb-0">
                            <li class="mb-1">• <strong>Name:</strong> <span id="result-name">-</span></li>
                            <li class="mb-1">• <strong>SKU:</strong> <span id="result-sku">-</span></li>
                            <li class="mb-1">• <strong>Type:</strong> Raw Material</li>
                            <li class="mb-1">• <strong>Status:</strong> Pending Review</li>
                            <li class="mb-0">• <strong>FSANZ Source:</strong> Linked for audit trail</li>
                        </ul>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="cancelBtn">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="createSpecificationBtn">
                    <span class="spinner-border spinner-border-sm d-none me-2" id="loadingSpinner" role="status"></span>
                    Create Specification
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body py-4">
                <i class="bi bi-check-circle-fill text-success fs-1 mb-3 d-block"></i>
                <h5>Specification Created!</h5>
                <p class="text-muted small mb-3" id="success-spec-name">-</p>
                <a href="#" id="viewSpecificationLink" class="btn btn-primary">
                    View Specification
                </a>
            </div>
        </div>
    </div>
</div>
