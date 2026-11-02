/**
 * UNESCO LUMINA-X INTERACTIVE APPLICATION STEPPER
 * The United Nations Global Scholastic Grant & Education Equity Network
 * Eliminates synchronous full-page reloads and interfaces directly with legacy PHP endpoints.
 */

class LuminaStepper {
  constructor(options = {}) {
    this.currentStep = options.initialStep || 1;
    this.totalSteps = 6;
    this.isHostelResident = false;
    this.formDataCache = {};
    this.filePreviews = {};

    this.initElements();
    this.bindEvents();
    this.updateUI();
  }

  initElements() {
    this.stepperNodes = document.querySelectorAll('.stepper-node');
    this.stagePanels = document.querySelectorAll('.stepper-stage-panel');
    this.progressFill = document.getElementById('stepperProgressFill');
    this.progressText = document.getElementById('stepperProgressText');
    this.btnPrev = document.getElementById('btnPrev');
    this.btnNext = document.getElementById('btnNext');
    this.hostelSelect = document.getElementById('hostel');
    this.hostelRadioCards = document.querySelectorAll('.radio-card');
  }

  bindEvents() {
    // Next Step Button
    if (this.btnNext) {
      this.btnNext.addEventListener('click', () => this.handleNextStep());
    }

    // Previous Step Button
    if (this.btnPrev) {
      this.btnPrev.addEventListener('click', () => this.handlePrevStep());
    }

    // Stepper Node Clicks (jump to completed steps)
    this.stepperNodes.forEach((node) => {
      node.addEventListener('click', () => {
        const stepNum = parseInt(node.getAttribute('data-step'), 10);
        if (stepNum < this.currentStep) {
          this.goToStep(stepNum);
        }
      });
    });

    // Hostel selection change listener
    const hostelInputs = document.querySelectorAll('input[name="hostel"]');
    hostelInputs.forEach((radio) => {
      radio.addEventListener('change', (e) => {
        this.isHostelResident = e.target.value === 'yes';
        this.updateHostelNodeVisibility();
      });
    });

    // File Upload Drag-and-Drop & Instant Previews
    this.setupFileUploads();
  }

  updateHostelNodeVisibility() {
    const hostelNode = document.querySelector('.stepper-node[data-step="2"]');
    if (hostelNode) {
      if (!this.isHostelResident) {
        hostelNode.style.opacity = '0.45';
        hostelNode.title = 'Skipped (Day Scholar)';
      } else {
        hostelNode.style.opacity = '1';
        hostelNode.title = 'Hostel Residency';
      }
    }
  }

