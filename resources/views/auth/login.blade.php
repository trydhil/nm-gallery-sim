<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — NM Gallery SIM</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
    --black:#0a0a0a; --black2:#111111; --black3:#1a1a1a; --black4:#242424;
    --gold:#C9A84C; --gold-lt:#e0c06e; --gold-dk:#a07830;
    --gold-xs:rgba(201,168,76,.08); --gold-sm:rgba(201,168,76,.14);
    --gold-md:rgba(201,168,76,.25); --gold-rim:rgba(201,168,76,.28);
    --white:#ffffff; --gray-50:#fafafa; --gray-100:#f4f4f5;
    --gray-200:#e4e4e7; --gray-300:#d4d4d8; --gray-400:#a1a1aa;
    --gray-500:#71717a; --gray-600:#52525b;
    --ff:'Plus Jakarta Sans',sans-serif;
    --ff-serif:'Instrument Serif',serif;
    --ff-mono:'JetBrains Mono',monospace;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{-webkit-font-smoothing:antialiased}
body{font-family:var(--ff);background:var(--black);min-height:100vh;overflow:hidden}
input,button{font-family:var(--ff)}
::selection{background:var(--gold-sm)}

.split-container{display:flex;width:100vw;height:100vh}

/* ── LEFT — Visual Panel 50% ── */
.visual-panel{
    flex:1;position:relative;overflow:hidden;
    display:flex;align-items:flex-end;justify-content:flex-start;
}
.visual-bg{
    position:absolute;inset:0;width:100%;height:100%;
    object-fit:cover;object-position:center;
    transform:scale(1.05);
    animation:subtleZoom 20s ease-in-out infinite alternate;
}
@keyframes subtleZoom{from{transform:scale(1.05) translate(0,0)}to{transform:scale(1.12) translate(1%,-1%)}}

.visual-panel::before{
    content:'';position:absolute;inset:0;
    background:linear-gradient(180deg,rgba(10,10,10,.3) 0%,rgba(10,10,10,.1) 40%,rgba(10,10,10,.5) 80%,rgba(10,10,10,.85) 100%);
    z-index:1;
}
/* Right-side fade toward the form panel */
.visual-panel::after{
    content:'';position:absolute;top:0;right:0;bottom:0;width:80px;
    background:linear-gradient(270deg,rgba(10,10,10,.35) 0%,transparent 100%);
    z-index:1;
}
/* Gold accent line — now on the RIGHT edge of visual panel */
.gold-accent{
    position:absolute;top:0;right:0;bottom:0;width:3px;
    background:linear-gradient(180deg,transparent 0%,var(--gold) 20%,var(--gold-lt) 50%,var(--gold) 80%,transparent 100%);
    opacity:.6;z-index:2;
}
.particles{position:absolute;inset:0;z-index:1;pointer-events:none;overflow:hidden}
.particle{position:absolute;width:3px;height:3px;background:var(--gold);border-radius:50%;opacity:0;animation:floatParticle linear infinite}
@keyframes floatParticle{
    0%{opacity:0;transform:translateY(100vh) scale(0)}
    10%{opacity:.6}90%{opacity:.4}
    100%{opacity:0;transform:translateY(-10vh) scale(1)}
}

/* Floating stat cards — now on LEFT side of visual panel */
.visual-cards{
    position:absolute;top:50%;left:32px;transform:translateY(-50%);
    z-index:3;display:flex;flex-direction:column;gap:12px;
}
.vcard{
    background:rgba(10,10,10,.55);
    backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
    border:1px solid var(--gold-rim);border-radius:12px;
    padding:14px 18px;min-width:160px;
}
.vcard-label{font-size:10px;color:rgba(255,255,255,.4);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:4px}
.vcard-val{font-family:var(--ff-mono);font-size:20px;font-weight:700;color:var(--gold-lt);line-height:1}
.vcard-sub{font-size:11px;color:rgba(255,255,255,.35);margin-top:3px}

