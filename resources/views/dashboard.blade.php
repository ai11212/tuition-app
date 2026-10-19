@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-bold mb-4">Dashboard</h1>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- AUTO-ADD-FIRST-STUDENT START -->
    <script>
    (function(){
      function clickAdd(){
        try {
          if (typeof window.addSibling === "function") { window.addSibling(); return true; }
          var btn = document.querySelector("#add-sibling-btn,[data-add-sibling]");
          if (!btn) {
            btn = Array.from(document.querySelectorAll("button,a")).find(function(el){
              return /\+\s*Add\s*sibling/i.test((el.textContent||"").trim());
            });
            if (btn) btn.setAttribute("id","add-sibling-btn");
          }
          if (btn) { btn.click(); return true; }
        } catch(e) { /* ignore */ }
        return false;
      }
      function ensureFirstRow(){
        var box = document.getElementById("students-container") || document.querySelector(".students-container");
        var have = box && box.querySelectorAll(".student-row").length>0;
        if (!have) clickAdd();
      }
      document.addEventListener("DOMContentLoaded", function(){
        if (!clickAdd()){
          // Try repeatedly while the DOM hydrates
          var tries=0, iv = setInterval(function(){
            tries++; if (clickAdd() || tries>40) clearInterval(iv);
          }, 150);
          // Also watch for late-added nodes
          var mo = new MutationObserver(function(){ if (clickAdd()) mo.disconnect(); });
          try { mo.observe(document.body,{childList:true,subtree:true}); setTimeout(function(){mo.disconnect();}, 10000); } catch(e) {}
        }
        // One last check after a short delay
        setTimeout(ensureFirstRow, 500);
      });
    })();
    </script>
    <!-- AUTO-ADD-FIRST-STUDENT END -->
  @can('admin')
  <div class="p-4 rounded-xl bg-blue-50 border"><div class="text-sm">Total Students</div><div class="text-2xl font-semibold">{{ $students }}</div></div>
  <div class="p-4 rounded-xl bg-green-50 border"><div class="text-sm">Staff</div><div class="text-2xl font-semibold">{{ $staff }}</div></div>
  @endcan
  @canany(['admin','books'])
  <div class="p-4 rounded-xl bg-yellow-50 border"><div class="text-sm">Books</div><div class="text-2xl font-semibold">{{ $books }}</div></div>
  @endcanany
  {{-- Pending card hidden on request (20/07/2026) — flip to true to restore --}}
  @if(false)
  <div class="p-4 rounded-xl bg-red-50 border"><div class="text-sm">Pending</div><div class="text-2xl font-semibold">{{ $pending }}</div></div>
  @endif
</div>

{{-- An account with no permissions can sign in but reach nothing. Say so,
     rather than leaving them looking at an empty page. --}}
@canany(['admin','admission','payments','attendance','books','teachers','lookup'])
<h2 class="font-semibold text-lg mb-3">Quick Actions</h2>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
  @can('admission')
  <a href="/students/create" class="p-3 rounded-lg border bg-white hover:bg-blue-50">New Admission</a>
  @endcan
  @can('payments')
  <a href="/payments" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Take Payment</a>
  @endcan
  @can('lookup')
  <a href="/reference-profile" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Reference Profile</a>
  @endcan
  @can('finance')
  <a href="/accounts" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Accounts Summary</a>
  @endcan
  @can('books')
  <a href="/books/create" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Issue Books</a>
  @endcan
  {{-- Defaulter List card hidden on request (20/07/2026) — flip to true to restore --}}
  @if(false)
  <a href="/payment-verification?search=&status=pending" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Defaulter List</a>
  @endif
  @can('admin')
  <a href="/students" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Print Time Table</a>
  @endcan
  @can('attendance')
  <a href="/attendance" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Attendance</a>
  @endcan
  @can('books')
  <a href="/book-library" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Book Library</a>
  @endcan
  @can('teachers')
  <a href="/teachers/create" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Add Teacher</a>
  @endcan
  @can('admin')
  <a href="{{ route('admin.users.index') }}" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Users &amp; Access</a>
  @can('super')
  <a href="{{ route('admin.activity') }}" class="p-3 rounded-lg border bg-white hover:bg-blue-50">Activity Log</a>
  @endcan
  @endcan
</div>
@else
<div class="p-6 rounded-xl bg-amber-50 border border-amber-200 text-amber-900">
  <div class="font-semibold mb-1">Your account doesn't have any permissions yet</div>
  <div class="text-sm">Ask your administrator to give you access, then sign out and back in.</div>
</div>
@endcanany
@endsection
