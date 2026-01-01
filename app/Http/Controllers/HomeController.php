<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->isAdvisor()) {
                return redirect()->route('advisor.dashboard');
            }

            if ($user->isClient()) {
                return redirect()->route('client.dashboard');
            }
        }

        return view('welcome');
    }
}
