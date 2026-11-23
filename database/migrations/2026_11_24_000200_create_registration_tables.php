<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            // Null for walk-in applicants registered by the Records Officer.
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('applicant_type', 20); // ApplicantType
            $table->string('name');
            $table->string('phone', 13);
            $table->string('email')->nullable();
            $table->text('nin')->nullable();               // encrypted cast
            $table->char('nin_hash', 64)->nullable()->index(); // HMAC for lookup without decrypting
            $table->string('company_registration_number', 50)->nullable()->index();
            $table->text('address');
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('plot_number', 50);
            $table->string('block_number', 50);
            // Normalised (upper-case alphanumerics) for the duplicate check: "Plot 12" == "12".
            $table->string('plot_key', 50);
            $table->string('block_key', 50);
            $table->foreignId('village_id')->constrained()->restrictOnDelete();
            $table->text('land_title_details')->nullable(); // encrypted cast
            $table->timestamps();

            $table->index(['block_key', 'plot_key']);
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number', 30)->unique(); // KTC/BP/2026/00125
            $table->string('bims_reference', 50)->nullable()->index();
            $table->foreignId('applicant_id')->constrained()->restrictOnDelete();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            // Appeals and amendments are new applications linked to the original.
            $table->foreignId('parent_application_id')->nullable()->constrained('applications')->restrictOnDelete();
            $table->date('application_date');
            // FK to workflow_stages is added in feature 4.
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->string('current_status', 40);
            $table->foreignId('assigned_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority', 10)->default('normal');
            $table->timestamp('stage_deadline')->nullable();
            $table->unsignedBigInteger('committee_sitting_id')->nullable(); // FK added with committee sittings
            $table->decimal('fee_estimate', 14, 2)->nullable();
            $table->string('public_tracking_pin_hash');
            $table->boolean('is_legacy')->default(false);
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('duplicate_warning_acknowledged')->default(false);
            $table->timestamp('submitted_at')->nullable();
            // Optimistic locking for concurrent edits.
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(['current_status', 'stage_deadline', 'assigned_officer_id'], 'applications_queue_index');
            $table->index('application_date');
        });

        Schema::create('building_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('building_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('land_use_id')->constrained()->restrictOnDelete(); // proposed use
            $table->unsignedSmallInteger('number_of_floors');
            $table->decimal('plot_size_m2', 12, 2);
            $table->decimal('floor_area_m2', 12, 2);
            $table->unsignedBigInteger('estimated_cost_ugx');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('application_professionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('role', 30); // ProfessionalRole
            $table->string('role_description', 100)->nullable(); // for "other"
            $table->string('name');
            $table->string('registration_number', 50)->nullable();
            $table->string('phone', 13)->nullable();
            $table->timestamps();
        });

        // One row per year; the row is locked while the next number is taken.
        Schema::create('application_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        // Autosaved wizard state. Becomes a real application only on submit.
        Schema::create('application_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 20)->default('self'); // self | walk_in
            $table->unsignedTinyInteger('step')->default(1);
            $table->text('data'); // encrypted: holds NIN and land title details
            $table->timestamps();
            $table->unique(['user_id', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_drafts');
        Schema::dropIfExists('application_sequences');
        Schema::dropIfExists('application_professionals');
        Schema::dropIfExists('building_plans');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('applicants');
    }
};
