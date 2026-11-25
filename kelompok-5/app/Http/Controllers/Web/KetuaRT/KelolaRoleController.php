<?php

namespace App\Http\Controllers\Web\KetuaRT;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class KelolaRoleController extends Controller
{
    /**
     * ID role bawaan sistem.
     *
     * ID tetap menjadi identitas role meskipun nama_role diubah.
     */
    private const ROLE_BAWAAN = [1, 2, 3, 4];

    /**
     * Menampilkan daftar role.
     */
    public function index()
    {
        $roles = Role::withCount('pengguna')
            ->orderBy('id')
            ->paginate(10);

        return view(
            'ketua_rt.kelola_role.index',
            compact('roles')
        );
    }

    /**
     * Menampilkan detail role.
     */
    public function show(Role $role)
    {
        $role->loadCount('pengguna');

        return view(
            'ketua_rt.kelola_role.show',
            compact('role')
        );
    }

    /**
     * Menampilkan halaman edit role.
     */
    public function edit(Role $role)
    {
        return view(
            'ketua_rt.kelola_role.edit',
            compact('role')
        );
    }

    /**
     * Memperbarui nama dan deskripsi role.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'nama_role' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('role', 'nama_role')
                    ->ignore($role->id),
            ],

            'deskripsi' => [
                'nullable',
                'string',
                'max:255',
            ],
        ], [
            'nama_role.required' => 'Nama role wajib diisi.',
            'nama_role.unique' => 'Nama role sudah digunakan.',
            'nama_role.alpha_dash' => 'Nama role hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'nama_role.max' => 'Nama role maksimal 50 karakter.',
            'deskripsi.max' => 'Deskripsi maksimal 255 karakter.',
        ]);

        // Menyimpan nama role sebelum diperbarui.
        $namaRoleLama = $role->nama_role;

        // Memperbarui data role.
        $role->update($validated);

        // Mencatat aktivitas perubahan role.
        LogAktivitas::create([
            'pengguna_id' => Auth::id(),
            'aktivitas' => 'Memperbarui data role',
            'deskripsi' => "Memperbarui data role {$namaRoleLama}.",
        ]);

        return redirect()
            ->route('ketua-rt.kelola-role.index')
            ->with('success', 'Data role berhasil diperbarui.');
    }
}