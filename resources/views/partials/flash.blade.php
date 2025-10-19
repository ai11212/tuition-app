@if(session('ok'))
  <div class="p-3 mb-4 rounded bg-green-50 border border-green-200 text-green-800">{{ session('ok') }}</div>
@endif
@if(session('warning'))
  <div class="p-3 mb-4 rounded bg-yellow-50 border border-yellow-200 text-yellow-800">{{ session('warning') }}</div>
@endif
@foreach($errors->all() as $e)
  <div class="p-3 mb-2 rounded bg-red-50 border border-red-200 text-red-800">{{ $e }}</div>
@endforeach
