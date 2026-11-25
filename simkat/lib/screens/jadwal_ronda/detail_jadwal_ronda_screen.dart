import 'package:flutter/material.dart';

import '../../core/services/auth_service.dart';
import '../../core/services/jadwal_ronda_service.dart';

class DetailJadwalRondaScreen extends StatefulWidget {
  final int jadwalId;
  final String role;

  const DetailJadwalRondaScreen({
    super.key,
    required this.jadwalId,
    this.role = '',
  });

  @override
  State<DetailJadwalRondaScreen> createState() =>
      _DetailJadwalRondaScreenState();
}

class _DetailJadwalRondaScreenState extends State<DetailJadwalRondaScreen> {
  Map<String, dynamic>? _jadwal;

  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _ambilDetailJadwal();
  }

  Future<void> _ambilDetailJadwal() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final profil = await AuthService.getProfil();
      final role = profil['role']?.toString();
      if (role == null || role.isEmpty) {
        throw Exception('Role pengguna tidak ditemukan.');
      }

      if (!mounted) return;

      final isPengelola = role == 'warga_pj_kegiatan_dan_jadwal_ronda';
      final response = isPengelola
          ? await JadwalRondaService.getDetailJadwalRondaKelola(widget.jadwalId)
          : await JadwalRondaService.getDetailJadwalRonda(
              widget.jadwalId,
              role: role,
            );

      if (!mounted) return;

      setState(() {
        _jadwal = Map<String, dynamic>.from(response['data']);
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

  String _formatHari(dynamic hari) {
    if (hari == null) return '-';

    if (hari is List) {
      if (hari.isEmpty) return '-';
      return hari.map((item) => item.toString()).join(', ');
    }

    return hari.toString();
  }

  String _formatWaktu(dynamic waktu) {
    if (waktu == null) return '-';

    final teks = waktu.toString();

    if (teks.length >= 5) {
      return teks.substring(0, 5);
    }

    return teks;
  }

  String _formatTanggalWaktu(dynamic tanggalWaktu) {
    if (tanggalWaktu == null || tanggalWaktu.toString().isEmpty) return '-';

    try {
      final date = DateTime.parse(tanggalWaktu.toString()).toLocal();

      final hari = date.day.toString().padLeft(2, '0');
      final bulan = date.month.toString().padLeft(2, '0');
      final tahun = date.year.toString();
      final jam = date.hour.toString().padLeft(2, '0');
      final menit = date.minute.toString().padLeft(2, '0');

      return '$hari-$bulan-$tahun $jam:$menit';
    } catch (_) {
      return '-';
    }
  }

  List<dynamic> _daftarPeserta(dynamic peserta) {
    if (peserta is List) {
      return peserta;
    }

    return [];
  }

  Widget _buildInformasi({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: Colors.blue.shade700),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetail() {
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
              const Icon(Icons.error_outline, color: Colors.red, size: 42),
              const SizedBox(height: 12),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13),
              ),
              const SizedBox(height: 12),
              ElevatedButton.icon(
                onPressed: _ambilDetailJadwal,
                icon: const Icon(Icons.refresh, size: 18),
                label: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (_jadwal == null) {
      return const Center(child: Text('Detail jadwal ronda tidak ditemukan.'));
    }

    final jadwal = _jadwal!;

    final hari = _formatHari(jadwal['hari']);
    final mulai = _formatWaktu(jadwal['waktu_mulai']);
    final selesai = _formatWaktu(jadwal['waktu_selesai']);
    final gang = jadwal['gang']?.toString() ?? '-';
    final keterangan = jadwal['keterangan']?.toString() ?? '-';
    final peserta = _daftarPeserta(jadwal['peserta']);
    final pengguna = jadwal['pengguna'];
    final dibuatOleh = pengguna is Map
        ? pengguna['nama']?.toString() ?? 'Tidak tercatat'
        : 'Tidak tercatat';

    return RefreshIndicator(
      onRefresh: _ambilDetailJadwal,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.only(bottom: 24),
        children: [
          // Informasi Jadwal
          Card(
            elevation: 0,
            margin: EdgeInsets.zero,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: BorderSide(color: Colors.grey.shade200),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 23,
                        backgroundColor: Colors.blue.shade50,
                        child: Icon(
                          Icons.shield_outlined,
                          color: Colors.blue.shade800,
                          size: 25,
                        ),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Text(
                          'Informasi Jadwal Ronda',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 5,
                        ),
                        decoration: BoxDecoration(
                          color: jadwal['status'] == 'aktif'
                              ? Colors.green.shade50
                              : Colors.grey.shade200,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          jadwal['status']?.toString() == 'nonaktif'
                              ? 'Nonaktif'
                              : 'Aktif',
                          style: TextStyle(
                            color: jadwal['status']?.toString() == 'nonaktif'
                                ? Colors.grey.shade700
                                : Colors.green.shade700,
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 20),

                  _buildInformasi(
                    icon: Icons.calendar_today_outlined,
                    label: 'Hari Ronda',
                    value: hari,
                  ),

                  _buildInformasi(
                    icon: Icons.access_time,
                    label: 'Waktu Ronda',
                    value: '$mulai - $selesai',
                  ),

                  _buildInformasi(
                    icon: Icons.location_on_outlined,
                    label: 'Gang',
                    value: gang,
                  ),

                  _buildInformasi(
                    icon: Icons.description_outlined,
                    label: 'Keterangan',
                    value: keterangan.isEmpty ? '-' : keterangan,
                  ),

                  _buildInformasi(
                    icon: Icons.publish_outlined,
                    label: 'Diterbitkan pada',
                    value: _formatTanggalWaktu(jadwal['diterbitkan_pada']),
                  ),
                  _buildInformasi(
                    icon: Icons.event_busy_outlined,
                    label: 'Dinonaktifkan pada',
                    value: _formatTanggalWaktu(jadwal['dinonaktifkan_pada']),
                  ),
                  _buildInformasi(
                    icon: Icons.add_circle_outline,
                    label: 'Dibuat pada',
                    value: _formatTanggalWaktu(jadwal['created_at']),
                  ),
                  _buildInformasi(
                    icon: Icons.person_outline,
                    label: 'Dibuat oleh',
                    value: dibuatOleh,
                  ),
                  _buildInformasi(
                    icon: Icons.update_outlined,
                    label: 'Diupdate pada',
                    value: _formatTanggalWaktu(jadwal['updated_at']),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 16),

          // Daftar Peserta
          Card(
            elevation: 0,
            margin: EdgeInsets.zero,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: BorderSide(color: Colors.grey.shade200),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.people_outline,
                        color: Colors.blue.shade700,
                        size: 22,
                      ),
                      const SizedBox(width: 10),
                      const Expanded(
                        child: Text(
                          'Daftar Warga Ronda',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      Text(
                        '${peserta.length} Orang',
                        style: TextStyle(
                          color: Colors.blue.shade700,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 14),

                  if (peserta.isEmpty)
                    Text(
                      'Belum ada peserta yang tercatat.',
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 13,
                      ),
                    )
                  else
                    ...peserta.asMap().entries.map((entry) {
                      final index = entry.key;
                      final item = entry.value;

                      String nama;
                      String blok;

                      if (item is Map) {
                        nama =
                            item['nama']?.toString() ?? 'Peserta ${index + 1}';
                        blok = item['blok']?.toString() ?? '-';
                      } else {
                        nama = 'Peserta ${index + 1}';
                        blok = '-';
                      }

                      return Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: Row(
                          children: [
                            CircleAvatar(
                              radius: 16,
                              backgroundColor: Colors.blue.shade50,
                              child: Text(
                                '${index + 1}',
                                style: TextStyle(
                                  color: Colors.blue.shade800,
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
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
                                    style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    'Blok $blok',
                                    style: TextStyle(
                                      fontSize: 11,
                                      color: Colors.grey.shade600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    }),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.white,
        elevation: 0,
        title: const Text(
          'Detail Jadwal Ronda',
          style: TextStyle(
            color: Color(0xFF1E293B),
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: Padding(padding: const EdgeInsets.all(16), child: _buildDetail()),
    );
  }
}
