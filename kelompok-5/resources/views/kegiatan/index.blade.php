@extends('layouts.app')

@section('title', 'Kegiatan - SIPEKAT')
@section('header', 'Kegiatan')

@section('content')

@php
    $pengguna = Auth::user();

    $bolehKelola = (int) $pengguna->role_id === 2;
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">
                Daftar Kegiatan
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Informasi kegiatan dan aktivitas lingkungan RT.
            </p>
        </div>

        @if ($bolehKelola)
            <a
                href="{{ route('kegiatan.create') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                Tambah Kegiatan
            </a>
        @endif
    </div>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- DAFTAR KEGIATAN --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="p-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">
                Semua Kegiatan
            </h3>

            <p class="text-xs text-gray-500 mt-1">
                Daftar kegiatan yang tercatat dalam sistem.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">

                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Kegiatan</th>
                        <th class="px-5 py-3 font-semibold">Tanggal</th>
                        <th class="px-5 py-3 font-semibold">Lokasi</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Pengelola</th>
                        <th class="px-5 py-3 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($kegiatan as $item)

                        <tr class="hover:bg-gray-50 transition">

                            {{-- KEGIATAN --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3 min-w-56">

                                    @if ($item->link_poster)
                                        <a
                                            href="{{ $item->link_poster }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="w-12 h-12 shrink-0 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs font-medium hover:bg-blue-100 transition">
                                            Poster
                                        </a>
                                    @else
                                        <div class="w-12 h-12 shrink-0 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs border border-gray-200">
                                            Foto
                                        </div>
                                    @endif

                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            {{ $item->nama_kegiatan }}
                                        </p>

                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ \Illuminate\Support\Str::limit($item->deskripsi, 55) }}
                                        </p>

                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ $item->jenis_kegiatan }}
                                        </p>
                                    </div>

                                </div>
                            </td>

                            {{-- TANGGAL --}}
                            <td class="px-5 py-4 text-gray-700 whitespace-nowrap">
                                <p>
                                    {{ $item->tanggal_mulai->translatedFormat('d M Y') }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $item->tanggal_mulai->format('H:i') }}
                                    -
                                    {{ $item->tanggal_selesai->format('H:i') }}
                                </p>
                            </td>

                            {{-- LOKASI --}}
                            <td class="px-5 py-4 text-gray-700">
                                {{ $item->lokasi }}
                            </td>

                            {{-- STATUS --}}
                            <td class="px-5 py-4 whitespace-nowrap">

                                @if ($item->status === 'draft')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                        Draft
                                    </span>

                                @elseif ($item->status === 'terbit')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Terbit
                                    </span>

                                @elseif ($item->status === 'selesai')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        Selesai
                                    </span>

                                @elseif ($item->status === 'nonaktif')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Nonaktif
                                    </span>
                                @endif

                            </td>

                            {{-- PENGELOLA --}}
                            <td class="px-5 py-4 text-gray-700 whitespace-nowrap">
                                {{ $item->pengguna->nama ?? '-' }}
                            </td>

                            {{-- AKSI --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">

                                    {{-- DETAIL --}}
                                    <a
                                        href="{{ route('kegiatan.show', $item->id) }}"
                                        class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-medium transition">
                                        Detail
                                    </a>

                                    @if ($bolehKelola)

                                        {{-- EDIT --}}
                                        <a
                                            href="{{ route('kegiatan.edit', $item->id) }}"
                                            class="px-3 py-1.5 rounded-lg bg-yellow-100 text-yellow-700 hover:bg-yellow-200 text-xs font-medium transition">
                                            Edit
                                        </a>

                                        {{-- NONAKTIFKAN --}}
                                        @if ($item->status === 'terbit')

                                            <form
                                                action="{{ route('kegiatan.ubah_status', $item->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Nonaktifkan kegiatan ini?')">

                                                @csrf
                                                @method('PATCH')

                                                <input type="hidden" name="status" value="nonaktif">

                                                <button
                                                    type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-medium transition">
                                                    Nonaktifkan
                                                </button>

                                            </form>

                                        {{-- TERBITKAN --}}
                                        @elseif (in_array($item->status, ['draft', 'nonaktif']))

                                            <form
                                                action="{{ route('kegiatan.ubah_status', $item->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Terbitkan kegiatan ini?')">

                                                @csrf
                                                @method('PATCH')

                                                <input type="hidden" name="status" value="terbit">

                                                <button
                                                    type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 text-xs font-medium transition">
                                                    Terbitkan
                                                </button>

                                            </form>

                                        @endif

                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">

                                <p class="font-medium text-gray-700">
                                    Belum ada kegiatan.
                                </p>

                                <p class="text-sm text-gray-500 mt-1">
                                    Daftar kegiatan akan ditampilkan di sini.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- PAGINATION --}}
        @if ($kegiatan->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $kegiatan->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
