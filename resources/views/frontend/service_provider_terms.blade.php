<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Terms & Conditions – Legacy Carry</title>
  <meta name="description" content="Terms and Conditions for Legacy Carry platform." />

  <link rel="icon" href="{{ asset('frontend/logo.png') }}" />
  <link rel="stylesheet" href="{{ asset('frontend/style.css') }}" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    .page-hero {
      background: linear-gradient(160deg, #fff 0%, #eef1f8 60%, #dce6fb 100%);
      padding: 120px 5vw 56px;
      border-bottom: 1px solid #ddd;
    }

    .page-hero-inner {
      max-width: 800px;
      margin: auto;
    }

    .pol-wrap {
      max-width: 900px;
      margin: auto;
      padding: 40px 20px;
    }

    .pol-sec {
      margin-bottom: 35px;
    }

    .pol-sec h2 {
      margin-bottom: 10px;
      color: #0B1B3E;
    }

    .pol-sec p,
    .pol-sec li {
      font-size: 0.9rem;
      line-height: 1.7;
      color: #555;
    }
  </style>
</head>

<body>

  <!-- NAV -->
  <nav>
    <a class="nav-logo" href="{{ route('index') }}">
      <img src="{{ asset('frontend/logo.png') }}" height="40">
      <span>Legacy Carry</span>
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
        <a href="{{ route('resident-terms') }}">
          <i class="fa-solid fa-file-contract"></i> Resident Terms
        </a>
      </li>
      <li>
        <a href="{{ route('service-provider-terms') }}">
          <i class="fa-solid fa-file-contract" class="active"></i> Service Provider Terms
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
      <h1 style="font-size:2.8rem;font-weight:800;">
        Terms & Conditions
      </h1>
      <p style="color:#666;font-size:0.9rem;">
        For Users & Service Providers · Last Updated: April 2026
      </p>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="pol-wrap">

    <!-- GENERAL TERMS -->
    <div class="pol-sec">
      <h2>1. General Terms</h2>
      <p>By using Legacy Carry Pvt. Ltd., you agree to follow all platform rules and Indian laws.</p>

      <ul>
        <li>Minimum age: 18 years</li>
        <li>Provide accurate details</li>
        <li>Keep account secure</li>
        <li>No illegal activities allowed</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>2. Subscription & Payments</h2>
      <ul>
        <li>1 month free trial</li>
        <li>₹10 per user after trial</li>
        <li>UPI / Cards / Net Banking supported</li>
        <li>Refunds processed in 5–10 days</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>3. Platform Rules</h2>
      <ul>
        <li>Cashless system preferred</li>
        <li>Company not liable for disputes</li>
        <li>We may update pricing anytime</li>
      </ul>
    </div>

    <!-- SERVICE PROVIDER TERMS -->
    <div class="pol-sec">
      <h2>Service Provider Terms & Conditions</h2>

      <ul>
        <li>Must be 18+ and legally eligible to work in India</li>

        <li>Provide accurate details and documents during registration</li>

        <li>Account activation within 24 hours after verification</li>

        <li>False information may lead to legal action</li>

        <li>All data must remain correct and updated</li>

        <li><strong>10% platform fee</strong> applied on each job</li>

        <li>GST applicable as per law</li>

        <li>Review job details before accepting</li>

        <li>Must complete accepted jobs properly</li>

        <li><strong>5% penalty</strong> if job not completed</li>

        <li>Penalty deducted from future earnings</li>

        <li>No misuse of platform allowed</li>

        <li>Only 2 cash jobs allowed</li>

        <li>After limit, fees must be paid online</li>

        <li>Non-payment may lead to account suspension</li>

        <li>All payments follow RBI guidelines</li>

        <li>Platform is intermediary only</li>

        <li>Provider responsible for service quality</li>

        <li>Company not liable for damages</li>

        <li>No illegal or harmful activities</li>

        <li>Fraud may result in account termination</li>

        <li>Company can suspend account anytime</li>

        <li>Pending dues must be cleared before leaving</li>

        <li>Terms may be updated anytime</li>

        <li>Use = acceptance of updated terms</li>

        <li>Disputes under Indian jurisdiction</li>
      </ul>

      <p><strong>By registering, you agree to all terms above.</strong></p>
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
