@extends('backend.master', [
'pageTitle' => 'FSANZ Food',
'activeMenu' => [
'item' => 'FSANZ Food',
'subitem' => 'FSANZ Food',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'FSANZ', 'url' => '#'],
['label' => 'FSANZ']
]
])

@push('styles')
@endpush

@section('content')

<div class="container-fluid product-search dataTable-wrapper">
    <div class="card-header">
        <div class="title-add">
            <div class="d-flex gap-4">
                <div>
                <h1 class="h3 mb-0">{{ $food->name }}</h1>
                <p class="mb-0 mt-2" style="font-size: 20px; font-weight: 700;">FSANZ Key: {{ $food->fsanz_key }}</p>
                </div>
                @include('backend.fsanz_food.partials._ai-status-badge', ['status' => $food->ai_estimation_status])
            </div>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <button type="button" 
                class="btn btn-primary" 
                onclick="openCreateSpecificationModal()">
            <i class="bi bi-file-earmark-text me-2"></i>
            Create Specification
        </button>
            </div>

        </div>
    </div>
    <div class="card-body">
        {{-- Tabs Navigation --}}
        <ul class="nav nav-tabs" id="fsanzTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                    Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="labelling-tab" data-bs-toggle="tab" data-bs-target="#labelling" type="button" role="tab">
                    Labelling
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="safety-tab" data-bs-toggle="tab" data-bs-target="#safety" type="button" role="tab">
                    Safety
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="regulatory-tab" data-bs-toggle="tab" data-bs-target="#regulatory" type="button" role="tab">
                    Regulatory
                </button>
            </li>
        </ul>  

        {{-- Tab Content --}}
        <div class="tab-content pt-4" id="fsanzTabsContent">
            {{-- Overview Tab --}}
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                @include('backend.fsanz_food.partials._overview-tab')
            </div>

            {{-- Labelling Tab --}}
            <div class="tab-pane fade" id="labelling" role="tabpanel">
                @include('backend.fsanz_food.partials._labelling-tab')
            </div>

            {{-- Safety Tab --}}
            <div class="tab-pane fade" id="safety" role="tabpanel">
                @include('backend.fsanz_food.partials._safety-tab')
            </div>

            {{-- Regulatory Tab --}}
            <div class="tab-pane fade" id="regulatory" role="tabpanel">
                @include('backend.fsanz_food.partials._regulatory-tab')
            </div>
        </div>

        @include('backend.fsanz_food._specmodal')

    </div>
</div>
@endsection

