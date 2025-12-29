// Helper to select elements
const qs = (sel) => document.querySelector(sel);

// Forms
const loginForm = qs("#login-form");
const signupForm = qs("#signup-form");

// Login fields
const loginEmail = qs("#login-email");
const loginPassword = qs("#login-password");
const loginEmailError = qs("#login-email-error");
const loginPasswordError = qs("#login-password-error");

// Signup fields
const signupName = qs("#signup-name");
const signupEmail = qs("#signup-email");
const signupPassword = qs("#signup-password");
const signupNameError = qs("#signup-name-error");
const signupEmailError = qs("#signup-email-error");
const signupPasswordError = qs("#signup-password-error");

// Toggle password buttons
const loginTogglePw = qs("#login-toggle-pw");
const signupTogglePw = qs("#signup-toggle-pw");

// Show/hide forms
function showSignupForm() {
  loginForm.classList.add("hidden");
  signupForm.classList.remove("hidden");
}
function showLoginForm() {
  signupForm.classList.add("hidden");
  loginForm.classList.remove("hidden");
}

// Validators
function isValidGmail(email) { return /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email); }
function isValidEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); }
function isValidSignupPassword(pw) { return pw.length >= 6 && /[!@#$%^&*(),.?":{}|<>]/.test(pw); }

// Update visuals
function setValidityVisual(input, isValid) {
  const row = input.closest(".field");
  if (!row) return;
  row.classList.toggle("valid", isValid);
  row.classList.toggle("invalid", !isValid);
}

// Password eye toggle
loginTogglePw.addEventListener("click", () => {
  loginPassword.type = loginPassword.type === "password" ? "text" : "password";
  loginTogglePw.querySelector("i").className =
    loginPassword.type === "password" ? "fa-solid fa-eye" : "fa-solid fa-eye-slash";
});

signupTogglePw.addEventListener("click", () => {
  signupPassword.type = signupPassword.type === "password" ? "text" : "password";
  signupTogglePw.querySelector("i").className =
    signupPassword.type === "password" ? "fa-solid fa-eye" : "fa-solid fa-eye-slash";
});

// Real-time validation
signupName.addEventListener("input", () => {
  if (signupName.value.trim().length < 2) {
    signupNameError.textContent = "Enter your full name.";
    setValidityVisual(signupName, false);
  } else { signupNameError.textContent = ""; setValidityVisual(signupName, true); }
});

signupEmail.addEventListener("input", () => {
  if (!isValidGmail(signupEmail.value.trim())) {
    signupEmailError.textContent = "(example@gmail.com).";
    setValidityVisual(signupEmail, false);
  } else { signupEmailError.textContent = ""; setValidityVisual(signupEmail, true); }
});

signupPassword.addEventListener("input", () => {
  if (!isValidSignupPassword(signupPassword.value)) {
    signupPasswordError.textContent = "Weak password (min 6 chars + special char).";
    setValidityVisual(signupPassword, false);
  } else { signupPasswordError.textContent = ""; setValidityVisual(signupPassword, true); }
});

loginEmail.addEventListener("input", () => {
  if (!isValidEmail(loginEmail.value.trim())) {
    loginEmailError.textContent = "Enter a valid email.";
    setValidityVisual(loginEmail, false);
  } else { loginEmailError.textContent = ""; setValidityVisual(loginEmail, true); }
});

loginPassword.addEventListener("input", () => {
  if (loginPassword.value.length < 6) {
    loginPasswordError.textContent = "Weak password (min 6 chars).";
    setValidityVisual(loginPassword, false);
  } else { loginPasswordError.textContent = ""; setValidityVisual(loginPassword, true); }
});

// Switch forms
qs("#to-signup-wrapper").addEventListener("click", e => { e.preventDefault(); showSignupForm(); signupName.focus(); });
qs("#to-login-wrapper").addEventListener("click", e => { e.preventDefault(); showLoginForm(); loginEmail.focus(); });

// Submit validation
signupForm.addEventListener("submit", e => {
  let ok = true;

  // Validate all fields before submission
  if (signupName.value.trim().length < 2) { signupNameError.textContent = "Enter your full name."; setValidityVisual(signupName, false); ok = false; }
  if (!isValidGmail(signupEmail.value.trim())) { signupEmailError.textContent = "(example@gmail.com)."; setValidityVisual(signupEmail, false); ok = false; }
  if (!isValidSignupPassword(signupPassword.value)) { signupPasswordError.textContent = "Weak password."; setValidityVisual(signupPassword, false); ok = false; }

  if (!ok) {
    e.preventDefault(); // prevent submission if invalid
  }
  // If all valid → form submits normally to PHP backend (signup.php)
});

loginForm.addEventListener("submit", e => {
  let ok = true;

  if (!isValidEmail(loginEmail.value.trim())) { loginEmailError.textContent = "Enter a valid email."; setValidityVisual(loginEmail, false); ok = false; }
  if (loginPassword.value.length < 6) { loginPasswordError.textContent = "Weak password."; setValidityVisual(loginPassword, false); ok = false; }

  if (!ok) {
    e.preventDefault(); // prevent submission if invalid
  }
  // If all valid → form submits normally to PHP backend (login.php)
});
