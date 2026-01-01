<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sistem Verifikasi Ijazah</title>

    <link rel="stylesheet" href="{{ asset('front/assets/css/akadify.css') }}">

    @livewireStyles
</head>
<body>

    <main>
        {{ $slot }}
    </main>

    @livewireScripts
    <script src="{{ asset('front/assets/js/akadify.js') }}"></script>
</body>
</html>
