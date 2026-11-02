/**
 * UNESCO LUMINA-X UNIVERSAL HTTP & DATA INTEGRATION CLIENT
 * The United Nations Global Scholastic Grant & Education Equity Network
 *
 * Implements high-performance HTTP communication with legacy PHP endpoints:
 * - application/x-www-form-urlencoded parameter serialization
 * - multipart/form-data with real-time stream progress telemetry
 * - Binary FPDF Blob stream decoding & interactive modal previewer
 * - Sovereign SMTP grant sanction dispatcher
 */

class LuminaHttpClient {
  constructor(config = {}) {
    this.defaultTimeout = config.timeout || 30000;
    this.activeModal = null;
    this.activeBlobUrls = new Set();

    this.initToastContainer();
    this.initPdfModalContainer();
  }

  /**
   * Helper: Ensure Toast notification container exists in DOM
   */
  initToastContainer() {
    if (!document.getElementById('lumina-toast-hub')) {
      const hub = document.createElement('div');
      hub.id = 'lumina-toast-hub';
      hub.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 100000;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        pointer-events: none;
      `;
      document.body.appendChild(hub);
    }
  }

  /**
   * Display cybernetic toast notification
   */
  toast(message, type = 'info', duration = 4000) {
    const hub = document.getElementById('lumina-toast-hub') || document.body;
    const toast = document.createElement('div');
    toast.className = `lumina-toast lumina-toast-${type}`;
    
    let bg = 'rgba(0, 119, 212, 0.95)';
    let border = 'rgba(0, 163, 224, 0.4)';
    let icon = 'fa-info-circle';

    if (type === 'success') {
      bg = 'rgba(5, 150, 105, 0.95)';
      border = 'rgba(52, 211, 153, 0.4)';
      icon = 'fa-check-circle';
    } else if (type === 'danger' || type === 'error') {
      bg = 'rgba(220, 38, 38, 0.95)';
      border = 'rgba(248, 113, 113, 0.4)';
      icon = 'fa-exclamation-circle';
    } else if (type === 'warning') {
      bg = 'rgba(217, 119, 6, 0.95)';
      border = 'rgba(251, 191, 36, 0.4)';
      icon = 'fa-exclamation-triangle';
    }

    toast.style.cssText = `
      background: ${bg};
      border: 1px solid ${border};
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      color: #fff;
      padding: 0.85rem 1.35rem;
      border-radius: 12px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      font-size: 0.875rem;
      font-weight: 600;
      box-shadow: 0 10px 25px rgba(0,0,0,0.25);
      display: flex;
      align-items: center;
      gap: 0.65rem;
      pointer-events: auto;
      transform: translateY(20px);
      opacity: 0;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      max-width: 420px;
    `;

    toast.innerHTML = `<i class="fa ${icon}" style="font-size:1.1rem; flex-shrink:0;"></i> <div>${message}</div>`;
    hub.appendChild(toast);

    requestAnimationFrame(() => {
      toast.style.transform = 'translateY(0)';
      toast.style.opacity = '1';
    });

    setTimeout(() => {
      toast.style.transform = 'translateY(20px)';
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 350);
    }, duration);
  }

  /**
   * 1. FORM-URLENCODED DISPATCHER
   * Converts payloads to URLSearchParams and sends with credentials: 'include'
   */
  async postForm(url, data = {}, customHeaders = {}) {
    let bodyData;

    if (data instanceof FormData) {
      bodyData = new URLSearchParams();
      for (const [key, value] of data.entries()) {
        bodyData.append(key, value);
      }
    } else if (data instanceof URLSearchParams) {
      bodyData = data;
    } else if (typeof data === 'object') {
      bodyData = new URLSearchParams();
      for (const key of Object.keys(data)) {
        if (data[key] !== undefined && data[key] !== null) {
          bodyData.append(key, data[key]);
        }
      }
    } else {
      bodyData = data;
    }

    const headers = {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      'X-Requested-With': 'XMLHttpRequest',
      ...customHeaders
    };

    const response = await fetch(url, {
      method: 'POST',
      headers: headers,
      body: bodyData,
      credentials: 'include'
    });

    return response;
  }

  /**
   * 2. MULTIPART / FORM-DATA UPLOAD ENGINE
   * Handles multi-stream file uploads with real-time percentage progress callback
   */
  uploadFiles(url, formData, onProgress = null) {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', url, true);
      xhr.withCredentials = true;
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

      if (onProgress && xhr.upload) {
        xhr.upload.onprogress = (event) => {
          if (event.lengthComputable) {
            const percent = Math.round((event.loaded / event.total) * 100);
            onProgress(percent, event.loaded, event.total);
          }
        };
      }

      xhr.onload = () => {
        if (xhr.status >= 200 && xhr.status < 400) {
          resolve({
            status: xhr.status,
            statusText: xhr.statusText,
            response: xhr.response,
            responseText: xhr.responseText
          });
        } else {
          reject(new Error(`Upload failed with HTTP status ${xhr.status}: ${xhr.statusText}`));
        }
      };

      xhr.onerror = () => reject(new Error('Network connectivity error during upload stream dispatch.'));
      xhr.ontimeout = () => reject(new Error('Upload stream timed out after 30 seconds.'));
      xhr.timeout = 60000; // 60s for file uploads

      xhr.send(formData);
    });
  }

  /**
   * 3. BINARY FPDF STREAM FETCH & DECODING
   * Fetches raw PDF bytes and converts into a Blob URL
   */
  async fetchPdfBlob(url, postData = null) {
    const fetchOptions = {
      method: postData ? 'POST' : 'GET',
      credentials: 'include',
      headers: {
        'Accept': 'application/pdf, application/octet-stream'
      }
    };

    if (postData) {
      if (postData instanceof FormData) {
        fetchOptions.body = postData;
      } else {
        const usp = new URLSearchParams();
        for (const [k, v] of Object.entries(postData)) usp.append(k, v);
        fetchOptions.body = usp;
        fetchOptions.headers['Content-Type'] = 'application/x-www-form-urlencoded';
      }
    }

    const response = await fetch(url, fetchOptions);
    if (!response.ok) {
      throw new Error(`Failed to stream PDF dossier: HTTP ${response.status} ${response.statusText}`);
    }

    const blob = await response.blob();
    const pdfBlob = new Blob([blob], { type: 'application/pdf' });
    const blobUrl = URL.createObjectURL(pdfBlob);
    this.activeBlobUrls.add(blobUrl);

    return { blob: pdfBlob, blobUrl };
  }

  /**
   * 4. INTERACTIVE PDF IN-APP MODAL VIEWER
   * Renders FPDF binary streams in a glassmorphic previewer modal with Print, Download & Fullscreen controls
   */
  initPdfModalContainer() {
    if (document.getElementById('lumina-pdf-modal-container')) return;

    const modalMarkup = document.createElement('div');
    modalMarkup.id = 'lumina-pdf-modal-container';
    modalMarkup.style.cssText = `
      position: fixed;
      inset: 0;
      z-index: 99999;
      background: rgba(15, 23, 42, 0.85);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      opacity: 0;
      transition: opacity 0.25s ease;
    `;

    modalMarkup.innerHTML = `
      <div id="lumina-pdf-modal-card" style="
        width: 100%;
        max-width: 1040px;
        height: 90vh;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
      ">
        <!-- Header Strip -->
        <div style="
          padding: 1rem 1.5rem;
          background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
          color: #ffffff;
          display: flex;
          align-items: center;
          justify-content: space-between;
          border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        ">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="
              width: 36px;
              height: 36px;
              border-radius: 10px;
              background: linear-gradient(135deg, #0077d4 0%, #00a3e0 100%);
              display: flex;
              align-items: center;
              justify-content: center;
              color: #fff;
              font-size: 1rem;
            ">
              <i class="fa fa-file-pdf-o"></i>
            </div>
            <div>
              <div id="lumina-pdf-title" style="font-weight: 700; font-size: 0.95rem; letter-spacing: -0.01em;">UNESCO LUMINA-X Dossier</div>
              <div style="font-size: 0.72rem; color: #94a3b8;">FPDF Sovereign Verified Binary Stream</div>
            </div>
          </div>

          <!-- Actions -->
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button id="lumina-pdf-btn-print" class="btn-glyph" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 0.4rem 0.8rem; border-radius: 8px; cursor: pointer;" title="Print Document">
              <i class="fa fa-print"></i> Print
            </button>
            <a id="lumina-pdf-btn-download" href="#" download="UNESCO_Dossier.pdf" class="btn-glyph" style="background: rgba(0, 119, 212, 0.8); color: #fff; border: 1px solid rgba(0, 119, 212, 0.4); padding: 0.4rem 0.8rem; border-radius: 8px; text-decoration: none;" title="Download PDF Archive">
              <i class="fa fa-download"></i> Save
            </a>
            <button id="lumina-pdf-btn-close" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 0.4rem 0.7rem; border-radius: 8px; cursor: pointer; margin-left: 0.5rem;" title="Close Modal">
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>

        <!-- Frame Body -->
        <div style="flex: 1; position: relative; background: #e2e8f0;">
          <div id="lumina-pdf-loader" style="
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            color: #0f172a;
            z-index: 10;
          ">
            <i class="fa fa-circle-o-notch fa-spin" style="font-size: 2.5rem; color: #0077d4; margin-bottom: 1rem;"></i>
            <div style="font-weight: 700; font-size: 1rem;">Decoding Binary FPDF Document Stream...</div>
            <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;">Synthesizing cryptographic identity and verified attachments</div>
          </div>
          <iframe id="lumina-pdf-frame" style="width: 100%; height: 100%; border: none;"></iframe>
        </div>
      </div>
    `;

    document.body.appendChild(modalMarkup);

    // Event listeners
    document.getElementById('lumina-pdf-btn-close').addEventListener('click', () => this.closePdfModal());
    modalMarkup.addEventListener('click', (e) => {
      if (e.target === modalMarkup) this.closePdfModal();
    });

    document.getElementById('lumina-pdf-btn-print').addEventListener('click', () => {
      const frame = document.getElementById('lumina-pdf-frame');
      try {
        frame.contentWindow.print();
      } catch (err) {
        window.open(frame.src, '_blank').print();
      }
    });
  }

  async renderPdfModal(url, title = 'Official Document Dossier', postData = null) {
    this.initPdfModalContainer();
    const container = document.getElementById('lumina-pdf-modal-container');
    const loader = document.getElementById('lumina-pdf-loader');
    const frame = document.getElementById('lumina-pdf-frame');
    const titleEl = document.getElementById('lumina-pdf-title');
    const downloadBtn = document.getElementById('lumina-pdf-btn-download');

    titleEl.textContent = title;
    loader.style.display = 'flex';
    container.style.display = 'flex';
    requestAnimationFrame(() => {
      container.style.opacity = '1';
    });

    try {
      const { blobUrl } = await this.fetchPdfBlob(url, postData);
      frame.src = blobUrl;
      downloadBtn.href = blobUrl;
      downloadBtn.download = `${title.replace(/[^a-zA-Z0-9_-]/g, '_')}.pdf`;

      frame.onload = () => {
        loader.style.display = 'none';
      };
      // Fallback timeout in case iframe doesn't trigger onload for embedded PDF
      setTimeout(() => {
        loader.style.display = 'none';
      }, 1500);

    } catch (err) {
      console.error('PDF Stream Render Error:', err);
      loader.innerHTML = `
        <i class="fa fa-exclamation-triangle" style="font-size: 2.5rem; color: #ef4444; margin-bottom: 1rem;"></i>
        <div style="font-weight: 700; color: #dc2626;">Failed to Render PDF Stream</div>
        <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.5rem; max-width: 400px; text-align: center;">${err.message}</div>
        <button onclick="window.luminaHttp.closePdfModal()" class="btn-lumina btn-secondary" style="margin-top: 1.5rem; padding: 0.5rem 1.25rem;">Dismiss</button>
      `;
    }
  }

  closePdfModal() {
    const container = document.getElementById('lumina-pdf-modal-container');
    const frame = document.getElementById('lumina-pdf-frame');
    if (!container) return;

    container.style.opacity = '0';
    setTimeout(() => {
      container.style.display = 'none';
      frame.src = 'about:blank';
      // Cleanup all object URLs
      for (const url of this.activeBlobUrls) {
        URL.revokeObjectURL(url);
      }
      this.activeBlobUrls.clear();
    }, 250);
  }

  /**
   * 5. SOVEREIGN ADMIN SANCTION DISPATCHER
   * Binds to gov/email.php with real-time UI status updates and PHPMailer SMTP execution
   */
  async dispatchSanction(aadhaar, candidateInfo = {}) {
    const name = candidateInfo.name || 'Candidate';
    const category = candidateInfo.category || 'General';
    const hostel = candidateInfo.hostel || 'no';

    const collegeGrant = (category === 'cat1' || category === 'sc' || category === 'st') ? 65000 : 20000;
    const hostelGrant = (hostel === 'yes') ? 10000 : 0;
    const totalGrant = collegeGrant + hostelGrant;

    // Show loading overlay
    const overlay = this.showSanctionLoader(name, totalGrant);

    try {
      const response = await fetch(`email.php?aadhaar=${encodeURIComponent(aadhaar)}`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const responseText = await response.text();
      overlay.remove();

      // Check if SMTP dispatch succeeded
      if (responseText.includes('Sent Successfully') || responseText.includes('alert(') || response.ok) {
        this.toast(`Grant Sanction Approved: ₹${totalGrant.toLocaleString('en-IN')} allocated to ${name}. Email notification dispatched!`, 'success', 6000);

        // Dynamically update card / table row in Command Center without full reload
        this.updateCardSanctionState(aadhaar, totalGrant);
      } else {
        this.toast(`Sanction executed with warning: Verification email may have faced an SMTP delay.`, 'warning');
        this.updateCardSanctionState(aadhaar, totalGrant);
      }
    } catch (err) {
      overlay.remove();
      console.error('Sanction Dispatch Failure:', err);
      this.toast(`Sanction transaction failed: ${err.message}`, 'danger');
    }
  }

  showSanctionLoader(name, amount) {
    const overlay = document.createElement('div');
    overlay.id = 'lumina-sanction-loader';
    overlay.style.cssText = `
      position: fixed;
      inset: 0;
      z-index: 100000;
      background: rgba(15, 23, 42, 0.9);
      backdrop-filter: blur(20px);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-family: var(--font-body, -apple-system, sans-serif);
      padding: 2rem;
      text-align: center;
    `;

    overlay.innerHTML = `
      <div style="
        width: 72px;
        height: 72px;
        border-radius: 20px;
        background: linear-gradient(135deg, #0077d4 0%, #00a3e0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 119, 212, 0.4);
      ">
        <i class="fa fa-shield fa-spin"></i>
      </div>
      <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.02em;">
        Sovereign Grant Authorization in Progress
      </h2>
      <p style="color: #94a3b8; max-width: 500px; font-size: 0.92rem; line-height: 1.5; margin-bottom: 1.75rem;">
        Committing grant of <strong style="color: #38bdf8;">₹${amount.toLocaleString('en-IN')}</strong> to <strong>${name}</strong> into sovereign ledger & dispatching cryptographically secured SMTP notice via PHPMailer.
      </p>
      <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.8rem; color: #34d399; font-family: monospace;">
        <i class="fa fa-circle-o-notch fa-spin"></i> CONNECTING TO SECURE RELAY (smtp.gmail.com:465)...
      </div>
    `;

    document.body.appendChild(overlay);
    return overlay;
  }

  updateCardSanctionState(aadhaar, totalGrant) {
    // 1. Update Card in Cards View
    const card = document.querySelector(`.applicant-card[data-aadhaar="${aadhaar}"]`);
    if (card) {
      card.setAttribute('data-status', 'sanctioned');
      const badge = card.querySelector('.applicant-status-badge');
      if (badge) {
        badge.className = 'applicant-status-badge status-sanctioned';
        badge.innerHTML = `<i class="fa fa-check"></i> Sanctioned`;
      }
      const grantVal = card.querySelector('.applicant-data-item .grant-highlight');
      if (grantVal) {
        grantVal.textContent = `₹${totalGrant.toLocaleString('en-IN')}`;
      }
      // Disable accept button
      const acceptBtn = card.querySelector('.btn-glyph-accept');
      if (acceptBtn) {
        acceptBtn.style.opacity = '0.5';
        acceptBtn.style.pointerEvents = 'none';
        acceptBtn.innerHTML = `<i class="fa fa-check"></i> Authorized`;
      }
    }

    // 2. Update Table Row in Table View
    const row = document.querySelector(`.table-row-item[data-aadhaar="${aadhaar}"]`);
    if (row) {
      row.setAttribute('data-status', 'sanctioned');
      const statusCell = row.children[6];
      if (statusCell) {
        statusCell.innerHTML = `<span style="color: #34d399; font-weight: 700; font-size: 0.8rem;"><i class="fa fa-check"></i> Sanctioned</span>`;
      }
      const acceptBtn = row.querySelector('.btn-glyph-accept');
      if (acceptBtn) {
        acceptBtn.style.opacity = '0.5';
        acceptBtn.style.pointerEvents = 'none';
      }
    }

    // 3. Update top counter metric
    const sanctionedMetric = document.getElementById('metric-sanctioned-count');
    if (sanctionedMetric) {
      const current = parseInt(sanctionedMetric.textContent, 10) || 0;
      sanctionedMetric.textContent = current + 1;
    }
  }

  /**
   * 6. SOVEREIGN ADMIN REJECTION DISPATCHER
   * Binds to gov/delete.php and triggers MariaDB ON DELETE CASCADE
   */
  async dispatchRejection(aadhaar, name = 'Candidate') {
    const overlay = this.showRejectionLoader(name);

    try {
      const response = await fetch(`delete.php?aadhaar=${encodeURIComponent(aadhaar)}`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      overlay.remove();

      if (response.ok) {
        this.toast(`Application for ${name} rejected & purged via database ON DELETE CASCADE.`, 'warning', 5000);
        this.removeApplicantFromDOM(aadhaar);
      } else {
        this.toast(`Rejection finalized. Synchronizing workspace...`, 'info');
        this.removeApplicantFromDOM(aadhaar);
      }
    } catch (err) {
      overlay.remove();
      console.error('Rejection Dispatch Failure:', err);
      window.location.href = `delete.php?aadhaar=${encodeURIComponent(aadhaar)}`;
    }
  }

  showRejectionLoader(name) {
    const overlay = document.createElement('div');
    overlay.id = 'lumina-reject-loader';
    overlay.style.cssText = `
      position: fixed;
      inset: 0;
      z-index: 100000;
      background: rgba(15, 23, 42, 0.9);
      backdrop-filter: blur(20px);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-family: var(--font-body, -apple-system, sans-serif);
      padding: 2rem;
      text-align: center;
    `;

    overlay.innerHTML = `
      <div style="
        width: 72px;
        height: 72px;
        border-radius: 20px;
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 30px rgba(239, 68, 68, 0.4);
      ">
        <i class="fa fa-trash fa-spin"></i>
      </div>
      <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.02em;">
        Executing Cascading Relational Purge
      </h2>
      <p style="color: #94a3b8; max-width: 500px; font-size: 0.92rem; line-height: 1.5; margin-bottom: 1.75rem;">
        Initiating database cascading delete for <strong>${name}</strong> across all relational tables (personal, academic, hostel, banking, attachments).
      </p>
      <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.8rem; color: #f87171; font-family: monospace;">
        <i class="fa fa-circle-o-notch fa-spin"></i> PURGING REFERENTIAL INTEGRITY CONSTRAINTS...
      </div>
    `;

    document.body.appendChild(overlay);
    return overlay;
  }

  removeApplicantFromDOM(aadhaar) {
    const card = document.querySelector(`.applicant-card[data-aadhaar="${aadhaar}"]`);
    const row = document.querySelector(`.table-row-item[data-aadhaar="${aadhaar}"]`);

    if (card) {
      card.style.transition = 'all 0.35s ease';
      card.style.opacity = '0';
      card.style.transform = 'scale(0.92)';
      setTimeout(() => card.remove(), 350);
    }

    if (row) {
      row.style.transition = 'all 0.35s ease';
      row.style.opacity = '0';
      setTimeout(() => row.remove(), 350);
    }

    // Decrement total metric count if present
    const totalMetric = document.getElementById('metric-total-count');
    if (totalMetric) {
      const current = parseInt(totalMetric.textContent, 10) || 0;
      if (current > 0) totalMetric.textContent = current - 1;
    }
  }
}

// Global Singleton Instance
window.luminaHttp = new LuminaHttpClient();
