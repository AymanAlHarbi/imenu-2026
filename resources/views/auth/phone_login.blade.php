@extends('layouts.front', ['title' => __('Sign in'), 'class' => 'imenu-clean-page'])

@section('content')
@php
  $brand = ($resto ?? null) && method_exists($resto, 'getConfig') ? $resto->getConfig('theme_color', '#FA8128') : '#FA8128';
  $brand = $brand ?: '#FA8128';
@endphp
<div dir="rtl" id="imenu-phone-login" style="--brand: {{ $brand }}; min-height:100vh; background:#ECEAE6; display:flex; justify-content:center; font-family:'Cairo',system-ui,sans-serif; color:#1B1B1A;">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    #imenu-phone-login .pl-card{ background:#fff; border:1px solid #E9E7E2; border-radius:18px; box-shadow:0 1px 2px rgba(20,20,18,.05); padding:22px 20px; }
    #imenu-phone-login .pl-title{ font-weight:700; font-size:1.25rem; margin:0 0 4px; }
    #imenu-phone-login .pl-sub{ font-size:.86rem; color:#8A8983; margin:0 0 18px; }
    #imenu-phone-login .pl-label{ display:block; font-weight:600; font-size:.86rem; margin:0 0 7px; color:#1B1B1A; }
    #imenu-phone-login .pl-input{
      width:100%; border:1.5px solid #E9E7E2; border-radius:13px; background:#FBFAF9;
      padding:13px 15px; font-size:1rem; font-family:'Cairo',system-ui,sans-serif; color:#1B1B1A; outline:none;
      transition:border-color .15s, box-shadow .15s;
    }
    #imenu-phone-login .pl-input:focus{ border-color:var(--brand); box-shadow:0 0 0 3px color-mix(in srgb, var(--brand) 18%, transparent); background:#fff; }
    #imenu-phone-login .pl-input::placeholder{ color:#B9B7B1; }
    #imenu-phone-login .pl-tel{ direction:ltr; text-align:center; font-size:1.25rem; font-weight:600; letter-spacing:.06em; }
    #imenu-phone-login .pl-otp{ direction:ltr; text-align:center; font-size:1.6rem; font-weight:700; letter-spacing:.45em; }
    #imenu-phone-login .pl-btn{
      width:100%; border:none; border-radius:14px; background:var(--brand); color:#fff;
      padding:14px; font-size:1.02rem; font-weight:700; font-family:'Cairo',system-ui,sans-serif;
      cursor:pointer; margin-top:14px; transition:filter .15s, opacity .15s;
    }
    #imenu-phone-login .pl-btn:hover{ filter:brightness(.94); }
    #imenu-phone-login .pl-btn:disabled{ opacity:.55; cursor:default; }
    #imenu-phone-login .pl-ghost{
      width:100%; border:none; background:none; color:#8A8983; padding:11px; margin-top:6px;
      font-size:.9rem; font-weight:600; font-family:'Cairo',system-ui,sans-serif; cursor:pointer;
    }
    #imenu-phone-login .pl-ghost:not(:disabled){ color:var(--brand); }
    #imenu-phone-login .pl-link{ display:block; text-align:center; margin-top:12px; font-size:.88rem; color:#8A8983; text-decoration:none; font-weight:600; }
    #imenu-phone-login .pl-error{
      display:none; margin-top:14px; background:#FDEEEC; color:#C0392B; border:1px solid #F5CBC4;
      border-radius:13px; padding:12px 14px; font-size:.88rem; font-weight:600;
    }
    #imenu-phone-login .pl-step{ display:none; }
    #imenu-phone-login .pl-step.active{ display:block; animation:plFade .22s ease; }
    @keyframes plFade{ from{ opacity:0; transform:translateY(6px);} to{ opacity:1; transform:none;} }
    @media (prefers-reduced-motion: reduce){ #imenu-phone-login .pl-step.active{ animation:none; } }
  </style>

  <div style="width:100%; max-width:440px; min-height:100vh; display:flex; flex-direction:column; padding:0 16px;">

    {{-- الهيدر: رجوع للمنيو --}}
    <header style="padding:14px 0 6px; display:flex; align-items:center; gap:11px;">
      <a href="{{ $backUrl ?? url('/') }}" aria-label="{{ __('Go Back') }}" style="width:40px;height:40px;flex:none;background:#fff;border:1px solid #E9E7E2;border-radius:12px;display:grid;place-items:center;color:#1B1B1A;text-decoration:none;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
      </a>
      <span style="font-size:.9rem; color:#8A8983; font-weight:600;">{{ __('Go back to restaurant') }}</span>
    </header>

    <main style="flex:1; display:flex; flex-direction:column; justify-content:center; padding:18px 0 40px;">

      {{-- هوية المطعم --}}
      <div style="text-align:center; margin-bottom:20px;">
        @if(($resto ?? null) && strlen($resto->logom ?? '') > 5)
          <img src="{{ $resto->logom }}" alt="{{ $resto->name }}" style="width:72px;height:72px;border-radius:20px;object-fit:cover;border:1px solid #E9E7E2;background:#fff;">
        @else
          <div style="width:72px;height:72px;border-radius:20px;background:var(--brand);color:#fff;display:inline-grid;place-items:center;font-weight:700;font-size:1.7rem;">
            {{ ($resto ?? null) ? mb_substr($resto->name,0,1) : 'i' }}
          </div>
        @endif
        @if($resto ?? null)
          <div style="font-weight:700; font-size:1.05rem; margin-top:10px;">{{ $resto->name }}</div>
          <div style="font-size:.84rem; color:#8A8983;">{{ __('Sign in to complete your order') }}</div>
        @endif
      </div>

      <div class="pl-card">

        {{-- الخطوة 1: رقم الجوال --}}
        <div id="step-phone" class="pl-step active">
          <h2 class="pl-title">{{ __('Enter your phone number') }}</h2>
          <p class="pl-sub">{{ __('We use your number to sign you in — no password needed') }}</p>
          <label class="pl-label" for="phoneInput">{{ __('Phone') }}</label>
          <input type="tel" id="phoneInput" class="pl-input pl-tel" placeholder="05XXXXXXXX" inputmode="tel" autocomplete="tel" required
                 onkeydown="if(event.key==='Enter'){AppAuth.submitPhone();}">
          <button id="btnPhone" class="pl-btn" onclick="AppAuth.submitPhone()">{{ __('Continue') }}</button>
        </div>

        {{-- الخطوة 2: عميل جديد --}}
        <div id="step-register" class="pl-step">
          <h2 class="pl-title">{{ __('Welcome, first time here') }}</h2>
          <p class="pl-sub">{{ __('Tell us your name and you are all set') }}</p>
          <label class="pl-label" for="nameInput">{{ __('Name') }}</label>
          <input type="text" id="nameInput" class="pl-input" placeholder="{{ __('Name') }}" autocomplete="name" required>
          <div style="height:12px"></div>
          <label class="pl-label" for="emailInput">{{ __('Email') }} <span style="color:#B9B7B1;font-weight:400;">({{ __('optional') }})</span></label>
          <input type="email" id="emailInput" class="pl-input" placeholder="name@email.com" autocomplete="email" style="direction:ltr;text-align:left;">
          <button id="btnRegister" class="pl-btn" onclick="AppAuth.submitRegister()">{{ __('Continue') }}</button>
          <a href="#" class="pl-link" onclick="AppAuth.backToPhone(); return false;">{{ __('Change phone number') }}</a>
        </div>

        {{-- الخطوة 3: رمز التحقق --}}
        <div id="step-otp" class="pl-step">
          <h2 class="pl-title">{{ __('Verify your phone') }}</h2>
          <p class="pl-sub">{{ __('We sent a code to') }} <span id="otpPhoneLabel" dir="ltr" style="font-weight:700;color:#1B1B1A;"></span></p>
                    <p id="otpChannelNote" style="display:none;margin:0 0 12px;padding:10px 14px;border-radius:10px;background:#e7f8ef;color:#128C4B;font-size:14px;font-weight:600;">
            {{ __('The code was sent via WhatsApp this time.') }}
          </p>
          <input type="text" id="codeInput" class="pl-input pl-otp" maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                 placeholder="——————" required
                 onkeydown="if(event.key==='Enter'){AppAuth.submitCode();}">
          <button id="btnVerify" class="pl-btn" onclick="AppAuth.submitCode()">{{ __('Verify profile') }}</button>
          <button id="btnResend" class="pl-ghost" onclick="AppAuth.resendCode()" disabled></button>
          <a href="#" class="pl-link" onclick="AppAuth.backToPhone(); return false;">{{ __('Change phone number') }}</a>
        </div>

        <div id="step-error" class="pl-error"></div>
      </div>
    </main>
  </div>
</div>

<script>
const AppAuth = {
  csrf: document.querySelector('meta[name="csrf-token"]').content,
  resendTimer: null,
  RESEND_SECONDS: 60,

  headers(){
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': this.csrf
    };
  },

  showStep(id){
    ['step-phone','step-register','step-otp'].forEach(s=>document.getElementById(s).classList.toggle('active', s===id));
    document.getElementById('step-error').style.display='none';
    if(id==='step-otp'){ this.startResendCountdown(); document.getElementById('codeInput').focus(); }
  },

  showError(msg){
    const e=document.getElementById('step-error');
    e.textContent = typeof msg === 'string' ? msg : JSON.stringify(msg);
    e.style.display='block';
  },

  setBusy(btnId, busy){
    const b=document.getElementById(btnId);
    if(b){ b.disabled=busy; }
  },

  handleFailure(data){
    if(data && data.use_standard_login && data.login_url){
      window.location.href = data.login_url;
      return;
    }
    this.showError((data && (data.errMsg || data.message)) || 'حدث خطأ');
  },

  async submitPhone(){
    const phone = document.getElementById('phoneInput').value.trim();
    if(!phone){ this.showError('{{ __('Please enter phone number.') }}'); return; }
    this.setBusy('btnPhone', true);
    try {
      const res = await fetch("{{ route('client.phone.check') }}", {
        method:'POST', headers: this.headers(), body: JSON.stringify({phone})
      });
      const data = await res.json();
      if(!res.ok){ this.handleFailure(data); return; }
      document.getElementById('otpPhoneLabel').textContent = data.phone || phone;
      if(data.exists){ await this.requestCode(); }
      else { this.showStep('step-register'); document.getElementById('nameInput').focus(); }
    } catch(e){ this.showError('خطأ في الاتصال بالسيرفر: ' + e.message); }
    finally{ this.setBusy('btnPhone', false); }
  },

  async requestCode(){
    try {
      const res = await fetch("{{ route('client.phone.send-code') }}", {
        method:'POST', headers: this.headers()
      });
      const data = await res.json();
      if(!res.ok){
        if(res.status===429 && document.getElementById('step-otp').classList.contains('active')){
          this.startResendCountdown(data.retry_after || this.RESEND_SECONDS);
        }
        this.handleFailure(data); return;
      }
      if(data.skip_otp && data.redirect){ window.location.href = data.redirect; return; }
      if(data.status){
        this.showStep('step-otp');
        const note = document.getElementById('otpChannelNote');
        note.style.display = (data.channel === 'whatsapp') ? 'block' : 'none';
      } else { this.handleFailure(data); }
      } catch(e){ this.showError('خطأ في الاتصال بالسيرفر: ' + e.message); }
  },

  async resendCode(){
    this.setBusy('btnResend', true);
    document.getElementById('step-error').style.display='none';
    await this.requestCode();
    this.startResendCountdown();
  },

  startResendCountdown(seconds){
    let left = seconds || this.RESEND_SECONDS;
    const btn = document.getElementById('btnResend');
    clearInterval(this.resendTimer);
    const tick = () => {
      if(left > 0){
        btn.disabled = true;
        btn.textContent = '{{ __('Resend code') }} (' + left + ')';
        left--;
      } else {
        clearInterval(this.resendTimer);
        btn.disabled = false;
        btn.textContent = '{{ __('Resend code') }}';
      }
    };
    tick();
    this.resendTimer = setInterval(tick, 1000);
  },

  async submitRegister(){
    const name = document.getElementById('nameInput').value.trim();
    const email = document.getElementById('emailInput').value.trim();
    if(!name){ this.showError('{{ __('Please enter your name.') }}'); return; }
    this.setBusy('btnRegister', true);
    try {
      const res = await fetch("{{ route('client.phone.register') }}", {
        method:'POST', headers: this.headers(), body: JSON.stringify({name, email: email || null})
      });
      const data = await res.json();
      if(!res.ok){ this.handleFailure(data); return; }
      if(data.skip_otp && data.redirect){ window.location.href = data.redirect; return; }
      if(data.status){ this.showStep('step-otp'); } else { this.handleFailure(data); }
    } catch(e){ this.showError('خطأ في الاتصال بالسيرفر: ' + e.message); }
    finally{ this.setBusy('btnRegister', false); }
  },

  async submitCode(){
    const code = document.getElementById('codeInput').value.trim();
    if(!code){ this.showError('{{ __('Enter your verification code') }}'); return; }
    this.setBusy('btnVerify', true);
    try {
      const res = await fetch("{{ route('client.phone.verify') }}", {
        method:'POST', headers: this.headers(), body: JSON.stringify({code})
      });
      const data = await res.json();
      if(data.status){ window.location.href = data.redirect; } else { this.handleFailure(data); }
    } catch(e){ this.showError('خطأ في الاتصال بالسيرفر: ' + e.message); }
    finally{ this.setBusy('btnVerify', false); }
  },

  backToPhone(){
    clearInterval(this.resendTimer);
    document.getElementById('codeInput').value='';
    this.showStep('step-phone');
    document.getElementById('phoneInput').focus();
  }
};
</script>
@endsection