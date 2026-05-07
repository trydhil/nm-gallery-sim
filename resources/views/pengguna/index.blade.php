@extends('layouts.app')

@section('title', 'Kelola User')
@section('breadcrumb', 'Kelola User')

@section('content')

<style>
.user-page { max-width: 1200px; }

/* ── Stat strip ── */
.user-stat-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 22px; }
.user-stat-card {
    background: #fff; border: 1px solid var(--gray-200);
    border-radius: var(--r3); padding: 16px 20px;
    box-shadow: var(--sh-xs); transition: box-shadow .2s, border-color .2s;
}
.user-stat-card:hover { box-shadow: var(--sh-sm); border-color: var(--gray-300); }

/* ── Grid kartu user ── */
.user-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
}
.user-card {
    background: #fff; border: 1.5px solid var(--gray-200);
    border-radius: var(--r3); overflow: hidden;
    transition: border-color .18s, box-shadow .18s, transform .18s;
    position: relative;
}
.user-card:hover { border-color: var(--gold-rim); box-shadow: var(--sh-md); transform: translateY(-2px); }

/* Foto / avatar area */
.user-card-foto {
    height: 100px; display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #faf5e8, #f5edd6); position: relative; overflow: hidden;
}
.user-card-foto img { width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; }
.user-card-ava {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, var(--gold-dk), var(--gold));
    border: 2.5px solid var(--gold-md);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 700; color: var(--black);
    flex-shrink: 0; z-index: 1;
}
.user-card-body { padding: 12px 14px; }
.user-card-name { font-size: 13px; font-weight: 700; color: var(--black); margin-bottom: 2px; }
.user-card-username { font-size: 11px; color: var(--gray-400); margin-bottom: 8px; }
.user-role-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 9px; border-radius: 5px; font-size: 10.5px; font-weight: 700;
}
.role-owner   { background: var(--gold-xs); color: var(--gold-dk); border: 1px solid var(--gold-md); }
.role-karyawan{ background: rgba(45,166,110,.08); color: #1a8050; border: 1px solid rgba(45,166,110,.2); }
.user-card-actions { display: flex; gap: 6px; margin-top: 11px; }
.btn-card-edit, .btn-card-del {
    flex: 1; padding: 6px 8px; border-radius: 7px; font-size: 11.5px;
    font-weight: 600; cursor: pointer; border: 1px solid; transition: .14s;
    font-family: var(--ff); text-align: center;
}
.btn-card-edit { background: var(--gold-xs); border-color: var(--gold-md); color: var(--gold-dk); }
.btn-card-edit:hover { background: var(--gold-sm); border-color: var(--gold); }
.btn-card-del  { background: rgba(220,52,52,.07); border-color: rgba(220,52,52,.25); color: #c0392b; }
.btn-card-del:hover  { background: rgba(220,52,52,.14); border-color: #c0392b; }

/* Badge "Anda" */
.badge-saya {
    position: absolute; top: 8px; right: 8px; z-index: 2;
    background: var(--black); color: var(--gold-lt); font-size: 9.5px; font-weight: 700;
    padding: 2px 8px; border-radius: 10px; border: 1px solid var(--gold-rim);
}

/* ── Modal ── */
.modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.6);
    backdrop-filter: blur(4px); z-index: 1000;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; visibility: hidden; transition: all .25s ease; padding: 16px;
}
.modal-overlay.show { opacity: 1; visibility: visible; }
.modal-popup {
    background: #fff; border-radius: 20px; width: 100%; max-width: 700px;
    overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,.25);
    transform: scale(.94) translateY(8px); transition: transform .25s cubic-bezier(.34,1.4,.64,1);
}
.modal-overlay.show .modal-popup { transform: scale(1) translateY(0); }
.modal-head {
    padding: 18px 24px; background: var(--black);
    display: flex; align-items: center; justify-content: space-between;
}
.modal-head-title { font-size: 15px; font-weight: 600; color: var(--gold-lt); }
.modal-head-sub   { font-size: 11px; color: rgba(255,255,255,.35); margin-top: 2px; }
.modal-close {
    width: 30px; height: 30px; border-radius: 8px; border: 1px solid rgba(255,255,255,.15);
    background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,.45); transition: .15s; font-size: 16px;
}
.modal-close:hover { background: rgba(255,255,255,.08); color: rgba(255,255,255,.8); }

/* Dua kolom di dalam modal */
.modal-body { display: grid; grid-template-columns: 1fr 220px; }
.modal-form-col { padding: 22px 24px; display: flex; flex-direction: column; gap: 13px; }
.modal-photo-col {
    padding: 22px 20px; border-left: 1px solid var(--gray-100);
    display: flex; flex-direction: column; align-items: center; gap: 14px;
    background: var(--gray-50);
}

