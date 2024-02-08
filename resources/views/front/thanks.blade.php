@extends('front.layouts.app')

@section('content')
    <section class="container">
        <div class="col-md-12 text-center py-5 shadow-lg my-5">
            <div class="py-5">
                @if (Session::has('success'))
                    <div class="alert alert-success">
                        {{ Session::get('success') }}
                    </div>
                @endif
                <h1>Thank You</h1>
                <p class="mt-2">Your Order ID is: {{ $id }}</p>
            </div>
        </div>
    </section>
@endsection
