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
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropIndex(['user_id']); // Drops the index if you rollback
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
