@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

    <div id="view-users" class="view-section active">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-700">System Users</h2>
            <div class="flex gap-2">
                <button onclick="toggleModal('addClientModal')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-user-plus"></i> Add Client
                </button>
                <button onclick="toggleModal('addAdvisorModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-plus"></i> Add Advisor
                </button>
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="p-4 font-semibold">Name</th>
                        <th class="p-4 font-semibold">Email</th>
                        <th class="p-4 font-semibold">Role</th>
                        <th class="p-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($clients as $user)
                    <tr>
                        <td class="p-4 font-medium text-gray-800">{{ $user->name }}</td>
                        <td class="p-4">{{ $user->email }}</td>
                        <td class="p-4"><span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">Client</span></td>
                        <td class="p-4 text-right">
                             <form action="{{ route('admin.clients.delete', $user->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    @foreach($advisors as $advisor)
                    <tr>
                        <td class="p-4 font-medium text-gray-800">{{ $advisor->user->name }}</td>
                        <td class="p-4">{{ $advisor->user->email }}</td>
                        <td class="p-4"><span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Advisor</span></td>
                        <td class="p-4 text-right">
                             <form action="{{ route('admin.advisors.delete', $advisor->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Advisor Modal -->
    <div id="addAdvisorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-700">Add New Advisor</h3>
                <button onclick="toggleModal('addAdvisorModal')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form action="{{ route('admin.advisors.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Name</label>
                    <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Advisor Name" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="advisor@example.com" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password</label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Certification ID</label>
                    <input type="text" name="certification_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="e.g., CPA-123456" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Hourly Rate</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400">$</span>
                        <input type="number" name="hourly_rate" step="0.01" min="0" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="0.00" required>
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded-lg hover:bg-indigo-700 transition-colors">Create Advisor</button>
            </form>
        </div>
    </div>

    <!-- Add Client Modal -->
    <div id="addClientModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-700">Add New Client</h3>
                <button onclick="toggleModal('addClientModal')" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form action="{{ route('admin.clients.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Name</label>
                    <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Client Name" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="client@example.com" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password</label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Currency (Optional)</label>
                    <select name="currency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="USD" selected>USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="CAD">CAD - Canadian Dollar</option>
                        <option value="AUD">AUD - Australian Dollar</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Monthly Budget Limit (Optional)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400">$</span>
                        <input type="number" name="monthly_budget_limit" step="0.01" min="0" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="0.00">
                    </div>
                </div>

                <button type="submit" class="w-full bg-green-600 text-white font-bold py-2 rounded-lg hover:bg-green-700 transition-colors">Create Client</button>
            </form>
        </div>
    </div>

@endsection