@extends('layouts.app')

@section('title', 'Edit Kegiatan - SIPEKAT')
@section('header', 'Edit Kegiatan')

@section('content')

<div class="max-w-3xl mx-auto">

    {{-- HEADER --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">
            Edit Kegiatan
        </h2>

        <p class="text-sm text-gray-500 mt-1">
            Perbarui informasi kegiatan.
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

    {{-- FORM EDIT --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        <form action="{{ route('kegiatan.update', $kegiatan->id) }}" method="POST">
            @csrf
            @method('PUT')

            @php
                $lokasiLama = old('lokasi', $kegiatan->lokasi);

                $lokasiTermasukGang = $daftarGang->contains($lokasiLama);

                $pilihLokasiLainnya = old('lokasi') === '__lainnya__'
                    || (!$lokasiTermasukGang && !old('lokasi'));
                
                if (old('lokasi') !== null) {
                    $pilihLokasiLainnya = old('lokasi') === '__lainnya__';
                }

                $nilaiLokasiLainnya = old(
                    'lokasi_lainnya',
                    $lokasiTermasukGang ? '' : $kegiatan->lokasi
                );
            @endphp

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
                        value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}"
                        maxlength="150"
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
                                {{ old('jenis_kegiatan', $kegiatan->jenis_kegiatan) === $jenis ? 'selected' : '' }}>
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
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>

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
                            value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai->format('Y-m-d\TH:i')) }}"
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
                            value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai->format('Y-m-d\TH:i')) }}"
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
                                {{ !$pilihLokasiLainnya && $lokasiLama === $gang ? 'selected' : '' }}>
                                {{ $gang }}
                            </option>
                        @endforeach

                        <option value="__lainnya__"
                            {{ $pilihLokasiLainnya ? 'selected' : '' }}>
                            Lainnya
                        </option>

                    </select>

                    <div id="lokasiLainnyaContainer"
                         class="{{ $pilihLokasiLainnya ? '' : 'hidden' }} mt-3">

                        <label for="lokasi_lainnya" class="block text-sm font-medium text-gray-700 mb-2">
                            Lokasi Lainnya <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            name="lokasi_lainnya"
                            id="lokasi_lainnya"
                            value="{{ $nilaiLokasiLainnya }}"
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

                {{-- POSTER --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Poster Kegiatan Saat Ini
                    </label>

                    @if ($kegiatan->link_poster)
                        <div class="mb-4 p-3 rounded-lg bg-gray-50 border border-gray-200">
                            <a
                                href="{{ $kegiatan->link_poster }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium break-all">
                                Lihat Poster Kegiatan
                            </a>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 mb-4">
                            Belum ada poster kegiatan.
                        </p>
                    @endif

                    <label for="link_poster" class="block text-sm font-medium text-gray-700 mb-2">
                        Link Poster Baru
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
                        Kosongkan jika tidak ingin mengubah tautan poster. Jika ingin mengganti, masukkan tautan baru.
                    </p>

                    @error('link_poster')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- INFORMASI STATUS --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status Kegiatan Saat Ini
                    </label>

                    <div class="px-4 py-3 rounded-lg border border-gray-200 bg-gray-50">
                        <span class="text-sm font-medium text-gray-700">
                            {{ ucfirst($kegiatan->status) }}
                        </span>

                        <p class="text-xs text-gray-500 mt-1">
                            Status tidak diubah melalui formulir ini. Gunakan aksi status pada halaman detail kegiatan.
                        </p>
                    </div>
                </div>

            </div>

            {{-- TOMBOL --}}
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-8 pt-5 border-t border-gray-100">

                <a
                    href="{{ route('kegiatan.show', $kegiatan->id) }}"
                    class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium text-center hover:bg-gray-50 transition">
                    Batal
                </a>

                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-lg bg-yellow-500 text-white text-sm font-medium hover:bg-yellow-600 transition">
                    Simpan Perubahan
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

    document.addEventListener('DOMContentLoaded', function () {
        toggleLokasiLainnya();
    });
</script>

@endsection
