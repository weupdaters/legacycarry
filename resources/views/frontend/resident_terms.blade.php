<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Terms & Conditions – Legacy Carry</title>
  <meta name="description" content="Terms and Conditions for Legacy Carry platform." />

  <link rel="icon" type="image/png" href="{{ asset('frontend/logo.png') }}" />
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

    .pol-wrap {
      max-width: 900px;
      margin: auto;
      padding: 40px 20px;
    }

    .pol-sec {
      margin-bottom: 30px;
    }

    .pol-sec h2 {
      margin-bottom: 10px;
      color: var(--navy);
    }

    .pol-sec p,
    .pol-sec li {
      font-size: 0.9rem;
      color: var(--sub);
      line-height: 1.7;
    }
  </style>
</head>

<body>

  <!-- NAV -->
  <nav>
    <a class="nav-logo" href="{{ route('index') }}">
      <div class="nav-logo-img">
        <img src="{{ asset('frontend/logo.png') }}" alt="Legacy Carry">
      </div>
      <div class="nav-logo-text">
        Legacy Carry<span>Smart Society Platform</span>
      </div>
    </a>

    <ul class="nav-links">
      <li>
        <a href="{{ route('index') }}">
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
        <a href="{{ route('refund') }}">
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
  </nav>

  <!-- HERO -->
  <div class="page-hero">
    <div class="page-hero-inner">
      <h1 style="font-size:2.8rem;font-weight:800;">Terms & Conditions</h1>
      <p style="color:var(--sub);font-size:0.9rem;">
        Last Updated: April 2026
      </p>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="pol-wrap">

    <div class="pol-sec">
      <h2>1. Introduction</h2>
      <p>Welcome to Legacy Carry Pvt. Ltd. ("Company"). By using our platform, you agree to these Terms & Conditions.
      </p>
      <p>We provide society management services including security, billing, job posting, visitor management, and
        digital payments.</p>
    </div>

    <div class="pol-sec">
      <h2>2. Eligibility</h2>
      <ul>
        <li>Minimum age: 18 years</li>
        <li>Provide accurate information</li>
        <li>Comply with Indian laws</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>3. Subscription & Pricing</h2>
      <ul>
        <li>1 month free trial</li>
        <li>After trial: ₹10 per user</li>
        <li>Pricing may vary</li>
      </ul>
      <p>Company may modify pricing or services anytime.</p>
    </div>

    <div class="pol-sec">
      <h2>4. Account Responsibility</h2>
      <ul>
        <li>Keep login details secure</li>
        <li>Do not share account</li>
        <li>Report unauthorized access</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>5. Payments</h2>
      <ul>
        <li>UPI, Cards, Net Banking supported</li>
        <li>Secure payment gateways used</li>
        <li>No storage of sensitive financial data</li>
        <li>Refunds processed in 5–10 days</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>6. Cashless Policy</h2>
      <p>All transactions must be digital. Offline payments are not our responsibility.</p>
    </div>

    <div class="pol-sec">
      <h2>7. User Conduct</h2>
      <ul>
        <li>No misuse of platform</li>
        <li>No illegal activities</li>
        <li>No false job postings</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>8. Job Disclaimer</h2>
      <p>We only provide a platform for job listings. We are not responsible for hiring decisions or disputes.</p>
    </div>

    <div class="pol-sec">
      <h2>9. Data Usage</h2>
      <p>We collect basic user data for service delivery and follow Indian data protection laws.</p>
      <p>We do not sell user data.</p>
    </div>

    <div class="pol-sec">
      <h2>10. Liability</h2>
      <p>Company is not liable for losses due to user error, payment failures, or third-party services.</p>
    </div>

    <div class="pol-sec">
      <h2>11. Termination</h2>
      <p>We may suspend or terminate accounts for violations or fraud.</p>
    </div>

    <div class="pol-sec">
      <h2>12. Changes</h2>
      <p>We may update these Terms anytime. Continued use means acceptance.</p>
    </div>

    <div class="pol-sec">
      <h2>13. Governing Law</h2>
      <p>These Terms are governed by Indian law.</p>
    </div>

  </div>

  <!-- FOOTER -->
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
      <p>© 2024 Legacy Carry. All rights reserved. New Delhi, India.</p>
      <div class="foot-links">
        <a href="{{ route('privacy') }}">Privacy</a>
        <a href="{{ route('refund') }}">Refund</a>
        <a href="{{ route('contact') }}">Contact</a>
      </div>
    </div>
  </footer>

</body>

</html>
