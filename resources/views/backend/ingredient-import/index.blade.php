@extends('backend.master', [
'pageTitle' => 'Raw Materials',
'activeMenu' => [
'item' => 'raw_materials',
'subitem' => 'raw_materials',
'additional' => '',
],
'features' => [
'datatables' => '1',
],
'breadcrumbItems' => [
['label' => 'Database', 'url' => '#'],
['label' => 'Raw Materials']
],])

@push('styles')
<style>
    .table {
        table-layout: auto;
    }
</style>
@endpush
@section('content')
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-md-12">
            <div class="">
                <div class="card-header">
                    <h1 class="page-title">Raw Materials</h1>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <!-- Step 1: Download Template -->
                    <div class="mb-4">
                        <h5>Step 1: Download Template</h5>
                        <p>Download the Excel template and fill in your ingredient data.</p>
                        <a href="{{ route('ingredients.template.download') }}" class="btn btn-secondary-blue">
                            Download Template
                        </a>
                    </div>

                    <!-- Step 2: Upload File -->
                    <div class="mb-4">
                        <h5>Step 2: Upload Filled Template</h5>
                        <form id="uploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls">
                            </div>
                            <button type="button" class="btn btn-secondary-blue" id="previewBtn">
                                Preview Data
                            </button>
                            <div class="ingredient-import-notes">
                                <h6>Note:</h6>
                                <ul>
                                    <li><strong>Ingredient SKU:</strong> The unique identifier for each ingredient.</li>
                                    <li><strong>Update Existing:</strong> If the SKU exists, the record will be <strong>updated</strong> (overwritten).</li>
                                    <li><strong>New Record:</strong> If the SKU doesn't exist, a <strong>new record</strong> will be created.</li>
                                </ul>
                            </div>
                        </form>
                    </div>

                    <!-- Step 3: Preview Data -->
                    <div id="previewSection" class="mb-4" style="display: none;">
                        <h5>Step 3: Review Data</h5>
                        <div id="validationErrors" class="alert alert-danger mb-3" style="display: none;"></div>

                        <div class="table-responsive my-4">
                            <table class="table table-responsive" id="previewTable">
                                <thead id="previewHeader"></thead>
                                <tbody id="previewBody"></tbody>
                            </table>
                        </div>

                        <!-- Step 4: Import Button -->
                        <form action="{{ route('ingredients.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                            @csrf
                            <input type="file" id="importFile" name="file" style="display: none;">
                            <button type="submit" class="btn btn-secondary-blue" id="importBtn">
                                Import Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    $(document).ready(function() {
        let fileToImport = null;

        $('#previewBtn').click(function() {
            const fileInput = $('#file')[0];
            const formData = new FormData();

            if (fileInput.files.length === 0) {
                alert('Please select a file first');
                return;
            }

            fileToImport = fileInput.files[0];
            formData.append('file', fileToImport);
            formData.append('_token', '{{ csrf_token() }}');

            // Show loading state
            $('#previewBtn').prop('disabled', true).html('Loading...');

            $.ajax({
                url: '{{ route("ingredients.preview") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        displayPreview(response.data);

                        if (response.mandatory && response.mandatory.length > 0) {
                            displayValidationErrors(response.errors);
                            $('#importBtn').prop('disabled', true);
                        }else{
                            if (response.errors && response.errors.length > 0) {
                                displayValidationErrors(response.errors);
                                $('#importBtn').prop('disabled', false);
                            } else {
                                $('#validationErrors').hide();
                                $('#importBtn').prop('disabled', false);
                            }
                        }

                        
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error previewing file.';

                    // Check if response is JSON and has an `error` field
                    try {
                        let response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            errorMessage = response.error;
                        }
                    } catch (e) {
                        console.log("Response is not valid JSON", e);
                    }

                    alert(errorMessage);
                },
                complete: function() {
                    $('#previewBtn').prop('disabled', false).html('Preview Data');
                }
            });

        });

        $('#importForm').submit(function(e) {
            if (fileToImport) {
                const importInput = $('#importFile')[0];

                // Create a new DataTransfer object and add the file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(fileToImport);

                // Set the files property of the hidden input
                importInput.files = dataTransfer.files;
            }
        });

        function displayPreview(data) {
            const previewSection = $('#previewSection');
            const headerRow = $('#previewHeader');
            const bodyRows = $('#previewBody');

            headerRow.empty();
            bodyRows.empty();

            if (data.length > 0) {
                // Create header row
                const headers = Object.keys(data[0]);
                headerRow.append('<tr>' + headers.map(h =>
                    `<th class="text-primary-blue th-top-align text-center" >${h.replace(/_/g, ' ').toUpperCase()}</th>`
                ).join('') + '</tr>');

                // Create data rows
                data.forEach((row, index) => {
                    bodyRows.append('<tr>' + headers.map(h =>
                        `<td class="text-center">${row[h] || ''}</td>`
                    ).join('') + '</tr>');
                });
            }

            previewSection.show();
            /*
                        if (!$.fn.DataTable.isDataTable('#previewTable')) {
                            $('#previewTable').DataTable({
                                responsive: true,
                                dom: "<'row mb-4'<'col-md-6 col-6 col-sm-6'f><'col-md-6 col-6 col-sm-6'l>>" + // Search box (f) and entries dropdown (l)
                                    "<'row'<'col-sm-12'tr>>" + // Table rows
                                    "<'row'<'col-md-5'i><'col-md-7'p>>", // Info text (i) and pagination (p)
                                language: {
                                    search: "", // Removes "Search:" label
                                    searchPlaceholder: "Search", // Adds placeholder to the search box
                                    lengthMenu: "_MENU_ per page", // Customizes "entries per page" text
                                    paginate: {
                                        previous: "<i class='material-symbols-outlined'>chevron_left</i>", // Replace "Previous" text with '<'
                                        next: "<i class='material-symbols-outlined'>chevron_right</i>" // Replace "Next" text with '>'
                                    }
                                },
                                pageLength: 25,
                                initComplete: function() {
                                    // Move the search box to the left and entries dropdown to the right
                                    const tableWrapper = $(this).closest('.dataTables_wrapper');
                                    const searchBox = tableWrapper.find('.dataTables_filter');
                                    const lengthDropdown = tableWrapper.find('.dataTables_length');

                                    searchBox.css({
                                        'float': 'left',
                                        'margin-top': '0'
                                    });
                                    lengthDropdown.css('float', 'right');
                                }
                            });
                        }
                            */

        }

        function displayValidationErrors(errors) {
            const errorDiv = $('#validationErrors');
            if (errors.length > 0) {
                const errorList = errors.map(error => `<li>${error}</li>`).join('');
                errorDiv.html(`
                <h6>Please correct the following errors:</h6>
                <ul>${errorList}</ul>
            `).show();
            } else {
                errorDiv.hide();
            }
        }

        // File input change handler to reset the form
        $('#file').change(function() {
            $('#previewSection').hide();
            $('#validationErrors').hide();
            fileToImport = null;
        });
    });
</script>
@endpush