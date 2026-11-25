@extends('layouts.app')

@section('title', 'Jadwal Ronda - SIPEKAT')
@section('header', 'Jadwal Ronda')

@section('content')

@php
    $pengguna = Auth::user();

    $bolehKelola = (int) $pengguna->role_id === 2;
@endphp

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Daftar Jadwal Ronda</h2>
        <p class="text-sm text-gray-500 mt-1">
            Jadwal ronda rutin setiap minggu.
        </p>
    </div>

    @if ($bolehKelola)
        <a href="{{ route('jadwal_ronda.create') }}"
           class="inline-flex items-center justify-center px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            <i class="fas fa-plus mr-2"></i>
            Tambah Jadwal
        </a>
    @endif
</div>

@if (session('success'))
    <div class="mb-5 p-4 rounded-lg bg-green-100 text-green-700 text-sm">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-5 p-4 rounded-lg bg-red-100 text-red-700 text-sm">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-5 py-4">No</th>
                    <th class="px-5 py-4">Hari</th>
                    <th class="px-5 py-4">Waktu</th>
                    <th class="px-5 py-4">Gang</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($jadwalRonda as $jadwal)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4">
                            {{ $jadwalRonda->firstItem() + $loop->index }}
                        </td>

                        <td class="px-5 py-4 font-medium text-gray-800">
                            @if (count($jadwal->hari ?? []) === 7)
                                Setiap Hari
                            @else
                                {{ implode(', ', $jadwal->hari ?? []) }}
                            @endif
                        </td>

                        <td class="px-5 py-4 text-gray-600 whitespace-nowrap">
                            {{ substr($jadwal->waktu_mulai, 0, 5) }}
                            -
                            {{ substr($jadwal->waktu_selesai, 0, 5) }}
                        </td>

                        <td class="px-5 py-4 text-gray-600">
                            {{ $jadwal->gang }}
                        </td>

                        <td class="px-5 py-4">
                            @if ($jadwal->status === 'aktif')
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    Aktif
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    Nonaktif
                                </span>
                            @endif
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('jadwal_ronda.show', $jadwal->id) }}"
                                   class="px-3 py-2 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition text-xs font-medium">
                                    Detail
                                </a>

                                @if ($bolehKelola)
                                    <a href="{{ route('jadwal_ronda.edit', $jadwal->id) }}"
                                       class="px-3 py-2 rounded-lg bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition text-xs font-medium">
                                        Edit
                                    </a>

                                    <form action="{{ route('jadwal_ronda.ubah_status', $jadwal->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin mengubah status jadwal ronda ini?')">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="px-3 py-2 rounded-lg {{ $jadwal->status === 'aktif' ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} transition text-xs font-medium">
                                            {{ $jadwal->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-500">
                            Belum ada jadwal ronda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($jadwalRonda->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $jadwalRonda->links() }}
        </div>
    @endif
</div>

@endsection
