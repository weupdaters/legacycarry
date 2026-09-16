<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        body {
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #1e293b;
        }
        .card {
            background: #ffffff;
            padding: 35px 25px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            text-align: center;
            max-width: 480px;
            width: 90%;
        }
        .icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 16px;
        }
        .icon.success {
            background: #dcfce7;
            color: #16a34a;
        }
        .icon.error {
            background: #fee2e2;
            color: #dc2626;
        }
        .icon.pending {
            background: #fef3c7;
            color: #d97706;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        p {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 20px;
        }
        .details {
            background: #f1f5f9;
            padding: 16px;
            border-radius: 10px;
            text-align: left;
            font-size: 13px;
            margin-bottom: 24px;
        }
        .details div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .details div:last-child {
            margin-bottom: 0;
        }
        .details span.label {
            color: #64748b;
        }
        .details span.val {
            font-weight: 600;
            color: #0f172a;
        }
        .btn {
            display: inline-block;
            padding: 10px 24px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        @if($status === 'success')
            <div class="icon success">✓</div>
            <h1>Payment Successful!</h1>
            <p>{{ $message }}</p>
        @elseif($status === 'pending')
            <div class="icon pending">⏳</div>
            <h1>Payment Pending / Cancelled</h1>
            <p>{{ $message }}</p>
        @else
            <div class="icon error">✕</div>
            <h1>Payment Failed</h1>
            <p>{{ $message }}</p>
        @endif

        @if(!empty($orderId))
            <div class="details">
                <div>
                    <span class="label">Order ID:</span>
                    <span class="val">{{ $orderId }}</span>
                </div>
                @if(!empty($paymentDetails['cf_payment_id']))
                <div>
                    <span class="label">CF Payment ID:</span>
                    <span class="val">{{ $paymentDetails['cf_payment_id'] }}</span>
                </div>
                @endif
                @if(!empty($paymentDetails['payment_amount']))
                <div>
                    <span class="label">Amount:</span>
                    <span class="val">₹{{ number_format($paymentDetails['payment_amount'], 2) }}</span>
                </div>
                @endif
                @if(!empty($paymentDetails['payment_method']))
                <div>
                    <span class="label">Payment Method:</span>
                    <span class="val">{{ json_encode($paymentDetails['payment_method']) }}</span>
                </div>
                @endif
            </div>
        @endif

        <a href="/" class="btn">Return to Home</a>
    </div>
</body>
</html>
