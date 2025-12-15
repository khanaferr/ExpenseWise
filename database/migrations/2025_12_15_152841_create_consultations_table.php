<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Client
            $table->foreignId('advisor_id')->constrained('financial_advisors')->onDelete('cascade'); // The Advisor
            
            $table->dateTime('scheduled_at');
            $table->string('status')->default('pending'); // pending, confirmed, completed
            $table->text('notes')->nullable(); // Advisor's advice
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
