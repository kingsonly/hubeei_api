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
        Schema::create('hub_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->date('expiration_date')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            // $table->string('additional_column');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hub_subscriptions');
    }
};
