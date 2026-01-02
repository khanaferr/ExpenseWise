@extends('layouts.app')

@section('title', 'My Expenses')

@section('header-actions')
    <button onclick="toggleModal('transactionModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2 transition-all">
        <i class="fa-solid fa-plus"></i> Add Transaction
    </button>
@endsection

@section('content')

    <div id="view-timeline" class="view-section active space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Expenses</p>
                    <h2 class="text-2xl font-bold text-red-500">
                        -${{ number_format($expenses->sum('amount'), 2) }}
                    </h2>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center text-red-500">
                    <i class="fa-solid fa-arrow-down"></i>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Balance</p>
                    <h2 class="text-2xl font-bold text-green-600">
                        +${{ number_format($wallets->sum('balance'), 2) }}
                    </h2>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50 font-semibold text-sm text-gray-500">Recent Activity</div>
            <ul class="divide-y divide-gray-100">
                @forelse($expenses as $expense)
                <li class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-sm">{{ $expense->description ?? 'Expense' }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $expense->category->name }} • {{ $expense->date }} • {{ $expense->wallet->name }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="font-bold text-red-500">
                            -${{ number_format($expense->amount, 2) }}
                        </span>
                        <form action="{{ route('client.expenses.delete', $expense->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-600 text-xs">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </li>
                @empty
                <li class="p-6 text-center text-gray-500 text-sm">No transactions found.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div id="view-wallets" class="view-section">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($wallets as $wallet)
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden group">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-slate-800 text-white rounded-lg flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-700">{{ $wallet->name }}</h3>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Wallet</p>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($wallet->balance, 2) }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div id="view-budgets" class="view-section space-y-6">
        @foreach($budgets as $budget)
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex justify-between items-end mb-2">
                <div>
                    <h3 class="font-bold text-gray-800">{{ $budget->category->name }}</h3>
                    <p class="text-xs text-gray-500">
                        Spent: ${{ number_format($budget->spent, 0) }} / ${{ number_format($budget->amount, 0) }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="font-bold {{ $budget->remaining < 0 ? 'text-red-500' : 'text-indigo-600' }}">
                        ${{ number_format($budget->remaining, 0) }} Left
                    </span>
                </div>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                <div class="h-3 rounded-full {{ $budget->percentage > 100 ? 'bg-red-500' : 'bg-indigo-500' }}" 
                     style="width: {{ min($budget->percentage, 100) }}%"></div>
            </div>
        </div>
        @endforeach
        
        <button onclick="toggleModal('budgetModal')" class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-400 font-medium hover:border-indigo-500 hover:text-indigo-500 transition-colors">
            + Set New Budget
        </button>
    </div>

    <div id="transactionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-700">New Transaction</h3>
                <button onclick="toggleModal('transactionModal')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form action="{{ route('client.expenses.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400">$</span>
                        <input type="number" name="amount" step="0.01" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="0.00" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pay From</label>
                        <select name="wallet_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none">
                            @foreach($wallets as $wallet)
                                <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                        <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none">
                            @foreach(Auth::user()->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                    <input type="date" name="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Note</label>
                    <input type="text" name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none" placeholder="Description...">
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded-lg hover:bg-indigo-700 transition-colors">Save Expense</button>
            </form>
        </div>
    </div>

    <!-- Budget Modal -->
    <div id="budgetModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-700">Set Budget</h3>
                <button onclick="toggleModal('budgetModal')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form action="{{ route('client.budgets.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="">Select a category</option>
                        @foreach(Auth::user()->categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Budget Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400">$</span>
                        <input type="number" name="amount" step="0.01" min="1" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="0.00" required>
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded-lg hover:bg-indigo-700 transition-colors">Save Budget</button>
            </form>
        </div>
    </div>

    <!-- Consultation Modal -->
    <div id="consultationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-700">Book Consultation</h3>
                <button onclick="toggleModal('consultationModal')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form action="{{ route('client.consultations.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Select Advisor</label>
                    <select name="advisor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="">Select an advisor</option>
                        @foreach($availableAdvisors as $advisor)
                            <option value="{{ $advisor->id }}">{{ $advisor->user->name }} - ${{ number_format($advisor->hourly_rate ?? 0, 2) }}/hr</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Scheduled Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" required min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded-lg hover:bg-indigo-700 transition-colors">Request Consultation</button>
            </form>
        </div>
    </div>

    <!-- Consultations Section -->
    <div id="view-consultations" class="view-section space-y-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-700">My Consultations</h2>
            <button onclick="toggleModal('consultationModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2 transition-all">
                <i class="fa-solid fa-plus"></i> Book Consultation
            </button>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <ul class="divide-y divide-gray-100">
                @forelse($myConsultations as $consultation)
                <li class="p-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                    <div>
                        <p class="font-bold text-gray-800">{{ $consultation->advisor->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $consultation->scheduled_at }}</p>
                        <span class="inline-block mt-2 px-2 py-1 rounded text-xs font-bold 
                            @if($consultation->status === 'pending') bg-yellow-100 text-yellow-700
                            @elseif($consultation->status === 'confirmed') bg-green-100 text-green-700
                            @elseif($consultation->status === 'declined') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-700
                            @endif">
                            {{ ucfirst($consultation->status) }}
                        </span>
                    </div>
                    @if($consultation->status === 'pending')
                    <form action="{{ route('client.consultations.cancel', $consultation->id) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Cancel</button>
                    </form>
                    @endif
                </li>
                @empty
                <li class="p-6 text-center text-gray-500 text-sm">No consultations booked yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

@endsection