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
        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('required_action')->nullable();
            $table->integer('required_count')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreignId('battle_pass_id')
                ->references('id')
                ->on('battle_passes')
                ->onDelete('cascade');
            $table->dateTime('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quests');
    }
};
