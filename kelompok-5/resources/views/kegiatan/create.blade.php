@extends('layouts.app')

@section('title', 'Tambah Kegiatan - SIPEKAT')
@section('header', 'Tambah Kegiatan')

@section('content')

<div class="max-w-3xl mx-auto">

    {{-- HEADER --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">
            Tambah Kegiatan
        </h2>

        <p class="text-sm text-gray-500 mt-1">
            Masukkan informasi kegiatan yang akan dicatat.
        </p>
    </div>

    {{-- PESAN ERROR --}}
    @if ($errors->any())
        <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
            <p class="font-semibold mb-2">
                Terdapat kesalahan pada input:
            </p>

            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        <form action="{{ route('kegiatan.store') }}" method="POST">
            @csrf

            <div class="space-y-5">

                {{-- NAMA KEGIATAN --}}
                <div>
                    <label for="nama_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Kegiatan <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama_kegiatan"
                        id="nama_kegiatan"
                        value="{{ old('nama_kegiatan') }}"
                        maxlength="150"
                        placeholder="Masukkan nama kegiatan"
                        required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">

                    @error('nama_kegiatan')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- JENIS KEGIATAN --}}
                <div>
                    <label for="jenis_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Kegiatan <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="jenis_kegiatan"
                        id="jenis_kegiatan"
                        required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">

                        <option value="">Pilih Jenis Kegiatan</option>

                        @foreach ([
                            'Sosial',
                            'Keagamaan',
                            'Olahraga',
                            'Kebersihan',
                            'Pendidikan',
                            'Peringatan Hari Besar',
                            'Rapat',
                            'Lainnya'
                        ] as $jenis)
                            <option value="{{ $jenis }}"
                                {{ old('jenis_kegiatan') === $jenis ? 'selected' : '' }}>
                                {{ $jenis }}
                            </option>
                        @endforeach

                    </select>

                    @error('jenis_kegiatan')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DESKRIPSI --}}
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi Kegiatan
                    </label>

                    <textarea
                        name="deskripsi"
                        id="deskripsi"
                        rows="5"
                        placeholder="Jelaskan informasi kegiatan jika diperlukan"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">{{ old('deskripsi') }}</textarea>

                    @error('deskripsi')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- TANGGAL DAN WAKTU --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal dan Waktu Mulai <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="datetime-local"
                            name="tanggal_mulai"
                            id="tanggal_mulai"
                            value="{{ old('tanggal_mulai') }}"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">

                        @error('tanggal_mulai')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal dan Waktu Selesai <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="datetime-local"
                            name="tanggal_selesai"
                            id="tanggal_selesai"
                            value="{{ old('tanggal_selesai') }}"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">

                        @error('tanggal_selesai')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- LOKASI --}}
                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-2">
                        Lokasi Kegiatan <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="lokasi"
                        id="lokasi"
                        required
                        onchange="toggleLokasiLainnya()"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">

                        <option value="">Pilih Lokasi</option>

                        @foreach ($daftarGang as $gang)
                            <option value="{{ $gang }}"
                                {{ old('lokasi') === $gang ? 'selected' : '' }}>
                                {{ $gang }}
                            </option>
                        @endforeach

                        <option value="__lainnya__"
                            {{ old('lokasi') === '__lainnya__' ? 'selected' : '' }}>
                            Lainnya
                        </option>

                    </select>

                    <div id="lokasiLainnyaContainer"
                         class="{{ old('lokasi') === '__lainnya__' ? '' : 'hidden' }} mt-3">

                        <label for="lokasi_lainnya" class="block text-sm font-medium text-gray-700 mb-2">
                            Lokasi Lainnya <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            name="lokasi_lainnya"
                            id="lokasi_lainnya"
                            value="{{ old('lokasi_lainnya') }}"
                            maxlength="150"
                            placeholder="Masukkan lokasi kegiatan"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">

                    </div>

                    @error('lokasi')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror

                    @error('lokasi_lainnya')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- LINK POSTER --}}
                <div>
                    <label for="link_poster" class="block text-sm font-medium text-gray-700 mb-2">
                        Link Poster Kegiatan (Google Drive)
                    </label>

                    <input
                        type="url"
                        name="link_poster"
                        id="link_poster"
                        value="{{ old('link_poster') }}"
                        maxlength="255"
                        placeholder="https://drive.google.com/..."
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">

                    <p class="text-xs text-gray-500 mt-2">
                        Masukkan tautan poster yang tersimpan di Google Drive. Kosongkan jika belum tersedia.
                    </p>

                    @error('link_poster')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- STATUS --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status Kegiatan
                    </label>

                    <div class="px-4 py-3 rounded-lg border border-yellow-200 bg-yellow-50">
                        <span class="text-sm font-medium text-yellow-700">
                            Draft
                        </span>

                        <p class="text-xs text-gray-500 mt-1">
                            Kegiatan akan disimpan sebagai Draft. Terbitkan setelah informasi panitia, khususnya Ketua Pelaksana, sudah lengkap.
                        </p>
                    </div>
                </div>

            </div>

            {{-- TOMBOL --}}
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-8 pt-5 border-t border-gray-100">

                <a
                    href="{{ route('kegiatan.index') }}"
                    class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium text-center hover:bg-gray-50 transition">
                    Batal
                </a>

                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                    Simpan Kegiatan
                </button>

            </div>

        </form>

    </div>

</div>

{{-- JAVASCRIPT LOKASI --}}
<script>
    function toggleLokasiLainnya() {
        const lokasi = document.getElementById('lokasi');
        const container = document.getElementById('lokasiLainnyaContainer');
        const input = document.getElementById('lokasi_lainnya');

        const pilihLainnya = lokasi.value === '__lainnya__';

        container.classList.toggle('hidden', !pilihLainnya);
        input.required = pilihLainnya;

        if (!pilihLainnya) {
            input.value = '';
        }
    }
</script>

@endsection
