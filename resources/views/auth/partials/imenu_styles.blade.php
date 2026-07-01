{{-- أنماط صفحات الحساب — ثيم "اي منيو" (مشترك بين login / passwords/email / register) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#FAF7F1; --surface:#FFFFFF; --cream-deep:#F1EADD;
    --ink:#13202E; --ink-soft:#4E5A68; --line:#E8E1D4;
    --green-900:#0E2C4A; --green-700:#155083; --green-500:#2C82C4;
    --saffron:#F2A33C; --saffron-deep:#E27D26; --coral:#E1604A;
    --display:"El Messiri", serif;
    --body:"IBM Plex Sans Arabic", system-ui, sans-serif;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:var(--body);background:var(--bg);color:var(--ink);line-height:1.7;-webkit-font-smoothing:antialiased}
  a{color:inherit;text-decoration:none}
  h1,h2,h3{font-family:var(--display);line-height:1.25}
  img{max-width:100%;display:block}

  .page{display:grid;grid-template-columns:1fr 1.05fr;min-height:100vh}

  .showcase{position:relative;background:linear-gradient(160deg,var(--green-700),var(--green-900));color:#fff;padding:clamp(34px,4vw,64px);display:flex;flex-direction:column;justify-content:space-between;overflow:hidden}
  .showcase::before{content:"";position:absolute;width:420px;height:420px;border-radius:50%;background:rgba(242,163,60,.16);top:-140px;inset-inline-end:-120px;filter:blur(8px)}
  .showcase::after{content:"";position:absolute;width:300px;height:300px;border-radius:50%;background:rgba(44,130,196,.22);bottom:-120px;inset-inline-start:-90px;filter:blur(8px)}
  .sc-brand{display:flex;align-items:center;gap:11px;font-family:var(--display);font-weight:700;font-size:1.4rem;position:relative;z-index:2;color:#fff}
  .sc-brand .mark{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:var(--saffron);color:var(--green-900);font-size:1.2rem;font-weight:700;overflow:hidden}
  .sc-brand .mark img{width:100%;height:100%;object-fit:contain}
  .sc-mid{position:relative;z-index:2;margin-block:auto;padding-block:40px}
  .sc-mid h2{font-size:clamp(1.7rem,2.6vw,2.4rem);font-weight:700;margin-bottom:16px;max-width:13em}
  .sc-mid p{color:rgba(255,255,255,.82);font-size:1.08rem;max-width:26em;margin-bottom:30px}
  .sc-list{list-style:none;display:flex;flex-direction:column;gap:16px}
  .sc-list li{display:flex;gap:13px;align-items:flex-start;font-size:1.02rem}
  .sc-list .ck{width:28px;height:28px;border-radius:9px;background:rgba(242,163,60,.18);display:grid;place-items:center;flex-shrink:0;margin-top:2px}
  .sc-list .ck svg{width:16px;height:16px;stroke:var(--saffron)}
  .sc-foot{position:relative;z-index:2;display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.7);font-size:.92rem}
  .sc-foot .stars{color:var(--saffron);letter-spacing:2px}

  .formside{display:flex;flex-direction:column;padding:clamp(28px,3vw,48px) clamp(24px,4vw,72px)}
  .top-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:auto;gap:12px;flex-wrap:wrap}
  .back{display:inline-flex;align-items:center;gap:8px;color:var(--ink-soft);font-weight:500;font-size:.95rem;border:1px solid var(--line);padding:9px 16px;border-radius:999px;transition:.15s}
  .back:hover{border-color:var(--green-700);color:var(--green-700)}
  .have-acc{font-size:.95rem;color:var(--ink-soft)}
  .have-acc a{color:var(--green-700);font-weight:600}

  .form-card{width:100%;max-width:440px;margin-inline:auto;margin-block:auto;padding-block:40px}
  .eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--cream-deep);color:var(--green-700);font-weight:600;font-size:.84rem;padding:6px 14px;border-radius:999px;margin-bottom:18px}
  .eyebrow .dot{width:7px;height:7px;border-radius:50%;background:var(--saffron-deep)}
  .form-card h1{font-size:clamp(1.7rem,3vw,2.2rem);color:var(--green-900);margin-bottom:8px}
  .form-card .sub{color:var(--ink-soft);font-size:1.02rem;margin-bottom:28px}

  .alert{background:#E7F6EC;border:1px solid #BFE6CC;color:#1F8A5B;padding:13px 16px;border-radius:12px;font-size:.95rem;margin-bottom:22px}
  .demo-box{background:#fff;border:1px dashed var(--line);border-radius:14px;padding:16px;margin-bottom:22px}
  .demo-box .dh{font-size:.82rem;color:var(--ink-soft);margin-bottom:10px;text-align:center}
  .demo-grid{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
  .demo-grid button{font-family:var(--body);font-size:.84rem;font-weight:600;border:1.5px solid var(--line);background:#fff;color:var(--ink);border-radius:999px;padding:7px 13px;cursor:pointer;transition:.15s}
  .demo-grid button:hover{border-color:var(--green-700);color:var(--green-700)}

  .social-row{display:flex;gap:12px;margin-bottom:22px}
  .social-btn{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:9px;font-family:var(--body);font-weight:600;font-size:.95rem;color:var(--ink);background:#fff;border:1.5px solid var(--line);border-radius:13px;padding:12px;cursor:pointer;transition:.15s}
  .social-btn:hover{border-color:var(--green-500);background:#fbfdff}
  .social-btn svg,.social-btn img{width:19px;height:19px}
  .divider{display:flex;align-items:center;gap:14px;color:var(--ink-soft);font-size:.85rem;margin-bottom:22px}
  .divider::before,.divider::after{content:"";flex:1;height:1px;background:var(--line)}

  .field{margin-bottom:18px}
  .field label{display:block;font-size:.92rem;font-weight:600;color:var(--ink);margin-bottom:7px}
  .field .control{position:relative;display:flex;align-items:center}
  .field .control .ic{position:absolute;inset-inline-start:15px;display:grid;place-items:center;pointer-events:none}
  .field .control .ic svg{width:19px;height:19px;stroke:var(--ink-soft)}
  .field input{width:100%;font-family:var(--body);font-size:1rem;color:var(--ink);padding:14px 46px 14px 16px;border:1.5px solid var(--line);border-radius:14px;background:#fff;transition:border-color .15s, box-shadow .15s}
  .field input::placeholder{color:#A9B0B8}
  .field input:focus{outline:none;border-color:var(--green-500);box-shadow:0 0 0 4px rgba(44,130,196,.12)}
  .field.has-danger input{border-color:var(--coral)}
  .field .toggle-pass{position:absolute;inset-inline-end:14px;background:none;border:none;cursor:pointer;display:grid;place-items:center;padding:4px}
  .field .toggle-pass svg{width:19px;height:19px;stroke:var(--ink-soft)}
  .field .err{color:var(--coral);font-size:.85rem;margin-top:6px;display:block}

  .row-between{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:10px;flex-wrap:wrap}
  .check{display:flex;align-items:center;gap:9px;cursor:pointer;font-size:.92rem;color:var(--ink-soft);user-select:none;position:relative}
  .check input{position:absolute;opacity:0;width:0;height:0}
  .check .box{width:20px;height:20px;border-radius:6px;border:1.5px solid var(--line);background:#fff;display:grid;place-items:center;transition:.15s;flex-shrink:0}
  .check .box svg{width:13px;height:13px;stroke:#fff;opacity:0;transition:.15s}
  .check input:checked + .box{background:var(--green-700);border-color:var(--green-700)}
  .check input:checked + .box svg{opacity:1}
  .link-soft{color:var(--green-700);font-weight:600;font-size:.92rem}
  .terms-check{margin-bottom:8px;align-items:flex-start}
  .terms-check .box{margin-top:2px}
  .terms-check span a{color:var(--green-700);font-weight:600;text-decoration:underline}

  .btn-submit{width:100%;display:inline-flex;align-items:center;justify-content:center;gap:10px;font-family:var(--body);font-weight:600;font-size:1.06rem;padding:16px 26px;border-radius:14px;cursor:pointer;border:none;background:linear-gradient(180deg,#F4AE4A,var(--saffron-deep));color:#2A170A;box-shadow:0 8px 22px rgba(226,125,38,.32);transition:transform .18s ease, box-shadow .18s ease, opacity .18s;margin-top:6px}
  .btn-submit:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(226,125,38,.42)}
  .btn-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
  .alt-line{text-align:center;margin-top:24px;color:var(--ink-soft);font-size:.95rem}
  .alt-line a{color:var(--green-700);font-weight:600}

  @media(max-width:900px){
    .page{grid-template-columns:1fr}
    .showcase{display:none}
    .have-acc{display:none}
  }
  @media(max-width:520px){ .formside{padding:24px 20px} .social-row{flex-direction:column} }
  :focus-visible{outline:3px solid var(--saffron-deep);outline-offset:2px;border-radius:6px}
</style>
