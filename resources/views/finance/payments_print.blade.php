<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Payments</title>
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial; margin:20px;}
  table{border-collapse:collapse; width:100%}
  th,td{border:1px solid #ddd; padding:6px; font-size:13px}
  th{background:#f3f4f6; text-align:left}
  .meta{margin-bottom:10px; font-size:14px}
  @media print{ .no-print{display:none} }
</style>
</head>
<body>
  <div class="meta"><b>Payments</b> — Reference: {{ $ref ?: 'All' }}, From: {{ $from ?: '—' }}, To: {{ $to ?: '—' }}</div>
  <table>
    <thead><tr>
      <th>Reference</th><th>Student</th><th>Paid at</th><th>Method</th><th>Amount (£)</th><th>Notes</th><th>Invoice</th>
    </tr></thead>
    <tbody>
      @foreach($payments as $p)
      <tr>
        <td>{{ $p->student_ref }}</td>
        <td>{{ $p->student_name }}</td>
        <td>{{ $p->paid_at ? \Carbon\Carbon::parse($p->paid_at)->format('d/m/Y H:i') : (\Carbon\Carbon::parse($p->paid_on)->format('d/m/Y').' 00:00') }}</td>
        <td>{{ $p->method }}</td>
        <td style="text-align:right">£{{ number_format($p->amount,2) }}</td>
        <td>{{ $p->notes }}</td>
        <td>{{ $p->invoice_ref }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="no-print" style="margin-top:10px"><button onclick="window.print()">Print / Save as PDF</button></div>
</body></html>
