<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller {
    /**
     * Show expense management page - list expenses and add form
     * Similar to payments page structure
     */
    public function index(Request $request)
    {
        $from = $request->input('from', '');
        $to = $request->input('to', '');
        $category = $request->input('category', '');
        $search = $request->input('search', '');

        // Build query with filters
        $query = Expense::query();

        if ($from) {
            $query->whereDate('expense_on', '>=', $from);
        }
        if ($to) {
            $query->whereDate('expense_on', '<=', $to);
        }
        if ($category) {
            $query->where('category', $category);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('category', 'LIKE', '%' . $search . '%')
                  ->orWhere('notes', 'LIKE', '%' . $search . '%');
            });
        }

        // Get expenses ordered by date (newest first)
        $expenses = $query->orderBy('expense_on', 'desc')
                          ->orderBy('id', 'desc')
                          ->paginate(25)
                          ->withQueryString();

        // Category list for dropdown
        $categories = [
            'Salary',
            'Teacher Salary',
            'Rent',
            'Utilities',
            'Supplies',
            'Maintenance',
            'Marketing',
            'Transport',
            'Books',
            'Equipment',
            'Other'
        ];

        return view('expenses.index', compact('expenses', 'from', 'to', 'category', 'search', 'categories'));
    }
    
    public function create(){ return view('finance.expense_create'); }
    
    public function store(Request $r){
        $data = $r->validate([
            'expense_on' => 'required|date',
            'amount'     => 'required|numeric|min:0.01',
            'method'     => 'required|string', // Cash | Card | Bank
            'type'       => 'nullable|string|in:expense,refund', // expense | refund
            'category'   => 'required|string|max:50',
            'notes'      => 'nullable|string|max:255',
        ]);
        
        // Set default type if not provided
        $data['type'] = $data['type'] ?? 'expense';
        
        Expense::create($data);

        return redirect()->route('expenses')
            ->with('success', 'Expense added successfully!');
    }

    /**
     * Update an existing expense (same rules as store)
     */
    public function update(Request $r, $id)
    {
        $data = $r->validate([
            'expense_on' => 'required|date',
            'amount'     => 'required|numeric|min:0.01',
            'method'     => 'required|string', // Cash | Card | Bank
            'type'       => 'nullable|string|in:expense,refund',
            'category'   => 'required|string|max:50',
            'notes'      => 'nullable|string|max:255',
        ]);

        $data['type'] = $data['type'] ?? 'expense';

        try {
            Expense::findOrFail($id)->update($data);

            return redirect()->route('expenses')
                ->with('success', 'Expense updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('expenses')
                ->with('error', 'Failed to update expense.');
        }
    }

    /**
     * Delete an expense
     */
    public function destroy($id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $expense->delete();

            return redirect()->route('expenses')
                ->with('success', 'Expense deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('expenses')
                ->with('error', 'Failed to delete expense: ' . $e->getMessage());
        }
    }

    /**
     * Export expenses to CSV
     */
    public function exportCsv(Request $request)
    {
        $from = $request->input('from', '');
        $to = $request->input('to', '');
        $category = $request->input('category', '');

        // Build query with filters
        $query = Expense::query();

        if ($from) {
            $query->whereDate('expense_on', '>=', $from);
        }
        if ($to) {
            $query->whereDate('expense_on', '<=', $to);
        }
        if ($category) {
            $query->where('category', $category);
        }

        $expenses = $query->orderBy('expense_on', 'desc')
                          ->orderBy('id', 'desc')
                          ->get();

        $filename = 'expenses_' . date('Ymd_His') . '.csv';
        
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        $out = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($out, ['Date', 'Category', 'Amount', 'Method', 'Type', 'Notes']);
        
        // CSV rows
        foreach ($expenses as $expense) {
            fputcsv($out, [
                $expense->expense_on,
                $expense->category,
                number_format($expense->amount, 2),
                $expense->method,
                $expense->type,
                $expense->notes
            ]);
        }
        
        fclose($out);
        exit;
    }
}
