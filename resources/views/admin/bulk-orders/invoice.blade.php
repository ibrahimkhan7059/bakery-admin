<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Order Receipt #{{ $bulkOrder->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #FF6B6B;
            --secondary-color: #4ECDC4;
            --accent-color: #FFE66D;
            --dark-color: #2C3E50;
            --light-color: #F7F9FC;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--light-color);
        }

        .receipt {
            max-width: 400px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed #eee;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            color: var(--primary-color);
            font-weight: 700;
        }

        .header p {
            margin: 8px 0;
            color: #666;
        }

        .order-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .order-info p {
            margin: 5px 0;
            color: #666;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items th, .items td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .items th {
            background-color: var(--dark-color);
            color: white;
            font-weight: 500;
        }

        .items tr:last-child td {
            border-bottom: none;
        }

        .total {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .total p {
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
            color: #666;
        }

        .grand-total {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color) !important;
            border-top: 2px solid #eee;
            padding-top: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px dashed #eee;
        }

        .thank-you {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .print-btn {
            background: var(--dark-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .print-btn:hover {
            background: #1a252f;
            transform: translateY(-2px);
        }

        .close-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-left: 10px;
        }

        .close-btn:hover {
            background: #1a252f;
            transform: translateY(-2px);
        }

        @media print {
            .no-print {
                display: none;
            }
            .receipt {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>BakeHub</h1>
            <p>Bulk Order Receipt</p>
            <p>Order #{{ $bulkOrder->id }}</p>
            <p>Date: {{ $bulkOrder->created_at ? $bulkOrder->created_at->format('F j, Y g:i A') : now()->format('F j, Y g:i A') }}</p>
        </div>

        <div class="order-info">
            <p><strong>Customer:</strong> {{ $bulkOrder->customer_name }}</p>
            <p><strong>Phone:</strong> {{ $bulkOrder->customer_phone }}</p>
            <p><strong>Address:</strong> {{ $bulkOrder->delivery_address }}</p>
            <p><strong>Delivery:</strong> {{ $bulkOrder->delivery_time }}</p>
        </div>

        <table class="items">
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

        <div class="total">
            <p>
                <span>Subtotal:</span>
                <span>PKR {{ number_format($bulkOrder->items->sum(function($item) { return $item->price * $item->quantity; }), 2) }}</span>
            </p>
            <p>
                <span>Discount:</span>
                <span>PKR {{ number_format(($bulkOrder->items->sum(function($item) { return $item->price * $item->quantity; }) - $bulkOrder->total_amount), 2) }}</span>
            </p>
            <p class="grand-total">
                <span>Final Amount:</span>
                <span>PKR {{ number_format($bulkOrder->total_amount, 2) }}</span>
            </p>
            <p>
                <span>Advance Payment:</span>
                <span>PKR {{ number_format($bulkOrder->advance_payment, 2) }}</span>
            </p>
            <p class="grand-total">
                <span>Remaining Amount:</span>
                <span>PKR {{ number_format($bulkOrder->total_amount - $bulkOrder->advance_payment, 2) }}</span>
            </p>
        </div>

        <div class="footer">
            <p class="thank-you">Thank you for your bulk order!</p>
            <p>We appreciate your business</p>
            <p>Please come again</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button class="print-btn" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Receipt
        </button>
        <button class="close-btn" onclick="window.close()">
            <i class="bi bi-x-lg"></i> Close
        </button>
    </div>
</body>
</html> 