@extends('backend.master', [
'pageTitle' => 'FSANZ Food Nutrition Data',
'activeMenu' => [
'item' => 'dashboard',
'subitem' => 'fsanz_nutrition',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Database', 'url' => '#'],
['label' => 'FSANZ']
],])

@push('styles')
<style>
    /* Ensure the modal is always centered on the screen */
    .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 70px);
    }

    /* Prevent content overflow in modal */
    .modal-content {
        max-height: 80vh;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="container-fluid product-list-container fsanz_nutrition background-bg">
    <div class="card-header">
        <div class="title-add d-flex justify-content-between">
            <h1 class="page-title">FSANZ</h1>
            <div class="right-side content d-flex flex-row-reverse align-items-center">
                <div class="Export-btn">
                    <div class="btn-group click-dropdown">
                        <button type="button" class="btn btn-primary-orange plus-icon" title="Download FSANZ">
                            <span class="material-symbols-outlined">download</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{env('APP_URL')}}/download/csv/fsanz">
                                    Download as CSV
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{env('APP_URL')}}/download/excel/fsanz">
                                    Download as Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class=" card-body">
        <!-- Loader -->
        <div id="tableSkeleton" class="skeleton-wrapper">
            @for($i=0;$i<6;$i++)
            <div class="skeleton-row"></div>
            @endfor
        </div>


        <table class="table responsiveness" id="dtRecordsView" style="display:none;">
            <thead class="thead-light">
                <tr>
                    <th class="text-primary-orange align-bottom">Food ID</th>
                    <th class="text-primary-orange align-bottom">Food Name</th>
                    <th class="text-primary-orange align-bottom text-end">Energy <br>(kJ)</th>
                    <th class="text-primary-orange align-bottom text-end">Protein <br>(g)</th>
                    <th class="text-primary-orange align-bottom text-end">Fat, Total <br>(g)</th>
                    <th class="text-primary-orange align-bottom text-end">Fat, Saturated <br>(g)</th>
                    <th class="text-primary-orange align-bottom text-end">Carbohydrate <br>(g)</th>
                    <th class="text-primary-orange align-bottom text-end">Total Sugars <br>(g)</th>
                    <th class="text-primary-orange align-bottom text-end">Sodium <br>(mg)</th>
                    <th class="text-primary-orange align-bottom text-end">Specific Gravity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nutrition as $nutritn)
                <tr>
                    <td class="text-primary-dark-mud">{{ $nutritn->food_id }}</td>
                    <td class="text-primary-dark-mud">
                        @php   
                            $tooltipHtml = "<h4>{$nutritn->food_name}</h4> <p>{$nutritn->description}</p>";
                        @endphp
                        <span data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="custom-tooltip admin-preference" data-bs-html="true" data-bs-title="<?= htmlspecialchars($tooltipHtml) ?>">{{ $nutritn->food_name }}</span>
                    </td>
                    <td class="text-primary-dark-mud text-end">{{ number_format($nutritn->energy_kj, 0) }}</td>
                    <td class="text-primary-dark-mud text-end">{{ number_format($nutritn->protein_g, 1) }}</td>
                    <td class="text-primary-dark-mud text-end">{{ number_format($nutritn->fat_total_g, 1) }}</td>
                    <td class="text-primary-dark-mud text-end">{{ number_format($nutritn->fat_saturated_g, 1) }}</td>
                    <td class="text-primary-dark-mud text-end">{{ number_format($nutritn->carbohydrate_g, 1) }}</td>
                    <td class="text-primary-dark-mud text-end">{{ number_format($nutritn->total_sugars_g, 1) }}</td>
                    <td class="text-primary-dark-mud text-end">{{ number_format($nutritn->sodium_mg, 0) }}</td>
                    <td class="text-primary-dark-mud text-end">{{ number_format($nutritn->specific_gravity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- Description Modal -->
<div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="descriptionModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="foodDescription"></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Modal handling
    document.addEventListener('DOMContentLoaded', function() {
        let modalTimeout;
        const modal = new bootstrap.Modal(document.getElementById('descriptionModal'));

        // Function to show modal
        function showModal(element) {
            const description = element.dataset.description;
            const foodName = element.dataset.foodName;

            document.getElementById('descriptionModalLabel').textContent = foodName;
            document.getElementById('foodDescription').textContent = description;

            modal.show();
        }

        // Function to hide modal
        function hideModal() {
            modal.hide();
        }

        // Add event listeners to all food names
        document.querySelectorAll('.food-name').forEach(element => {
            element.addEventListener('mouseenter', function() {
                modalTimeout = setTimeout(() => {
                    showModal(this);
                }, 500); // 500ms delay before showing modal
            });

            element.addEventListener('mouseleave', function() {
                clearTimeout(modalTimeout);
                // Add small delay before hiding to allow moving mouse to modal
                setTimeout(() => {
                    if (!document.querySelector('.modal:hover')) {
                        hideModal();
                    }
                }, 300);
            });
        });

        // Add event listener to modal for mouse leave
        document.getElementById('descriptionModal').addEventListener('mouseleave', function() {
            hideModal();
        });
    });
</script>
@endpush