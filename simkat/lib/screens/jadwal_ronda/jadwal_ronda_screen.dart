import 'package:flutter/material.dart';

import '../../core/services/auth_service.dart';
import '../../core/services/jadwal_ronda_service.dart';
import '../../layouts/main_layout.dart';
import 'detail_jadwal_ronda_screen.dart';
import 'form_jadwal_ronda_screen.dart';

class JadwalRondaScreen extends StatefulWidget {
  final Map<String, dynamic> pengguna;

  const JadwalRondaScreen({super.key, required this.pengguna});

  @override
  State<JadwalRondaScreen> createState() => _JadwalRondaScreenState();
}

class _JadwalRondaScreenState extends State<JadwalRondaScreen> {
  static const String _semuaGang = 'Semua Gang';
  static const String _semuaHari = 'Semua Hari';
  static const List<String> _daftarHari = [
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
    'Minggu',
  ];

  List<dynamic> daftarJadwal = [];
  List<String> _daftarGangFilter = [_semuaGang];

  bool sedangMemuat = true;
  String? pesanError;
  bool _sudahMuatDaftarGang = false;

  int halamanSekarang = 1;
  int halamanTerakhir = 1;
  int totalJadwal = 0;
  String _roleAktual = '';
  String _filterGang = _semuaGang;
  String _filterHari = _semuaHari;

  bool get bolehKelola => _roleAktual == 'warga_pj_kegiatan_dan_jadwal_ronda';

  @override
  void initState() {
    super.initState();
    _ambilDataJadwal();
  }

  // =========================================================
  // AMBIL DATA
  // =========================================================

  Future<void> _ambilDataJadwal({int halaman = 1}) async {
    if (!mounted) return;

    setState(() {
      sedangMemuat = true;
      pesanError = null;
    });

    try {
      final profil = await AuthService.getProfil();
      final role = profil['role']?.toString();
      if (role == null || role.isEmpty) {
        throw Exception('Role pengguna tidak ditemukan.');
      }

      if (!mounted) return;
      setState(() => _roleAktual = role);

      if (bolehKelola && !_sudahMuatDaftarGang) {
        final peserta = await JadwalRondaService.getDaftarPeserta();
        if (!mounted) return;
        final gang = peserta
            .map((item) => item['gang']?.toString() ?? '')
            .where((nama) => nama.isNotEmpty && nama != _semuaGang)
            .toSet()
            .toList()
          ..sort();
        setState(() {
          _daftarGangFilter = [_semuaGang, ...gang];
          _sudahMuatDaftarGang = true;
          if (!_daftarGangFilter.contains(_filterGang)) {
            _filterGang = _semuaGang;
          }
        });
      }

      final response = bolehKelola
          ? await JadwalRondaService.getJadwalRondaKelola(
              page: halaman,
              gang: _filterGang,
              hari: _filterHari,
            )
          : await JadwalRondaService.getJadwalRonda(role: role, page: halaman);

      if (!mounted) return;

      final data = response['data'];
      final meta = response['meta'];

      setState(() {
        daftarJadwal = data is List ? data : [];

        if (meta is Map) {
          halamanSekarang = int.tryParse('${meta['current_page']}') ?? 1;

          halamanTerakhir = int.tryParse('${meta['last_page']}') ?? 1;

          totalJadwal = int.tryParse('${meta['total']}') ?? 0;
        } else {
          halamanSekarang = 1;
          halamanTerakhir = 1;
          totalJadwal = daftarJadwal.length;
        }

        sedangMemuat = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        sedangMemuat = false;
        pesanError = e.toString();
      });
    }
  }

  // =========================================================
  // FORMAT DATA
  // =========================================================

  String _formatHari(dynamic hari) {
    if (hari is List) {
      return hari.join(', ');
    }

    if (hari == null || hari.toString().isEmpty) {
      return '-';
    }

    return hari.toString();
  }

  String _formatWaktu(dynamic waktu) {
    if (waktu == null || waktu.toString().isEmpty) {
      return '-';
    }

    final nilai = waktu.toString();

    if (nilai.length >= 5) {
      return nilai.substring(0, 5);
    }

    return nilai;
  }

  int _jumlahPeserta(dynamic peserta) {
    if (peserta is List) {
      return peserta.length;
    }

    return 0;
  }

  // =========================================================
  // BUKA DETAIL
  // =========================================================

