<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;     // For Clients
use App\Models\FinancialAdvisor; // For Advisors
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:client,advisor'], // Ensure valid role
        ]);

        // 1. Create the Base User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // 2. Create the Role-Specific Profile (The "University Level" Step)
        if ($request->role === 'client') {
            UserProfile::create([
                'id' => $user->id, // Shared Key: Same ID as User
                'currency' => 'USD', // Default value
                'monthly_budget_limit' => null,
            ]);
        } elseif ($request->role === 'advisor') {
            FinancialAdvisor::create([
                'id' => $user->id, // Shared Key
                'certification_id' => 'PENDING-' . uniqid(), // Placeholder
                'hourly_rate' => 50.00, // Default starting rate
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        // 3. Redirect based on Role (Dynamic Redirection)
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isAdvisor()) {
            return redirect()->route('advisor.dashboard');
        }

        return redirect()->route('client.dashboard');
    }
}
