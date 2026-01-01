<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Consultation;

class FinancialAdvisorController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $advisor = $user->advisorProfile;

        $pendingConsultations = $advisor->consultations()
            ->where('status', 'pending')
            ->with('client')
            ->get();

        $confirmedConsultations = $advisor->consultations()
            ->where('status', 'confirmed')
            ->with('client')
            ->get();

        return view('advisor.dashboard', compact('pendingConsultations', 'confirmedConsultations'));
    }

    public function approveConsultation($id)
    {
        $advisorId = Auth::user()->advisorProfile->id;

        $consultation = Consultation::where('id', $id)
            ->where('advisor_id', $advisorId)
            ->firstOrFail();

        $consultation->update(['status' => 'confirmed']);

        return redirect()->route('advisor.dashboard')->with('success', 'Consultation confirmed.');
    }

    public function declineConsultation($id)
    {
        $advisorId = Auth::user()->advisorProfile->id;

        $consultation = Consultation::where('id', $id)
            ->where('advisor_id', $advisorId)
            ->firstOrFail();

        $consultation->update(['status' => 'declined']);

        return redirect()->route('advisor.dashboard')->with('success', 'Consultation declined.');
    }
}
