<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    //$P$BY2R7BZGW6ecxc2iEZJQE6/gemu7E./
    public function up(): void
    {
        Schema::create('hub_settings', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hub_settings');
    }
};
