<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Budget' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-white shadow-sm px-6 py-4 flex items-center gap-6">
        <a href="/" class="text-xl font-bold text-indigo-600 tracking-tight">Budget</a>
        <div class="flex gap-4 text-sm">
            <a href="{{ route('rekeningen.index') }}" class="text-gray-600 hover:text-indigo-600 transition-colors {{ request()->is('rekeningen*') ? 'text-indigo-600 font-medium' : '' }}">Rekeningen</a>
            <a href="{{ route('transacties.index') }}" class="text-gray-600 hover:text-indigo-600 transition-colors {{ request()->is('transacties*') ? 'text-indigo-600 font-medium' : '' }}">Transacties</a>
            <a href="{{ route('categorieen.index') }}" class="text-gray-600 hover:text-indigo-600 transition-colors {{ request()->is('categorieen*') ? 'text-indigo-600 font-medium' : '' }}">Categorieën</a>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-6 py-10">

        @if(session('succes'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                {{ session('succes') }}
            </div>
        @endif

        {{ $slot }}

    </div>

</body>
</html>
