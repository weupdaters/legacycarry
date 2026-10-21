<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashfree Payment Gateway Demo & Tester</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #2d3748;
        }
        .header-bar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.06);
            background: #fff;
            margin-bottom: 25px;
        }
        .card-custom .card-header {
            background: #ffffff;
            border-bottom: 1px solid #edf2f7;
            padding: 16px 20px;
            font-weight: 600;
            border-radius: 12px 12px 0 0;
        }
        .json-viewer {
            background: #1a202c;
            color: #68d391;
            padding: 16px;
            border-radius: 8px;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
            font-size: 13px;
            max-height: 400px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .badge-env {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 6px;
        }
        .btn-test {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            font-weight: 600;
            padding: 12px 24px;
            color: white;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .btn-test:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
            color: white;
        }
    </style>
</head>
<body>

    <div class="header-bar shadow-sm">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="fw-bold mb-1"><i class="fas fa-wallet me-2"></i>Cashfree Payment Gateway Demo & Tester</h2>
                    <p class="mb-0 text-white-50">Test API keys, diagnose connection errors, and simulate test transactions</p>
                </div>
                <div>
                    <a href="{{ url('/admin/settings?tab=payment') }}" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-cog me-1"></i> Admin Settings
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row">
            <!-- Left Column: Test Configuration Form -->
            <div class="col-lg-5">
                <div class="card card-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-sliders-h me-2 text-primary"></i>1. API Configuration</span>
                        <span id="envBadge" class="badge badge-env bg-warning text-dark">Sandbox Mode</span>
                    </div>
                    <div class="card-body p-4">
                        <form id="cashfreeTestForm">
                            @csrf

                            <!-- Environment -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Target Environment</label>
                                <select class="form-select" id="environment" name="environment" onchange="updateEnvState()">
                                    <option value="sandbox" {{ $defaultEnv !== 'production' ? 'selected' : '' }}>🧪 Sandbox / Testing Mode (sandbox.cashfree.com)</option>
                                    <option value="production" {{ $defaultEnv === 'production' ? 'selected' : '' }}>🚀 Production / Live Mode (api.cashfree.com)</option>
                                </select>
                                <small class="text-muted d-block mt-1">Make sure your App ID & Secret Key match this mode.</small>
                            </div>

                            <!-- App ID -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">App ID (Client ID)</label>
                                <input type="text" class="form-control font-monospace" id="app_id" name="app_id" 
                                       value="{{ $defaultAppId }}" placeholder="e.g. TEST123456... or CF_APP_..." required>
                            </div>

                            <!-- Secret Key -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Secret Key</label>
                                <div class="input-group">
                                    <input type="password" class="form-control font-monospace" id="secret_key" name="secret_key" 
                                           value="{{ $defaultSecret }}" placeholder="e.g. cfsk_ma_test_..." required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="toggleSecret()">
                                        <i class="fas fa-eye" id="secretToggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold text-muted mb-3"><i class="fas fa-shopping-cart me-2"></i>Test Order Details</h6>

                            <!-- Amount -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Test Amount (INR ₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="1" class="form-control fw-bold" id="amount" name="amount" value="10.00" required>
                                    <span class="input-group-text">INR</span>
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold">Customer Name</label>
                                    <input type="text" class="form-control form-control-sm" id="customer_name" name="customer_name" value="Test User" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold">Customer Phone</label>
                                    <input type="text" class="form-control form-control-sm" id="customer_phone" name="customer_phone" value="9999999999" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Customer Email</label>
                                <input type="email" class="form-control form-control-sm" id="customer_email" name="customer_email" value="test@legacycarry.com" required>
                            </div>

                            <button type="submit" class="btn btn-test w-100 mt-2" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i> Hit Cashfree API & Create Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Results & Diagnosis -->
            <div class="col-lg-7">
                <!-- Status & Diagnostic Card -->
                <div class="card card-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-stethoscope me-2 text-info"></i>2. Diagnostic Analysis</span>
                        <span id="responseStatusBadge" class="badge bg-secondary">Awaiting Test</span>
                    </div>
                    <div class="card-body p-4" id="diagnosticContainer">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-satellite-dish fa-3x mb-3 text-secondary"></i>
                            <h5>Ready to test API Keys</h5>
                            <p class="mb-0 small">Fill in the API configuration on the left and click <strong>"Hit Cashfree API"</strong> to diagnose your credentials.</p>
                        </div>
                    </div>
                </div>

                <!-- API Response JSON Viewer -->
                <div class="card card-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-code me-2 text-dark"></i>3. Raw API Response</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyResponseJson()">
                            <i class="fas fa-copy me-1"></i> Copy JSON
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div class="json-viewer" id="jsonOutput">// Click "Hit Cashfree API" to see raw request and response details here...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function toggleSecret() {
            const input = document.getElementById('secret_key');
            const icon = document.getElementById('secretToggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        function updateEnvState() {
            const env = document.getElementById('environment').value;
            const badge = document.getElementById('envBadge');
            if (env === 'production') {
                badge.className = 'badge badge-env bg-success text-white';
                badge.innerText = '🚀 Production Mode';
            } else {
                badge.className = 'badge badge-env bg-warning text-dark';
                badge.innerText = '🧪 Sandbox Mode';
            }
        }

        updateEnvState();

        document.getElementById('cashfreeTestForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const diagnosticContainer = document.getElementById('diagnosticContainer');
            const jsonOutput = document.getElementById('jsonOutput');
            const statusBadge = document.getElementById('responseStatusBadge');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Connecting to Cashfree...';

            statusBadge.className = 'badge bg-info';
            statusBadge.innerText = 'Sending Request...';

            diagnosticContainer.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <h5>Sending API Request to Cashfree...</h5>
                    <p class="text-muted small">Validating headers, signatures, and environment endpoints.</p>
                </div>
            `;

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('{{ route('cashfree.test.order') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const resData = await response.json();
                jsonOutput.innerText = JSON.stringify(resData, null, 2);

                if (resData.success) {
                    statusBadge.className = 'badge bg-success';
                    statusBadge.innerText = 'HTTP ' + resData.status_code + ' Success';

                    let paymentActions = '';
                    if (resData.payment_link) {
                        paymentActions = `
                            <div class="mt-4 p-3 bg-light rounded border">
                                <h6 class="fw-bold text-success mb-2"><i class="fas fa-check-circle me-1"></i>Checkout Ready!</h6>
                                <p class="small text-muted mb-3">Order session ID: <code>${resData.payment_session_id || 'N/A'}</code></p>
                                <a href="${resData.payment_link}" target="_blank" class="btn btn-success fw-bold">
                                    <i class="fas fa-external-link-alt me-2"></i> Open Test Cashfree Checkout Page
                                </a>
                            </div>
                        `;
                    }

                    diagnosticContainer.innerHTML = `
                        <div class="alert alert-success shadow-sm mb-3">
                            <h5 class="alert-heading fw-bold mb-1">${resData.analysis.title}</h5>
                            <p class="mb-0">${resData.analysis.details}</p>
                        </div>
                        <div class="row g-2 mb-2 text-muted small">
                            <div class="col-sm-6"><strong>Endpoint:</strong> <code>${resData.endpoint}</code></div>
                            <div class="col-sm-6"><strong>Response Time:</strong> ${resData.duration_ms} ms</div>
                        </div>
                        ${paymentActions}
                    `;
                } else {
                    statusBadge.className = 'badge bg-danger';
                    statusBadge.innerText = 'HTTP ' + (resData.status_code || 500) + ' Failed';

                    let recommendationsHtml = '';
                    if (resData.analysis?.recommendation) {
                        recommendationsHtml = `
                            <div class="mt-3 p-3 bg-white rounded border border-warning">
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-tools me-1 text-warning"></i>How to resolve:</h6>
                                <ul class="mb-0 ps-3 small text-dark">
                                    ${resData.analysis.recommendation.map(r => `<li class="mb-1">${r}</li>`).join('')}
                                </ul>
                            </div>
                        `;
                    }

                    diagnosticContainer.innerHTML = `
                        <div class="alert alert-danger shadow-sm mb-3">
                            <h5 class="alert-heading fw-bold mb-1">${resData.analysis?.title || 'API Request Failed'}</h5>
                            <p class="mb-0">${resData.analysis?.details || resData.error || 'Check details below.'}</p>
                        </div>
                        <div class="row g-2 mb-2 text-muted small">
                            <div class="col-sm-6"><strong>Endpoint:</strong> <code>${resData.endpoint || 'N/A'}</code></div>
                            <div class="col-sm-6"><strong>Response Time:</strong> ${resData.duration_ms || 0} ms</div>
                        </div>
                        ${recommendationsHtml}
                    `;
                }

            } catch (err) {
                statusBadge.className = 'badge bg-danger';
                statusBadge.innerText = 'Error';
                diagnosticContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <h5 class="alert-heading fw-bold">Network or Server Error</h5>
                        <p class="mb-0">${err.message}</p>
                    </div>
                `;
                jsonOutput.innerText = '// Error: ' + err.message;
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Hit Cashfree API & Create Order';
            }
        });

        function copyResponseJson() {
            const text = document.getElementById('jsonOutput').innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied raw JSON to clipboard!');
            });
        }
    </script>
</body>
</html>