/* Corner badge — top LEFT */
.corner-badge{
    position:absolute;top:28px;left:28px;z-index:3;
    background:rgba(10,10,10,.6);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
    border:1px solid var(--gold-rim);border-radius:40px;
    padding:7px 16px;display:flex;align-items:center;gap:8px;
}
.corner-dot{width:7px;height:7px;border-radius:50%;background:#2da66e;box-shadow:0 0 6px #2da66e;animation:pulse 2s ease infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.corner-text{font-size:11px;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:.5px}

/* Bottom brand overlay */
.visual-overlay{position:relative;z-index:3;padding:40px 48px;max-width:480px}
.overlay-brand{font-family:var(--ff-serif);font-style:italic;font-size:38px;color:var(--gold-lt);letter-spacing:.5px;text-shadow:0 0 20px rgba(201,168,76,.3);line-height:1.1;margin-bottom:10px}
.overlay-tagline{font-size:11px;color:rgba(255,255,255,.35);letter-spacing:2.5px;text-transform:uppercase;font-weight:500;margin-bottom:14px}
.overlay-desc{font-size:12.5px;color:rgba(255,255,255,.5);line-height:1.7}

/* ── RIGHT — Form Panel 50% ── */
.form-panel{
    flex:0 0 50%;
    background:var(--white);
    display:flex;flex-direction:column;
    justify-content:center;align-items:center;
    position:relative;z-index:10;
    overflow-y:auto;padding:48px 56px;
}
.form-inner{width:100%;max-width:400px}

/* Brand */
.brand-mark{display:flex;align-items:center;gap:12px;margin-bottom:36px}
.brand-icon{
    width:50px;height:50px;border-radius:10px;
    
    display:flex;align-items:center;justify-content:center;
    font-family:var(--ff-serif);font-size:22px;font-style:italic;
    color:var(--black);font-weight:700;
    
    position:relative;overflow:hidden;
}
.brand-icon img{
    width:100%;
    height:100%;
    object-fit:contain;
    display:block;
}
.brand-icon::after{
    content:'';position:absolute;top:-50%;left:-60%;
    width:50%;height:200%;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);
    transform:skewX(-15deg);animation:iconShimmer 4s infinite;
}
@keyframes iconShimmer{0%,60%{left:-60%}80%{left:130%}100%{left:130%}}
.brand-text{display:flex;flex-direction:column}
.brand-name{font-size:15px;font-weight:700;color:var(--black);letter-spacing:.2px;line-height:1.2}
.brand-sub{font-size:10px;color:var(--gray-400);letter-spacing:1.5px;text-transform:uppercase;font-weight:500;margin-top:2px}

/* Heading */
.form-heading{margin-bottom:28px}
.form-heading h1{font-size:24px;font-weight:700;color:var(--black);letter-spacing:-.3px;line-height:1.2}
.form-heading p{font-size:13px;color:var(--gray-400);margin-top:6px;line-height:1.5}

/* Error */
.error-msg{
    background:#fee2e2;color:#dc2626;padding:12px 16px;border-radius:8px;
    font-size:12.5px;margin-bottom:20px;border-left:3px solid #dc2626;
    display:flex;align-items:center;gap:8px;animation:errorSlide .3s ease;
}
.error-msg::before{content:'⚠';font-size:14px;flex-shrink:0}
@keyframes errorSlide{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}

/* Fields */
.field-group{margin-bottom:16px}
.field-label{display:block;font-size:12px;font-weight:600;color:var(--gray-600);margin-bottom:6px}
.field-wrap{position:relative}
.field-icon{
    position:absolute;left:14px;top:50%;transform:translateY(-50%);
    color:var(--gray-300);pointer-events:none;transition:color .2s;z-index:2;
    display:flex;align-items:center;
}
.field-input{
    width:100%;padding:12px 14px 12px 44px;
    border:1.5px solid var(--gray-200);border-radius:8px;
    font-size:13.5px;color:var(--black);background:var(--gray-50);
    outline:none;transition:all .2s ease;
}
.field-input::placeholder{color:var(--gray-300);font-size:13px}
.field-input:hover{border-color:var(--gray-300)}
.field-input:focus{border-color:var(--gold);background:var(--white);box-shadow:0 0 0 3px var(--gold-xs)}
.field-wrap:focus-within .field-icon{color:var(--gold)}

.pw-toggle{
    position:absolute;right:14px;top:50%;transform:translateY(-50%);
    background:none;border:none;cursor:pointer;color:var(--gray-400);
    padding:0;display:flex;align-items:center;z-index:3;transition:color .2s;
}
.pw-toggle:hover{color:var(--gold)}

.remember-row{display:flex;align-items:center;gap:8px;margin-bottom:20px;margin-top:4px}
.remember-row input[type="checkbox"]{width:15px;height:15px;accent-color:var(--gold);cursor:pointer}
.remember-row label{font-size:12.5px;color:var(--gray-500);cursor:pointer;user-select:none}

