{{-- PARTIAL PESERTA KEGIATAN --}}

@php
    $pengguna = Auth::user();

    $bolehKelola = (int) $pengguna->role_id === 2;

    $daftarStatus = [
        'belum_dikonfirmasi' => 'Belum Dikonfirmasi',
        'hadir' => 'Hadir',
        'tidak_hadir' => 'Tidak Hadir',
        'izin' => 'Izin',
        'sakit' => 'Sakit',
    ];

    $jumlahPeserta = $kegiatan->peserta->count();

    $jumlahHadir = $kegiatan->peserta
        ->where('status_kehadiran', 'hadir')->count();

    $jumlahTidakHadir = $kegiatan->peserta
        ->where('status_kehadiran', 'tidak_hadir')->count();

    $jumlahIzin = $kegiatan->peserta
        ->where('status_kehadiran', 'izin')->count();

    $jumlahSakit = $kegiatan->peserta
        ->where('status_kehadiran', 'sakit')->count();

    $jumlahBelumKonfirmasi = $kegiatan->peserta
        ->where('status_kehadiran', 'belum_dikonfirmasi')->count();
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

    {{-- HEADER --}}
    <div class="p-6 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    Peserta Kegiatan
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Daftar peserta dan informasi kehadiran kegiatan.
                </p>
            </div>

            <span class="text-sm text-gray-500">
                {{ $jumlahPeserta }} Peserta
            </span>
        </div>
    </div>

    <div class="p-6 space-y-6">

        {{-- RINGKASAN PESERTA --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                <p class="text-xs text-blue-700">Total Peserta</p>
                <p class="text-2xl font-bold text-blue-800 mt-1">
                    {{ $jumlahPeserta }}
                </p>
            </div>

            <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                <p class="text-xs text-green-700">Hadir</p>
                <p class="text-2xl font-bold text-green-800 mt-1">
                    {{ $jumlahHadir }}
                </p>
            </div>

            <div class="bg-red-50 border border-red-100 rounded-xl p-4">
                <p class="text-xs text-red-700">Tidak Hadir</p>
                <p class="text-2xl font-bold text-red-800 mt-1">
                    {{ $jumlahTidakHadir }}
                </p>
            </div>

            <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4">
                <p class="text-xs text-yellow-700">Belum Dikonfirmasi</p>
                <p class="text-2xl font-bold text-yellow-800 mt-1">
                    {{ $jumlahBelumKonfirmasi }}
                </p>
            </div>

            <div class="bg-purple-50 border border-purple-100 rounded-xl p-4">
                <p class="text-xs text-purple-700">Izin</p>
                <p class="text-2xl font-bold text-purple-800 mt-1">
                    {{ $jumlahIzin }}
                </p>
            </div>

            <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
                <p class="text-xs text-orange-700">Sakit</p>
                <p class="text-2xl font-bold text-orange-800 mt-1">
                    {{ $jumlahSakit }}
                </p>
            </div>

        </div>

        {{-- FORM TAMBAH PESERTA --}}
        @if ($bolehKelola)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">

                <h4 class="font-semibold text-gray-800 mb-4">
                    Tambah Peserta
                </h4>

                <form action="{{ route('kegiatan.peserta.store', $kegiatan->id) }}"
                      method="POST"
                      class="space-y-4">

                    @csrf

                    {{-- NAMA --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Peserta <span class="text-red-500">*</span>
                        </label>

                        <input type="text"
                               name="nama"
                               value="{{ old('nama') }}"
                               maxlength="150"
                               required
                               placeholder="Masukkan nama peserta"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- GANG --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Gang <span class="text-red-500">*</span>
                        </label>

                        <select name="gang"
                                required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            <option value="">Pilih Gang</option>

                            @foreach ($daftarGang as $gang)
                                <option value="{{ $gang }}"
                                    {{ old('gang') === $gang ? 'selected' : '' }}>
                                    {{ $gang }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- STATUS KEHADIRAN --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status Kehadiran
                        </label>

                        <select name="status_kehadiran"
                                required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            @foreach ($daftarStatus as $nilai => $label)
                                <option value="{{ $nilai }}"
                                    {{ old('status_kehadiran', 'belum_dikonfirmasi') === $nilai ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- KETERANGAN --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Keterangan
                        </label>

                        <textarea name="keterangan"
                                  rows="3"
                                  placeholder="Keterangan tambahan jika ada"
                                  class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('keterangan') }}</textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                            Tambah Peserta
                        </button>
                    </div>

                </form>
            </div>
        @endif

        {{-- DAFTAR PESERTA --}}
        <div>
            <h4 class="font-semibold text-gray-800 mb-4">
                Daftar Peserta
            </h4>

            @forelse ($kegiatan->peserta as $item)

                <div class="border border-gray-200 rounded-xl p-5 mb-3">

                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

                        <div class="flex-1">

                            <h5 class="font-semibold text-gray-800">
                                {{ $item->nama }}
                            </h5>

                            <p class="text-sm text-gray-500 mt-1">
                                {{ $item->gang }}
                            </p>

                            @php
                                $warnaStatus = [
                                    'belum_dikonfirmasi' => 'bg-yellow-100 text-yellow-700',
                                    'hadir' => 'bg-green-100 text-green-700',
                                    'tidak_hadir' => 'bg-red-100 text-red-700',
                                    'izin' => 'bg-purple-100 text-purple-700',
                                    'sakit' => 'bg-orange-100 text-orange-700',
                                ];
                            @endphp

                            <span class="inline-block mt-3 px-2.5 py-1 rounded-full text-xs font-medium {{ $warnaStatus[$item->status_kehadiran] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $daftarStatus[$item->status_kehadiran] ?? 'Tidak Diketahui' }}
                            </span>

                            @if ($item->keterangan)
                                <p class="text-sm text-gray-600 mt-3 whitespace-pre-line">
                                    {{ $item->keterangan }}
                                </p>
                            @endif

                        </div>

                        @if ($bolehKelola)
                            <div class="flex items-center gap-3">

                                <button type="button"
                                        onclick="document.getElementById('edit-peserta-{{ $item->id }}').classList.toggle('hidden')"
                                        class="text-sm font-medium text-yellow-600 hover:text-yellow-700">
                                    Edit
                                </button>

                                <form action="{{ route('kegiatan.peserta.destroy', [$kegiatan->id, $item->id]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus peserta ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="text-sm font-medium text-red-600 hover:text-red-700">
                                        Hapus
                                    </button>

                                </form>

                            </div>
                        @endif

                    </div>

                    {{-- FORM EDIT PESERTA --}}
                    @if ($bolehKelola)
                        <div id="edit-peserta-{{ $item->id }}"
                             class="hidden mt-5 pt-5 border-t border-gray-200">

                            <h6 class="font-semibold text-gray-800 mb-4">
                                Edit Data Peserta
                            </h6>

                            <form action="{{ route('kegiatan.peserta.update', [$kegiatan->id, $item->id]) }}"
                                  method="POST"
                                  class="space-y-4">

                                @csrf
                                @method('PUT')

                                {{-- NAMA --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Peserta <span class="text-red-500">*</span>
                                    </label>

                                    <input type="text"
                                           name="nama"
                                           value="{{ $item->nama }}"
                                           maxlength="150"
                                           required
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- GANG --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Gang <span class="text-red-500">*</span>
                                    </label>

                                    <select name="gang"
                                            required
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                        @if (!$daftarGang->contains($item->gang))
                                            <option value="{{ $item->gang }}" selected>
                                                {{ $item->gang }}
                                            </option>
                                        @endif

                                        @foreach ($daftarGang as $gang)
                                            <option value="{{ $gang }}"
                                                {{ $item->gang === $gang ? 'selected' : '' }}>
                                                {{ $gang }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                {{-- STATUS --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Status Kehadiran
                                    </label>

                                    <select name="status_kehadiran"
                                            required
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                        @foreach ($daftarStatus as $nilai => $label)
                                            <option value="{{ $nilai }}"
                                                {{ $item->status_kehadiran === $nilai ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                {{-- KETERANGAN --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Keterangan
                                    </label>

                                    <textarea name="keterangan"
                                              rows="3"
                                              class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $item->keterangan }}</textarea>
                                </div>

                                <div class="flex justify-end gap-3">

                                    <button type="button"
                                            onclick="document.getElementById('edit-peserta-{{ $item->id }}').classList.add('hidden')"
                                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                                        Batal
                                    </button>

                                    <button type="submit"
                                            class="px-4 py-2 rounded-lg bg-yellow-500 text-white text-sm hover:bg-yellow-600">
                                        Simpan Perubahan
                                    </button>

                                </div>

                            </form>
                        </div>
                    @endif

                </div>

            @empty

                <div class="text-center py-10 border border-dashed border-gray-300 rounded-xl">
                    <p class="text-sm font-medium text-gray-700">
                        Belum ada peserta kegiatan.
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        Peserta yang ditambahkan akan ditampilkan di sini.
                    </p>
                </div>

            @endforelse
        </div>

    </div>
</div>
