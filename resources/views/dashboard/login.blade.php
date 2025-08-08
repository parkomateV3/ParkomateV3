<!DOCTYPE html>
<html lang="en" dir="ltr" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <title>Dashcode - HTML Template</title>
    <link rel="icon" type="image/png" href="assets/images/logo/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/rt-plugins.css">
    <link href="https://unpkg.com/aos@2.3.0/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="">
    <link rel="stylesheet" href="assets/css/app.css">
    <!-- START : Theme Config js-->
    <script src="assets/js/settings.js" sync></script>
    <!-- END : Theme Config js-->
</head>
<style>
    .custom-img {
        max-width: 50%;
        height: auto;
        margin: 1px auto;
    }

    #bg-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        margin: -110px 0px !important;
    }
</style>

<body class=" font-inter skin-default">
    <!-- [if IE]> <p class="browserupgrade">
            You are using an <strong>outdated</strong> browser. Please
            <a href="https://browsehappy.com/">upgrade your browser</a> to improve
            your experience and security.
        </p> <![endif] -->

    <div class="loginwrapper">
        <div class="lg-inner-column">
            <div class="left-column relative z-[1]">
                <div class="max-w-[300px] ltr:pl-20 rtl:pr-20">
                    <a href="#">
                        <img src="assets/images/logo/logo-white.png" alt="" class=" dark_logo">
                        <img src="assets/images/logo/logo-white.png" alt="" class=" white_logo">
                    </a>
                </div>
                <div class="absolute left-0 2xl:bottom-[-100px] bottom-[150px] h-full w-full z-[-1]">
                    <!-- <img src="assets/images/logo/PGS-scaled-1.jpg" alt="" class=" h-full w-full"> -->
                    <video autoplay loop muted playsinline id="bg-video">
                        <source src="assets/images/logo/p-video.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
            <div class="right-column relative">
                <div class="inner-content h-full flex flex-col bg-white dark:bg-slate-800">
                    <div class="auth-box h-full flex flex-col justify-center">
                        <div class="mobile-logo text-center mb-6 lg:hidden block">
                            <a href="#">
                                <img src="assets/images/logo/Logo-Black.png" alt="" class="custom-img mb-10 dark_logo">
                                <img src="assets/images/logo/logo-white.png" alt="" class="custom-img mb-10 white_logo">
                            </a>
                        </div>
                        <div class="text-center 2xl:mb-10 mb-4">
                            <h4 class="font-medium">Sign in</h4>
                            <div class="text-slate-500 text-base">
                                Sign in to continue to Parkomate Dashboard.
                            </div>
                            <br>

                            @if ($errors->any())
                            @foreach ($errors->all() as $error)
                            <div class="text-slate-500 text-base" style="color: red;">
                                {{ $error }}
                            </div>
                            @endforeach
                            @endif

                        </div>
                        <!-- BEGIN: Login Form -->
                        <form class="space-y-4" method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="fromGroup">
                                <label class="block capitalize form-label">username</label>
                                <div class="relative ">
                                    <input type="text" name="email" class="form-control py-2" placeholder="Enter Username" value="{{ old('email') }}" required autofocus>
                                </div>
                            </div>
                            <div class="fromGroup">
                                <label class="block capitalize form-label">password</label>
                                <div class="relative "><input type="password" name="password" class="form-control py-2" placeholder="Enter Password" value="{{ old('password') }}" required>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <label class="flex items-center cursor-pointer">
                                    <!-- <input type="checkbox" class="hiddens"> -->
                                    <!-- <span class="text-slate-500 dark:text-slate-400 text-sm leading-6 capitalize">Keep me signed in</span> -->
                                </label>
                                <a class="text-sm text-slate-800 dark:text-slate-400 leading-6 font-medium" href="forget-password-one.html">Forgot
                                    Password?
                                </a>
                            </div>
                            <input type="hidden" name="user" value="dashboard">
                            <button class="btn btn-dark block w-full text-center">Sign in</button>
                        </form>
                        <!-- END: Login Form -->

                    </div>
                    <div class="auth-footer text-center">
                        © <script>
                            document.write(new Date().getFullYear())
                        </script> Parkomate Solutions LLP.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- scripts -->
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/rt-plugins.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>