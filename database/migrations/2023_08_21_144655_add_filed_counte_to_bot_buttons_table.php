<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bot_buttons', function (Blueprint $table) {
            $table->integer('count')->nullable()->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('bot_buttons', function (Blueprint $table) {
            $table->dropColumn('count');
        });
    }
};
