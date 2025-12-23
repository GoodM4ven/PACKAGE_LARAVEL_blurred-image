<div class="mx-auto max-w-4xl p-6 text-slate-900 sm:p-10">
    <!-- Reset -->
    <div class="mx-auto mb-5 flex w-full">
        <button
            class="mx-auto rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400"
            type="button"
            wire:click="resetGrid"
            @class(['ring-2 ring-emerald-300/50' => count($cells) > 0])
            @disabled(count($cells) === 0)
        >Reset grid</button>
    </div>

    <!-- Clicker Grid -->
    <div
        class="relative grid justify-center justify-items-center gap-4 overflow-hidden transition-all duration-500"
        style="grid-template-columns: repeat({{ $this->columnCount }}, 120px);"
    >
        <div
            class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center opacity-0"
            data-flash
        >
            <div class="h-24 w-24 rounded-full bg-emerald-300/40 blur-3xl"></div>
        </div>

        <!-- Buttons -->
        @foreach ($this->tiles as $tile)
            @php
                $isClickable = $revealed || $tile['origin'];
            @endphp
            <button
                data-grid-tile
                data-tile-key="{{ $tile['key'] }}"
                data-filled="{{ $tile['value'] ? 'true' : 'false' }}"
                type="button"
                wire:key="tile-{{ $tile['key'] }}"
                @if ($isClickable) wire:click="clickCell({{ $tile['x'] }}, {{ $tile['y'] }})" @endif
                @class([
                    'relative flex aspect-square w-full max-w-[120px] items-center justify-center overflow-hidden rounded-2xl border text-center text-lg font-semibold transition-all duration-500 will-change-transform grid-tile',
                    'bg-emerald-400 text-emerald-950 border-emerald-200 shadow-[0_18px_48px_rgba(16,185,129,0.35)] ring-1 ring-emerald-200/90' =>
                        $tile['value'],
                    'bg-slate-50 text-slate-600 border-slate-200 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 hover:shadow-[0_12px_36px_rgba(16,185,129,0.18)]' =>
                        !$tile['value'] && $isClickable,
                    'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed opacity-60' => !$isClickable,
                    'opacity-100 scale-100' => true,
                    'ring-1 ring-emerald-100' => $tile['origin'],
                ])
            >
                <div class="bg-linear-to-br absolute inset-0 from-emerald-50/50 via-transparent to-white/50"></div>
                <div class="relative flex h-full w-full flex-col items-center justify-center gap-1">
                    <span class="text-3xl font-black tracking-tight">
                        {{ $tile['value'] ?? '—' }}
                    </span>
                    <span class="text-xs uppercase tracking-[0.3em] text-slate-500">
                        {{ $tile['value'] ? 'Locked' : 'Tap to add' }}
                    </span>
                </div>
            </button>
        @endforeach
    </div>
</div>

@push('injections')
    <script>
        document.addEventListener('livewire:initialized', () => {
            let animate = null;

            if (typeof window.Animate === 'function') {
                animate = window.Animate;
            } else if (window.Animate && typeof window.Animate.default === 'function') {
                animate = window.Animate.default;
            } else if (typeof window.anime === 'function') {
                animate = window.anime;
            } else if (window.anime && typeof window.anime.default === 'function') {
                animate = window.anime.default;
            }

            const flash = () => {
                if (typeof animate !== 'function') {
                    return;
                }

                const flashEl = document.querySelector('[data-flash]');

                if (!flashEl) {
                    return;
                }

                flashEl.style.opacity = '1';

                const glow = flashEl.querySelector('div');

                animate(
                    glow, {
                        scale: [0.4, 3.6],
                        opacity: [0.8, 0],
                    }, {
                        duration: 560,
                        easing: 'easeOutQuad',
                        complete: () => {
                            flashEl.style.opacity = '0';
                        },
                    },
                );
            };

            const popIn = () => {
                if (typeof animate !== 'function') {
                    return;
                }

                animate(
                    document.querySelectorAll('[data-grid-tile]'), {
                        opacity: [0, 1],
                        scale: [0.6, 1],
                        translateY: [16, 0],
                    }, {
                        delay: (_element, index) => 60 * index,
                        duration: 520,
                        easing: 'easeOutBack',
                    },
                );
            };

            if (@js($revealed)) {
                requestAnimationFrame(popIn);
            }

            Livewire.on('grid-revealed', () => {
                popIn();
                flash();
            });

            Livewire.on('cell-colored', (payload) => {
                const id = payload?.id ?? payload;
                const tile = document.querySelector(`[data-tile-key="${id}"]`);

                if (typeof animate === 'function' && tile) {
                    animate(
                        tile, {
                            boxShadow: ['0 0 0 rgba(0,0,0,0)', '0 16px 60px rgba(34,197,94,0.28)'],
                        }, {
                            duration: 360,
                            easing: 'easeOutQuad',
                        },
                    );
                }
            });
        });
    </script>
@endpush
