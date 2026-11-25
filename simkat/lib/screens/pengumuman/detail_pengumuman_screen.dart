import 'package:flutter/material.dart';

import '../../core/services/auth_service.dart';
import '../../core/services/pengumuman_service.dart';

class DetailPengumumanScreen extends StatefulWidget {
  final int pengumumanId;
  final String role;

  const DetailPengumumanScreen({
    super.key,
    required this.pengumumanId,
    this.role = '',
  });

  @override
  State<DetailPengumumanScreen> createState() => _DetailPengumumanScreenState();
}

class _DetailPengumumanScreenState extends State<DetailPengumumanScreen> {
  Map<String, dynamic>? _pengumuman;

  bool _isLoading = true;
  bool _isProcessing = false;
  String _roleAktual = '';
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _ambilDetailPengumuman();
  }

  Future<void> _ambilDetailPengumuman() async {
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

      final isPengelola = role == 'warga_pj_kegiatan_dan_jadwal_ronda';
      final response = isPengelola
          ? await PengumumanService.getDetailPengumumanKelola(
              widget.pengumumanId,
            )
          : await PengumumanService.getDetailPengumuman(
              widget.pengumumanId,
              role: role,
            );

      if (!mounted) return;

      setState(() {
        _roleAktual = role;
        _pengumuman = Map<String, dynamic>.from(response['data'] as Map);
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
                  value,
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

  String _labelStatus(String status) {
    return switch (status) {
      'draft' => 'Draft',
      'menunggu_persetujuan' => 'Menunggu Persetujuan',
      'terbit' => 'Disetujui',
      'ditolak' => 'Ditolak',
      'nonaktif' => 'Nonaktif',
      _ => status,
    };
  }

  Future<void> _prosesPersetujuan({required bool disetujui}) async {
    final id = widget.pengumumanId;
    String? catatan;

    if (!disetujui) {
      final controller = TextEditingController();
      catatan = await showDialog<String>(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Tolak pengumuman'),
          content: TextField(
            controller: controller,
            autofocus: true,
            minLines: 2,
            maxLines: 4,
            decoration: const InputDecoration(
              labelText: 'Alasan penolakan',
              border: OutlineInputBorder(),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal'),
            ),
            FilledButton(
              onPressed: () {
                final value = controller.text.trim();
                if (value.isNotEmpty) Navigator.pop(context, value);
              },
              child: const Text('Tolak'),
            ),
          ],
        ),
      );
      controller.dispose();
      if (catatan == null) return;
    }

    setState(() => _isProcessing = true);
    try {
      if (disetujui) {
        await PengumumanService.setujuiPengumuman(id);
      } else {
        await PengumumanService.tolakPengumuman(id, catatan: catatan!);
      }

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            disetujui
                ? 'Pengumuman disetujui dan diterbitkan.'
                : 'Pengumuman ditolak.',
          ),
        ),
      );
      await _ambilDetailPengumuman();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: const Text(
          'Detail Pengumuman',
          style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        surfaceTintColor: Colors.white,
      ),
      body: _buildKonten(),
      bottomNavigationBar: _buildAksiPersetujuan(),
    );
  }

  Widget? _buildAksiPersetujuan() {
    if (_roleAktual != 'ketua_rt' ||
        _pengumuman?['status'] != 'menunggu_persetujuan') {
      return null;
    }

    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
        child: Row(
          children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed: _isProcessing
                    ? null
                    : () => _prosesPersetujuan(disetujui: false),
                icon: const Icon(Icons.close),
                label: const Text('Tolak'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: FilledButton.icon(
                onPressed: _isProcessing
                    ? null
                    : () => _prosesPersetujuan(disetujui: true),
                icon: _isProcessing
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.check),
                label: const Text('Setujui'),
              ),
            ),
          ],
        ),
      ),
    );
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
                onPressed: _ambilDetailPengumuman,
                icon: const Icon(Icons.refresh, size: 18),
                label: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (_pengumuman == null) {
      return const Center(child: Text('Data pengumuman tidak ditemukan.'));
    }

    final judul = _pengumuman!['judul']?.toString() ?? '-';
    final isi = _pengumuman!['isi']?.toString() ?? '-';
    final status = _pengumuman!['status']?.toString() ?? 'terbit';

    final pengguna = _pengumuman!['pengguna'];
    final namaPembuat = pengguna is Map
        ? pengguna['nama']?.toString() ?? '-'
        : '-';

    return RefreshIndicator(
      onRefresh: _ambilDetailPengumuman,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Header Pengumuman
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
                      Icons.campaign_outlined,
                      color: Colors.blue.shade700,
                      size: 22,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'INFORMASI RT',
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
                  judul,
                  style: const TextStyle(
                    fontSize: 19,
                    fontWeight: FontWeight.bold,
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 10),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: status == 'terbit'
                        ? Colors.green.shade100
                        : status == 'menunggu_persetujuan'
                        ? Colors.orange.shade100
                        : status == 'ditolak'
                        ? Colors.red.shade100
                        : Colors.grey.shade200,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    _labelStatus(status),
                    style: TextStyle(
                      color: status == 'terbit'
                          ? Colors.green.shade800
                          : status == 'menunggu_persetujuan'
                          ? Colors.orange.shade800
                          : status == 'ditolak'
                          ? Colors.red.shade800
                          : Colors.grey.shade700,
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Informasi Pengumuman
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Informasi Pengumuman',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                _buildInformasi(
                  icon: Icons.person_outline,
                  label: 'Dibuat oleh',
                  value: namaPembuat,
                ),
                if (_pengumuman!['disetujui_oleh'] is Map)
                  _buildInformasi(
                    icon: Icons.verified_user_outlined,
                    label: 'Disetujui oleh',
                    value:
                        (_pengumuman!['disetujui_oleh'] as Map)['nama']
                            ?.toString() ??
                        '-',
                  ),
                if (_pengumuman!['catatan_persetujuan'] != null)
                  _buildInformasi(
                    icon: Icons.comment_outlined,
                    label: status == 'ditolak'
                        ? 'Alasan penolakan'
                        : 'Catatan persetujuan',
                    value: _pengumuman!['catatan_persetujuan'].toString(),
                  ),
                _buildInformasi(
                  icon: Icons.calendar_today_outlined,
                  label: 'Diterbitkan pada',
                  value: _formatTanggal(_pengumuman!['diterbitkan_pada']),
                ),
                _buildInformasi(
                  icon: Icons.event_busy_outlined,
                  label: 'Dinonaktifkan pada',
                  value: _formatTanggal(_pengumuman!['dinonaktifkan_pada']),
                ),
                _buildInformasi(
                  icon: Icons.add_circle_outline,
                  label: 'Dibuat pada',
                  value: _formatTanggal(
                    _pengumuman!['dibuat'] ?? _pengumuman!['created_at'],
                  ),
                ),
                _buildInformasi(
                  icon: Icons.update_outlined,
                  label: 'Diupdate pada',
                  value: _formatTanggal(
                    _pengumuman!['diupdate'] ?? _pengumuman!['updated_at'],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Isi Pengumuman
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Isi Pengumuman',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 14),
                SelectableText(
                  isi,
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.grey.shade800,
                    height: 1.7,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
