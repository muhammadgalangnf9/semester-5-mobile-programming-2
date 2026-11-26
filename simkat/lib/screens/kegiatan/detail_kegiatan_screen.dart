import 'package:flutter/material.dart';

import '../../core/services/auth_service.dart';
import '../../core/services/kegiatan_service.dart';

class DetailKegiatanScreen extends StatefulWidget {
  final int kegiatanId;
  final String role;

  const DetailKegiatanScreen({
    super.key,
    required this.kegiatanId,
    required this.role,
  });

  @override
  State<DetailKegiatanScreen> createState() => _DetailKegiatanScreenState();
}

class _DetailKegiatanScreenState extends State<DetailKegiatanScreen> {
  Map<String, dynamic>? _kegiatan;

  bool _isLoading = true;
  bool _isProcessing = false;
  String? _errorMessage;
  String _roleAktual = '';

  @override
  void initState() {
    super.initState();
    _ambilDetailKegiatan();
  }

  Future<void> _ambilDetailKegiatan() async {
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

      final response = switch (role) {
        'ketua_rt' => await KegiatanService.getDetailKegiatanKetuaRT(
          widget.kegiatanId,
        ),
        'warga_pj_kegiatan_dan_jadwal_ronda' =>
          await KegiatanService.getDetailKegiatanWargaPJ(widget.kegiatanId),
        'warga_biasa' => await KegiatanService.getDetailKegiatanWarga(
          widget.kegiatanId,
        ),
        _ => throw Exception('Role pengguna tidak memiliki akses kegiatan.'),
      };

      if (!mounted) return;

      setState(() {
        _kegiatan = Map<String, dynamic>.from(response['data'] as Map);
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

  String _formatTanggal(dynamic tanggal) {
    if (tanggal == null) return '-';

    try {
      final date = DateTime.parse(tanggal.toString()).toLocal();

      final hari = date.day.toString().padLeft(2, '0');
      final bulan = date.month.toString().padLeft(2, '0');
      final tahun = date.year.toString();

      final jam = date.hour.toString().padLeft(2, '0');
      final menit = date.minute.toString().padLeft(2, '0');

      return '$hari-$bulan-$tahun, $jam:$menit';
    } catch (_) {
      return '-';
    }
  }

  String _formatNominal(dynamic nominal) {
    if (nominal == null) return '-';

    final angka = double.tryParse(nominal.toString());

    if (angka == null) return nominal.toString();

    final nilai = angka.toInt().toString();
    final buffer = StringBuffer();

    for (int i = 0; i < nilai.length; i++) {
      buffer.write(nilai[i]);

      final sisa = nilai.length - i - 1;

      if (sisa > 0 && sisa % 3 == 0) {
        buffer.write('.');
      }
    }

    return 'Rp${buffer.toString()}';
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
        return status ?? '-';
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

  Widget _buildInformasi({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 19, color: Colors.blue.shade700),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                ),
                const SizedBox(height: 3),
                Text(
                  value.isEmpty ? '-' : value,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBagian({
    required String judul,
    required List<Widget> children,
    IconData icon = Icons.info_outline,
  }) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 19, color: Colors.blue.shade700),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  judul,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          ...children,
        ],
      ),
    );
  }

