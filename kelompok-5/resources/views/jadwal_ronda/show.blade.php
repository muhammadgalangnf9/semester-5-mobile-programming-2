@extends('layouts.app')

@section('title', 'Detail Jadwal Ronda - SIPEKAT')
@section('header', 'Detail Jadwal Ronda')

@section('content')

@php
    $pengguna = Auth::user();

    $bolehKelola = (int) $pengguna->role_id === 2;
@endphp

<div class="max-w-3xl mx-auto">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">
                Detail Jadwal Ronda
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Informasi jadwal ronda rutin mingguan.
            </p>
        </div>

        <a href="{{ route('jadwal_ronda.index') }}"
           class="text-sm text-gray-600 hover:text-gray-900">
            Kembali ke daftar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- JUDUL DAN STATUS --}}
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">
                Informasi Jadwal
            </h3>

            @if ($jadwalRonda->status === 'aktif')
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    Aktif
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                    Nonaktif
                </span>
            @endif
        </div>

        {{-- INFORMASI JADWAL --}}
        <div class="p-6 space-y-5">

            {{-- HARI RONDA --}}
            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Hari Ronda
                </p>

                <p class="font-semibold text-gray-800">
                    @if (count($jadwalRonda->hari ?? []) === 7)
                        Setiap Hari
                    @else
                        {{ implode(', ', $jadwalRonda->hari ?? []) }}
                    @endif
                </p>
            </div>

            {{-- WAKTU RONDA --}}
            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Waktu Ronda
                </p>

                <p class="font-semibold text-gray-800">
                    {{ substr($jadwalRonda->waktu_mulai, 0, 5) }}
                    -
                    {{ substr($jadwalRonda->waktu_selesai, 0, 5) }}
                </p>
            </div>

            {{-- GANG RONDA --}}
            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Gang Ronda
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $jadwalRonda->gang }}
                </p>
            </div>

            {{-- PESERTA RONDA --}}
            <div>
                <p class="text-sm text-gray-500 mb-3">
                    Peserta Ronda
                </p>

                @if ($pesertaTerpilih->isNotEmpty())
                    <div class="space-y-4">
                        @foreach ($pesertaTerpilih->groupBy('gang') as $namaGang => $daftarPeserta)
                            <div class="border border-gray-200 rounded-lg overflow-hidden">

                                <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                                    <p class="text-sm font-semibold text-gray-700">
                                        {{ $namaGang }}
                                    </p>
                                </div>

                                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach ($daftarPeserta as $peserta)
                                        <div class="flex items-center gap-2 text-sm text-gray-800">
                                            <span class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 text-xs font-semibold">
                                                {{ $loop->iteration }}
                                            </span>

                                            <span>
                                                {{ $peserta->nama }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">
                        Belum ada peserta yang dipilih.
                    </p>
                @endif
            </div>

            {{-- KETERANGAN --}}
            <div>
                <p class="text-sm text-gray-500 mb-1">
                    Keterangan
                </p>

                <p class="text-gray-800 whitespace-pre-line">
                    {{ $jadwalRonda->keterangan ?: '-' }}
                </p>
            </div>

            {{-- WAKTU PENCATATAN --}}
            <div class="pt-4 border-t border-gray-100 space-y-4">

                <h4 class="text-sm font-semibold text-gray-700">
                    Informasi Pencatatan
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    {{-- DITERBITKAN PADA --}}
                    <div>
                        <p class="text-sm text-gray-500 mb-1">
                            Diterbitkan Pada
                        </p>

                        <p class="text-sm text-gray-800">
                            {{ $jadwalRonda->diterbitkan_pada
                                ? $jadwalRonda->diterbitkan_pada->translatedFormat('d F Y, H:i')
                                : '-' }}
                        </p>
                    </div>

                    {{-- DINONAKTIFKAN PADA --}}
                    <div>
                        <p class="text-sm text-gray-500 mb-1">
                            Dinonaktifkan Pada
                        </p>

                        <p class="text-sm text-gray-800">
                            {{ $jadwalRonda->dinonaktifkan_pada
                                ? $jadwalRonda->dinonaktifkan_pada->translatedFormat('d F Y, H:i')
                                : '-' }}
                        </p>
                    </div>

                    {{-- DIBUAT --}}
                    <div>
                        <p class="text-sm text-gray-500 mb-1">
                            Dibuat
                        </p>

                        <p class="text-sm text-gray-800">
                            {{ $jadwalRonda->created_at
                                ? $jadwalRonda->created_at->translatedFormat('d F Y, H:i')
                                : '-' }}
                        </p>
                    </div>

                    {{-- TERAKHIR DIPERBARUI --}}
                    <div>
                        <p class="text-sm text-gray-500 mb-1">
                            Terakhir Diperbarui
                        </p>

                        <p class="text-sm text-gray-800">
                            {{ $jadwalRonda->updated_at
                                ? $jadwalRonda->updated_at->translatedFormat('d F Y, H:i')
                                : '-' }}
                        </p>
                    </div>

                </div>
            </div>

        </div>

        {{-- TOMBOL EDIT --}}
        @if ($bolehKelola)
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <a href="{{ route('jadwal_ronda.edit', $jadwalRonda->id) }}"
                   class="px-5 py-2.5 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600 transition">
                    Edit Jadwal
                </a>
            </div>
        @endif

    </div>
</div>

@endsection