/* Label + input field */
.mf-group { display: flex; flex-direction: column; gap: 5px; }
.mf-label { font-size: 11px; font-weight: 700; color: var(--gray-600); text-transform: uppercase; letter-spacing: .5px; }
.mf-input, .mf-select {
    width: 100%; padding: 9px 12px; border: 1.5px solid var(--gray-200);
    border-radius: var(--r2); font-size: 13px; font-family: var(--ff); color: var(--black);
    outline: none; transition: border-color .18s, box-shadow .18s; background: #fff;
}
.mf-input:focus, .mf-select:focus { border-color: var(--gold); box-shadow: 0 0 0 3px var(--gold-xs); }
.mf-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* Foto preview */
.photo-preview-wrap {
    width: 130px; height: 130px; border-radius: 50%;
    border: 3px solid var(--gold-md); overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, var(--gold-dk), var(--gold));
    position: relative; font-size: 44px; font-weight: 700; color: var(--black);
}
.photo-preview-wrap img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.photo-upload-btn {
    width: 100%; padding: 9px; background: var(--white); border: 1.5px dashed var(--gray-300);
    border-radius: var(--r2); font-size: 12px; color: var(--gray-500); cursor: pointer;
    transition: .15s; text-align: center; font-family: var(--ff);
}
.photo-upload-btn:hover { border-color: var(--gold-rim); color: var(--gold-dk); background: var(--gold-xs); }
.photo-hint { font-size: 10px; color: var(--gray-400); text-align: center; line-height: 1.5; }

.modal-foot {
    padding: 14px 24px; border-top: 1px solid var(--gray-100);
    background: var(--gray-50); display: flex; gap: 10px; justify-content: flex-end;
}

@media (max-width: 768px) {
    .user-stat-grid { grid-template-columns: 1fr; }
    .user-grid { grid-template-columns: repeat(2, 1fr); }
    .modal-body { grid-template-columns: 1fr; }
    .modal-photo-col { border-left: none; border-top: 1px solid var(--gray-100); }
}
</style>

<div class="user-page">

    {{-- ── Header ── --}}
    <div class="pg-head">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <div class="pg-title"><i class="bi bi-people-fill"></i> Kelola User</div>
                <div class="pg-sub">{{ $totalPengguna }} akun terdaftar — Owner &amp; Karyawan</div>
            </div>
            <button class="btn-gold" onclick="openModal('tambah')">
                <i class="bi bi-plus-circle-fill"></i> Tambah User
            </button>
        </div>
    </div>

    {{-- ── Stat strip ── --}}
    <div class="user-stat-grid">
        <div class="user-stat-card" style="border-top:2px solid var(--gold)">
            <div style="font-size:10.5px;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">Total User</div>
            <div style="font-size:28px;font-weight:800;color:var(--black)">{{ $totalPengguna }}</div>
            <div style="font-size:11px;color:var(--gray-400);margin-top:4px">Semua akun aktif</div>
        </div>
        <div class="user-stat-card" style="border-top:2px solid var(--gold)">
            <div style="font-size:10.5px;font-weight:700;color:var(--gold-dk);text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">Owner</div>
            <div style="font-size:28px;font-weight:800;color:var(--black)">{{ $totalOwner }}</div>
            <div style="font-size:11px;color:var(--gray-400);margin-top:4px">Akses laporan &amp; pengaturan</div>
        </div>
        <div class="user-stat-card" style="border-top:2px solid #2da66e">
            <div style="font-size:10.5px;font-weight:700;color:#1a8050;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">Karyawan</div>
            <div style="font-size:28px;font-weight:800;color:var(--black)">{{ $totalKaryawan }}</div>
            <div style="font-size:11px;color:var(--gray-400);margin-top:4px">Akses transaksi &amp; inventaris</div>
        </div>
    </div>

    {{-- ── Grid user ── --}}
    <div class="user-grid" id="userGrid">
        @forelse($pengguna as $u)
        @php $isSelf = session('user')['id_user'] == $u->id_user; @endphp
        <div class="user-card">
            @if($isSelf)
            <div class="badge-saya">Anda</div>
            @endif
            <div class="user-card-foto">
                @if($u->foto)
                <img src="/{{ $u->foto }}" alt="{{ $u->nama_lengkap }}">
                @else
                <div class="user-card-ava">{{ strtoupper(substr($u->nama_lengkap,0,1)) }}</div>
                @endif
            </div>
            <div class="user-card-body">
                <div class="user-card-name">{{ $u->nama_lengkap }}</div>
                <div class="user-card-username">{{ $u->username }}</div>
                @if($u->email)
                <div style="font-size:10px;color:var(--gray-400);margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $u->email }}</div>
                @endif
                <span class="user-role-badge {{ $u->role === 'Owner' ? 'role-owner' : 'role-karyawan' }}">
                    <i class="bi {{ $u->role === 'Owner' ? 'bi-star-fill' : 'bi-person-fill' }}" style="font-size:9px"></i>
                    {{ $u->role }}
                </span>
                <div class="user-card-actions">
                    <button class="btn-card-edit" onclick="openModal('edit',
                        {{ $u->id_user }},
                        '{{ addslashes($u->nama_lengkap) }}',
                        '{{ $u->username }}',
                        '{{ $u->email ?? '' }}',
                        '{{ $u->role }}',
                        '{{ $u->foto ?? '' }}'
                    )">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </button>
                    @if(!$isSelf)
                    <button class="btn-card-del" onclick="hapusUser({{ $u->id_user }}, '{{ addslashes($u->nama_lengkap) }}')">
                        <i class="bi bi-trash3-fill"></i> Hapus
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div style="grid-column:1/-1;padding:60px;text-align:center;color:var(--gray-400)">
            <i class="bi bi-people" style="font-size:36px;opacity:.3;display:block;margin-bottom:12px"></i>
            Belum ada user terdaftar
        </div>
        @endforelse
    </div>

