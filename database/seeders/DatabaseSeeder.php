<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update a simple admin login
        if (class_exists(\App\Models\User::class)) {
            \App\Models\User::updateOrCreate(
                ['email' => 'admin@example.com'],
                ['name' => 'Admin', 'password' => Hash::make('password')]
            );
        }

        // Demo data (idempotent)
        \App\Models\Student::updateOrCreate(['reference'=>'A1001'],['first_name'=>'Ali','last_name'=>'Khan']);
        \App\Models\Student::updateOrCreate(['reference'=>'A1002'],['first_name'=>'Sara','last_name'=>'Iqbal']);

        \App\Models\Staff::updateOrCreate(['name'=>'Mr. Ahmed'],['role'=>'teacher']);
        \App\Models\Staff::updateOrCreate(['name'=>'Ms. Fatima'],['role'=>'teacher']);

        \App\Models\Book::updateOrCreate(['reference'=>'A1001','title'=>'Algebra I'],['subject'=>'Math','price'=>500]);
        \App\Models\Book::updateOrCreate(['reference'=>'A1002','title'=>'Physics Basics'],['subject'=>'Science','price'=>600]);
    }
}
