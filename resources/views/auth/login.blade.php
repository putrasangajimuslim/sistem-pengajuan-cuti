<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('img/default-image.png') }}" type="image/x-icon">
    <title>Login Page</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #F5F8FE;
            font-family: Arial, sans-serif;
            overflow: hidden; /* Prevent scrollbar when SweetAlert2 appears */
        }
        .login-container {
            position: fixed; /* Keep the container fixed in position */
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Translate to center */
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            z-index: 10; /* Ensure proper stacking order */
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .login-container input {
            width: 100%;
            padding: 13px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #4469D7;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <img src="{{ asset('img/undraw_profile.svg') }}" alt="logo" style="width: 40%; padding: 20px;">
        <form id="loginForm">
            <input type="text" placeholder="Email" name="email" id="email" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <i class="fas fa-fw fa-eye-slash" id="togglePassword" style="position: absolute; top: 78%; right: 33px; transform: translateY(-50%); cursor: pointer;"></i>
            <button type="button" id="loginButton" disabled>Login</button>
        </form>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
            
            $('#togglePassword').click(function() {
                const passwordField = $('#password');
                const passwordFieldType = passwordField.attr('type');
                if (passwordFieldType === 'password') {
                    passwordField.attr('type', 'text');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            function checkFormValidity() {
                const isFormValid = $('#loginForm')[0].checkValidity();
                
                $('#loginButton').prop('disabled', !isFormValid);
            }

            $('#loginForm').on('input change', function () {
                checkFormValidity();
            });

            $('#loginButton').click(function () {
                const email = $('#email').val();
                const password = $('#password').val();
                const token = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url: '{{ route('login.post') }}',
                    method: 'POST',
                    data: {
                        _token: token,
                        email: email,
                        password: password,
                    },
                    success: function (response) {
                        $('#loginButton').text('Login').prop('disabled', true);

                        Toast.fire({
                            text: response.message,
                            icon: "success"
                        });
                        setTimeout(function () {
                            window.location.href = response.redirect_url;
                        }, 2000);
                    },
                    error: function (xhr) {
                        const error = xhr.responseJSON.message;

                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "Periksa Kembali Email Dan Password!",
                        });
                    },
                });
            });

            checkFormValidity();
        });
      </script>
</body>
</html>
