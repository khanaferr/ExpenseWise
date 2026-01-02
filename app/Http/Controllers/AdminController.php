<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FinancialAdvisor;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    public function dashboard()
    {
        $advisors = FinancialAdvisor::with('user')->get();

        $clients = User::where('role', 'client')->with('profile')->get();

        return view('admin.dashboard', compact('advisors', 'clients'));
    }

    public function deleteClient($id)
    {
        $user = User::findOrFail($id);
        if ($user->role !== 'client') abort(403);

        $user->delete();
        return redirect()->back()->with('success', 'Client deleted successfully.');
    }

    public function storeAdvisor(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'certification_id' => ['required', 'string'],
            'hourly_rate' => ['required', 'numeric'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'advisor',
        ]);

        FinancialAdvisor::create([
            'id' => $user->id,
            'certification_id' => $request->certification_id,
            'hourly_rate' => $request->hourly_rate,
        ]);

        return redirect()->back()->with('success', 'New Advisor created.');
    }

    public function deleteAdvisor($id)
    {
        $advisor = FinancialAdvisor::with('user')->findOrFail($id);
        
        // Delete the user first, which will cascade delete the advisor record
        $advisor->user->delete();

        return redirect()->back()->with('success', 'Advisor deleted successfully.');
    }

    public function storeClient(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
        ]);

        UserProfile::create([
            'id' => $user->id,
            'currency' => $request->currency ?? 'USD',
            'monthly_budget_limit' => $request->monthly_budget_limit ?? null,
        ]);

        return redirect()->back()->with('success', 'New Client created successfully.');
    }
}
