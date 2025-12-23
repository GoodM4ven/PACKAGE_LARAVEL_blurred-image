@props(['caption'])

<div class="relative">
    <!-- Left Half -->
    <div class="absolute left-0 flex flex-col justify-center h-full w-1/2">
        <!-- Dashed Faded Line -->
        <div class="absolute w-full -z-10 border-2 border-gray-400 border-dashed"></div>
        <div class="absolute w-full z-10 h-5 bg-linear-to-r from-transparent via-white to-white"></div>

        <!-- Tag -->
        <div class="ms-10 relative z-20">
            <p class="text-xl px-3 py-2 bg-white italic border inline-flex w-fit">{{ $caption }}</p>
        </div>
    </div>

    <div class="relative z-30">
        {{ $slot }}
    </div>
</div>
