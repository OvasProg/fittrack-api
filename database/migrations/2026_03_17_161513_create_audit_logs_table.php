<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the table for tracking sensitive admin actions.
 *
 * This table acts as a security camera for the app's backend. 
 * If an admin changes a user's role or deletes a plan, 
 * we record it here for accountability.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();

            // This stores a short tag for the action, 
            // like 'USER_DELETED' or 'PLAN_UPDATED'.
            $table->string('action');

            // We use a text column to store more detailed info, 
            // like the original and new values of a changed record.
            $table->text('details');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
