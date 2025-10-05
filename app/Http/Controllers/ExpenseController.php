<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller {
    public function create(){ return view('finance.expense_create'); }
    public function store(Request $r){
        $data = $r->validate([
            'expense_on' => 'required|date',
            'amount'     => 'required|numeric|min:0',
            'method'     => 'required|string', // Cash | Card | Bank
            'type'       => 'required|string', // expense | refund
            'category'   => 'nullable|string',
            'notes'      => 'nullable|string',
        ]);
        Expense::create($data);
        return redirect()->route('accounts.summary', [
            'from' => now()->startOfMonth()->toDateString(),
            'to'   => now()->toDateString()
        ])->with('ok','Expense saved');
    }
}
