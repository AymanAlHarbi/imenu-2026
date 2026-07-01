@extends('layouts.front', ['title' => __('Sign in')])

@section('content')
<div style="min-height:70vh; display:flex; align-items:center; justify-content:center; padding:48px 20px;">
  <div class="auth-card" style="width:100%; max-width:420px;">

    <div id="step-phone">
      <h2>{{ __('Enter your phone number') }}</h2>
      <input type="tel" id="phoneInput" class="form-control" placeholder="05xxxxxxxx" required>
      <button class="btn btn-primary w-100 mt-3" onclick="AppAuth.submitPhone()">{{ __('Continue') }}</button>
    </div>

    <div id="step-register" style="display:none">
      <h2>{{ __('Welcome, first time here') }}</h2>
      <input type="text" id="nameInput" class="form-control mt-2" placeholder="{{ __('Name') }}" required>
      <input type="email" id="emailInput" class="form-control mt-2" placeholder="{{ __('Email') }} ({{ __('optional') }})">
      <button class="btn btn-primary w-100 mt-3" onclick="AppAuth.submitRegister()">{{ __('Send verification code') }}</button>
    </div>

    <div id="step-otp" style="display:none">
      <h2>{{ __('Verify your phone') }}</h2>
      <input type="text" id="codeInput" class="form-control" maxlength="6" placeholder="{{ __('Verification Code') }}" required>
      <button class="btn btn-primary w-100 mt-3" onclick="AppAuth.submitCode()">{{ __('Verify profile') }}</button>
      <p class="text-center mt-2"><a href="#" onclick="AppAuth.backToPhone(); return false;">{{ __('Change phone number') }}</a></p>
    </div>

    <div id="step-error" class="alert alert-danger mt-3" style="display:none"></div>
  </div>
</div>

<script>
const AppAuth = {
  csrf: document.querySelector('meta[name="csrf-token"]').content,

  headers(){
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': this.csrf
    };
  },

  showStep(id){
    ['step-phone','step-register','step-otp'].forEach(s=>document.getElementById(s).style.display = s===id?'block':'none');
    document.getElementById('step-error').style.display='none';
  },

  showError(msg){
    const e=document.getElementById('step-error');
    e.textContent = typeof msg === 'string' ? msg : JSON.stringify(msg);
    e.style.display='block';
  },

  async submitPhone(){
    const phone = document.getElementById('phoneInput').value;
    try {
      const res = await fetch("{{ route('client.phone.check') }}", {
        method:'POST', headers: this.headers(), body: JSON.stringify({phone})
      });
      const data = await res.json();
      if(!res.ok){ this.showError(data.errMsg || data.message || 'حدث خطأ'); return; }
      if(data.exists){ await this.requestCodeExisting(); }
      else { this.showStep('step-register'); }
    } catch(e){ this.showError('خطأ في الاتصال بالسيرفر: ' + e.message); }
  },

  async requestCodeExisting(){
    try {
      const res = await fetch("{{ route('client.phone.send-code') }}", {
        method:'POST', headers: this.headers()
      });
      const data = await res.json();
      if(!res.ok){ this.showError(data.errMsg || 'حدث خطأ'); return; }
      if(data.skip_otp && data.redirect){ window.location.href = data.redirect; return; }
      if(data.status){ this.showStep('step-otp'); } else { this.showError(data.errMsg); }
    } catch(e){ this.showError('خطأ في الاتصال بالسيرفر: ' + e.message); }
  },

  async submitRegister(){
    const name = document.getElementById('nameInput').value;
    const email = document.getElementById('emailInput').value;
    try {
      const res = await fetch("{{ route('client.phone.register') }}", {
        method:'POST', headers: this.headers(), body: JSON.stringify({name, email: email || null})
      });
      const data = await res.json();
      if(!res.ok){ this.showError(data.errMsg || data.message || 'حدث خطأ'); return; }
      if(data.skip_otp && data.redirect){ window.location.href = data.redirect; return; }
      if(data.status){ this.showStep('step-otp'); } else { this.showError(data.errMsg); }
    } catch(e){ this.showError('خطأ في الاتصال بالسيرفر: ' + e.message); }
  },

  async submitCode(){
    const code = document.getElementById('codeInput').value;
    try {
      const res = await fetch("{{ route('client.phone.verify') }}", {
        method:'POST', headers: this.headers(), body: JSON.stringify({code})
      });
      const data = await res.json();
      if(data.status){ window.location.href = data.redirect; } else { this.showError(data.errMsg); }
    } catch(e){ this.showError('خطأ في الاتصال بالسيرفر: ' + e.message); }
  },

  backToPhone(){ this.showStep('step-phone'); }
};
</script>
@endsection