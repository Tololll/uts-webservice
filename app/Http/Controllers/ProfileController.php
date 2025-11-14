<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Penting untuk mengelola file

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua data profile dan kirim ke view 'index'
        $profiles = Profile::all();
        return view('profiles.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Tampilkan halaman form 'create'
        return view('profiles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Wajib gambar, maks 2MB
        ]);

        // 1. Simpan Gambar
        // Gambar akan disimpan di folder 'storage/app/public/pasfoto'
        $path = $request->file('gambar')->store('public/pasfoto');
        
        // 2. Buat data baru di database
        Profile::create([
            'nama' => $request->nama,
            'gambar' => $path // Simpan path gambarnya
        ]);

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('myprofiles.index')
                         ->with('success', 'Profile berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     * (Kita tidak pakai ini, jadi biarkan kosong atau redirect)
     */
    public function show(Profile $profile)
    {
        // Redirect ke index
        return redirect()->route('myprofiles.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Cari profile berdasarkan ID
        $profile = Profile::findOrFail($id);
        // Tampilkan view 'edit' dan kirim data profile
        return view('profiles.edit', compact('profile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Gambar boleh kosong saat update
        ]);

        $path = $profile->gambar; // Path gambar default (yang lama)

        // Cek jika ada file gambar baru di-upload
        if ($request->hasFile('gambar')) {
            // 1. Hapus gambar lama
            Storage::delete($profile->gambar); 
            // 2. Simpan gambar baru
            $path = $request->file('gambar')->store('public/pasfoto');
        }

        // Update data di database
        $profile->update([
            'nama' => $request->nama,
            'gambar' => $path
        ]);

        return redirect()->route('myprofiles.index')
                         ->with('success', 'Profile berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $profile = Profile::findOrFail($id);
        
        // Hapus file gambar dari storage
        Storage::delete($profile->gambar);
        
        // Hapus data dari database
        $profile->delete();

        return redirect()->route('myprofiles.index')
                         ->with('success', 'Profile berhasil dihapus.');
    }
}