  Widget _buildDaftarKosong(String pesan) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Center(
        child: Text(
          pesan,
          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
        ),
      ),
    );
  }

  List<Map<String, dynamic>> _ambilDaftar(String key) {
    final data = _kegiatan?[key];

    if (data is! List) return [];

    return data
        .whereType<Map>()
        .map((item) => Map<String, dynamic>.from(item))
        .toList();
  }

  Widget _barisKomponen({
    required IconData icon,
    required String judul,
    required String keterangan,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      elevation: 0,
      color: const Color(0xFFF8FAFC),
      child: ListTile(
        leading: Icon(icon, color: Colors.blue.shade700),
        title: Text(
          judul,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
        ),
        subtitle: Text(keterangan),
        isThreeLine: keterangan.contains('\n'),
      ),
    );
  }

  Widget _buildPanitia() {
    final daftar = _ambilDaftar('panitia');

    return _buildBagian(
      judul: 'Panitia Kegiatan',
      icon: Icons.groups_outlined,
      children: [
        if (daftar.isEmpty)
          _buildDaftarKosong('Belum ada data panitia.')
        else
          ...daftar.map((item) {
            return _barisKomponen(
              icon: Icons.person_outline,
              judul: item['jabatan']?.toString() ?? 'Panitia',
              keterangan:
                  '${item['nama_panitia'] ?? '-'}\n${item['tugas'] ?? ''}',
            );
          }),
      ],
    );
  }

  Widget _buildRab() {
    final daftar = _ambilDaftar('rab');

    return _buildBagian(
      judul: 'Rencana Anggaran Biaya',
      icon: Icons.receipt_long_outlined,
      children: [
        if (daftar.isEmpty)
          _buildDaftarKosong('Belum ada data anggaran.')
        else
          ...daftar.map((item) {
            return _barisKomponen(
              icon: Icons.payments_outlined,
              judul: item['nama_kebutuhan']?.toString() ?? '-',
              keterangan:
                  '${item['kategori_pengeluaran'] ?? '-'} • ${item['jumlah'] ?? '-'} ${item['satuan'] ?? ''}\n'
                  '${_formatNominal(item['total_biaya'])} • ${item['sumber_dana'] ?? '-'}',
            );
          }),
      ],
    );
  }

  Widget _buildTransaksi() {
    final daftar = _ambilDaftar('transaksi');

    return _buildBagian(
      judul: 'Transaksi Kegiatan',
      icon: Icons.account_balance_wallet_outlined,
      children: [
        if (daftar.isEmpty)
          _buildDaftarKosong('Belum ada transaksi.')
        else
          ...daftar.map((item) {
            final jenis = item['jenis_transaksi']?.toString() ?? '-';

            return _barisKomponen(
              icon: jenis == 'pemasukan'
                  ? Icons.arrow_downward
                  : Icons.arrow_upward,
              judul: item['kategori']?.toString() ?? '-',
              keterangan:
                  '${jenis.toUpperCase()} • ${_formatTanggal(item['tanggal_transaksi'])}\n'
                  '${_formatNominal(item['nominal'])} • ${item['keterangan'] ?? '-'}',
            );
          }),
      ],
    );
  }

  Widget _buildPeserta() {
    final daftar = _ambilDaftar('peserta');

    return _buildBagian(
      judul: 'Peserta Kegiatan',
      icon: Icons.people_outline,
      children: [
        if (daftar.isEmpty)
          _buildDaftarKosong('Belum ada data peserta.')
        else
          ...daftar.map((item) {
            return _barisKomponen(
              icon: Icons.person_outline,
              judul: item['nama']?.toString() ?? '-',
              keterangan:
                  '${item['gang'] ?? 'Gang -'} • ${item['status_kehadiran'] ?? 'belum_dikonfirmasi'}\n'
                  '${item['keterangan'] ?? ''}',
            );
          }),
      ],
    );
  }

  Widget _buildDokumentasi() {
    final daftar = _ambilDaftar('dokumentasi');

    return _buildBagian(
      judul: 'Dokumentasi',
      icon: Icons.photo_library_outlined,
      children: [
        if (daftar.isEmpty)
          _buildDaftarKosong('Belum ada dokumentasi.')
        else
          ...daftar.map((item) {
            return _barisKomponen(
              icon: item['jenis'] == 'video'
                  ? Icons.videocam_outlined
                  : Icons.image_outlined,
              judul: item['nama_dokumentasi']?.toString() ?? '-',
              keterangan:
                  '${item['jenis']?.toString().toUpperCase() ?? '-'}\n'
                  '${item['link_drive'] ?? '-'}',
            );
          }),
      ],
    );
  }

  Future<void> _setujuiKegiatan() async {
    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: const Text('Setujui Kegiatan'),
          content: const Text(
            'Apakah kamu yakin ingin menyetujui kegiatan ini? '
            'Kegiatan akan ditampilkan sebagai kegiatan yang disetujui.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext, false),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.pop(dialogContext, true),
              child: const Text('Setujui'),
            ),
          ],
        );
      },
    );

    if (konfirmasi != true) return;

    setState(() {
      _isProcessing = true;
    });

    try {
      await KegiatanService.setujuiKegiatan(widget.kegiatanId);

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Kegiatan berhasil disetujui.'),
          backgroundColor: Colors.green,
        ),
      );

      await _ambilDetailKegiatan();
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
          _isProcessing = false;
        });
      }
    }
  }

  Future<void> _tolakKegiatan() async {
    final controller = TextEditingController();

    final catatan = await showDialog<String>(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: const Text('Tolak Kegiatan'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Berikan alasan penolakan agar pengelola '
                'kegiatan dapat melakukan perbaikan.',
                style: TextStyle(fontSize: 13),
              ),
              const SizedBox(height: 14),
              TextField(
                controller: controller,
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'Catatan Penolakan',
                  hintText: 'Masukkan alasan penolakan...',
                  border: OutlineInputBorder(),
                  alignLabelWithHint: true,
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red,
                foregroundColor: Colors.white,
              ),
              onPressed: () {
                final nilai = controller.text.trim();

                if (nilai.isEmpty) {
                  ScaffoldMessenger.of(dialogContext).showSnackBar(
                    const SnackBar(
                      content: Text('Catatan penolakan wajib diisi.'),
                    ),
                  );
                  return;
                }

                Navigator.pop(dialogContext, nilai);
              },
              child: const Text('Tolak Kegiatan'),
            ),
          ],
        );
      },
    );

    controller.dispose();

    if (catatan == null || catatan.trim().isEmpty) return;

    setState(() {
      _isProcessing = true;
    });

    try {
      await KegiatanService.tolakKegiatan(widget.kegiatanId, catatan: catatan);

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Kegiatan berhasil ditolak.'),
          backgroundColor: Colors.green,
        ),
      );

      await _ambilDetailKegiatan();
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
          _isProcessing = false;
        });
      }
    }
  }

  // Membatalkan persetujuan kegiatan
  Future<void> _batalkanPersetujuan() async {
    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: const Text('Batalkan Persetujuan'),
          content: const Text(
            'Apakah kamu yakin ingin membatalkan persetujuan kegiatan ini? '
            'Status kegiatan akan kembali menjadi Menunggu Persetujuan '
            'dan kegiatan tidak lagi ditampilkan sebagai kegiatan yang disetujui.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext, false),
              child: const Text('Kembali'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red,
                foregroundColor: Colors.white,
              ),
              onPressed: () => Navigator.pop(dialogContext, true),
              child: const Text('Batalkan Persetujuan'),
            ),
          ],
        );
      },
    );

    if (konfirmasi != true) return;

    setState(() {
      _isProcessing = true;
    });

    try {
      await KegiatanService.batalkanPersetujuanKegiatan(widget.kegiatanId);

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Persetujuan kegiatan berhasil dibatalkan.'),
          backgroundColor: Colors.green,
        ),
      );

      await _ambilDetailKegiatan();
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
          _isProcessing = false;
        });
      }
    }
  }

  Widget _buildTombolPersetujuan() {
    if (_roleAktual != 'ketua_rt') {
      return const SizedBox.shrink();
    }

    final status = _kegiatan?['status']?.toString();

    // Tombol untuk kegiatan yang menunggu persetujuan
    if (status == 'menunggu_persetujuan') {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: Colors.grey.shade200)),
        ),
        child: Row(
          children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed: _isProcessing ? null : _tolakKegiatan,
                icon: const Icon(Icons.close, size: 18),
                label: const Text('Tolak'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.red,
                  side: const BorderSide(color: Colors.red),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: ElevatedButton.icon(
                onPressed: _isProcessing ? null : _setujuiKegiatan,
                icon: const Icon(Icons.check, size: 18),
                label: const Text('Setujui'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          ],
        ),
      );
    }

    // Tombol untuk kegiatan yang sudah disetujui
    if (status == 'disetujui') {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: Colors.grey.shade200)),
        ),
        child: SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: _isProcessing ? null : _batalkanPersetujuan,
            icon: const Icon(Icons.cancel_outlined, size: 18),
            label: const Text('Batalkan Persetujuan'),
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.red,
              side: const BorderSide(color: Colors.red),
              padding: const EdgeInsets.symmetric(vertical: 12),
            ),
          ),
        ),
      );
    }

    return const SizedBox.shrink();
  }

  Widget _buildKonten() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, color: Colors.red, size: 44),
              const SizedBox(height: 12),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: _ambilDetailKegiatan,
                icon: const Icon(Icons.refresh, size: 18),
                label: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (_kegiatan == null) {
      return const Center(child: Text('Data kegiatan tidak ditemukan.'));
    }

    final kegiatan = _kegiatan!;

    final nama = kegiatan['nama_kegiatan']?.toString() ?? '-';
    final jenis = kegiatan['jenis_kegiatan']?.toString() ?? '-';
    final deskripsi = kegiatan['deskripsi']?.toString() ?? '-';
    final lokasi = kegiatan['lokasi']?.toString() ?? '-';
    final poster = kegiatan['link_poster']?.toString() ?? '';
    final status = kegiatan['status']?.toString();

    final pengguna = kegiatan['pengguna'];
    final namaPembuat = pengguna is Map
        ? pengguna['nama']?.toString() ?? '-'
        : '-';
    final pengaju = kegiatan['pengaju'];
    final namaPengaju = pengaju is Map
        ? pengaju['nama']?.toString() ?? 'Belum tercatat'
        : status == 'draft'
        ? 'Belum diajukan'
        : 'Belum tercatat';
    final pengubah = kegiatan['pengubah'];
    final namaPengubah = pengubah is Map
        ? pengubah['nama']?.toString() ?? 'Belum tercatat'
        : 'Belum tercatat';
    final penyetuju = kegiatan['disetujui_oleh'];
    final namaPenyetuju = penyetuju is Map
        ? penyetuju['nama']?.toString() ?? penyetuju['name']?.toString() ?? '-'
        : '-';

    return RefreshIndicator(
      onRefresh: _ambilDetailKegiatan,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Header Kegiatan
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.blue.shade50,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.event_note_outlined,
                      color: Colors.blue.shade700,
                      size: 22,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'INFORMASI KEGIATAN RT',
                      style: TextStyle(
                        color: Colors.blue.shade800,
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                Text(
                  nama,
                  style: const TextStyle(
                    fontSize: 19,
                    fontWeight: FontWeight.bold,
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: _warnaStatus(status).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    _labelStatus(status),
                    style: TextStyle(
                      color: _warnaStatus(status),
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Informasi Utama
          _buildBagian(
            judul: 'Informasi Kegiatan',
            icon: Icons.info_outline,
            children: [
              _buildInformasi(
                icon: Icons.person_outline,
                label: 'Dibuat oleh',
                value: namaPembuat,
              ),
              _buildInformasi(
                icon: Icons.send_outlined,
                label: 'Diajukan oleh',
                value: namaPengaju,
              ),
              _buildInformasi(
                icon: Icons.edit_outlined,
                label: 'Terakhir diedit oleh',
                value: namaPengubah,
              ),
              _buildInformasi(
                icon: Icons.publish_outlined,
                label: 'Diterbitkan pada',
                value: _formatTanggal(kegiatan['diterbitkan_pada']),
              ),
              _buildInformasi(
                icon: Icons.verified_user_outlined,
                label: 'Disetujui oleh',
                value: namaPenyetuju,
              ),
              _buildInformasi(
                icon: Icons.event_busy_outlined,
                label: 'Dinonaktifkan pada',
                value: _formatTanggal(kegiatan['dinonaktifkan_pada']),
              ),
              _buildInformasi(
                icon: Icons.schedule_outlined,
                label: 'Dibuat pada',
                value: _formatTanggal(kegiatan['created_at']),
              ),
              _buildInformasi(
                icon: Icons.update_outlined,
                label: 'Diupdate pada',
                value: _formatTanggal(kegiatan['updated_at']),
              ),
              _buildInformasi(
                icon: Icons.category_outlined,
                label: 'Jenis kegiatan',
                value: jenis,
              ),
              _buildInformasi(
                icon: Icons.calendar_today_outlined,
                label: 'Tanggal mulai',
                value: _formatTanggal(kegiatan['tanggal_mulai']),
              ),
              _buildInformasi(
                icon: Icons.event_outlined,
                label: 'Tanggal selesai',
                value: _formatTanggal(kegiatan['tanggal_selesai']),
              ),
              _buildInformasi(
                icon: Icons.location_on_outlined,
                label: 'Lokasi',
                value: lokasi,
              ),
              if (poster.isNotEmpty)
                _buildInformasi(
                  icon: Icons.image_outlined,
                  label: 'Link poster',
                  value: poster,
                ),
            ],
          ),

          // Deskripsi
          _buildBagian(
            judul: 'Deskripsi Kegiatan',
            icon: Icons.description_outlined,
            children: [
              SelectableText(
                deskripsi.isEmpty ? '-' : deskripsi,
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.grey.shade800,
                  height: 1.7,
                ),
              ),
            ],
          ),

          // Catatan Persetujuan
          if (kegiatan['catatan_persetujuan'] != null &&
              kegiatan['catatan_persetujuan'].toString().isNotEmpty)
            _buildBagian(
              judul: 'Catatan Persetujuan',
              icon: Icons.comment_outlined,
              children: [
                SelectableText(
                  kegiatan['catatan_persetujuan'].toString(),
                  style: const TextStyle(fontSize: 13, height: 1.6),
                ),
              ],
            ),

          _buildPanitia(),
          _buildRab(),
          _buildTransaksi(),
          _buildPeserta(),
          _buildDokumentasi(),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: const Text(
          'Detail Kegiatan',
          style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        surfaceTintColor: Colors.white,
      ),
      body: _buildKonten(),
      bottomNavigationBar:
          _kegiatan != null && !_isLoading && _errorMessage == null
          ? _buildTombolPersetujuan()
          : null,
    );
  }
}
