{{-- PARTIAL RENCANA ANGGARAN BIAYA --}}

@php
    $pengguna = Auth::user();

    $bolehKelola = (int) $pengguna->role_id === 2;

    $daftarSatuan = [
        'buah', 'unit', 'pcs', 'set', 'paket', 'pasang',
        'lusin', 'kodi', 'rim', 'kotak', 'dus', 'botol',
        'kaleng', 'bungkus', 'sachet', 'lembar', 'batang',
        'gulungan', 'gram', 'kilogram', 'kuintal', 'ton',
        'mililiter', 'liter', 'galon', 'milimeter',
        'sentimeter', 'meter', 'kilometer', 'meter_persegi',
        'menit', 'jam', 'hari', 'minggu', 'bulan',
        'porsi', 'orang', 'perjalanan', 'kendaraan',
        'tiket', 'sesi', 'kegiatan',
    ];

    $daftarKategori = [
        'Konsumsi',
        'Perlengkapan',
        'Dekorasi',
        'Hadiah',
        'Dokumentasi',
        'Transportasi',
    ];

    $totalRab = $kegiatan->rab->sum('total_biaya');
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

    {{-- HEADER --}}
    <div class="p-6 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    Rencana Anggaran Biaya
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Daftar kebutuhan dan estimasi biaya kegiatan.
                </p>
            </div>

            <span class="text-sm text-gray-500">
                {{ $kegiatan->rab->count() }} Item Anggaran
            </span>
        </div>
    </div>

    <div class="p-6 space-y-6">

        {{-- FORM TAMBAH RAB --}}
        @if ($bolehKelola)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">

                <h4 class="font-semibold text-gray-800 mb-4">
                    Tambah Anggaran
                </h4>

                <form action="{{ route('kegiatan.rab.store', $kegiatan->id) }}"
                      method="POST"
                      class="space-y-4">

                    @csrf

                    {{-- NAMA KEBUTUHAN --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kebutuhan <span class="text-red-500">*</span>
                        </label>

                        <input type="text"
                               name="nama_kebutuhan"
                               value="{{ old('nama_kebutuhan') }}"
                               maxlength="150"
                               required
                               placeholder="Contoh: Konsumsi peserta"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- KATEGORI PENGELUARAN --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori Pengeluaran <span class="text-red-500">*</span>
                        </label>

                        <select name="kategori_pengeluaran"
                                id="kategori-rab-baru"
                                required
                                onchange="toggleRabLainnya(this, 'kategori-rab-baru-lainnya')"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            <option value="">Pilih Kategori</option>

                            @foreach ($daftarKategori as $kategori)
                                <option value="{{ $kategori }}"
                                    {{ old('kategori_pengeluaran') === $kategori ? 'selected' : '' }}>
                                    {{ $kategori }}
                                </option>
                            @endforeach

                            <option value="__lainnya__"
                                {{ old('kategori_pengeluaran') === '__lainnya__' ? 'selected' : '' }}>
                                Lainnya
                            </option>
                        </select>

                        <div id="kategori-rab-baru-lainnya"
                             class="{{ old('kategori_pengeluaran') === '__lainnya__' ? '' : 'hidden' }} mt-3">

                            <input type="text"
                                   name="kategori_pengeluaran_lainnya"
                                   value="{{ old('kategori_pengeluaran_lainnya') }}"
                                   maxlength="100"
                                   {{ old('kategori_pengeluaran') === '__lainnya__' ? 'required' : '' }}
                                   placeholder="Masukkan kategori pengeluaran"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- JUMLAH DAN SATUAN --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jumlah <span class="text-red-500">*</span>
                            </label>

                            <input type="number"
                                   name="jumlah"
                                   value="{{ old('jumlah') }}"
                                   min="0.01"
                                   step="0.01"
                                   required
                                   placeholder="Contoh: 50"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Satuan <span class="text-red-500">*</span>
                            </label>

                            <select name="satuan"
                                    id="satuan-rab-baru"
                                    required
                                    onchange="toggleRabLainnya(this, 'satuan-rab-baru-lainnya')"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                <option value="">Pilih Satuan</option>

                                @foreach ($daftarSatuan as $satuan)
                                    <option value="{{ $satuan }}"
                                        {{ old('satuan') === $satuan ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', ucfirst($satuan)) }}
                                    </option>
                                @endforeach

                                <option value="__lainnya__"
                                    {{ old('satuan') === '__lainnya__' ? 'selected' : '' }}>
                                    Lainnya
                                </option>
                            </select>

                            <div id="satuan-rab-baru-lainnya"
                                 class="{{ old('satuan') === '__lainnya__' ? '' : 'hidden' }} mt-3">

                                <input type="text"
                                       name="satuan_lainnya"
                                       value="{{ old('satuan_lainnya') }}"
                                       maxlength="100"
                                       {{ old('satuan') === '__lainnya__' ? 'required' : '' }}
                                       placeholder="Masukkan satuan lainnya"
                                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                    </div>

                    {{-- HARGA SATUAN --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Harga Satuan (Rp) <span class="text-red-500">*</span>
                        </label>

                        <input type="number"
                               name="harga_satuan"
                               value="{{ old('harga_satuan') }}"
                               min="0"
                               step="0.01"
                               required
                               placeholder="Contoh: 15000"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- SUMBER DANA --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Sumber Dana <span class="text-red-500">*</span>
                        </label>

                        <input type="text"
                               name="sumber_dana"
                               value="{{ old('sumber_dana') }}"
                               maxlength="100"
                               required
                               placeholder="Contoh: Kas RT atau Iuran Warga"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                            Tambah Anggaran
                        </button>
                    </div>

                </form>
            </div>
        @endif

        {{-- DAFTAR ANGGARAN --}}
        <div>
            <h4 class="font-semibold text-gray-800 mb-4">
                Daftar Rencana Anggaran
            </h4>

            @forelse ($kegiatan->rab as $item)

                @php
                    $kategoriKustom = !in_array(
                        $item->kategori_pengeluaran,
                        $daftarKategori
                    );

                    $satuanKustom = !in_array(
                        $item->satuan,
                        $daftarSatuan
                    );
                @endphp

                <div class="border border-gray-200 rounded-xl p-5 mb-3">

                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

                        <div class="flex-1">

                            <h5 class="font-semibold text-gray-800">
                                {{ $item->nama_kebutuhan }}
                            </h5>

                            <p class="text-xs text-gray-500 mt-1">
                                {{ $item->kategori_pengeluaran }}
                            </p>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">

                                <div>
                                    <p class="text-xs text-gray-500">Jumlah</p>
                                    <p class="text-sm font-medium text-gray-800">
                                        {{ rtrim(rtrim(number_format((float) $item->jumlah, 2, ',', '.'), '0'), ',') }}
                                        {{ str_replace('_', ' ', $item->satuan) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500">Harga Satuan</p>
                                    <p class="text-sm font-medium text-gray-800">
                                        Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500">Total Biaya</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        Rp {{ number_format((float) $item->total_biaya, 0, ',', '.') }}
                                    </p>
                                </div>

                            </div>

                            <p class="text-xs text-gray-500 mt-3">
                                Sumber Dana: {{ $item->sumber_dana }}
                            </p>

                        </div>

                        @if ($bolehKelola)
                            <div class="flex items-center gap-3">

                                <button type="button"
                                        onclick="document.getElementById('edit-rab-{{ $item->id }}').classList.toggle('hidden')"
                                        class="text-sm font-medium text-yellow-600 hover:text-yellow-700">
                                    Edit
                                </button>

                                <form action="{{ route('kegiatan.rab.destroy', [$kegiatan->id, $item->id]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus anggaran ini?')">

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

                    {{-- FORM EDIT RAB --}}
                    @if ($bolehKelola)
                        <div id="edit-rab-{{ $item->id }}"
                             class="hidden mt-5 pt-5 border-t border-gray-200">

                            <h6 class="font-semibold text-gray-800 mb-4">
                                Edit Anggaran
                            </h6>

                            <form action="{{ route('kegiatan.rab.update', [$kegiatan->id, $item->id]) }}"
                                  method="POST"
                                  class="space-y-4">

                                @csrf
                                @method('PUT')

                                {{-- NAMA KEBUTUHAN --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nama Kebutuhan <span class="text-red-500">*</span>
                                    </label>

                                    <input type="text"
                                           name="nama_kebutuhan"
                                           value="{{ $item->nama_kebutuhan }}"
                                           maxlength="150"
                                           required
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- KATEGORI --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Kategori Pengeluaran <span class="text-red-500">*</span>
                                    </label>

                                    <select name="kategori_pengeluaran"
                                            id="kategori-rab-{{ $item->id }}"
                                            required
                                            onchange="toggleRabLainnya(this, 'kategori-rab-lainnya-{{ $item->id }}')"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                        @foreach ($daftarKategori as $kategori)
                                            <option value="{{ $kategori }}"
                                                {{ !$kategoriKustom && $item->kategori_pengeluaran === $kategori ? 'selected' : '' }}>
                                                {{ $kategori }}
                                            </option>
                                        @endforeach

                                        <option value="__lainnya__"
                                            {{ $kategoriKustom ? 'selected' : '' }}>
                                            Lainnya
                                        </option>
                                    </select>

                                    <div id="kategori-rab-lainnya-{{ $item->id }}"
                                         class="{{ $kategoriKustom ? '' : 'hidden' }} mt-3">

                                        <input type="text"
                                               name="kategori_pengeluaran_lainnya"
                                               value="{{ $kategoriKustom ? $item->kategori_pengeluaran : '' }}"
                                               maxlength="100"
                                               {{ $kategoriKustom ? 'required' : '' }}
                                               placeholder="Masukkan kategori pengeluaran"
                                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                {{-- JUMLAH DAN SATUAN --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Jumlah <span class="text-red-500">*</span>
                                        </label>

                                        <input type="number"
                                               name="jumlah"
                                               value="{{ $item->jumlah }}"
                                               min="0.01"
                                               step="0.01"
                                               required
                                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Satuan <span class="text-red-500">*</span>
                                        </label>

                                        <select name="satuan"
                                                id="satuan-rab-{{ $item->id }}"
                                                required
                                                onchange="toggleRabLainnya(this, 'satuan-rab-lainnya-{{ $item->id }}')"
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                            @foreach ($daftarSatuan as $satuan)
                                                <option value="{{ $satuan }}"
                                                    {{ !$satuanKustom && $item->satuan === $satuan ? 'selected' : '' }}>
                                                    {{ str_replace('_', ' ', ucfirst($satuan)) }}
                                                </option>
                                            @endforeach

                                            <option value="__lainnya__"
                                                {{ $satuanKustom ? 'selected' : '' }}>
                                                Lainnya
                                            </option>
                                        </select>

                                        <div id="satuan-rab-lainnya-{{ $item->id }}"
                                             class="{{ $satuanKustom ? '' : 'hidden' }} mt-3">

                                            <input type="text"
                                                   name="satuan_lainnya"
                                                   value="{{ $satuanKustom ? $item->satuan : '' }}"
                                                   maxlength="100"
                                                   {{ $satuanKustom ? 'required' : '' }}
                                                   placeholder="Masukkan satuan lainnya"
                                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>

                                </div>

                                {{-- HARGA SATUAN --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Harga Satuan (Rp) <span class="text-red-500">*</span>
                                    </label>

                                    <input type="number"
                                           name="harga_satuan"
                                           value="{{ $item->harga_satuan }}"
                                           min="0"
                                           step="0.01"
                                           required
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- SUMBER DANA --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Sumber Dana <span class="text-red-500">*</span>
                                    </label>

                                    <input type="text"
                                           name="sumber_dana"
                                           value="{{ $item->sumber_dana }}"
                                           maxlength="100"
                                           required
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div class="flex justify-end gap-3">
                                    <button type="button"
                                            onclick="document.getElementById('edit-rab-{{ $item->id }}').classList.add('hidden')"
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
                        Belum ada rencana anggaran biaya.
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        Anggaran yang ditambahkan akan ditampilkan di sini.
                    </p>
                </div>

            @endforelse
        </div>

        {{-- TOTAL ANGGARAN --}}
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
            <p class="text-sm text-blue-700">
                Total Rencana Anggaran
            </p>

            <p class="text-2xl font-bold text-blue-800 mt-1">
                Rp {{ number_format((float) $totalRab, 0, ',', '.') }}
            </p>
        </div>

    </div>
</div>

{{-- SCRIPT INPUT LAINNYA --}}
<script>
    function toggleRabLainnya(select, targetId) {
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
