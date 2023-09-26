<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->default(null);
            $table->integer('code_attempts')->default(5);
            $table->string('app_id')->default(null)->nullable();
            $table->string('phone')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_verification');
    }
};
