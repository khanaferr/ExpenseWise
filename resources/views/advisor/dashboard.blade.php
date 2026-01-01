@extends('layouts.app')

@section('title', 'Advisor Dashboard')

@section('content')

    <div id="view-requests" class="view-section active">
        <h2 class="text-lg font-bold text-gray-700 mb-4">Pending Consultations</h2>
        
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="p-4 font-semibold">Client Name</th>
                        <th class="p-4 font-semibold">Requested Date</th>
                        <th class="p-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pendingConsultations as $consultation)
                    <tr>
                        <td class="p-4 font-medium text-gray-800">{{ $consultation->client->name }}</td>
                        <td class="p-4">{{ $consultation->scheduled_at }}</td>
                        <td class="p-4 text-right space-x-2">
                            <form action="{{ route('advisor.consultations.approve', $consultation->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs font-bold hover:bg-green-200">
                                    Approve
                                </button>
                            </form>
                            <form action="{{ route('advisor.consultations.decline', $consultation->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-xs font-bold hover:bg-red-200">
                                    Decline
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="p-6 text-center text-gray-400">No pending requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="view-upcoming" class="view-section">
        <h2 class="text-lg font-bold text-gray-700 mb-4">Upcoming Schedule</h2>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
             <ul class="divide-y divide-gray-100">
                @foreach($confirmedConsultations as $consultation)
                <li class="p-4 flex justify-between items-center">
                    <div>
                        <p class="font-bold text-gray-800">{{ $consultation->client->name }}</p>
                        <p class="text-xs text-gray-500">{{ $consultation->scheduled_at }}</p>
                    </div>
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">Confirmed</span>
                </li>
                @endforeach
             </ul>
        </div>
    </div>

@endsection