@push('scripts')
<script>
    /**
     * FSANZ Create Specification Modal Handler
     */
    class FSANZCreateSpecificationModal {
        constructor() {
            this.modal = null;
            this.successModal = null;
            this.currentFood = null;
            this.isSubmitting = false;
            
            this.init();
        }
        
        init() {
            // Initialize Bootstrap modals
            const modalEl = document.getElementById('createSpecificationModal');
            const successModalEl = document.getElementById('successModal');
            
            if (modalEl) {
                this.modal = new bootstrap.Modal(modalEl);
            }
            if (successModalEl) {
                this.successModal = new bootstrap.Modal(successModalEl);
            }
            
            // Bind create button
            const createBtn = document.getElementById('createSpecificationBtn');
            if (createBtn) {
                createBtn.addEventListener('click', () => this.handleCreate());
            }
            
            // Reset form when modal is hidden
            modalEl?.addEventListener('hidden.bs.modal', () => this.resetForm());
        }
        
        /**
         * Open modal with FSANZ food data
         * @param {Object} food - FSANZ food object
         */
        open(food) {
            if (!food) {
                console.error('No food data provided');
                return;
            }
            
            this.currentFood = food;
            this.populateModal(food);
            this.validateFood(food);
            this.modal?.show();
        }
        
        /**
         * Populate modal with FSANZ food data
         */
        populateModal(food) {
            // Hidden fields
            document.getElementById('fsanz_food_id').value = food.id;
            
            // Preview section
            document.getElementById('preview-food-name').textContent = food.name || '-';
            document.getElementById('preview-food-description').textContent = food.description || '';
            document.getElementById('preview-fsanz-key').textContent = food.fsanz_key || '-';
            document.getElementById('preview-measurement-basis').textContent = food.measurement_basis || 'per 100g';
            
            // Result preview
            document.getElementById('result-name').textContent = food.name || '-';
            document.getElementById('result-sku').textContent = `FSANZ-${food.fsanz_key}`;
        }
        
        /**
         * Validate FSANZ food data and show errors/warnings
         */
        validateFood(food) {
            const errors = [];
            const warnings = [];
            
            // Critical validations
            if (!food.name || food.name.trim() === '') {
                errors.push('Food name is required');
            }
            
            if (!food.id) {
                errors.push('FSANZ food ID is missing');
            }
            
            // Check nutritional data
            const nutritionalValues = [food.energy_kj, food.protein_g, food.fat_total_g, food.carbohydrate_g];
            const hasNutritionalData = nutritionalValues.some(val => val !== null && val !== undefined);
            
            if (!hasNutritionalData) {
                errors.push('At least one nutritional value (energy, protein, fat, or carbohydrate) is required');
            }
            
            // Quality warnings
            if (!food.estimated_allergens) {
                warnings.push('No allergen data available - allergen fields will be empty');
            }
            
            if (!food.estimated_dietary_status) {
                warnings.push('No dietary status data available - certification fields will be false');
            }
            
            if (!food.primary_origin_country && !food.estimated_australia_percent) {
                warnings.push('No country of origin data available');
            }
            
            if (!food.estimated_ingredients) {
                warnings.push('No ingredient list data available');
            }
            
            // Display errors
            const errorsContainer = document.getElementById('validation-errors');
            const errorList = document.getElementById('error-list');
            if (errors.length > 0) {
                errorList.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
                errorsContainer.classList.remove('d-none');
                document.getElementById('createSpecificationBtn').disabled = true;
            } else {
                errorsContainer.classList.add('d-none');
                document.getElementById('createSpecificationBtn').disabled = false;
            }
            
            // Display warnings
            const warningsContainer = document.getElementById('validation-warnings');
            const warningList = document.getElementById('warning-list');
            if (warnings.length > 0) {
                warningList.innerHTML = warnings.map(w => `<li>${w}</li>`).join('');
                warningsContainer.classList.remove('d-none');
            } else {
                warningsContainer.classList.add('d-none');
            }
        }
        
        /**
         * Handle form submission
         */
        async handleCreate() {
            if (this.isSubmitting || !this.currentFood) return;
            
            this.isSubmitting = true;
            this.setLoading(true);
            
            const formData = new FormData(document.getElementById('createSpecificationForm'));
            
            try {
                const response = await fetch('/specifications/create-from-fsanz', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    this.showError(data.message);
                    return; 
                    // throw new Error(data.message || 'Failed to create specification');
                }
                
                // Success - show success modal
                this.modal?.hide();
                this.showSuccess(data.specification);
                
            } catch (error) {
                console.error('Error creating specification:', error);
                this.showError(error.message);
            } finally {
                this.isSubmitting = false;
                this.setLoading(false);
            }
        }
        
        /**
         * Show success modal
         */
        showSuccess(specification) {
            Swal.fire({
                title: "Success!",
                text: "Specification created successfully.",
                icon: "success",
                confirmButtonText: "Go to Specification",
                confirmButtonColor: "#3085d6",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/specifications/edit/${specification.id}`; // Your redirect URL
                }
            });

            // document.getElementById('success-spec-name').textContent = specification.name;
            // document.getElementById('viewSpecificationLink').href = `/specifications/${specification.id}`;
            // this.successModal?.show();
        }
        
        /**
         * Show error toast/alert
         */
        showError(message) {
            // You can use Bootstrap toast or any notification library
            Swal.fire({
                title: "Warning!",
                text: message || 'Failed to create specification',
                icon: "warning"
            })
            // alert('Error: ' + message);
        }
        
        /**
         * Set loading state
         */
        setLoading(loading) {
            const btn = document.getElementById('createSpecificationBtn');
            const spinner = document.getElementById('loadingSpinner');
            const cancelBtn = document.getElementById('cancelBtn');
            
            if (loading) {
                btn.disabled = true;
                cancelBtn.disabled = true;
                spinner.classList.remove('d-none');
            } else {
                btn.disabled = false;
                cancelBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        }
        
        /**
         * Reset form to initial state
         */
        resetForm() {
            document.getElementById('createSpecificationForm').reset();
            document.getElementById('include_classification').checked = true; // Default checked
            document.getElementById('validation-errors').classList.add('d-none');
            document.getElementById('validation-warnings').classList.add('d-none');
            this.currentFood = null;
            this.isSubmitting = false;
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        window.fsanzSpecModal = new FSANZCreateSpecificationModal();
    });
</script>
<script>
    // FSANZ Food data for the modal
    const fsanzFoodData = @json($food);
    
    // Function called by the Create Specification button
    function openCreateSpecificationModal() {
        if (window.fsanzSpecModal) {
            window.fsanzSpecModal.open(fsanzFoodData);
        } else {
            console.error('Modal handler not initialized');
        }
    }
</script>
@endpush
