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
        Schema::create('engagments', function (Blueprint $table) {
            $table->id();
            $table->string("question");
            $table->string("hub_content_id");
            $table->string("answer_type")->coment("could be multiple or single");
            $table->integer("hub_id");
            $table->integer("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagments');
    }
};
