<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('records', function (Blueprint $table) {
            $table->id();

            $table->string('resource_id')->constrained('associations')->nullOnDelete();;
            $table->string('sector_id')->constrained('associations')->nullOnDelete();;
            $table->string('exhibition_id')->constrained('associations')->nullOnDelete();;

            $table->text('title')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('gender')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->json('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
