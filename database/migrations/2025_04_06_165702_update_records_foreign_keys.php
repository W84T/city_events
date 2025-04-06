<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {

            $table->dropColumn(['resource', 'sector', 'exhibition']);

            $table->foreignId('resource_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->foreignId('sector_id')->nullable()->constrained('associations')->nullOnDelete();
            $table->foreignId('exhibition_id')->nullable()->constrained('associations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
