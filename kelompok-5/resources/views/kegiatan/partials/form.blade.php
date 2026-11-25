{{-- FORM KEGIATAN --}}
@php
    $isEdit = isset($kegiatan) && $kegiatan->exists;

    $jenisKegiatan = [
        'Sosial',
        'Keagamaan',
        'Olahraga',
        'Kebersihan',
        'Pendidikan',
        'Peringatan Hari Besar',
        'Rapat',
        'Lainnya',
    ];
@endphp

<form action="{{ $isEdit
        ? route('kegiatan.update', $kegiatan->id)
        : route('kegiatan.store') }}"
      method="POST">

    @csrf

    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">

        {{-- INFORMASI NAMA KEGIATAN --}}
        <div>
            <label for="nama_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                Nama Kegiatan <span class="text-red-500">*</span>
            </label>

            <input type="text"
                   name="nama_kegiatan"
                   id="nama_kegiatan"
                   value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan ?? '') }}"
                   maxlength="150"
                   required
                   placeholder="Masukkan nama kegiatan"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- JENIS KEGIATAN --}}
        <div>
            <label for="jenis_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                Jenis Kegiatan <span class="text-red-500">*</span>
            </label>

            <select name="jenis_kegiatan"
                    id="jenis_kegiatan"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                <option value="">Pilih Jenis Kegiatan</option>

                @foreach ($jenisKegiatan as $jenis)
                    <option value="{{ $jenis }}"
                        {{ old('jenis_kegiatan', $kegiatan->jenis_kegiatan ?? '') === $jenis ? 'selected' : '' }}>
                        {{ $jenis }}
                    </option>
                @endforeach

            </select>
        </div>

        {{-- DESKRIPSI --}}
        <div>
            <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">
                Deskripsi Kegiatan <span class="text-red-500">*</span>
            </label>

            <textarea name="deskripsi"
                      id="deskripsi"
                      rows="5"
                      required
                      placeholder="Jelaskan tujuan dan informasi kegiatan"
                      class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('deskripsi', $kegiatan->deskripsi ?? '') }}</textarea>
        </div>

        {{-- TANGGAL DAN WAKTU --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div>
                <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal dan Waktu Mulai <span class="text-red-500">*</span>
                </label>

                <input type="datetime-local"
                       name="tanggal_mulai"
                       id="tanggal_mulai"
                       value="{{ old('tanggal_mulai', isset($kegiatan) && $kegiatan->tanggal_mulai
                            ? $kegiatan->tanggal_mulai->format('Y-m-d\TH:i')
                            : '') }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal dan Waktu Selesai <span class="text-red-500">*</span>
                </label>

                <input type="datetime-local"
                       name="tanggal_selesai"
                       id="tanggal_selesai"
                       value="{{ old('tanggal_selesai', isset($kegiatan) && $kegiatan->tanggal_selesai
                            ? $kegiatan->tanggal_selesai->format('Y-m-d\TH:i')
                            : '') }}"
                       required
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

        </div>

        {{-- LOKASI --}}
        <div>
            <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-2">
                Lokasi <span class="text-red-500">*</span>
            </label>

            <input type="text"
                   name="lokasi"
                   id="lokasi"
                   value="{{ old('lokasi', $kegiatan->lokasi ?? '') }}"
                   maxlength="150"
                   required
                   placeholder="Masukkan lokasi kegiatan"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- LINK POSTER --}}
        <div>
            <label for="link_poster" class="block text-sm font-medium text-gray-700 mb-2">
                Link Poster Kegiatan
            </label>

            <input type="url"
                   name="link_poster"
                   id="link_poster"
                   value="{{ old('link_poster', $kegiatan->link_poster ?? '') }}"
                   maxlength="500"
                   placeholder="https://drive.google.com/..."
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

            <p class="text-xs text-gray-500 mt-1">
                Masukkan tautan poster dari Google Drive. Kosongkan jika belum tersedia.
            </p>

            @if ($isEdit && $kegiatan->link_poster)
                <a href="{{ $kegiatan->link_poster }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-800">
                    Lihat poster saat ini
                </a>
            @endif
        </div>

        {{-- STATUS --}}
        @if (!$isEdit)
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Status Kegiatan <span class="text-red-500">*</span>
                </label>

                <select name="status"
                        id="status"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>
                        Draft
                    </option>

                    <option value="terbit" {{ old('status') === 'terbit' ? 'selected' : '' }}>
                        Terbit
                    </option>

                </select>

                <p class="text-xs text-gray-500 mt-1">
                    Sebaiknya simpan sebagai Draft terlebih dahulu, lalu lengkapi panitia sebelum menerbitkan kegiatan.
                </p>
            </div>
        @endif

        {{-- TOMBOL --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-5 border-t border-gray-100">

            <a href="{{ route('kegiatan.index') }}"
               class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium text-center hover:bg-gray-50 transition">
                Batal
            </a>

            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Kegiatan' }}
            </button>

        </div>

    </div>
</form>