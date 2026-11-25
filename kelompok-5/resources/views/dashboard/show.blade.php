@extends('layouts.app')

@section('title', 'Daftar Pengumuman - SIPEKAT')
@section('header', 'Daftar Pengumuman')

@section('content')

    {{-- HEADER HALAMAN --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            Semua Pengumuman
        </h2>

        <p class="text-sm text-gray-500 mt-1">
            Daftar informasi dan pengumuman yang telah diterbitkan oleh pengurus RT.
        </p>
    </div>

    {{-- DAFTAR PENGUMUMAN --}}
    <div class="bg-white rounded-xl shadow-sm p-5">

        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-semibold text-gray-800">
                Daftar Pengumuman
            </h3>

            <span class="text-sm text-gray-500">
                {{ $pengumuman->total() }} pengumuman
            </span>
        </div>

        @forelse ($pengumuman as $item)

            <div class="py-5 border-b border-gray-100 last:border-b-0">

                {{-- JUDUL --}}
                <h4 class="text-base font-semibold text-gray-800">
                    {{ $item->judul }}
                </h4>

                {{-- RINGKASAN ISI --}}
                <p class="mt-2 text-sm text-gray-600 leading-6"
                   style="
                       display: -webkit-box;
                       -webkit-line-clamp: 3;
                       -webkit-box-orient: vertical;
                       overflow: hidden;
                   ">
                    {{ $item->isi }}
                </p>

                {{-- INFORMASI PENGUMUMAN --}}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3 text-xs text-gray-400">

                    <span>
                        {{ $item->pengguna?->nama ?? 'Pengguna tidak tersedia' }}
                    </span>

                    <span>
                        {{ $item->diterbitkan_pada?->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}
                    </span>

                </div>

                {{-- TOMBOL DETAIL --}}
                <button
                    type="button"
                    onclick="bukaPengumuman(
                        {{ Illuminate\Support\Js::from($item->judul) }},
                        {{ Illuminate\Support\Js::from($item->isi) }},
                        {{ Illuminate\Support\Js::from($item->pengguna?->nama ?? 'Pengguna tidak tersedia') }},
                        {{ Illuminate\Support\Js::from($item->diterbitkan_pada?->locale('id')->translatedFormat('d F Y, H:i') ?? '-') }}
                    )"
                    class="mt-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                    Lihat Selengkapnya →
                </button>

            </div>

        @empty

            <div class="py-12 text-center">
                <p class="text-sm text-gray-400">
                    Belum ada pengumuman yang diterbitkan.
                </p>
            </div>

        @endforelse

        {{-- PAGINATION --}}
        @if ($pengumuman->hasPages())
            <div class="mt-6">
                {{ $pengumuman->links() }}
            </div>
        @endif

    </div>

    {{-- TOMBOL KEMBALI --}}
    <div class="mt-6">
        <a href="{{ route('dashboard') }}"
           class="inline-block rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
            Kembali ke Dashboard
        </a>
    </div>

    {{-- MODAL DETAIL PENGUMUMAN --}}
    <div id="modalPengumuman"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

        <div class="w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-xl bg-white shadow-xl">

            {{-- HEADER MODAL --}}
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">

                <h3 class="text-lg font-semibold text-gray-800">
                    Detail Pengumuman
                </h3>

                <button type="button"
                        onclick="tutupPengumuman()"
                        aria-label="Tutup pop-up"
                        class="text-gray-400 hover:text-gray-700 text-2xl leading-none">
                    &times;
                </button>

            </div>

            {{-- ISI MODAL --}}
            <div class="overflow-y-auto px-6 py-5"
                 style="max-height: calc(90vh - 140px);">

                <h2 id="modalJudul"
                    class="text-xl font-bold text-gray-800">
                </h2>

                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs text-gray-500">
                    <span id="modalPenulis"></span>
                    <span id="modalTanggal"></span>
                </div>

                <div class="my-5 border-t border-gray-100"></div>

                <div id="modalIsi"
                     class="text-sm text-gray-700 leading-7 whitespace-pre-line break-words">
                </div>

            </div>

            {{-- FOOTER MODAL --}}
            <div class="flex justify-end border-t border-gray-200 px-6 py-4">

                <button type="button"
                        onclick="tutupPengumuman()"
                        class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Tutup
                </button>

            </div>

        </div>
    </div>

    {{-- JAVASCRIPT MODAL --}}
    <script>
        function bukaPengumuman(judul, isi, penulis, tanggal) {
            document.getElementById('modalJudul').textContent = judul;
            document.getElementById('modalIsi').textContent = isi;
            document.getElementById('modalPenulis').textContent = penulis;
            document.getElementById('modalTanggal').textContent = tanggal;

            const modal = document.getElementById('modalPengumuman');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.body.style.overflow = 'hidden';
        }

        function tutupPengumuman() {
            const modal = document.getElementById('modalPengumuman');

            modal.classList.add('hidden');
            modal.classList.remove('flex');

            document.body.style.overflow = '';
        }

        document.getElementById('modalPengumuman')?.addEventListener('click', function(event) {
            if (event.target === this) {
                tutupPengumuman();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                tutupPengumuman();
            }
        });
    </script>

@endsection
