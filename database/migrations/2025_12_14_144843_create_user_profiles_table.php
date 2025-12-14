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
    Schema::create('user_profiles', function (Blueprint $table) {
        $table->unsignedBigInteger('id')->primary(); 
        $table->string('currency')->default('USD');
        $table->decimal('monthly_budget_limit', 15, 2)->nullable();
        $table->timestamps();

        $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
