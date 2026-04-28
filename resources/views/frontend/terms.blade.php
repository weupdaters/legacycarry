<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Terms & Conditions – Legacy Carry</title>

  <link rel="icon" href="{{ asset('frontend/logo.png') }}" />
  <link rel="stylesheet" href="{{ asset('frontend/style.css') }}" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    .page-hero {
      background: linear-gradient(160deg, #fff 0%, #eef1f8 60%, #dce6fb 100%);
      padding: 120px 5vw 56px;
      border-bottom: 1px solid #ddd;
    }

    .pol-wrap {
      max-width: 950px;
      margin: auto;
      padding: 50px 20px;
    }

    .pol-sec {
      margin-bottom: 40px;
    }

    .pol-sec h2 {
      color: #0B1B3E;
      margin-bottom: 10px;
    }

    .pol-sec p,
    .pol-sec li {
      font-size: 0.9rem;
      line-height: 1.7;
      color: #555;
    }

    .tag-box {
      background: #eef3ff;
      padding: 6px 14px;
      display: inline-block;
      border-radius: 20px;
      font-size: 0.75rem;
      margin-bottom: 10px;
      font-weight: 600;
      color: #1a56db;
    }
  </style>
</head>

<body>

  <!-- NAV -->
  <nav>
    <a href="{{ route('index') }}">
      <img src="{{ asset('frontend/logo.png') }}" height="40">
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
    <h1 style="text-align:center;font-size:2.8rem;font-weight:800;">
      Terms & Conditions
    </h1>
    <p style="text-align:center;color:#666;">
      Effective: 01 April 2026 · Legacy Carry Pvt. Ltd.
    </p>
  </div>

  <div class="pol-wrap">

    <!-- RESIDENT TERMS -->
    <div class="pol-sec">
      <div class="tag-box">FOR RESIDENTS</div>
      <h2>1. Introduction</h2>
      <p>Legacy Carry is a digital society management platform offering security, payments, visitor management, and
        service booking.</p>
      <p>By using the app, you agree to these Terms.</p>
    </div>

    <div class="pol-sec">
      <h2>2. Eligibility</h2>
      <ul>
        <li>Must be 18+</li>
        <li>Provide accurate details</li>
        <li>Must comply with Indian laws</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>3. Subscription</h2>
      <ul>
        <li>1 month free trial</li>
        <li>₹10/month after trial</li>
        <li>GST applicable</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>4. Payments</h2>
      <ul>
        <li>UPI / Cards / Net Banking</li>
        <li>Secure payment gateways</li>
        <li>No storage of sensitive data</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>5. User Responsibility</h2>
      <ul>
        <li>Keep account secure</li>
        <li>No misuse</li>
        <li>No illegal content</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>6. Liability</h2>
      <p>Company is not responsible for service disputes, losses, or damages.</p>
    </div>

    <!-- SERVICE PROVIDERS -->
    <div class="pol-sec">
      <div class="tag-box">FOR SERVICE PROVIDERS</div>
      <h2>1. Registration</h2>
      <ul>
        <li>Must be 18+</li>
        <li>Provide valid documents</li>
        <li>Account activation within 24 hours</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>2. Platform Fees</h2>
      <ul>
        <li>10% platform fee per job</li>
        <li>GST applicable</li>
        <li>2 cash jobs allowed</li>
        <li>5% penalty for incomplete job</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>3. Job Rules</h2>
      <ul>
        <li>Must complete accepted jobs</li>
        <li>No fake bookings</li>
        <li>No off-platform deals</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>4. Conduct</h2>
      <ul>
        <li>No fraud or illegal activity</li>
        <li>Respect users</li>
        <li>Maintain service quality</li>
      </ul>
    </div>

    <div class="pol-sec">
      <h2>5. Termination</h2>
      <p>Accounts may be suspended for violations or fraud.</p>
    </div>

    <div class="pol-sec">
      <h2>6. Governing Law</h2>
      <p>All disputes fall under Indian law and New Delhi jurisdiction.</p>
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
