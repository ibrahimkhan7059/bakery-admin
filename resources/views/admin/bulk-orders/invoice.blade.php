<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BakeHub - Receipt</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 10px;
                font-family: Arial, sans-serif;
                font-size: 12px;
            }
            .no-print {
                display: none;
            }
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ccc;
        }
        .receipt-header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 18px;
        }
        .receipt-header p {
            margin: 3px 0;
            color: #666;
            font-size: 12px;
        }
        .receipt-info {
            margin-bottom: 15px;
            font-size: 12px;
        }
        .receipt-info p {
            margin: 3px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12px;
        }
        .items-table th {
            text-align: left;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .items-table td {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .total-section {
            text-align: right;
            margin-top: 10px;
            font-size: 12px;
        }
        .total-section table {
            width: 100%;
        }
        .total-section td {
            padding: 3px;
        }
        .total-section .grand-total {
            font-weight: bold;
            border-top: 1px dashed #ccc;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 11px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 8px 15px;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .print-button:hover {
            background-color: #34495e;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        <i class="fas fa-print"></i> Print Receipt
    </button>

    <div class="receipt-header">
        <h1>BakeHub</h1>
        <p>Bulk Order Receipt</p>
        <p>Order #{{ $bulkOrder->id }}</p>
        <p>{{ $bulkOrder->created_at->format('M d, Y h:i A') }}</p>
    </div>

    <div class="receipt-info">
        <p><strong>Customer:</strong> {{ $bulkOrder->customer_name }}</p>
        <p><strong>Phone:</strong> {{ $bulkOrder->customer_phone }}</p>
        <p><strong>Address:</strong> {{ $bulkOrder->delivery_address }}</p>
        <p><strong>Delivery:</strong> {{ $bulkOrder->delivery_time }}</p>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bulkOrder->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>PKR {{ number_format($item->price, 2) }}</td>
                <td>PKR {{ number_format($item->price * $item->quantity * (1 - $item->discount), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <table>
            <tr>
                <td>Total Amount:</td>
                <td>PKR {{ number_format($bulkOrder->items->sum(function($item) { return $item->price * $item->quantity; }), 2) }}</td>
            </tr>
            <tr>
                <td>Discount Amount:</td>
                <td>PKR {{ number_format(($bulkOrder->items->sum(function($item) { return $item->price * $item->quantity; }) - $bulkOrder->total_amount), 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>Final Amount:</td>
                <td>PKR {{ number_format($bulkOrder->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Advance Payment:</td>
                <td>PKR {{ number_format($bulkOrder->advance_payment, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>Remaining Amount:</td>
                <td>PKR {{ number_format($bulkOrder->total_amount - $bulkOrder->advance_payment, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Thank you for choosing BakeHub!</p>
        <p>For any queries, please contact us at support@bakehub.com</p>
    </div>
</body>
</html> 