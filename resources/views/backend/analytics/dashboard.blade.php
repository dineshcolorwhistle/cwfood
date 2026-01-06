@extends('backend.master', [
  'pageTitle' => 'Analytics Dashboard',
  'activeMenu' => ['item'=>'Analytics','sub'=>'Dashboard'],
  'features' => [
    'datatables' => '1',
  ],
])

@push('styles')
<style>
  /* (Optional) Keep any dashboard-specific styles you still use on Overview */
  .dropdown-item.active {
    background-color: #f8f9fa !important;
    color: #ea580c !important;
  }

  /* If your Overview partial uses these, keep them; otherwise safe to remove */
  .filter-toggle {
    border: 2px solid var(--secondary-color);
    border-radius: 30px;
    display: flex;
  }
  .filter-toggle .filter-button {
    display: flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    border: none;
    padding: 6px 16px;
    color: var(--secondary-color);
    border-radius: 30px;
  }
  .filter-toggle .filter-text { font-size: 14px; font-weight: 500; }
  .filter-toggle .material-symbols-outlined { font-size: 20px; }
</style>
@endpush

@section('content')
<div class="container-fluid">
  {{-- Overview (single-page; tabs removed) --}}
  @include('backend.analytics.partials.overview', ['all' => $all])
</div>
@endsection