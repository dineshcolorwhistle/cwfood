{{-- resources/views/components/raw-materials/specification-search-modal.blade.php --}}

<div class="modal fade" id="specificationSearchModal" tabindex="-1" aria-labelledby="specificationSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="specificationSearchModalLabel">Search Specifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Info Box --}}
                <div class="alert alert-info d-flex align-items-start gap-2 mb-3">
                    <i class="bi bi-file-text fs-5"></i>
                    <div>
                        <strong>Supplier-Specific Data</strong>
                        <p class="mb-0 small">Specifications contain detailed nutritional information, allergens, and compliance data from your suppliers</p>
                    </div>
                </div>

                {{-- Needs Approval Warning --}}
                <div id="needs-approval-warning" class="alert alert-warning small py-2 d-none">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <span id="needs-approval-count">0</span> specification(s) need approval before use
                </div>

                {{-- Search Input --}}
                <div class="position-relative mb-3">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" 
                           id="spec-search-input" 
                           class="form-control ps-5" 
                           placeholder="Search by name, SKU, or supplier...">
                </div>

                {{-- Results Count --}}
                <div id="spec-results-count" class="small text-muted mb-2 d-none">
                    Showing <span id="spec-count">0</span> specification(s)
                </div>

                {{-- Results Container --}}
                <div id="spec-search-results" class="border rounded" style="max-height: 400px; overflow-y: auto;">
                    {{-- Loading State --}}
                    <div id="spec-loading" class="d-flex justify-content-center align-items-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    {{-- Empty State --}}
                    <div id="spec-empty" class="text-center py-5 d-none">
                        <i class="bi bi-search fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Start typing to search specifications</p>
                    </div>

                    {{-- No Results --}}
                    <div id="spec-no-results" class="text-center py-5 d-none">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No specifications found matching your search</p>
                    </div>

                    {{-- Results List --}}
                    <div id="spec-list" class="p-3"></div>
                </div>

                {{-- Data Source Info --}}
                <div class="small text-muted mt-3">
                    <strong>Data Source:</strong> Your Specification Library<br>
                    Specifications contain supplier-provided data that has been validated and stored in your system.
                </div>

                {{-- Escape Hatch --}}
                <!-- <div class="border-top pt-3 mt-3 text-center">
                    <p class="small text-muted mb-2">Can't find a spec sheet?</p>
                    <button type="button" class="btn btn-outline-primary w-100" onclick="navigateToAddSpecification()">
                        <i class="bi bi-plus-lg me-1"></i> Add New Specification
                    </button>
                </div> -->
            </div>
        </div>
    </div>
</div>

{{-- Approval Required Alert Modal --}}
<div class="modal fade" id="approvalRequiredModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Specification Not Approved
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="fw-medium" id="approval-spec-name"></p>
                <div class="mb-3">
                    <span>Status:</span>
                    <span id="approval-spec-status" class="badge ms-2"></span>
                </div>
                <p class="text-muted">This specification must be approved before it can be linked to raw materials.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Choose Different</button>
                <button type="button" class="btn btn-primary" id="review-approve-btn">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Review & Approve
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.spec-item {
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    transition: background-color 0.15s;
}
.spec-item.approved:hover {
    background-color: #f3f4f6;
    cursor: pointer;
}
.spec-item.not-approved {
    opacity: 0.7;
    cursor: not-allowed;
}
.spec-item .nutritional-info {
    font-size: 0.75rem;
    color: #6b7280;
}
</style>
