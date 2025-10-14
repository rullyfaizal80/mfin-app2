<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sis_user', function (Blueprint $table) {
            // Charset disamakan dengan DB lama
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->increments('id');
            $table->string('username', 45)->nullable();
            $table->string('password', 100)->nullable();
            $table->string('fullname', 45);
            $table->string('nickname', 45)->nullable();
            $table->string('placeofbirth', 45)->nullable();
            $table->date('dateofbirth')->nullable();
            $table->text('street')->nullable();
            $table->string('city', 45)->nullable();
            $table->string('province', 45)->nullable();
            $table->string('country', 45)->nullable();
            $table->string('postalcode', 45)->nullable();
            $table->string('gender', 1)->nullable();
            $table->string('home_phone', 45)->nullable();
            $table->string('mobile_phone', 45)->nullable();
            $table->string('email', 45)->nullable();
            $table->string('bank_acc', 45)->default('0');
            $table->string('religion', 45)->nullable();
            $table->string('is_active', 45)->default('yes');
            $table->integer('paid_by')->default(0);
            $table->string('user_type', 45)->nullable();
            $table->string('is_student', 3)->default('no');
            $table->string('is_parent', 3)->default('no');
            $table->string('is_teacher', 3)->default('no');
            $table->string('is_educator', 3)->default('no');
            $table->string('is_admin', 3)->default('no');
            $table->string('activation', 255)->nullable();
            $table->string('photo', 255)->nullable();
            $table->integer('update_by')->default(0);
            $table->dateTime('created')->nullable();
            $table->dateTime('updated')->nullable();
            $table->string('contact_name', 45)->nullable();
            $table->string('contact_mobile', 45)->nullable();
            $table->string('contact_email', 45)->nullable();
            $table->string('contact_posisi', 45)->nullable();
            $table->string('contact_mobile1', 45)->nullable();
            $table->string('contact_email1', 45)->nullable();
            $table->string('contact_name1', 45)->nullable();
            $table->string('contact_posisi1', 45)->nullable();
            $table->string('office_phone', 45)->nullable();
            $table->string('is_company', 3)->default('no');

            // Primary key
            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sis_user');
    }
};
