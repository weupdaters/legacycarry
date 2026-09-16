<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashfree Gateway Tester</title>
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
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            background: #1e293b;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid #334155;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #334155;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            background: {{ $cfConfig['is_live'] ? '#166534; color: #4ade80;' : '#854d0e; color: #fde047;' }};
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #475569;
            background: #0f172a;
            color: #fff;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: #1d4ed8;
        }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .output-box {
            margin-top: 25px;
            padding: 16px;
            border-radius: 8px;
            background: #090d16;
            border: 1px solid #334155;
            font-family: monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-break: break-all;
            display: none;
        }
        .success-box {
            margin-top: 20px;
            padding: 16px;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid #22c55e;
            border-radius: 8px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cashfree Live Payment Tester</h1>
            <span class="badge">{{ $cfConfig['is_live'] ? '🟢 LIVE PRODUCTION' : '🟡 SANDBOX MODE' }}</span>
        </div>

        <form id="testerForm">
            @csrf
            <div class="form-group">
                <label>Amount (INR ₹):</label>
                <input type="number" id="amount" value="1.00" step="0.5" required>
            </div>
            <div class="form-group">
                <label>Customer Name:</label>
                <input type="text" id="name" value="Test Customer" required>
            </div>
            <div class="form-group">
                <label>Customer Phone:</label>
                <input type="tel" id="phone" value="9999999999" required>
            </div>
            <div class="form-group">
                <label>Customer Email:</label>
                <input type="email" id="email" value="customer@test.com" required>
            </div>

            <button type="submit" id="submitBtn" class="btn-submit">🚀 Pay Now with Cashfree</button>
        </form>

        <div id="successBox" class="success-box">
            <h4 style="color: #4ade80; margin-bottom: 8px;">Order Created Successfully!</h4>
            <p style="font-size: 13px; color: #cbd5e1;">Payment Session ID: <strong id="sessionIdText"></strong></p>
            <p style="font-size: 13px; color: #cbd5e1; margin-top: 6px;">Opening Cashfree payment gateway...</p>
        </div>

        <div id="outputBox" class="output-box"></div>
    </div>

    <script>
        const isLive = {{ $cfConfig['is_live'] ? 'true' : 'false' }};
        const cashfree = Cashfree({
            mode: isLive ? "production" : "sandbox"
        });

        document.getElementById('testerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const output = document.getElementById('outputBox');
            const successBox = document.getElementById('successBox');

            btn.disabled = true;
            btn.innerText = "Creating Order in Cashfree...";
            output.style.display = 'none';
            successBox.style.display = 'none';

            try {
                const response = await fetch('/cashfree-tester/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        amount: document.getElementById('amount').value,
                        name: document.getElementById('name').value,
                        phone: document.getElementById('phone').value,
                        email: document.getElementById('email').value,
                    })
                });

                const data = await response.json();
                output.style.display = 'block';
                output.innerText = JSON.stringify(data, null, 2);

                if (data.success && data.payment_session_id) {
                    successBox.style.display = 'block';
                    document.getElementById('sessionIdText').innerText = data.payment_session_id;

                    // Trigger official Cashfree Checkout
                    let checkoutOptions = {
                        paymentSessionId: data.payment_session_id,
                        redirectTarget: "_self"
                    };

                    cashfree.checkout(checkoutOptions);
                } else {
                    alert(data.message || "Failed to create Cashfree order.");
                }
            } catch (err) {
                console.error(err);
                output.style.display = 'block';
                output.innerText = "Error: " + err.message;
            } finally {
                btn.disabled = false;
                btn.innerText = "🚀 Pay Now with Cashfree";
            }
        });
    </script>
</body>
</html>
