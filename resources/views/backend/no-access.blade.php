@extends('backend.master', [
'activeItem' => 'No Access',
'activeSubitem' => 'No Access'
])

@section('content')
<div class="container-fluid px-0">
    <div class="row w-lg-100 w-md-100">
        <div class="col-12">
            <div class="">
                <div class="card-body text-center p-5">
                    <div class="icon-primary-orange" style="margin-right: 0px;">
                        <span class="material-symbols-outlined" style="font-size: 48px;">block</span>
                    </div>
                    <h3 class="mt-2 text-primary-orange-lg">No Access</h3>
                    <div class="text-primary-dark-mud">
                        You do not have access to this module. Please contact the BatchBase administrator for assistance.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection