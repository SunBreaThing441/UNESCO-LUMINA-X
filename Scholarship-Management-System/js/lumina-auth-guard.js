/**
 * UNESCO LUMINA-X CLIENT-SIDE AUTHENTICATION & NAVIGATION GUARD
 * The United Nations Global Scholastic Grant & Education Equity Network
 *
 * Implements client-side route guarding synchronized with native PHP sessions,
 * enforces strict Student/Admin workspace isolation, and manages session inactivity heartbeats.
 */

class LuminaAuthGuard {
  constructor(options = {}) {
    this.requiredRole = options.requiredRole || 'scholar'; // 'scholar' | 'sovereign' | 'public'
    this.inactivityLimitMs = options.inactivityLimitMs || 15 * 60 * 1000; // 15 minutes
    this.warningTimeMs = options.warningTimeMs || 2 * 60 * 1000; // 2 minute countdown
    this.baseApi = options.baseApi || this.detectBasePath();
    this.idleTimer = null;
    this.countdownTimer = null;
    this.lastActivity = Date.now();

    this.enforceFrameBusting();
    if (this.requiredRole !== 'public') {
      this.initSessionSync();
      this.initInactivityMonitor();
    }
  }

  detectBasePath() {
    // Detect whether running in root or inside /gov/ subfolder
    const path = window.location.pathname;
    if (path.includes('/gov/')) {
      return ''; // Relative to current gov folder: 'auth_status.php'
    }
    return ''; // Relative to root
  }

  enforceFrameBusting() {
    // Prevent Clickjacking attacks in untrusted iframes
    if (window.top !== window.self) {
      window.top.location = window.self.location;
    }
  }

  async checkSession() {
    try {
      const probeUrl = this.requiredRole === 'sovereign' ? 'auth_status.php' : 'auth_status.php';
      const response = await fetch(probeUrl, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        return { authenticated: false, role: null };
      }

      return await response.json();
    } catch (err) {
      console.warn('LuminaAuthGuard: Network failure during session probe, checking ambient DOM state.', err);
      return { authenticated: null, role: null };
    }
  }

  async initSessionSync() {
    const session = await this.checkSession();

    // If probe conclusively returned unauthenticated
    if (session.authenticated === false) {
      this.handleUnauthorizedAccess('Unauthenticated session.');
      return;
    }

    // Role Isolation Enforcer
    if (session.authenticated === true) {
      // If page requires 'sovereign' (Admin) but active session is 'scholar' (Student)
      if (this.requiredRole === 'sovereign' && session.role !== 'sovereign') {
        this.handleCrossPortalViolation('Access Denied: Scholar session cannot access Administrative Sovereign Workspace.');
        return;
      }

      // If page requires 'scholar' (Student) but active session is 'sovereign' (Admin)
      if (this.requiredRole === 'scholar' && session.role !== 'scholar') {
        this.handleCrossPortalViolation('Notice: Sovereign officer session active. Redirecting to Command Center.');
        return;
      }
    }

    // Session is valid and role is synchronized
    window.luminaSession = session;
  }

  handleUnauthorizedAccess(reason) {
    console.warn(`[LuminaGuard] ${reason} Routing to authentication gateway.`);
    sessionStorage.setItem('lumina_redirect_target', window.location.href);

    if (this.requiredRole === 'sovereign') {
      window.location.replace('login.php?error=unauthorized_access');
    } else {
      window.location.replace('login.php?notice=auth_required');
    }
  }

  handleCrossPortalViolation(msg) {
    console.warn(`[LuminaGuard: Role Isolation] ${msg}`);
    if (this.requiredRole === 'sovereign') {
      window.location.replace('login.php?error=unauthorized_access');
    } else {
      window.location.replace('gov/data.php');
    }
  }

  initInactivityMonitor() {
    const resetActivity = () => {
      this.lastActivity = Date.now();
      this.dismissInactivityModal();
    };

    // User activity listeners
    ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll'].forEach((event) => {
      window.addEventListener(event, resetActivity, { passive: true });
    });

    // Inactivity heartbeat check interval
    this.idleTimer = setInterval(() => {
      const idleElapsed = Date.now() - this.lastActivity;
      const timeRemaining = this.inactivityLimitMs - idleElapsed;

      if (timeRemaining <= this.warningTimeMs && timeRemaining > 0) {
        this.showInactivityModal(Math.round(timeRemaining / 1000));
      } else if (timeRemaining <= 0) {
        this.terminateDueToInactivity();
      }
    }, 5000);
  }

  showInactivityModal(secondsRemaining) {
    let modal = document.getElementById('lumina-idle-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'lumina-idle-modal';
      modal.style.cssText = `
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(7, 14, 29, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100000;
        animation: luminaFadeIn 250ms ease;
      `;
      modal.innerHTML = `
        <div style="background: rgba(16, 36, 64, 0.95); border: 1px solid rgba(0, 163, 224, 0.35); border-radius: 20px; padding: 2.5rem; max-width: 440px; text-align: center; color: #fff; box-shadow: 0 20px 60px rgba(0,0,0,0.6);">
          <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(245, 158, 11, 0.2); border: 1px solid #f59e0b; color: #fbbf24; font-size: 1.6rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
            <i class="fa fa-clock-o"></i>
          </div>
          <h3 style="font-family: var(--font-display); font-size: 1.3rem; font-weight: 800; margin-bottom: 0.5rem;">Session Inactivity Detected</h3>
          <p style="color: var(--color-frost-300); font-size: 0.88rem; line-height: 1.5; margin-bottom: 1.5rem;">
            For academic credential security, your active grant session will auto-lock in 
            <span id="idle-countdown-sec" style="font-family: var(--font-mono); font-weight: 800; color: #38bdf8;">${secondsRemaining}</span> seconds.
          </p>
          <button id="btnRenewSession" class="btn-lumina btn-primary" style="width: 100%; padding: 0.85rem;">
            <i class="fa fa-refresh"></i> Extend Active Session
          </button>
        </div>
      `;
      document.body.appendChild(modal);

      document.getElementById('btnRenewSession').addEventListener('click', () => {
        this.lastActivity = Date.now();
        this.dismissInactivityModal();
      });
    }

    const countdownSpan = document.getElementById('idle-countdown-sec');
    if (countdownSpan) {
      countdownSpan.textContent = secondsRemaining;
    }
  }

  dismissInactivityModal() {
    const modal = document.getElementById('lumina-idle-modal');
    if (modal) {
      modal.remove();
    }
  }

  terminateDueToInactivity() {
    clearInterval(this.idleTimer);
    this.dismissInactivityModal();

    const logoutUrl = this.requiredRole === 'sovereign' ? 'logout.php' : 'logout.php';
    window.location.replace(`${logoutUrl}?notice=session_inactivity_timeout`);
  }
}

// Global hook definition for pages to activate their respective guard
window.LuminaAuthGuard = LuminaAuthGuard;
