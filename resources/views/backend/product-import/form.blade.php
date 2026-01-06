@extends('backend.master', [
'pageTitle' => 'Products Import',
'activeMenu' => [
'item' => 'Products',
'subitem' => 'Products',
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Database', 'url' => '#'],
['label' => 'Products']
],])

@section('content')
<div class="container-fluid product-import px-0">
    <div class="row">
        <div class="col-md-12">
            <div class="">
                <div class="card-header">
                    <h1 class="page-title">Products</h1>
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

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if(session('validation_errors'))
                    <div class="alert alert-danger">
                        <h4>Validation Errors:</h4>
                        <ul>
                            @foreach(session('validation_errors') as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('product.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="products_csv" class="form-label">Products CSV File</label>
                                    <input type="file" class="form-control" name="products_csv" accept=".csv" required>
                                    <small class="form-text text-muted">Upload the products CSV file</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ingredients_csv" class="form-label">Product Ingredients CSV File</label>
                                    <input type="file" class="form-control" name="ingredients_csv" accept=".csv" required>
                                    <small class="form-text text-muted">Upload the product ingredients CSV file</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-secondary-blue">
                                    Import Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

@endpush