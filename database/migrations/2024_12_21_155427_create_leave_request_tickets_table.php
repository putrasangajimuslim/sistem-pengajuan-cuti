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
        Schema::create('leave_request_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('no_ticket')->nullable();
            $table->string('npp')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('years')->nullable();
            $table->string('reason')->nullable();
            $table->integer('total_days')->nullable();
            $table->string('status')->nullable();
            $table->text('media_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_tickets');
    }
};