  Future<void> _bukaDetail(dynamic id) async {
    final jadwalId = int.tryParse(id.toString());

    if (jadwalId == null) return;

    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) =>
            DetailJadwalRondaScreen(jadwalId: jadwalId, role: _roleAktual),
      ),
    );
  }

  Future<void> _bukaForm({Map<String, dynamic>? jadwal}) async {
    final tersimpan = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => FormJadwalRondaScreen(jadwal: jadwal)),
    );
    if (tersimpan == true && mounted) {
      await _ambilDataJadwal(halaman: halamanSekarang);
    }
  }

  Future<void> _ubahStatus(Map<String, dynamic> jadwal) async {
    final id = int.tryParse(jadwal['id']?.toString() ?? '');
    if (id == null) return;
    final statusLama = jadwal['status']?.toString() ?? 'aktif';
    final statusBaru = statusLama == 'aktif' ? 'nonaktif' : 'aktif';

    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          statusBaru == 'aktif'
              ? 'Aktifkan jadwal ronda?'
              : 'Nonaktifkan jadwal ronda?',
        ),
        content: Text(
          statusBaru == 'aktif'
              ? 'Jadwal akan ditampilkan kembali kepada warga.'
              : 'Jadwal tidak akan ditampilkan kepada warga.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text(statusBaru == 'aktif' ? 'Aktifkan' : 'Nonaktifkan'),
          ),
        ],
      ),
    );
    if (konfirmasi != true) return;

    try {
      await JadwalRondaService.ubahStatusJadwalRonda(id, statusBaru);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            statusBaru == 'aktif'
                ? 'Jadwal ronda berhasil diaktifkan.'
                : 'Jadwal ronda berhasil dinonaktifkan.',
          ),
        ),
      );
      await _ambilDataJadwal(halaman: halamanSekarang);
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

  Widget _buildFilter() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          children: [
            Expanded(
              child: DropdownButtonFormField<String>(
                initialValue: _filterGang,
                decoration: const InputDecoration(
                  labelText: 'Filter Gang',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
                items: _daftarGangFilter
                    .map(
                      (gang) =>
                          DropdownMenuItem(value: gang, child: Text(gang)),
                    )
                    .toList(),
                onChanged: sedangMemuat
                    ? null
                    : (value) {
                        if (value == null) return;
                        setState(() => _filterGang = value);
                        _ambilDataJadwal(halaman: 1);
                      },
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: DropdownButtonFormField<String>(
                initialValue: _filterHari,
                decoration: const InputDecoration(
                  labelText: 'Filter Hari',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
                items: [
                  const DropdownMenuItem(
                    value: _semuaHari,
                    child: Text(_semuaHari),
                  ),
                  ..._daftarHari.map(
                    (hari) =>
                        DropdownMenuItem(value: hari, child: Text(hari)),
                  ),
                ],
                onChanged: sedangMemuat
                    ? null
                    : (value) {
                        if (value == null) return;
                        setState(() => _filterHari = value);
                        _ambilDataJadwal(halaman: 1);
                      },
              ),
            ),
          ],
        ),
      ),
    );
  }

  // =========================================================
  // HALAMAN UTAMA
  // =========================================================

  @override
  Widget build(BuildContext context) {
    return MainLayout(
      title: 'Jadwal Ronda',
      selectedMenu: 'Jadwal Ronda',
      pengguna: widget.pengguna,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 16),
            _buildRingkasan(),
            if (bolehKelola) ...[
              const SizedBox(height: 16),
              _buildFilter(),
            ],
            const SizedBox(height: 16),
            Expanded(child: _buildDaftarJadwal()),
            if (!sedangMemuat && pesanError == null && daftarJadwal.isNotEmpty)
              _buildPagination(),
          ],
        ),
      ),
    );
  }

  // =========================================================
  // HEADER
  // =========================================================

  Widget _buildHeader() {
    return Row(
      children: [
        const Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Daftar Jadwal Ronda',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: MainLayout.textColor,
                ),
              ),
              SizedBox(height: 4),
              Text(
                'Informasi jadwal pelaksanaan ronda lingkungan RT.',
                style: TextStyle(fontSize: 13, color: MainLayout.mutedColor),
              ),
            ],
          ),
        ),
        if (bolehKelola)
          IconButton.filled(
            tooltip: 'Tambah Jadwal Ronda',
            onPressed: () => _bukaForm(),
            icon: const Icon(Icons.add),
          ),
      ],
    );
  }

  // =========================================================
  // RINGKASAN
  // =========================================================

  Widget _buildRingkasan() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: MainLayout.primaryColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          const Icon(
            Icons.calendar_month_outlined,
            color: Colors.white,
            size: 28,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Total Jadwal',
                  style: TextStyle(color: Colors.white70, fontSize: 13),
                ),
                const SizedBox(height: 4),
                Text(
                  sedangMemuat ? '-' : '$totalJadwal Jadwal',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================
  // DAFTAR JADWAL
  // =========================================================

  Widget _buildDaftarJadwal() {
    if (sedangMemuat) {
      return const Center(child: CircularProgressIndicator());
    }

    if (pesanError != null) {
      return _buildError();
    }

    if (daftarJadwal.isEmpty) {
      return _buildKosong();
    }

    return RefreshIndicator(
      onRefresh: () => _ambilDataJadwal(halaman: halamanSekarang),
      child: ListView.separated(
        physics: const AlwaysScrollableScrollPhysics(),
        itemCount: daftarJadwal.length,
        separatorBuilder: (_, _) => const SizedBox(height: 8),
        itemBuilder: (context, index) {
          final jadwal = daftarJadwal[index];

          return _buildKartuJadwal(jadwal);
        },
      ),
    );
  }

  // =========================================================
  // KARTU JADWAL
  // =========================================================

  Widget _buildKartuJadwal(Map<String, dynamic> jadwal) {
    final hari = _formatHari(jadwal['hari']);

    final waktuMulai = _formatWaktu(jadwal['waktu_mulai']);

    final waktuSelesai = _formatWaktu(jadwal['waktu_selesai']);

    final gang = jadwal['gang']?.toString() ?? '-';

    final jumlahPeserta = _jumlahPeserta(jadwal['peserta']);
    final status = jadwal['status']?.toString() ?? 'aktif';

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: const Color(0xFFDBEAFE),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(
              Icons.calendar_month_outlined,
              color: MainLayout.primaryColor,
              size: 23,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  hari,
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: MainLayout.textColor,
                  ),
                ),
                const SizedBox(height: 5),
                if (bolehKelola)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 4),
                    child: Text(
                      status == 'aktif' ? 'Aktif' : 'Nonaktif',
                      style: TextStyle(
                        color: status == 'aktif'
                            ? Colors.green.shade700
                            : Colors.grey.shade700,
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                Row(
                  children: [
                    const Icon(
                      Icons.access_time,
                      size: 14,
                      color: MainLayout.mutedColor,
                    ),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        '$waktuMulai - $waktuSelesai',
                        style: const TextStyle(
                          fontSize: 12,
                          color: MainLayout.mutedColor,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(
                      Icons.location_on_outlined,
                      size: 14,
                      color: MainLayout.mutedColor,
                    ),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        gang,
                        style: const TextStyle(
                          fontSize: 12,
                          color: MainLayout.mutedColor,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(
                      Icons.people_outline,
                      size: 14,
                      color: MainLayout.mutedColor,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      '$jumlahPeserta peserta',
                      style: const TextStyle(
                        fontSize: 12,
                        color: MainLayout.mutedColor,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          IconButton(
            tooltip: 'Lihat detail',
            constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
            padding: const EdgeInsets.all(4),
            onPressed: () => _bukaDetail(jadwal['id']),
            icon: const Icon(
              Icons.visibility_outlined,
              color: MainLayout.primaryColor,
              size: 21,
            ),
          ),
          if (bolehKelola)
            IconButton(
              tooltip: 'Edit jadwal',
              constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
              padding: const EdgeInsets.all(4),
              onPressed: () =>
                  _bukaForm(jadwal: Map<String, dynamic>.from(jadwal)),
              icon: const Icon(
                Icons.edit_outlined,
                color: MainLayout.primaryColor,
                size: 21,
              ),
            ),
          if (bolehKelola)
            IconButton(
              tooltip: status == 'aktif'
                  ? 'Nonaktifkan jadwal'
                  : 'Aktifkan jadwal',
              constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
              padding: const EdgeInsets.all(4),
              onPressed: () => _ubahStatus(Map<String, dynamic>.from(jadwal)),
              icon: Icon(
                status == 'aktif'
                    ? Icons.check_circle_outline
                    : Icons.block_outlined,
                color: status == 'aktif'
                    ? Colors.green.shade700
                    : Colors.grey.shade700,
                size: 21,
              ),
            ),
        ],
      ),
    );
  }

  // =========================================================
  // KOSONG
  // =========================================================

  Widget _buildKosong() {
    return RefreshIndicator(
      onRefresh: () => _ambilDataJadwal(),
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: const [
          SizedBox(height: 100),
          Icon(
            Icons.calendar_month_outlined,
            size: 54,
            color: Color(0xFF94A3B8),
          ),
          SizedBox(height: 12),
          Center(
            child: Text(
              'Belum ada jadwal ronda aktif.',
              style: TextStyle(color: MainLayout.mutedColor, fontSize: 14),
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================
  // ERROR
  // =========================================================

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.error_outline,
              size: 48,
              color: MainLayout.dangerColor,
            ),
            const SizedBox(height: 12),
            const Text(
              'Gagal mengambil data jadwal ronda.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: MainLayout.textColor,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              pesanError ?? '',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 12,
                color: MainLayout.mutedColor,
              ),
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: () => _ambilDataJadwal(halaman: halamanSekarang),
              icon: const Icon(Icons.refresh),
              label: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }

  // =========================================================
  // PAGINATION
  // =========================================================

  Widget _buildPagination() {
    return Padding(
      padding: const EdgeInsets.only(top: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          OutlinedButton.icon(
            onPressed: halamanSekarang > 1
                ? () => _ambilDataJadwal(halaman: halamanSekarang - 1)
                : null,
            icon: const Icon(Icons.chevron_left),
            label: const Text('Sebelumnya'),
          ),
          Text(
            '$halamanSekarang / $halamanTerakhir',
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: MainLayout.textColor,
            ),
          ),
          OutlinedButton.icon(
            onPressed: halamanSekarang < halamanTerakhir
                ? () => _ambilDataJadwal(halaman: halamanSekarang + 1)
                : null,
            icon: const Icon(Icons.chevron_right),
            label: const Text('Berikutnya'),
          ),
        ],
      ),
    );
  }
}
