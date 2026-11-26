@extends('layouts.app')

@section('title', 'Detail Kegiatan - SIPEKAT')
@section('header', 'Detail Kegiatan')

@section('content')

@php
    $pengguna = Auth::user();

    $bolehKelola = (int) $pengguna->role_id === 2;
@endphp

<div class="max-w-5xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">
                Detail Kegiatan
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Informasi lengkap kegiatan lingkungan RT.
            </p>
        </div>

        <a
            href="{{ route('kegiatan.index') }}"
            class="text-sm text-gray-600 hover:text-gray-900">
            Kembali ke daftar
        </a>
    </div>

    {{-- INFORMASI UTAMA --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- JUDUL DAN STATUS --}}
        <div class="p-6 border-b border-gray-100 flex items-center justify-between gap-3">

            <h3 class="font-semibold text-gray-800">
                Informasi Kegiatan
            </h3>

            @if ($kegiatan->status === 'draft')
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                    Draft
                </span>
            @elseif ($kegiatan->status === 'terbit')
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    Terbit
                </span>
            @elseif ($kegiatan->status === 'selesai')
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                    Selesai
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                    Nonaktif
                </span>
            @endif

        </div>

        <div class="p-6 space-y-6">

            {{-- POSTER --}}
            @if ($kegiatan->link_poster)
                <div>
                    <p class="text-sm text-gray-500 mb-2">
                        Poster Kegiatan
                    </p>

                    <a
                        href="{{ $kegiatan->link_poster }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-800">
                        Lihat Poster Kegiatan
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            @endif

            {{-- NAMA KEGIATAN --}}
            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Nama Kegiatan
                </p>

                <p class="text-lg font-bold text-gray-800">
                    {{ $kegiatan->nama_kegiatan }}
                </p>
            </div>

            {{-- JENIS KEGIATAN --}}
            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Jenis Kegiatan
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $kegiatan->jenis_kegiatan }}
                </p>
            </div>

            {{-- DESKRIPSI --}}
            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Deskripsi Kegiatan
                </p>

                <p class="text-gray-800 whitespace-pre-line">
                    {{ $kegiatan->deskripsi ?: 'Tidak ada deskripsi kegiatan.' }}
                </p>
            </div>

            {{-- TANGGAL KEGIATAN --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div>
                    <p class="text-sm text-gray-500 mb-1">
                        Tanggal dan Waktu Mulai
                    </p>

                    <p class="font-semibold text-gray-800">
                        {{ $kegiatan->tanggal_mulai->translatedFormat('d F Y, H:i') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 mb-1">
                        Tanggal dan Waktu Selesai
                    </p>

                    <p class="font-semibold text-gray-800">
                        {{ $kegiatan->tanggal_selesai->translatedFormat('d F Y, H:i') }}
                    </p>
                </div>

            </div>

            {{-- LOKASI --}}
            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Lokasi
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $kegiatan->lokasi }}
                </p>
            </div>

            {{-- PEMBUAT --}}
            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Dibuat Oleh
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $kegiatan->pengguna->nama ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Diajukan Oleh
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $kegiatan->pengaju?->nama ?? ($kegiatan->status === 'draft' ? 'Belum diajukan' : 'Belum tercatat') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Terakhir Diedit Oleh
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $kegiatan->pengubah?->nama ?? 'Belum tercatat' }}
                </p>
            </div>

            {{-- INFORMASI PENCATATAN --}}
            <div class="pt-5 border-t border-gray-100 space-y-4">

                <h4 class="text-sm font-semibold text-gray-700">
                    Informasi Pencatatan
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div>
                        <p class="text-sm text-gray-500 mb-1">
                            Diterbitkan Pada
                        </p>

                        <p class="text-sm text-gray-800">
                            {{ $kegiatan->diterbitkan_pada
                                ? $kegiatan->diterbitkan_pada->translatedFormat('d F Y, H:i')
                                : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">
                            Dinonaktifkan Pada
                        </p>

                        <p class="text-sm text-gray-800">
                            {{ $kegiatan->dinonaktifkan_pada
                                ? $kegiatan->dinonaktifkan_pada->translatedFormat('d F Y, H:i')
                                : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">
                            Dibuat
                        </p>

                        <p class="text-sm text-gray-800">
                            {{ $kegiatan->created_at
                                ? $kegiatan->created_at->translatedFormat('d F Y, H:i')
                                : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">
                            Terakhir Diperbarui
                        </p>

                        <p class="text-sm text-gray-800">
                            {{ $kegiatan->updated_at
                                ? $kegiatan->updated_at->translatedFormat('d F Y, H:i')
                                : '-' }}
                        </p>
                    </div>

                </div>
            </div>

        </div>

        {{-- TOMBOL EDIT --}}
        @if ($bolehKelola)
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <a
                    href="{{ route('kegiatan.edit', $kegiatan->id) }}"
                    class="px-5 py-2.5 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600 transition">
                    Edit Kegiatan
                </a>
            </div>
        @endif

    </div>

    {{-- PENGELOLAAN PANITIA --}}
    @include('kegiatan.partials.panitia')

    {{-- RENCANA ANGGARAN BIAYA --}}
    @include('kegiatan.partials.rab')

    {{-- TRANSAKSI KEGIATAN --}}
    @include('kegiatan.partials.transaksi')

    {{-- PESERTA KEGIATAN --}}
    @include('kegiatan.partials.peserta')

    {{-- DOKUMENTASI KEGIATAN --}}
    @include('kegiatan.partials.dokumentasi')

</div>

@endsection
