@extends('layouts.app')

@section('title', 'Detail Pengumuman - SIPEKAT')
@section('header', 'Detail Pengumuman')

@section('content')

@php
    $bolehKelola = (int) Auth::user()->role_id === 2;
@endphp

<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Detail Pengumuman
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Informasi lengkap pengumuman.
        </p>
    </div>

    <a href="{{ route('pengumuman.index') }}"
        class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">
        Kembali
    </a>
</div>

<div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

    {{-- Informasi Utama --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-xl font-bold text-gray-800">
                {{ $pengumuman->judul }}
            </h3>

            <p class="mt-2 text-sm text-gray-500">
                Dibuat oleh {{ $pengumuman->pengguna?->nama ?? '-' }}
            </p>
            <p class="mt-1 text-sm text-gray-500">
                Terakhir diedit oleh {{ $pengumuman->pengubah?->nama ?? 'Belum tercatat' }}
            </p>
        </div>

        @if ($pengumuman->status === 'draft')
            <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                Draft
            </span>
        @elseif ($pengumuman->status === 'menunggu_persetujuan')
            <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-medium text-orange-700">
                Menunggu Persetujuan
            </span>
        @elseif ($pengumuman->status === 'terbit')
            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                Disetujui
            </span>
        @elseif ($pengumuman->status === 'ditolak')
            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                Ditolak
            </span>
        @else
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                Nonaktif
            </span>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($pengumuman->catatan_persetujuan)
        <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
            <p class="text-xs font-medium text-gray-500">Catatan Persetujuan</p>
            <p class="mt-1 whitespace-pre-line text-sm text-gray-800">
                {{ $pengumuman->catatan_persetujuan }}
            </p>
        </div>
    @endif

    {{-- Informasi Tanggal --}}
    <div class="mb-6 grid gap-4 border-y border-gray-200 py-4 sm:grid-cols-2">

        <div>
            <p class="text-xs font-medium text-gray-500">
                Tanggal Dibuat
            </p>
            <p class="mt-1 text-sm text-gray-800">
                {{ $pengumuman->created_at?->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-xs font-medium text-gray-500">
                Tanggal Diterbitkan
            </p>
            <p class="mt-1 text-sm text-gray-800">
                {{ $pengumuman->diterbitkan_pada?->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-xs font-medium text-gray-500">
                Tanggal Dinonaktifkan
            </p>
            <p class="mt-1 text-sm text-gray-800">
                {{ $pengumuman->dinonaktifkan_pada?->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-xs font-medium text-gray-500">
                Disetujui Oleh
            </p>
            <p class="mt-1 text-sm text-gray-800">
                {{ $pengumuman->disetujuiOleh?->nama ?? '-' }}
            </p>
        </div>

        <div>
            <p class="text-xs font-medium text-gray-500">
                Terakhir Diperbarui
            </p>
            <p class="mt-1 text-sm text-gray-800">
                {{ $pengumuman->updated_at?->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}
            </p>
        </div>

    </div>

    {{-- Isi Pengumuman --}}
    <div>
        <h4 class="mb-3 font-semibold text-gray-800">
            Isi Pengumuman
        </h4>

        <div class="whitespace-pre-line break-words text-sm leading-7 text-gray-700">{{ $pengumuman->isi }}</div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-5">

        @if ((int) Auth::user()->role_id === 1 && $pengumuman->status === 'menunggu_persetujuan')
            <form action="{{ route('pengumuman.setujui', $pengumuman->id) }}" method="POST" class="w-full space-y-3">
                @csrf
                <textarea name="catatan_persetujuan" rows="2"
                    placeholder="Catatan persetujuan (opsional)"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <button type="submit"
                    class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-700">
                    Setujui dan Terbitkan
                </button>
            </form>
            <form action="{{ route('pengumuman.tolak', $pengumuman->id) }}" method="POST" class="w-full space-y-3">
                @csrf
                <textarea name="catatan_persetujuan" rows="2" required
                    placeholder="Alasan penolakan (wajib)"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('catatan_persetujuan') }}</textarea>
                @error('catatan_persetujuan')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit"
                    class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-700">
                    Tolak Pengumuman
                </button>
            </form>
        @endif

        @if ($bolehKelola)
            <a href="{{ route('pengumuman.edit', $pengumuman->id) }}"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                Edit Pengumuman
            </a>
        @endif

        <a href="{{ route('pengumuman.index') }}"
            class="rounded-lg bg-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-300">
            Kembali
        </a>

    </div>

</div>

@endsection
