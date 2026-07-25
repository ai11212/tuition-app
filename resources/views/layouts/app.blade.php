<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name','Alperton Academy') }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style> 
    body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,"Helvetica Neue",Arial} 
    .active{background:#e8f0ff;color:#1d4ed8} 
    [x-cloak] { display: none !important; }
  </style>
  <!-- Bootstrap 5 for forms that use .container/.card/.form-control etc. -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Fallback vanilla JS for mobile menu
    document.addEventListener('DOMContentLoaded', function() {
      const menuBtn = document.getElementById('mobile-menu-btn');
      const sidebar = document.getElementById('mobile-sidebar');
      const backdrop = document.getElementById('mobile-backdrop');
      const closeBtn = document.getElementById('close-sidebar-btn');
      
      if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', function() {
          sidebar.classList.toggle('translate-x-0');
          sidebar.classList.toggle('-translate-x-full');
          if (backdrop) backdrop.classList.toggle('hidden');
          console.log('Menu toggled via vanilla JS');
        });
        
        if (backdrop) {
          backdrop.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            backdrop.classList.add('hidden');
          });
        }
        
        if (closeBtn) {
          closeBtn.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            if (backdrop) backdrop.classList.add('hidden');
          });
        }
        
        // Close on link click
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
          link.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            if (backdrop) backdrop.classList.add('hidden');
          });
        });
      }
    });
    
    document.addEventListener('alpine:init', () => {
      console.log('Alpine.js initialized successfully');
    });
  </script>
