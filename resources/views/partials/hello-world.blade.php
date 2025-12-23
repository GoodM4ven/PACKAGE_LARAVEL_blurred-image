@pushOnce('injections')
    <link
        href="{{ asset('vendor/:package_slug/:package_slug.css') }}"
        rel="stylesheet"
    >
    <script src="{{ asset('vendor/:package_slug/:package_slug.js') }}"></script>
@endPushOnce

<div class="bg-linear-to-br mx-auto my-10 grid select-none place-items-center overflow-hidden">
    <div
        class="w-full rounded-2xl border border-slate-200 bg-white/80 backdrop-blur-xl transition-all duration-500 ease-[cubic-bezier(.22,1,.36,1)] will-change-transform active:rounded-2xl"
        x-bind:class="open ? 'max-w-lg border-transparent' : 'max-w-md border-slate-200'"
        x-data="{
            open: false,
            height: 0,
            init() {
                this.$watch('open', value => {
                    this.height = (value ? this.$refs.content.scrollHeight : 0) + 'px';
                });
            },
            toggle() {
                this.open = !this.open;
            }
        }"
    >
        <!-- Header -->
        <button
            class="group my-0.5 flex w-full cursor-pointer items-center justify-between rounded-2xl px-7 py-5 text-left focus:outline-none focus-visible:ring-slate-300 focus-visible:transition-all active:rounded-2xl"
            type="button"
            x-bind:class="open ? 'focus-visible:ring-[none]' : 'focus-visible:ring-2'"
            x-on:click.stop="toggle"
        >
            <div class="flex items-center gap-3">
                <span
                    class="grid h-9 w-9 place-items-center rounded-full bg-slate-900 text-sm text-white transition-transform duration-500 group-hover:scale-105"
                >👋</span><span class="text-lg font-semibold tracking-tight text-slate-900">Hello World</span>
            </div>

            <!-- Arrow -->
            <svg
                class="h-5 w-5 text-slate-500 transition-transform duration-500 ease-[cubic-bezier(.22,1,.36,1)]"
                x-bind:class="{ 'rotate-180': open }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </button>

        <!-- Animated Collapse -->
        <div
            class="overflow-hidden transition-all duration-500 ease-[cubic-bezier(.22,1,.36,1)] will-change-[width,height]"
            x-bind:style="{ height: height }"
        >
            <div
                class="px-7 pb-6 pt-1 transition-all duration-500 ease-[cubic-bezier(.22,1,.36,1)] will-change-[opacity,transform,filter]"
                x-ref="content"
                x-bind:class="open
                    ?
                    'opacity-100 translate-y-0 scale-100 blur-0' :
                    'opacity-0 -translate-y-2 scale-[0.98] blur-sm'"
            >
                <p class="text-sm leading-relaxed text-slate-600">Welcome to the <b>biggest package reveal!</b></p>

                <div class="mt-5 animate-pulse rounded-xl bg-slate-100/70 px-4 py-3 text-xs text-slate-500">
                    Counting down and all...
                </div>
            </div>
        </div>
    </div>
</div>
