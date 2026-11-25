import 'dart:async';

import 'package:flutter/material.dart';

import '../../core/services/auth_service.dart';
import '../../core/services/kegiatan_service.dart';
import '../../layouts/main_layout.dart';
import 'form_kegiatan_screen.dart';

class KegiatanScreen extends StatefulWidget {
  final Map<String, dynamic> pengguna;

  const KegiatanScreen({super.key, required this.pengguna});

  @override
  State<KegiatanScreen> createState() => _KegiatanScreenState();
}

class _KegiatanScreenState extends State<KegiatanScreen> {
  bool _isLoading = true;
  String? _errorMessage;

  List<Map<String, dynamic>> _daftarKegiatan = [];

  int _currentPage = 1;
  int _lastPage = 1;
  int _totalKegiatan = 0;
  int _requestVersion = 0;
  String _roleAktual = '';
  late final TextEditingController _namaFilterController;
  DateTime? _tanggalDibuatFilter;
  String _statusFilter = 'semua';
  Timer? _filterDebounce;

  bool get _bolehKelola => _roleAktual == 'warga_pj_kegiatan_dan_jadwal_ronda';
  bool get _adaFilter =>
      _namaFilterController.text.trim().isNotEmpty ||
      _tanggalDibuatFilter != null ||
      _statusFilter != 'semua';

  @override
  void initState() {
    super.initState();
    _namaFilterController = TextEditingController();
    _ambilDataKegiatan();
  }

  @override
  void dispose() {
    _filterDebounce?.cancel();
    _namaFilterController.dispose();
    super.dispose();
  }