</head>
<body class="bg-gray-100 text-gray-800" x-data="{ open: false }">
  <!-- Topbar -->
  <header class="fixed top-0 inset-x-0 z-40 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center">
      <button id="mobile-menu-btn" class="md:hidden mr-3 p-2 rounded hover:bg-gray-100 text-gray-700" aria-label="Toggle menu">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
      <a href="/" class="font-bold text-xl text-gray-800 flex items-center gap-2">
        <span class="text-2xl">🎓</span>
        <span class="hidden sm:inline">{{ config('app.name','Alperton Academy') }}</span>
      </a>
      <nav class="ml-8 hidden md:flex gap-1 text-sm flex-1" x-data="{ studentsOpen: false, financeOpen: false, libraryOpen: false }" @click.away="studentsOpen=false; financeOpen=false; libraryOpen=false">
        <a href="/" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition {{ request()->is('/') ? 'bg-gray-100 text-gray-900 font-semibold' : '' }}">
          Dashboard
        </a>
        
        <!-- Students Dropdown (click-only; opening closes the others) -->
        <div class="relative">
          <button @click="studentsOpen = !studentsOpen; financeOpen = false; libraryOpen = false"
                  class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition flex items-center gap-1 {{ request()->is('students*') || request()->is('reference-profile*') ? 'bg-gray-100 text-gray-900 font-semibold' : '' }}">
            Students
            <svg class="w-4 h-4 transition-transform" :class="{'rotate-180':studentsOpen}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="studentsOpen" x-cloak x-transition class="absolute top-full left-0 mt-1 bg-white rounded-lg shadow-xl border border-gray-200 w-48 py-2">
            <a href="/students" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">View All Students</a>
            <a href="/students?action=new" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">New Admission</a>
            <a href="/reference-profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">Reference Profile</a>
            <a href="/students" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">Print Time Table</a>
          </div>
        </div>

        <a href="/attendance" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition {{ request()->is('attendance*') ? 'bg-gray-100 text-gray-900 font-semibold' : '' }}">
          Attendance
        </a>
        
        <!-- Finance Dropdown (click-only; opening closes the others) -->
        <div class="relative">
          <button @click="financeOpen = !financeOpen; studentsOpen = false; libraryOpen = false"
                  class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition flex items-center gap-1 {{ request()->is('payments*') || request()->is('accounts*') || request()->is('payment-verification*') || request()->is('expenses*') || request()->is('teacher-salaries*') ? 'bg-gray-100 text-gray-900 font-semibold' : '' }}">
            Finance
            <svg class="w-4 h-4 transition-transform" :class="{'rotate-180':financeOpen}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="financeOpen" x-cloak x-transition class="absolute top-full left-0 mt-1 bg-white rounded-lg shadow-xl border border-gray-200 w-52 py-2">
            <a href="/payments" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">Take Payment</a>
            {{-- Defaulter List hidden (15/07/2026) — flip to true to restore --}}
            @if(false)
            <a href="/payment-verification?search=&status=pending" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">Defaulter List</a>
            @endif
            <a href="/accounts" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">Accounts Summary</a>
            <a href="/expenses" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">Expenses</a>
            <a href="/teacher-salaries" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">Teacher Salaries</a>
            @if(config('reminders.enabled'))
              <a href="/payment-reminders" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900 flex items-center justify-between">
                <span>Payment Reminders</span>
                @php
                  $badgeCount = \App\Http\Controllers\PaymentReminderController::getBadgeCount();
                @endphp
                @if($badgeCount > 0)
                  <span class="badge bg-danger rounded-pill">{{ $badgeCount }}</span>
                @endif
              </a>
            @endif
          </div>
        </div>

        <!-- Library Dropdown (click-only; opening closes the others) -->
        <div class="relative">
          <button @click="libraryOpen = !libraryOpen; studentsOpen = false; financeOpen = false"
                  class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition flex items-center gap-1 {{ request()->is('books*') || request()->is('book-library*') ? 'bg-gray-100 text-gray-900 font-semibold' : '' }}">
            Library
            <svg class="w-4 h-4 transition-transform" :class="{'rotate-180':libraryOpen}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="libraryOpen" x-cloak x-transition class="absolute top-full left-0 mt-1 bg-white rounded-lg shadow-xl border border-gray-200 w-48 py-2">
            <a href="/books" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">View Books</a>
            <a href="/books/create" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">Add Books</a>
            <a href="/book-library" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-gray-900">Book Library</a>
          </div>
        </div>

        <a href="/account/password" class="px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition {{ request()->is('account*') ? 'bg-gray-100 text-gray-900 font-semibold' : '' }}">
          Account
        </a>
      </nav>
      <div class="ml-auto">
        <form method="POST" action="/logout" class="hidden md:block">@csrf
          <button class="px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700 transition font-medium border border-red-200">
            Logout
          </button>
        </form>
      </div>
    </div>
  </header>

  <!-- Mobile Backdrop -->
  <div id="mobile-backdrop" class="fixed inset-0 bg-black/50 z-40 md:hidden top-16 hidden"></div>

  <!-- Sidebar (Mobile Only) -->
  <aside id="mobile-sidebar" class="fixed z-50 top-16 left-0 bottom-0 w-64 bg-white border-r overflow-y-auto p-4 transform -translate-x-full shadow-xl transition-transform duration-300 ease-in-out md:hidden" style="will-change: transform;">
    <!-- Close button for mobile -->
    <button id="close-sidebar-btn" class="md:hidden absolute top-2 right-2 p-2 rounded-lg hover:bg-gray-100 text-gray-600">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    
    <div class="text-xs uppercase tracking-wider text-gray-400 px-2 mb-2">Main</div>
    <ul class="space-y-1 text-sm">
      <li><a href="/" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('/') ? 'active' : '' }}" @click="open=false">🏠 Dashboard</a></li>
      <li><a href="/students" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('students*') ? 'active' : '' }}" @click="open=false">🧑‍🎓 Students (New Admission)</a></li>
    </ul>

    <div class="text-xs uppercase tracking-wider text-gray-400 px-2 mt-5 mb-2">Attendance</div>
    <ul class="space-y-1 text-sm">
      <li><a href="/attendance" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('attendance') ? 'active' : '' }}" @click="open=false">📝 New Attendance</a></li>
      <li><a href="/attendance/view" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('attendance/view') ? 'active' : '' }}" @click="open=false">📄 View Attendance</a></li>
    </ul>

    <div class="text-xs uppercase tracking-wider text-gray-400 px-2 mt-5 mb-2">Finance</div>
    <ul class="space-y-1 text-sm">
      <li><a href="/payments" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('payments') ? 'active' : '' }}" @click="open=false">💳 Take Payment</a></li>
      {{-- Defaulter List hidden (15/07/2026) — flip to true to restore --}}
      @if(false)
      <li><a href="/defaulters" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('defaulters') ? 'active' : '' }}" @click="open=false">⚠️ Defaulter List</a></li>
      @endif
      <li><a href="/accounts" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('accounts') ? 'active' : '' }}" @click="open=false">🧾 Accounts Summary</a></li>
      <li><a href="/expenses" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('expenses') ? 'active' : '' }}" @click="open=false">💸 Expenses</a></li>
      <li><a href="/teacher-salaries" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('teacher-salaries*') ? 'active' : '' }}" @click="open=false">💰 Teacher Salaries</a></li>
      @if(config('reminders.enabled'))
      <li>
        <a href="/payment-reminders" class="flex items-center justify-between px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('payment-reminders*') ? 'active' : '' }}" @click="open=false">
          <span>🔔 Payment Reminders</span>
          @php $badgeCount = \App\Http\Controllers\PaymentReminderController::getBadgeCount(); @endphp
          @if($badgeCount > 0)
            <span class="badge bg-danger rounded-pill">{{ $badgeCount }}</span>
          @endif
        </a>
      </li>
      @endif
    </ul>

    <div class="text-xs uppercase tracking-wider text-gray-400 px-2 mt-5 mb-2">Library & Reference</div>
    <ul class="space-y-1 text-sm">
      <li><a href="/books" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('books*') ? 'active' : '' }}" @click="open=false">📚 Add Books</a></li>
      <li><a href="/reference-profile" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('reference-profile*') ? 'active' : '' }}" @click="open=false">🪪 Reference Profile</a></li>
      <li><a href="/print-timetable" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('print-timetable*') ? 'active' : '' }}" @click="open=false">🖨️ Print Time Table</a></li>
    </ul>

    <div class="mt-6 text-xs text-gray-400 px-2">Logged in</div>
    <form method="POST" action="/logout" class="px-2 mt-1">@csrf
      <button class="text-red-600 hover:text-red-700 text-sm">Logout</button>
    </form>
  </aside>

  <!-- Main -->
  <main class="pt-16">
    <div class="max-w-7xl mx-auto p-4 md:p-6">
      @yield('content')
    </div>
  </main>
</body>
</html>
