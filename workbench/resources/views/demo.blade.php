<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta
        http-equiv="X-UA-Compatible"
        content="ie=edge"
    >
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >
    <title>Demo - Testing</title>

    <!-- Styles -->
    <link
        href="{{ asset('build/demo.css') }}"
        rel="stylesheet"
    >
    @livewireStyles
</head>

<body class="antialiased">
    <x-:package_slug::tech-line :caption="'Laravel & AlpineJS'">
        @include(':package_slug::partials.hello-world')
    </x-:package_slug::tech-line>

    <x-:package_slug::tech-line :caption="'Livewire & SQLite'">
        @livewire(':package_slug::countland')
    </x-:package_slug::tech-line>

    <!-- Bottom -->
    <div class="relative flex h-50">
        <div class="absolute inset-0 flex items-end justify-center">
            <!-- Faded Dashed vertical line -->
            <div class="absolute z-0 h-full border-r-2 border-dashed border-gray-400"></div>
            <div class="bg-linear-to-t absolute z-10 h-full w-3 from-transparent via-white/80 to-white"></div>

            <!-- Label -->
            <div class="relative z-20 mb-5 flex flex-col justify-end">
                <p class="inline-flex w-fit border bg-white px-3 py-2 text-xl italic">TailwindCSS</p>
            </div>
        </div>
    </div>

    <!-- Body Scripts -->
    <script src="{{ asset('build/demo.js') }}"></script>
    @env('local')
        @livewireScriptConfig
    @else
        @livewireScripts
    @endenv

    <!-- Injections -->
    @stack('injections')
</body>

</html>
