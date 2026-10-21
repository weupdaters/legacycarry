<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashfree Return Callback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card-receipt {
            max-width: 500px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
            background: #fff;
        }
    </style>
</head>
<body>

    <div class="card card-receipt text-center p-4">
        <div class="my-3">
            <i class="fas fa-check-circle fa-4x text-success"></i>
        </div>
        <h4 class="fw-bold mb-1">Cashfree Return Callback</h4>
        <p class="text-muted small mb-4">The payment flow has redirected back to your application.</p>

        <div class="bg-light p-3 rounded mb-4 text-start font-monospace small">
            <div><strong>Order ID:</strong> <span class="text-primary">{{ $order_id ?? request('order_id') ?? 'N/A' }}</span></div>
            <div class="mt-1"><strong>Status:</strong> Returned successfully from checkout</div>
        </div>

        <div class="d-flex gap-2 justify-content-center">
            <a href="{{ route('cashfree.tester') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Cashfree Tester
            </a>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                <i class="fas fa-home me-1"></i> Home
            </a>
        </div>
    </div>

</body>
</html>
