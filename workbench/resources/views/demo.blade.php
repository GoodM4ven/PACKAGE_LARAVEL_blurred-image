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

<body
    class="min-h-screen bg-[radial-gradient(circle_at_20%_20%,rgba(56,189,248,0.12),transparent_30%),radial-gradient(circle_at_80%_0%,rgba(217,70,239,0.12),transparent_32%),linear-gradient(135deg,#0b1220_0%,#0b1220_40%,#0d1a2c_100%)] text-slate-100 antialiased"
>
    <main
        class="mx-auto flex max-w-6xl flex-col gap-8 px-5 py-12 md:px-8 lg:px-10"
        x-data
    >
        <header class="flex flex-wrap items-start justify-between gap-6">
            <div class="space-y-3">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-300"
                >
                    Workbench Demo
                </span>
                <h1 class="text-4xl font-bold tracking-tight md:text-5xl">Blurred Image Playground</h1>
                <p class="max-w-3xl text-base text-slate-400 md:text-lg">
                    Five variations: explicit assets, a dedicated profile media item, slot overlays, full-intersection
                    reveals, and intersection-delayed downloads.
                </p>
            </div>
        </header>

        <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-2">
            <article
                class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-2xl shadow-black/50 backdrop-blur"
            >
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-300"
                >
                    Explicit paths
                </span>
                <h3 class="mt-3 text-xl font-semibold">Static assets</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">
                    Load an eager blurred image from public assets with a manual thumbnail path.
                </p>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    Uses the published vendor bundle only (no Alpine bundling). Because display enforcement is on, the
                    blurhash and final image run immediately, falling back to the package placeholder if either path is
                    missing.
                </p>

                <div class="mt-4">
                    <x-goodmaven::blurred-image
                        data-testid="explicit-blur"
                        alt="Sunlit coastal cliffs"
                        :image-path="$imagePath"
                        :thumbnail-image-path="$thumbnailPath"
                        width-class="w-full max-w-xl"
                        height-class="h-[320px]"
                        container-classes="rounded-xl ring-1 ring-white/10 bg-gradient-to-br from-white/5 to-cyan-200/5 shadow-inner shadow-cyan-500/10"
                        image-classes="rounded-xl"
                        :is-display-enforced="true"
                    />
                </div>
            </article>

            <article
                class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-2xl shadow-black/50 backdrop-blur"
            >
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-indigo-200"
                >
                    Media Library
                </span>
                <h3 class="mt-3 text-xl font-semibold">Avatar from collection</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">
                    Reads the first <code class="font-mono text-xs text-indigo-200">profile</code> media item, now using
                    the dedicated portrait and its blurred thumbnail conversion.
                </p>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    Pulls from <code class="font-mono text-[11px] text-indigo-200">media-index="0"</code> on the profile
                    collection using the configured conversion name, so tweaking <code
                        class="font-mono text-[11px] text-indigo-200"
                    >conversion_name</code> in the config updates this
                    block without changing the template.
                </p>

                <div class="mt-4 flex items-center gap-4">
                    <x-goodmaven::blurred-image
                        data-testid="avatar-blur"
                        alt="Profile avatar"
                        :model="$demoUser"
                        collection="profile"
                        :media-index="0"
                        :is-display-enforced="true"
                        :conversion="$conversionName"
                        width-class="h-[220px] w-[220px]"
                        height-class="h-[220px]"
                        container-classes="rounded-2xl ring-1 ring-white/10 bg-gradient-to-br from-white/5 to-indigo-200/5 shadow-inner shadow-indigo-500/10"
                        image-classes="rounded-2xl"
                    />

                    <div class="space-y-1">
                        <p class="text-base font-semibold text-white">{{ $demoUser->name }}</p>
                        <p class="text-sm text-slate-400">Collection: profile</p>
                        <p class="text-sm text-slate-400">Conversion: {{ $conversionName }}</p>
                    </div>
                </div>
            </article>

            <article
                class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-2xl shadow-black/50 backdrop-blur"
            >
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-200"
                >
                    Slot overlay
                </span>
                <h3 class="mt-3 text-xl font-semibold">Inner content overlay</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">
                    A portrait frame that carries its own foreground content; tall enough to force scrolling so you can
                    watch the blurhash settle behind the slot content.
                </p>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    Slot content floats above both the canvas and the final image. Display enforcement keeps everything
                    visible while the blurhash hold timers finish, even if the frame cannot fully intersect the
                    viewport.
                </p>

                <div class="mt-4">
                    <x-goodmaven::blurred-image
                        data-testid="inner-content-blur"
                        alt="Forest canopy with sunlight"
                        :image-path="$innerContentImagePath"
                        :thumbnail-image-path="$innerContentThumbnailPath"
                        width-class="w-full"
                        height-class="h-[520px] md:h-[780px]"
                        container-classes="rounded-2xl ring-1 ring-white/10 bg-gradient-to-b from-white/5 to-emerald-200/5 shadow-inner shadow-emerald-500/10"
                        image-classes="rounded-2xl"
                        :is-display-enforced="true"
                    >
                        <div
                            class="absolute inset-0 bg-linear-to-t from-slate-950/65 via-slate-900/30 to-transparent">
                        </div>
                        <div class="relative flex h-full flex-col justify-between gap-6 p-6 md:p-8">
                            <div class="space-y-3">
                                <span
                                    class="inline-flex items-center gap-2 self-start rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/90"
                                >
                                    Inside the frame
                                </span>
                                <p class="text-2xl font-semibold text-white">Overlayed itinerary</p>
                                <p class="max-w-xl text-sm text-slate-200/80">
                                    Slot content rides on top of the blurhash and finished image, so you can mix text,
                                    controls, or other UI directly into the photo surface.
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white">
                                    Sunrise start
                                </span>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white">
                                    Ridge camp
                                </span>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white">
                                    Night drive
                                </span>
                            </div>
                        </div>
                    </x-goodmaven::blurred-image>
                </div>
            </article>

            <article
                class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-2xl shadow-black/50 backdrop-blur"
            >
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-amber-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-200"
                >
                    Fully intersected
                </span>
                <h3 class="mt-3 text-xl font-semibold">Reveal at the very end</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">
                    Eagerly fetch the original file, but wait until the tall portrait is fully inside the viewport
                    before the high-res pixels fade in.
                </p>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    The network request starts immediately via eager loading, yet the reveal waits for a full
                    intersection callback because display enforcement is off—useful for confirming the
                    <code class="font-mono text-[11px] text-amber-200">x-intersect:enter.full</code> gating.
                </p>

                <div class="mt-4">
                    <x-goodmaven::blurred-image
                        data-testid="fully-intersected-blur"
                        alt="Canyon silhouette"
                        :image-path="$intersectedFullyImagePath"
                        :thumbnail-image-path="$intersectedFullyThumbnailPath"
                        width-class="w-full"
                        height-class="h-[520px] md:h-[760px]"
                        container-classes="rounded-2xl ring-1 ring-white/10 bg-gradient-to-b from-white/5 to-amber-200/5 shadow-inner shadow-amber-500/10"
                        image-classes="rounded-2xl"
                        :is-eager-loaded="true"
                        :is-display-enforced="false"
                    />
                </div>
            </article>

            <article
                class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-2xl shadow-black/50 backdrop-blur md:col-span-2"
            >
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-fuchsia-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-fuchsia-200"
                >
                    Delayed download
                </span>
                <h3 class="mt-3 text-xl font-semibold">Blurhash now, bytes later</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">
                    The blurhash draws immediately while the original waits until the frame starts intersecting the
                    viewport. It spans the full grid to emphasize the staggered loading.
                </p>
                <p class="mt-3 text-xs leading-relaxed text-slate-500">
                    With eager loading and display enforcement both off, the high-res request does not fire until
                    intersection events arrive; the blurhash is decoded client-side from the thumbnail first so you can
                    spot any missing thumbnail edge cases.
                </p>

                <div class="mt-4">
                    <x-goodmaven::blurred-image
                        data-testid="delayed-download-blur"
                        alt="Foggy valley from above"
                        :image-path="$delayedImagePath"
                        :thumbnail-image-path="$delayedThumbnailPath"
                        width-class="w-full"
                        height-class="h-[420px] md:h-[520px]"
                        container-classes="rounded-2xl ring-1 ring-white/10 bg-gradient-to-br from-white/5 to-fuchsia-200/5 shadow-inner shadow-fuchsia-500/10"
                        image-classes="rounded-2xl object-top!"
                        :is-eager-loaded="false"
                        :is-display-enforced="false"
                    />
                </div>
            </article>
        </section>
    </main>

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
