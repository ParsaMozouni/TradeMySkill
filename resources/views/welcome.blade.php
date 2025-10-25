<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SkillSwap') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    {{-- Tailwind (Laravel welcome already ships styles; we keep utilities) --}}
    <style>
        :root { --brand:#111827; --brand-2:#0ea5e9; --brand-3:#22c55e; }
        .text-gradient {
            background: linear-gradient(90deg, var(--brand-2), var(--brand-3));
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .bg-grid {
            background-image:
                radial-gradient(rgba(0,0,0,0.06) 1px, transparent 1px);
            background-size: 18px 18px;
        }
        @media (prefers-color-scheme: dark) {
            .bg-grid { background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px); }
        }
    </style>
</head>
<body class="min-h-screen antialiased bg-white text-[#1b1b18] dark:bg-neutral-950 dark:text-neutral-100">
{{-- Top nav --}}
<header class="sticky top-0 z-20 backdrop-blur bg-white/70 dark:bg-neutral-950/60 border-b border-zinc-200 dark:border-neutral-800">
    <div class="mx-auto max-w-6xl px-4 py-3 flex items-center justify-between">
        <a href="/" class="inline-flex items-center gap-2">
                <span class="inline-grid place-items-center w-9 h-9 rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-black">
                    Logo
                </span>
            <span class="font-semibold tracking-tight">{{ config('app.name', 'Trade My Skill') }}</span>
        </a>

        @if (Route::has('login'))
            <nav class="flex items-center gap-2">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="hidden sm:inline-block px-4 py-2 rounded-lg border border-zinc-300 dark:border-neutral-700 hover:bg-zinc-50 dark:hover:bg-neutral-900 transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 rounded-lg hover:bg-zinc-50 dark:hover:bg-neutral-900 transition">
                        Log in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="px-4 py-2 rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-black hover:opacity-90 transition">
                            Get started
                        </a>
                    @endif
                @endauth
            </nav>
        @endif
    </div>
</header>

{{-- Hero --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-grid"></div>
    <div class="absolute -top-40 -left-40 w-[36rem] h-[36rem] rounded-full bg-sky-400/10 blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 w-[36rem] h-[36rem] rounded-full bg-emerald-400/10 blur-3xl"></div>

    <div class="mx-auto max-w-6xl px-4 py-16 lg:py-24 relative">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                    <span class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full border border-zinc-200 dark:border-neutral-800">
                        🌍 Local & friendly · 🔒 Privacy-minded
                    </span>
                <h1 class="mt-4 text-4xl/tight sm:text-5xl/tight font-semibold">
                    Learn something new. <span class="text-gradient">Teach what you know.</span>
                </h1>
                <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                    Discover neighbors with the skills you want, share the ones you have, and trade knowledge safely.
                    Create a listing, match by skills, chat, and meet in a public spot—no exact address required.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="px-5 py-3 rounded-xl bg-zinc-900 text-white dark:bg-white dark:text-black hover:opacity-90 transition font-medium">
                            Create your free profile
                        </a>
                    @endif
                    <a href="{{ route('login') }}"
                       class="px-5 py-3 rounded-xl border-2 border-zinc-900 text-zinc-900 hover:bg-zinc-900 hover:text-white transition font-medium
                                  dark:border-white dark:text-white dark:hover:bg-white dark:hover:text-black">
                        Explore listings
                    </a>
                </div>

                <div class="mt-6 flex items-center gap-6 text-sm text-zinc-600 dark:text-zinc-400">
                    <div class="flex items-center gap-2">
                        ✅ Skill-based matching
                    </div>
                    <div class="flex items-center gap-2">
                        ✅ Built-in chat
                    </div>
                    <div class="flex items-center gap-2">
                        ✅ Map with location
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="rounded-2xl border border-zinc-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-4 shadow-sm">
                    {{-- Mocked listing card preview --}}
                    <div class="rounded-xl border border-zinc-200 dark:border-neutral-700 p-4">
                        <div class="flex items-start gap-3">
                            <img alt="User" class="h-10 w-10 rounded-full ring-1 ring-zinc-200 dark:ring-neutral-700"
                                 src="https://ui-avatars.com/api/?name=Alex+M&background=111&color=fff" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-zinc-800 dark:text-zinc-100 line-clamp-2">
                                    I can help with <strong>Guitar Basics</strong> and would love to learn
                                    <strong>Cooking (Persian)</strong>. Weekend afternoons work best!
                                </p>
                                <p class="mt-1 text-xs text-zinc-500">
                                    Wants to learn: Cooking · Food & Culture
                                </p>
                                <div class="mt-3 flex justify-end gap-2">
                                    <button class="px-3 py-1 text-xs rounded-lg border">Details</button>
                                    <button class="px-3 py-1 text-xs rounded-lg bg-zinc-900 text-white">Chat</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tiny map mock --}}
                    <div class="mt-3 rounded-xl h-40 border border-dashed border-zinc-300 dark:border-neutral-700 grid place-items-center text-xs text-zinc-500">
                        Approximate area map
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section class="mx-auto max-w-6xl px-4 py-14">
    <h2 class="text-2xl font-semibold">Why you’ll love it</h2>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 p-5">
            <div class="text-2xl">🧭</div>
            <h3 class="mt-3 font-semibold">Skill-first matching</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Filter by category, sub-skill, and show listings that match your abilities.
            </p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 p-5">
            <div class="text-2xl">💬</div>
            <h3 class="mt-3 font-semibold">Built-in messenger</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Chat in-app, see read receipts, and coordinate a first exchange easily.
            </p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 p-5">
            <div class="text-2xl">🗺️</div>
            <h3 class="mt-3 font-semibold">Privacy-friendly map</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Share an approximate area, not your exact address.
            </p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 p-5">
            <div class="text-2xl">🤝</div>
            <h3 class="mt-3 font-semibold">Local community</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Meet neighbors, build trust, and grow a culture of helping.
            </p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 p-5">
            <div class="text-2xl">⚡</div>
            <h3 class="mt-3 font-semibold">Quick start</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Create a profile, add skills, post a listing—done in minutes.
            </p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 p-5">
            <div class="text-2xl">🎯</div>
            <h3 class="mt-3 font-semibold">No fees</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Skill-for-skill exchanges. Your time and knowledge are the currency.
            </p>
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="mx-auto max-w-6xl px-4 py-14">
    <h2 class="text-2xl font-semibold">How it works</h2>
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 p-5">
            <span class="text-xs px-2 py-1 rounded-full border">Step 1</span>
            <h3 class="mt-3 font-semibold">Create your profile</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Add skills you can offer and what you want to learn.</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 p-5">
            <span class="text-xs px-2 py-1 rounded-full border">Step 2</span>
            <h3 class="mt-3 font-semibold">Browse local listings</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Use filters and categories to find a great match.</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 p-5">
            <span class="text-xs px-2 py-1 rounded-full border">Step 3</span>
            <h3 class="mt-3 font-semibold">Message & meet</h3>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Chat in-app and meet in a public place to exchange.</p>
        </div>
    </div>
</section>

{{-- CTA banner --}}
<section class="mx-auto max-w-6xl px-4 pb-16">
    <div class="rounded-2xl p-6 md:p-10 border border-zinc-200 dark:border-neutral-800 bg-zinc-50 dark:bg-neutral-900">
        <div class="grid md:grid-cols-2 gap-6 items-center">
            <div>
                <h3 class="text-2xl font-semibold">Ready to share what you know?</h3>
                <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                    Join neighbors trading guitar for cooking, English for coding, and more.
                </p>
            </div>
            <div class="flex md:justify-end items-center gap-3">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="px-5 py-3 rounded-xl bg-zinc-900 text-white dark:bg-white dark:text-black hover:opacity-90 transition font-medium">
                        Create an account
                    </a>
                @endif
                <a href="{{ route('login') }}"
                   class="px-5 py-3 rounded-xl border-2 border-zinc-900 text-zinc-900 hover:bg-zinc-900 hover:text-white transition font-medium
                              dark:border-white dark:text-white dark:hover:bg-white dark:hover:text-black">
                    I already have one
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="border-t border-zinc-200 dark:border-neutral-800">
    <div class="mx-auto max-w-6xl px-4 py-8 text-sm text-zinc-600 dark:text-zinc-400 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p>© {{ date('Y') }} {{ config('app.name', 'SkillSwap') }}. All rights reserved.</p>
        <div class="flex items-center gap-4">
            <a href="#" class="hover:underline">About</a>
            <a href="#" class="hover:underline">Contact</a>
            <a href="#" class="hover:underline">Privacy</a>
        </div>
    </div>
</footer>
</body>
<!-- Tailwind CDN (dev only) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'media',
        theme: {
            extend: {
                fontFamily: { sans: ['"Instrument Sans"', 'ui-sans-serif', 'system-ui'] },
                colors: { brand: '#111827' }
            }
        }
    }
</script>
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
</html>
