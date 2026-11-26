import 'package:flutter/material.dart';

import '../../core/services/kelola_warga_service.dart';
import '../../layouts/main_layout.dart';
import 'tambah_warga_screen.dart';
import 'edit_warga_screen.dart';
import 'detail_warga_screen.dart';

class KelolaWargaScreen extends StatefulWidget {
  final Map<String, dynamic> pengguna;

  const KelolaWargaScreen({super.key, required this.pengguna});

  @override
  State<KelolaWargaScreen> createState() => _KelolaWargaScreenState();
}

class _KelolaWargaScreenState extends State<KelolaWargaScreen> {
  final TextEditingController _searchController = TextEditingController();

  List<dynamic> _daftarWarga = [];
  List<String> _daftarBlok = [];
  Map<String, dynamic>? _ketuaRT;
  final Set<int> _statusSedangDiubah = {};

  bool _isLoading = true;
  String? _errorMessage;

  String? _filterGang;
  String? _filterBlok;
  String? _filterStatus;
  String? _filterRole;

  int _currentPage = 1;
  int _lastPage = 1;
  int _totalWarga = 0;

  static const List<String> _daftarGang = [
    'Gang E6',
    'Gang E4/E5',
    'Gang E2/E3',
    'Gang C16/E1',
  ];

  @override
  void initState() {
    super.initState();
    _ambilDataWarga();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _ambilDataWarga({int page = 1}) async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await KelolaWargaService.getWarga(
        search: _searchController.text.trim(),
        gang: _filterGang,
        blok: _filterBlok,
        status: _filterStatus,
        role: _filterRole,
        page: page,
      );

      if (!mounted) return;

      final data = response['data'] as List<dynamic>? ?? [];
      final meta = response['meta'] as Map<String, dynamic>? ?? {};
      final ketuaRTData = response['ketua_rt'];
      final daftarBlok = (response['daftar_blok'] as List<dynamic>? ?? [])
          .map((blok) => blok.toString())
          .toList();

      setState(() {
        _daftarWarga = data;
        _daftarBlok = daftarBlok;
        _ketuaRT = ketuaRTData is Map<String, dynamic>
            ? Map<String, dynamic>.from(ketuaRTData)
            : null;
        _currentPage = meta['current_page'] ?? 1;
        _lastPage = meta['last_page'] ?? 1;
        _totalWarga = meta['total'] ?? data.length;
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

  Future<void> _bukaTambahWarga() async {
    final berhasil = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => const TambahWargaScreen()),
    );

    if (berhasil == true && mounted) {
      await _ambilDataWarga();
    }
  }

  Future<void> _bukaDetailWarga(int wargaId) async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => DetailWargaScreen(wargaId: wargaId)),
    );
  }

  Future<void> _bukaEditWarga(int wargaId) async {
    final berhasil = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => EditWargaScreen(wargaId: wargaId)),
    );

    if (berhasil == true && mounted) {
      await _ambilDataWarga(page: _currentPage);
    }
  }

  Future<void> _ubahStatusWarga(Map<String, dynamic> warga) async {
    final id = int.tryParse(warga['id'].toString());
    if (id == null) return;

    final statusAktif = warga['status']?.toString() == 'aktif';
    final nama = warga['nama']?.toString() ?? 'warga ini';
    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(statusAktif ? 'Nonaktifkan warga?' : 'Aktifkan warga?'),
        content: Text(
          'Yakin ingin ${statusAktif ? 'menonaktifkan' : 'mengaktifkan'} akun $nama?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text(statusAktif ? 'Nonaktifkan' : 'Aktifkan'),
          ),
        ],
      ),
    );

    if (konfirmasi != true || !mounted) return;

    setState(() {
      _statusSedangDiubah.add(id);
    });

    try {
      final response = await KelolaWargaService.ubahStatusWarga(id);
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            response['pesan']?.toString() ?? 'Status warga berhasil diubah.',
          ),
          backgroundColor: Colors.green,
        ),
      );
      await _ambilDataWarga(page: _currentPage);
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _statusSedangDiubah.remove(id);
        });
      }
    }
  }

  void _resetFilter() {
    _searchController.clear();

    setState(() {
      _filterGang = null;
      _filterBlok = null;
      _filterStatus = null;
      _filterRole = null;
    });

    _ambilDataWarga();
  }

  String _formatRole(Map<String, dynamic> warga) {
    final role = warga['role'];

    if (role is Map<String, dynamic>) {
      return role['nama_role']?.toString() ?? 'Warga';
    }

    return 'Warga';
  }

  String _formatGang(dynamic gang) {
    return gang?.toString() ?? '-';
  }

  Color _warnaStatus(String status) {
    return status == 'aktif' ? Colors.green.shade700 : Colors.red.shade700;
  }

  @override
  Widget build(BuildContext context) {
    return MainLayout(
      title: 'Kelola Warga',
      selectedMenu: 'Kelola Warga',
      pengguna: widget.pengguna,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Wrap(
              alignment: WrapAlignment.spaceBetween,
              crossAxisAlignment: WrapCrossAlignment.center,
              spacing: 12,
              runSpacing: 12,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Daftar Warga',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Kelola data akun warga lingkungan RT.',
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
                ElevatedButton.icon(
                  onPressed: _bukaTambahWarga,
                  icon: const Icon(Icons.person_add_alt_1, size: 18),
                  label: const Text('Tambah Warga'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.blue.shade700,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 12,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Info Total Warga
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
                    Icons.people_outline,
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
                    '$_totalWarga Akun',
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

            // Area Filter dan Pencarian
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                children: [
                  // Pencarian
                  TextField(
                    controller: _searchController,
                    textInputAction: TextInputAction.search,
                    onSubmitted: (_) => _ambilDataWarga(),
                    decoration: InputDecoration(
                      isDense: true,
                      hintText: 'Cari nama atau username...',
                      hintStyle: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade500,
                      ),
                      prefixIcon: const Icon(Icons.search, size: 20),
                      suffixIcon: IconButton(
                        tooltip: 'Cari',
                        icon: const Icon(Icons.arrow_forward, size: 20),
                        onPressed: () => _ambilDataWarga(),
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),

                  // Filter Gang, Status, dan Blok
                  Column(
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              initialValue: _filterGang,
                              isExpanded: true,
                              decoration: InputDecoration(
                                isDense: true,
                                labelText: 'Gang',
                                labelStyle: const TextStyle(fontSize: 12),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 10,
                                ),
                              ),
                              items: [
                                const DropdownMenuItem<String>(
                                  value: null,
                                  child: Text(
                                    'Semua',
                                    style: TextStyle(fontSize: 12),
                                  ),
                                ),
                                ..._daftarGang.map(
                                  (gang) => DropdownMenuItem<String>(
                                    value: gang,
                                    child: Text(
                                      gang,
                                      style: const TextStyle(fontSize: 12),
                                    ),
                                  ),
                                ),
                              ],
                              onChanged: (value) {
                                setState(() {
                                  _filterGang = value;
                                  _filterBlok = null;
                                });
                                _ambilDataWarga();
                              },
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              initialValue: _filterStatus,
                              isExpanded: true,
                              decoration: InputDecoration(
                                isDense: true,
                                labelText: 'Status',
                                labelStyle: const TextStyle(fontSize: 12),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 10,
                                ),
                              ),
                              items: const [
                                DropdownMenuItem<String>(
                                  value: null,
                                  child: Text(
                                    'Semua',
                                    style: TextStyle(fontSize: 12),
                                  ),
                                ),
                                DropdownMenuItem<String>(
                                  value: 'aktif',
                                  child: Text(
                                    'Aktif',
                                    style: TextStyle(fontSize: 12),
                                  ),
                                ),
                                DropdownMenuItem<String>(
                                  value: 'nonaktif',
                                  child: Text(
                                    'Nonaktif',
                                    style: TextStyle(fontSize: 12),
                                  ),
                                ),
                              ],
                              onChanged: (value) {
                                setState(() {
                                  _filterStatus = value;
                                });
                                _ambilDataWarga();
                              },
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(width: 8),
                      Row(
                        children: [
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              initialValue: _filterBlok,
                              isExpanded: true,
                              decoration: InputDecoration(
                                isDense: true,
                                labelText: 'Blok',
                                labelStyle: const TextStyle(fontSize: 12),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 10,
                                ),
                              ),
                              items: [
                                const DropdownMenuItem<String>(
                                  value: null,
                                  child: Text(
                                    'Semua',
                                    style: TextStyle(fontSize: 12),
                                  ),
                                ),
                                ..._daftarBlok.map(
                                  (blok) => DropdownMenuItem<String>(
                                    value: blok,
                                    child: Text(
                                      blok,
                                      style: const TextStyle(fontSize: 12),
                                    ),
                                  ),
                                ),
                              ],
                              onChanged: _daftarBlok.isEmpty
                                  ? null
                                  : (value) {
                                      setState(() {
                                        _filterBlok = value;
                                      });
                                      _ambilDataWarga();
                                    },
                            ),
                          ),
                          const SizedBox(width: 8),
                          SizedBox(
                            height: 46,
                            child: OutlinedButton(
                              onPressed: _resetFilter,
                              style: OutlinedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                              ),
                              child: const Icon(Icons.refresh, size: 20),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<String>(
                        initialValue: _filterRole,
                        isExpanded: true,
                        decoration: InputDecoration(
                          isDense: true,
                          labelText: 'Role',
                          labelStyle: const TextStyle(fontSize: 12),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 10,
                          ),
                        ),
                        items: const [
                          DropdownMenuItem<String>(
                            value: null,
                            child: Text(
                              'Semua role',
                              style: TextStyle(fontSize: 12),
                            ),
                          ),
                          DropdownMenuItem<String>(
                            value: 'ketua_rt',
                            child: Text(
                              'Ketua RT',
                              style: TextStyle(fontSize: 12),
                            ),
                          ),
                          DropdownMenuItem<String>(
                            value: 'warga_pj_kegiatan_dan_jadwal_ronda',
                            child: Text(
                              'Warga PJ Kegiatan dan Jadwal Ronda',
                              style: TextStyle(fontSize: 12),
                            ),
                          ),
                          DropdownMenuItem<String>(
                            value: 'warga_biasa',
                            child: Text(
                              'Warga Biasa',
                              style: TextStyle(fontSize: 12),
                            ),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _filterRole = value;
                          });
                          _ambilDataWarga();
                        },
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Daftar Warga
            Expanded(child: _buildDaftarWarga()),

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
                              ? () => _ambilDataWarga(page: _currentPage - 1)
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
                              ? () => _ambilDataWarga(page: _currentPage + 1)
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

  Widget _buildDaftarWarga() {
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
              onPressed: () => _ambilDataWarga(page: _currentPage),
              icon: const Icon(Icons.refresh, size: 18),
              label: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (_daftarWarga.isEmpty && _ketuaRT == null) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.people_outline, size: 48, color: Colors.grey),
            SizedBox(height: 12),
            Text(
              'Data warga tidak ditemukan.',
              style: TextStyle(color: Colors.grey),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => _ambilDataWarga(page: _currentPage),
      child: ListView.separated(
        itemCount: _daftarWarga.length + (_ketuaRT == null ? 0 : 1),
        separatorBuilder: (_, _) => const SizedBox(height: 8),
        itemBuilder: (context, index) {
          if (_ketuaRT != null && index == 0) {
            return _buildKartuWarga(_ketuaRT!, isKetuaRT: true);
          }

          final wargaIndex = index - (_ketuaRT == null ? 0 : 1);
          return _buildKartuWarga(
            Map<String, dynamic>.from(_daftarWarga[wargaIndex]),
          );
        },
      ),
    );
  }

  Widget _buildKartuWarga(
    Map<String, dynamic> warga, {
    bool isKetuaRT = false,
  }) {
    final id = int.tryParse(warga['id'].toString());
    final nama = warga['nama']?.toString() ?? '-';
    final username = warga['username']?.toString() ?? '-';
    final status = warga['status']?.toString() ?? 'nonaktif';
    final colorStatus = _warnaStatus(status);
    final gang = _formatGang(warga['gang']);
    final blok = warga['blok']?.toString() ?? '-';
    final role = _formatRole(warga);
    final isChangingStatus = id != null && _statusSedangDiubah.contains(id);

    return Card(
      elevation: 0,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(10),
        side: BorderSide(color: Colors.grey.shade200),
      ),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 20,
                  backgroundColor: Colors.blue.shade50,
                  child: Text(
                    nama.isNotEmpty ? nama[0].toUpperCase() : '?',
                    style: TextStyle(
                      color: Colors.blue.shade800,
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        nama,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'username: $username',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade600,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '$role • $gang • Blok $blok',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: colorStatus.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    status.toUpperCase(),
                    style: TextStyle(
                      color: colorStatus,
                      fontSize: 9,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            Wrap(
              alignment: WrapAlignment.end,
              spacing: 4,
              children: [
                IconButton(
                  tooltip: 'Detail Warga',
                  visualDensity: VisualDensity.compact,
                  constraints: const BoxConstraints(
                    minWidth: 36,
                    minHeight: 36,
                  ),
                  padding: EdgeInsets.zero,
                  onPressed: id == null ? null : () => _bukaDetailWarga(id),
                  icon: Icon(
                    Icons.visibility_outlined,
                    size: 20,
                    color: Colors.blue.shade700,
                  ),
                ),
                IconButton(
                  tooltip: 'Edit Warga',
                  visualDensity: VisualDensity.compact,
                  constraints: const BoxConstraints(
                    minWidth: 36,
                    minHeight: 36,
                  ),
                  padding: EdgeInsets.zero,
                  onPressed: id == null ? null : () => _bukaEditWarga(id),
                  icon: Icon(
                    Icons.edit_outlined,
                    size: 20,
                    color: Colors.green.shade700,
                  ),
                ),
                if (!isKetuaRT)
                  TextButton.icon(
                    onPressed: id == null || isChangingStatus
                        ? null
                        : () => _ubahStatusWarga(warga),
                    icon: isChangingStatus
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : Icon(
                            status == 'aktif'
                                ? Icons.person_off_outlined
                                : Icons.person_outline,
                          ),
                    label: Text(status == 'aktif' ? 'Nonaktifkan' : 'Aktifkan'),
                    style: TextButton.styleFrom(
                      foregroundColor: status == 'aktif'
                          ? Colors.red.shade700
                          : Colors.green.shade700,
                    ),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
