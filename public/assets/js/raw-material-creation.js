// public/js/raw-material-creation.js

class RawMaterialCreationFlow {
    constructor() {
        this.selectedSpecification = null;
        this.formData = {};
        this.searchTimeout = null;
        
        this.searchModal = new bootstrap.Modal(document.getElementById('specificationSearchModal'));
        this.detailsModal = new bootstrap.Modal(document.getElementById('rawMaterialDetailsModal'));
        this.confirmModal = new bootstrap.Modal(document.getElementById('rawMaterialConfirmModal'));
        this.approvalModal = new bootstrap.Modal(document.getElementById('approvalRequiredModal'));
        
        this.init();
    }

    init() {
        // Search input debounce
        const searchInput = document.getElementById('spec-search-input');
        searchInput?.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => this.searchSpecifications(e.target.value), 300);
        });

        // Pricing unit change handler
        document.getElementById('pricing_unit')?.addEventListener('change', (e) => {
            this.handlePricingUnitChange(e.target.value);
        });

        // Price input change for cost calculation
        ['purchase_price_ex_gst', 'unit_conversion_factor', 'specific_gravity'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', () => this.calculateCostPerKg());
        });

        // Continue to confirm button
        document.getElementById('continue-to-confirm-btn')?.addEventListener('click', () => {
            if (this.validateDetailsForm()) {
                this.openConfirmModal();
            }
        });

        // Confirm create button
        document.getElementById('confirm-create-btn')?.addEventListener('click', () => {
            this.createRawMaterial();
        });

        // SG override input
        document.getElementById('sg-override')?.addEventListener('input', (e) => {
            const badge = document.getElementById('sg-badge');
            badge.textContent = e.target.value ? 'Using override' : 'Using source value';
            badge.className = e.target.value ? 'input-group-text bg-info text-white' : 'input-group-text';
        });
    }

    // Step 1: Open Specification Search
    open() {
        this.resetAll();
        this.searchModal.show();
        this.loadSpecifications();
    }

    async loadSpecifications() {
        this.showSearchLoading();
        
        try {
            const response = await fetch(`/specifications/search`);
            const data = await response.json();
            this.renderSpecifications(data.specifications || []);
        } catch (error) {
            console.error('Error loading specifications:', error);
            this.showSearchError();
        }
    }

    async searchSpecifications(term) {
        if (!term) {
            this.loadSpecifications();
            return;
        }

        this.showSearchLoading();

        try {
            const response = await fetch(`/specifications/search?search=${encodeURIComponent(term)}`);
            const data = await response.json();
            this.renderSpecifications(data.specifications || []);
            
            document.getElementById('spec-results-count').classList.remove('d-none');
            document.getElementById('spec-count').textContent = data.specifications?.length || 0;
        } catch (error) {
            console.error('Error searching specifications:', error);
        }
    }

    renderSpecifications(specifications) {
        const listContainer = document.getElementById('spec-list');
        const loadingEl = document.getElementById('spec-loading');
        const emptyEl = document.getElementById('spec-empty');
        const noResultsEl = document.getElementById('spec-no-results');

        loadingEl.classList.add('d-none');
        emptyEl.classList.add('d-none');
        noResultsEl.classList.add('d-none');

        if (specifications.length === 0) {
            const searchTerm = document.getElementById('spec-search-input').value;
            if (searchTerm) {
                noResultsEl.classList.remove('d-none');
            } else {
                emptyEl.classList.remove('d-none');
            }
            listContainer.innerHTML = '';
            return;
        }

        // Count unapproved
        const needsApproval = specifications.filter(s => s.spec_status !== 'approved').length;
        const warningEl = document.getElementById('needs-approval-warning');
        if (needsApproval > 0) {
            warningEl.classList.remove('d-none');
            document.getElementById('needs-approval-count').textContent = needsApproval;
        } else {
            warningEl.classList.add('d-none');
        }

        listContainer.innerHTML = specifications.map(spec => this.renderSpecificationItem(spec)).join('');
    }

    renderSpecificationItem(spec) {
        const isApproved = spec.spec_status === 'approved';
        const statusClass = this.getStatusClass(spec.spec_status);
        
        return `
            <div class="spec-item ${isApproved ? 'approved' : 'not-approved'}" 
                 data-spec-id="${spec.id}" 
                 data-approved="${isApproved}"
                 onclick="rawMaterialFlow.selectSpecification(${JSON.stringify(spec).replace(/"/g, '&quot;')})">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                            <span class="fw-bold" style="color:var(--primary-color);">${spec.spec_name}</span>
                            <span class="badge ${statusClass}">${spec.spec_status}</span>
                            ${spec.spec_sku ? `<span class="badge bg-light text-dark border">${spec.spec_sku}</span>` : ''}
                            ${spec.supplier_name ? `<span class="badge bg-secondary">${spec.supplier_name}</span>` : ''}
                        </div>
                        ${spec.description ? `<p class="text-muted small mb-2 text-truncate" style="max-width: 650px;">${spec.description}</p>` : ''}
                        <div class="nutritional-info d-flex gap-3">
                            ${spec.nutr_energy_kj != null ? `<span>Energy: ${spec.nutr_energy_kj}kJ</span>` : ''}
                            ${spec.nutr_protein_g != null ? `<span>Protein: ${spec.nutr_protein_g}g</span>` : ''}
                            ${spec.nutr_fat_total_g != null ? `<span>Fat: ${spec.nutr_fat_total_g}g</span>` : ''}
                            ${spec.nutr_carbohydrate_g != null ? `<span>Carbs: ${spec.nutr_carbohydrate_g}g</span>` : ''}
                        </div>
                    </div>
                    <button class="btn btn-sm btn-ghost ${!isApproved ? 'opacity-50' : ''}">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        `;
    }

    selectSpecification(spec) {
        const isApproved = spec.spec_status === 'approved';
        
        if (!isApproved) {
            // Show approval required modal
            document.getElementById('approval-spec-name').textContent = spec.spec_name;
            document.getElementById('approval-spec-status').textContent = spec.spec_status;
            document.getElementById('approval-spec-status').className = `badge ${this.getStatusClass(spec.spec_status)}`;
            document.getElementById('review-approve-btn').onclick = () => {
                window.location.href = `/specifications/edit/${spec.id}`;
            };
            this.approvalModal.show();
            return;
        }

        // Fetch full specification data
        this.fetchAndSelectSpec(spec.id);
    }

    async fetchAndSelectSpec(specId) {
        try {
            const response = await fetch(`/specifications/fsanz/${specId}`);
            const spec = await response.json();
            
            this.selectedSpecification = spec.specification;
            this.searchModal.hide();
            this.openDetailsModal(spec);
        } catch (error) {
            console.error('Error fetching specification:', error);
            this.showToast('Error loading specification', 'danger');
        }
    }

    // Step 2: Details Modal
    openDetailsModal(spec) {
        
        
        // Populate form with spec data
        document.getElementById('specification_id').value = spec.specification.id;
        document.getElementById('selected-spec-name').textContent = spec.specification.spec_name;
        document.getElementById('name').value = spec.specification.spec_name || '';
        document.getElementById('description').value = spec.specification.description|| '';
        document.getElementById('supplier').value = spec.specification.supplier_name || '';
        document.getElementById('specific_gravity').value = spec.specification.phys_specific_gravity || '';
        
        // Auto-generate code
        this.generateCode();
        
        this.detailsModal.show();
    }

    async generateCode() {
        document.getElementById('code').value = 'FSANZ-001';
        // try {
        //     const response = await fetch(`/api/raw-materials/next-code?workspace_id=${WORKSPACE_ID}`);
        //     const data = await response.json();
        //     document.getElementById('code').value = data.code || 'RM-001';
        // } catch (error) {
        //     document.getElementById('code').value = 'RM-001';
        // }
    }

    handlePricingUnitChange(unit) {
        const countUnits = ['units', 'each', 'dozen', 'pair'];
        const volumeUnits = ['L', 'mL'];
        
        // Show/hide count unit fields
        document.querySelectorAll('.count-unit-field').forEach(el => {
            el.classList.toggle('d-none', !countUnits.includes(unit));
        });
        
        // Show/hide volume unit fields
        const sgField = document.querySelector('.volume-unit-field');
        const specSg = this.selectedSpecification?.phys_specific_gravity;
        sgField?.classList.toggle('d-none', !volumeUnits.includes(unit) || specSg);
        
        this.calculateCostPerKg();
    }

    calculateCostPerKg() {
        const price = parseFloat(document.getElementById('purchase_price_ex_gst').value);
        const unit = document.getElementById('pricing_unit').value;
        
        if (!price || !unit) {
            document.getElementById('cost-per-kg-display').style.display = 'none';
            return;
        }

        let costPerKg = null;
        let info = '';

        if (unit === 'kg') {
            costPerKg = price;
            info = 'Direct pricing in kg';
        } else if (unit === 'g') {
            costPerKg = price * 1000;
            info = 'Converted from g to kg (×1000)';
        } else if (['L', 'mL'].includes(unit)) {
            const sg = parseFloat(document.getElementById('specific_gravity').value) || 
                       this.selectedSpecification?.phys_specific_gravity;
            if (sg) {
                const pricePerL = unit === 'mL' ? price * 1000 : price;
                costPerKg = pricePerL / sg;
                info = `$${pricePerL.toFixed(2)}/L ÷ ${sg} SG = $${costPerKg.toFixed(2)}/kg`;
            }
        } else if (['units', 'each', 'dozen', 'pair'].includes(unit)) {
            const factor = parseFloat(document.getElementById('unit_conversion_factor').value);
            const toUnit = document.getElementById('unit_conversion_to').value;
            if (factor) {
                let unitsInBatch = unit === 'dozen' ? 12 : unit === 'pair' ? 2 : 1;
                const pricePerUnit = price / unitsInBatch;
                const gramsPerUnit = toUnit === 'kg' ? factor * 1000 : factor;
                costPerKg = (pricePerUnit / gramsPerUnit) * 1000;
                info = `$${pricePerUnit.toFixed(2)}/unit ÷ ${gramsPerUnit}g/unit × 1000`;
            }
        }

        if (costPerKg !== null) {
            document.getElementById('calculated-cost-per-kg').textContent = `$${costPerKg.toFixed(2)}`;
            document.getElementById('cost-calculation-info').textContent = info;
            document.getElementById('cost-per-kg-display').style.display = 'block';
        } else {
            document.getElementById('cost-per-kg-display').style.display = 'none';
        }
    }

    validateDetailsForm() {
        const name = document.getElementById('name').value;
        const code = document.getElementById('code').value;
        const price = document.getElementById('purchase_price_ex_gst').value;
        const unit = document.getElementById('pricing_unit').value;
        const minimumOrderQuantity = document.getElementById('minimum_order_quantity').value;

        if (!name || !code || !price || !minimumOrderQuantity) {
            this.showToast('Please fill in name, code, price, and minimum order quantity', 'warning');
            return false;
        }

        if (['units', 'each', 'dozen', 'pair'].includes(unit)) {
            const factor = document.getElementById('unit_conversion_factor').value;
            if (!factor) {
                this.showToast('Count units require weight/volume per unit', 'warning');
                return false;
            }
        }

        if (['L', 'mL'].includes(unit)) {
            const sg = document.getElementById('specific_gravity').value || 
                       this.selectedSpecification?.phys_specific_gravity;
            if (!sg) {
                this.showToast('Volume units require specific gravity', 'warning');
                return false;
            }
        }

        // Collect form data
        this.formData = {
            name: name,
            code: code,
            description: document.getElementById('description').value,
            supplier: document.getElementById('supplier').value,
            specification_id: document.getElementById('specification_id').value,
            pricing_unit: unit,
            purchase_price_ex_gst: price,
            gst_applicable: document.getElementById('gst_applicable').checked,
            minimum_order_quantity: document.getElementById('minimum_order_quantity').value,
            minimum_order_quantity_unit: 'kg',
            lead_time_days: document.getElementById('lead_time_days').value,
            unit_conversion_factor: document.getElementById('unit_conversion_factor').value,
            unit_conversion_to: document.getElementById('unit_conversion_to').value,
            specific_gravity: document.getElementById('specific_gravity').value,
            custom_unit_name: document.getElementById('custom_unit_name').value,
        };

        return true;
    }

    // Step 3: Confirmation Modal
    openConfirmModal() {
        this.detailsModal.hide();
        
        const spec = this.selectedSpecification;

        // Basic Info
        document.getElementById('confirm-name').textContent = this.formData.name;
        document.getElementById('confirm-code').textContent = this.formData.code;
        document.getElementById('confirm-supplier').textContent = this.formData.supplier || '-';
        document.getElementById('confirm-source').textContent = spec.spec_name;

        // NIP Data
        const hasNIP = spec.nutr_energy_kj != null || spec.nutr_protein_g != null;
        if (hasNIP) {
            document.getElementById('nip-data-container').classList.remove('d-none');
            document.getElementById('nip-no-data').classList.add('d-none');
            document.getElementById('nip-energy').textContent = spec.nutr_energy_kj ?? '-';
            document.getElementById('nip-protein').textContent = spec.nutr_protein_g ?? '-';
            document.getElementById('nip-fat-total').textContent = spec.nutr_fat_total_g ?? '-';
            document.getElementById('nip-fat-sat').textContent = spec.nutr_fat_saturated_g ?? '-';
            document.getElementById('nip-carbs').textContent = spec.nutr_carbohydrate_g ?? '-';
            document.getElementById('nip-sugars').textContent = spec.nutr_sugars_g ?? '-';
            document.getElementById('nip-sodium').textContent = spec.nutr_sodium_mg ?? '-';
        } else {
            document.getElementById('nip-data-container').classList.add('d-none');
            document.getElementById('nip-no-data').classList.remove('d-none');
        }

        // Ingredients
        const ingredients = spec.ing_ingredient_list?.split(',').map(s => s.trim()).filter(Boolean) || [];
        this.renderBadgeList('ingredients-list', ingredients, 'bg-secondary');
        document.getElementById('ingredients-no-data').classList.toggle('d-none', ingredients.length > 0);
        document.getElementById('ingredients-list').classList.toggle('d-none', ingredients.length === 0);

        // Allergens
        // const containsAllergens = spec.ingr_contains_allergens || [];
        // const mayContainAllergens = spec.ingr_may_contain_allergens || [];
        
        // this.renderBadgeList('allergens-contains-list', containsAllergens, 'bg-danger');
        // this.renderBadgeList('allergens-may-contain-list', mayContainAllergens, 'bg-warning text-dark');
        
        // document.getElementById('allergens-contains').classList.toggle('d-none', containsAllergens.length === 0);
        // document.getElementById('allergens-may-contain').classList.toggle('d-none', mayContainAllergens.length === 0);
        
        const statement = spec.allergen_statement;
        document.getElementById('allergens-statement').classList.toggle('d-none', !statement);
        document.getElementById('allergens-statement-text').textContent = statement || '';
        
        // const noAllergenData = containsAllergens.length === 0 && mayContainAllergens.length === 0 && !statement;
        // document.getElementById('allergens-no-data').classList.toggle('d-none', !noAllergenData);

        // Dietary Claims
        const dietaryClaims = [
            // { key: 'vegetarian', value: spec.aus_claim_vegetarian },
            // { key: 'vegan', value: spec.aus_claim_vegan },
            { key: 'gluten_free', value: spec.cert_is_gluten_free },
            { key: 'halal', value: spec.cert_is_halal },
            { key: 'kosher', value: spec.cert_is_kosher },
        ];
        this.renderDietaryClaims(dietaryClaims);

        // Specific Gravity (for liquids)
        const isLiquid = ['L', 'mL'].includes(this.formData.pricing_unit);
        const sgSection = document.getElementById('specific-gravity-section');
        sgSection.style.display = isLiquid ? 'block' : 'none';
        
        if (isLiquid && spec.phys_specific_gravity) {
            document.getElementById('sg-source-value').textContent = `(Source: ${spec.phys_specific_gravity})`;
        }

        this.confirmModal.show();
    }

    renderBadgeList(containerId, items, badgeClass) {
        const container = document.getElementById(containerId);
        container.innerHTML = items.map(item => 
            `<span class="badge ${badgeClass}">${item}</span>`
        ).join('');
    }

    renderDietaryClaims(claims) {
        const container = document.getElementById('dietary-claims-list');
        container.innerHTML = claims.map(({ key, value }) => {
            const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const icon = value ? '<i class="bi bi-check-circle-fill text-success me-1"></i>' : 
                                 '<i class="bi bi-circle text-muted me-1"></i>';
            const textClass = value ? 'fw-medium' : 'text-muted';
            return `<div class="d-flex align-items-center gap-1 bg-light px-3 py-2 rounded">
                ${icon}<span class="${textClass}">${label}</span>
            </div>`;
        }).join('');
    }

    async createRawMaterial() {
        const btn = document.getElementById('confirm-create-btn');
        const spinner = document.getElementById('create-spinner');
        const text = document.getElementById('confirm-create-text');
        
        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.textContent = 'Creating...';

        try {
            const sgOverride = document.getElementById('sg-override').value;
            const payload = {
                ...this.formData,
            };
            
            if (sgOverride) {
                payload.specific_gravity = parseFloat(sgOverride);
            }

            const response = await fetch('/data/store/spec', {
                method: 'POST',   
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (response.ok) {
                this.confirmModal.hide();
                this.showToast('Raw material created successfully', 'success');
                window.location.reload();
            } else {
                throw new Error(result.message || 'Failed to create raw material');
            }
        } catch (error) {
            console.error('Error creating raw material:', error);
            this.showToast(error.message, 'danger');
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
            text.textContent = 'Confirm & Create';
        }
    }

    // Helper methods
    changeDataSource() {
        this.detailsModal.hide();
        this.searchModal.show();
    }

    backToDetails() {
        this.confirmModal.hide();
        this.detailsModal.show();
    }

    showSearchLoading() {
        document.getElementById('spec-loading').classList.remove('d-none');
        document.getElementById('spec-list').innerHTML = '';
        document.getElementById('spec-empty').classList.add('d-none');
        document.getElementById('spec-no-results').classList.add('d-none');
    }

    showSearchError() {
        document.getElementById('spec-loading').classList.add('d-none');
        document.getElementById('spec-list').innerHTML = '<div class="alert alert-danger">Error loading specifications</div>';
    }

    getStatusClass(status) {
        const classes = {
            'approved': 'bg-success',
            'draft': 'bg-secondary',
            'pending_review': 'bg-warning text-dark',
            'rejected': 'bg-danger',
            'archived': 'bg-dark',
        };
        return classes[status] || 'bg-secondary';
    }

    showToast(message, type = '') {
        // Use your preferred toast implementation

        let titles = {
            success: "Success!",
            warning: "Warning!",
            danger: "Error!"
        };

        let icons = {
            success: "success",
            warning: "warning",
            danger: "error"
        };

        // let alertType = type || "warning";   // default if type missing

        Swal.fire({
            title: titles[type],
            text: message || 'Something went wrong.',
            icon: icons[type]
        });

        // alert(message); // Replace with Bootstrap toast or similar
    }

    resetAll() {
        this.selectedSpecification = null;
        this.formData = {};
        document.getElementById('spec-search-input').value = '';
        document.getElementById('raw-material-form')?.reset();
    }
}

// Initialize
let rawMaterialFlow;
document.addEventListener('DOMContentLoaded', () => {
    rawMaterialFlow = new RawMaterialCreationFlow();
});

// Global functions
function changeDataSource() { rawMaterialFlow.changeDataSource(); }
function backToDetails() { rawMaterialFlow.backToDetails(); }
function navigateToAddSpecification() {
    window.location.href = '/specifications/add';
}
