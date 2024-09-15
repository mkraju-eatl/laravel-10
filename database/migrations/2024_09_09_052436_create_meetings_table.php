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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by');
            $table->string('meeting_serial');
            $table->string('meeting_id');
            $table->string('meeting_name');
            $table->string('moderator_password');
            $table->string('attendee_password');
            $table->string('moderator_url')->nullable();
            $table->string('attendee_url')->nullable();
            $table->boolean('record_permission')->default(true);
            $table->string('duration')->nullable();
            $table->enum('meeting_type', ['public', 'restricted'])->default('restricted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
