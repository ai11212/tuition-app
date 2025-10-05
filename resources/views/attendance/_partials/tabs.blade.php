<div class="mb-4 border-b">
  <nav class="flex gap-4 text-sm">
    <a href="{{ route('attendance.sheet') }}" class="pb-2 border-b-2 {{ request()->routeIs('attendance.sheet') ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-600 hover:text-blue-700' }}">New Attendance</a>
    <a href="{{ route('attendance.view') }}" class="pb-2 border-b-2 {{ request()->routeIs('attendance.view') ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-600 hover:text-blue-700' }}">View Attendance</a>
    <a href="{{ route('attendance.status') }}" class="pb-2 border-b-2 {{ request()->routeIs('attendance.status') ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-600 hover:text-blue-700' }}">Attendance Data</a>
    <a href="{{ route('attendance.staff') }}" class="pb-2 border-b-2 {{ request()->routeIs('attendance.staff') ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-600 hover:text-blue-700' }}">Staff Back Attendance</a>
  </nav>
</div>
