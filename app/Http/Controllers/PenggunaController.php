<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index()
    {
        $pengguna      = Pengguna::orderBy('created_at', 'desc')->get();
        $totalPengguna = $pengguna->count();
        $totalOwner    = $pengguna->where('role', 'Owner')->count();
        $totalKaryawan = $pengguna->where('role', 'Karyawan')->count();

        return view('pengguna.index', compact(
            'pengguna', 'totalPengguna', 'totalOwner', 'totalKaryawan'
        ));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'username'     => 'required|string|max:100|unique:pengguna,username',
                'email'        => 'nullable|email|max:255',
                'password'     => 'required|string|min:6',
                'role'         => 'required|in:Owner,Karyawan',
                'foto'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $file     = $request->file('foto');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $file->move(public_path('uploads/pengguna'), $filename);
                $fotoPath = 'uploads/pengguna/' . $filename;
            }

            $pengguna = Pengguna::create([
                'nama_lengkap' => $request->nama_lengkap,
                'username'     => $request->username,
                'email'        => $request->email,
                'password'     => Hash::make($request->password),
                'role'         => $request->role,
                'foto'         => $fotoPath,
            ]);

            return response()->json(['success' => true, 'data' => $pengguna]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $pengguna = Pengguna::findOrFail($id);

            $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'username'     => 'required|string|max:100|unique:pengguna,username,' . $id . ',id_user',
                'email'        => 'nullable|email|max:255',
                'password'     => 'nullable|string|min:6',
                'role'         => 'required|in:Owner,Karyawan',
                'foto'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            
            $fotoPath = $pengguna->foto;

            // Kasus 1: User mengupload foto baru
            if ($request->hasFile('foto')) {
                // Hapus file lama dari disk agar tidak menumpuk di server
                if ($pengguna->foto && file_exists(public_path($pengguna->foto))) {
                    unlink(public_path($pengguna->foto));
                }
                $file     = $request->file('foto');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $file->move(public_path('uploads/pengguna'), $filename);
                $fotoPath = 'uploads/pengguna/' . $filename;
            }

            // Kasus 2: User menekan tombol "Hapus Foto" (tanpa upload foto baru)
            if ($request->hapus_foto == '1') {
                if ($pengguna->foto && file_exists(public_path($pengguna->foto))) {
                    unlink(public_path($pengguna->foto));
                }
                // Set null supaya kolom foto di database juga dikosongkan
                $fotoPath = null;
            }

            $data = [
                'nama_lengkap' => $request->nama_lengkap,
                'username'     => $request->username,
                'email'        => $request->email,
                'role'         => $request->role,
                'foto'         => $fotoPath,
            ];

            // Password hanya diupdate jika user mengisinya (tidak wajib saat edit)
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $pengguna->update($data);

            if (session('user')['id_user'] == $id) {

                $sessionUser = session('user');

                $sessionUser['nama_lengkap'] = $pengguna->nama_lengkap;
                $sessionUser['username']     = $pengguna->username;
                $sessionUser['email']        = $pengguna->email;
                $sessionUser['role']         = $pengguna->role;
                $sessionUser['foto'] = $fotoPath;

                // Tulis kembali session yang sudah diperbarui
                session(['user' => $sessionUser]);
            }

            return response()->json(['success' => true, 'data' => $pengguna->fresh()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        // Tidak boleh hapus akun yang sedang login
        if (session('user')['id_user'] == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus akun yang sedang aktif!'
            ]);
        }

        $masihDiproses = Transaksi::where('id_user', $id)
            ->where('status_transaksi', 'Diproses')
            ->exists();

        if ($masihDiproses) {
            return response()->json([
                'success' => false,
                'message' => 'User masih punya transaksi yang sedang berlangsung.'
            ]);
        }

        $pengguna = Pengguna::findOrFail($id);
        if ($pengguna->foto && file_exists(public_path($pengguna->foto))) {
            unlink(public_path($pengguna->foto));
        }
        $pengguna->delete();

        return response()->json(['success' => true]);
    }
}