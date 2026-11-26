import 'package:flutter/material.dart';

import '../../core/services/dashboard_service.dart';
import '../../layouts/main_layout.dart';

class DashboardScreen extends StatefulWidget {
  final Map<String, dynamic> pengguna;

  const DashboardScreen({super.key, required Map<String, dynamic>? pengguna})
    : pengguna = pengguna ?? const {};

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  Map<String, dynamic>? _dashboardData;

  String get _role => widget.pengguna['role']?.toString() ?? '';

  @override
  void initState() {
    super.initState();
    _ambilDataDashboard();
  }

  Future<void> _ambilDataDashboard() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await DashboardService.getDashboard(role: _role);
      final data = response['data'];
      final dashboardData = data is Map
          ? Map<String, dynamic>.from(data)
          : <String, dynamic>{};
      if (_role == 'warga_biasa') {
        dashboardData['jadwal_ronda_saya'] = _jadwalRondaPengguna(
          dashboardData['jadwal_ronda_saya'],
        );
      }

      if (!mounted) return;

      setState(() {
        _dashboardData = dashboardData;
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

  List<dynamic> _jadwalRondaPengguna(dynamic jadwalData) {
    final idPengguna = widget.pengguna['id']?.toString();
    if (idPengguna == null || jadwalData is! List) return const [];

    return jadwalData.where((item) {
      if (item is! Map || item['peserta'] is! List) return false;
      return (item['peserta'] as List).any(
        (id) => id?.toString() == idPengguna,
      );
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    final sapaan = _dashboardData?['sapaan']?.toString() ?? 'Selamat Datang';
    final nama =
        widget.pengguna['nama']?.toString() ??
        widget.pengguna['username']?.toString() ??
        'Pengguna';

    return MainLayout(
      title: 'Dashboard',
      selectedMenu: 'Dashboard',
      pengguna: widget.pengguna,
      child: RefreshIndicator(
        onRefresh: _ambilDataDashboard,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                '$sapaan, $nama!',
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF1E293B),
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Berikut ringkasan informasi kegiatan dan status lingkungan RT.',
                style: TextStyle(fontSize: 14, color: Color(0xFF64748B)),
              ),
              const SizedBox(height: 28),
              if (_isLoading)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.symmetric(vertical: 40),
                    child: CircularProgressIndicator(),
                  ),
                )
              else if (_errorMessage != null)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  margin: const EdgeInsets.only(bottom: 24),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFFCA5A5)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.error_outline, color: Color(0xFFDC2626)),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          _errorMessage!,
                          style: const TextStyle(color: Color(0xFF991B1B)),
                        ),
                      ),
                      TextButton(
                        onPressed: _ambilDataDashboard,
                        child: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              else ...[
                _buildInfoCards(),
                const SizedBox(height: 28),
                _buildAktivitasSection(),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoCards() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final cards = _getCardsForRole();
        final double cardWidth = constraints.maxWidth >= 900
            ? (constraints.maxWidth - 12 * (cards.length >= 4 ? 3 : 2)) /
                  (cards.length >= 4 ? 4 : 3)
            : constraints.maxWidth >= 550
            ? (constraints.maxWidth - 12) / 2
            : constraints.maxWidth;

        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: cards.map((c) {
            return SizedBox(
              width: cardWidth,
              child: _buildInfoCard(
                title: c['title'] as String,
                value: c['value'] as String,
                icon: c['icon'] as IconData,
                color: c['color'] as Color,
              ),
            );
          }).toList(),
        );
      },
    );
  }

  List<Map<String, dynamic>> _getCardsForRole() {
    final d = _dashboardData ?? {};

    if (_role == 'ketua_rt') {
      return [
        {
          'title': 'Warga Aktif',
          'value': '${d['total_warga_aktif'] ?? 0}',
          'icon': Icons.people_outline,
          'color': const Color(0xFF2563EB),
        },
        {
          'title': 'Warga Nonaktif',
          'value': '${d['total_warga_nonaktif'] ?? 0}',
          'icon': Icons.person_off_outlined,
          'color': const Color(0xFF64748B),
        },
        {
          'title': 'Kegiatan Menunggu',
          'value': '${d['total_kegiatan_menunggu_persetujuan'] ?? 0}',
          'icon': Icons.pending_actions_outlined,
          'color': const Color(0xFFD97706),
        },
        {
          'title': 'Pengumuman Terbit',
          'value': '${(d['pengumuman_terbaru'] as List?)?.length ?? 0}',
          'icon': Icons.campaign_outlined,
          'color': const Color(0xFF0891B2),
        },
      ];
    }

    if (_role == 'warga_pj_kegiatan_dan_jadwal_ronda') {
      return [
        {
          'title': 'Kegiatan Warga',
          'value': '${d['total_kegiatan_saya'] ?? 0}',
          'icon': Icons.event_note_outlined,
          'color': const Color(0xFF2563EB),
        },
        {
          'title': 'Pengumuman Warga',
          'value': '${d['total_pengumuman_saya'] ?? 0}',
          'icon': Icons.campaign_outlined,
          'color': const Color(0xFF0891B2),
        },
        {
          'title': 'Jadwal Ronda Warga',
          'value': '${d['total_jadwal_ronda_saya'] ?? 0}',
          'icon': Icons.calendar_month_outlined,
          'color': const Color(0xFF16A34A),
        },
      ];
    }

    // Warga Biasa
    final jadwalRonda = (d['jadwal_ronda_saya'] as List?)?.length ?? 0;
    final pengumuman = (d['pengumuman_terbaru'] as List?)?.length ?? 0;
    final kegiatan = (d['kegiatan_terbaru'] as List?)?.length ?? 0;

    return [
      {
        'title': 'Jadwal Ronda Anda',
        'value': '$jadwalRonda',
        'icon': Icons.calendar_month_outlined,
        'color': const Color(0xFF16A34A),
      },
      {
        'title': 'Pengumuman',
        'value': '$pengumuman',
        'icon': Icons.campaign_outlined,
        'color': const Color(0xFF0891B2),
      },
      {
        'title': 'Kegiatan Lingkungan',
        'value': '$kegiatan',
        'icon': Icons.event_note_outlined,
        'color': const Color(0xFF2563EB),
      },
    ];
  }

  Widget _buildInfoCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 13,
                    color: Color(0xFF64748B),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1E293B),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAktivitasSection() {
    final d = _dashboardData ?? {};

    if (_role == 'warga_pj_kegiatan_dan_jadwal_ronda') {
      return Column(
        children: [
          _buildDataSection(
            title: 'Kegiatan Warga',
            data: d['kegiatan_saya'] as List?,
            jenis: 'kegiatan',
            icon: Icons.event_note_outlined,
            color: const Color(0xFF2563EB),
            route: '/kegiatan',
          ),
          const SizedBox(height: 16),
          _buildDataSection(
            title: 'Pengumuman Warga',
            data: d['pengumuman_saya'] as List?,
            jenis: 'pengumuman',
            icon: Icons.campaign_outlined,
            color: const Color(0xFF0891B2),
            route: '/pengumuman',
          ),
          const SizedBox(height: 16),
          _buildDataSection(
            title: 'Jadwal Ronda Warga',
            data: d['jadwal_ronda_saya'] as List?,
            jenis: 'jadwal_ronda',
            icon: Icons.calendar_month_outlined,
            color: const Color(0xFF16A34A),
            route: '/jadwal-ronda',
          ),
        ],
      );
    }

    if (_role == 'warga_biasa') {
      return _buildWargaBiasaSections(d);
    }

    final aktivitas = d['aktivitas_terbaru'] as List?;
    final pengumuman = d['pengumuman_terbaru'] as List?;
    final kegiatanSaya = d['kegiatan_saya_terbaru'] as List?;
    final kegiatanWarga = d['kegiatan_terbaru'] as List?;

    if (_role == 'ketua_rt' && aktivitas != null && aktivitas.isNotEmpty) {
      return _buildContainerList(
        judul: 'Aktivitas Terbaru Sistem',
        items: aktivitas.map((item) {
          final m = item is Map ? item : {};
          final pengguna = m['pengguna'] is Map ? m['pengguna']['nama'] : '-';
          return ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const CircleAvatar(
              backgroundColor: Color(0xFFEFF6FF),
              child: Icon(Icons.history, color: Color(0xFF2563EB), size: 20),
            ),
            title: Text(
              m['aktivitas']?.toString() ?? '-',
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
            ),
            subtitle: Text(
              '${m['deskripsi'] ?? ''} • oleh $pengguna',
              style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
            ),
          );
        }).toList(),
      );
    }

    if (pengumuman != null && pengumuman.isNotEmpty) {
      return _buildContainerList(
        judul: 'Pengumuman Terbaru',
        items: pengumuman.map((item) {
          final m = item is Map ? item : {};
          final pembuat = m['pengguna'] is Map ? m['pengguna']['nama'] : '-';
          return ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const CircleAvatar(
              backgroundColor: Color(0xFFECFEFF),
              child: Icon(Icons.campaign, color: Color(0xFF0891B2), size: 20),
            ),
            title: Text(
              m['judul']?.toString() ?? '-',
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
            ),
            subtitle: Text(
              'Diterbitkan oleh $pembuat',
              style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
            ),
          );
        }).toList(),
      );
    }

    final daftarKegiatan = kegiatanSaya ?? kegiatanWarga;
    if (daftarKegiatan != null && daftarKegiatan.isNotEmpty) {
      return _buildContainerList(
        judul: 'Kegiatan Terbaru',
        items: daftarKegiatan.map((item) {
          final m = item is Map ? item : {};
          return ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const CircleAvatar(
              backgroundColor: Color(0xFFFEF3C7),
              child: Icon(Icons.event, color: Color(0xFFD97706), size: 20),
            ),
            title: Text(
              m['nama_kegiatan']?.toString() ?? '-',
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
            ),
            subtitle: Text(
              'Status: ${m['status'] ?? '-'} • Lokasi: ${m['lokasi'] ?? '-'}',
              style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
            ),
          );
        }).toList(),
      );
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: const Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Aktivitas Lingkungan',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E293B),
            ),
          ),
          SizedBox(height: 20),
          Center(
            child: Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Column(
                children: [
                  Icon(
                    Icons.inbox_outlined,
                    size: 40,
                    color: Color(0xFF94A3B8),
                  ),
                  SizedBox(height: 12),
                  Text(
                    'Belum ada data aktivitas terbaru.',
                    style: TextStyle(color: Color(0xFF64748B)),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildWargaBiasaSections(Map<String, dynamic> data) {
    return Column(
      children: [
        _buildWargaBiasaSection(
          title: 'Jadwal Ronda Anda',
          items: data['jadwal_ronda_saya'] as List?,
          jenis: 'jadwal_ronda',
          route: '/jadwal-ronda',
        ),
        const SizedBox(height: 12),
        _buildWargaBiasaSection(
          title: 'Pengumuman',
          items: data['pengumuman_terbaru'] as List?,
          jenis: 'pengumuman',
          route: '/pengumuman',
          tampilkanSemua: true,
        ),
        const SizedBox(height: 12),
        _buildWargaBiasaSection(
          title: 'Kegiatan Lingkungan',
          items: data['kegiatan_terbaru'] as List?,
          jenis: 'kegiatan',
          route: '/kegiatan',
        ),
      ],
    );
  }

  Widget _buildWargaBiasaSection({
    required String title,
    required List<dynamic>? items,
    required String jenis,
    required String route,
    bool tampilkanSemua = false,
  }) {
    final daftar = items ?? const [];
    final yangDitampilkan = tampilkanSemua ? daftar : daftar.take(3).toList();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF1E293B),
                  ),
                ),
              ),
              TextButton(
                onPressed: () => _bukaSemua(route),
                child: const Text('Lihat semuanya'),
              ),
            ],
          ),
          if (yangDitampilkan.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 12),
              child: Text(
                'Belum ada data.',
                style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
              ),
            )
          else
            ...yangDitampilkan.asMap().entries.map((entry) {
              final item = entry.value;
              final jadwal = item is Map
                  ? Map<String, dynamic>.from(item)
                  : <String, dynamic>{};
              final judul = switch (jenis) {
                'pengumuman' => jadwal['judul']?.toString() ?? '-',
                'kegiatan' => jadwal['nama_kegiatan']?.toString() ?? '-',
                _ => 'Gang ${jadwal['gang'] ?? '-'}',
              };
              final keterangan = switch (jenis) {
                'pengumuman' => 'Pengumuman warga',
                'kegiatan' => 'Lokasi: ${jadwal['lokasi'] ?? '-'}',
                _ => _keteranganJadwal(jadwal),
              };
              return Column(
                children: [
                  if (entry.key > 0) const Divider(height: 1),
                  Padding(
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    child: Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                judul,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              const SizedBox(height: 3),
                              Text(
                                keterangan,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 12,
                                  color: Color(0xFF64748B),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              );
            }),
        ],
      ),
    );
  }

  Widget _buildDataSection({
    required String title,
    required List<dynamic>? data,
    required String jenis,
    required IconData icon,
    required Color color,
    required String route,
  }) {
    final items = data ?? const [];

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E293B),
            ),
          ),
          const SizedBox(height: 12),
          if (items.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Center(
                child: Text(
                  'Belum ada data untuk ditampilkan.',
                  style: TextStyle(color: Color(0xFF64748B)),
                ),
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: items.length > 3 ? 3 : items.length,
              separatorBuilder: (context, index) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final item = items[index];
                final dataItem = item is Map
                    ? Map<String, dynamic>.from(item)
                    : <String, dynamic>{};
                final judul = switch (jenis) {
                  'kegiatan' => dataItem['nama_kegiatan']?.toString() ?? '-',
                  'pengumuman' => dataItem['judul']?.toString() ?? '-',
                  _ => 'Jadwal Ronda ${dataItem['gang'] ?? '-'}',
                };
                final keterangan = switch (jenis) {
                  'kegiatan' =>
                    'Status: ${_labelStatus(dataItem['status'])} • '
                        'Lokasi: ${dataItem['lokasi'] ?? '-'}',
                  'pengumuman' => 'Status: ${_labelStatus(dataItem['status'])}',
                  _ => _keteranganJadwal(dataItem),
                };

                return ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: CircleAvatar(
                    backgroundColor: color.withValues(alpha: 0.1),
                    child: Icon(icon, color: color, size: 20),
                  ),
                  title: Text(
                    judul,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontWeight: FontWeight.w600,
                      fontSize: 14,
                    ),
                  ),
                  subtitle: Text(
                    keterangan,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 12,
                      color: Color(0xFF64748B),
                    ),
                  ),
                );
              },
            ),
          Align(
            alignment: Alignment.centerRight,
            child: TextButton.icon(
              onPressed: () {
                _bukaSemua(route);
              },
              icon: const Icon(Icons.arrow_forward, size: 16),
              label: const Text('Lihat semua'),
            ),
          ),
        ],
      ),
    );
  }

  String _labelStatus(dynamic status) {
    return switch (status?.toString()) {
      'draft' => 'Draft',
      'menunggu_persetujuan' => 'Menunggu Persetujuan',
      'disetujui' || 'terbit' => 'Disetujui',
      'ditolak' => 'Ditolak',
      'nonaktif' => 'Nonaktif',
      'aktif' => 'Aktif',
      'selesai' => 'Selesai',
      _ => status?.toString() ?? '-',
    };
  }

  String _keteranganJadwal(Map<String, dynamic> data) {
    final hari = data['hari'] is List
        ? (data['hari'] as List).join(', ')
        : data['hari']?.toString() ?? '-';
    final mulai = _formatWaktu(data['waktu_mulai']);
    final selesai = _formatWaktu(data['waktu_selesai']);

    return '$hari • $mulai-$selesai • ${_labelStatus(data['status'])}';
  }

  String _formatWaktu(dynamic waktu) {
    if (waktu == null) return '-';
    final value = waktu.toString();
    return value.length >= 5 ? value.substring(0, 5) : value;
  }

  Future<void> _bukaSemua(String route) async {
    await Navigator.pushNamed(context, route, arguments: widget.pengguna);
    if (mounted) await _ambilDataDashboard();
  }

  Widget _buildContainerList({
    required String judul,
    required List<Widget> items,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            judul,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E293B),
            ),
          ),
          const SizedBox(height: 12),
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: items.length,
            separatorBuilder: (context, index) => const Divider(height: 1),
            itemBuilder: (_, index) => items[index],
          ),
        ],
      ),
    );
  }
}
