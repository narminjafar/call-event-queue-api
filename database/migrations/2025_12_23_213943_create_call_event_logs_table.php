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
        Schema::create('call_event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('call_id')->index();
            $table->string('event_type')->index();
            $table->json('payload');
            $table->timestamp('created_time')->useCurrent();
            
            $table->index(['call_id', 'event_type']);
            $table->index('created_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_event_logs');
    }
};
