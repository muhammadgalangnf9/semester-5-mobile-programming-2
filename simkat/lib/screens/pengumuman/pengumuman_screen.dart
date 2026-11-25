import 'package:flutter/material.dart';

import '../../core/services/auth_service.dart';
import '../../core/services/pengumuman_service.dart';
import '../../layouts/main_layout.dart';
import 'detail_pengumuman_screen.dart';
import 'form_pengumuman_screen.dart';

class PengumumanScreen extends StatefulWidget {
  final Map<String, dynamic> pengguna;

  const PengumumanScreen({super.key, required this.pengguna});

  @override
  State<PengumumanScreen> createState() => _PengumumanScreenState();
}

class _PengumumanScreenState extends State<PengumumanScreen> {
  List<dynamic> _daftarPengumuman = [];

  bool _isLoading = true;
  String? _errorMessage;

  int _currentPage = 1;
  int _lastPage = 1;
  int _totalPengumuman = 0;
  String _roleAktual = '';

  bool get _bolehKelola => _roleAktual == 'warga_pj_kegiatan_dan_jadwal_ronda';
  bool get _bolehTinjau => _roleAktual == 'ketua_rt';

  @override
  void initState() {
    super.initState();
    _ambilDataPengumuman();
  }

  Future<void> _ambilDataPengumuman({int page = 1}) async {
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

      if (!mounted) return;
      setState(() => _roleAktual = role);

      final response = _bolehKelola
          ? await PengumumanService.getPengumumanKelola(page: page)
          : await PengumumanService.getPengumuman(role: role, page: page);

      if (!mounted) return;

      final data = response['data'] as List<dynamic>? ?? [];
      final meta = response['meta'] as Map<String, dynamic>? ?? {};

      setState(() {
        _daftarPengumuman = data;
        _currentPage = meta['current_page'] ?? 1;
        _lastPage = meta['last_page'] ?? 1;
        _totalPengumuman = meta['total'] ?? data.length;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _errorMessage = e.toString().replaceFirst('Exception: ', '');
        _isLoading = false;
      });
    }
  }

  Future<void> _bukaDetailPengumuman(int pengumumanId) async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => DetailPengumumanScreen(
          pengumumanId: pengumumanId,
          role: _roleAktual,
        ),
      ),
    );
    if (mounted) await _ambilDataPengumuman(page: _currentPage);
  }

  Future<void> _bukaFormPengumuman({Map<String, dynamic>? pengumuman}) async {
    final tersimpan = await Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => FormPengumumanScreen(pengumuman: pengumuman),
      ),
    );
    if (tersimpan == true && mounted) {
      await _ambilDataPengumuman(page: _currentPage);
    }
  }

  Future<void> _hapusPengumuman(Map<String, dynamic> pengumuman) async {
    final id = int.tryParse(pengumuman['id']?.toString() ?? '');
    if (id == null) return;

    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Nonaktifkan pengumuman?'),
        content: const Text(
          'Pengumuman tidak akan tampil lagi sebagai pengumuman aktif.',
        ),
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
      await PengumumanService.hapusPengumuman(id);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pengumuman berhasil dinonaktifkan.')),
      );
      await _ambilDataPengumuman(page: _currentPage);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _aktifkanPengumuman(Map<String, dynamic> pengumuman) async {
    final id = int.tryParse(pengumuman['id']?.toString() ?? '');
    if (id == null) return;

    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Aktifkan sebagai draft?'),
        content: const Text(
          'Pengumuman akan aktif kembali sebagai draft dan perlu diajukan '
          'untuk persetujuan Ketua RT sebelum diterbitkan.',
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
      await PengumumanService.ubahStatusPengumuman(id, 'draft');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pengumuman aktif kembali sebagai draft.'),
        ),
      );
      await _ambilDataPengumuman(page: _currentPage);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _ajukanPengumuman(Map<String, dynamic> pengumuman) async {
    final id = int.tryParse(pengumuman['id']?.toString() ?? '');
    if (id == null) return;

    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Ajukan persetujuan?'),
        content: const Text(
          'Pengumuman akan dikirim kepada Ketua RT untuk diperiksa sebelum '
          'diterbitkan.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Ajukan'),
          ),
        ],
      ),
    );
    if (konfirmasi != true) return;

    try {
      await PengumumanService.ajukanPersetujuanPengumuman(id);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pengumuman diajukan untuk persetujuan Ketua RT.'),
        ),
      );
      await _ambilDataPengumuman(page: _currentPage);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  String _formatTanggal(dynamic tanggal) {
    if (tanggal == null) return '-';

    try {
      final date = DateTime.parse(tanggal.toString()).toLocal();

      final hari = date.day.toString().padLeft(2, '0');
      final bulan = date.month.toString().padLeft(2, '0');
      final tahun = date.year.toString();

      return '$hari-$bulan-$tahun';
    } catch (_) {
      return '-';
    }
  }

  String _cuplikanIsi(String isi) {
    final teks = isi.replaceAll(RegExp(r'<[^>]*>'), '').trim();

    if (teks.length > 120) {
      return '${teks.substring(0, 120)}...';
    }

    return teks;
  }

  Widget _buildStatus(String status) {
    final label = switch (status) {
      'terbit' => 'Disetujui',
      'menunggu_persetujuan' => 'Menunggu Persetujuan',
      'ditolak' => 'Ditolak',
      'draft' => 'Draft',
      'nonaktif' => 'Nonaktif',
      _ => status,
    };
    final warna = switch (status) {
      'terbit' => Colors.green,
      'menunggu_persetujuan' => Colors.orange,
      'ditolak' => Colors.red,
      'draft' => Colors.grey,
      _ => Colors.grey.shade700,
    };
    final tampilkanLabelAktif = [
      'draft',
      'menunggu_persetujuan',
      'terbit',
    ].contains(status);

    Widget badge(String teks, Color warnaBadge) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: warnaBadge.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          teks,
          style: TextStyle(
            color: warnaBadge,
            fontSize: 11,
            fontWeight: FontWeight.w600,
          ),
        ),
      );
    }

    return Wrap(
      spacing: 6,
      runSpacing: 6,
      children: [
        badge(label, warna),
        if (tampilkanLabelAktif) badge('Aktif', Colors.green.shade700),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return MainLayout(
      title: 'Pengumuman',
      selectedMenu: 'Pengumuman',
      pengguna: widget.pengguna,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Daftar Pengumuman',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Informasi dan pemberitahuan lingkungan RT.',
                        style: TextStyle(color: Colors.grey, fontSize: 12),
                      ),
                    ],
                  ),
                ),
                if (_bolehKelola)
                  IconButton.filled(
                    tooltip: 'Tambah Pengumuman',
                    onPressed: () => _bukaFormPengumuman(),
                    icon: const Icon(Icons.add),
                  ),
              ],
            ),
            const SizedBox(height: 16),

            // Informasi Total Pengumuman
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.campaign_outlined,
                    color: Colors.blue.shade700,
                    size: 24,
                  ),
                  const SizedBox(width: 10),
                  Text(
                    'Total:',
                    style: TextStyle(
                      color: Colors.blue.shade800,
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(width: 6),
                  Text(
                    '$_totalPengumuman Pengumuman',
                    style: TextStyle(
                      color: Colors.blue.shade900,
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Daftar Pengumuman
            Expanded(child: _buildDaftarPengumuman()),

            // Navigasi Halaman
            if (!_isLoading && _errorMessage == null)
              Padding(
                padding: const EdgeInsets.only(top: 12),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Hal $_currentPage / $_lastPage',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade700,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    Row(
                      children: [
                        InkWell(
                          onTap: _currentPage > 1
                              ? () =>
                                    _ambilDataPengumuman(page: _currentPage - 1)
                              : null,
                          borderRadius: BorderRadius.circular(8),
                          child: Padding(
                            padding: const EdgeInsets.all(8),
                            child: Icon(
                              Icons.chevron_left,
                              size: 24,
                              color: _currentPage > 1
                                  ? Colors.blue.shade700
                                  : Colors.grey.shade300,
                            ),
                          ),
                        ),
                        const SizedBox(width: 4),
                        InkWell(
                          onTap: _currentPage < _lastPage
                              ? () =>
                                    _ambilDataPengumuman(page: _currentPage + 1)
                              : null,
                          borderRadius: BorderRadius.circular(8),
                          child: Padding(
                            padding: const EdgeInsets.all(8),
                            child: Icon(
                              Icons.chevron_right,
                              size: 24,
                              color: _currentPage < _lastPage
                                  ? Colors.blue.shade700
                                  : Colors.grey.shade300,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildDaftarPengumuman() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, color: Colors.red, size: 42),
            const SizedBox(height: 12),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13),
              ),
            ),
            const SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: () => _ambilDataPengumuman(page: _currentPage),
              icon: const Icon(Icons.refresh, size: 18),
              label: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (_daftarPengumuman.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.campaign_outlined, size: 48, color: Colors.grey),
            SizedBox(height: 12),
            Text(
              'Belum ada pengumuman.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => _ambilDataPengumuman(page: _currentPage),
      child: ListView.separated(
        itemCount: _daftarPengumuman.length,
        separatorBuilder: (_, _) => const SizedBox(height: 8),
        itemBuilder: (context, index) {
          final pengumuman = Map<String, dynamic>.from(
            _daftarPengumuman[index],
          );

          final id = int.tryParse(pengumuman['id'].toString());

          final judul = pengumuman['judul']?.toString() ?? '-';
          final isi = pengumuman['isi']?.toString() ?? '';
          final status = pengumuman['status']?.toString() ?? 'terbit';
          final tanggal = _formatTanggal(
            status == 'terbit'
                ? pengumuman['diterbitkan_pada']
                : pengumuman['updated_at'] ?? pengumuman['created_at'],
          );
          final labelTanggal = status == 'terbit'
              ? 'Diterbitkan'
              : status == 'menunggu_persetujuan'
              ? 'Diajukan'
              : 'Diperbarui';

          return Card(
            elevation: 0,
            margin: EdgeInsets.zero,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
              side: BorderSide(color: Colors.grey.shade200),
            ),
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Row(
                children: [
                  // Ikon Pengumuman
                  CircleAvatar(
                    radius: 20,
                    backgroundColor: Colors.blue.shade50,
                    child: Icon(
                      Icons.campaign_outlined,
                      color: Colors.blue.shade800,
                      size: 21,
                    ),
                  ),
                  const SizedBox(width: 12),

                  // Informasi Pengumuman
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          judul,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _cuplikanIsi(isi),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                          ),
                        ),
                        const SizedBox(height: 5),
                        Row(
                          children: [
                            Icon(
                              Icons.calendar_today_outlined,
                              size: 12,
                              color: Colors.grey.shade500,
                            ),
                            const SizedBox(width: 5),
                            Text(
                              '$labelTanggal: $tanggal',
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey.shade500,
                              ),
                            ),
                          ],
                        ),
                        if (_bolehKelola || _bolehTinjau) ...[
                          const SizedBox(height: 5),
                          _buildStatus(status),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(width: 6),

                  // Ikon Detail
                  IconButton(
                    tooltip: 'Lihat detail',
                    visualDensity: VisualDensity.compact,
                    constraints: const BoxConstraints(
                      minWidth: 36,
                      minHeight: 36,
                    ),
                    padding: EdgeInsets.zero,
                    onPressed: id == null
                        ? null
                        : () => _bukaDetailPengumuman(id),
                    icon: const Icon(Icons.visibility_outlined, size: 21),
                    color: MainLayout.primaryColor,
                  ),
                  if (_bolehKelola) ...[
                    IconButton(
                      tooltip: 'Edit pengumuman',
                      visualDensity: VisualDensity.compact,
                      constraints: const BoxConstraints(
                        minWidth: 36,
                        minHeight: 36,
                      ),
                      padding: EdgeInsets.zero,
                      onPressed: () =>
                          _bukaFormPengumuman(pengumuman: pengumuman),
                      icon: const Icon(Icons.edit_outlined, size: 21),
                      color: MainLayout.primaryColor,
                    ),
                    if (status == 'draft' || status == 'ditolak')
                      IconButton(
                        tooltip: 'Ajukan persetujuan',
                        visualDensity: VisualDensity.compact,
                        constraints: const BoxConstraints(
                          minWidth: 36,
                          minHeight: 36,
                        ),
                        padding: EdgeInsets.zero,
                        onPressed: () => _ajukanPengumuman(pengumuman),
                        icon: const Icon(Icons.send_outlined, size: 20),
                        color: Colors.green.shade700,
                      ),
                    IconButton(
                      tooltip: status == 'nonaktif'
                          ? 'Aktifkan pengumuman'
                          : 'Nonaktifkan pengumuman',
                      visualDensity: VisualDensity.compact,
                      constraints: const BoxConstraints(
                        minWidth: 36,
                        minHeight: 36,
                      ),
                      padding: EdgeInsets.zero,
                      onPressed: status == 'nonaktif'
                          ? () => _aktifkanPengumuman(pengumuman)
                          : () => _hapusPengumuman(pengumuman),
                      icon: Icon(
                        status == 'nonaktif'
                            ? Icons.block_outlined
                            : Icons.check_circle_outline,
                        size: 21,
                        color: status == 'nonaktif'
                            ? Colors.grey.shade700
                            : Colors.green.shade700,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
