<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sis_page', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->unsignedInteger('id')->autoIncrement();
            $table->string('name', 100);
            $table->string('title', 100);
            $table->string('link', 255);
            $table->unsignedInteger('parent_id')->default(0);
            $table->unsignedSmallInteger('ordering');
            $table->string('icon', 100);
            $table->string('description', 255)->nullable();
            $table->unsignedSmallInteger('enabled');
            $table->unsignedSmallInteger('is_menu');
            $table->unsignedInteger('application_id');

            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sis_page');
    }
};
