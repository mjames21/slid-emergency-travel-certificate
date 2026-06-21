{{-- FILE: resources/views/partials/head.blade.php --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name', 'SLID Visa on Arrival Platform') }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])