<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sis_group', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->unsignedInteger('id')->autoIncrement();
            $table->string('group_name', 45);
            $table->unsignedInteger('ordering')->default(1);

            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sis_group');
    }
};
