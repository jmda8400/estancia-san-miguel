<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->updateOrInsert(
            ['name' => 'dev'],
            [
                'email' => 'dev@local.test',
                'password' => Hash::make('123'),
            ]
        );
    }

    public function down(): void
    {
        DB::table('users')->where('name', 'dev')->delete();
    }
};
