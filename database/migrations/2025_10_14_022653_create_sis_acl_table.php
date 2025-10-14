<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sis_acl', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->unsignedInteger('id')->autoIncrement();
            $table->unsignedInteger('group_id')->default(0);
            $table->unsignedInteger('page_id')->default(0);
            $table->string('acl_level', 45)->default('ro');

            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sis_acl');
    }
};
