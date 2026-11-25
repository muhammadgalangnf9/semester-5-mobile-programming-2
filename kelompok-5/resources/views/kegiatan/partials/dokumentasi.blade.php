{{-- PARTIAL DOKUMENTASI KEGIATAN --}}

@php
    $pengguna = Auth::user();

    $bolehKelola = Auth::check() && (int) $pengguna->role_id === 2;
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">

        <div>
            <h3 class="text-lg font-bold text-gray-800">
                Dokumentasi Kegiatan
            </h3>

            <p class="text-sm text-gray-500 mt-1">
                Daftar foto dan video dokumentasi kegiatan.
            </p>
        </div>

        @if ($bolehKelola)
            <button
                type="button"
                onclick="toggleFormDokumentasi()"
                class="inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
                + Tambah Dokumentasi
            </button>
        @endif

    </div>

    {{-- PESAN VALIDASI --}}
    @if ($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm font-semibold text-red-700 mb-2">
                Terdapat kesalahan pengisian:
            </p>

            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM TAMBAH --}}
    @if ($bolehKelola)
        <div id="formDokumentasi"
             class="hidden mb-6 p-5 bg-gray-50 border border-gray-200 rounded-xl">

            <h4 class="font-semibold text-gray-800 mb-4">
                Tambah Dokumentasi
            </h4>

            <form
                action="{{ route('kegiatan.dokumentasi.store', $kegiatan->id) }}"
                method="POST"
                class="space-y-4">

                @csrf

                {{-- NAMA DOKUMENTASI --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Dokumentasi <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama_dokumentasi"
                        value="{{ old('nama_dokumentasi') }}"
                        maxlength="150"
                        required
                        placeholder="Contoh: Foto pembukaan kegiatan"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>

                {{-- JENIS DOKUMENTASI --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jenis Dokumentasi <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="jenis"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">

                        <option value="">Pilih Jenis</option>

                        <option value="foto" {{ old('jenis') === 'foto' ? 'selected' : '' }}>
                            Foto
                        </option>

                        <option value="video" {{ old('jenis') === 'video' ? 'selected' : '' }}>
                            Video
                        </option>

                    </select>
                </div>

                {{-- LINK GOOGLE DRIVE --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Link Google Drive <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="url"
                        name="link_drive"
                        value="{{ old('link_drive') }}"
                        maxlength="500"
                        required
                        placeholder="https://drive.google.com/..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">

                    <p class="text-xs text-gray-500 mt-1">
                        Pastikan tautan Google Drive dapat diakses oleh warga yang memiliki izin.
                    </p>
                </div>

                {{-- TOMBOL --}}
                <div class="flex justify-end gap-2">

                    <button
                        type="button"
                        onclick="toggleFormDokumentasi()"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Simpan Dokumentasi
                    </button>

                </div>

            </form>
        </div>
    @endif

    {{-- DAFTAR DOKUMENTASI --}}
    @if ($kegiatan->dokumentasi->isEmpty())

        <div class="text-center py-10 border border-dashed border-gray-300 rounded-xl">
            <p class="text-sm text-gray-500">
                Belum ada dokumentasi kegiatan.
            </p>
        </div>

    @else

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            @foreach ($kegiatan->dokumentasi as $dokumentasi)

                <div class="border border-gray-200 rounded-xl p-4">

                    {{-- INFORMASI DOKUMENTASI --}}
                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <h4 class="font-semibold text-gray-800 break-words">
                                {{ $dokumentasi->nama_dokumentasi }}
                            </h4>

                            <span class="inline-block mt-2 px-2.5 py-1 text-xs font-medium rounded-full
                                {{ $dokumentasi->jenis === 'foto'
                                    ? 'bg-blue-100 text-blue-700'
                                    : 'bg-purple-100 text-purple-700' }}">
                                {{ ucfirst($dokumentasi->jenis) }}
                            </span>

                        </div>

                        <a
                            href="{{ $dokumentasi->link_drive }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="shrink-0 inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">
                            Buka Link
                        </a>

                    </div>

                    {{-- TOMBOL KELOLA --}}
                    @if ($bolehKelola)

                        <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-gray-100">

                            <button
                                type="button"
                                onclick="toggleEditDokumentasi({{ $dokumentasi->id }})"
                                class="px-3 py-1.5 text-sm font-medium text-yellow-700 bg-yellow-50 rounded-lg hover:bg-yellow-100">
                                Edit
                            </button>

                            <form
                                action="{{ route('kegiatan.dokumentasi.destroy', [$kegiatan->id, $dokumentasi->id]) }}"
                                method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus dokumentasi ini?')">

                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100">
                                    Hapus
                                </button>

                            </form>

                        </div>

                        {{-- FORM EDIT --}}
                        <div
                            id="editDokumentasi{{ $dokumentasi->id }}"
                            class="hidden mt-4 pt-4 border-t border-gray-200">

                            <h5 class="font-semibold text-sm text-gray-800 mb-3">
                                Edit Dokumentasi
                            </h5>

                            <form
                                action="{{ route('kegiatan.dokumentasi.update', [$kegiatan->id, $dokumentasi->id]) }}"
                                method="POST"
                                class="space-y-3">

                                @csrf
                                @method('PUT')

                                {{-- NAMA DOKUMENTASI --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nama Dokumentasi <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="nama_dokumentasi"
                                        value="{{ $dokumentasi->nama_dokumentasi }}"
                                        maxlength="150"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                </div>

                                {{-- JENIS DOKUMENTASI --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Jenis Dokumentasi <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        name="jenis"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">

                                        <option value="foto" {{ $dokumentasi->jenis === 'foto' ? 'selected' : '' }}>
                                            Foto
                                        </option>

                                        <option value="video" {{ $dokumentasi->jenis === 'video' ? 'selected' : '' }}>
                                            Video
                                        </option>

                                    </select>
                                </div>

                                {{-- LINK GOOGLE DRIVE --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Link Google Drive <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        type="url"
                                        name="link_drive"
                                        value="{{ $dokumentasi->link_drive }}"
                                        maxlength="500"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                </div>

                                {{-- TOMBOL --}}
                                <div class="flex justify-end gap-2">

                                    <button
                                        type="button"
                                        onclick="toggleEditDokumentasi({{ $dokumentasi->id }})"
                                        class="px-3 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300">
                                        Batal
                                    </button>

                                    <button
                                        type="submit"
                                        class="px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                                        Simpan Perubahan
                                    </button>

                                </div>

                            </form>
                        </div>

                    @endif

                </div>

            @endforeach

        </div>

    @endif

</div>

{{-- JAVASCRIPT --}}
@if ($bolehKelola)
    <script>
        function toggleFormDokumentasi() {
            const form = document.getElementById('formDokumentasi');

            if (form) {
                form.classList.toggle('hidden');
            }
        }

        function toggleEditDokumentasi(id) {
            const form = document.getElementById('editDokumentasi' + id);

            if (form) {
                form.classList.toggle('hidden');
            }
        }
    </script>
@endif