  Future<void> _ambilDataKegiatan({int? page}) async {
    final halaman = page ?? _currentPage;
    final requestVersion = ++_requestVersion;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
      _roleAktual = '';
    });

    try {
      final profil = await AuthService.getProfil();
      final role = profil['role']?.toString();
      if (role == null || role.isEmpty) {
        throw Exception('Role pengguna tidak ditemukan.');
      }

      if (!mounted || requestVersion != _requestVersion) return;
      setState(() => _roleAktual = role);

      final namaKegiatan = _namaFilterController.text.trim();
      final tanggalDibuat = _tanggalDibuatFilter == null
          ? null
          : _formatTanggalQuery(_tanggalDibuatFilter!);
      final status = _statusFilter == 'semua' ? null : _statusFilter;
      final response = switch (role) {
        'ketua_rt' => await KegiatanService.getKegiatanKetuaRT(
          page: halaman,
          namaKegiatan: namaKegiatan,
          tanggalDibuat: tanggalDibuat,
        ),
        'warga_pj_kegiatan_dan_jadwal_ronda' =>
          await KegiatanService.getKegiatanWargaPJ(
            page: halaman,
            namaKegiatan: namaKegiatan,
            tanggalDibuat: tanggalDibuat,
            status: status,
          ),
        'warga_biasa' => await KegiatanService.getKegiatanWarga(
          page: halaman,
          namaKegiatan: namaKegiatan,
          tanggalDibuat: tanggalDibuat,
        ),
        _ => throw Exception('Role pengguna tidak memiliki akses kegiatan.'),
      };

      final data = response['data'];
      final meta = response['meta'];

      if (data is! List) {
        throw Exception('Format data kegiatan tidak valid.');
      }

      final List<Map<String, dynamic>> hasil = data
          .whereType<Map>()
          .map((item) => Map<String, dynamic>.from(item))
          .toList();

      final metadata = meta is Map
          ? Map<String, dynamic>.from(meta)
          : <String, dynamic>{};

      if (!mounted || requestVersion != _requestVersion) return;

      setState(() {
        _daftarKegiatan = hasil;
        _currentPage =
            int.tryParse(metadata['current_page']?.toString() ?? '') ?? halaman;
        _lastPage = int.tryParse(metadata['last_page']?.toString() ?? '') ?? 1;
        _totalKegiatan =
            int.tryParse(metadata['total']?.toString() ?? '') ?? hasil.length;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted || requestVersion != _requestVersion) return;

      setState(() {
        _errorMessage = e.toString().replaceFirst('Exception: ', '');
        _isLoading = false;
      });
    }
  }

  Future<void> _refreshData() async {
    await _ambilDataKegiatan(page: _currentPage);
  }

  String _formatTanggalQuery(DateTime tanggal) =>
      '${tanggal.year.toString().padLeft(4, '0')}-'
      '${tanggal.month.toString().padLeft(2, '0')}-'
      '${tanggal.day.toString().padLeft(2, '0')}';

  void _jadwalkanPencarianNama(String _) {
    _filterDebounce?.cancel();
    setState(() {});
    _filterDebounce = Timer(const Duration(milliseconds: 350), () {
      if (mounted) _ambilDataKegiatan(page: 1);
    });
  }

  Future<void> _pilihTanggalDibuat() async {
    final tanggal = await showDatePicker(
      context: context,
      initialDate: _tanggalDibuatFilter ?? DateTime.now(),
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (tanggal == null || !mounted) return;

    _filterDebounce?.cancel();
    setState(() {
      _tanggalDibuatFilter = DateTime(tanggal.year, tanggal.month, tanggal.day);
    });
    await _ambilDataKegiatan(page: 1);
  }

  Future<void> _hapusFilterTanggal() async {
    _filterDebounce?.cancel();
    setState(() => _tanggalDibuatFilter = null);
    await _ambilDataKegiatan(page: 1);
  }

  Future<void> _bersihkanFilter() async {
    _filterDebounce?.cancel();
    _namaFilterController.clear();
    setState(() {
      _tanggalDibuatFilter = null;
      _statusFilter = 'semua';
    });
    await _ambilDataKegiatan(page: 1);
  }

  Widget _buildFilter() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          children: [
            TextField(
              controller: _namaFilterController,
              onChanged: _jadwalkanPencarianNama,
              decoration: InputDecoration(
                labelText: 'Nama kegiatan',
                hintText: 'Cari nama kegiatan',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _namaFilterController.text.isEmpty
                    ? null
                    : IconButton(
                        tooltip: 'Hapus pencarian',
                        onPressed: () {
                          _namaFilterController.clear();
                          _jadwalkanPencarianNama('');
                        },
                        icon: const Icon(Icons.close),
                      ),
                border: const OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _pilihTanggalDibuat,
                    icon: const Icon(Icons.calendar_today_outlined),
                    label: Text(
                      _tanggalDibuatFilter == null
                          ? 'Semua tanggal dibuat'
                          : 'Dibuat: ${_formatTanggal(_formatTanggalQuery(_tanggalDibuatFilter!))}',
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ),
                if (_tanggalDibuatFilter != null)
                  IconButton(
                    tooltip: 'Hapus filter tanggal',
                    onPressed: _hapusFilterTanggal,
                    icon: const Icon(Icons.close),
                  ),
              ],
            ),
            if (_bolehKelola) ...[
              const SizedBox(height: 10),
              DropdownButtonFormField<String>(
                initialValue: _statusFilter,
                isExpanded: true,
                decoration: const InputDecoration(
                  labelText: 'Filter status',
                  border: OutlineInputBorder(),
                ),
                items: const [
                  DropdownMenuItem(value: 'semua', child: Text('Semua Status')),
                  DropdownMenuItem(
                    value: 'disetujui',
                    child: Text('Disetujui Aktif'),
                  ),
                  DropdownMenuItem(
                    value: 'menunggu_persetujuan',
                    child: Text('Menunggu Persetujuan Aktif'),
                  ),
                  DropdownMenuItem(value: 'draft', child: Text('Draft Aktif')),
                  DropdownMenuItem(value: 'nonaktif', child: Text('Nonaktif')),
                ],
                onChanged: (status) {
                  if (status == null) return;
                  _filterDebounce?.cancel();
                  setState(() => _statusFilter = status);
                  _ambilDataKegiatan(page: 1);
                },
              ),
            ],
            if (_adaFilter)
              Align(
                alignment: Alignment.centerRight,
                child: TextButton.icon(
                  onPressed: _bersihkanFilter,
                  icon: const Icon(Icons.filter_alt_off_outlined),
                  label: const Text('Bersihkan filter'),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Future<void> _bukaDetail(Map<String, dynamic> kegiatan) async {
    final id = int.tryParse(kegiatan['id']?.toString() ?? '');

    if (id == null) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('ID kegiatan tidak valid.')));
      return;
    }

    await Navigator.pushNamed(
      context,
      '/detail-kegiatan',
      arguments: {
        'kegiatanId': id,
        'pengguna': {...widget.pengguna, 'role': _roleAktual},
      },
    );
    if (mounted) await _ambilDataKegiatan(page: _currentPage);
  }

  Future<void> _bukaFormKegiatan({Map<String, dynamic>? kegiatan}) async {
    final tersimpan = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => FormKegiatanScreen(kegiatan: kegiatan)),
    );
    if (tersimpan == true && mounted) {
      await _ambilDataKegiatan(page: _currentPage);
    }
  }

  Future<void> _nonaktifkanKegiatan(Map<String, dynamic> kegiatan) async {
    final id = int.tryParse(kegiatan['id']?.toString() ?? '');
    if (id == null) return;

    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Nonaktifkan kegiatan?'),
        content: const Text('Kegiatan akan disembunyikan dari warga.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Nonaktifkan'),
          ),
        ],
      ),
    );
    if (konfirmasi != true) return;

    try {
      await KegiatanService.nonaktifkanKegiatan(id);
      if (!mounted) return;
      _tampilkanPesan('Kegiatan berhasil dinonaktifkan.');
      await _ambilDataKegiatan(page: _currentPage);
    } catch (e) {
      if (!mounted) return;
      _tampilkanPesan(
        e.toString().replaceFirst('Exception: ', ''),
        error: true,
      );
    }
  }

  Future<void> _aktifkanKegiatan(Map<String, dynamic> kegiatan) async {
    final id = int.tryParse(kegiatan['id']?.toString() ?? '');
    if (id == null) return;

    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Aktifkan kembali kegiatan?'),
        content: const Text(
          'Kegiatan akan kembali menjadi draft dan perlu diajukan ulang '
          'untuk persetujuan.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Aktifkan'),
          ),
        ],
      ),
    );
    if (konfirmasi != true) return;

    try {
      await KegiatanService.aktifkanKegiatan(id);
      if (!mounted) return;
      _tampilkanPesan(
        'Kegiatan diaktifkan kembali sebagai draft. Ajukan ulang untuk persetujuan.',
      );
      await _ambilDataKegiatan(page: _currentPage);
    } catch (e) {
      if (!mounted) return;
      _tampilkanPesan(
        e.toString().replaceFirst('Exception: ', ''),
        error: true,
      );
    }
  }

  Future<void> _ajukanKegiatan(Map<String, dynamic> kegiatan) async {
    final id = int.tryParse(kegiatan['id']?.toString() ?? '');
    if (id == null) return;

    try {
      await KegiatanService.ajukanKegiatan(id);
      if (!mounted) return;
      _tampilkanPesan('Kegiatan berhasil diajukan untuk persetujuan.');
      await _ambilDataKegiatan(page: _currentPage);
    } catch (e) {
      if (!mounted) return;
      _tampilkanPesan(
        e.toString().replaceFirst('Exception: ', ''),
        error: true,
      );
    }
  }

  void _tampilkanPesan(String pesan, {bool error = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(pesan),
        backgroundColor: error ? Colors.red : null,
      ),
    );
  }

  String _formatTanggal(String? tanggal) {
    if (tanggal == null || tanggal.isEmpty) {
      return '-';
    }

    final parsed = DateTime.tryParse(tanggal);

    if (parsed == null) {
      return tanggal;
    }

    const namaBulan = [
      'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember',
    ];

    return '${parsed.day} ${namaBulan[parsed.month - 1]} ${parsed.year}';
  }

  String _labelStatus(String? status) {
    switch (status) {
      case 'draft':
        return 'Draft';
      case 'menunggu_persetujuan':
        return 'Menunggu Persetujuan';
      case 'disetujui':
        return 'Disetujui';
      case 'ditolak':
        return 'Ditolak';
      case 'selesai':
        return 'Selesai';
      case 'nonaktif':
        return 'Nonaktif';
      default:
        return status ?? 'Tidak diketahui';
    }
  }

  Color _warnaStatus(String? status) {
    switch (status) {
      case 'draft':
        return Colors.grey;
      case 'menunggu_persetujuan':
        return Colors.orange;
      case 'disetujui':
        return Colors.green;
      case 'ditolak':
        return Colors.red;
      case 'selesai':
        return Colors.blue;
      case 'nonaktif':
        return Colors.grey.shade700;
      default:
        return Colors.grey;
    }
  }

  Widget _buildStatus(String? status) {
    final warna = _warnaStatus(status);
    final tampilkanLabelAktif = const {
      'draft',
      'menunggu_persetujuan',
      'disetujui',
    }.contains(status);

    return Wrap(
      spacing: 6,
      runSpacing: 6,
      children: [
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
          decoration: BoxDecoration(
            color: warna.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(20),
          ),
          child: Text(
            _labelStatus(status),
            style: TextStyle(
              color: warna,
              fontSize: 11,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
        if (tampilkanLabelAktif)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: Colors.green.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              'Aktif',
              style: TextStyle(
                color: Colors.green.shade700,
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.primary,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          const Icon(Icons.event_note_outlined, color: Colors.white, size: 32),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Data Kegiatan',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Total $_totalKegiatan kegiatan terdaftar',
                  style: const TextStyle(color: Colors.white, fontSize: 12),
                ),
              ],
            ),
          ),
          const Icon(Icons.groups_outlined, color: Colors.white, size: 28),
          if (_bolehKelola)
            IconButton(
              tooltip: 'Tambah Kegiatan',
              onPressed: () => _bukaFormKegiatan(),
              color: Colors.white,
              icon: const Icon(Icons.add_circle_outline),
            ),
        ],
      ),
    );
  }

  Widget _buildKartuKegiatan(Map<String, dynamic> kegiatan) {
    final nama = kegiatan['nama_kegiatan']?.toString() ?? '-';
    final jenis = kegiatan['jenis_kegiatan']?.toString() ?? '-';
    final lokasi = kegiatan['lokasi']?.toString() ?? '-';
    final tanggal = _formatTanggal(kegiatan['tanggal_mulai']?.toString());
    final status = kegiatan['status']?.toString();

    return Card(
      elevation: 1,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: Colors.grey.shade200),
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.primary
                    .withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                Icons.event_outlined,
                color: Theme.of(context).colorScheme.primary,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    nama,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(
                        Icons.category_outlined,
                        size: 14,
                        color: Colors.grey.shade600,
                      ),
                      const SizedBox(width: 5),
                      Expanded(
                        child: Text(
                          jenis,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade700,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 5),
                  Row(
                    children: [
                      Icon(
                        Icons.calendar_today_outlined,
                        size: 14,
                        color: Colors.grey.shade600,
                      ),
                      const SizedBox(width: 5),
                      Expanded(
                        child: Text(
                          tanggal,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade700,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 5),
                  Row(
                    children: [
                      Icon(
                        Icons.location_on_outlined,
                        size: 14,
                        color: Colors.grey.shade600,
                      ),
                      const SizedBox(width: 5),
                      Expanded(
                        child: Text(
                          lokasi,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade700,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  _buildStatus(status),
                ],
              ),
            ),
            const SizedBox(width: 4),
            IconButton(
              tooltip: 'Lihat detail',
              onPressed: () => _bukaDetail(kegiatan),
              icon: const Icon(Icons.visibility_outlined, size: 21),
              color: Theme.of(context).colorScheme.primary,
            ),
            if (_bolehKelola) ...[
              if (status != 'selesai' && status != 'nonaktif')
                IconButton(
                  tooltip: 'Edit kegiatan',
                  onPressed: () => _bukaFormKegiatan(kegiatan: kegiatan),
                  icon: const Icon(Icons.edit_outlined, size: 21),
                  color: Theme.of(context).colorScheme.primary,
                ),
              if (status == 'draft' || status == 'ditolak')
                IconButton(
                  tooltip: 'Ajukan persetujuan',
                  onPressed: () => _ajukanKegiatan(kegiatan),
                  icon: const Icon(Icons.send_outlined, size: 21),
                  color: Colors.green.shade700,
                ),
              if (status == 'nonaktif')
                IconButton(
                  tooltip: 'Aktifkan kegiatan',
                  onPressed: () => _aktifkanKegiatan(kegiatan),
                  icon: const Icon(Icons.block_outlined, size: 21),
                  color: Colors.grey.shade700,
                )
              else
                IconButton(
                  tooltip: 'Nonaktifkan kegiatan',
                  onPressed: () => _nonaktifkanKegiatan(kegiatan),
                  icon: const Icon(Icons.check_circle_outline, size: 21),
                  color: Colors.green.shade700,
                ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildPagination() {
    if (_lastPage <= 1) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          IconButton(
            tooltip: 'Halaman sebelumnya',
            onPressed: _currentPage > 1 && !_isLoading
                ? () => _ambilDataKegiatan(page: _currentPage - 1)
                : null,
            icon: const Icon(Icons.chevron_left),
          ),
          Text(
            'Halaman $_currentPage dari $_lastPage',
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500),
          ),
          IconButton(
            tooltip: 'Halaman berikutnya',
            onPressed: _currentPage < _lastPage && !_isLoading
                ? () => _ambilDataKegiatan(page: _currentPage + 1)
                : null,
            icon: const Icon(Icons.chevron_right),
          ),
        ],
      ),
    );
  }

  Widget _buildKonten() {
    if (_isLoading && _daftarKegiatan.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null && _daftarKegiatan.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, size: 42, color: Colors.red),
              const SizedBox(height: 12),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13),
              ),
              const SizedBox(height: 12),
              ElevatedButton.icon(
                onPressed: () => _ambilDataKegiatan(),
                icon: const Icon(Icons.refresh),
                label: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (_daftarKegiatan.isEmpty) {
      return RefreshIndicator(
        onRefresh: _refreshData,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          children: [
            _buildHeader(),
            const SizedBox(height: 24),
            _buildFilter(),
            const SizedBox(height: 16),
            const Icon(Icons.event_busy_outlined, size: 52, color: Colors.grey),
            const SizedBox(height: 12),
            Center(
              child: Text(
                _adaFilter
                    ? 'Tidak ada kegiatan yang sesuai filter.'
                    : 'Belum ada kegiatan.',
                style: const TextStyle(color: Colors.grey, fontSize: 13),
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _refreshData,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.only(bottom: 16),
        children: [
          _buildHeader(),
          const SizedBox(height: 16),
          _buildFilter(),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Daftar Kegiatan',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
              ),
              Text(
                '$_totalKegiatan kegiatan',
                style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ..._daftarKegiatan.map(_buildKartuKegiatan),
          _buildPagination(),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return MainLayout(
      title: 'Kegiatan',
      selectedMenu: 'Kegiatan',
      pengguna: widget.pengguna,
      child: Column(
        children: [
          if (_isLoading && _daftarKegiatan.isNotEmpty)
            const LinearProgressIndicator(),
          if (_errorMessage != null && _daftarKegiatan.isNotEmpty)
            Material(
              color: Colors.red.shade50,
              child: Padding(
                padding: const EdgeInsets.all(10),
                child: Row(
                  children: [
                    const Icon(
                      Icons.error_outline,
                      color: Colors.red,
                      size: 18,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        _errorMessage!,
                        style: const TextStyle(fontSize: 12, color: Colors.red),
                      ),
                    ),
                    IconButton(
                      onPressed: () => _ambilDataKegiatan(),
                      icon: const Icon(Icons.refresh, size: 18),
                    ),
                  ],
                ),
              ),
            ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: _buildKonten(),
            ),
          ),
        ],
      ),
    );
  }
}
