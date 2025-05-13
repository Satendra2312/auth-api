<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->before('remember_token');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('phone');
            $table->date('dob')->nullable()->after('gender');
            $table->string('state')->nullable()->after('dob');
            $table->string('city')->nullable()->after('state');
            $table->string('address')->nullable()->after('city');
            $table->string('pincode')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'gender',
                'dob',
                'state',
                'city',
                'address',
                'pincode'
            ]);
        });
    }
};
