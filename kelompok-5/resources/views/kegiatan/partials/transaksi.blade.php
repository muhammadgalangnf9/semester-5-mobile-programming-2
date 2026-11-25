{{-- PARTIAL TRANSAKSI KEGIATAN --}}

@php
    $pengguna = Auth::user();

    $bolehKelola = (int) $pengguna->role_id === 2;

    $kategoriPemasukan = [
        'Iuran Warga',
        'Donasi',
        'Sponsor',
        'Kas RT',
    ];

    $kategoriPengeluaran = [
        'Konsumsi',
        'Perlengkapan',
        'Dekorasi',
        'Hadiah',
        'Dokumentasi',
        'Transportasi',
    ];

    $totalPemasukan = $kegiatan->transaksi
        ->where('jenis_transaksi', 'pemasukan')
        ->sum('nominal');

    $totalPengeluaran = $kegiatan->transaksi
        ->where('jenis_transaksi', 'pengeluaran')
        ->sum('nominal');

    $saldo = $totalPemasukan - $totalPengeluaran;
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

    {{-- HEADER --}}
    <div class="p-6 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    Transaksi Kegiatan
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Catatan pemasukan dan pengeluaran aktual kegiatan.
                </p>
            </div>

            <span class="text-sm text-gray-500">
                {{ $kegiatan->transaksi->count() }} Transaksi
            </span>
        </div>
    </div>

    <div class="p-6 space-y-6">

        {{-- RINGKASAN KEUANGAN --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

            <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                <p class="text-xs text-green-700">Total Pemasukan</p>

                <p class="text-lg font-bold text-green-800 mt-2">
                    Rp {{ number_format((float) $totalPemasukan, 0, ',', '.') }}
                </p>
            </div>

            <div class="bg-red-50 border border-red-100 rounded-xl p-4">
                <p class="text-xs text-red-700">Total Pengeluaran</p>

                <p class="text-lg font-bold text-red-800 mt-2">
                    Rp {{ number_format((float) $totalPengeluaran, 0, ',', '.') }}
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                <p class="text-xs text-blue-700">Saldo Kegiatan</p>

                <p class="text-lg font-bold text-blue-800 mt-2">
                    Rp {{ number_format((float) $saldo, 0, ',', '.') }}
                </p>
            </div>

        </div>

        {{-- FORM TAMBAH TRANSAKSI --}}
        @if ($bolehKelola)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">

                <h4 class="font-semibold text-gray-800 mb-4">
                    Tambah Transaksi
                </h4>

                <form action="{{ route('kegiatan.transaksi.store', $kegiatan->id) }}"
                      method="POST"
                      class="space-y-4">

                    @csrf

                    {{-- JENIS TRANSAKSI --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Transaksi <span class="text-red-500">*</span>
                        </label>

                        <select name="jenis_transaksi"
                                id="jenis-transaksi-baru"
                                required
                                onchange="ubahKategoriTransaksi(this, 'kategori-transaksi-baru', 'kategori-lainnya-baru')"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            <option value="">Pilih Jenis Transaksi</option>

                            <option value="pemasukan"
                                {{ old('jenis_transaksi') === 'pemasukan' ? 'selected' : '' }}>
                                Pemasukan
                            </option>

                            <option value="pengeluaran"
                                {{ old('jenis_transaksi') === 'pengeluaran' ? 'selected' : '' }}>
                                Pengeluaran
                            </option>
                        </select>
                    </div>

                    {{-- KATEGORI --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori <span class="text-red-500">*</span>
                        </label>

                        <select name="kategori"
                                id="kategori-transaksi-baru"
                                required
                                onchange="toggleKategoriLainnya(this, 'kategori-lainnya-baru')"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            <option value="">Pilih Jenis Transaksi Terlebih Dahulu</option>

                            @foreach ($kategoriPemasukan as $kategori)
                                <option value="{{ $kategori }}"
                                        data-jenis="pemasukan"
                                        {{ old('kategori') === $kategori ? 'selected' : '' }}>
                                    {{ $kategori }}
                                </option>
                            @endforeach

                            <option value="__lainnya__"
                                    data-jenis="pemasukan"
                                    {{ old('kategori') === '__lainnya__' && old('jenis_transaksi') === 'pemasukan' ? 'selected' : '' }}>
                                Lainnya
                            </option>

                            @foreach ($kategoriPengeluaran as $kategori)
                                <option value="{{ $kategori }}"
                                        data-jenis="pengeluaran"
                                        {{ old('kategori') === $kategori ? 'selected' : '' }}>
                                    {{ $kategori }}
                                </option>
                            @endforeach

                            <option value="__lainnya__"
                                    data-jenis="pengeluaran"
                                    {{ old('kategori') === '__lainnya__' && old('jenis_transaksi') === 'pengeluaran' ? 'selected' : '' }}>
                                Lainnya
                            </option>

                        </select>

                        <div id="kategori-lainnya-baru"
                             class="{{ old('kategori') === '__lainnya__' ? '' : 'hidden' }} mt-3">

                            <input type="text"
                                   name="kategori_lainnya"
                                   value="{{ old('kategori_lainnya') }}"
                                   maxlength="100"
                                   {{ old('kategori') === '__lainnya__' ? 'required' : '' }}
                                   placeholder="Masukkan kategori transaksi"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- TANGGAL --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Transaksi <span class="text-red-500">*</span>
                        </label>

                        <input type="date"
                               name="tanggal_transaksi"
                               value="{{ old('tanggal_transaksi', now()->format('Y-m-d')) }}"
                               required
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- NOMINAL --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nominal (Rp) <span class="text-red-500">*</span>
                        </label>

                        <input type="number"
                               name="nominal"
                               value="{{ old('nominal') }}"
                               min="0.01"
                               step="0.01"
                               required
                               placeholder="Masukkan nominal transaksi"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- KETERANGAN --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Keterangan
                        </label>

                        <textarea name="keterangan"
                                  rows="3"
                                  placeholder="Tambahkan keterangan transaksi"
                                  class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('keterangan') }}</textarea>
                    </div>

                    {{-- LINK BUKTI --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Link Bukti Transaksi
                        </label>

                        <input type="url"
                               name="link_bukti"
                               value="{{ old('link_bukti') }}"
                               maxlength="500"
                               placeholder="https://drive.google.com/..."
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                        <p class="text-xs text-gray-500 mt-1">
                            Masukkan tautan bukti transaksi dari Google Drive jika tersedia.
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                            Simpan Transaksi
                        </button>
                    </div>

                </form>
            </div>
        @endif

        {{-- DAFTAR TRANSAKSI --}}
        <div>
            <h4 class="font-semibold text-gray-800 mb-4">
                Riwayat Transaksi
            </h4>

            @forelse ($kegiatan->transaksi->sortByDesc('tanggal_transaksi') as $item)

                @php
                    $daftarKategoriItem = $item->jenis_transaksi === 'pemasukan'
                        ? $kategoriPemasukan
                        : $kategoriPengeluaran;

                    $kategoriKustom = !in_array(
                        $item->kategori,
                        $daftarKategoriItem
                    );
                @endphp

                <div class="border border-gray-200 rounded-xl p-5 mb-3">

                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

                        <div class="flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <h5 class="font-semibold text-gray-800">
                                    {{ $item->kategori }}
                                </h5>

                                @if ($item->jenis_transaksi === 'pemasukan')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Pemasukan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        Pengeluaran
                                    </span>
                                @endif

                            </div>

                            <p class="text-xs text-gray-500 mt-2">
                                {{ \Carbon\Carbon::parse($item->tanggal_transaksi)->translatedFormat('d F Y') }}
                            </p>

                            <p class="text-lg font-bold mt-3 {{ $item->jenis_transaksi === 'pemasukan' ? 'text-green-700' : 'text-red-700' }}">
                                {{ $item->jenis_transaksi === 'pemasukan' ? '+' : '-' }}
                                Rp {{ number_format((float) $item->nominal, 0, ',', '.') }}
                            </p>

                            @if ($item->keterangan)
                                <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">
                                    {{ $item->keterangan }}
                                </p>
                            @endif

                            @if ($item->link_bukti)
                                <a href="{{ $item->link_bukti }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-block mt-3 text-sm text-blue-600 hover:text-blue-800">
                                    Lihat Bukti Transaksi
                                </a>
                            @endif

                        </div>

                        @if ($bolehKelola)
                            <div class="flex items-center gap-3">

                                <button type="button"
                                        onclick="document.getElementById('edit-transaksi-{{ $item->id }}').classList.toggle('hidden')"
                                        class="text-sm font-medium text-yellow-600 hover:text-yellow-700">
                                    Edit
                                </button>

                                <form action="{{ route('kegiatan.transaksi.destroy', [$kegiatan->id, $item->id]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">

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

                    {{-- FORM EDIT TRANSAKSI --}}
                    @if ($bolehKelola)
                        <div id="edit-transaksi-{{ $item->id }}"
                             class="hidden mt-5 pt-5 border-t border-gray-200">

                            <h6 class="font-semibold text-gray-800 mb-4">
                                Edit Transaksi
                            </h6>

                            <form action="{{ route('kegiatan.transaksi.update', [$kegiatan->id, $item->id]) }}"
                                  method="POST"
                                  class="space-y-4">

                                @csrf
                                @method('PUT')

                                {{-- JENIS TRANSAKSI --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Jenis Transaksi <span class="text-red-500">*</span>
                                    </label>

                                    <select name="jenis_transaksi"
                                            id="jenis-transaksi-{{ $item->id }}"
                                            required
                                            onchange="ubahKategoriTransaksi(this, 'kategori-transaksi-{{ $item->id }}', 'kategori-lainnya-{{ $item->id }}')"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                        <option value="pemasukan"
                                            {{ $item->jenis_transaksi === 'pemasukan' ? 'selected' : '' }}>
                                            Pemasukan
                                        </option>

                                        <option value="pengeluaran"
                                            {{ $item->jenis_transaksi === 'pengeluaran' ? 'selected' : '' }}>
                                            Pengeluaran
                                        </option>

                                    </select>
                                </div>

                                {{-- KATEGORI --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Kategori <span class="text-red-500">*</span>
                                    </label>

                                    <select name="kategori"
                                            id="kategori-transaksi-{{ $item->id }}"
                                            required
                                            onchange="toggleKategoriLainnya(this, 'kategori-lainnya-{{ $item->id }}')"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                        @foreach ($kategoriPemasukan as $kategori)
                                            <option value="{{ $kategori }}"
                                                    data-jenis="pemasukan"
                                                    {{ $item->jenis_transaksi === 'pemasukan' && $item->kategori === $kategori ? 'selected' : '' }}>
                                                {{ $kategori }}
                                            </option>
                                        @endforeach

                                        <option value="__lainnya__"
                                                data-jenis="pemasukan"
                                                {{ $item->jenis_transaksi === 'pemasukan' && $kategoriKustom ? 'selected' : '' }}>
                                            Lainnya
                                        </option>

                                        @foreach ($kategoriPengeluaran as $kategori)
                                            <option value="{{ $kategori }}"
                                                    data-jenis="pengeluaran"
                                                    {{ $item->jenis_transaksi === 'pengeluaran' && $item->kategori === $kategori ? 'selected' : '' }}>
                                                {{ $kategori }}
                                            </option>
                                        @endforeach

                                        <option value="__lainnya__"
                                                data-jenis="pengeluaran"
                                                {{ $item->jenis_transaksi === 'pengeluaran' && $kategoriKustom ? 'selected' : '' }}>
                                            Lainnya
                                        </option>

                                    </select>

                                    <div id="kategori-lainnya-{{ $item->id }}"
                                         class="{{ $kategoriKustom ? '' : 'hidden' }} mt-3">

                                        <input type="text"
                                               name="kategori_lainnya"
                                               value="{{ $kategoriKustom ? $item->kategori : '' }}"
                                               maxlength="100"
                                               {{ $kategoriKustom ? 'required' : '' }}
                                               placeholder="Masukkan kategori transaksi"
                                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                {{-- TANGGAL --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Transaksi <span class="text-red-500">*</span>
                                    </label>

                                    <input type="date"
                                           name="tanggal_transaksi"
                                           value="{{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('Y-m-d') }}"
                                           required
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- NOMINAL --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nominal (Rp) <span class="text-red-500">*</span>
                                    </label>

                                    <input type="number"
                                           name="nominal"
                                           value="{{ $item->nominal }}"
                                           min="0.01"
                                           step="0.01"
                                           required
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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

                                {{-- LINK BUKTI --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Link Bukti Transaksi
                                    </label>

                                    <input type="url"
                                           name="link_bukti"
                                           value="{{ $item->link_bukti }}"
                                           maxlength="500"
                                           placeholder="https://drive.google.com/..."
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div class="flex justify-end gap-3">

                                    <button type="button"
                                            onclick="document.getElementById('edit-transaksi-{{ $item->id }}').classList.add('hidden')"
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
                        Belum ada transaksi kegiatan.
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        Transaksi yang dicatat akan ditampilkan di sini.
                    </p>
                </div>

            @endforelse
        </div>

    </div>
</div>

{{-- SCRIPT KATEGORI TRANSAKSI --}}
<script>
    function toggleKategoriLainnya(select, targetId) {
        const container = document.getElementById(targetId);

        if (!container) return;

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

    function ubahKategoriTransaksi(selectJenis, idKategori, idLainnya) {
        const selectKategori = document.getElementById(idKategori);

        if (!selectKategori) return;

        const jenis = selectJenis.value;
        const pilihanSebelumnya = selectKategori.value;

        Array.from(selectKategori.options).forEach(option => {
            if (!option.dataset.jenis) return;

            const sesuaiJenis = option.dataset.jenis === jenis;

            option.hidden = !sesuaiJenis;
            option.disabled = !sesuaiJenis;
        });

        const pilihanValid = Array.from(selectKategori.options).some(option =>
            option.value === pilihanSebelumnya
            && option.dataset.jenis === jenis
        );

        if (!pilihanValid) {
            selectKategori.value = '';
        }

        toggleKategoriLainnya(selectKategori, idLainnya);
    }

    document.addEventListener('DOMContentLoaded', function () {

        const jenisBaru = document.getElementById('jenis-transaksi-baru');

        if (jenisBaru) {
            ubahKategoriTransaksi(
                jenisBaru,
                'kategori-transaksi-baru',
                'kategori-lainnya-baru'
            );
        }

        document.querySelectorAll('[id^="jenis-transaksi-"]').forEach(select => {

            if (select.id === 'jenis-transaksi-baru') return;

            const id = select.id.replace('jenis-transaksi-', '');

            ubahKategoriTransaksi(
                select,
                'kategori-transaksi-' + id,
                'kategori-lainnya-' + id
            );
        });

    });
</script>
