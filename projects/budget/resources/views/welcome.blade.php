<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Budget</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-white shadow-sm px-6 py-4 flex items-center">
        <span class="text-xl font-bold text-indigo-600 tracking-tight">
            <i class="fa-solid fa-wallet me-2"></i>Budget
        </span>
    </nav>

    <div class="max-w-5xl mx-auto px-6 py-10">
        <h1 class="text-2xl font-semibold text-gray-800 mb-1">Welkom terug</h1>
        <p class="text-gray-500 mb-8">Wat wil je doen?</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <a href="/rekeningen" class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all block">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <h2 class="font-semibold text-gray-800 mb-1">Rekeningen</h2>
                <p class="text-sm text-gray-500">Beheer je betaal- en spaarrekeningen</p>
            </a>

            <a href="/transacties" class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all block">
                <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                </div>
                <h2 class="font-semibold text-gray-800 mb-1">Transacties</h2>
                <p class="text-sm text-gray-500">Inkomsten en uitgaven bijhouden</p>
            </a>

            <a href="/categorieen" class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all block">
                <div class="w-12 h-12 rounded-xl bg-yellow-100 text-yellow-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <h2 class="font-semibold text-gray-800 mb-1">Categorieën</h2>
                <p class="text-sm text-gray-500">Categorieën aanmaken en beheren</p>
            </a>

        </div>
    </div>

</body>
</html>
