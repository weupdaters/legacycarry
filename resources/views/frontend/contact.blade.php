<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us – Legacy Carry</title>
  <meta name="description"
    content="Get in touch with Legacy Carry. Call, email, or send us a message. We respond within 24 business hours." />
  <link rel="icon" type="image/png" href="{{ asset('frontend/logo.png') }}" />
  <link rel="stylesheet" href="{{ asset('frontend/style.css') }}" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .page-hero {
      background: linear-gradient(160deg, #fff 0%, #eef1f8 60%, #dce6fb 100%);
      padding: 120px 5vw 64px;
      border-bottom: 1.5px solid var(--border);
    }

    .page-hero-inner {
      max-width: 1100px;
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
      max-width: 500px;
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

    .map-embed {
      width: 100%;
      height: 240px;
      border-radius: 16px;
      border: 1.5px solid var(--border);
      margin-top: 20px;
      overflow: hidden;
    }

    .map-embed iframe {
      width: 100%;
      height: 100%;
      border: none;
      filter: grayscale(20%);
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
        <a href="{{ route('contact') }}" class="active">
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

  <!-- PAGE HERO -->
  <div class="page-hero">
    <div class="page-hero-inner">
      <div class="breadcrumb">
        <a href="{{ route('index') }}"><i class="fa-solid fa-house"></i> Home</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>Contact</span>
      </div>
      <div class="page-hero-tag"><i class="fa-solid fa-envelope"></i> Get In Touch</div>
      <h1>We'd Love to <em>Hear</em><br />From You</h1>
      <p>Have questions about Legacy Carry? Want to onboard your society or facility? Our team will respond promptly.
      </p>
    </div>
  </div>

  <!-- CONTACT SECTION -->
  <div class="contact-wrap">
    <div class="c-split">

      <!-- LEFT: INFO -->
      <div>
        <div class="info-card">
          <h3>Contact Information</h3>
          <p class="sub">Legacy Carry — Smart Society Management Platform</p>

          <div class="ci">
            <div class="ci-i"><i class="fa-solid fa-location-dot"></i></div>
            <div class="ci-d">
              <label>Address</label>
              <span style="font-size:0.82rem;line-height:1.7;font-weight:500">
                Property No. 70 GF, Kh No 1513/1,<br />
                Baba Mohalla, Aya Nagar Ext. Phase 3,<br />
                New Delhi – 110047
              </span>
            </div>
          </div>

          <div class="ci">
            <div class="ci-i"><i class="fa-solid fa-phone"></i></div>
            <div class="ci-d">
              <label>Phone</label>
              <a href="tel:+919217535473">+91 92175 35473</a>
            </div>
          </div>

          <div class="ci">
            <div class="ci-i"><i class="fa-solid fa-envelope"></i></div>
            <div class="ci-d">
              <label>Email</label>
              <a href="mailto:info@legacycarry.com">info@legacycarry.com</a>
            </div>
          </div>

          <div class="ci">
            <div class="ci-i"><i class="fa-solid fa-clock"></i></div>
            <div class="ci-d">
              <label>Response Time</label>
              <span>Within 24 business hours</span>
            </div>
          </div>

          <div class="ci">
            <div class="ci-i"><i class="fa-solid fa-map-location-dot"></i></div>
            <div class="ci-d">
              <label>Service Area</label>
              <span>Pan India</span>
            </div>
          </div>
        </div>

        <div class="qbtns">
          <a href="tel:+919217535473" class="btn-solid">
            <i class="fa-solid fa-phone"></i> Call Now
          </a>
          <a href="mailto:info@legacycarry.com" class="btn-outline">
            <i class="fa-solid fa-envelope"></i> Send Email
          </a>
        </div>

        <!-- MAP -->
        <div class="map-embed">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3506.5!2d77.07!3d28.51!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjjCsDMwJzM2LjAiTiA3N8KwMDQnMTIuMCJF!5e0!3m2!1sen!2sin!4v1234567890"
            allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
            title="Legacy Carry Office Location">
          </iframe>
        </div>
      </div>

      <!-- RIGHT: FORM -->
      <div class="form-card">
        <h3><i class="fa-solid fa-paper-plane" style="color:var(--blue);margin-right:8px"></i>Send a Message</h3>

        <div class="frow">
          <div class="fg">
            <label><i class="fa-solid fa-user"></i> First Name</label>
            <input type="text" placeholder="Rahul" id="cf-fname" />
          </div>
          <div class="fg">
            <label><i class="fa-solid fa-user"></i> Last Name</label>
            <input type="text" placeholder="Sharma" />
          </div>
        </div>

        <div class="fg">
          <label><i class="fa-solid fa-envelope"></i> Email Address</label>
          <input type="email" placeholder="rahul@example.com" id="cf-email" />
        </div>

        <div class="fg">
          <label><i class="fa-solid fa-phone"></i> Phone Number</label>
          <input type="tel" placeholder="+91 98765 43210" />
        </div>

        <div class="fg">
          <label><i class="fa-solid fa-id-badge"></i> I Am A...</label>
          <select>
            <option value="">Select your role</option>
            <option>Society Administrator</option>
            <option>Resident</option>
            <option>Corporate Facility Manager</option>
            <option>Security Agency</option>
            <option>Service Provider</option>
            <option>Other</option>
          </select>
        </div>

        <div class="fg">
          <label><i class="fa-solid fa-tag"></i> Subject</label>
          <input type="text" placeholder="Onboarding, support, general enquiry..." />
        </div>

        <div class="fg">
          <label><i class="fa-solid fa-message"></i> Message</label>
          <textarea placeholder="Tell us about your society, requirements, or any questions..."></textarea>
        </div>

        <button class="btn-solid" style="width:100%;justify-content:center" onclick="submitForm()">
          <i class="fa-solid fa-paper-plane"></i> Send Message
        </button>
        <div class="form-ok" id="formOk">
          <i class="fa-solid fa-circle-check" style="margin-right:6px"></i>
          Message sent! Our team will get back to you within 24 business hours.
        </div>
      </div>
    </div>

    <!-- FAQ -->
    <div style="margin-top:72px">
      <div class="sec-label"><i class="fa-solid fa-circle-question"></i> FAQ</div>
      <h2 class="sec-h">Common <em>Questions</em></h2>
      <div class="faq-grid">
        <div class="fc">
          <div class="fc-ico"><i class="fa-solid fa-building-circle-arrow-right"></i></div>
          <div class="fc-title">How do I onboard my society?</div>
          <div class="fc-desc" style="margin-top:8px">Contact us with your society details. Our team will guide you
            through setup — from resident registration to guard onboarding.</div>
        </div>
        <div class="fc">
          <div class="fc-ico"><i class="fa-solid fa-flask"></i></div>
          <div class="fc-title">Is there a free trial?</div>
          <div class="fc-desc" style="margin-top:8px">Yes — we offer a pilot programme for new societies. Contact us
            to learn about trial terms and onboarding support options.</div>
        </div>
        <div class="fc">
          <div class="fc-ico"><i class="fa-solid fa-map-pin"></i></div>
          <div class="fc-title">Which cities are supported?</div>
          <div class="fc-desc" style="margin-top:8px">Legacy Carry is available pan-India. Metro or tier-2 city — our
            platform is built for your community.</div>
        </div>
        <div class="fc">
          <div class="fc-ico"><i class="fa-solid fa-briefcase"></i></div>
          <div class="fc-title">Can corporate offices use this?</div>
          <div class="fc-desc" style="margin-top:8px">Absolutely. Legacy Carry supports corporate offices, commercial
            complexes, industrial facilities, and mixed-use developments.</div>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <div class="foot-grid">
      <div>
        <div class="foot-logo-wrap">
          <div class="foot-logo-img"><img src="logo.png" alt="Legacy Carry"></div>
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
