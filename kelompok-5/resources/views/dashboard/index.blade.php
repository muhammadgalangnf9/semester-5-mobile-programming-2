@extends('layouts.app')

@section('title', 'Dashboard - SIPEKAT')
@section('header', 'Dashboard')

@section('content')

    {{-- SAPAAN PENGGUNA --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold">
            {{ $sapaan }}, {{ $pengguna->nama }}!
        </h2>

        <p class="text-gray-500 mt-1">
            Selamat datang di Sistem Informasi Pengelolaan E-Kas Kegiatan Administrasi Transparan.
        </p>

        <span class="inline-block mt-2 text-sm text-gray-600">
            {{ (int) $pengguna->role_id === 1 ? 'KETUA RT' : $pengguna->labelRole() }}
        </span>
    </div>

    {{-- RINGKASAN DATA --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- WARGA AKTIF --}}
        <div class="bg-white p-5 rounded-xl shadow-sm">
            <p class="text-sm text-gray-500">Warga Aktif</p>
            <h3 class="text-2xl font-bold mt-2 text-green-600">
                {{ $totalWargaAktif }}
            </h3>
            <p class="text-xs text-gray-400 mt-1">
                Akun warga yang dapat digunakan
            </p>
        </div>

        {{-- WARGA NONAKTIF --}}
        <div class="bg-white p-5 rounded-xl shadow-sm">
            <p class="text-sm text-gray-500">Warga Nonaktif</p>
            <h3 class="text-2xl font-bold mt-2 text-red-600">
                {{ $totalWargaNonaktif }}
            </h3>
            <p class="text-xs text-gray-400 mt-1">
                Akun warga yang dinonaktifkan
            </p>
        </div>

        {{-- ROLE TERDAFTAR --}}
        <div class="bg-white p-5 rounded-xl shadow-sm">
            <p class="text-sm text-gray-500">Role Terdaftar</p>
            <h3 class="text-2xl font-bold mt-2">
                {{ $totalRole }}
            </h3>
            <p class="text-xs text-gray-400 mt-1">
                Role yang tersedia dalam sistem
            </p>
        </div>

    </div>

    {{-- PENGUMUMAN TERBARU --}}
    @if (in_array((int) $pengguna->role_id, [1, 2, 3], true))
        <div class="mt-8 bg-white p-5 rounded-xl shadow-sm">

            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        Pengumuman Terbaru
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Informasi terbaru yang diterbitkan oleh pengurus RT.
                    </p>
                </div>

                <a href="{{ route('dashboard.pengumuman') }}"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800">
                    Lihat Semua
                </a>
            </div>

            @forelse ($pengumumanTerbaru as $item)
                <div class="py-4 border-b border-gray-100 last:border-b-0">

                    <div class="flex flex-wrap items-start justify-between gap-3">

                        <div class="flex-1 min-w-0">

                            {{-- JUDUL PENGUMUMAN --}}
                            <h4 class="font-semibold text-gray-800">
                                {{ $item->judul }}
                            </h4>

                            {{-- RINGKASAN ISI --}}
                            <p class="mt-2 text-sm text-gray-600 leading-6"
                               style="
                                   display: -webkit-box;
                                   -webkit-line-clamp: 2;
                                   -webkit-box-orient: vertical;
                                   overflow: hidden;
                               ">
                                {{ $item->isi }}
                            </p>

                            {{-- INFORMASI PENGUMUMAN --}}
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-3 text-xs text-gray-400">
                                <span>
                                    {{ $item->pengguna?->nama ?? 'Pengguna tidak tersedia' }}
                                </span>

                                <span>
                                    {{ $item->diterbitkan_pada?->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}
                                </span>
                            </div>

                            {{-- TOMBOL LIHAT SELENGKAPNYA --}}
                            <button
                                type="button"
                                onclick="bukaPengumuman(
                                    {{ Illuminate\Support\Js::from($item->judul) }},
                                    {{ Illuminate\Support\Js::from($item->isi) }},
                                    {{ Illuminate\Support\Js::from($item->pengguna?->nama ?? 'Pengguna tidak tersedia') }},
                                    {{ Illuminate\Support\Js::from($item->diterbitkan_pada?->locale('id')->translatedFormat('d F Y, H:i') ?? '-') }}
                                )"
                                class="inline-block mt-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                                Lihat Selengkapnya →
                            </button>

                        </div>

                        {{-- STATUS --}}
                        <span class="shrink-0 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                            Terbit
                        </span>

                    </div>
                </div>

            @empty
                <div class="py-8 text-center">
                    <p class="text-sm text-gray-400">
                        Belum ada pengumuman yang diterbitkan.
                    </p>
                </div>
            @endforelse

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

                    {{-- JUDUL --}}
                    <h2 id="modalJudul"
                        class="text-xl font-bold text-gray-800">
                    </h2>

                    {{-- INFORMASI --}}
                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs text-gray-500">

                        <span id="modalPenulis"></span>

                        <span id="modalTanggal"></span>

                    </div>

                    <div class="my-5 border-t border-gray-100"></div>

                    {{-- DESKRIPSI LENGKAP --}}
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
    @endif

    {{-- RINGKASAN FITUR --}}
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">
            Administrasi RT
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- KELOLA WARGA --}}
            <div class="bg-white p-5 rounded-xl shadow-sm">
                <h4 class="font-semibold text-base">
                    Kelola Warga
                </h4>

                <p class="text-sm text-gray-500 mt-2 mb-4">
                    Mengelola data warga, informasi akun, serta status aktif dan nonaktif pengguna.
                </p>

                <a href="{{ route('ketua-rt.kelola-warga.index') }}"
                   class="inline-block bg-gray-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">
                    Buka Kelola Warga
                </a>
            </div>

            {{-- KELOLA ROLE --}}
            <div class="bg-white p-5 rounded-xl shadow-sm">
                <h4 class="font-semibold text-base">
                    Kelola Role
                </h4>

                <p class="text-sm text-gray-500 mt-2 mb-4">
                    Melihat dan mengelola nama serta deskripsi role pengguna dalam sistem.
                </p>

                <a href="{{ route('ketua-rt.kelola-role.index') }}"
                   class="inline-block bg-gray-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">
                    Buka Kelola Role
                </a>
            </div>

        </div>
    </div>

    {{-- AKTIVITAS TERBARU --}}
    <div class="mt-8 bg-white p-5 rounded-xl shadow-sm">

        <div class="mb-5">
            <h3 class="text-lg font-semibold">
                Aktivitas Terbaru
            </h3>

            <p class="text-sm text-gray-500 mt-1">
                Riwayat aktivitas pengguna dalam sistem.
            </p>
        </div>

        @forelse ($aktivitasTerbaru as $aktivitas)

            <div class="flex items-start gap-3 py-4 border-b last:border-b-0">

                <div class="flex-shrink-0 bg-gray-100 rounded-full p-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5 text-gray-600"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="flex-1 min-w-0">

                    <h4 class="text-sm font-semibold text-gray-800">
                        {{ $aktivitas->aktivitas }}
                    </h4>

                    <p class="text-sm text-gray-600 mt-1">
                        {{ $aktivitas->deskripsi }}
                    </p>

                    <div class="flex flex-wrap gap-x-2 text-xs text-gray-400 mt-2">
                        <span>
                            {{ $aktivitas->pengguna?->nama ?? 'Pengguna tidak tersedia' }}
                        </span>

                        <span>•</span>

                        <span>
                            {{ $aktivitas->created_at?->locale('id')->diffForHumans() }}
                        </span>
                    </div>

                </div>

            </div>

        @empty

            <div class="text-center py-8 text-sm text-gray-400">
                Belum ada data aktivitas.
            </div>

        @endforelse

    </div>

    {{-- JAVASCRIPT MODAL PENGUMUMAN --}}
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

        // Tutup ketika area luar modal diklik.
        document.getElementById('modalPengumuman')?.addEventListener('click', function(event) {
            if (event.target === this) {
                tutupPengumuman();
            }
        });

        // Tutup menggunakan tombol Escape.
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                tutupPengumuman();
            }
        });
    </script>

@endsection
