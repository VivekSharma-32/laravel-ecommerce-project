@extends('front.layouts.app')
@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.home') }}">Home</a></li>
                    <li class="breadcrumb-item">Reset Password</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-10">
        <div class="container">
            @if (Session::has('error'))
                <div class="alert alert-danger">
                    {{ Session::get('error') }}
                </div>
            @endif

            @if (Session::has('success'))
                <div class="alert alert-success">
                    {{ Session::get('success') }}
                </div>
            @endif
            <div class="login-form">
                <form action="{{ route('front.processResetPassword') }}" method="post">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <h4 class="modal-title">Reset Password</h4>
                    <div class="form-group">
                        <input autocomplete="off" type="password"
                            class="form-control @error('new_password') is-invalid @enderror" placeholder="New Password"
                            id="password" name="new_password">
                        @error('new_password')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input autocomplete="off" type="password"
                            class="form-control @error('confirm_password') is-invalid @enderror"
                            placeholder="Confirm Password" id="confirm_password" name="confirm_password">
                        @error('confirm_password')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-dark btn-block btn-lg">Reset</button>

                </form>
                <div class="text-center small"><a href="{{ route('account.login') }}">Click Here To Login</a>
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection
