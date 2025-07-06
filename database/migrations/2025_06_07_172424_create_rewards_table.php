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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('reward_points')->default(0);
            $table->string('reward_item')->nullable();
            $table->foreignId('quest_id')
                ->references('id')
                ->on('quests')
                ->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_claimed')->default(false);
            $table->dateTime('claimed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