</div>

{{-- ══════ MODAL TAMBAH / EDIT USER ══════ --}}
<div class="modal-overlay" id="userModal">
    <div class="modal-popup">
        <div class="modal-head">
            <div>
                <div class="modal-head-title" id="modalTitle">Tambah User Baru</div>
                <div class="modal-head-sub" id="modalSub">Isi semua data user dengan lengkap</div>
            </div>
            <button class="modal-close" onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
        </div>

        <form id="userForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="fMethod" name="_method" value="POST">
            <input type="hidden" id="fUserId" name="user_id" value="">
            <input type="hidden" id="fHapusFoto" name="hapus_foto" value="0">

            <div class="modal-body">
                {{-- ── Kiri: Form ── --}}
                <div class="modal-form-col">
                    <div class="mf-group">
                        <label class="mf-label">Nama Lengkap *</label>
                        <input class="mf-input" type="text" id="fNama" name="nama_lengkap" placeholder="Nama lengkap" required>
                    </div>
                    <div class="mf-grid2">
                        <div class="mf-group">
                            <label class="mf-label">Username *</label>
                            <input class="mf-input" type="text" id="fUsername" name="username" placeholder="username" required>
                        </div>
                        <div class="mf-group">
                            <label class="mf-label">Role *</label>
                            <select class="mf-select" id="fRole" name="role" required>
                                <option value="Owner">Owner</option>
                                <option value="Karyawan">Karyawan</option>
                            </select>
                        </div>
                    </div>
                    <div class="mf-group">
                        <label class="mf-label">Email / Gmail</label>
                        <input class="mf-input" type="email" id="fEmail" name="email" placeholder="contoh@gmail.com">
                    </div>
                    <div class="mf-group">
                        <label class="mf-label" id="passLabel">Password *</label>
                        <input class="mf-input" type="password" id="fPassword" name="password" placeholder="Minimal 6 karakter">
                        <span id="passHint" style="font-size:10px;color:var(--gray-400);display:none">
                            Kosongkan jika tidak ingin mengubah password
                        </span>
                    </div>
                </div>

                {{-- ── Kanan: Upload Foto ── --}}
                <div class="modal-photo-col">
                    <div style="font-size:11px;font-weight:700;color:var(--gray-600);text-transform:uppercase;letter-spacing:.5px;align-self:flex-start">Foto Profil</div>
                    <div class="photo-preview-wrap" id="photoPreviewWrap">
                        <img id="photoPreviewImg" src="" alt="" style="display:none">
                        <span id="photoPreviewInitial" style="z-index:1">?</span>
                    </div>
                    <input type="file" id="fFotoFile" name="foto" accept="image/jpeg,image/png,image/jpg" style="display:none" onchange="previewFoto(this)">
                    <button type="button" class="photo-upload-btn" onclick="document.getElementById('fFotoFile').click()">
                        <i class="bi bi-cloud-arrow-up"></i> Upload Foto
                    </button>
                    <button type="button" class="photo-upload-btn" id="btnHapusFoto" onclick="hapusFotoPreview()" style="display:none;border-color:rgba(220,52,52,.3);color:#c0392b">
                        <i class="bi bi-trash3"></i> Hapus Foto
                    </button>
                    <div class="photo-hint">JPG / PNG<br>Maks. 2 MB<br>Disarankan persegi</div>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn-white" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-gold" id="btnSimpanUser">
                    <i class="bi bi-floppy2-fill"></i> Simpan User
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Toast --}}
<div id="userToast" style="
    display:none;position:fixed;top:20px;right:24px;z-index:3000;
    background:var(--black);color:var(--gold-lt);
    border:1px solid var(--gold-rim);border-radius:10px;
    padding:12px 18px;font-size:12.5px;font-weight:600;
    box-shadow:0 8px 24px rgba(0,0,0,.25);max-width:280px">
