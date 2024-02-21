<!-- resources/views/account_deactivated.blade.php -->

@extends('core.layouts.public_app', [
    'page' => 'class="horizontal-layout horizontal-menu 2-columns bg-full-screen-image" data-open="click" data-menu="horizontal-menu" data-col="2-columns"'
])

@section('content')
    <div class="container justify-content-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card" style="background-color: rgba(0, 0, 0, 0.4); color: white; background-color: #72929F; underline">
                    <h1 class="card-header" style="background-color: rgba(0, 0, 0, 0); color: white">{{ __('Account Deactivated') }}</h1>

                    <div class="card-body" >
                        <h2>Your account has been deactivated. If you believe this is a mistake or have any questions, please contact your administrator.</h2>
                        {{-- You can customize the message and add more information or instructions as needed --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
