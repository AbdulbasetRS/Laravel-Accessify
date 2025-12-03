<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

return new class extends Migration
{
    public function up()
    {
        Schema::create(Config::get('accessify.tables.permission_role'), function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained(Config::get('accessify.tables.permissions'))->onDelete('cascade');
            $table->foreignId('role_id')->constrained(Config::get('accessify.tables.roles'))->onDelete('cascade');
            $table->primary(['permission_id', 'role_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(Config::get('accessify.tables.permission_role'));
    }
};
