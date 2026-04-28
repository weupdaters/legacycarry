/* ═══════════════════════════════════════
   LEGACY CARRY — SHARED NAV + FOOTER JS
   ═══════════════════════════════════════ */

/* ── Inject Nav ── */
function injectNav(activePage) {
   const pages = [
        { id: 'home', href: routes.home, icon: 'fa-house', label: 'Home' },
        { id: 'about', href: routes.about, icon: 'fa-circle-info', label: 'About Us' },
        { id: 'contact', href: routes.contact, icon: 'fa-envelope', label: 'Contact' },
        { id: 'privacy', href: routes.privacy, icon: 'fa-shield-halved', label: 'Privacy' },
        { id: 'refund', href: routes.refund, icon: 'fa-rotate-left', label: 'Refund Policy' }
    ];

  const navLinks = pages.map(p => `
    <li>
      <a href="${p.href}" ${p.id === activePage ? 'class="active"' : ''}>
        <i class="fa-solid ${p.icon}"></i> ${p.label}
      </a>
    </li>`).join('');

  const mobLinks = pages.map(p => `
    <a href="${p.href}">
      <i class="fa-solid ${p.icon}"></i> ${p.label}
    </a>`).join('');

  document.body.insertAdjacentHTML('afterbegin', `
    <nav>
      <a class="nav-logo" href="index.html">
        <div class="nav-logo-img"><img src="logo.png" alt="Legacy Carry"/></div>
        <div class="nav-logo-text">Legacy Carry<span>Smart Society Platform</span></div>
      </a>
      <ul class="nav-links">
        ${navLinks}
        <li><button class="nav-admin" onclick="alert('Admin portal link coming soon!')">
          <i class="fa-solid fa-lock"></i> Admin Login
        </button></li>
      </ul>
      <button class="ham" onclick="toggleMob()" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </nav>
    <div class="mob-menu" id="mobMenu">
      ${mobLinks}
      <button onclick="alert('Admin portal link coming soon!')">
        <i class="fa-solid fa-lock"></i> Admin Login
      </button>
    </div>
  `);
}

/* ── Inject Full Footer ── */
function injectFooter() {
  const footerHTML = `
    <footer>
      <div class="foot-grid">
        <div>
          <div class="foot-logo-wrap">
            <div class="foot-logo-img"><img src="logo.png" alt="Legacy Carry"/></div>
            <div class="foot-logo-text">Legacy Carry<span>Smart Society Platform</span></div>
          </div>
          <p class="foot-desc">Book trusted local services &amp; manage society security in one app. Serving residential societies, corporate facilities, and communities across India.</p>
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
            <li><a href="index.html"><i class="fa-solid fa-house"></i> Home</a></li>
            <li><a href="about.html"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
            <li><a href="contact.html"><i class="fa-solid fa-envelope"></i> Contact</a></li>
          </ul>
        </div>
        <div class="foot-col">
          <h4>Legal</h4>
          <ul>
            <li><a href="privacy.html"><i class="fa-solid fa-shield-halved"></i> Privacy Policy</a></li>
            <li><a href="refund.html"><i class="fa-solid fa-rotate-left"></i> Refund Policy</a></li>
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
            <a href="mailto:info@legacycarry.com" style="color:rgba(255,255,255,0.55);text-decoration:none;font-size:0.82rem">info@legacycarry.com</a>
          </div>
        </div>
      </div>
      <div class="foot-bottom">
        <p>&copy; 2024 Legacy Carry. All rights reserved. New Delhi, India.</p>
        <div class="foot-links">
          <a href="privacy.html">Privacy</a>
          <a href="refund.html">Refund</a>
          <a href="contact.html">Contact</a>
        </div>
      </div>
    </footer>`;
  document.body.insertAdjacentHTML('beforeend', footerHTML);
}

/* ── Inject Mini Footer (policy pages) ── */
function injectMiniFooter() {
  document.body.insertAdjacentHTML('beforeend', `
    <div class="mini-footer">
      <div class="mini-footer-inner">
        <p>&copy; 2024 Legacy Carry. All rights reserved.</p>
        <div class="mini-footer-links">
          <a href="index.html">Home</a>
          <a href="privacy.html">Privacy</a>
          <a href="refund.html">Refund</a>
          <a href="contact.html">Contact</a>
        </div>
      </div>
    </div>`);
}

/* ── Mobile menu toggle ── */
function toggleMob() {
  document.getElementById('mobMenu').classList.toggle('open');
}

/* ── Contact form submit ── */
function submitForm() {
  const f = document.getElementById('cf-fname') ? document.getElementById('cf-fname').value : '';
  const e = document.getElementById('cf-email') ? document.getElementById('cf-email').value : '';
  if (!f || !e) { alert('Please fill in your name and email.'); return; }
  document.getElementById('formOk').classList.add('show');
}

/* ── Close mobile menu on link click ── */
document.addEventListener('click', function(e) {
  const menu = document.getElementById('mobMenu');
  if (menu && menu.classList.contains('open')) {
    if (!menu.contains(e.target) && !e.target.closest('.ham')) {
      menu.classList.remove('open');
    }
  }
});
