@extends('layouts.app')

@section('title', 'Expense Management')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Page Title --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">💸 Expense Management</h1>
        <p class="text-gray-600 mt-1">Track and manage all business expenses</p>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('expenses') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="from" value="{{ $from }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="to" value="{{ $to }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Category or notes..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    🔍 Filter
                </button>
                <a href="{{ route('expenses') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                    🔄 Reset
                </a>
                <a href="{{ route('expenses.export', request()->query()) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition ml-auto">
                    📥 Export CSV
                </a>
            </div>
        </form>
    </div>

    {{-- Add Expense Form --}}
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">➕ Add New Expense</h2>
        <form method="POST" action="{{ route('expenses.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date <span class="text-red-500">*</span></label>
                <input type="date" name="expense_on" value="{{ old('expense_on', date('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('expense_on') border-red-500 @enderror">
                @error('expense_on')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" placeholder="0.00" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amount') border-red-500 @enderror">
                @error('amount')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                <select name="method" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('method') border-red-500 @enderror">
                    <option value="">Select Method</option>
                    <option value="Cash" {{ old('method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Card" {{ old('method') == 'Card' ? 'selected' : '' }}>Card</option>
                    <option value="Bank" {{ old('method') == 'Bank' ? 'selected' : '' }}>Bank Transfer</option>
                </select>
                @error('method')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                <select name="category" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('category') border-red-500 @enderror">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror">
                    <option value="expense" {{ old('type', 'expense') == 'expense' ? 'selected' : '' }}>Expense</option>
                    <option value="refund" {{ old('type') == 'refund' ? 'selected' : '' }}>Refund</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Optional notes..." maxlength="255"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror">
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                    💾 Save Expense
                </button>
            </div>
        </form>
    </div>

    {{-- Expense List --}}
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">📋 Expense Records</h2>
            <p class="text-sm text-gray-600 mt-1">Total: {{ $expenses->total() }} records</p>
        </div>

        @if($expenses->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($expenses as $expense)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($expense->expense_on)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $expense->category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $expense->type == 'refund' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $expense->type == 'refund' ? '+' : '-' }} £{{ number_format($expense->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $expense->method }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs rounded {{ $expense->type == 'refund' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($expense->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $expense->notes ?: '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $expensePayload = [
                                            'id'         => $expense->id,
                                            'expense_on' => \Carbon\Carbon::parse($expense->expense_on)->format('Y-m-d'),
                                            'amount'     => $expense->amount,
                                            'method'     => $expense->method,
                                            'category'   => $expense->category,
                                            'type'       => $expense->type,
                                            'notes'      => $expense->notes,
                                        ];
                                    @endphp
                                    <button type="button" class="text-blue-600 hover:text-blue-900 font-medium mr-3"
                                            onclick='openExpenseEdit(@json($expensePayload))'>
                                        ✏️ Edit
                                    </button>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense->id) }}"
                                          onsubmit="return confirm('Are you sure you want to delete this expense?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $expenses->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-gray-500 text-lg">📭 No expenses found</p>
                <p class="text-gray-400 text-sm mt-2">Add your first expense using the form above</p>
            </div>
        @endif
    </div>
</div>

{{-- Edit Expense Modal --}}
<div id="expenseEditModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">✏️ Edit Expense</h2>
        <button onclick="closeExpenseEdit()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>

      <form method="POST" id="expenseEditForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date <span class="text-red-500">*</span></label>
            <input type="date" name="expense_on" id="exp_edit_date" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
            <input type="number" name="amount" id="exp_edit_amount" step="0.01" min="0.01" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
            <select name="method" id="exp_edit_method" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Bank">Bank Transfer</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
            <select name="category" id="exp_edit_category" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
            <select name="type" id="exp_edit_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="expense">Expense</option>
                <option value="refund">Refund</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <input type="text" name="notes" id="exp_edit_notes" maxlength="255" placeholder="Optional notes..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2 flex justify-end gap-3">
            <button type="button" onclick="closeExpenseEdit()"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">💾 Update Expense</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function expSetSelect(sel, value) {
    if (value && !Array.from(sel.options).some(function (o) { return o.value === value; })) {
        const o = document.createElement('option');
        o.value = value;
        o.textContent = value;
        sel.appendChild(o);
    }
    sel.value = value || '';
}
function openExpenseEdit(e) {
    document.getElementById('expenseEditForm').action = '/expenses/' + e.id;
    document.getElementById('exp_edit_date').value = e.expense_on || '';
    document.getElementById('exp_edit_amount').value = e.amount || '';
    expSetSelect(document.getElementById('exp_edit_method'), e.method);
    expSetSelect(document.getElementById('exp_edit_category'), e.category);
    document.getElementById('exp_edit_type').value = e.type || 'expense';
    document.getElementById('exp_edit_notes').value = e.notes || '';
    document.getElementById('expenseEditModal').classList.remove('hidden');
}
function closeExpenseEdit() {
    document.getElementById('expenseEditModal').classList.add('hidden');
}
document.getElementById('expenseEditModal').addEventListener('click', function (e) {
    if (e.target === this) closeExpenseEdit();
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeExpenseEdit();
});
</script>
@endsection
