<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Return & Refund Policy – Legacy Carry</title>
  <meta name="description"
    content="Refund and cancellation policy for Legacy Carry — eligibility, process, timelines and service disputes." />
  <link rel="icon" type="image/png" href="logo.png" />
  <link rel="stylesheet" href="{{ asset('frontend/style.css') }}" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .page-hero {
      background: linear-gradient(160deg, #fff 0%, #eef1f8 60%, #dce6fb 100%);
      padding: 120px 5vw 56px;
      border-bottom: 1.5px solid var(--border);
    }

    .page-hero-inner {
      max-width: 800px;
      margin: 0 auto;
    }

    .page-hero-tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.7rem;
      font-weight: 800;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--blue);
      background: var(--sky);
      border: 1px solid rgba(26, 86, 219, 0.2);
      padding: 4px 14px;
      border-radius: 100px;
      margin-bottom: 16px;
    }

    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.8rem;
      color: var(--muted);
      margin-bottom: 28px;
    }

    .breadcrumb a {
      color: var(--blue);
      text-decoration: none;
      font-weight: 600;
    }

    .breadcrumb a:hover {
      text-decoration: underline;
    }

    .breadcrumb i {
      font-size: 0.65rem;
    }

    .step-list {
      list-style: none;
      padding: 0;
      margin-bottom: 13px;
    }

    .step-list li {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      padding: 10px 0;
      border-bottom: 1px solid var(--border);
      color: var(--sub);
      font-size: 0.9rem;
      line-height: 1.7;
    }

    .step-list li:last-child {
      border-bottom: none;
    }

    .step-num {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background: var(--sky);
      color: var(--blue);
      font-size: 0.75rem;
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 1px;
    }
  </style>
</head>

