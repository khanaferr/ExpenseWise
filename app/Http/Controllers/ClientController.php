<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FinancialAdvisor;
use App\Models\Consultation;
use App\Models\Expense; 

class ClientController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $expenses = $user->expenses()->latest()->take(5)->get();
        $myConsultations = $user->consultations()->with('advisor.user')->get();
        $availableAdvisors = FinancialAdvisor::with('user')->get();

        return view('client.dashboard', compact('expenses', 'myConsultations', 'availableAdvisors'));
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
