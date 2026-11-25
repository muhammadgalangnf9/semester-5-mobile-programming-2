@extends('layouts.app')

@section('title', 'Tambah Pengumuman - SIPEKAT')
@section('header', 'Tambah Pengumuman')

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">
        Tambah Pengumuman
    </h2>
    <p class="mt-1 text-sm text-gray-500">
        Masukkan informasi pengumuman yang akan disampaikan.
    </p>
</div>

<div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

    <form action="{{ route('pengumuman.store') }}" method="POST">
        @csrf

        <div class="mb-5">
            <label for="judul" class="mb-2 block text-sm font-medium text-gray-700">
                Judul Pengumuman
            </label>

            <input type="text"
                name="judul"
                id="judul"
                value="{{ old('judul') }}"
                maxlength="150"
                placeholder="Masukkan judul pengumuman"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">

            @error('judul')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-5">
            <label for="isi" class="mb-2 block text-sm font-medium text-gray-700">
                Isi Pengumuman
            </label>

            <textarea name="isi"
                id="isi"
                rows="8"
                placeholder="Tuliskan isi pengumuman..."
                class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('isi') }}</textarea>

            @error('isi')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-5 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
            Pengumuman akan disimpan sebagai draft. Ajukan persetujuan kepada Ketua RT
            setelah menyimpan agar pengumuman dapat diterbitkan.
        </div>

        <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-5">
            <button type="submit"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                Simpan Pengumuman
            </button>

            <a href="{{ route('pengumuman.index') }}"
                class="rounded-lg bg-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-300">
                Batal
            </a>
        </div>

    </form>

</div>

@endsection
