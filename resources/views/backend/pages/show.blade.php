@extends('backend.master', [
'pageTitle' => $page->title,
'activeMenu' => [
'item' => 'Pages',
'subitem' => $page->title ,
'additional' => '',
],
'breadcrumbItems' => [
['label' => 'Pages', 'url' => '#'],
['label' => $page->title ]
],])


@section('content')
<div class="container-fluid products_details">
    <div class="card-header d-flex justify-content-between">
        <h1 class="page-title text-primary-orange">{{ $page->title }}</h1>
        <a href="{{ route('page.edit', $page->slug) }}" class="icon-primary-orange">
            <span class="material-symbols-outlined">edit</span>
        </a>
    </div>

    <div class="card-body text-primary-dark-mud mt-3" id="content-area">
        {!! $page->content !!}
    </div>
</div>
@endsection