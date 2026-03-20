<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the table for site-wide news and alerts.
 *
 * Admins use these to communicate with all users at once.
 * This is perfect for maintenance notices or sharing
 * exciting new features in FitTrack.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');

            $table->text('message');

            // We default to 'true' so that as soon as an admin
            // saves a message, it is immediately visible to users.
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
