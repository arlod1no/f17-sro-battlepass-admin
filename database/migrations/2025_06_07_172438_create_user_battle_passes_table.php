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
        Schema::create('user_battle_passes', function (Blueprint $table) {
            $table->id();

            // Liên kết với TB_User qua JID thay vì user_id
            $table->unsignedInteger('jid')->comment('Foreign key reference to TB_User.JID');

            $table->foreignId('battle_pass_id')
                ->references('id')
                ->on('battle_passes')
                ->onDelete('cascade');

            $table->integer('level')->default(0);
            $table->integer('experience')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('status')->default('active');
            $table->string('type')->default('standard');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->integer('total_levels')->default(100);
            $table->integer('total_experience')->default(0);
            $table->boolean('is_claimed')->default(false);
            $table->timestamp('claimed_at')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active_for_user')->default(true);
            $table->timestamps();

            // Tạo unique constraint để tránh user có nhiều battle pass giống nhau
            $table->unique(['jid', 'battle_pass_id'], 'unique_user_battle_pass');

            // Tạo index cho performance
            $table->index('jid', 'idx_user_battle_passes_jid');
            $table->index(['jid', 'is_active'], 'idx_user_battle_passes_jid_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_battle_passes');
    }
};
