<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grid_cells', function (Blueprint $table): void {
            $table->id();
            $table->integer('x');
            $table->integer('y');
            $table->unsignedInteger('label');
            $table->timestamps();

            $table->unique(['x', 'y']);
            $table->unique('label');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grid_cells');
    }
};
