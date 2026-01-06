@extends('backend.master', [
'activeItem' => 'Subscription expired',
'activeSubitem' => 'Subscription expired'
])

@section('content')
<div class="container-fluid px-0">
    <div class="row w-lg-100 w-md-100">
        <div class="col-12">
            <div class="">
                <div class="card-body text-center p-5">
                    <div class="icon-primary-orange" style="margin-right: 0px;">
                        <span class="material-symbols-outlined" style="font-size: 48px;">engineering</span>
                    </div>
                    <h3 class="mt-2 text-primary-orange-lg">Subscription Expired</h3>
                    <div class="text-primary-dark-mud">
                        Your subscription has expired. To continue using our services, please reach out to the BatchBase support team or administrator.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection