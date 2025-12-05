class CookieConsent {
  constructor() {
    this.cookieName = 'user_cookie_consent';
    this.cookieExpiry = 365;  // Default expiry time
    this.init();
  }

  init() {
    const consent = this.getCookie(this.cookieName);
    console.log('Consent cookie at init:', consent); // Debugging line
    if (!consent) {
      this.showBanner();
    } else {
      console.log('Cookie consent already given:', consent); // Debugging line
    }
  }

  showBanner() {
    const banner = document.createElement('div');
    banner.id = 'cookie-consent-banner';
    banner.innerHTML = `
      <div class="cookie-consent-content">
        <div class="cookie-consent-text">
          <h3>🍪 We use cookies</h3>
          <p>We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.</p>
        </div>
        <div class="cookie-consent-buttons">
          <button id="cookie-accept-all" class="cookie-btn cookie-btn-primary">Accept All</button>
          <button id="cookie-reject" class="cookie-btn cookie-btn-secondary">Reject</button>
          <button id="cookie-settings" class="cookie-btn cookie-btn-link">Cookie Settings</button>
        </div>
      </div>
    `;

    document.body.appendChild(banner);

    document.getElementById('cookie-accept-all').addEventListener('click', () => this.acceptAll());
    document.getElementById('cookie-reject').addEventListener('click', () => this.rejectAll());
    document.getElementById('cookie-settings').addEventListener('click', () => this.showSettings());
  }

  showSettings() {
    const banner = document.getElementById('cookie-consent-banner');
    if (banner) banner.style.display = 'none';

    const modal = document.createElement('div');
    modal.id = 'cookie-settings-modal';
    modal.innerHTML = `
      <div class="cookie-modal-overlay"></div>
      <div class="cookie-modal-content">
        <div class="cookie-modal-header">
          <h2>Cookie Settings</h2>
          <button class="cookie-modal-close">&times;</button>
        </div>
        <div class="cookie-modal-body">
          <div class="cookie-category">
            <div class="cookie-category-header">
              <label>
                <input type="checkbox" id="cookie-necessary" checked disabled>
                <span class="cookie-category-title">Necessary Cookies</span>
              </label>
            </div>
            <p class="cookie-category-desc">These cookies are essential for the website to function properly. They cannot be disabled.</p>
          </div>

          <div class="cookie-category">
            <div class="cookie-category-header">
              <label>
                <input type="checkbox" id="cookie-analytics">
                <span class="cookie-category-title">Analytics Cookies</span>
              </label>
            </div>
            <p class="cookie-category-desc">These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously.</p>
          </div>

          <div class="cookie-category">
            <div class="cookie-category-header">
              <label>
                <input type="checkbox" id="cookie-marketing">
                <span class="cookie-category-title">Marketing Cookies</span>
              </label>
            </div>
            <p class="cookie-category-desc">These cookies are used to track visitors across websites to display relevant advertisements.</p>
          </div>
        </div>
        <div class="cookie-modal-footer">
          <button id="cookie-save-settings" class="cookie-btn cookie-btn-primary">Save Settings</button>
          <button id="cookie-accept-all-modal" class="cookie-btn cookie-btn-secondary">Accept All</button>
        </div>
      </div>
    `;

    document.body.appendChild(modal);

    modal.querySelector('.cookie-modal-close').addEventListener('click', () => this.closeSettings());
    modal.querySelector('.cookie-modal-overlay').addEventListener('click', () => this.closeSettings());
    document.getElementById('cookie-save-settings').addEventListener('click', () => this.saveSettings());
    document.getElementById('cookie-accept-all-modal').addEventListener('click', () => this.acceptAll());
  }

  closeSettings() {
    const modal = document.getElementById('cookie-settings-modal');
    if (modal) {
      modal.remove();
      if (!this.getCookie(this.cookieName)) {
        const banner = document.getElementById('cookie-consent-banner');
        if (banner) banner.style.display = 'block';
      }
    }
  }

  acceptAll() {
    const consent = {
      necessary: true,
      analytics: true,
      marketing: true,
      timestamp: new Date().toISOString()
    };

    this.setCookie(this.cookieName, JSON.stringify(consent), this.cookieExpiry);
    this.removeBanner();
    this.closeSettings();
    window.dispatchEvent(new CustomEvent('cookieConsentGiven', { detail: consent }));
    console.log('All cookies accepted');
  }

  rejectAll() {
    const consent = {
      necessary: true,
      analytics: false,
      marketing: false,
      timestamp: new Date().toISOString()
    };

    this.setCookie(this.cookieName, JSON.stringify(consent), this.cookieExpiry);
    this.removeBanner();
    window.dispatchEvent(new CustomEvent('cookieConsentGiven', { detail: consent }));
    console.log('Optional cookies rejected');
  }

  saveSettings() {
    const consent = {
      necessary: true,
      analytics: document.getElementById('cookie-analytics').checked,
      marketing: document.getElementById('cookie-marketing').checked,
      timestamp: new Date().toISOString()
    };

    this.setCookie(this.cookieName, JSON.stringify(consent), this.cookieExpiry);
    this.removeBanner();
    this.closeSettings();
    window.dispatchEvent(new CustomEvent('cookieConsentGiven', { detail: consent }));
    console.log('Cookie settings saved:', consent);
  }

  removeBanner() {
    const banner = document.getElementById('cookie-consent-banner');
    if (banner) banner.remove();
  }

  setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
  }

  getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) === ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  getConsent() {
    const consent = this.getCookie(this.cookieName);
    return consent ? JSON.parse(consent) : null;
  }

  isAllowed(type) {
    const consent = this.getConsent();
    return consent ? consent[type] === true : false;
  }
}

// This will clear the cookie and force the banner to appear on the next load
function clearCookieConsent() {
  document.cookie = 'user_cookie_consent=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; SameSite=Lax';
  console.log("Cookie consent cleared");
}

// Simulating login success (clear the cookie and force the banner to show)
function onLoginSuccess() {
  clearCookieConsent();
  // Proceed with the login flow
  // Optionally, reload the page or redirect to the home page
  location.reload();  // This will trigger the init function again after login
}

// Demo login handler
document.getElementById('loginButton').addEventListener('click', onLoginSuccess);

// Initialize cookie consent on page load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.cookieConsent = new CookieConsent();
  });
} else {
  window.cookieConsent = new CookieConsent();
}
