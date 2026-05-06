@extends('layouts.app')

@section('title', 'Pengaturan')
@section('breadcrumb', 'Pengaturan')

@section('content')

{{-- Flash message area (untuk konfirmasi simpan) --}}
<div id="flashMsg" style="display:none;position:fixed;top:20px;right:24px;z-index:2000;
    background:var(--black);color:var(--gold-lt);border:1px solid var(--gold-rim);
    border-radius:10px;padding:12px 20px;font-size:13px;font-weight:600;
    box-shadow:0 8px 24px rgba(0,0,0,.25);transition:opacity .3s ease">
    <i class="bi bi-check-circle-fill"></i> Tarif berhasil disimpan
</div>

<div class="pg-head">
    <div class="pg-title"><i class="bi bi-gear-wide-connected"></i> Pengaturan</div>
    <div class="pg-sub">Kelola tarif sewa dan ketentuan denda keterlambatan</div>
</div>

{{--
    Layout dua kolom:
    kiri  = form tarif (area utama)
    kanan = info sesi + tombol logout
--}}
<div class="pengaturan-grid" style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

    {{-- ══════════════════════════════
         KIRI — Tarif & Ketentuan
    ══════════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Header card informasi --}}
        <div style="background:var(--black);border-radius:var(--r3);padding:20px 24px;
            border:1px solid rgba(201,168,76,.2);position:relative;overflow:hidden">
            <div style="position:absolute;top:0;left:0;right:0;height:2px;
                background:linear-gradient(90deg,var(--gold-dk),var(--gold),var(--gold-lt))"></div>
            <div style="position:absolute;top:-40px;right:-40px;width:150px;height:150px;
                border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.08) 0%,transparent 70%)"></div>
            <div style="font-family:var(--ff-serif);font-style:italic;font-size:22px;
                color:var(--gold-lt);margin-bottom:6px">Tarif & Ketentuan</div>
            <div style="font-size:12px;color:rgba(255,255,255,.4);line-height:1.6">
                Pengaturan tarif berlaku untuk seluruh transaksi baru. Perubahan tidak memengaruhi
                transaksi yang sudah berjalan. Denda dihitung secara otomatis berdasarkan nilai
                di bawah ini setiap kali terjadi keterlambatan pengembalian.
            </div>
        </div>

        {{-- Grup: Denda Keterlambatan --}}
        <div class="card" style="border-top:2px solid #e03434;margin-top:16px">
            <div class="card-head" style="background:rgba(220,52,52,.03)">
                <div>
                    <div class="card-title" style="color:#c0392b"><i class="bi bi-exclamation-triangle-fill"></i> Denda Keterlambatan</div>
                    <div class="card-sub">Dikenakan per hari setelah tanggal jatuh tempo terlewati</div>
                </div>
                <div style="background:rgba(220,52,52,.08);color:#c0392b;border:1px solid rgba(220,52,52,.2);
                    padding:4px 10px;border-radius:20px;font-size:10.5px;font-weight:700">
                    Auto-hitung
                </div>
            </div>
            <div style="padding:20px 22px">

                {{-- Denda per hari --}}
                <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:16px 18px;background:rgba(220,52,52,.04);border-radius:var(--r2);
                    border:1.5px solid rgba(220,52,52,.2);transition:border-color .2s"
                    id="wrap_denda">
                    <div>
                        <div style="font-size:12.5px;font-weight:700;color:#c0392b;margin-bottom:2px">
                            Denda per Hari Keterlambatan
                        </div>
                        <div style="font-size:11px;color:var(--gray-400)">
                            Dihitung otomatis: jumlah hari telat × nominal ini
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                        <span style="font-size:12px;color:var(--gray-400);font-weight:500">Rp</span>
                        <input type="number" name="denda" id="denda"
                            value="{{ $tarif['denda'] ?? 50000 }}"
                            class="tarif-input denda-input"
                            placeholder="50000"
                            min="0" step="1000">
                        <span style="font-size:11px;color:var(--gray-400)">/hari</span>
                    </div>
                </div>

                {{-- Ilustrasi perhitungan denda --}}
                <div id="dendaPreview" style="margin-top:14px;padding:12px 16px;
                    background:rgba(220,52,52,.04);border-radius:var(--r2);
                    border:1px dashed rgba(220,52,52,.25)">
                    <div style="font-size:10px;font-weight:700;color:#c0392b;
                        text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px">
                        Contoh perhitungan
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        <div style="display:flex;justify-content:space-between;font-size:11.5px">
                            <span style="color:var(--gray-500)">Telat 1 hari</span>
                            <span id="denda1" style="font-family:var(--ff-mono);font-weight:600;color:#c0392b">
                                Rp {{ number_format($tarif['denda'] ?? 50000, 0, ',', '.') }}
                            </span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:11.5px">
                            <span style="color:var(--gray-500)">Telat 3 hari</span>
                            <span id="denda3" style="font-family:var(--ff-mono);font-weight:600;color:#c0392b">
                                Rp {{ number_format(($tarif['denda'] ?? 50000) * 3, 0, ',', '.') }}
                            </span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:11.5px">
                            <span style="color:var(--gray-500)">Telat 7 hari</span>
                            <span id="denda7" style="font-family:var(--ff-mono);font-weight:600;color:#c0392b">
                                Rp {{ number_format(($tarif['denda'] ?? 50000) * 7, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Tombol Simpan --}}
        <div style="display:flex;align-items:center;justify-content:flex-end;
            gap:12px;margin-top:6px">
            <button type="button" onclick="resetTarif()" class="btn-white">
                <i class="bi bi-arrow-counterclockwise"></i> Reset ke Terakhir Disimpan
            </button>
            <button type="submit" class="btn-gold" id="btnSimpan"
                style="padding:10px 28px;font-size:13.5px">
                <i class="bi bi-floppy2-fill"></i> Simpan Perubahan Tarif
            </button>
        </div>

        </form>{{-- end #tarifForm --}}
    </div>

    {{-- ══════════════════════════════
         KANAN — Info Sesi & Logout
    ══════════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:14px">

        {{-- Kartu info sesi aktif --}}
        <div class="card gold-top">
            <div class="card-head">
                <div>
                    <div class="card-title"><i class="bi bi-person-badge"></i> Sesi Aktif</div>
                    <div class="card-sub">Informasi akun yang sedang login</div>
                </div>
            </div>
            <div style="padding:18px 20px">
                <div style="display:flex;align-items:center;gap:12px;
                    padding-bottom:14px;border-bottom:1px solid var(--gray-100)">
                    <div style="width:46px;height:46px;border-radius:50%;flex-shrink:0;
                        background:linear-gradient(135deg,var(--gold-dk),var(--gold));
                        border:2px solid var(--gold-md);display:flex;align-items:center;
                        justify-content:center;font-size:18px;font-weight:700;color:var(--black)">
                        {{ strtoupper(substr(session('user')['nama_lengkap'] ?? 'O', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:var(--black)">
                            {{ session('user')['nama_lengkap'] ?? '-' }}
                        </div>
                        <div style="font-size:11px;color:var(--gray-400);margin-top:2px">
                            {{ session('user')['username'] ?? '-' }}
                        </div>
                    </div>
                </div>
                <div style="padding-top:12px">
                    <div style="display:flex;justify-content:space-between;
                        font-size:12px;padding:5px 0;border-bottom:1px solid var(--gray-100)">
                        <span style="color:var(--gray-500)">Role</span>
                        <span style="background:var(--gold-xs);color:var(--gold-dk);
                            border:1px solid var(--gold-md);padding:2px 9px;
                            border-radius:5px;font-size:10.5px;font-weight:700">
                            {{ session('user')['role'] ?? '-' }}
                        </span>
                    </div>
                    <div style="display:flex;justify-content:space-between;
                        font-size:12px;padding:6px 0">
                        <span style="color:var(--gray-500)">Status</span>
                        <span style="color:#1a8050;font-weight:600;font-size:11.5px">
                            ● Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu Danger Zone: Logout --}}
        <div style="background:var(--white);border:1.5px solid rgba(220,52,52,.25);
            border-radius:var(--r3);overflow:hidden;box-shadow:var(--sh-xs)">
            <div style="padding:14px 18px 12px;border-bottom:1px solid rgba(220,52,52,.12);
                background:rgba(220,52,52,.03)">
                <div style="font-size:13px;font-weight:700;color:#c0392b"><i class="bi bi-box-arrow-right"></i> Keluar dari Sistem</div>
                <div style="font-size:11px;color:var(--gray-400);margin-top:3px">
                    Sesi Anda akan diakhiri dan diarahkan ke halaman login
                </div>
            </div>
            <div style="padding:16px 18px">
                <p style="font-size:11.5px;color:var(--gray-500);line-height:1.6;margin-bottom:14px">
                    Pastikan semua transaksi yang sedang berjalan sudah dicatat sebelum keluar.
                    Data yang belum disimpan akan hilang.
                </p>
                <form action="{{ route('logout') }}" method="POST"
                    onsubmit="return confirmLogout()">
                    @csrf
                    <button type="submit" style="width:100%;padding:11px 16px;
                        background:#b71c1c;border:none;border-radius:var(--r2);
                        color:#fff;font-size:13px;font-weight:700;cursor:pointer;
                        transition:all .18s;font-family:var(--ff);
                        box-shadow:0 2px 8px rgba(183,28,28,.25)"
                        onmouseover="this.style.background='#c62828'"
                        onmouseout="this.style.background='#b71c1c'">
                        <i class="bi bi-box-arrow-right"></i> Logout Sekarang
                    </button>
                </form>
            </div>
        </div>

    </div>{{-- end right col --}}

</div>{{-- end grid --}}

<style>
.tarif-input {
    width: 130px;
    padding: 8px 12px;
    border: 1.5px solid var(--gray-200);
    border-radius: var(--r2);
    font-size: 14px;
    font-family: var(--ff-mono);
    font-weight: 700;
    color: var(--gold-dk);
    text-align: right;
    outline: none;
    transition: border-color .18s, box-shadow .18s;
    background: var(--white);
}
.tarif-input:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px var(--gold-xs);
}
.denda-input {
    color: #c0392b;
}
.denda-input:focus {
    border-color: rgba(220,52,52,.5);
    box-shadow: 0 0 0 3px rgba(220,52,52,.08);
}
/* Pengaturan responsive */
@media (max-width: 900px) {
    .pengaturan-grid {
        grid-template-columns: 1fr !important;
    }
}
@media (max-width: 768px) {
    .tarif-input { width: 110px; }
    #wrap_tarif_dasar,
    #wrap_tarif_fullset,
    #wrap_jaminan,
    #wrap_denda {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 10px !important;
    }
}

/* Highlight wrapper saat input di dalamnya fokus */
#wrap_tarif_dasar:focus-within,
#wrap_tarif_fullset:focus-within,
#wrap_jaminan:focus-within {
    border-color: var(--gold-rim) !important;
    background: var(--gold-xs) !important;
}
#wrap_denda:focus-within {
    border-color: rgba(220,52,52,.4) !important;
    background: rgba(220,52,52,.06) !important;
}
</style>

<script>
// ── Simpan nilai awal untuk reset ──
const _initialTarif = {
    tarif_dasar:   {{ $tarif['tarif_dasar'] ?? 150000 }},
    tarif_fullset: {{ $tarif['tarif_fullset'] ?? 650000 }},
    jaminan:       {{ $tarif['jaminan'] ?? 200000 }},
    denda:         {{ $tarif['denda'] ?? 50000 }},
};

// ── Update preview denda & ringkasan sisi kanan secara real-time ──
function updateDendaPreview() {
    const denda = parseInt(document.getElementById('denda').value) || 0;
    const fmt = n => 'Rp ' + n.toLocaleString('id-ID');
    document.getElementById('denda1').textContent = fmt(denda);
    document.getElementById('denda3').textContent = fmt(denda * 3);
    document.getElementById('denda7').textContent = fmt(denda * 7);
}

function updateSummary() {
    const map = {
        'tarif-dasar':   ['tarif_dasar',   '/hari'],
        'full-set':      ['tarif_fullset',  '/set'],
        'jaminan':       ['jaminan',        ''],
        'denda-telat':   ['denda',          '/hari'],
    };
    const fmt = n => 'Rp ' + parseInt(n || 0).toLocaleString('id-ID');
    Object.entries(map).forEach(([slug, [id, suffix]]) => {
        const el = document.getElementById('summary_' + slug);
        if (el) {
            const val = parseInt(document.getElementById(id)?.value) || 0;
            el.textContent = fmt(val) + suffix;
        }
    });
}

// ── Event listeners untuk input ──
document.querySelectorAll('.tarif-input').forEach(input => {
    input.addEventListener('input', () => {
        updateDendaPreview();
        updateSummary();
    });
});

// ── Reset ke nilai terakhir yang disimpan ──
function resetTarif() {
    Object.entries(_initialTarif).forEach(([key, val]) => {
        const el = document.getElementById(key);
        if (el) el.value = val;
    });
    updateDendaPreview();
    updateSummary();
}

// ── Submit form via fetch (tidak perlu reload halaman) ──
document.getElementById('tarifForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('btnSimpan');
    btn.disabled  = true;
    btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Menyimpan…';

    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route("pengaturan.update.tarif") }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Perbarui nilai awal agar reset selanjutnya pakai nilai baru
            Object.assign(_initialTarif, data.tarif);

            // Flash message singkat
            const flash = document.getElementById('flashMsg');
            flash.style.display = 'block';
            flash.style.opacity = '1';
            setTimeout(() => {
                flash.style.opacity = '0';
                setTimeout(() => { flash.style.display = 'none'; }, 300);
            }, 2500);
        } else {
            swalAlert('Gagal menyimpan tarif. Silakan coba lagi.', 'error', 'Gagal');
        }
    })
    .catch(() => swalAlert('Terjadi kesalahan jaringan.', 'error', 'Gagal'))
    .finally(() => {
        btn.disabled    = false;
        btn.innerHTML = '<i class="bi bi-floppy2-fill"></i> Simpan Perubahan Tarif';
    });
});

// ── Inisialisasi preview denda saat halaman dimuat ──
updateDendaPreview();
</script>

@endsection