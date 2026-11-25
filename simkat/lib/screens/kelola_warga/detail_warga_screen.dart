import 'package:flutter/material.dart';

import '../../core/services/kelola_warga_service.dart';

class DetailWargaScreen extends StatefulWidget {
  final int wargaId;

  const DetailWargaScreen({super.key, required this.wargaId});

  @override
  State<DetailWargaScreen> createState() => _DetailWargaScreenState();
}

class _DetailWargaScreenState extends State<DetailWargaScreen> {
  Map<String, dynamic>? _warga;

  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _ambilDetailWarga();
  }

  Future<void> _ambilDetailWarga() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await KelolaWargaService.getDetailWarga(widget.wargaId);

      if (!mounted) return;

      final data = response['data'];

      if (data is! Map) {
        throw Exception('Format data warga tidak valid.');
      }

      setState(() {
        _warga = Map<String, dynamic>.from(data);
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

  String _ambilNilai(dynamic value) {
    if (value == null || value.toString().trim().isEmpty) {
      return '-';
    }

    return value.toString();
  }

  String _formatTanggal(dynamic value) {
    final teks = value?.toString().trim();

    if (teks == null || teks.isEmpty) {
      return '-';
    }

    final tanggal = DateTime.tryParse(teks)?.toLocal();

    if (tanggal == null) {
      return '-';
    }

    final hari = tanggal.day.toString().padLeft(2, '0');
    final bulan = tanggal.month.toString().padLeft(2, '0');
    final tahun = tanggal.year.toString();
    final jam = tanggal.hour.toString().padLeft(2, '0');
    final menit = tanggal.minute.toString().padLeft(2, '0');

    return '$hari-$bulan-$tahun, $jam:$menit';
  }

  String _formatJenisKelamin(dynamic value) {
    switch (value?.toString()) {
      case 'laki_laki':
        return 'Laki-laki';
      case 'perempuan':
        return 'Perempuan';
      default:
        return '-';
    }
  }

  String _formatRole(Map<String, dynamic> warga) {
    final role = warga['role'];

    if (role is Map) {
      return _ambilNilai(role['nama_role']);
    }

    return 'Warga';
  }

  Color _warnaStatus(String status) {
    return status == 'aktif' ? Colors.green.shade700 : Colors.red.shade700;
  }

  Widget _buildBagian({
    required String judul,
    required List<Widget> children,
    IconData? icon,
  }) {
    return Container(
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
          Row(
            children: [
              if (icon != null) ...[
                Icon(icon, size: 19, color: Colors.blue.shade700),
                const SizedBox(width: 8),
              ],
              Text(
                judul,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          ...children,
        ],
      ),
    );
  }

  Widget _buildInfo({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 19, color: Colors.grey.shade500),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
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
                onPressed: _ambilDetailWarga,
                icon: const Icon(Icons.refresh, size: 18),
                label: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (_warga == null) {
      return const Center(child: Text('Data warga tidak ditemukan.'));
    }

    final warga = _warga!;
    final nama = _ambilNilai(warga['nama']);
    final username = _ambilNilai(warga['username']);
    final status = _ambilNilai(warga['status']);
    final warnaStatus = _warnaStatus(status);

    return RefreshIndicator(
      onRefresh: _ambilDetailWarga,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Identitas Warga
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: Colors.blue.shade50,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                CircleAvatar(
                  radius: 34,
                  backgroundColor: Colors.blue.shade100,
                  child: Text(
                    nama != '-' ? nama[0].toUpperCase() : '?',
                    style: TextStyle(
                      fontSize: 26,
                      fontWeight: FontWeight.bold,
                      color: Colors.blue.shade800,
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  nama,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '@$username',
                  style: TextStyle(fontSize: 13, color: Colors.grey.shade700),
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: warnaStatus.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    status.toUpperCase(),
                    style: TextStyle(
                      color: warnaStatus,
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Informasi Akun
          _buildBagian(
            judul: 'Informasi Akun',
            icon: Icons.manage_accounts_outlined,
            children: [
              _buildInfo(
                icon: Icons.person_outline,
                label: 'Nama Lengkap',
                value: nama,
              ),
              _buildInfo(
                icon: Icons.alternate_email,
                label: 'Username',
                value: username,
              ),
              _buildInfo(
                icon: Icons.badge_outlined,
                label: 'Role',
                value: _formatRole(warga),
              ),
            ],
          ),

          const SizedBox(height: 12),

          // Informasi Pribadi
          _buildBagian(
            judul: 'Informasi Pribadi',
            icon: Icons.person_outline,
            children: [
              _buildInfo(
                icon: Icons.wc_outlined,
                label: 'Jenis Kelamin',
                value: _formatJenisKelamin(warga['jenis_kelamin']),
              ),
              _buildInfo(
                icon: Icons.location_on_outlined,
                label: 'Gang',
                value: _ambilNilai(warga['gang']),
              ),
              _buildInfo(
                icon: Icons.home_work_outlined,
                label: 'Blok',
                value: _ambilNilai(warga['blok']),
              ),
              _buildInfo(
                icon: Icons.phone_outlined,
                label: 'Nomor HP',
                value: _ambilNilai(warga['no_hp']),
              ),
              _buildInfo(
                icon: Icons.email_outlined,
                label: 'Email',
                value: _ambilNilai(warga['email']),
              ),
            ],
          ),

          const SizedBox(height: 12),

          // Riwayat Akun
          _buildBagian(
            judul: 'Riwayat Akun',
            icon: Icons.history_outlined,
            children: [
              _buildInfo(
                icon: Icons.event_busy_outlined,
                label: 'Dinonaktifkan pada',
                value: _formatTanggal(warga['dinonaktifkan_pada']),
              ),
              _buildInfo(
                icon: Icons.add_circle_outline,
                label: 'Dibuat pada',
                value: _formatTanggal(warga['dibuat'] ?? warga['created_at']),
              ),
              _buildInfo(
                icon: Icons.update_outlined,
                label: 'Diupdate pada',
                value: _formatTanggal(warga['diupdate'] ?? warga['updated_at']),
              ),
            ],
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
        title: const Text(
          'Detail Warga',
          style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(color: Colors.grey.shade200, height: 1),
        ),
      ),
      body: _buildKonten(),
    );
  }
}