</div>

<script>
const CSRF = '{{ csrf_token() }}';
let currentEditId = null;

// ── Buka modal ──
function openModal(mode, id, nama, username, email, role, foto) {
    currentEditId = mode === 'edit' ? id : null;
    const isEdit  = mode === 'edit';

    document.getElementById('modalTitle').textContent = isEdit ? 'Edit User' : 'Tambah User Baru';
    document.getElementById('modalSub').textContent   = isEdit ? 'Perbarui data user' : 'Isi semua data dengan lengkap';
    document.getElementById('fMethod').value          = isEdit ? 'PUT' : 'POST';
    document.getElementById('fUserId').value          = id || '';
    document.getElementById('fNama').value            = nama || '';
    document.getElementById('fUsername').value        = username || '';
    document.getElementById('fEmail').value           = email || '';
    document.getElementById('fRole').value            = role || 'Karyawan';
    document.getElementById('fPassword').value        = '';
    document.getElementById('fHapusFoto').value       = '0';

    // Password hint
    document.getElementById('passLabel').textContent  = isEdit ? 'Password' : 'Password *';
    document.getElementById('passHint').style.display = isEdit ? 'block' : 'none';
    document.getElementById('fPassword').required     = !isEdit;

    // Foto preview
    setPhotoPreview(foto || '', nama || '?');
    document.getElementById('fFotoFile').value = '';
    document.getElementById('btnHapusFoto').style.display = (isEdit && foto) ? 'block' : 'none';

    document.getElementById('userModal').classList.add('show');
}

function closeModal() { document.getElementById('userModal').classList.remove('show'); }

// ── Preview foto saat file dipilih ──
function previewFoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('photoPreviewImg').src = e.target.result;
        document.getElementById('photoPreviewImg').style.display = 'block';
        document.getElementById('photoPreviewInitial').style.display = 'none';
        document.getElementById('btnHapusFoto').style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
    document.getElementById('fHapusFoto').value = '0';
}

function setPhotoPreview(fotoUrl, namaOrInisial) {
    const img  = document.getElementById('photoPreviewImg');
    const init = document.getElementById('photoPreviewInitial');
    if (fotoUrl) {
        img.src = '/' + fotoUrl;
        img.style.display = 'block';
        init.style.display = 'none';
    } else {
        img.style.display = 'none';
        init.style.display = 'block';
        init.textContent = (namaOrInisial || '?').charAt(0).toUpperCase();
    }
}

function hapusFotoPreview() {
    document.getElementById('photoPreviewImg').style.display = 'none';
    document.getElementById('photoPreviewInitial').style.display = 'block';
    document.getElementById('fHapusFoto').value = '1';
    document.getElementById('fFotoFile').value  = '';
    document.getElementById('btnHapusFoto').style.display = 'none';
}

// ── Submit form (tambah/edit) ──
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSimpanUser');
    btn.disabled = true;
    btn.innerHTML = '⏳ Menyimpan…';

    const formData = new FormData(this);
    const isEdit   = currentEditId !== null;
    const url      = isEdit ? '/pengguna/' + currentEditId : '/pengguna';

    // Laravel method spoofing untuk PUT
    if (isEdit) formData.set('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal();
            showToast(isEdit ? '✅ User berhasil diperbarui' : '✅ User baru berhasil ditambahkan');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast('❌ ' + (data.message || 'Gagal menyimpan'));
        }
    })
    .catch(() => showToast('❌ Terjadi kesalahan jaringan'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy2-fill"></i> Simpan User';
    });
});

// ── Hapus user ──
function hapusUser(id, nama) {
    swalConfirm('Hapus akun "' + nama + '"?', {
        title: 'Hapus akun',
        confirmButtonText: 'Hapus',
        confirmButtonColor: '#c0392b',
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch('/pengguna/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN'    : CSRF,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept'          : 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('🗑️ User berhasil dihapus');
                setTimeout(() => location.reload(), 1000);
            } else {
                swalAlert(data.message || 'Gagal menghapus', 'error', 'Gagal');
            }
        })
        .catch(() => swalAlert('Terjadi kesalahan jaringan', 'error', 'Gagal menghapus'));
    });
}

// ── Toast notifikasi singkat ──
function showToast(msg) {
    swalToast(msg);
}

// Tutup modal saat klik di luar
document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>

@endsection