<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FinancialAdvisor;
use App\Models\Consultation;
use App\Models\Expense;
use App\Models\Budget;

class ClientController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $expenses = $user->expenses()->latest()->take(5)->get();
        $wallets = $user->wallets;
        $budgets = $user->budgets;
        $myConsultations = $user->consultations()->with('advisor.user')->get();
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
            'amount' => 'required|numeric',
            'wallet_id' => 'required|exists:wallets,id',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        Auth::user()->expenses()->create([
            'amount' => $request->amount,
            'wallet_id' => $request->wallet_id,
            'category_id' => $request->category_id,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Expense added successfully!');
    }

    public function deleteExpense($id)
    {
        $expense = Auth::user()->expenses()->findOrFail($id);

        $expense->delete();

        return redirect()->back()->with('success', 'Expense deleted successfully!');
    }


    public function storeBudget(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:1',
        ]);

        Budget::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'category_id' => $request->category_id
            ],
            [
                'amount' => $request->amount,
                'period' => 'monthly'
            ]
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

        return redirect()->route('client.dashboard')->with('success', 'Consultation requested successfully.');
    }

    public function cancelConsultation($id)
    {
        $user = Auth::user();

        $consultation = Consultation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $consultation->delete();

        return redirect()->route('client.dashboard')->with('success', 'Consultation cancelled.');
    }
}
