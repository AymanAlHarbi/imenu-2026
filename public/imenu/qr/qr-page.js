/* iMenu 2026 — منطق صفحة رمز QR ومطبوعات الكوفي (الإصدار الثاني)
   يعتمد على qr-code-styling (MIT) المضمَّن محليًا — لا خدمة خارجية. */
(function () {
  'use strict';
  var root = document.getElementById('iq');
  if (!root || typeof QRCodeStyling === 'undefined') { return; }

  var cfg = JSON.parse(root.getAttribute('data-config'));
  var PRESETS = {
    navy:   { dots: '#16304C', corners: '#16304C' },
    orange: { dots: '#E8952F', corners: '#E8952F' },
    black:  { dots: '#111111', corners: '#111111' },
    dual:   { dots: '#16304C', corners: '#E8952F' }
  };
  var state = { style: cfg.hasLogo ? 'logo' : 'dots', preset: 'navy', custom: null };

  /* ---------- helpers ---------- */
  function $(sel, el) { return (el || root).querySelector(sel); }
  function $$(sel, el) { return Array.prototype.slice.call((el || root).querySelectorAll(sel)); }
  function colors() { return state.custom ? { dots: state.custom, corners: state.custom } : PRESETS[state.preset]; }
  function withSrc(url, src) { return src ? url + (url.indexOf('?') === -1 ? '?' : '&') + 'src=' + src : url; }

  function luminance(hex) {
    var c = [1, 3, 5].map(function (i) {
      var v = parseInt(hex.substr(i, 2), 16) / 255;
      return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
  }
  function contrastOnWhite(hex) { return 1.05 / (luminance(hex) + 0.05); }

  function options(width, type, style, data) {
    var c = colors();
    var logo = style === 'logo' && cfg.hasLogo;
    var classic = style === 'classic';
    var o = {
      width: width, height: width, type: type, data: data, margin: 0,
      qrOptions: { errorCorrectionLevel: logo ? 'H' : 'M' },
      dotsOptions: { color: c.dots, type: classic ? 'square' : 'dots' },
      cornersSquareOptions: { color: c.corners, type: classic ? 'square' : 'extra-rounded' },
      cornersDotOptions: { color: c.corners, type: classic ? 'square' : 'dot' },
      backgroundOptions: { color: '#FFFFFF' }
    };
    if (logo) {
      o.image = cfg.logo;
      o.imageOptions = { crossOrigin: 'anonymous', margin: Math.max(2, Math.round(width * 0.012)), imageSize: 0.2, hideBackgroundDots: true };
    }
    return o;
  }

  /* ---------- instances ---------- */
  var instances = []; // {qr, style, src, width, type}
  function mount(el, width, style, src) {
    var type = 'svg';
    var qr = new QRCodeStyling(options(width, type, style, withSrc(cfg.url, src)));
    qr.append(el);
    instances.push({ qr: qr, el: el, style: style, src: src, width: width, type: type });
    return qr;
  }
  function refresh() {
    instances.forEach(function (it) {
      var style = it.style === 'current' ? state.style : it.style;
      it.qr.update(options(it.width, it.type, style, withSrc(cfg.url, it.src)));
    });
    $$('.iq-style').forEach(function (b) { b.classList.toggle('is-on', b.getAttribute('data-style') === state.style); });
    $$('.iq-swatch').forEach(function (b) {
      var k = b.getAttribute('data-preset');
      b.classList.toggle('is-on', state.custom ? k === 'custom' : k === state.preset);
    });
    var dot = $('.iq-swatch[data-preset="custom"] .dot');
    if (dot) { dot.style.background = state.custom || 'transparent'; dot.classList.toggle('custom', !state.custom); }
    updateTemplateLinks();
  }

  /* ---------- mount everything ---------- */
  $$('[data-qr]').forEach(function (el) {
    mount(el, parseInt(el.getAttribute('data-w') || '300', 10), el.getAttribute('data-qr'), el.getAttribute('data-src') || '');
  });

  /* ---------- style + color pickers ---------- */
  $$('.iq-style').forEach(function (b) {
    b.addEventListener('click', function () { state.style = b.getAttribute('data-style'); refresh(); });
  });
  var hint = $('#iq-color-hint');
  var hintDefault = hint ? hint.innerHTML : '';
  function showHint(err) {
    if (!hint) { return; }
    hint.classList.toggle('is-error', !!err);
    hint.innerHTML = err ? hint.getAttribute('data-icon') + '<span>' + err + '</span>' : hintDefault;
  }
  $$('.iq-swatch').forEach(function (b) {
    var k = b.getAttribute('data-preset');
    if (k === 'custom') {
      var input = $('input[type=color]', b);
      b.addEventListener('click', function (e) { if (e.target !== input) { input.click(); } });
      input.addEventListener('input', function () {
        var hex = input.value;
        if (contrastOnWhite(hex) < 2.3) { showHint(cfg.t.tooLight); return; } // ٢٫٣:١ — البرتقالي #E8952F يمرّ (٢٫٤)، الأصفر والرمادي الفاتح يُرفضان
        showHint(null); state.custom = hex; refresh();
      });
    } else {
      b.addEventListener('click', function () { state.custom = null; state.preset = k; showHint(null); refresh(); });
    }
  });

  /* ---------- downloads ---------- */
  function download(ext) {
    var width = ext === 'svg' ? 1000 : 3000;
    var type = ext === 'svg' ? 'svg' : 'canvas';
    var tmp = new QRCodeStyling(options(width, type, state.style, cfg.url));
    var name = 'qr-' + cfg.slug + (state.style === 'logo' ? '-logo' : '');
    tmp.download({ name: name, extension: ext });
  }
  $$('[data-download]').forEach(function (b) {
    b.addEventListener('click', function () { download(b.getAttribute('data-download')); });
  });

  /* ---------- copy link ---------- */
  var copy = $('#iq-copy');
  if (copy) {
    copy.addEventListener('click', function () {
      var done = function () { var t = copy.querySelector('span'); var old = t.textContent; t.textContent = cfg.t.copied; setTimeout(function () { t.textContent = old; }, 1600); };
      if (navigator.clipboard) { navigator.clipboard.writeText(cfg.url).then(done); }
      else { var ta = document.createElement('textarea'); ta.value = cfg.url; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); done(); }
    });
  }

  /* ---------- template links carry the chosen style/colors ---------- */
  function updateTemplateLinks() {
    var c = colors();
    var q = '?s=' + encodeURIComponent(state.style) + '&c=' + encodeURIComponent(c.dots) + '&c2=' + encodeURIComponent(c.corners);
    $$('[data-print]').forEach(function (a) { a.setAttribute('href', cfg.printBase + '/' + a.getAttribute('data-print') + q); });
  }

  refresh();
})();
