<x-layouts.app title="Budget">

    {{-- Banner --}}
    <div class="budget-banner">
        <div class="budget-banner-top">
            <div class="budget-banner-icoon">
                <i class="fa-solid fa-euro-sign"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight" style="color:#f5e9c0">Budget</h1>
                <p class="text-sm mt-1" style="color:#8a7040">Beheer je rekeningen, transacties en categorieën.</p>
            </div>
        </div>

        {{-- Stadssilhouet ------------------------------------------------ --}}
        @php
            $gebouwen = [
                ['h'=>55,  'w'=>38],
                ['h'=>90,  'w'=>26],
                ['h'=>70,  'w'=>42],
                ['h'=>100, 'w'=>30],
                ['h'=>60,  'w'=>36],
                ['h'=>85,  'w'=>24],
                ['h'=>45,  'w'=>44],
                ['h'=>95,  'w'=>28],
                ['h'=>75,  'w'=>38],
                ['h'=>50,  'w'=>32],
                ['h'=>88,  'w'=>26],
                ['h'=>65,  'w'=>40],
                ['h'=>110, 'w'=>22],
                ['h'=>58,  'w'=>36],
                ['h'=>80,  'w'=>30],
                ['h'=>48,  'w'=>44],
                ['h'=>92,  'w'=>26],
                ['h'=>62,  'w'=>38],
            ];
        @endphp
        <div class="budget-skyline" aria-hidden="true">
            @foreach($gebouwen as $g)
                <div class="budget-gebouw" style="height:{{ $g['h'] }}px;width:{{ $g['w'] }}px"></div>
            @endforeach
        </div>
    </div>

    {{-- Navigatiekaarten --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        <a href="{{ route('rekeningen.index') }}" class="budget-kaart group">
            <div class="budget-kaart-icoon" style="background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.25)">
                <i class="fa-solid fa-building-columns" style="color:#818cf8;font-size:1.2rem"></i>
            </div>
            <div class="font-bold text-tekst group-hover:text-accent transition-colors">Rekeningen</div>
            <div class="text-sm text-gedempt leading-relaxed">Beheer je betaal- en spaarrekeningen</div>
        </a>

        <a href="{{ route('transacties.index') }}" class="budget-kaart group">
            <div class="budget-kaart-icoon" style="background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.22)">
                <i class="fa-solid fa-arrow-right-arrow-left" style="color:#34d399;font-size:1.1rem"></i>
            </div>
            <div class="font-bold text-tekst group-hover:text-accent transition-colors">Transacties</div>
            <div class="text-sm text-gedempt leading-relaxed">Inkomsten en uitgaven bijhouden</div>
        </a>

        <a href="{{ route('categorieen.index') }}" class="budget-kaart group">
            <div class="budget-kaart-icoon" style="background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.22)">
                <i class="fa-solid fa-tags" style="color:#fbbf24;font-size:1.1rem"></i>
            </div>
            <div class="font-bold text-tekst group-hover:text-accent transition-colors">Categorieën</div>
            <div class="text-sm text-gedempt leading-relaxed">Categorieën aanmaken en beheren</div>
        </a>

    </div>

</x-layouts.app>