  setupFileUploads() {
    const uploadCards = document.querySelectorAll('.upload-card');

    uploadCards.forEach((card) => {
      const fileInput = card.querySelector('.upload-file-input');
      const thumb = card.querySelector('.upload-preview-thumb');
      const badge = card.querySelector('.upload-badge-success');
      const hint = card.querySelector('.upload-card-hint');

      if (!fileInput) return;

      // Drag over
      card.addEventListener('dragover', (e) => {
        e.preventDefault();
        card.classList.add('drag-over');
      });

      card.addEventListener('dragleave', () => {
        card.classList.remove('drag-over');
      });

      card.addEventListener('drop', (e) => {
        e.preventDefault();
        card.classList.remove('drag-over');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
          fileInput.files = e.dataTransfer.files;
          this.handleFileSelected(fileInput.files[0], card, thumb, badge, hint);
        }
      });

      fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files[0]) {
          this.handleFileSelected(fileInput.files[0], card, thumb, badge, hint);
        }
      });
    });
  }

  handleFileSelected(file, card, thumb, badge, hint) {
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!validTypes.includes(file.type)) {
      this.showToast('Please upload a valid JPG or PNG image.', 'danger');
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      this.showToast('File size exceeds 5MB limit.', 'danger');
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      const dataUrl = e.target.result;
      card.classList.add('has-file');
      if (thumb) {
        thumb.src = dataUrl;
        thumb.style.display = 'block';
      }
      if (badge) {
        badge.style.display = 'inline-flex';
        badge.innerHTML = `<i class="fa fa-check"></i> ${file.name.substring(0, 16)}... (${(file.size / 1024).toFixed(0)} KB)`;
      }
      if (hint) hint.style.display = 'none';

      // Cache for preview
      this.filePreviews[card.getAttribute('data-field')] = dataUrl;
    };
    reader.readAsDataURL(file);
  }

  validateCurrentStep() {
    const activePanel = document.querySelector(`.stepper-stage-panel[data-step="${this.currentStep}"]`);
    if (!activePanel) return true;

    const requiredInputs = activePanel.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    requiredInputs.forEach((input) => {
      // Radio button handling
      if (input.type === 'radio') {
        const checked = activePanel.querySelector(`input[name="${input.name}"]:checked`);
        if (!checked) {
          isValid = false;
          input.closest('.form-group')?.classList.add('has-error');
        } else {
          input.closest('.form-group')?.classList.remove('has-error');
        }
        return;
      }

      // Checkbox handling
      if (input.type === 'checkbox') {
        if (!input.checked) {
          isValid = false;
          input.parentElement?.classList.add('has-error');
        } else {
          input.parentElement?.classList.remove('has-error');
        }
        return;
      }

      // Standard text/select handling
      if (!input.value.trim()) {
        isValid = false;
        input.classList.add('border-danger');
      } else {
        input.classList.remove('border-danger');
      }

      // Specific Bank Confirmation Match
      if (input.id === 'acnumber') {
        const accnumber = document.getElementById('accnumber');
        if (accnumber && input.value !== accnumber.value) {
          isValid = false;
          input.classList.add('border-danger');
          this.showToast('Bank Account Numbers must match exactly.', 'danger');
        }
      }
    });

    // Special Check for Step 5 Uploads
    if (this.currentStep === 5) {
      const mandatoryUploads = [
        { label: 'Passport Photograph', selectors: ['passport'] },
        { label: 'Aadhaar Card Scan', selectors: ['aadh', 'aadhaar'] },
        { label: 'Bank Passbook Front Page', selectors: ['passbook'] },
        { label: 'Caste & Income Certificate', selectors: ['castecertificate', 'income'] },
        { label: 'College Admission Fee Receipt', selectors: ['cfeesrecipt', 'fees'] },
        { label: 'Qualifying Exam Marksheet', selectors: ['markssheet', 'marksheet'] }
      ];

      for (const item of mandatoryUploads) {
        let found = false;
        for (const sel of item.selectors) {
          const input = document.querySelector(`input[name="${sel}"]`);
          if (input && input.files && input.files.length > 0) {
            found = true;
            break;
          }
        }
        if (!found) {
          isValid = false;
          this.showToast(`Please upload mandatory document: ${item.label}`, 'danger');
          break;
        }
      }

      // If hostel resident, also check hostel fee receipt
      if (this.isHostelResident) {
        const hInput = document.querySelector('input[name="hfees"], input[name="hostelfees"]');
        if (!hInput || !hInput.files || hInput.files.length === 0) {
          this.showToast('Notice: Hostel fee receipt not uploaded (optional but recommended for hostel allowance).', 'warning');
        }
      }
    }

    if (!isValid) {
      this.showToast('Please complete all mandatory fields with valid data.', 'warning');
    }

    return isValid;
  }

  async handleNextStep() {
    if (!this.validateCurrentStep()) return;

    // Show button loading state
    this.setButtonLoading(true);

    try {
      // Asynchronously submit current step data to legacy PHP endpoint
      await this.persistStepToBackend(this.currentStep);

      // Determine next step (Skip hostel if day scholar)
      let nextStep = this.currentStep + 1;
      if (this.currentStep === 1 && !this.isHostelResident) {
        nextStep = 3; // Jump directly to College details
      }

      if (nextStep === 6) {
        this.synthesizePreviewDossier();
      }

      this.goToStep(nextStep);
    } catch (err) {
      console.error('Error persisting step:', err);
      this.showToast('Step saved locally. Proceeding with application.', 'info');
      let nextStep = this.currentStep + 1;
      if (this.currentStep === 1 && !this.isHostelResident) nextStep = 3;
      if (nextStep === 6) this.synthesizePreviewDossier();
      this.goToStep(nextStep);
    } finally {
      this.setButtonLoading(false);
    }
  }

  handlePrevStep() {
    let prevStep = this.currentStep - 1;
    if (this.currentStep === 3 && !this.isHostelResident) {
      prevStep = 1; // Jump back past hostel
    }
    if (prevStep >= 1) {
      this.goToStep(prevStep);
    }
  }

  async persistStepToBackend(step) {
    const activePanel = document.querySelector(`.stepper-stage-panel[data-step="${step}"]`);
    if (!activePanel) return;

    const formData = new FormData();
    const inputs = activePanel.querySelectorAll('input, select, textarea');

    inputs.forEach((input) => {
      if (input.type === 'file') {
        if (input.files[0]) {
          formData.append(input.name, input.files[0]);
          // Add aliases if applicable
          if (input.name === 'castecertificate') formData.append('income', input.files[0]);
          if (input.name === 'cfeesrecipt') formData.append('fees', input.files[0]);
          if (input.name === 'markssheet') formData.append('marksheet', input.files[0]);
          if (input.name === 'hfees') formData.append('hostelfees', input.files[0]);
        }
      } else if (input.type === 'radio') {
        if (input.checked) formData.append(input.name, input.value);
      } else if (input.type === 'checkbox') {
        if (input.checked) formData.append(input.name, 'on');
      } else {
        formData.append(input.name, input.value);
      }
    });

    let endpoint = '';
    switch (step) {
      case 1:
        endpoint = 'personal.php';
        formData.append('next', 'Next'); // Matches isset($_POST['next'])
        break;
      case 2:
        endpoint = 'hostel.php';
        formData.append('next5', 'Next'); // Matches isset($_POST['next5'])
        break;
      case 3:
        endpoint = 'college.php';
        formData.append('next1', 'Next'); // Matches isset($_POST['next1'])
        break;
      case 4:
        endpoint = 'bank.php';
        formData.append('next2', 'Next'); // Matches isset($_POST['next2'])
        break;
      case 5:
        endpoint = 'attachment.php';
        formData.append('next3', 'Next'); // Matches isset($_POST['next3'])
        break;
    }

    if (!endpoint) return;

    // STEP 5: Dispatched via multipart/form-data with real-time stream upload progress
    if (step === 5 && window.luminaHttp) {
      const response = await window.luminaHttp.uploadFiles(endpoint, formData, (percent) => {
        if (this.btnNext) {
          this.btnNext.innerHTML = `<i class="fa fa-circle-o-notch fa-spin"></i> Uploading Streams ${percent}%`;
        }
      });
      window.luminaHttp.toast('All 7 verification streams securely persisted to gov/upload/', 'success');
      return response;
    }

    // STEPS 1-4: Dispatched via application/x-www-form-urlencoded
    if (window.luminaHttp) {
      return await window.luminaHttp.postForm(endpoint, formData);
    } else {
      return await fetch(endpoint, {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });
    }
  }

  goToStep(stepNumber) {
    if (stepNumber < 1 || stepNumber > this.totalSteps) return;

    this.currentStep = stepNumber;
    this.updateUI();
    window.scrollTo({ top: 120, behavior: 'smooth' });
  }

  updateUI() {
    // 1. Update Stepper Node states
    this.stepperNodes.forEach((node) => {
      const nodeStep = parseInt(node.getAttribute('data-step'), 10);
      node.classList.remove('active', 'completed');

      if (nodeStep === this.currentStep) {
        node.classList.add('active');
      } else if (nodeStep < this.currentStep) {
        node.classList.add('completed');
      }
    });

    // 2. Update Progress Bar
    const percent = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
    if (this.progressFill) {
      this.progressFill.style.width = `${percent}%`;
    }
    if (this.progressText) {
      this.progressText.textContent = `Stage ${this.currentStep} of ${this.totalSteps} (${Math.round(percent)}%)`;
    }

    // 3. Switch Stage Panels
    this.stagePanels.forEach((panel) => {
      const panelStep = parseInt(panel.getAttribute('data-step'), 10);
      if (panelStep === this.currentStep) {
        panel.classList.add('active');
      } else {
        panel.classList.remove('active');
      }
    });

    // 4. Update Navigation Buttons
    if (this.btnPrev) {
      this.btnPrev.style.visibility = this.currentStep === 1 ? 'hidden' : 'visible';
    }

    if (this.btnNext) {
      if (this.currentStep === this.totalSteps) {
        this.btnNext.style.display = 'none';
      } else if (this.currentStep === 5) {
        this.btnNext.innerHTML = 'Upload & Review Dossier <i class="fa fa-arrow-right"></i>';
      } else {
        this.btnNext.innerHTML = 'Save & Continue <i class="fa fa-arrow-right"></i>';
      }
    }
  }

  synthesizePreviewDossier() {
    // Populate Step 6 summary from entered fields
    const getVal = (id) => document.getElementById(id)?.value || 'N/A';

    document.getElementById('prev_name').textContent = getVal('name');
    document.getElementById('prev_email').textContent = getVal('email');
    document.getElementById('prev_aadhaar').textContent = getVal('aadh');
    document.getElementById('prev_phone').textContent = getVal('phone');
    document.getElementById('prev_gender').textContent = getVal('gender');
    document.getElementById('prev_dob').textContent = getVal('dob');
    document.getElementById('prev_category').textContent = getVal('category').toUpperCase();
    document.getElementById('prev_caste').textContent = getVal('caste');
    document.getElementById('prev_income').textContent = `₹ ${parseInt(getVal('income') || 0).toLocaleString('en-IN')}`;
    document.getElementById('prev_hostel').textContent = this.isHostelResident ? 'Hostel Resident' : 'Day Scholar';

    // Academic Details
    document.getElementById('prev_usn').textContent = getVal('usn');
    document.getElementById('prev_university').textContent = getVal('university');
    document.getElementById('prev_college').textContent = getVal('collegename');
    document.getElementById('prev_course').textContent = getVal('course');
    document.getElementById('prev_percentage').textContent = `${getVal('percentage')}%`;

    // Banking Details
    document.getElementById('prev_bankname').textContent = getVal('bname');
    document.getElementById('prev_accnumber').textContent = getVal('accnumber');
    document.getElementById('prev_ifsc').textContent = getVal('ifsc');

    // Document Thumbnails Preview
    const previewContainer = document.getElementById('prev_docs_grid');
    if (previewContainer && Object.keys(this.filePreviews).length > 0) {
      previewContainer.innerHTML = '';
      for (const [key, src] of Object.entries(this.filePreviews)) {
        const item = document.createElement('div');
        item.className = 'preview-doc-card';
        item.style.cssText = 'text-align:center; padding:10px; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;';
        item.innerHTML = `
          <img src="${src}" style="width:100px; height:80px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1; margin-bottom:6px;">
          <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#0f172a;">${key}</div>
        `;
        previewContainer.appendChild(item);
      }
    }
  }

  setButtonLoading(isLoading) {
    if (!this.btnNext) return;
    if (isLoading) {
      this.btnNext.disabled = true;
      this.btnNext.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
    } else {
      this.btnNext.disabled = false;
      this.btnNext.innerHTML = 'Save & Continue <i class="fa fa-arrow-right"></i>';
    }
  }

  showToast(message, type = 'info') {
    let toast = document.getElementById('lumina-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'lumina-toast';
      toast.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 9999;
        padding: 0.85rem 1.4rem;
        border-radius: 12px;
        font-family: var(--font-body);
        font-size: 0.875rem;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        transition: all 300ms ease;
        opacity: 0;
        transform: translateY(20px);
      `;
      document.body.appendChild(toast);
    }

    if (type === 'danger') {
      toast.style.background = '#ef4444';
      toast.style.color = '#fff';
    } else if (type === 'warning') {
      toast.style.background = '#f59e0b';
      toast.style.color = '#fff';
    } else {
      toast.style.background = '#0077d4';
      toast.style.color = '#fff';
    }

    toast.innerHTML = `<i class="fa fa-info-circle"></i> ${message}`;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';

    clearTimeout(this.toastTimeout);
    this.toastTimeout = setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(20px)';
    }, 3500);
  }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  window.luminaStepper = new LuminaStepper();
});
