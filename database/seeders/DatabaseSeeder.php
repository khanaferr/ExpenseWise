<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\FinancialAdvisor;
use App\Models\Admin;
use App\Models\Wallet;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@expensewise.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        Admin::create(['id' => $admin->id, 'department' => 'IT Support']);


        $advisor = User::create([
            'name' => 'Dr. Wealth',
            'email' => 'advisor@expensewise.com',
            'password' => Hash::make('password'),
            'role' => 'advisor',
        ]);

        FinancialAdvisor::create([
            'id' => $advisor->id,
            'certification_id' => 'CPA-998877',
            'hourly_rate' => 150.00
        ]);


        $client = User::create([
            'name' => 'John Doe',
            'email' => 'client@expensewise.com',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);

        UserProfile::create([
            'id' => $client->id,
            'currency' => 'USD',
            'monthly_budget_limit' => 2000.00
        ]);

        $walletBank = Wallet::create([
            'user_id' => $client->id,
            'name' => 'Chase Bank',
            'balance' => 4500.00
        ]);

        $walletCash = Wallet::create([
            'user_id' => $client->id,
            'name' => 'Cash',
            'balance' => 200.00
        ]);

        $catFood = Category::create([
            'user_id' => $client->id,
            'name' => 'Food & Dining',
            'type' => 'expense'
        ]);

        $catTransport = Category::create([
            'user_id' => $client->id,
            'name' => 'Transportation',
            'type' => 'expense'
        ]);

        Expense::create([
            'user_id' => $client->id,
            'wallet_id' => $walletBank->id,
            'category_id' => $catFood->id,
            'amount' => 45.50,
            'date' => now()->subDays(1),
            'description' => 'Grocery Store'
        ]);

        Expense::create([
            'user_id' => $client->id,
            'wallet_id' => $walletCash->id,
            'category_id' => $catTransport->id,
            'amount' => 12.00,
            'date' => now(),
            'description' => 'Uber Ride'
        ]);
    }
}
