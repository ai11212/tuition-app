@extends('layouts.app')

@section('title', 'Users & Access')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">👥 Users &amp; Access</h1>
            <p class="text-sm text-gray-500">Give each person their own login and only the access they need.</p>
        </div>
        @can('super')
        <a href="{{ route('admin.activity') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">📋 Activity Log →</a>
        @endcan
    </div>

    @include('partials.flash')

    {{-- Add / Edit form --}}
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4" id="user-form-heading">➕ Add New User</h2>

        <form method="POST" action="{{ route('admin.users.store') }}" id="user-form" class="space-y-4">
            @csrf
            {{-- JS injects _method=PUT here while editing --}}
            <span id="user-form-mode" class="hidden"></span>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" maxlength="100" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email (used to sign in) <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="190" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span class="text-red-500" id="pw-required">*</span>
                        <span class="text-xs text-gray-400 hidden" id="pw-hint">— leave blank to keep the current one</span>
                    </label>
                    <input type="password" name="password" minlength="6" autocomplete="new-password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                    <input type="password" name="password_confirmation" minlength="6" autocomplete="new-password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="border-t pt-4">
                {{-- Only the super user may create or grant admin access --}}
                @can('super')
                <label class="flex items-center gap-2 mb-3">
                    <input type="checkbox" name="is_admin" value="1" id="is-admin" class="rounded border-gray-300">
                    <span class="font-medium text-gray-800">Full admin</span>
                    <span class="text-xs text-gray-500">— everything, including finances, students and salaries</span>
                </label>
                @endcan

                <div id="capability-block" class="pl-6">
                    <div class="text-sm font-medium text-gray-700 mb-2">Or give limited access:</div>
                    @foreach($capabilities as $key => $label)
                        <label class="flex items-start gap-2 mb-2">
                            <input type="checkbox" name="capabilities[]" value="{{ $key }}"
                                   class="capability-box mt-1 rounded border-gray-300">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <label class="flex items-center gap-2 mt-4 pt-3 border-t">
                    <input type="checkbox" name="is_active" value="1" id="is-active" checked class="rounded border-gray-300">
                    <span class="font-medium text-gray-800">Active</span>
                    <span class="text-xs text-gray-500">— untick to block sign-in without deleting their history</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" id="user-submit-btn"
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                    💾 Save User
                </button>
                <button type="button" id="user-cancel-btn" onclick="cancelUserEdit()"
                        class="hidden px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-md hover:bg-gray-300 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    {{-- User list --}}
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Access</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $u->name }}
                                @if($u->id === auth()->id())
                                    <span class="ml-1 text-xs text-gray-400">(you)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $u->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($u->is_super)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Super</span>
                                @elseif($u->is_admin)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Admin</span>
                                @elseif(count($u->capabilityList()))
                                    @foreach($u->capabilityList() as $cap)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst($cap) }}</span>
                                    @endforeach
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">No access</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($u->is_active)
                                    <span class="text-green-700">Active</span>
                                @else
                                    <span class="text-red-600">Disabled</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button type="button"
                                        onclick='editUser(@json($u->id), @json($u->name), @json($u->email), @json((bool) $u->is_admin), @json($u->capabilityList()), @json((bool) $u->is_active))'
                                        class="text-blue-600 hover:text-blue-900 font-medium mr-3">✏️ Edit</button>

                                @if($u->id !== auth()->id() && !$u->is_super)
                                    <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}"
                                          onsubmit="return confirm('Delete {{ addslashes($u->name) }}? Their activity log history is kept.');"
                                          class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">🗑️ Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>

<script>
function syncAdminToggle() {
    // The admin tick-box is only rendered for the super user
    const adminBox = document.getElementById('is-admin');
    const isAdmin  = adminBox ? adminBox.checked : false;
    const block    = document.getElementById('capability-block');
    if (block) block.style.opacity = isAdmin ? '0.4' : '1';
    document.querySelectorAll('.capability-box').forEach(b => b.disabled = isAdmin);
}
const adminToggle = document.getElementById('is-admin');
if (adminToggle) adminToggle.addEventListener('change', syncAdminToggle);

function editUser(id, name, email, isAdmin, caps, isActive) {
    const form = document.getElementById('user-form');
    form.action = '{{ url('admin/users') }}/' + id;
    document.getElementById('user-form-mode').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    form.querySelector('[name="name"]').value  = name;
    form.querySelector('[name="email"]').value = email;
    form.querySelector('[name="password"]').value = '';
    form.querySelector('[name="password_confirmation"]').value = '';

    const editAdminBox = document.getElementById('is-admin');
    if (editAdminBox) editAdminBox.checked = isAdmin;
    document.getElementById('is-active').checked = isActive;
    document.querySelectorAll('.capability-box').forEach(b => b.checked = caps.includes(b.value));
    syncAdminToggle();

    document.getElementById('user-form-heading').textContent = '✏️ Edit User';
    document.getElementById('user-submit-btn').textContent   = 'Update User';
    document.getElementById('user-cancel-btn').classList.remove('hidden');
    document.getElementById('pw-required').classList.add('hidden');
    document.getElementById('pw-hint').classList.remove('hidden');

    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cancelUserEdit() {
    const form = document.getElementById('user-form');
    form.action = '{{ route('admin.users.store') }}';
    document.getElementById('user-form-mode').innerHTML = '';
    form.reset();

    document.getElementById('is-active').checked = true;
    syncAdminToggle();

    document.getElementById('user-form-heading').textContent = '➕ Add New User';
    document.getElementById('user-submit-btn').textContent   = '💾 Save User';
    document.getElementById('user-cancel-btn').classList.add('hidden');
    document.getElementById('pw-required').classList.remove('hidden');
    document.getElementById('pw-hint').classList.add('hidden');
}

syncAdminToggle();
</script>
@endsection
