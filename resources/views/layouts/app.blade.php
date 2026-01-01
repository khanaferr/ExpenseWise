<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpenseWise - @yield('title', 'Dashboard')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .view-section { display: none; }
        .view-section.active { display: block; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans h-screen flex overflow-hidden">

    <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col z-10">
        <div class="h-16 flex items-center px-6 border-b border-gray-100">
            <div class="text-2xl font-bold text-indigo-600 flex items-center gap-2">
                <i class="fa-solid fa-wallet"></i> ExpenseWise
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            
            @if(Auth::user()->isClient())
                <button onclick="switchView('timeline')" id="nav-timeline" class="nav-item active w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg bg-indigo-50 text-indigo-600 transition-colors">
                    <i class="fa-solid fa-clock-rotate-left w-5"></i> Timeline
                </button>
                <button onclick="switchView('wallets')" id="nav-wallets" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                    <i class="fa-solid fa-credit-card w-5"></i> Wallets
                </button>
                <button onclick="switchView('budgets')" id="nav-budgets" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                    <i class="fa-solid fa-chart-pie w-5"></i> Budgets
                </button>
            
            @elseif(Auth::user()->isAdvisor())
                <button onclick="switchView('requests')" id="nav-requests" class="nav-item active w-full flex items-center gap-3 px-4 py-3 text-sm font-medium bg-indigo-50 text-indigo-600 rounded-lg transition-colors">
                    <i class="fa-solid fa-clipboard-list w-5"></i> Requests
                </button>
                <button onclick="switchView('upcoming')" id="nav-upcoming" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    <i class="fa-solid fa-calendar-check w-5"></i> Upcoming
                </button>

            @elseif(Auth::user()->isAdmin())
                <button onclick="switchView('users')" id="nav-users" class="nav-item active w-full flex items-center gap-3 px-4 py-3 text-sm font-medium bg-indigo-50 text-indigo-600 rounded-lg transition-colors">
                    <i class="fa-solid fa-users w-5"></i> Manage Users
                </button>
            @endif

        </nav>

        <div class="p-4 border-t border-gray-100">
            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" class="w-10 h-10 rounded-full">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 lg:px-10">
            <h1 id="page-title" class="text-xl font-bold text-gray-800">Dashboard</h1>
            
            <button class="md:hidden text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            
            <div>
                @yield('header-actions')
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-10">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                 <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        function switchView(viewName) {
            // Reset Sidebar Styling
            document.querySelectorAll('.nav-item').forEach(btn => {
                btn.classList.remove('bg-indigo-50', 'text-indigo-600');
                btn.classList.add('text-gray-600', 'hover:bg-gray-50');
            });

            // Activate Clicked Link (if exists)
            const activeBtn = document.getElementById(`nav-${viewName}`);
            if(activeBtn) {
                activeBtn.classList.add('bg-indigo-50', 'text-indigo-600');
                activeBtn.classList.remove('text-gray-600');
            }

            // Hide all sections
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            
            // Show selected section
            const targetSection = document.getElementById(`view-${viewName}`);
            if(targetSection) targetSection.classList.add('active');

            // Update Header Title
            const titleEl = document.getElementById('page-title');
            if(titleEl) titleEl.textContent = viewName.charAt(0).toUpperCase() + viewName.slice(1);
        }

        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            if(modal) modal.classList.toggle('hidden');
        }
    </script>
    
    @stack('scripts')

</body>
</html>