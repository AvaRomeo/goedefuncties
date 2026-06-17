<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inloggen — Kleine Projecten</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('tailwind.config.js') }}"></script>
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px; }
        .login-kaart {
            background: #1e242d;
            border: 1px solid #2d3540;
            border-radius: 20px;
            padding: 40px 36px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 64px rgba(0,0,0,.5);
        }
        .login-input {
            width: 100%;
            background: #131820;
            border: 1px solid #2d3540;
            border-radius: 10px;
            padding: 10px 14px;
            color: #e6e9ee;
            font-size: .9rem;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }
        .login-input:focus {
            border-color: #4cc38a;
            box-shadow: 0 0 0 3px rgba(76,195,138,.18);
        }
        .login-input.fout { border-color: #e06c75; }
        .login-label { display: block; font-size: .825rem; font-weight: 600; color: #8a94a2; margin-bottom: 6px; letter-spacing: .02em; text-transform: uppercase; }
        .login-btn {
            width: 100%;
            background: #4cc38a;
            color: #0d1f16;
            border: none;
            border-radius: 10px;
            padding: 11px;
            font-size: .95rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }
        .login-btn:hover { background: #2f9e6e; }
    </style>
</head>
<body>
    <div class="login-kaart">

        {{-- Logo --}}
        <div class="flex items-center gap-3 mb-8">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(76,195,138,.12);border:1px solid rgba(76,195,138,.2);display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="rgba(76,195,138,.9)">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
            </div>
            <div>
                <div style="font-weight:700;color:#e6e9ee;font-size:.95rem;line-height:1">Kleine Projecten</div>
                <div style="font-size:.75rem;color:#5a6472;margin-top:2px">Inloggen om door te gaan</div>
            </div>
        </div>

        @if($errors->any())
            <div style="background:rgba(224,108,117,.1);border:1px solid rgba(224,108,117,.3);border-radius:10px;padding:10px 14px;margin-bottom:20px;color:#e06c75;font-size:.85rem">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.opslaan') }}" class="flex flex-col gap-5">
            @csrf

            <div>
                <label class="login-label" for="email">E-mailadres</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="login-input {{ $errors->has('email') ? 'fout' : '' }}"
                    placeholder="naam@voorbeeld.nl">
            </div>

            <div>
                <label class="login-label" for="wachtwoord">Wachtwoord</label>
                <input type="password" id="wachtwoord" name="wachtwoord" required
                    class="login-input {{ $errors->has('wachtwoord') ? 'fout' : '' }}"
                    placeholder="••••••••">
            </div>

            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;color:#8a94a2">
                <input type="checkbox" name="onthoud" style="accent-color:#4cc38a;width:15px;height:15px">
                Onthoud mij
            </label>

            <button type="submit" class="login-btn">Inloggen</button>
        </form>

    </div>
</body>
</html>
