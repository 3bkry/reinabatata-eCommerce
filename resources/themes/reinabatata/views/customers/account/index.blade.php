@extends('shop::layouts.master')

@section('content-wrapper')
    <div class="account-content row no-gutters velocity-divide-page mb-5">
        <div class="sidebar left col-2">
            @include('shop::customers.account.partials.sidemenu')
        </div>

        <div class="account-layout col-12 col-md-6">
            @yield('page-detail-wrapper')
        </div>
    </div>
@endsection
