<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Countland extends Component
{
    // TODO rework for a proper blurred-image demo

    public function render(): View
    {
        return view('livewire.countland');
    }
}
