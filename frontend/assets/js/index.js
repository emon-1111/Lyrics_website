document.addEventListener("DOMContentLoaded", () => {

  // -------------------
  // Custom Alert Function
  // -------------------
  function showCustomAlert(message) {
    const alertOverlay = document.getElementById('custom-alert');
    const alertMessage = document.getElementById('custom-alert-message');
    const alertOkBtn = document.getElementById('custom-alert-ok');
    
    alertMessage.textContent = message;
    alertOverlay.classList.add('show');
    
    alertOkBtn.onclick = () => {
      alertOverlay.classList.remove('show');
    };
    
    // Close on overlay click
    alertOverlay.onclick = (e) => {
      if (e.target === alertOverlay) {
        alertOverlay.classList.remove('show');
      }
    };
  }

  // Check for URL parameters to show alerts
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('error')) {
    showCustomAlert(decodeURIComponent(urlParams.get('error')));
    window.history.replaceState({}, document.title, window.location.pathname);
  }
  if (urlParams.has('success')) {
    showCustomAlert(decodeURIComponent(urlParams.get('success')));
    window.history.replaceState({}, document.title, window.location.pathname);
  }

  // -------------------
  // Helper
  // -------------------
  const qs = (sel) => document.querySelector(sel);

  // -------------------
  // Forms
  // -------------------
  const loginForm = qs("#login-form");
  const signupForm = qs("#signup-form");

  // Login fields
  const loginEmail = qs("#login-email");
  const loginPassword = qs("#login-password");
  const loginEmailError = qs("#login-email-error");

  // Signup fields
  const signupName = qs("#signup-name");
  const signupEmail = qs("#signup-email");
  const signupPassword = qs("#signup-password");
  const signupNameError = qs("#signup-name-error");
  const signupEmailError = qs("#signup-email-error");
  const signupPasswordError = qs("#signup-password-error");
  const signupGenreError = qs("#signup-genre-error");

  // Toggle buttons
  const loginTogglePw = qs("#login-toggle-pw");
  const signupTogglePw = qs("#signup-toggle-pw");

  // Switch links
  const toSignup = qs("#to-signup-wrapper");
  const toLogin = qs("#to-login-wrapper");

  // -------------------
  // Utility functions
  // -------------------
  function isValidGmail(email) {
    return /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email);
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function isValidSignupPassword(pw) {
    return pw.length >= 6 && /[!@#$%^&*(),.?":{}|<>]/.test(pw);
  }

  function setValidityVisual(input, isValid) {
    if (!input) return;
    const row = input.closest(".field");
    if (!row) return;
    row.classList.toggle("valid", isValid);
    row.classList.toggle("invalid", !isValid);
  }

  function getSelectedGenres() {
    const checkboxes = document.querySelectorAll('input[name="genres[]"]:checked');
    return Array.from(checkboxes).map(cb => cb.value);
  }

  function showSignupForm() {
    loginForm?.classList.add("hidden");
    signupForm?.classList.remove("hidden");
  }

  function showLoginForm() {
    signupForm?.classList.add("hidden");
    loginForm?.classList.remove("hidden");
  }

  // -------------------
  // Form switch
  // -------------------
  toSignup?.addEventListener("click", (e) => {
    e.preventDefault();
    showSignupForm();
    signupName?.focus();
  });

  toLogin?.addEventListener("click", (e) => {
    e.preventDefault();
    showLoginForm();
    loginEmail?.focus();
  });

  // -------------------
  // Password toggles
  // -------------------
  loginTogglePw?.addEventListener("click", () => {
    if (!loginPassword) return;
    loginPassword.type = loginPassword.type === "password" ? "text" : "password";
    const icon = loginTogglePw.querySelector("i");
    if (icon) icon.className = loginPassword.type === "password" ? "fa-solid fa-eye" : "fa-solid fa-eye-slash";
  });

  signupTogglePw?.addEventListener("click", () => {
    if (!signupPassword) return;
    signupPassword.type = signupPassword.type === "password" ? "text" : "password";
    const icon = signupTogglePw.querySelector("i");
    if (icon) icon.className = signupPassword.type === "password" ? "fa-solid fa-eye" : "fa-solid fa-eye-slash";
  });

  // -------------------
  // Real-time validation for SIGNUP only
  // -------------------
  signupName?.addEventListener("input", () => {
    if (signupName.value.trim().length < 2) {
      signupNameError.textContent = "Enter your full name.";
      setValidityVisual(signupName, false);
    } else {
      signupNameError.textContent = "";
      setValidityVisual(signupName, true);
    }
  });

  signupEmail?.addEventListener("input", () => {
    if (!isValidGmail(signupEmail.value.trim())) {
      signupEmailError.textContent = "(example@gmail.com).";
      setValidityVisual(signupEmail, false);
    } else {
      signupEmailError.textContent = "";
      setValidityVisual(signupEmail, true);
    }
  });

  signupPassword?.addEventListener("input", () => {
    if (!isValidSignupPassword(signupPassword.value)) {
      signupPasswordError.textContent = "Weak password (min 6 chars + special char).";
      setValidityVisual(signupPassword, false);
    } else {
      signupPasswordError.textContent = "";
      setValidityVisual(signupPassword, true);
    }
  });

  // -------------------
  // Real-time validation for LOGIN
  // -------------------
  loginEmail?.addEventListener("input", () => {
    if (!isValidEmail(loginEmail.value.trim())) {
      loginEmailError.textContent = "Enter a valid email.";
      setValidityVisual(loginEmail, false);
    } else {
      loginEmailError.textContent = "";
      setValidityVisual(loginEmail, true);
    }
  });

  // -------------------
  // Submit validation
  // -------------------
  signupForm?.addEventListener("submit", (e) => {
    let ok = true;
    if (!signupName || signupName.value.trim().length < 2) { 
      signupNameError.textContent = "Enter your full name."; 
      setValidityVisual(signupName, false); 
      ok = false; 
    }
    if (!signupEmail || !isValidGmail(signupEmail.value.trim())) { 
      signupEmailError.textContent = "(example@gmail.com)."; 
      setValidityVisual(signupEmail, false); 
      ok = false; 
    }
    if (!signupPassword || !isValidSignupPassword(signupPassword.value)) { 
      signupPasswordError.textContent = "Weak password."; 
      setValidityVisual(signupPassword, false); 
      ok = false; 
    }
    
    // Validate genre selection
    const selectedGenres = getSelectedGenres();
    if (selectedGenres.length === 0) {
      signupGenreError.textContent = "Please select at least one music genre.";
      ok = false;
    } else {
      signupGenreError.textContent = "";
    }
    
    if (!ok) e.preventDefault();
  });

  loginForm?.addEventListener("submit", (e) => {
    let ok = true;
    if (!loginEmail || !isValidEmail(loginEmail.value.trim())) { 
      loginEmailError.textContent = "Enter a valid email."; 
      setValidityVisual(loginEmail, false); 
      ok = false; 
    }
    if (!loginPassword || loginPassword.value.length === 0) { 
      ok = false; 
    }
    if (!ok) e.preventDefault();
  });

});