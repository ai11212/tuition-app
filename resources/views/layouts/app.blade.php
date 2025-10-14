<!doctype html>
<html lang="en" x-data="{ open:false }">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name','Tuition App') }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style> body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,"Helvetica Neue",Arial} .active{background:#e8f0ff;color:#1d4ed8} </style>
  <!-- Bootstrap 5 for forms that use .container/.card/.form-control etc. -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <!-- Topbar -->
  <header class="fixed top-0 inset-x-0 z-40 bg-white/80 backdrop-blur border-b">
    <div class="max-w-7xl mx-auto px-4 h-14 flex items-center">
      <button @click="open=!open" class="md:hidden mr-3 p-2 rounded hover:bg-gray-100" aria-label="Toggle menu">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
      <a href="/" class="font-semibold text-lg">{{ config('app.name','Tuition App') }}</a>
      <nav class="ml-6 hidden md:flex gap-4 text-sm">
        <a href="/" class="hover:text-blue-700 {{ request()->is('/') ? 'font-semibold text-blue-700' : '' }}">Dashboard</a>
        <a href="/students" class="hover:text-blue-700 {{ request()->is('students*') ? 'font-semibold text-blue-700' : '' }}">Students</a>
        <a href="/attendance" class="hover:text-blue-700 {{ request()->is('attendance*') ? 'font-semibold text-blue-700' : '' }}">Attendance</a>
        <a href="/payments" class="hover:text-blue-700 {{ request()->is('payments*') ? 'font-semibold text-blue-700' : '' }}">Payments</a>
      <a href="/account/password" class="hover:text-blue-700 '{{ request()->is("account*") ? "font-semibold text-blue-700" : "" }}'">Account</a> </nav>
      <div class="ml-auto">
        <form method="POST" action="/logout" class="hidden md:block">@csrf
          <button class="text-red-600 hover:text-red-700">Logout</button>
        </form>
      </div>
    </div>
  </header>

  <!-- Sidebar -->
  <aside class="fixed z-30 top-14 left-0 bottom-0 w-64 bg-white border-r overflow-y-auto p-4"
         :class="{'hidden':!open,'block':open}" @click.outside="open=false" x-transition.origin.left>
    <div class="text-xs uppercase tracking-wider text-gray-400 px-2 mb-2">Main</div>
    <ul class="space-y-1 text-sm">
      <li><a href="/" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('/') ? 'active' : '' }}">🏠 Dashboard</a></li>
      <li><a href="/students" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('students*') ? 'active' : '' }}">🧑‍🎓 Students (New Admission)</a></li>
    </ul>

    <div class="text-xs uppercase tracking-wider text-gray-400 px-2 mt-5 mb-2">Attendance</div>
    <ul class="space-y-1 text-sm">
      <li><a href="/attendance" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('attendance') ? 'active' : '' }}">📝 New Attendance</a></li>
      <li><a href="/attendance/view" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('attendance/view') ? 'active' : '' }}">📄 View Attendance</a></li>
      <li><a href="/attendance/status" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('attendance/status') ? 'active' : '' }}">📊 Attendance Data</a></li>
      <li><a href="/attendance/staff" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('attendance/staff') ? 'active' : '' }}">👩‍🏫 Staff (Back Attendance)</a></li>
    </ul>

    <div class="text-xs uppercase tracking-wider text-gray-400 px-2 mt-5 mb-2">Finance</div>
    <ul class="space-y-1 text-sm">
      <li><a href="/payments" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('payments') ? 'active' : '' }}">💳 Take Payment</a></li>
      <li><a href="/defaulters" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('defaulters') ? 'active' : '' }}">⚠️ Defaulter List</a></li>
      <li><a href="/accounts" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('accounts') ? 'active' : '' }}">🧾 Accounts Summary</a></li>
      <li><a href="/expenses" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('expenses') ? 'active' : '' }}">💸 Expenses</a></li>
    </ul>

    <div class="text-xs uppercase tracking-wider text-gray-400 px-2 mt-5 mb-2">Library & Reference</div>
    <ul class="space-y-1 text-sm">
      <li><a href="/books" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('books*') ? 'active' : '' }}">📚 Add Books</a></li>
      <li><a href="/reference-profile" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('reference-profile*') ? 'active' : '' }}">🪪 Reference Profile</a></li>
      <li><a href="/print-timetable" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('print-timetable*') ? 'active' : '' }}">🖨️ Print Time Table</a></li>
    </ul>

    <div class="mt-6 text-xs text-gray-400 px-2">Logged in</div>
    <form method="POST" action="/logout" class="px-2 mt-1">@csrf
      <button class="text-red-600 hover:text-red-700 text-sm">Logout</button>
    </form>
  </aside>

  <!-- Main -->
  <main class="pt-14 md:pl-64">
    <div class="max-w-7xl mx-auto p-4 md:p-6">
      @yield('content')
    </div>
  </main>
</body>
</html>
