@extends('backend.master', [
'pageTitle' => ucfirst(str_replace('-', ' ', request()->segment(2))),
'activeMenu' => [
'item' => '',
'subitem' => '',
'additional' => '',
],
'breadcrumbItems' => [
['label' => ucfirst(str_replace(['rep', 'ana', 'database', 'data'], ['Reports', 'Analytics', 'Database', 'Data Entry'], request()->segment(1))), 'url' => '#'],
['label' => ucfirst(str_replace('-', ' ', request()->segment(2)))]
],])


@section('content')
<div class="container-fluid px-0">
    <div class="row w-lg-100 w-md-100">
        <div class="col-12">
            <div class="">
                <div class="card-body text-center p-5">
                    <div class="icon-primary-orange" style="margin-right: 0px;">
                        <span class="material-symbols-outlined" style="font-size: 48px;">engineering</span>
                    </div>
                    <h3 class="mt-2 text-primary-orange-lg">Work in Progress</h3>
                    <div class="text-primary-dark-mud">
                        The {{ strtolower(str_replace('-', ' ', request()->segment(2))) }} section is currently under development.
                        Please check back later.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection