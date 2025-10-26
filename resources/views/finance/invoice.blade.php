<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .action-buttons {
            max-width: 800px;
            margin: 0 auto 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-back {
            background: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background: #5a6268;
        }
        .btn-print {
            background: #007bff;
            color: white;
        }
        .btn-print:hover {
            background: #0056b3;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #333;
        }
        .invoice-header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
        }
        .invoice-header .ref {
            font-size: 14px;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            padding: 8px 0;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        table th {
            background: #f8f8f8;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
        }
        .amount-total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            padding: 15px;
            background: #f8f8f8;
            border-radius: 5px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .action-buttons {
                display: none;
            }
            .invoice-container {
                box-shadow: none;
                padding: 20px;
                max-width: 100%;
            }
            .section {
                page-break-inside: avoid;
            }
            table {
                page-break-inside: avoid;
            }
        }
        
        @page {
            size: A4;
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <a href="{{ route('payments') }}" class="btn btn-back">← Back to Payments</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ Print Invoice</button>
    </div>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>INVOICE</h1>
            <div class="ref">{{ $invoice->reference }}</div>
        </div>

        <!-- Student Information -->
        <div class="section">
            <div class="section-title">Student Information</div>
            <div class="info-row">
                <div class="info-label">Reference:</div>
                <div class="info-value">{{ $invoice->student->reference }}</div>
            </div>
        </div>

        <!-- Period Information -->
        <div class="section">
            <div class="section-title">Billing Period</div>
            <div class="info-row">
                <div class="info-label">Period Type:</div>
                <div class="info-value">{{ ucfirst($invoice->student->period ?? 'Weekly') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">From:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($invoice->period_from)->format('d M Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">To:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($invoice->period_to)->format('d M Y') }}</div>
            </div>
        </div>

        <!-- Payment Transactions -->
        <div class="section">
            <div class="section-title">Payment Transactions</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->transactions as $t)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($t->paid_on)->format('d M Y') }}</td>
                        <td>£{{ number_format($t->amount, 2) }}</td>
                        <td>{{ $t->method }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            // Calculate overall balance (same logic as payment page)
            $student = $invoice->student;
            
            // Get total paid via JOIN (same as PaymentController)
            $totalPaid = \App\Models\PaymentTransaction::leftJoin('invoices', 'payment_transactions.invoice_id', '=', 'invoices.id')
                ->where('invoices.student_id', $student->id)
                ->sum('payment_transactions.amount');
            
            // Get books - NOTE: student_reference now stores student ID, not reference string
            $assignedBooks = \App\Models\Book::leftJoin('students', 'books.student_reference', '=', 'students.id')
                ->where('students.reference', $student->reference)
                ->select('books.*')
                ->get();
            
            $studentSubjects = \App\Models\Timetable::where('student_id', $student->id)->pluck('subject')->unique();
            $subjectBooks = \App\Models\Book::whereNull('student_reference')->whereIn('subject', $studentSubjects)->get();
            $totalBookPrice = $assignedBooks->sum('price') + $subjectBooks->sum('price');
            
            // Calculate balance (Expected - Paid) - This matches "Payment Pending" on payment page
            // Use pending_amount if set, otherwise fall back to payment
            $expectedTotal = ($student->pending_amount ?? $student->payment ?? 0) + $totalBookPrice;
            $balanceDue = max(0, $expectedTotal - $totalPaid);
            
            // For this invoice
            $invoicePaid = $invoice->transactions->sum('amount');
        @endphp

        <!-- Balance Amount (only if pending) - Shows same as "Payment Pending" on payment page -->
        @if($balanceDue > 0)
        <div class="amount-total" style="background: transparent; border: none; padding: 10px 15px; font-size: 16px; font-weight: normal;">
            Balance Amount: £{{ number_format($balanceDue, 2) }}
        </div>
        @endif

        <!-- Total Amount -->
        <div class="amount-total">
            Total Amount Paid: £{{ number_format($invoicePaid, 2) }}
        </div>
    </div>
</body>
</html>
