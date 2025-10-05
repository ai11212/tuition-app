@if(session('ok'))
  <div class="p-3 mb-4 rounded bg-green-50 border border-green-200 text-green-800">{{ session('ok') }}</div>
@endif
@foreach($errors->all() as $e)
  <div class="p-3 mb-2 rounded bg-red-50 border border-red-200 text-red-800">{{ $e }}</div>
@endforeach
