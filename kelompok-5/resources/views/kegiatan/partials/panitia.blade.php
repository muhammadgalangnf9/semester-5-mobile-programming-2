{{-- PARTIAL PANITIA KEGIATAN --}}

@php
    $pengguna = Auth::user();

    $bolehKelola = (int) $pengguna->role_id === 2;

    $daftarJabatan = [
        'Ketua Pelaksana',
        'Sekretaris',
        'Bendahara',
        'Koordinator',
        'Anggota',
    ];

    $sudahAdaKetuaPelaksana = $kegiatan->panitia
        ->contains('jabatan', 'Ketua Pelaksana');
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

    {{-- HEADER --}}
    <div class="p-6 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    Panitia Kegiatan
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Susunan pengurus dan penanggung jawab kegiatan.
                </p>
            </div>

            <span class="text-sm text-gray-500">
                {{ $kegiatan->panitia->count() }} Anggota
            </span>
        </div>
    </div>

    <div class="p-6 space-y-6">

        {{-- PESAN NOTIFIKASI --}}
        @if (session('success'))
            <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- FORM TAMBAH PANITIA --}}
        @if ($bolehKelola)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">

                <h4 class="font-semibold text-gray-800 mb-4">
                    Tambah Panitia
                </h4>

                <form action="{{ route('kegiatan.panitia.store', $kegiatan->id) }}"
                      method="POST"
                      class="space-y-4">

                    @csrf

                    {{-- NAMA PANITIA --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Panitia <span class="text-red-500">*</span>
                        </label>

                        <input type="text"
                               name="nama_panitia"
                               value="{{ old('nama_panitia') }}"
                               maxlength="150"
                               required
                               placeholder="Masukkan nama panitia"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- JABATAN --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jabatan <span class="text-red-500">*</span>
                        </label>

                        <select name="jabatan"
                                id="jabatan-panitia-baru"
                                required
                                onchange="toggleInputLainnya(this, 'jabatan-panitia-baru-lainnya')"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            <option value="">Pilih Jabatan</option>

                            @foreach ($daftarJabatan as $jabatan)
                                <option value="{{ $jabatan }}"
                                    {{ old('jabatan') === $jabatan ? 'selected' : '' }}
                                    @if ($jabatan === 'Ketua Pelaksana' && $sudahAdaKetuaPelaksana)
                                        disabled
                                    @endif>
                                    {{ $jabatan }}
                                </option>
                            @endforeach

                            <option value="__lainnya__"
                                {{ old('jabatan') === '__lainnya__' ? 'selected' : '' }}>
                                Lainnya
                            </option>
                        </select>

                        <div id="jabatan-panitia-baru-lainnya"
                             class="{{ old('jabatan') === '__lainnya__' ? '' : 'hidden' }} mt-3">

                            <input type="text"
                                   name="jabatan_lainnya"
                                   value="{{ old('jabatan_lainnya') }}"
                                   maxlength="100"
                                   {{ old('jabatan') === '__lainnya__' ? 'required' : '' }}
                                   placeholder="Masukkan jabatan lainnya"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        @if ($sudahAdaKetuaPelaksana)
                            <p class="text-xs text-gray-500 mt-1">
                                Ketua Pelaksana sudah ditentukan.
                            </p>
                        @endif
                    </div>

                    {{-- TUGAS --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tugas
                        </label>

                        <textarea name="tugas"
                                  rows="3"
                                  placeholder="Jelaskan tugas dan tanggung jawab panitia"
                                  class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('tugas') }}</textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                            Tambah Panitia
                        </button>
                    </div>

                </form>
            </div>
        @endif

        {{-- DAFTAR PANITIA --}}
        <div>
            <h4 class="font-semibold text-gray-800 mb-4">
                Daftar Susunan Panitia
            </h4>

            @forelse ($kegiatan->panitia as $anggota)

                @php
                    $jabatanKustom = !in_array($anggota->jabatan, $daftarJabatan);
                @endphp

                <div class="border border-gray-200 rounded-xl p-5 mb-3">

                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

                        <div class="flex-1">

                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <h5 class="font-semibold text-gray-800">
                                    {{ $anggota->nama_panitia }}
                                </h5>

                                @if ($anggota->jabatan === 'Ketua Pelaksana')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        Ketua Pelaksana
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600">
                                {{ $anggota->jabatan }}
                            </p>

                            @if ($anggota->tugas)
                                <p class="text-sm text-gray-600 mt-3 whitespace-pre-line">
                                    {{ $anggota->tugas }}
                                </p>
                            @endif

                        </div>

                        @if ($bolehKelola)
                            <div class="flex items-center gap-3">

                                {{-- TOMBOL EDIT --}}
                                <button type="button"
                                        onclick="document.getElementById('edit-panitia-{{ $anggota->id }}').classList.toggle('hidden')"
                                        class="text-sm font-medium text-yellow-600 hover:text-yellow-700">
                                    Edit
                                </button>

                                {{-- TOMBOL HAPUS --}}
                                <form action="{{ route('kegiatan.panitia.destroy', [$kegiatan->id, $anggota->id]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus panitia ini?')">

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

                    {{-- FORM EDIT PANITIA --}}
                    @if ($bolehKelola)
                        <div id="edit-panitia-{{ $anggota->id }}"
                             class="hidden mt-5 pt-5 border-t border-gray-200">

                            <h6 class="font-semibold text-gray-800 mb-4">
                                Edit Data Panitia
                            </h6>

                            <form action="{{ route('kegiatan.panitia.update', [$kegiatan->id, $anggota->id]) }}"
                                  method="POST"
                                  class="space-y-4">

                                @csrf
                                @method('PUT')

                                {{-- NAMA --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Panitia <span class="text-red-500">*</span>
                                    </label>

                                    <input type="text"
                                           name="nama_panitia"
                                           value="{{ $anggota->nama_panitia }}"
                                           maxlength="150"
                                           required
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- JABATAN --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Jabatan <span class="text-red-500">*</span>
                                    </label>

                                    <select name="jabatan"
                                            id="jabatan-panitia-{{ $anggota->id }}"
                                            required
                                            onchange="toggleInputLainnya(this, 'jabatan-panitia-lainnya-{{ $anggota->id }}')"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                        @foreach ($daftarJabatan as $jabatan)
                                            <option value="{{ $jabatan }}"
                                                {{ $anggota->jabatan === $jabatan ? 'selected' : '' }}
                                                @if (
                                                    $jabatan === 'Ketua Pelaksana'
                                                    && $sudahAdaKetuaPelaksana
                                                    && $anggota->jabatan !== 'Ketua Pelaksana'
                                                )
                                                    disabled
                                                @endif>
                                                {{ $jabatan }}
                                            </option>
                                        @endforeach

                                        <option value="__lainnya__"
                                            {{ $jabatanKustom ? 'selected' : '' }}>
                                            Lainnya
                                        </option>
                                    </select>

                                    <div id="jabatan-panitia-lainnya-{{ $anggota->id }}"
                                         class="{{ $jabatanKustom ? '' : 'hidden' }} mt-3">

                                        <input type="text"
                                               name="jabatan_lainnya"
                                               value="{{ $jabatanKustom ? $anggota->jabatan : '' }}"
                                               maxlength="100"
                                               {{ $jabatanKustom ? 'required' : '' }}
                                               placeholder="Masukkan jabatan lainnya"
                                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                {{-- TUGAS --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tugas
                                    </label>

                                    <textarea name="tugas"
                                              rows="3"
                                              class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $anggota->tugas }}</textarea>
                                </div>

                                <div class="flex justify-end gap-3">
                                    <button type="button"
                                            onclick="document.getElementById('edit-panitia-{{ $anggota->id }}').classList.add('hidden')"
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
                        Belum ada panitia kegiatan.
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        Susunan panitia akan ditampilkan setelah ditambahkan.
                    </p>
                </div>

            @endforelse
        </div>

    </div>
</div>

{{-- SCRIPT INPUT JABATAN LAINNYA --}}
<script>
    function toggleInputLainnya(select, targetId) {
        const container = document.getElementById(targetId);
        const input = container.querySelector('input');

        if (select.value === '__lainnya__') {
            container.classList.remove('hidden');
            input.required = true;
        } else {
            container.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    }
</script>
