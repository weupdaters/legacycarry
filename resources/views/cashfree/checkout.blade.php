<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - Cashfree</title>
    <!-- Cashfree JS SDK v3 -->
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        body {
            background: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #333;
        }
        .card {
            background: #fff;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            text-align: center;
            max-width: 440px;
            width: 90%;
        }
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #2563eb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1e293b;
        }
        p {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 24px;
        }
        .btn-retry {
            display: inline-block;
            padding: 10px 20px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-retry:hover {
            background: #1d4ed8;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 15px;
            background: {{ $isLive ? '#dcfce7; color: #166534;' : '#fef3c7; color: #92400e;' }};
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner" id="spinner"></div>
        <h2 id="title">Connecting to Cashfree...</h2>
        <p id="desc">Please wait while we securely open the payment gateway.</p>
        <button id="btnPay" class="btn-retry" style="display:none;" onclick="triggerCheckout()">Click to Open Payment</button>
        <br>
        <span class="badge">{{ $isLive ? 'LIVE PAYMENT GATEWAY' : 'SANDBOX MODE' }}</span>
    </div>

    <script>
        const paymentSessionId = "{{ $paymentSessionId }}";
        const mode = "{{ $mode }}"; // 'production' or 'sandbox'

        let cashfree;

        try {
            cashfree = Cashfree({
                mode: mode
            });
        } catch (e) {
            console.error("Cashfree Init Error:", e);
            document.getElementById('title').innerText = "Gateway Initialization Failed";
            document.getElementById('desc').innerText = e.message || "Failed to load Cashfree SDK.";
        }

        function triggerCheckout() {
            if (!cashfree || !paymentSessionId) {
                alert("Payment session ID is missing.");
                return;
            }

            let checkoutOptions = {
                paymentSessionId: paymentSessionId,
                redirectTarget: "_self" // opens in current window
            };

            cashfree.checkout(checkoutOptions).then((result) => {
                if (result.error) {
                    console.error("Checkout Error:", result.error);
                    document.getElementById('spinner').style.display = 'none';
                    document.getElementById('title').innerText = "Payment Gateway Error";
                    document.getElementById('desc').innerText = result.error.message || "Unable to launch checkout.";
                    document.getElementById('btnPay').style.display = 'inline-block';
                }
                if (result.redirect) {
                    console.log("Redirecting...");
                }
            }).catch((err) => {
                console.error("Checkout Exception:", err);
                document.getElementById('spinner').style.display = 'none';
                document.getElementById('title').innerText = "Payment Exception";
                document.getElementById('desc').innerText = err.message || "Failed to launch payment.";
                document.getElementById('btnPay').style.display = 'inline-block';
            });
        }

        // Auto trigger checkout after slight delay for SDK load
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(triggerCheckout, 400);
        });
    </script>
</body>
</html>
