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