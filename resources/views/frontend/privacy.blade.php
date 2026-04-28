<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Privacy Policy – Legacy Carry</title>
  <meta name="description"
    content="Privacy Policy for Legacy Carry — how we collect, use, and protect your personal data." />
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
        <a href="{{ route('privacy') }}" class="active">
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
    <button class="ham" onclick="toggleMob()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <div class="page-hero">
    <div class="page-hero-inner">
      <div class="breadcrumb">
        <a href="{{ route('index') }}"><i class="fa-solid fa-house"></i> Home</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>Privacy Policy</span>
      </div>
      <div class="page-hero-tag"><i class="fa-solid fa-shield-halved"></i> Legal</div>
      <h1
        style="font-family:var(--fd);font-size:clamp(2.2rem,5vw,3.5rem);font-weight:800;letter-spacing:-0.03em;color:var(--navy);margin-bottom:12px">
        Privacy <em style="font-style:italic;color:var(--blue)">Policy</em></h1>
      <p style="color:var(--sub);font-size:0.875rem">Last updated: December 2024 &nbsp;·&nbsp; Effective for all Legacy
        Carry users</p>
    </div>
  </div>

  <div class="pol-wrap" style="padding-top:56px">

    <div class="pol-toc">
      <h4><i class="fa-solid fa-list" style="margin-right:6px"></i>Table of Contents</h4>
      <ol>
        <li><a href="#pp1">Information We Collect</a></li>
        <li><a href="#pp2">How We Use Your Information</a></li>
        <li><a href="#pp3">Information Sharing &amp; Disclosure</a></li>
        <li><a href="#pp4">Data Storage &amp; Security</a></li>
        <li><a href="#pp5">Your Rights &amp; Choices</a></li>
        <li><a href="#pp6">Cookies &amp; Tracking</a></li>
        <li><a href="#pp7">Children's Privacy</a></li>
        <li><a href="#pp8">Changes to This Policy</a></li>
        <li><a href="#pp9">Contact Us</a></li>
      </ol>
    </div>

    <div class="pol-note">
      <p><i class="fa-solid fa-circle-info" style="margin-right:6px;color:var(--blue)"></i>
        <strong>Summary:</strong> Legacy Carry collects only data necessary to operate the platform. We do not sell your
        personal information to third parties. Your data is stored securely and you may request its deletion at any
        time.
      </p>
    </div>

    <div class="pol-sec" id="pp1">
      <h2><i class="fa-solid fa-database"></i> 1. Information We Collect</h2>
      <p>Legacy Carry is operated from New Delhi, India. We collect the following types of information:</p>
      <p><strong>Personal Information:</strong></p>
      <ul>
        <li>Name, phone number, and email address upon registration</li>
        <li>Residential address and flat/unit number for resident profiles</li>
        <li>Vehicle registration numbers added to your profile</li>
        <li>Identity documents submitted for verification (guards and service providers)</li>
        <li>Profile photos, where voluntarily uploaded</li>
      </ul>
      <p><strong>Usage &amp; Operational Data:</strong></p>
      <ul>
        <li>Visitor entry and exit logs associated with your property</li>
        <li>Vehicle entry and exit timestamps and records</li>
        <li>Service booking history and provider interactions</li>
        <li>Device information (type, OS, app version)</li>
        <li>IP address and approximate location (for service proximity)</li>
      </ul>
    </div>

    <div class="pol-sec" id="pp2">
      <h2><i class="fa-solid fa-gears"></i> 2. How We Use Your Information</h2>
      <ul>
        <li><strong>Account Management:</strong> To create and manage your profile and authenticate your identity</li>
        <li><strong>Service Delivery:</strong> To connect you with service providers and facilitate bookings</li>
        <li><strong>Security Operations:</strong> To maintain visitor logs, vehicle records, and guard operations</li>
        <li><strong>Notifications:</strong> To send real-time alerts about visitors, vehicles, and announcements</li>
        <li><strong>Platform Improvement:</strong> To analyse usage patterns and improve features</li>
        <li><strong>Legal Compliance:</strong> To comply with applicable Indian laws and governmental requests</li>
        <li><strong>Customer Support:</strong> To respond to queries, complaints, and support requests</li>
      </ul>
    </div>

    <div class="pol-sec" id="pp3">
      <h2><i class="fa-solid fa-share-nodes"></i> 3. Information Sharing &amp; Disclosure</h2>
      <p>We do not sell, trade, or rent your personal information. Data may be shared in limited circumstances:</p>
      <ul>
        <li><strong>Within Your Society:</strong> Resident names and vehicle details visible to your administrator and
          security personnel</li>
        <li><strong>Service Providers:</strong> Relevant contact details shared with assigned professionals to fulfil
          your request</li>
        <li><strong>Technology Partners:</strong> Trusted cloud infrastructure providers under strict confidentiality
          agreements</li>
        <li><strong>Legal Requirements:</strong> Where required by Indian law, court order, or governmental authority
        </li>
      </ul>
      <div class="pol-hl">
        <p><i class="fa-solid fa-triangle-exclamation" style="margin-right:6px;color:var(--accent)"></i>
          <strong>Important:</strong> We will never share your personal information with advertisers or data brokers
          without your explicit consent.
        </p>
      </div>
    </div>

    <div class="pol-sec" id="pp4">
      <h2><i class="fa-solid fa-server"></i> 4. Data Storage &amp; Security</h2>
      <p>Your data is stored on secure servers with industry-standard protections:</p>
      <ul>
        <li>Encryption of data in transit using TLS/SSL protocols</li>
        <li>Encryption of sensitive data at rest</li>
        <li>Role-based access controls limiting data access</li>
        <li>Regular security audits and vulnerability assessments</li>
      </ul>
      <p>Personal data will be erased within 30 days of account deletion, except where legally required.</p>
    </div>

    <div class="pol-sec" id="pp5">
      <h2><i class="fa-solid fa-user-check"></i> 5. Your Rights &amp; Choices</h2>
      <ul>
        <li><strong>Right to Access:</strong> Request a copy of personal data we hold about you</li>
        <li><strong>Right to Correction:</strong> Request correction of inaccurate data</li>
        <li><strong>Right to Deletion:</strong> Request deletion of your personal data</li>
        <li><strong>Right to Withdraw Consent:</strong> Withdraw consent for specific data processing</li>
      </ul>
      <p>To exercise any right, contact us at <strong>info@legacycarry.com</strong>. We will respond within 30 days.</p>
    </div>

    <div class="pol-sec" id="pp6">
      <h2><i class="fa-solid fa-cookie-bite"></i> 6. Cookies &amp; Tracking</h2>
      <p>The Legacy Carry mobile app uses minimal local storage for session and preferences. We do not use third-party
        advertising cookies or tracking pixels.</p>
    </div>

    <div class="pol-sec" id="pp7">
      <h2><i class="fa-solid fa-child"></i> 7. Children's Privacy</h2>
      <p>Legacy Carry is not intended for users under 18. We do not knowingly collect personal information from minors.
        Contact us immediately if you believe a minor has provided personal information.</p>
    </div>

    <div class="pol-sec" id="pp8">
      <h2><i class="fa-solid fa-arrows-rotate"></i> 8. Changes to This Policy</h2>
      <p>We may update this Privacy Policy from time to time. We will notify users of significant changes through in-app
        notifications or email. Continued use after changes are posted constitutes your acceptance.</p>
    </div>

    <div class="pol-sec" id="pp9">
      <h2><i class="fa-solid fa-headset"></i> 9. Contact Us</h2>
      <p>For questions, concerns, or data requests, please contact:</p>
      <div class="info-card" style="margin-top:18px">
        <h3>Legacy Carry</h3>
        <p class="sub" style="color:var(--sub);font-size:0.875rem;margin-bottom:20px">Privacy &amp; Data Team</p>
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
