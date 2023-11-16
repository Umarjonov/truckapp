<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->text('rank')->nullable();
            $table->unsignedBigInteger('company_id'); // Add this line
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ranks');
    }
};
