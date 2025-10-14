<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sis_usergroup', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('group_id')->default(0);
            $table->unsignedInteger('user_id')->default(0);

            // Index (optional tapi membantu performa)
            $table->index('group_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sis_usergroup');
    }
};
