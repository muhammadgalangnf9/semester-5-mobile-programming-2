
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIPEKAT')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800 overflow-hidden">

@php
    $penggunaLogin = auth()->user();
    $role = $penggunaLogin?->role?->nama_role;
@endphp

<div class="h-screen flex overflow-hidden">

    {{-- SIDEBAR --}}
    <aside class="w-64 shrink-0 bg-gray-900 text-white h-screen flex flex-col">

        {{-- LOGO --}}
        <div class="px-6 py-5 border-b border-gray-700 shrink-0">
            <h1 class="text-xl font-bold">SIPEKAT</h1>
            <p class="text-xs text-gray-400 mt-1">
                Sistem Informasi Pengelolaan RT
            </p>
        </div>

        {{-- PROFIL PENGGUNA --}}
        <div class="px-5 py-4 border-b border-gray-700 shrink-0">
            <p class="font-semibold text-sm">
                {{ $penggunaLogin->nama }}
            </p>

            <p class="text-xs text-gray-400 mt-1">
                {{ $penggunaLogin->labelRole() }}
            </p>
        </div>

        {{-- MENU SIDEBAR --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm">

            <p class="px-3 mb-2 text-xs text-gray-500 uppercase">
                Menu Utama
            </p>

            {{-- DASHBOARD --}}
            <a href="{{ route('dashboard') }}"
               class="block px-3 py-2 rounded-lg
               {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                Dashboard
            </a>

            {{-- KETUA RT --}}
            @if ($role === 'ketua_rt')

                <p class="px-3 pt-4 mb-2 text-xs text-gray-500 uppercase">
                    Administrasi
                </p>

                <a href="{{ route('ketua-rt.kelola-warga.index') }}"
                   class="block px-3 py-2 rounded-lg
                   {{ request()->routeIs('ketua-rt.kelola-warga.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    Kelola Warga
                </a>

                <a href="{{ route('ketua-rt.kelola-role.index') }}"
                   class="block px-3 py-2 rounded-lg
                   {{ request()->routeIs('ketua-rt.kelola-role.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    Kelola Role
                </a>

                <a href="{{ route('pengumuman.index') }}"
                    class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Pengumuman
                </a>

                <a href="{{ route('jadwal_ronda.index') }}"
                    class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Jadwal Ronda
                </a>

                <p class="px-3 pt-4 mb-2 text-xs text-gray-500 uppercase">
                    Pengelolaan
                </p>

                <a href="#"
                   class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Bank Sampah
                </a>

                <a href="#"
                   class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Iuran & Denda
                </a>

                <a href="{{ route('kegiatan.index') }}"
                    class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Kegiatan
                </a>

                <a href="#"
                   class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Scan Ronda
                </a>

                <a href="#"
                   class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Lihat Kas
                </a>

            @endif

            {{-- WARGA DAN WARGA BIASA --}}
            @if (in_array($role, ['warga_pj_kegiatan_dan_jadwal_ronda', 'warga_biasa']))

                <p class="px-3 pt-4 mb-2 text-xs text-gray-500 uppercase">
                    Layanan Warga
                </p>

                <a href="#"
                   class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Kegiatan
                </a>

                <a href="#"
                   class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Scan Ronda
                </a>

                <a href="#"
                   class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Lihat Kas
                </a>

                <a href="#"
                   class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Pengumuman
                </a>

                <a href="#"
                   class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    Jadwal Ronda
                </a>

                {{-- MENU PENGELOLAAN WARGA --}}
                @if ($role === 'warga_pj_kegiatan_dan_jadwal_ronda')
                    <a href="#"
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                        Bank Sampah
                    </a>

                    <a href="#"
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                        Iuran & Denda
                    </a>

                    <a href="#"
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                        Kelola Kegiatan
                    </a>

                    <a href="#"
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                        Kelola Pengumuman
                    </a>

                    <a href="#"
                       class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                        Kelola Jadwal Ronda
                    </a>
                @endif

            @endif

        </nav>

        {{-- LOGOUT --}}
        <div class="p-3 border-t border-gray-700 shrink-0">

            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit"
                        class="w-full text-left px-3 py-2 rounded-lg text-red-300 hover:bg-gray-800">
                    Keluar
                </button>
            </form>

        </div>

    </aside>

    {{-- KONTEN UTAMA --}}
    <main class="flex-1 min-w-0 h-screen flex flex-col overflow-hidden">

        {{-- HEADER --}}
        <header class="bg-white border-b px-8 py-4 shrink-0">
            <h2 class="text-lg font-semibold">
                @yield('header', 'Dashboard')
            </h2>
        </header>

        {{-- AREA SCROLL KONTEN --}}
        <div class="flex-1 overflow-y-auto p-6">

            {{-- PESAN SUKSES --}}
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- PESAN ERROR --}}
            @if (session('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ISI HALAMAN --}}
            @yield('content')

        </div>

    </main>

</div>

</body>
</html>
