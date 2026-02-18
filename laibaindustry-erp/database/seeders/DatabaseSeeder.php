<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $timestamp = now();

        DB::table('categories')->upsert([
            [
                'name' => 'General',
                'description' => 'Default category for auto-created products',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['name'], ['description', 'updated_at']);

        DB::table('tax_settings')->upsert([
            [
                'id' => 1,
                'default_rate' => 15.00,
                'description' => 'Default tax rate',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['id'], ['default_rate', 'description', 'updated_at']);

        DB::table('currencies')->upsert([
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'is_default' => true,
                'is_active' => true,
                'decimal_places' => 2,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'is_default' => false,
                'is_active' => true,
                'decimal_places' => 2,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'symbol' => '£',
                'is_default' => false,
                'is_active' => true,
                'decimal_places' => 2,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['code'], ['name', 'symbol', 'is_default', 'is_active', 'decimal_places', 'updated_at']);

        DB::table('users')->upsert([
            [
                'email' => 'admin@example.com',
                'password_hash' => '$2y$12$fqt0tvYgS5n15OyqPFclgeW6LOhaRBwsMvENCYjzeNSEzzL391Fe.',
                'name' => 'Admin',
                'role' => 'admin',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['email'], ['name', 'role', 'updated_at']);
    }
}
