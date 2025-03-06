<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyAssociationsTable extends Migration
{
    public function up(): void
    {
        Schema::table('associations', function (Blueprint $table) {
            $table->enum('type', ['resource', 'sector', 'exhibition'])->default('exhibition')->change();
        });
    }

    public function down(): void
    {
        Schema::table('associations', function (Blueprint $table) {
            $table->enum('type', ['classification', 'resource', 'sector', 'sub_sector'])->default('classification')->change();
        });
    }
}
