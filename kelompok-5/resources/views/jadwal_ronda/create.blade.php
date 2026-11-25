@extends('layouts.app')

@section('title', 'Tambah Jadwal Ronda - SIPEKAT')
@section('header', 'Tambah Jadwal Ronda')

@section('content')

@php
    $daftarHari = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Minggu',
    ];

    $hariTerpilih = old('hari', []);
    $pesertaTerpilih = array_map('strval', old('peserta', []));
@endphp

<div class="max-w-3xl mx-auto">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                Formulir Jadwal Ronda
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Lengkapi informasi jadwal ronda yang akan dibuat.
            </p>
        </div>

        <form action="{{ route('jadwal_ronda.store') }}" method="POST">
            @csrf

            {{-- HARI RONDA --}}
            <div class="mb-5">

                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <label class="block text-sm font-medium text-gray-700">
                        Hari Ronda <span class="text-red-500">*</span>
                    </label>

                    <button
                        type="button"
                        onclick="pilihSetiapHari()"
                        class="text-xs font-medium text-blue-600 hover:text-blue-800"
                    >
                        Pilih Setiap Hari
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ($daftarHari as $hari)
                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2.5 cursor-pointer hover:bg-gray-50">
                            <input
                                type="checkbox"
                                name="hari[]"
                                value="{{ $hari }}"
                                class="hari-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                {{ in_array($hari, $hariTerpilih) ? 'checked' : '' }}
                            >

                            <span class="text-sm text-gray-700">
                                {{ $hari }}
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('hari')
                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                @enderror

                @error('hari.*')
                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- GANG RONDA --}}
            <div class="mb-5">
                <label for="gang" class="block text-sm font-medium text-gray-700 mb-2">
                    Gang Ronda <span class="text-red-500">*</span>
                </label>

                <select
                    id="gang"
                    name="gang"
                    onchange="filterPeserta()"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                >
                    <option value="Semua Gang"
                        {{ old('gang', 'Semua Gang') === 'Semua Gang' ? 'selected' : '' }}>
                        Semua Gang
                    </option>

                    @foreach ($daftarGang as $gang)
                        <option
                            value="{{ $gang }}"
                            {{ old('gang') === $gang ? 'selected' : '' }}
                        >
                            {{ $gang }}
                        </option>
                    @endforeach
                </select>

                @error('gang')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror

                @if ($daftarGang->isEmpty())
                    <p class="text-sm text-red-500 mt-1">
                        Belum ada data gang pada pengguna.
                    </p>
                @endif
            </div>

            {{-- PESERTA RONDA --}}
            <div class="mb-5">

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">
                        Peserta Ronda <span class="text-red-500">*</span>
                    </label>

                    <p class="text-xs text-gray-500 mt-1">
                        Pilih warga yang akan mengikuti ronda. Kamu dapat memilih lebih dari satu orang.
                    </p>
                </div>

                <div id="daftar_peserta" class="space-y-3">

                    @forelse ($daftarPeserta->groupBy('gang') as $namaGang => $daftarWarga)

                        <div
                            class="grup-peserta overflow-hidden rounded-lg border border-gray-200"
                            data-gang="{{ $namaGang }}"
                        >

                            <div class="bg-gray-50 border-b border-gray-200 px-4 py-2.5">
                                <p class="text-sm font-semibold text-gray-700">
                                    {{ $namaGang }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-4">

                                @foreach ($daftarWarga as $warga)
                                    <label class="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2.5 cursor-pointer hover:bg-gray-50">

                                        <input
                                            type="checkbox"
                                            name="peserta[]"
                                            value="{{ $warga->id }}"
                                            class="peserta-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            {{ in_array((string) $warga->id, $pesertaTerpilih, true) ? 'checked' : '' }}
                                        >

                                        <span class="text-sm text-gray-700">
                                            {{ $warga->nama }}
                                        </span>

                                    </label>
                                @endforeach

                            </div>
                        </div>

                    @empty
                        <div class="rounded-lg border border-gray-200 px-4 py-6 text-center">
                            <p class="text-sm text-gray-500">
                                Belum ada warga aktif yang dapat dipilih sebagai peserta ronda.
                            </p>
                        </div>
                    @endforelse

                </div>

                @error('peserta')
                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                @enderror

                @error('peserta.*')
                    <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                @enderror

            </div>

            {{-- WAKTU MULAI DAN SELESAI --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">

                <div>
                    <label for="waktu_mulai" class="block text-sm font-medium text-gray-700 mb-2">
                        Waktu Mulai <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="time"
                        id="waktu_mulai"
                        name="waktu_mulai"
                        value="{{ old('waktu_mulai') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    >

                    @error('waktu_mulai')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="waktu_selesai" class="block text-sm font-medium text-gray-700 mb-2">
                        Waktu Selesai <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="time"
                        id="waktu_selesai"
                        name="waktu_selesai"
                        value="{{ old('waktu_selesai') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    >

                    @error('waktu_selesai')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- KETERANGAN --}}
            <div class="mb-6">
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                    Keterangan
                </label>

                <textarea
                    id="keterangan"
                    name="keterangan"
                    rows="4"
                    placeholder="Masukkan keterangan tambahan (opsional)"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                >{{ old('keterangan') }}</textarea>

                @error('keterangan')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- TOMBOL --}}
            <div class="flex flex-wrap justify-end gap-3 border-t border-gray-200 pt-5">

                <a
                    href="{{ route('jadwal_ronda.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Simpan Jadwal
                </button>

            </div>

        </form>
    </div>
</div>

<script>
    function pilihSetiapHari() {
        document.querySelectorAll('.hari-checkbox').forEach(function (checkbox) {
            checkbox.checked = true;
        });
    }

    function filterPeserta() {
        const gangDipilih = document.getElementById('gang').value;
        const grupPeserta = document.querySelectorAll('.grup-peserta');

        grupPeserta.forEach(function (grup) {
            const gang = grup.dataset.gang;

            if (gangDipilih === 'Semua Gang' || gang === gangDipilih) {
                grup.classList.remove('hidden');
            } else {
                grup.classList.add('hidden');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        filterPeserta();
    });
</script>

@endsection
