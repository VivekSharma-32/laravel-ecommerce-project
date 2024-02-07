@extends('front.layouts.app')
@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.home') }}">Home</a></li>
                    <li class="breadcrumb-item">Login</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-10">
        <div class="container">
            @if (Session::has('success'))
                <div class="alert alert-success">
                    {{ Session::get('success') }}
                </div>
            @endif
            <div class="login-form">
                <form action="" method="post" name="registrationForm" id="registrationForm">
                    <h4 class="modal-title">Login Now</h4>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Email" id="email" name="email">
                        <p></p>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" placeholder="Password" id="password" name="password">
                        <p></p>
                    </div>
                    <div class="form-group small">
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn btn-dark btn-block btn-lg">Register</button>
                </form>
                <div class="text-center small">Create your account. <a href="{{ route('account.register') }}">Register
                        Now</a>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('customJs')
    <script>
        $("#registrationForm").submit(function(event) {
            event.preventDefault();
            $("button[ttype='submit']").prop('disabled', true);
            $.ajax({
                url: "{{ route('account.processRegister') }}",
                type: 'post',
                data: $(this).serializeArray(),
                dataType: 'json',
                success: function(response) {
                    var errors = response.errors;
                    $("button[ttype='submit']").prop('disabled', false);
                    if (response.status == false) {


                        if (errors.email) {
                            $("#email").siblings('p').addClass('invalid-feedback').html(errors.email);
                            $("#email").addClass('is-invalid');
                        } else {
                            $("#email").siblings('p').removeClass('invalid-feedback').html('');
                            $("#email").removeClass('is-invalid');
                        }

                        if (errors.password) {
                            $("#password").siblings('p').addClass('invalid-feedback').html(errors
                                .password);
                            $("#password").addClass('is-invalid');
                        } else {
                            $("#password").siblings('p').removeClass('invalid-feedback').html('');
                            $("#password").removeClass('is-invalid');
                        }
                    } else {
                        $("#email").siblings('p').removeClass('invalid-feedback').html('');
                        $("#email").removeClass('is-invalid');

                        $("#password").siblings('p').removeClass('invalid-feedback').html('');
                        $("#password").removeClass('is-invalid');
                    }

                },
                error: function(jQXHR, exception) {
                    console.log('Something went wrong');
                }
            })
        });
    </script>
@endsection
