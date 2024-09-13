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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->string('group_pic')->default('default_group_pic.png');
            $table->string('group_cover')->default('default_group_cover.png');
            $table->string('conditions')->nullable();
            $table->boolean('chat_open')->default(true);

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Add foreign key constraint
            $table->boolean('open')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
