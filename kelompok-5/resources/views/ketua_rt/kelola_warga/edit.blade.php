@extends('layouts.app')

@section('title', $isKetuaRT ? 'Edit Ketua RT' : 'Edit Warga')

@section('header', $isKetuaRT ? 'Edit Ketua RT' : 'Edit Warga')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="bg-white rounded-xl shadow-sm border p-6">

        {{-- Header Form --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800">
                {{ $isKetuaRT ? 'Edit Data Ketua RT' : 'Edit Data Warga' }}
            </h3>

            <p class="text-sm text-gray-500 mt-1">
                Perbarui informasi akun {{ $isKetuaRT ? 'Ketua RT' : 'warga' }}.
            </p>
        </div>

        {{-- Form Edit --}}
        <form
            id="form-edit-warga"
            action="{{ route('ketua-rt.kelola-warga.update', $pengguna->id) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Nama --}}
                <div>
                    <label for="nama" class="block text-sm font-medium mb-1">
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="{{ old('nama', $pengguna->nama) }}"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                        required
                    >

                    @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Username --}}
                <div>
                    <label for="username" class="block text-sm font-medium mb-1">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        id="username"
                        value="{{ old('username', $pengguna->username) }}"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                        required
                    >

                    @error('username')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role --}}
                <div>
                    <label for="role_id" class="block text-sm font-medium mb-1">
                        Role
                    </label>

                    @if ($isKetuaRT)
                        {{-- Role Ketua RT tidak dapat diubah --}}
                        <input
                            type="text"
                            value="{{ $pengguna->labelRole() }}"
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100 text-gray-600"
                            disabled
                        >
                    @else
                        <select
                            name="role_id"
                            id="role_id"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                            required
                        >
                            <option value="">Pilih Role</option>

                            @foreach ($roles as $role)
                                <option
                                    value="{{ $role->id }}"
                                    {{ old('role_id', $pengguna->role_id) == $role->id ? 'selected' : '' }}
                                >
                                    {{ ucwords(str_replace('_', ' ', $role->nama_role)) }}
                                </option>
                            @endforeach
                        </select>

                        @error('role_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                {{-- Gang --}}
                <div>
                    <label for="gang" class="block text-sm font-medium mb-1">
                        Gang
                    </label>

                    <select
                        name="gang"
                        id="gang"
                        required
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                    >
                        <option value="">Pilih Gang</option>

                        @foreach ($daftarGang as $gang)
                            <option
                                value="{{ $gang }}"
                                {{ old('gang', $pengguna->gang) == $gang ? 'selected' : '' }}
                            >
                                {{ $gang }}
                            </option>
                        @endforeach
                    </select>

                    @error('gang')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Blok --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">
                        Blok
                    </label>

                    <div class="flex items-start gap-3">
                        <select id="blok_awalan"
                            class="w-1/2 border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                            disabled required>
                            <option value="">Pilih awalan</option>
                        </select>

                        <span class="pt-2">/</span>

                        <input type="text" id="blok_nomor" inputmode="numeric"
                            pattern="[0-9]*" placeholder="Nomor, mis. 04"
                            class="w-1/2 border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                            disabled>
                    </div>

                    @php
                        $blokSaatIni = old('blok', $pengguna->blok ?? '');
                    @endphp
                    <input type="hidden" name="blok" id="blok"
                        value="{{ $blokSaatIni }}">

                    <p class="text-xs text-gray-500 mt-1">
                        Awalan mengikuti gang. Nomor setelah garis miring diketik manual.
                    </p>

                    @error('blok')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jenis Kelamin --}}
                <div>
                    <label for="jenis_kelamin" class="block text-sm font-medium mb-1">
                        Jenis Kelamin
                    </label>

                    <select
                        name="jenis_kelamin"
                        id="jenis_kelamin"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                    >
                        <option value="">Pilih Jenis Kelamin</option>

                        <option
                            value="laki_laki"
                            {{ old('jenis_kelamin', $pengguna->jenis_kelamin) == 'laki_laki' ? 'selected' : '' }}
                        >
                            Laki-laki
                        </option>

                        <option
                            value="perempuan"
                            {{ old('jenis_kelamin', $pengguna->jenis_kelamin) == 'perempuan' ? 'selected' : '' }}
                        >
                            Perempuan
                        </option>
                    </select>

                    @error('jenis_kelamin')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nomor HP --}}
                <div>
                    <label for="no_hp" class="block text-sm font-medium mb-1">
                        Nomor HP
                    </label>

                    <input
                        type="text"
                        name="no_hp"
                        id="no_hp"
                        value="{{ old('no_hp', $pengguna->no_hp) }}"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                    >

                    @error('no_hp')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium mb-1">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $pengguna->email) }}"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                    >

                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="md:col-span-2">
                    <label for="password" class="block text-sm font-medium mb-1">
                        Password Baru
                    </label>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                    >

                    <p class="text-xs text-gray-500 mt-1">
                        Kosongkan jika tidak ingin mengubah password.
                    </p>

                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                @if (!$isKetuaRT)
                    <div>
                        <label for="status" class="block text-sm font-medium mb-1">
                            Status Akun
                        </label>

                        <select
                            name="status"
                            id="status"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
                            required
                        >
                            <option
                                value="aktif"
                                {{ old('status', $pengguna->status) == 'aktif' ? 'selected' : '' }}
                            >
                                Aktif
                            </option>

                            <option
                                value="nonaktif"
                                {{ old('status', $pengguna->status) == 'nonaktif' ? 'selected' : '' }}
                            >
                                Nonaktif
                            </option>
                        </select>

                        @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

            </div>

            {{-- Tombol --}}
            <div class="flex justify-end gap-3 mt-8 pt-5 border-t">

                <a
                    href="{{ route('ketua-rt.kelola-warga.index') }}"
                    class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-50"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                >
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-edit-warga');
        const gang = document.getElementById('gang');
        const awalan = document.getElementById('blok_awalan');
        const nomor = document.getElementById('blok_nomor');
        const blok = document.getElementById('blok');
        const opsiAwalan = {
            'Gang C16/E1': ['C16', 'E1'],
            'Gang E2/E3': ['E2', 'E3'],
            'Gang E4/E5': ['E4', 'E5'],
            'Gang E6': ['E6']
        };
        const nilaiLama = @json($blokSaatIni);
        const bagianBlok = nilaiLama.split('/');

        function perbaruiBlok() {
            const pilihan = opsiAwalan[gang.value] || [];
            const awalanLama = awalan.value;

            awalan.replaceChildren(new Option('Pilih awalan', ''));
            pilihan.forEach(function (item) {
                awalan.add(new Option(item, item));
            });

            awalan.value = pilihan.includes(awalanLama)
                ? awalanLama
                : (pilihan.includes(bagianBlok[0]) ? bagianBlok[0] : '');
            awalan.disabled = pilihan.length === 0;
            nomor.disabled = !awalan.value;
            awalan.required = true;
            nomor.required = Boolean(awalan.value);
            blok.value = awalan.value && nomor.value.trim()
                ? awalan.value + '/' + nomor.value.trim()
                : '';
        }

        if (bagianBlok.length === 2) {
            nomor.value = bagianBlok[1];
        }

        gang.addEventListener('change', function () {
            awalan.value = '';
            nomor.value = '';
            perbaruiBlok();
        });
        awalan.addEventListener('change', perbaruiBlok);
        nomor.addEventListener('input', function () {
            nomor.value = nomor.value.replace(/\D/g, '');
            perbaruiBlok();
        });
        form.addEventListener('submit', perbaruiBlok);
        perbaruiBlok();
    });
</script>

@endsection
