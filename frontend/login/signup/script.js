const qs = sel => document.querySelector(sel);

const loginForm = qs('#login-form');
const signupForm = qs('#signup-form');

const loginEmail = qs('#login-email');
const loginPassword = qs('#login-password');
const loginEmailError = qs('#login-email-error');
const loginPasswordError = qs('#login-password-error');

const signupName = qs('#signup-name');
const signupEmail = qs('#signup-email');
const signupPassword = qs('#signup-password');
const signupNameError = qs('#signup-name-error');
const signupEmailError = qs('#signup-email-error');
const signupPasswordError = qs('#signup-password-error');

const loginTogglePw = qs('#login-toggle-pw');
const signupTogglePw = qs('#signup-toggle-pw');

// show/hide form helpers
function showSignupForm(){
  loginForm.classList.add('hidden');
  signupForm.classList.remove('hidden');
}
function showLoginForm(){
  signupForm.classList.add('hidden');
  loginForm.classList.remove('hidden');
}

// simple Gmail validation for signup
function isValidGmail(email){
  return /^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email);
}

// general email validation for login
function isValidEmail(email){
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// password validation: at least 6 chars and special character
function isValidSignupPassword(pw){
  return pw.length >= 6 && /[!@#$%^&*(),.?":{}|<>]/.test(pw);
}

// update field visuals
function setValidityVisual(row, isValid){
  if(!row) return;
  row.classList.toggle('valid', isValid);
  row.classList.toggle('invalid', !isValid);
}

// Eye toggle
loginTogglePw.addEventListener('click', () => {
  loginPassword.type = loginPassword.type === 'password' ? 'text' : 'password';
  loginTogglePw.textContent = loginPassword.type === 'password' ? '👁️' : '🙈';
});
signupTogglePw.addEventListener('click', () => {
  signupPassword.type = signupPassword.type === 'password' ? 'text' : 'password';
  signupTogglePw.textContent = signupPassword.type === 'password' ? '👁️' : '🙈';
});

// Real-time validation
signupEmail.addEventListener('input', () => {
  const val = signupEmail.value.trim();
  if(val === ''){
    signupEmailError.textContent = '';
    setValidityVisual(signupEmail.parentElement, false);
    return;
  }
  if(!isValidGmail(val)){
    signupEmailError.textContent = '(example@gmail.com).';
    setValidityVisual(signupEmail.parentElement, false);
  } else {
    signupEmailError.textContent = '';
    setValidityVisual(signupEmail.parentElement, true);
  }
});

loginEmail.addEventListener('input', () => {
  const val = loginEmail.value.trim();
  if(val === ''){
    loginEmailError.textContent = '';
    setValidityVisual(loginEmail.parentElement, false);
    return;
  }
  if(!isValidEmail(val)){
    loginEmailError.textContent = 'Enter a valid email.';
    setValidityVisual(loginEmail.parentElement, false);
  } else {
    loginEmailError.textContent = '';
    setValidityVisual(loginEmail.parentElement, true);
  }
});

signupPassword.addEventListener('input', () => {
  const val = signupPassword.value;
  if(val === ''){
    signupPasswordError.textContent = '';
    setValidityVisual(signupPassword.parentElement, false);
    return;
  }
  if(!isValidSignupPassword(val)){
    signupPasswordError.textContent = 'weak password';
    setValidityVisual(signupPassword.parentElement, false);
  } else {
    signupPasswordError.textContent = '';
    setValidityVisual(signupPassword.parentElement, true);
  }
});

loginPassword.addEventListener('input', () => {
  const val = loginPassword.value;
  if(val === ''){
    loginPasswordError.textContent = '';
    setValidityVisual(loginPassword.parentElement, false);
    return;
  }
  if(val.length < 6){
    loginPasswordError.textContent = 'weak password';
    setValidityVisual(loginPassword.parentElement, false);
  } else {
    loginPasswordError.textContent = '';
    setValidityVisual(loginPassword.parentElement, true);
  }
});

signupName.addEventListener('input', () => {
  const val = signupName.value.trim();
  if(val === ''){
    signupNameError.textContent = '';
    setValidityVisual(signupName.parentElement, false);
    return;
  }
  if(val.length < 2){
    signupNameError.textContent = 'Enter your full name.';
    setValidityVisual(signupName.parentElement, false);
  } else {
    signupNameError.textContent = '';
    setValidityVisual(signupName.parentElement, true);
  }
});

// Full clickable wrappers
qs('#to-signup-wrapper').addEventListener('click', (e)=>{
  e.preventDefault();
  showSignupForm();
  signupName.focus();
});
qs('#to-login-wrapper').addEventListener('click', (e)=>{
  e.preventDefault();
  showLoginForm();
  loginEmail.focus();
});

// Form submissions
signupForm.addEventListener('submit', (e) => {
  e.preventDefault();
  const name = signupName.value.trim();
  const email = signupEmail.value.trim();
  const pw = signupPassword.value;
  let ok = true;

  if(name.length < 2){
    signupNameError.textContent = 'Enter your full name.';
    setValidityVisual(signupName.parentElement, false);
    ok = false;
  }
  if(!isValidGmail(email)){
    signupEmailError.textContent = '(example@gmail.com).';
    setValidityVisual(signupEmail.parentElement, false);
    ok = false;
  }
  if(!isValidSignupPassword(pw)){
    signupPasswordError.textContent = 'weak password';
    setValidityVisual(signupPassword.parentElement, false);
    ok = false;
  }
  if(!ok) return;

  alert('Sign up successful — demo only. Replace with backend call.');
  signupForm.reset();
  setValidityVisual(signupName.parentElement, false);
  setValidityVisual(signupEmail.parentElement, false);
  setValidityVisual(signupPassword.parentElement, false);
});

loginForm.addEventListener('submit', (e)=>{
  e.preventDefault();
  const email = loginEmail.value.trim();
  const pw = loginPassword.value;
  let ok = true;

  if(!isValidEmail(email)){
    loginEmailError.textContent = 'Enter a valid email.';
    setValidityVisual(loginEmail.parentElement, false);
    ok = false;
  }
  if(pw.length < 6){
    loginPasswordError.textContent = 'weak password';
    setValidityVisual(loginPassword.parentElement, false);
    ok = false;
  }
  if(!ok) return;

  alert('Login successful — demo only. Replace with backend call.');
  loginForm.reset();
  setValidityVisual(loginEmail.parentElement, false);
  setValidityVisual(loginPassword.parentElement, false);
});
