import 'package:flutter/material.dart';

import '../core/services/auth_service.dart';
import '../screens/auth/login_screen.dart';

class Sidebar extends StatelessWidget {
  final String selectedMenu;
  final Map<String, dynamic> pengguna;
  final bool isDrawer;

  const Sidebar({
    super.key,
    required this.selectedMenu,
    required this.pengguna,
    this.isDrawer = false,
  });

  static const Color primaryColor = Color(0xFF2563EB);
  static const Color sidebarColor = Color(0xFF1E293B);
  static const Color dangerColor = Color(0xFFDC2626);
  static const Color mutedColor = Color(0xFF64748B);

  static const List<Map<String, dynamic>> menus = [
    {
      'title': 'Dashboard',
      'key': 'dashboard',
      'icon': Icons.dashboard_outlined,
    },
    {
      'title': 'Kelola Warga',
      'key': 'kelola_warga',
      'icon': Icons.people_outline,
    },
    {
      'title': 'Pengumuman',
      'key': 'pengumuman',
      'icon': Icons.campaign_outlined,
    },
    {
      'title': 'Jadwal Ronda',
      'key': 'jadwal_ronda',
      'icon': Icons.calendar_month_outlined,
    },
    {'title': 'Kegiatan', 'key': 'kegiatan', 'icon': Icons.event_note_outlined},
  ];

  static const Map<String, String> _menuRoutes = {
    'dashboard': '/dashboard',
    'kelola_warga': '/kelola-warga',
    'pengumuman': '/pengumuman',
    'jadwal_ronda': '/jadwal-ronda',
    'kegiatan': '/kegiatan',
  };

  Map<String, dynamic> get akses {
    final data = pengguna['akses'];
    if (data is Map) {
      return Map<String, dynamic>.from(data);
    }
    return {};
  }

  bool memilikiAkses(String menu, String tindakan) {
    final izinMenu = akses[menu];
    if (izinMenu is List) {
      return izinMenu.contains(tindakan);
    }
    // Jika akses belum dimuat, gunakan role sebagai fallback terbatas
    if (akses.isEmpty) {
      final role = pengguna['role']?.toString();
      if (role == null) return false;
      // kelola_warga: hanya ketua_rt
      if (menu == 'kelola_warga') return role == 'ketua_rt';
      // Semua role valid boleh lihat menu utama (kecuali kelola_warga)
      return role == 'ketua_rt' ||
          role == 'warga_pj_kegiatan_dan_jadwal_ronda' ||
          role == 'warga_biasa';
    }
    return false;
  }

  List<Map<String, dynamic>> get menuDiizinkan {
    return menus.where((menu) {
      return memilikiAkses(menu['key'], 'read');
    }).toList();
  }

  Future<void> _konfirmasiLogout(BuildContext context) async {
    final bool? konfirmasi = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => AlertDialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        titlePadding: const EdgeInsets.fromLTRB(24, 24, 24, 8),
        contentPadding: const EdgeInsets.fromLTRB(24, 8, 24, 12),
        actionsPadding: const EdgeInsets.fromLTRB(24, 0, 24, 24),
        title: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: const Color(0xFFFEE2E2),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.logout_rounded,
                color: dangerColor,
                size: 22,
              ),
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Text(
                'Konfirmasi Keluar',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF1E293B),
                ),
              ),
            ),
          ],
        ),
        content: const Text(
          'Apakah kamu yakin ingin keluar dari aplikasi SIMKAT?',
          style: TextStyle(fontSize: 14, color: mutedColor, height: 1.5),
        ),
        actions: [
          Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              ElevatedButton(
                onPressed: () => Navigator.pop(dialogContext, true),
                style: ElevatedButton.styleFrom(
                  backgroundColor: dangerColor,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                child: const Text(
                  'Keluar',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                ),
              ),
              const SizedBox(height: 8),
              TextButton(
                onPressed: () => Navigator.pop(dialogContext, false),
                style: TextButton.styleFrom(
                  foregroundColor: mutedColor,
                  padding: const EdgeInsets.symmetric(vertical: 8),
                ),
                child: const Text(
                  'Batal',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w500),
                ),
              ),
            ],
          ),
        ],
      ),
    );

    if (konfirmasi != true || !context.mounted) return;

    try {
      await AuthService.logout();
    } catch (e) {
      debugPrint('Logout API gagal: $e');
    }

    if (!context.mounted) return;

    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Material(
      color: sidebarColor,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Padding(
            padding: EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(Icons.groups_rounded, color: Colors.white, size: 36),
                SizedBox(height: 12),
                Text(
                  'SIMKAT',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  'Manajemen Kegiatan RT',
                  style: TextStyle(color: Colors.white70, fontSize: 12),
                ),
              ],
            ),
          ),
          const Divider(color: Colors.white24, height: 1),
          const SizedBox(height: 16),
          ...menuDiizinkan.map((menu) => _buildMenuItem(context, menu)),
          const Spacer(),
          const Divider(color: Colors.white24, height: 1),
          ListTile(
            leading: const Icon(Icons.logout, color: Colors.white70),
            title: const Text(
              'Keluar',
              style: TextStyle(color: Colors.white70),
            ),
            onTap: () => _konfirmasiLogout(context),
          ),
          const SizedBox(height: 12),
        ],
      ),
    );
  }

  Widget _buildMenuItem(BuildContext context, Map<String, dynamic> menu) {
    final String menuTitle = menu['title'];
    final String menuKey = menu['key'];
    final bool isSelected = selectedMenu == menuTitle;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 3),
      child: ListTile(
        selected: isSelected,
        selectedTileColor: primaryColor,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        leading: Icon(
          menu['icon'],
          color: isSelected ? Colors.white : Colors.white70,
        ),
        title: Text(
          menuTitle,
          style: TextStyle(
            color: isSelected ? Colors.white : Colors.white70,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          ),
        ),
        onTap: () {
          final navigator = Navigator.of(context);
          if (isDrawer) {
            navigator.pop();
          }
          if (isSelected) return;

          final route = _menuRoutes[menuKey];
          if (route == null) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text('Halaman $menuTitle belum tersedia.')),
            );
            return;
          }

          navigator.pushReplacementNamed(route, arguments: pengguna);
        },
      ),
    );
  }
}
