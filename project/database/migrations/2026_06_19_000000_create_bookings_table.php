<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->string('status')->default('confirmed');
            $table->string('flight_id');
            $table->string('provider');
            $table->string('carrier');
            $table->string('flight_number');
            $table->string('from', 3);
            $table->string('to', 3);
            $table->dateTime('depart_at');
            $table->dateTime('arrive_at');
            $table->unsignedInteger('stops');
            $table->decimal('price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 3);
            $table->unsignedInteger('passenger_count');
            $table->json('passengers');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
