@extends('layouts.app')

@section('title', 'Edit Jadwal Ronda - SIPEKAT')
@section('header', 'Edit Jadwal Ronda')

@section('content')

@php
    $daftarHari = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Minggu'
    ];

    $hariTerpilih = old('hari', $jadwalRonda->hari ?? []);

    $pesertaTerpilih = array_map(
        'strval',
        old('peserta', $jadwalRonda->peserta ?? [])
    );
@endphp

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">
        Edit Jadwal Ronda
    </h2>
    <p class="mt-1 text-sm text-gray-500">
        Perbarui jadwal ronda mingguan.
    </p>
</div>

<div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

    <form action="{{ route('jadwal_ronda.update', $jadwalRonda->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- HARI RONDA --}}
        <div class="mb-5">

            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <label class="block text-sm font-medium text-gray-700">
                    Hari Ronda
                </label>

                <button
                    type="button"
                    onclick="pilihSetiapHari()"
                    class="rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-200"
                >
                    Setiap Hari
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($daftarHari as $hari)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-3 text-sm text-gray-700 hover:bg-gray-50">
                        <input
                            type="checkbox"
                            name="hari[]"
                            value="{{ $hari }}"
                            class="hari-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            {{ in_array($hari, $hariTerpilih) ? 'checked' : '' }}
                        >

                        {{ $hari }}
                    </label>
                @endforeach
            </div>

            @error('hari')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @error('hari.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- GANG RONDA --}}
        <div class="mb-5">
            <label for="gang" class="mb-2 block text-sm font-medium text-gray-700">
                Gang Ronda <span class="text-red-500">*</span>
            </label>

            <select
                name="gang"
                id="gang"
                onchange="filterPeserta()"
                required
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >

                <option value="Semua Gang"
                    {{ old('gang', $jadwalRonda->gang) === 'Semua Gang' ? 'selected' : '' }}>
                    Semua Gang
                </option>

                @foreach ($daftarGang as $gang)
                    <option
                        value="{{ $gang }}"
                        {{ old('gang', $jadwalRonda->gang) === $gang ? 'selected' : '' }}
                    >
                        {{ $gang }}
                    </option>
                @endforeach

            </select>

            @error('gang')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if ($daftarGang->isEmpty())
                <p class="mt-1 text-sm text-red-600">
                    Belum ada data gang dari pengguna yang dapat dipilih.
                </p>
            @endif
        </div>

        {{-- PESERTA RONDA --}}
        <div class="mb-5">

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">
                    Peserta Ronda <span class="text-red-500">*</span>
                </label>

                <p class="mt-1 text-xs text-gray-500">
                    Pilih warga yang akan mengikuti ronda. Kamu dapat memilih lebih dari satu orang.
                </p>
            </div>

            {{-- DAFTAR NAMA PESERTA --}}
            <div id="daftar_peserta" class="space-y-4">

                @forelse ($daftarPeserta->groupBy('gang') as $namaGang => $daftarWarga)

                    <div
                        class="grup-peserta overflow-hidden rounded-lg border border-gray-200"
                        data-gang="{{ $namaGang }}"
                    >

                        <div class="border-b border-gray-200 bg-gray-50 px-4 py-2.5">
                            <p class="text-sm font-semibold text-gray-700">
                                {{ $namaGang }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-2 p-4 sm:grid-cols-2">

                            @foreach ($daftarWarga as $warga)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-100 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50">

                                    <input
                                        type="checkbox"
                                        name="peserta[]"
                                        value="{{ $warga->id }}"
                                        class="peserta-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        {{ in_array((string) $warga->id, $pesertaTerpilih, true) ? 'checked' : '' }}
                                    >

                                    <span>
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
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @error('peserta.*')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

        </div>

        {{-- WAKTU MULAI --}}
        <div class="mb-5">
            <label for="waktu_mulai" class="mb-2 block text-sm font-medium text-gray-700">
                Waktu Mulai <span class="text-red-500">*</span>
            </label>

            <input
                type="time"
                name="waktu_mulai"
                id="waktu_mulai"
                value="{{ old('waktu_mulai', substr($jadwalRonda->waktu_mulai, 0, 5)) }}"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                required
            >

            @error('waktu_mulai')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- WAKTU SELESAI --}}
        <div class="mb-5">
            <label for="waktu_selesai" class="mb-2 block text-sm font-medium text-gray-700">
                Waktu Selesai <span class="text-red-500">*</span>
            </label>

            <input
                type="time"
                name="waktu_selesai"
                id="waktu_selesai"
                value="{{ old('waktu_selesai', substr($jadwalRonda->waktu_selesai, 0, 5)) }}"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                required
            >

            @error('waktu_selesai')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- KETERANGAN --}}
        <div class="mb-5">
            <label for="keterangan" class="mb-2 block text-sm font-medium text-gray-700">
                Keterangan
            </label>

            <textarea
                name="keterangan"
                id="keterangan"
                rows="4"
                placeholder="Masukkan keterangan tambahan (opsional)"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >{{ old('keterangan', $jadwalRonda->keterangan) }}</textarea>

            @error('keterangan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- TOMBOL --}}
        <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-5">

            <button
                type="submit"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
            >
                Simpan Perubahan
            </button>

            <a
                href="{{ route('jadwal_ronda.show', $jadwalRonda->id) }}"
                class="rounded-lg bg-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-300"
            >
                Batal
            </a>

        </div>

    </form>

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
