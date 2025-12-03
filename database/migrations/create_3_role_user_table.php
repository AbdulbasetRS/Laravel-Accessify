<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

return new class extends Migration
{
    public function up()
    {
        Schema::create(Config::get('accessify.tables.role_user'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->unique()  // Ensures one role per user
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('role_id')
                  ->constrained(Config::get('accessify.tables.roles'))
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(Config::get('accessify.tables.role_user'));
    }
};
