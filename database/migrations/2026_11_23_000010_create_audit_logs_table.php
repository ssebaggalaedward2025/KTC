<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only audit trail (spatie/laravel-activitylog, table renamed).
 *
 * In production the application's DB user must NOT have UPDATE or DELETE
 * on this table. See docker/mysql/grants.sql and docs/DEPLOYMENT.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            $table->nullableMorphs('subject', 'audit_subject');
            $table->string('event')->nullable()->index();
            $table->nullableMorphs('causer', 'audit_causer');
            $table->json('attribute_changes')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            // Tamper evidence: each row hashes its own content plus the previous row's hash.
            $table->char('prev_hash', 64)->nullable();
            $table->char('row_hash', 64)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