<body class="page-enter">
  <nav>
    <a class="nav-logo" href="{{ route('index') }}">
      <div class="nav-logo-img"><img src="{{ asset('frontend/logo.png') }}" alt="Legacy Carry"></div>
      <div class="nav-logo-text">Legacy Carry<span>Smart Society Platform</span></div>
    </a>
    <ul class="nav-links">

      <li>
        <a href="{{ route('index') }}" class="active">
          <i class="fa-solid fa-house"></i> Home
        </a>
      </li>
      <li>
        <a href="{{ route('about') }}">
          <i class="fa-solid fa-circle-info"></i> About Us
        </a>
      </li>
      <li>
        <a href="{{ route('contact') }}">
          <i class="fa-solid fa-envelope"></i> Contact
        </a>
      </li>
      <li>
        <a href="{{ route('privacy') }}">
          <i class="fa-solid fa-shield-halved"></i> Privacy
        </a>
      </li>
      <li>
        <a href="{{ route('refund') }}" class="active">
          <i class="fa-solid fa-rotate-left"></i> Refund Policy
        </a>
      </li>
      <li>
        <a href="{{ route('terms') }}">
          <i class="fa-solid fa-file-contract"></i> Terms & Conditions
        </a>
      </li>
      <li><button class="nav-admin" style="color: #ffffff !important; background-color: #0B1B3E !important;">
          <a href="{{ route('login') }}" style="color: #ffffff !important; background-color: #0B1B3E !important;">
            <i class="fa-solid fa-lock"></i> Admin Login
          </a>
        </button></li>
    </ul>
    <button class="ham" onclick="toggleMob()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <div class="page-hero">
    <div class="page-hero-inner">
      <div class="breadcrumb">
        <a href="{{ route('index') }}"><i class="fa-solid fa-house"></i> Home</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>Refund Policy</span>
      </div>
      <div class="page-hero-tag"><i class="fa-solid fa-rotate-left"></i> Legal</div>
      <h1
        style="font-family:var(--fd);font-size:clamp(2.2rem,5vw,3.5rem);font-weight:800;letter-spacing:-0.03em;color:var(--navy);margin-bottom:12px">
        Return &amp; Refund <em style="font-style:italic;color:var(--blue)">Policy</em></h1>
      <p style="color:var(--sub);font-size:0.875rem">Last updated: December 2024 &nbsp;·&nbsp; Applicable to all Legacy
        Carry transactions</p>
    </div>
  </div>

  <div class="pol-wrap" style="padding-top:56px">

    <div class="pol-toc">
      <h4><i class="fa-solid fa-list" style="margin-right:6px"></i>Table of Contents</h4>
      <ol>
        <li><a href="#rp1">Scope of This Policy</a></li>
        <li><a href="#rp2">Subscription Plans &amp; Fees</a></li>
        <li><a href="#rp3">Refund Eligibility</a></li>
        <li><a href="#rp4">Non-Refundable Items</a></li>
        <li><a href="#rp5">How to Request a Refund</a></li>
        <li><a href="#rp6">Refund Processing &amp; Timeline</a></li>
        <li><a href="#rp7">Service Disputes</a></li>
        <li><a href="#rp8">Cancellation Policy</a></li>
        <li><a href="#rp9">Contact Us</a></li>
      </ol>
    </div>

    <div class="pol-note">
      <p><i class="fa-solid fa-circle-info" style="margin-right:6px;color:var(--blue)"></i>
        <strong>Summary:</strong> We want you satisfied with Legacy Carry. If you experience a technical issue or are
        charged in error, we will investigate and process eligible refunds within 7–10 business days.
      </p>
    </div>

    <div class="pol-sec" id="rp1">
      <h2><i class="fa-solid fa-file-contract"></i> 1. Scope of This Policy</h2>
      <p>This Return and Refund Policy applies to all payments made through Legacy Carry, including subscription fees,
        in-app purchases, service booking fees, and any other chargeable features. This policy is governed by Indian
        law, including the Consumer Protection Act, 2019.</p>
    </div>

    <div class="pol-sec" id="rp2">
      <h2><i class="fa-solid fa-tags"></i> 2. Subscription Plans &amp; Fees</h2>
      <ul>
        <li><strong>Society Management Plans:</strong> Monthly or annual subscriptions for residential societies and
          commercial complexes</li>
        <li><strong>Service Booking Fees:</strong> Fees for connecting users with on-demand service professionals</li>
        <li><strong>Premium Features:</strong> Optional add-ons for advanced reporting, additional user seats, or
          enhanced integrations</li>
      </ul>
      <p>All prices are inclusive of applicable GST unless otherwise stated.</p>
    </div>

    <div class="pol-sec" id="rp3">
      <h2><i class="fa-solid fa-circle-check"></i> 3. Refund Eligibility</h2>
      <p>You may be eligible for a refund under these circumstances:</p>
      <ul>
        <li><strong>Duplicate Charge:</strong> Charged more than once for the same subscription or service due to a
          technical error</li>
        <li><strong>Service Non-Delivery:</strong> A confirmed service was not attended and no resolution was provided
        </li>
        <li><strong>Technical Failure:</strong> A critical paid feature is non-functional and cannot be resolved</li>
        <li><strong>Unauthorised Transaction:</strong> A transaction made without your authorisation, subject to
          investigation</li>
        <li><strong>Cooling-Off Period:</strong> New subscriptions cancelled within 48 hours of first payment, provided
          no services were used</li>
      </ul>
    </div>

    <div class="pol-sec" id="rp4">
      <h2><i class="fa-solid fa-ban"></i> 4. Non-Refundable Items</h2>
      <ul>
        <li>Subscription fees for periods already consumed or partially used</li>
        <li>Service booking fees where the service was successfully delivered</li>
        <li>Annual plan fees after the 48-hour cooling-off period</li>
        <li>Promotional or discounted pricing</li>
        <li>One-time setup or onboarding fees</li>
      </ul>
      <div class="pol-hl">
        <p><i class="fa-solid fa-triangle-exclamation" style="margin-right:6px;color:var(--accent)"></i>
          <strong>Note:</strong> If dissatisfied with a service, contact our support — we may provide credits or partial
          refunds at our discretion.
        </p>
      </div>
    </div>

    <div class="pol-sec" id="rp5">
      <h2><i class="fa-solid fa-hand-holding-dollar"></i> 5. How to Request a Refund</h2>
      <ul class="step-list">
        <li><span class="step-num">1</span>
          <div><strong>Contact us</strong> at <strong>info@legacycarry.com</strong> or call <strong>+91 92175
              35473</strong></div>
        </li>
        <li><span class="step-num">2</span>
          <div>Provide your registered contact, transaction ID, date, amount, and reason for the request</div>
        </li>
        <li><span class="step-num">3</span>
          <div>Our team will <strong>acknowledge within 2 business days</strong> and begin an investigation</div>
        </li>
        <li><span class="step-num">4</span>
          <div><strong>Resolution provided</strong> within 7 business days — approval or explanation</div>
        </li>
        <li><span class="step-num">5</span>
          <div>Approved refunds processed to original payment method within <strong>5–7 additional business
              days</strong></div>
        </li>
      </ul>
    </div>

    <div class="pol-sec" id="rp6">
      <h2><i class="fa-solid fa-clock-rotate-left"></i> 6. Refund Processing &amp; Timeline</h2>
      <ul>
        <li><strong>UPI / Net Banking:</strong> 3–5 business days</li>
        <li><strong>Credit / Debit Card:</strong> 5–10 business days (depending on your bank)</li>
        <li><strong>Wallets (Paytm, PhonePe, etc.):</strong> 1–3 business days</li>
      </ul>
      <p>We are not responsible for delays caused by your bank or payment processor.</p>
    </div>

    <div class="pol-sec" id="rp7">
      <h2><i class="fa-solid fa-scale-balanced"></i> 7. Service Disputes</h2>
      <p>Report disputes within 24 hours of service completion. Use the <strong>"Report Issue"</strong> feature in the
        app or contact support directly. Our team will investigate and may offer a re-service, partial refund, or
        account credit depending on the findings.</p>
    </div>

    <div class="pol-sec" id="rp8">
      <h2><i class="fa-solid fa-calendar-xmark"></i> 8. Cancellation Policy</h2>
      <p><strong>Service Bookings:</strong></p>
      <ul>
        <li>Cancellations more than 2 hours before scheduled service: <strong>Full refund</strong></li>
        <li>Cancellations within 2 hours of scheduled service: 50% of booking fee may be retained</li>
        <li>No-show (user unavailable when provider arrives): Booking fee is non-refundable</li>
      </ul>
      <p><strong>Subscriptions:</strong></p>
      <ul>
        <li>Cancel anytime; access continues until end of current billing period</li>
        <li>Annual subscriptions cancelled after 48 hours are not eligible for refund except under exceptional
          circumstances</li>
      </ul>
    </div>

    <div class="pol-sec" id="rp9">
      <h2><i class="fa-solid fa-headset"></i> 9. Contact Us</h2>
      <p>For refund requests, disputes, or questions, please reach our support team:</p>
      <div class="info-card" style="margin-top:18px">
        <h3>Legacy Carry</h3>
        <p class="sub" style="color:var(--sub);font-size:0.875rem;margin-bottom:20px">Support &amp; Billing Team
        </p>
        <div class="ci">
          <div class="ci-i"><i class="fa-solid fa-location-dot"></i></div>
          <div class="ci-d">
            <label>Address</label>
            <span style="font-size:0.82rem;line-height:1.65">Property No. 70 GF, Kh No 1513/1, Baba Mohalla, Aya Nagar
              Ext. Phase 3, New Delhi – 110047</span>
          </div>
        </div>
        <div class="ci">
          <div class="ci-i"><i class="fa-solid fa-phone"></i></div>
          <div class="ci-d"><label>Phone</label><a href="tel:+919217535473">+91 92175 35473</a></div>
        </div>
        <div class="ci">
          <div class="ci-i"><i class="fa-solid fa-envelope"></i></div>
          <div class="ci-d"><label>Email</label><a href="mailto:info@legacycarry.com">info@legacycarry.com</a></div>
        </div>
        <div class="ci">
          <div class="ci-i"><i class="fa-solid fa-clock"></i></div>
          <div class="ci-d"><label>Support Hours</label><span>Mon–Sat, 9:00 AM – 6:00 PM IST</span></div>
        </div>
      </div>
    </div>

  </div>

  <footer>
    <div class="foot-grid">
      <div>
        <div class="foot-logo-wrap">
          <div class="foot-logo-img"><img src="{{ asset('frontend/logo.png') }}" alt="Legacy Carry"></div>
          <div class="foot-logo-text">Legacy Carry<span>Smart Society Platform</span></div>
        </div>
        <p class="foot-desc">Book trusted local services &amp; manage society security in one app. Serving residential
          societies, corporate facilities, and communities across India.</p>
        <div class="foot-social">
          <a href="#" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" title="Twitter/X"><i class="fa-brands fa-x-twitter"></i></a>
          <a href="#" title="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
      </div>
      <div class="foot-col">
        <h4>Navigate</h4>
        <ul>
          <li><a href="{{ route('index') }}"><i class="fa-solid fa-house"></i> Home</a></li>
          <li><a href="{{ route('about') }}"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
          <li><a href="{{ route('contact') }}"><i class="fa-solid fa-envelope"></i> Contact</a></li>
        </ul>
      </div>
      <div class="foot-col">
        <h4>Legal</h4>
        <ul>
          <li><a href="{{ route('privacy') }}"><i class="fa-solid fa-shield-halved"></i> Privacy Policy</a></li>
          <li><a href="{{ route('refund') }}"><i class="fa-solid fa-rotate-left"></i> Refund Policy</a></li>
          <li><a href="{{ route('terms') }}"><i class="fa-solid fa-file-contract"></i> Terms & Conditions</a></li>
          </li>
        </ul>
      </div>
      <div class="foot-col">
        <h4>Contact Us</h4>
        <div class="foot-address">
          <strong><i class="fa-solid fa-location-dot"></i> Address</strong>
          Property No. 70 GF, Kh No 1513/1,<br>Baba Mohalla, Aya Nagar Ext. Phase 3,<br>New Delhi – 110047
        </div>
        <div class="foot-address" style="margin-top:14px">
          <strong><i class="fa-solid fa-phone"></i> Phone</strong>
          <a href="tel:+919217535473" style="color:rgba(255,255,255,0.55);text-decoration:none">+91 92175 35473</a>
        </div>
        <div class="foot-address" style="margin-top:10px">
          <strong><i class="fa-solid fa-envelope"></i> Email</strong>
          <a href="mailto:info@legacycarry.com"
            style="color:rgba(255,255,255,0.55);text-decoration:none;font-size:0.82rem">info@legacycarry.com</a>
        </div>
      </div>
    </div>
    <div class="foot-bottom">
      <p>© 2026 Legacy Carry. All rights reserved. New Delhi, India.</p>
      <div class="foot-links">
        <a href="{{ route('privacy') }}">Privacy</a>
        <a href="{{ route('refund') }}">Refund</a>
        <a href="{{ route('contact') }}">Contact</a>
      </div>
    </div>
  </footer>
</body>

</html>
