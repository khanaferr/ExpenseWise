<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FinancialAdvisor;

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

        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot delete an Admin.');
        }

        $user->delete();

        return redirect()->route('admin.dashboard')->with('success', 'User deleted successfully.');
    }
}
