{{-- resources/views/components/raw-materials/details-modal.blade.php --}}

<div class="modal fade" id="rawMaterialDetailsModal" tabindex="-1" aria-labelledby="rawMaterialDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rawMaterialDetailsModalLabel">Add Raw Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="raw-material-form">
                    {{-- Data Source Banner --}}
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-4">
                        <div>
                            <p class="mb-0 fw-medium small">Data Source: Specification</p>
                            <p class="mb-0 text-muted small" id="selected-spec-name"></p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeDataSource()">
                            Change Source
                        </button>
                    </div>

                    {{-- Hidden Fields --}}
                    <input type="hidden" id="specification_id" name="specification_id">

                    {{-- Basic Information --}}
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       placeholder="e.g., Organic Wheat Flour" required>
                            </div>
                            <div class="col-md-6">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" 
                                       placeholder="Auto-generated (e.g., RM-001)" required>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="2" placeholder="Additional details..."></textarea>
                            </div>
                            <div class="col-12">
                                <label for="supplier" class="form-label">Supplier</label>
                                <input type="text" class="form-control" id="supplier" name="supplier" 
                                       placeholder="e.g., ABC Supplies Ltd">
                            </div>
                        </div>
                    </div>

                    {{-- Pricing Section --}}
                    <div class="mb-4 p-3 bg-light rounded">
                        <h6 class="fw-semibold mb-3">Pricing</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="purchase_price_ex_gst" class="form-label">
                                    Purchase Price (ex GST) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text fs-4">$</span>
                                    <input type="number" class="form-control" id="purchase_price_ex_gst" 
                                           name="purchase_price_ex_gst" step="0.01" required>
                                    <span class="input-group-text">per</span>
                                    <select class="form-select" id="pricing_unit" name="pricing_unit" style="max-width: 120px;">
                                        <option value="kg">kg</option>
                                        <option value="g">g</option>
                                        <option value="L">L</option>
                                        <option value="mL">mL</option>
                                        <!-- <option value="units">units</option>
                                        <option value="each">each</option>
                                        <option value="dozen">dozen</option>
                                        <option value="pair">pair</option> -->
                                    </select>
                                </div>
                            </div>

                            {{-- Count Unit Fields (shown conditionally) --}}
                            <div class="col-md-6 count-unit-field d-none">
                                <label for="custom_unit_name" class="form-label">Custom Unit Name (Optional)</label>
                                <input type="text" class="form-control" id="custom_unit_name" name="custom_unit_name" 
                                       placeholder="e.g., eggs, slices, cans" maxlength="50">
                            </div>
                            <div class="col-md-6 count-unit-field d-none">
                                <label for="unit_conversion_factor" class="form-label">
                                    Weight per Unit <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="unit_conversion_factor" 
                                           name="unit_conversion_factor" step="0.01" placeholder="e.g., 60">
                                    <select class="form-select" id="unit_conversion_to" name="unit_conversion_to" style="max-width: 80px;">
                                        <option value="g">g</option>
                                        <option value="kg">kg</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Volume Unit Fields (shown conditionally) --}}
                            <div class="col-12 volume-unit-field d-none">
                                <label for="specific_gravity" class="form-label">
                                    Specific Gravity <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="specific_gravity" name="specific_gravity" 
                                       step="0.001" placeholder="e.g., 1.0 for water">
                                <small class="text-muted">Required for volume-based pricing to calculate cost per kg</small>
                            </div>

                            {{-- GST Toggle --}}
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="gst_applicable" 
                                           name="gst_applicable" checked>
                                    <label class="form-check-label" for="gst_applicable">GST Applicable (10%)</label>
                                </div>
                            </div>

                            {{-- Cost Per Kg Display --}}
                            <div class="col-12" id="cost-per-kg-display" style="display: none;">
                                <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-0">
                                    <i class="bi bi-calculator"></i>
                                    <div>
                                        <strong>Cost per kg:</strong> <span id="calculated-cost-per-kg">$0.00</span>
                                        <small class="d-block text-muted" id="cost-calculation-info"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Ordering Section --}}
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">Ordering</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="minimum_order_quantity" class="form-label">Minimum Order Quantity <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="minimum_order_quantity" 
                                           name="minimum_order_quantity" step="0.01" required>
                                    <!-- <select class="form-select" id="minimum_order_quantity_unit" 
                                            name="minimum_order_quantity_unit" style="max-width: 80px;">
                                        <option value="kg">kg</option>
                                        <option value="g">g</option>
                                        <option value="L">L</option>
                                        <option value="units">units</option>
                                    </select> -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="lead_time_days" class="form-label">Lead Time</label>
                                <div class="position-relative days-wrapper">
                                    <input type="number" class="form-control pe-5" id="lead_time_days" name="lead_time_days" min="0">
                                    <span class="days-label">days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="continue-to-confirm-btn">
                    Continue to Review
                    <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>
