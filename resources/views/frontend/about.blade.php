<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us – Legacy Carry</title>
  <meta name="description"
    content="Learn about Legacy Carry — built for the future of Indian communities. Our mission, vision, journey, and team." />
  <link rel="icon" type="image/png" href="logo.png" />
  <link rel="stylesheet" href="{{ asset('frontend/style.css') }}">
  <script src="{{ asset('frontend/main.js') }}"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .page-hero {
      background: linear-gradient(160deg, #fff 0%, #eef1f8 60%, #dce6fb 100%);
      padding: 120px 5vw 64px;
      border-bottom: 1.5px solid var(--border);
    }

    .page-hero-inner {
      max-width: 1180px;
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

    .page-hero h1 {
      font-family: var(--fd);
      font-size: clamp(2.4rem, 5vw, 4rem);
      font-weight: 800;
      letter-spacing: -0.03em;
      color: var(--navy);
      line-height: 1.05;
      margin-bottom: 18px;
    }

    .page-hero h1 em {
      font-style: italic;
      color: var(--blue);
    }

    .page-hero p {
      color: var(--sub);
      font-size: 1.05rem;
      line-height: 1.75;
      max-width: 560px;
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
        <a href="{{ route('about') }}" class="active">
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
      <li>
        <a href="{{ route('service-provider-terms') }}">
          <i class="fa-solid fa-file-contract"></i> Service Provider Terms
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

  <!-- PAGE HERO -->
  <div class="page-hero">
    <div class="page-hero-inner">
      <div class="breadcrumb">
        <a href="{{ route('index') }}"><i class="fa-solid fa-house"></i> Home</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>About Us</span>
      </div>
      <div class="page-hero-tag"><i class="fa-solid fa-circle-info"></i> Our Story</div>
      <h1>Built for the <em>Future</em><br />of Communities</h1>
      <p>Legacy Carry was born from a simple but powerful idea — that residential and commercial communities deserve a
        smarter, safer, more connected way to manage daily operations.</p>
    </div>
  </div>

  <!-- INTRO SPLIT -->
  <div class="about-wrap">
    <div class="split">
      <div>
        <div class="sec-label" style="margin-bottom:18px"><i class="fa-solid fa-lightbulb"></i> Who We Are</div>
        <h2 class="sec-h" style="font-size:clamp(1.8rem,3.5vw,2.8rem)">We Replaced <em>Paper Logs</em><br />With Smart
          Tech</h2>
        <p style="color:var(--sub);font-size:1rem;line-height:1.8;margin-bottom:32px">We observed residential societies
          struggling with paper-based visitor logs, unreliable service workers, and zero vehicle accountability. We
          built Legacy Carry to fix it permanently — one unified digital platform for the entire community ecosystem.
        </p>
        <a href="{{ route('contact') }}" class="btn-solid"><i class="fa-solid fa-handshake"></i> Work With Us</a>
      </div>
      <div class="about-card">
        <div class="about-card-logo"><img src="{{ asset('frontend/logo.png') }}" alt="Legacy Carry" /></div>
        <h3>Legacy Carry</h3>
        <p>Building smart infrastructure solutions for modern Indian communities — secure, scalable, and future-ready.
          Based in New Delhi.</p>
      </div>
    </div>

    <!-- VALUES -->
    <div style="margin-top:68px">
      <div class="sec-label"><i class="fa-solid fa-compass"></i> What Drives Us</div>
      <h2 class="sec-h">Mission, Vision & <em>Values</em></h2>
      <div class="vals">
        <div class="vc">
          <div class="vc-i"><i class="fa-solid fa-bullseye"></i></div>
          <div class="vc-t">Our Mission</div>
          <div class="vc-d">To digitise and simplify the management of residential societies, corporate facilities,
            and commercial complexes through accessible, intuitive technology.</div>
        </div>
        <div class="vc">
          <div class="vc-i"><i class="fa-solid fa-binoculars"></i></div>
          <div class="vc-t">Our Vision</div>
          <div class="vc-d">To become India's leading smart society management platform — connecting every home, guard
            post, service provider, and facility in one intelligent network.</div>
        </div>
        <div class="vc">
          <div class="vc-i"><i class="fa-solid fa-gem"></i></div>
          <div class="vc-t">Our Values</div>
          <div class="vc-d">Trust, transparency, security, and innovation. Every community deserves world-class
            digital tools regardless of size or budget.</div>
        </div>
      </div>
    </div>

    <!-- TIMELINE -->
    <div style="margin-top:68px">
      <div class="sec-label"><i class="fa-solid fa-timeline"></i> Our Journey</div>
      <h2 class="sec-h">How We <em>Got Here</em></h2>
      <div class="tl" style="margin-top:32px">
        <div class="tl-item">
          <div class="tl-l">
            <div class="tl-dot"></div>
            <div class="tl-line"></div>
          </div>
          <div class="tl-c">
            <h4><i class="fa-solid fa-magnifying-glass" style="color:var(--blue);margin-right:8px"></i>The Problem
              Identified</h4>
            <p>We observed residential societies struggling with paper-based visitor logs, unreliable service workers,
              and zero vehicle accountability — and decided to build a permanent solution.</p>
          </div>
        </div>
        <div class="tl-item">
          <div class="tl-l">
            <div class="tl-dot"></div>
            <div class="tl-line"></div>
          </div>
          <div class="tl-c">
            <h4><i class="fa-solid fa-pencil-ruler" style="color:var(--blue);margin-right:8px"></i>Platform Designed
            </h4>
            <p>Legacy Carry was designed as a comprehensive ERP-based digital platform addressing security, services,
              and daily operations simultaneously — built from New Delhi for India.</p>
          </div>
        </div>
        <div class="tl-item">
          <div class="tl-l">
            <div class="tl-dot"></div>
            <div class="tl-line"></div>
          </div>
          <div class="tl-c">
            <h4><i class="fa-solid fa-mobile-screen-button" style="color:var(--blue);margin-right:8px"></i>App
              Launched</h4>
            <p>Legacy Carry launched on mobile, putting vehicle tracking, service booking, guard management, and
              resident tools into one seamless application available pan-India.</p>
          </div>
        </div>
        <div class="tl-item">
          <div class="tl-l">
            <div class="tl-dot"></div>
          </div>
          <div class="tl-c">
            <h4><i class="fa-solid fa-road" style="color:var(--blue);margin-right:8px"></i>The Road Ahead</h4>
            <p>Expanding into asset management, smart IoT integrations, payment gateways, and third-party facility
              systems — building the complete smart society platform of tomorrow.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- DIFFERENTIATORS -->
    <div style="margin-top:68px">
      <div class="sec-label"><i class="fa-solid fa-trophy"></i> Platform Highlights</div>
      <h2 class="sec-h">What Makes Us <em>Different</em></h2>
      <div class="feat-grid" style="margin-top:36px">
        <div class="fc">
          <div class="fc-ico"><i class="fa-solid fa-flag"></i></div>
          <div class="fc-title">Made for India</div>
          <div class="fc-desc">Designed for Indian residential and commercial environments — gated societies,
            multi-tower complexes, mixed-use developments, and more.</div>
        </div>
        <div class="fc">
          <div class="fc-ico"><i class="fa-solid fa-lock"></i></div>
          <div class="fc-title">Security First</div>
          <div class="fc-desc">Every feature built with security at its core — from visitor verification to encrypted
            vehicle logs — protecting your community 24/7.</div>
        </div>
        <div class="fc">
          <div class="fc-ico"><i class="fa-solid fa-rocket"></i></div>
          <div class="fc-title">Scalable Architecture</div>
          <div class="fc-desc">Whether you manage 50 apartments or 5,000 units — Legacy Carry scales effortlessly from
            a single block to an entire township.</div>
        </div>
      </div>
    </div>

    <!-- CTA -->
    <div style="margin-top:68px">
      <div class="cta-box">
        <h2>Join the <em>Smart Society</em> Revolution</h2>
        <p>Be part of the growing network of communities modernising their operations with Legacy Carry.</p>
        <div class="cta-btns">
          <a href="{{ route('contact') }}" class="btn-white"><i class="fa-solid fa-paper-plane"></i> Get in
            Touch</a>
          <a href="{{ route('index') }}" class="btn-ghost"><i class="fa-solid fa-house"></i> Back to Home</a>
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
