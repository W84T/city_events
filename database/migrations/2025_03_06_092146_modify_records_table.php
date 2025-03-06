<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyRecordsTable extends Migration
{
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn(['classification', 'subsector']);

            $table->string('exhibition')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->string('classification')->nullable();
            $table->string('subsector')->nullable();
            $table->dropColumn('exhibition');
        });
    }
}
