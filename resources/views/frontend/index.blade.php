<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Legacy Carry – Services, Security & Smart Society Management</title>
  <meta name="description"
    content="Legacy Carry connects residents, security guards, admins, and service providers in one digital ecosystem — for safer, more efficient communities." />
  <link rel="icon" type="image/png" href="logo.png" />
  <link rel="stylesheet" href="{{ asset('frontend/style.css') }}">
  <script src="{{ asset('frontend/main.js') }}"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* ── HERO ── */
    .hero {
      min-height: 100vh;
      padding: 100px 5vw 80px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      position: relative;
      overflow: hidden;
      background: linear-gradient(165deg, #fff 0%, #eef1f8 50%, #dce6fb 100%);
    }

    .hero::before {
      content: '';
      position: absolute;
      width: 700px;
      height: 700px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(26, 86, 219, 0.07) 0%, transparent 70%);
      top: -150px;
      right: -150px;
      pointer-events: none;
    }

    .hero::after {
      content: '';
      position: absolute;
      width: 500px;
      height: 500px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(201, 168, 76, 0.06) 0%, transparent 70%);
      bottom: -100px;
      left: -80px;
      pointer-events: none;
    }

    .hero-logo-wrap {
      position: relative;
      z-index: 1;
      width: 110px;
      height: 110px;
      border-radius: 22px;
      background: #0a0a0a;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 32px;
      box-shadow: 0 12px 48px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(201, 168, 76, 0.3);
      overflow: hidden;
    }

    .hero-logo-wrap img {
      width: 90px;
      height: 90px;
      object-fit: contain;
    }

    .pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--sky);
      border: 1.5px solid rgba(26, 86, 219, 0.2);
      color: var(--blue);
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      padding: 6px 18px;
      border-radius: 100px;
      margin-bottom: 32px;
      position: relative;
      z-index: 1;
    }

    .pill i {
      font-size: 0.6rem;
      color: var(--green);
      animation: blink 2s infinite;
    }

    @keyframes blink {

      0%,
      100% {
        opacity: 1
      }

      50% {
        opacity: 0.15
      }
    }

    .hero h1 {
      font-family: var(--fd);
      font-size: clamp(3rem, 7.5vw, 6rem);
      font-weight: 800;
      line-height: 1.0;
      letter-spacing: -0.03em;
      color: var(--navy);
      margin-bottom: 6px;
      position: relative;
      z-index: 1;
    }

    .hero h1 em {
      font-style: italic;
      font-weight: 400;
      color: var(--blue);
    }

    .h1-line2 {
      font-family: var(--fd);
      font-style: italic;
      font-size: clamp(2.2rem, 5vw, 4.2rem);
      font-weight: 700;
      color: var(--gold);
      letter-spacing: -0.02em;
      line-height: 1.05;
      margin-bottom: 28px;
      position: relative;
      z-index: 1;
    }

    .hero-desc {
      font-size: clamp(0.95rem, 1.8vw, 1.12rem);
      color: var(--sub);
      max-width: 540px;
      line-height: 1.78;
      margin-bottom: 44px;
      position: relative;
      z-index: 1;
    }

    .hero-btns {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
      justify-content: center;
      margin-bottom: 60px;
      position: relative;
      z-index: 1;
    }

    .stats-wrap {
      position: relative;
      z-index: 1;
      width: 100%;
      display: flex;
      justify-content: center;
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
    <button class="ham" onclick="toggleMob()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </nav>


  <!-- HERO -->
  <section class="hero">
    <div class="hero-logo-wrap">
      <img src="{{ asset('frontend/logo.png') }}" alt="Legacy Carry" />
    </div>
    <div class="pill"><i class="fa-solid fa-circle-dot"></i> Live on Mobile &nbsp;·&nbsp; Pan India</div>
    <h1>One App for <em>Services,</em></h1>
    <h1>Security &amp; Societies</h1>
    <div class="h1-line2">Smarter Communities Start Here</div>
    <p class="hero-desc">Legacy Carry connects residents, security guards, admins, and service providers in one digital
      ecosystem — for safer, more efficient communities across India.</p>
    <div class="hero-btns">
      <a href="{{ route('contact') }}" class="btn-solid"><i class="fa-solid fa-rocket"></i> Get Started</a>
      <a href="{{ route('about') }}" class="btn-outline"><i class="fa-solid fa-circle-info"></i> Learn More</a>
    </div>
    <div class="stats-wrap">
      <div class="stats-strip">
        <div class="stat">
          <div class="stat-n"><span>10</span>+</div>
          <div class="stat-l">Service Types</div>
        </div>
        <div class="stat">
          <div class="stat-n"><span>5</span></div>
          <div class="stat-l">User Roles</div>
        </div>
        <div class="stat">
          <div class="stat-n">ERP</div>
          <div class="stat-l">Powered</div>
        </div>
        <div class="stat">
          <div class="stat-n">24/7</div>
          <div class="stat-l">Monitoring</div>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <div class="wrap">
    <div class="sec-label"><i class="fa-solid fa-layer-group"></i> Platform Features</div>
    <h2 class="sec-h">Everything Your Society <em>Needs</em></h2>
    <p class="sec-sub">One platform uniting service booking, vehicle tracking, security, and community management for
      modern facilities.</p>
    <div class="feat-grid">
      <div class="fc">
        <div class="fc-ico"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <div class="fc-title">On-Demand Labor & Services</div>
        <div class="fc-desc">Book trusted nearby professionals instantly — electricians, plumbers, technicians, repair
          workers, cleaners. Verified workers, fast response.</div>
        <div class="fc-chips"><span class="chip">Electricians</span><span class="chip">Plumbers</span><span
            class="chip">Cleaning</span></div>
      </div>
      <div class="fc">
        <div class="fc-ico"><i class="fa-solid fa-building"></i></div>
        <div class="fc-title">Smart Society Management</div>
        <div class="fc-desc">Digitally manage residential communities with tools for residents, security guards, and
          society admins — simplifying all daily operations.</div>
        <div class="fc-chips"><span class="chip">Residents</span><span class="chip">Guards</span><span
            class="chip">Admins</span></div>
      </div>
      <div class="fc">
        <div class="fc-ico"><i class="fa-solid fa-car"></i></div>
        <div class="fc-title">Vehicle Entry & Exit Tracking</div>
        <div class="fc-desc">Track all vehicles entering and exiting premises — resident, visitor, delivery,
          commercial,
          vendor — with secure digital logs and real-time records.</div>
        <div class="fc-chips"><span class="chip">Entry Logs</span><span class="chip">Delivery</span><span
            class="chip">Fleet</span></div>
      </div>
      <div class="fc">
        <div class="fc-ico"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="fc-title">Security Guard Management</div>
        <div class="fc-desc">Equip guards with digital tools for visitor verification, vehicle approval, entry logging,
          and security monitoring — all from mobile.</div>
        <div class="fc-chips"><span class="chip">Verification</span><span class="chip">Approvals</span><span
            class="chip">Monitoring</span></div>
      </div>
      <div class="fc">
        <div class="fc-ico"><i class="fa-solid fa-users"></i></div>
        <div class="fc-title">Resident Profile Management</div>
        <div class="fc-desc">Residents manage profiles, household members, registered vehicles, and visitor approvals
          in one secure digital space.</div>
        <div class="fc-chips"><span class="chip">Profiles</span><span class="chip">Vehicles</span><span
            class="chip">Visitors</span></div>
      </div>
      <div class="fc">
        <div class="fc-ico"><i class="fa-solid fa-bell"></i></div>
        <div class="fc-title">Real-Time Notifications</div>
        <div class="fc-desc">Instant alerts for visitor arrivals, vehicle entries, service bookings, security events,
          and announcements — stay informed always.</div>
        <div class="fc-chips"><span class="chip">Live Alerts</span><span class="chip">Updates</span><span
            class="chip">Security</span></div>
      </div>
      <div class="fc">
        <div class="fc-ico"><i class="fa-solid fa-chart-pie"></i></div>
        <div class="fc-title">ERP-Based Digital Management</div>
        <div class="fc-desc">Manage vehicle records, visitor logs, bookings, and society activities in a structured,
          future-ready ERP platform built to scale.</div>
        <div class="fc-chips"><span class="chip">ERP</span><span class="chip">Analytics</span><span
            class="chip">Records</span></div>
      </div>
      <div class="fc">
        <div class="fc-ico"><i class="fa-solid fa-link"></i></div>
        <div class="fc-title">Future Integration Ready</div>
        <div class="fc-desc">Designed to integrate with payment gateways, smart security devices, parking systems, IoT
          networks, and third-party platforms.</div>
        <div class="fc-chips"><span class="chip">IoT</span><span class="chip">Payments</span><span
            class="chip">APIs</span></div>
      </div>
    </div>
  </div>

  <!-- WHO USES -->
  <div class="wrap-sm" style="padding-top:0">
    <div class="sec-label"><i class="fa-solid fa-building-user"></i> Who Uses Legacy Carry</div>
    <h2 class="sec-h">Built for <em>Every</em> Facility</h2>
    <p class="sec-sub" style="margin-bottom:40px">From gated apartments to corporate campuses — Legacy Carry works
      wherever security and service management matter.</p>
    <div class="who-grid">
      <div class="who-c">
        <div class="who-e"><i class="fa-solid fa-city"></i></div>
        <div class="who-l">Residential Societies</div>
      </div>
      <div class="who-c">
        <div class="who-e"><i class="fa-solid fa-building-columns"></i></div>
        <div class="who-l">Apartment Communities</div>
      </div>
      <div class="who-c">
        <div class="who-e"><i class="fa-solid fa-briefcase"></i></div>
        <div class="who-l">Corporate Offices</div>
      </div>
      <div class="who-c">
        <div class="who-e"><i class="fa-solid fa-store"></i></div>
        <div class="who-l">Commercial Complexes</div>
      </div>
      <div class="who-c">
        <div class="who-e"><i class="fa-solid fa-user-shield"></i></div>
        <div class="who-l">Security Teams</div>
      </div>
      <div class="who-c">
        <div class="who-e"><i class="fa-solid fa-industry"></i></div>
        <div class="who-l">Industrial Facilities</div>
      </div>
      <div class="who-c">
        <div class="who-e"><i class="fa-solid fa-building-circle-check"></i></div>
        <div class="who-l">Mixed-Use Developments</div>
      </div>
      <div class="who-c">
        <div class="who-e"><i class="fa-solid fa-house-user"></i></div>
        <div class="who-l">Residents & Families</div>
      </div>
    </div>
  </div>

  <!-- WHY -->
  <div class="wrap-sm" style="padding-top:0">
    <div class="sec-label"><i class="fa-solid fa-star"></i> Why Choose Us</div>
    <h2 class="sec-h">The Smart Choice for <em>Modern</em> Communities</h2>
    <p class="sec-sub" style="margin-bottom:40px">Six reasons forward-thinking societies choose Legacy Carry.</p>
    <div class="why-grid">
      <div class="wc">
        <div class="wc-n">01</div>
        <div class="wc-title">Instant Worker Booking</div>
        <div class="wc-desc">Book trusted local professionals on demand with verified profiles and fast response.</div>
      </div>
      <div class="wc">
        <div class="wc-n">02</div>
        <div class="wc-title">Enhanced Security</div>
        <div class="wc-desc">Digitise visitor and vehicle management for better monitoring and accountability.</div>
      </div>
      <div class="wc">
        <div class="wc-n">03</div>
        <div class="wc-title">Centralised Admin</div>
        <div class="wc-desc">Manage residents, staff, vehicles, and services from one unified dashboard.</div>
      </div>
      <div class="wc">
        <div class="wc-n">04</div>
        <div class="wc-title">Future-Ready ERP</div>
        <div class="wc-desc">Scalable platform designed for asset management, facility ops, and integrations.</div>
      </div>
      <div class="wc">
        <div class="wc-n">05</div>
        <div class="wc-title">Real-Time Visibility</div>
        <div class="wc-desc">Live notifications and logs so nothing is missed — deliveries to security events.</div>
      </div>
      <div class="wc">
        <div class="wc-n">06</div>
        <div class="wc-title">Scalable Integrations</div>
        <div class="wc-desc">IoT, payments, smart devices, third-party systems — designed to connect.</div>
      </div>
    </div>
  </div>

  <!-- CTA -->
  <div class="wrap-sm" style="padding-top:0">
    <div class="cta-box">
      <h2>Ready to Modernise<br /><em>Your Society?</em></h2>
      <p>Join communities already using Legacy Carry to manage security, services, and daily operations digitally — all
        from one app.</p>
      <div class="cta-btns">
        <a href="{{ route('contact') }}" class="btn-white"><i class="fa-solid fa-paper-plane"></i> Contact Us</a>
        <a href="{{ route('about') }}" class="btn-ghost"><i class="fa-solid fa-book-open"></i> Our Story</a>
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
