<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sis_level', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->unsignedInteger('id')->autoIncrement();
            $table->string('title', 45);
            $table->unsignedInteger('grade')->default(0);

            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sis_level');
    }
};
