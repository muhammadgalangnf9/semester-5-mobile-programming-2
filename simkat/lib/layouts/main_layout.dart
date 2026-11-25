import 'package:flutter/material.dart';

import '../widgets/sidebar.dart';

class MainLayout extends StatelessWidget {
  final String title;
  final String selectedMenu;
  final Widget child;
  final Map<String, dynamic> pengguna;

  const MainLayout({
    super.key,
    required this.title,
    required this.selectedMenu,
    required this.child,
    required Map<String, dynamic>? pengguna,
  }) : pengguna = pengguna ?? const {};

  static const Color primaryColor = Color(0xFF2563EB);
  static const Color textColor = Color(0xFF1E293B);
  static const Color sidebarColor = Color(0xFF1E293B);
  static const Color backgroundColor = Color(0xFFF8FAFC);
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
    {
      'title': 'Kegiatan',
      'key': 'kegiatan',
      'icon': Icons.event_note_outlined,
    },
  ];

  // =========================================================
  // MAIN LAYOUT
  // =========================================================

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final bool isWide = constraints.maxWidth >= 900;

        return Scaffold(
          backgroundColor: backgroundColor,
          drawer: isWide ? null : Drawer(
            child: _buildSidebar(context),
          ),
          appBar: AppBar(
            automaticallyImplyLeading: !isWide,
            backgroundColor: Colors.white,
            surfaceTintColor: Colors.white,
            elevation: 0,
            title: Text(
              title,
              style: const TextStyle(
                color: textColor,
                fontWeight: FontWeight.bold,
                fontSize: 18,
              ),
            ),
          ),
          body: Row(
            children: [
              if (isWide)
                SizedBox(
                  width: 250,
                  child: _buildSidebar(context),
                ),
              Expanded(child: child),
            ],
          ),
        );
      },
    );
  }

  // =========================================================
  // SIDEBAR
  // =========================================================

  Widget _buildSidebar(BuildContext context) {
    final bool isWide = MediaQuery.of(context).size.width >= 900;

    return Sidebar(
      selectedMenu: selectedMenu,
      pengguna: pengguna,
      isDrawer: !isWide,
    );
  }
}
