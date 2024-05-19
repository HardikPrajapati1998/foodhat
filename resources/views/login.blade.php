
@extends('layout')
@section('title')
    <title>{{__('user.Login')}}</title>
@endsection
@section('meta')
    <meta name="description" content="{{__('user.Login')}}">
    <style>
        .loader {
            border: 20px solid rgba(0, 0, 0, 0.1);
            border-top: 20px solid #3498db;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            animation: rotate 2s linear infinite;
            margin: 0 auto;
            margin-top: 100px;
            margin-bottom: 100px;
        }

        @keyframes rotate {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
        }

    </style>
@endsection

@section('public-content')

    <!--=============================
        BREADCRUMB START
    ==============================-->
    <section class="tf__breadcrumb" style="background: url({{ asset($breadcrumb) }});">
        <div class="tf__breadcrumb_overlay">
            <div class="container">
                <div class="tf__breadcrumb_text">
                    <h1>{{__('user.Login')}}</h1>
                    <ul>
                        <li><a href="{{ route('home') }}">{{__('user.Home')}}</a></li>
                        <li><a href="{{ route('login') }}">{{__('user.Login')}}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!--=============================
        BREADCRUMB END
    ==============================-->

            <!--=========================
        SIGNIN START
    ==========================-->
    <section class="tf__signin pt_100 xs_pt_70 pb_100 xs_pb_70">
        <div class="container">
            <div class="row justify-content-center wow fadeInUp" data-wow-duration="1s">
                <div class="col-xl-5 col-sm-10 col-md-8 col-lg-6">
                    <div class="tf__login_area">
                        <h2>{{__('user.Welcome back!')}}</h2>
                        <p>{{__('user.Login in to continue')}}</p>
                        <form action="{{ route('store-login') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="tf__login_imput">
                                        <input type="email" name="email" placeholder="{{__('user.Email')}}">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="tf__login_imput">
                                        <input type="password" name="password" placeholder="{{__('user.Password')}}">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="tf__login_imput tf__login_check_area">
                                        <div class="form-check">
                                            <input class="form-check-input" name="remember" type="checkbox" value=""
                                                id="flexCheckDefault">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                {{__('user.Remeber Me')}}
                                            </label>
                                        </div>
                                        <a href="{{ route('forget-password') }}">{{__('user.Forgot Password ?')}}</a>
                                    </div>
                                </div>

                                @if($recaptcha_setting->status==1)
                                    <div class="col-xl-12 mb-3">
                                        <div class="g-recaptcha" data-sitekey="{{ $recaptcha_setting->site_key }}"></div>
                                    </div>
                                @endif

                                <div class="col-xl-12">
                                    <div class="tf__login_imput">
                                        <button type="submit" class="common_btn">{{__('user.login')}}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <form id="registerForm">
                            <div class="col-xl-12">
                                <div class="tf__login_imput">
                                    <button onclick="googleSignIn()" type="button" class="common_btn">Sign in with Google</button>
                                </div>
                            </div>
                        </form>

                        <p class="create_account">{{__('user.Do not have an account ?')}} <a href="{{ route('register') }}">{{__('user.Register here')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
                        {{-- <div class="tf__login_area">
                            <div class="loader"></div>
                        </div> --}}

                        {{-- <form id = "loginForm" action="{{ route('store-login') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="tf__login_imput">
                                        <input type="hidden" type="email" name="email"id="email" value="dhiraj@gmail.com">
                                    </div>
                                </div>


                            </div>
                        </form> --}}
    <!--=========================
        SIGNIN END
    ==========================-->
    <script src="https://apis.google.com/js/platform.js" async defer></script>

    {{-- <script>
        function isValidEmail(email) {
            // Regular expression for validating an email address
            var emailRegex = /\S+@\S+\.\S+/;
            return emailRegex.test(email);
        }

        // Function to get email address from the URL
        function getEmailFromURL() {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('email');
        }

        function parseAccessToken() {
            var hash = window.location.hash.substring(1);
            var params = new URLSearchParams(hash);
            return params.get('access_token');
        }

            // Make an API request to retrieve the user's email
        function getUserEmail(accessToken) {
            return fetch('https://www.googleapis.com/oauth2/v1/userinfo?alt=json', {
                    headers: {
                    'Authorization': 'Bearer ' + accessToken
                    }
                })
                .then(response => response.json())
                .then(data => data.email)
                .catch(error => {
                console.error('Error retrieving user email:', error);
                return null;
                });
        }

            // Check if the URL contains the access token


        Function to handle form submission
        function submitForm() {
            var accessToken = parseAccessToken();
            var appUrl = "{{ env('APP_URL') }}";
            if (accessToken) {
                getUserEmail(accessToken)
                    .then(email => {
                        console.log(email);
                        document.getElementById('email').value = email;
                        // Submit the form
                        document.getElementById('loginForm').submit();
                    });
            } else {
                // Redirect to the authentication page
                window.location.href = appUrl + '/login';
            }
        }

        Call the submitForm function when the page loads
        window.onload = function() {
            submitForm();
        };
     </script> --}}

     <script>
         function googleSignIn() {
            // Replace YOUR_CLIENT_ID with your actual Google client ID
            var CLIENT_ID = '771542863570-sa14n0uagm4vn8tvavk2rjptd4euoag9.apps.googleusercontent.com';
            var appUrl = "{{ env('APP_URL') }}";
            // Redirect URI where Google will redirect after authentication
            var REDIRECT_URI = 'http://localhost/foodhat/main_files/login/';

            // Scope for accessing user's email address
            var SCOPE = 'https://www.googleapis.com/auth/userinfo.email';

            // Google OAuth URL
            var oauthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' +
            'response_type=token&' +
            'client_id=' + encodeURIComponent(CLIENT_ID) + '&' +
            'redirect_uri=' + encodeURIComponent(REDIRECT_URI) + '&' +
            'scope=' + encodeURIComponent(SCOPE);

            // Redirecting the user to Google authentication

            window.location.href = oauthUrl;
        }
     </script>

@endsection
