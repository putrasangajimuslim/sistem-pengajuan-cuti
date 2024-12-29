<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="icon" href="{{ asset('img/default-image.png') }}" type="image/x-icon">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"> --}}
    <style>
        .custom-button-size {
            height: 38px; /* Samakan tinggi dengan input */
            padding: 0 12px;
        }

        .custom-input-size {
            height: 38px; /* Tinggi input sesuai tombol */
        }

        .disabled-input {
            background-color: #fff; /* Warna latar belakang seperti input aktif */
            color: #212529;        /* Warna teks seperti input aktif */
            cursor: not-allowed;   /* Tanda panah saat hover */
            pointer-events: none;  /* Mencegah interaksi pengguna */
            border: 1px solid #ced4da; /* Border seperti input aktif */
        }

        .separator {
            height: 1px;                  /* Tinggi garis */
            background-color: #ccc;       /* Warna garis */
            margin-top: 10px;             /* Jarak atas dari elemen sebelumnya */
            margin-bottom: 10px;          /* Jarak bawah dari elemen berikutnya */
        }

        .vertical-stepper {
            display: flex;
            flex-direction: column;
            position: relative;
            margin-top: 1rem;
            padding-left: 20px;
        }

        .step {
            display: flex;
            align-items: flex-start;
            position: relative;
            margin-bottom: 1rem;
        }

        .step-marker {
            width: 20px;
            height: 20px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.8rem;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }

        .step-line {
            position: absolute;
            top: 0;
            left: 9px;
            width: 2px;
            height: calc(100% + 1rem);
            background-color: #007bff;
            z-index: 1;
        }

        .step-content {
            margin-left: 1.5rem;
        }

        .step-title {
            font-weight: bold;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .step-description {
            color: #6c757d;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .step-content {
                margin-left: 1rem;
            }
            .step-marker {
                width: 16px;
                height: 16px;
                font-size: 0.7rem;
            }
            .step-title {
                font-size: 0.9rem;
            }
            .step-description {
                font-size: 0.8rem;
            }
        }

        .support_document_container img {
            width: 250px;
            height: 250px;
            object-fit: cover;
            border: 1px solid #ccc;
            cursor: pointer;
        }
    </style>
</head>
@yield('body')
@yield('style')
@include('sweetalert::alert')
</html>