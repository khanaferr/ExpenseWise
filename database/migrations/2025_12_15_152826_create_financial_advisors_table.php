<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
        {
            Schema::create('financial_advisors', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->string('certification_id');
                $table->decimal('hourly_rate', 8, 2)->nullable();
                $table->timestamps();

                $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_advisors');
    }
};
