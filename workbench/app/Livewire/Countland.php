<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Countland extends Component
{
    private const GRID_WIDTH = 5;
    private const GRID_HEIGHT = 3;

    public bool $revealed = false;

    /** @var array<string, int> */
    public array $cells = [];

    public function mount(): void
    {
        if (! Schema::hasTable('grid_cells')) {
            return;
        }

        $records = DB::table('grid_cells')->get();

        foreach ($records as $record) {
            $this->cells[$this->cellKey((int) $record->x, (int) $record->y)] = (int) $record->label;
        }

        $this->revealed = $this->cells !== [];
    }

    public function clickCell(int $x, int $y): void
    {
        if ($this->cellsCount() >= $this->maxCells() || $this->isOutsideGrid($x, $y)) {
            return;
        }

        $key = $this->cellKey($x, $y);

        if (isset($this->cells[$key])) {
            return;
        }

        $label = $this->randomLabel();
        $this->cells[$key] = $label;

        $this->persistCell($x, $y, $label);

        if (! $this->revealed) {
            $this->revealed = true;
            $this->dispatch('grid-revealed');
        }

        $this->dispatch('cell-colored', id: $key);
    }

    #[Computed]
    /**
     * @return list<array{x:int,y:int,key:string,value:int|null,origin:bool}>
     */
    public function tiles(): array
    {
        $tiles = [];

        foreach ($this->xCoordinates() as $x) {
            for ($y = 0; $y < self::GRID_HEIGHT; $y++) {
                $key = $this->cellKey($x, $y);
                $originRow = (int) floor(self::GRID_HEIGHT / 2);

                $tiles[] = [
                    'x' => $x,
                    'y' => $y,
                    'key' => $key,
                    'value' => $this->cells[$key] ?? null,
                    'origin' => $x === 0 && $y === $originRow,
                ];
            }
        }

        return $tiles;
    }

    #[Computed]
    public function columnCount(): int
    {
        return self::GRID_WIDTH;
    }

    #[Computed]
    public function totalSlots(): int
    {
        return $this->maxCells();
    }

    public function resetGrid(): void
    {
        if (! Schema::hasTable('grid_cells')) {
            return;
        }

        DB::table('grid_cells')->delete();

        $this->cells = [];
        $this->revealed = false;
    }

    private function randomLabel(): int
    {
        $used = $this->cells;

        do {
            $candidate = random_int(100, 999);
        } while (in_array($candidate, $used, true));

        return $candidate;
    }

    private function persistCell(int $x, int $y, int $label): void
    {
        DB::table('grid_cells')->insert([
            'x' => $x,
            'y' => $y,
            'label' => $label,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function maxCells(): int
    {
        return self::GRID_WIDTH * self::GRID_HEIGHT;
    }

    private function isOutsideGrid(int $x, int $y): bool
    {
        return ! in_array($x, $this->xCoordinates(), true) || $y < 0 || $y >= self::GRID_HEIGHT;
    }

    private function cellsCount(): int
    {
        return count($this->cells);
    }

    /**
     * @return list<int>
     */
    private function xCoordinates(): array
    {
        $half = (int) floor(self::GRID_WIDTH / 2);

        return range(-$half, $half);
    }

    private function cellKey(int $x, int $y): string
    {
        return "{$x}:{$y}";
    }

    public function render(): View
    {
        return view('livewire.countland');
    }
}
