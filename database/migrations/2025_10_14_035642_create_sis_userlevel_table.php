<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sis_userlevel', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('level_id')->default(0);
            $table->unsignedInteger('user_id')->default(0);

            // optional index untuk performa
            $table->index('level_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sis_userlevel');
    }
};
