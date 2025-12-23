import { Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { animate } from 'animejs';
import intersect from '@alpinejs/intersect';

window.Alpine = Alpine;
window.Animate = animate;

Alpine.plugin(intersect);

document.addEventListener('DOMContentLoaded', () => {
    if (document.documentElement.classList.contains('js-ready')) {
        return;
    }

    document.documentElement.classList.add('js-ready');
});
