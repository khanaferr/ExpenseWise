<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\FinancialAdvisor;
use App\Models\Consultation;
use App\Models\Expense;
use App\Models\Budget;
use App\Models\Wallet;

class ClientController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $expenses = $user->expenses()->with(['category', 'wallet'])->latest()->take(5)->get();
        $wallets = $user->wallets;


        $budgets = $user->budgets()->with('category')->get()->map(function ($budget) use ($user) {
            $spent = Expense::where('user_id', $user->id)
                ->where('category_id', $budget->category_id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('amount');

            $budget->spent = $spent;
            $budget->remaining = max(0, $budget->amount - $spent);
            $budget->percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;

            return $budget;
        });

        $myConsultations = $user->consultations()->with('advisor.user')->latest()->get();
        $availableAdvisors = FinancialAdvisor::with('user')->get();

        return view('client.dashboard', compact(
            'expenses',
            'wallets',
            'budgets',
            'myConsultations',
            'availableAdvisors'
        ));
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'wallet_id' => 'required|exists:wallets,id',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $wallet = $user->wallets()->findOrFail($request->wallet_id);

        if ($wallet->balance < $request->amount) {
            return redirect()->back()->withErrors(['amount' => 'Insufficient funds in this wallet.']);
        }


        DB::transaction(function () use ($request, $user, $wallet) {
            $user->expenses()->create([
                'amount' => $request->amount,
                'wallet_id' => $request->wallet_id,
                'category_id' => $request->category_id,
                'date' => $request->date,
                'description' => $request->description,
            ]);

            $wallet->decrement('balance', $request->amount);
        });

        return redirect()->back()->with('success', 'Expense added and wallet updated!');
    }

    public function deleteExpense($id)
    {
        $user = Auth::user();
        $expense = $user->expenses()->with('wallet')->findOrFail($id);

        DB::transaction(function () use ($expense) {
            // Restore the wallet balance
            $expense->wallet->increment('balance', $expense->amount);
            
            // Delete the expense
            $expense->delete();
        });

        return redirect()->back()->with('success', 'Expense deleted and wallet balance restored.');
    }

    public function storeBudget(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:1',
        ]);

        Budget::updateOrCreate(
            ['user_id' => Auth::id(), 'category_id' => $request->category_id],
            ['amount' => $request->amount, 'period' => 'monthly']
        );

        return redirect()->back()->with('success', 'Budget set successfully!');
    }

    public function storeConsultation(Request $request)
    {
        $request->validate([
            'advisor_id' => 'required|exists:financial_advisors,id',
            'scheduled_at' => 'required|date|after:now',
        ]);

        Consultation::create([
            'user_id' => Auth::id(),
            'advisor_id' => $request->advisor_id,
            'scheduled_at' => $request->scheduled_at,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Consultation requested!');
    }

    public function cancelConsultation($id)
    {
        Auth::user()->consultations()->findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Consultation cancelled.');
    }
}
