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
use App\Models\Category;

class ClientController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $expenses = $user->expenses()->with(['category', 'wallet'])->latest()->take(5)->get();
        $wallets = $user->wallets;
        
        $totalIncome = $user->expenses()
            ->whereHas('category', function($q) {
                $q->where('type', 'income');
            })
            ->sum('amount');
        
        $totalExpenses = $user->expenses()
            ->whereHas('category', function($q) {
                $q->where('type', 'expense');
            })
            ->sum('amount');

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

        $categorySpending = Expense::where('user_id', $user->id)
            ->whereHas('category', function($q) {
                $q->where('type', 'expense');
            })
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($expenses) {
                $firstExpense = $expenses->first();
                return [
                    'category' => $firstExpense->category->name ?? 'Uncategorized',
                    'amount' => floatval($expenses->sum('amount'))
                ];
            })
            ->values();

        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $total = Expense::where('user_id', $user->id)
                ->whereHas('category', function($q) {
                    $q->where('type', 'expense');
                })
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');
            $monthlyTrend[] = [
                'month' => $month->format('M'),
                'amount' => floatval($total)
            ];
        }

        $tips = [];
        $thisMonthSpending = Expense::where('user_id', $user->id)
            ->whereHas('category', function($q) {
                $q->where('type', 'expense');
            })
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        $lastMonthSpending = Expense::where('user_id', $user->id)
            ->whereHas('category', function($q) {
                $q->where('type', 'expense');
            })
            ->whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->sum('amount');
        
        if ($lastMonthSpending > 0) {
            $percentageChange = (($thisMonthSpending - $lastMonthSpending) / $lastMonthSpending) * 100;
            if ($percentageChange > 20) {
                $tips[] = "You spent " . number_format($percentageChange, 1) . "% more this month compared to last month. Consider reviewing your expenses.";
            } elseif ($percentageChange < -20) {
                $tips[] = "Great job! You spent " . number_format(abs($percentageChange), 1) . "% less this month. Keep up the good work!";
            }
        }

        $topCategory = $categorySpending->sortByDesc('amount')->first();
        if ($topCategory && $topCategory['amount'] > 0) {
            $categoryTotal = $categorySpending->sum('amount');
            $categoryPercentage = ($topCategory['amount'] / $categoryTotal) * 100;
            if ($categoryPercentage > 40) {
                $tips[] = "Most of your spending (" . number_format($categoryPercentage, 1) . "%) is on " . $topCategory['category'] . ". Consider setting a budget for this category.";
            }
        }

        return view('client.dashboard', compact(
            'expenses',
            'wallets',
            'budgets',
            'myConsultations',
            'availableAdvisors',
            'categorySpending',
            'monthlyTrend',
            'tips',
            'totalIncome',
            'totalExpenses'
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
        $category = Category::findOrFail($request->category_id);

        if ($category->type === 'expense' && $wallet->balance < $request->amount) {
            return redirect()->back()->withErrors(['amount' => 'Insufficient funds in this wallet.']);
        }

        DB::transaction(function () use ($request, $user, $wallet, $category) {
            $user->expenses()->create([
                'amount' => $request->amount,
                'wallet_id' => $request->wallet_id,
                'category_id' => $request->category_id,
                'date' => $request->date,
                'description' => $request->description,
            ]);

            if ($category->type === 'income') {
                $wallet->increment('balance', $request->amount);
            } else {
                $wallet->decrement('balance', $request->amount);
            }
        });

        $message = $category->type === 'income' 
            ? 'Income added and wallet updated!' 
            : 'Expense added and wallet updated!';

        return redirect()->back()->with('success', $message);
    }

    public function deleteExpense($id)
    {
        $user = Auth::user();
        $expense = $user->expenses()->with(['wallet', 'category'])->findOrFail($id);

        DB::transaction(function () use ($expense) {
            if ($expense->category->type === 'income') {
                $expense->wallet->decrement('balance', $expense->amount);
            } else {
                $expense->wallet->increment('balance', $expense->amount);
            }

            $expense->delete();
        });

        return redirect()->back()->with('success', 'Transaction deleted and wallet balance updated.');
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

        return redirect()->back()->with('success', 'Consultation requested successfully!');
    }

    public function cancelConsultation($id)
    {
        Auth::user()->consultations()->findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Consultation cancelled.');
    }

    public function storeWallet(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0',
        ]);

        Auth::user()->wallets()->create([
            'name' => $request->name,
            'balance' => $request->balance,
        ]);

        return redirect()->back()->with('success', 'Wallet created successfully!');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:expense,income',
        ]);

        Auth::user()->categories()->create([
            'name' => $request->name,
            'type' => $request->type ?? 'expense',
        ]);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function deleteWallet($id)
    {
        $user = Auth::user();
        $wallet = $user->wallets()->findOrFail($id);

        $hasExpenses = $user->expenses()->where('wallet_id', $wallet->id)->exists();
        
        if ($hasExpenses) {
            return redirect()->back()->withErrors(['wallet' => 'Cannot delete wallet with existing transactions. Please delete or reassign transactions first.']);
        }

        $wallet->delete();

        return redirect()->back()->with('success', 'Wallet deleted successfully.');
    }

    public function deleteBudget($id)
    {
        $user = Auth::user();
        $budget = $user->budgets()->findOrFail($id);

        $budget->delete();

        return redirect()->back()->with('success', 'Budget deleted successfully.');
    }
}
