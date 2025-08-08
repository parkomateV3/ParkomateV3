<!DOCTYPE html>
<!-- Source Codes By CodingNepal - www.codingnepalweb.com -->
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}" />
</head>

<body>
    <div class="login_form">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="color: red;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
        </div>
        @endif

        <!-- Login form container -->
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <h3>Admin Register</h3>


            <!-- Email input box -->
            <div class="input_box">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" placeholder="Enter name" value="{{ old('name') }}" required />
            </div>

            <div class="input_box">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter email address" value="{{ old('email') }}" required />
            </div>

            <!-- Paswwrod input box -->
            <div class="input_box">
                <div class="password_title">
                    <label for="password">Password</label>
                </div>

                <input type="password" id="password" name="password" placeholder="Enter your password" required />
            </div>

            <div class="input_box">
                <div class="password_title">
                    <label for="password">Confirm Password</label>
                </div>

                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Enter your password" required />
            </div>

            <!-- Login button -->
            <button type="submit">Log In</button>

        </form>
    </div>
</body>

</html>