/* CTA Button */
.btn-primary{
    width:100%;padding:13px 16px;background:var(--black);
    color:var(--gold-lt);border:none;border-radius:8px;
    font-size:13.5px;font-weight:700;cursor:pointer;
    transition:all .25s ease;position:relative;overflow:hidden;
    letter-spacing:.2px;display:flex;align-items:center;justify-content:center;gap:8px;
}
.btn-primary::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(201,168,76,.1) 0%,transparent 60%)}
.btn-primary::after{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent);transition:left .6s ease}
.btn-primary:hover{background:var(--black3);box-shadow:0 2px 12px rgba(201,168,76,.18);transform:translateY(-1px)}
.btn-primary:hover::after{left:100%}
.btn-primary:active{transform:translateY(0)}
.btn-spinner{display:none;width:14px;height:14px;border:2px solid rgba(224,192,110,.3);border-top-color:var(--gold-lt);border-radius:50%;animation:spin .7s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.btn-primary.loading .btn-text{opacity:.6}
.btn-primary.loading .btn-spinner{display:block}

.divider{display:flex;align-items:center;gap:12px;margin:22px 0}
.divider-line{flex:1;height:1px;background:var(--gray-200)}
.divider-text{font-size:11px;color:var(--gray-400);text-transform:uppercase;letter-spacing:1px;font-weight:500}

.security-badges{display:flex;justify-content:center;gap:20px}
.security-item{display:flex;align-items:center;gap:6px;font-size:11px;color:var(--gray-400)}
.security-dot{width:6px;height:6px;border-radius:50%;background:#2da66e}

.form-footer{position:absolute;bottom:20px;font-size:11px;color:var(--gray-400);text-align:center;width:100%}

/* Responsive */
@media(max-width:900px){
    .visual-panel{display:none}
    .form-panel{flex:1;max-width:none;padding:40px 32px}
    .form-footer{position:relative;bottom:auto;margin-top:32px}
}
@media(max-width:480px){
    .form-panel{padding:32px 24px}
    .form-inner{max-width:none}
}
</style>
</head>
<body>
<div class="split-container">

    <!-- LEFT — Visual Panel -->
    <div class="visual-panel">
        <img
            src="{{ asset('image/login-visual.jpg') }}"
            alt="NM Gallery — Baju Bodo Collection"
            class="visual-bg"
            onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#0a0a0a 0%,#1a1a1a 50%,#2e1e10 100%)';"
        >

        <div class="gold-accent"></div>
        <div class="particles" id="particles"></div>

        <div class="corner-badge">
            <div class="corner-dot"></div>
            <span class="corner-text">Sistem Aktif</span>
        </div>

        <div class="visual-cards">
            <div class="vcard">
                <div class="vcard-label">Koleksi</div>
                <div class="vcard-val">500+</div>
                <div class="vcard-sub">Baju Bodo tersedia</div>
            </div>
            <div class="vcard">
                <div class="vcard-label">Transaksi</div>
                <div class="vcard-val">1.000+</div>
                <div class="vcard-sub">Total penyewaan</div>
            </div>
            <div class="vcard">
                <div class="vcard-label">Uptime</div>
                <div class="vcard-val">24/7</div>
                <div class="vcard-sub">Akses sistem</div>
            </div>
        </div>

        <div class="visual-overlay">
            <div class="overlay-brand">NM Gallery</div>
            <div class="overlay-tagline">Baju Bodo Collection &middot; Bulukumba</div>
            
        </div>
    </div>

    <!-- RIGHT — Login Form -->
    <div class="form-panel">
        <div class="form-inner">

            <div class="brand-mark">
                <div class="brand-icon">
                    <img src="{{ asset('uploads/toko/Gallery.png') }}" alt="NM Gallery">
                </div>
                <div class="brand-text">
                    <div class="brand-name">NM Gallery</div>
                    <div class="brand-sub">Sistem Informasi Manajemen</div>
                </div>
            </div>

            <div class="form-heading">
                <h1>Selamat Datang Kembali</h1>
                <p>Masukkan kredensial Anda untuk mengakses sistem manajemen NM Gallery.</p>
            </div>

            <form id="loginForm" method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="field-group">
                    <label class="field-label" for="username">Username</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <input type="text" id="username" name="username" class="field-input" placeholder="Masukkan username Anda" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="password">Kata Sandi</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="password" name="password" class="field-input" placeholder="Masukkan kata sandi Anda" required autocomplete="current-password" style="padding-right:44px">
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password">
                            <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya selama 30 hari</label>
                </div>

                <button type="submit" class="btn-primary" id="submitBtn">
                    <span class="btn-text">Masuk ke Sistem</span>
                    <div class="btn-spinner"></div>
                </button>

                <div class="divider">
                    <span class="divider-line"></span>
                    <span class="divider-text">Aman &amp; Terenkripsi</span>
                    <span class="divider-line"></span>
                </div>

                <div class="security-badges">
                    <div class="security-item">
                        <span class="security-dot"></span>
                        SSL Secure
                    </div>
                    <div class="security-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--gold)"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Session Protected
                    </div>
                </div>
            </form>
        </div>

        <div class="form-footer">
            &copy; 2025 NM Gallery &middot; Sistem Informasi Manajemen
        </div>
    </div>

</div>

<script>
(function(){
    const c=document.getElementById('particles');
    for(let i=0;i<25;i++){
        const p=document.createElement('div');
        p.className='particle';
        const sz=2+Math.random()*3,l=Math.random()*100;
        p.style.cssText=`width:${sz}px;height:${sz}px;left:${l}%;opacity:${.2+Math.random()*.4};animation-delay:${Math.random()*15}s;animation-duration:${8+Math.random()*12}s`;
        c.appendChild(p);
    }
    const pwToggle=document.getElementById('pwToggle');
    const pwInput=document.getElementById('password');
    const eyeIcon=document.getElementById('eyeIcon');
    let visible=false;
    pwToggle.addEventListener('click',()=>{
        visible=!visible;
        pwInput.type=visible?'text':'password';
        eyeIcon.innerHTML=visible
            ?'<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
            :'<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    });
    const form=document.getElementById('loginForm');
    const btn=document.getElementById('submitBtn');
    form.addEventListener('submit',()=>{
        btn.classList.add('loading');
        btn.disabled=true;
    });
})();
</script>
</body>
</html>