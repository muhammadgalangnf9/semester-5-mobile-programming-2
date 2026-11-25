import 'package:flutter/material.dart';

import 'core/theme/app_theme.dart';
import 'screens/auth/login_screen.dart';
import 'screens/dashboard/dashboard_screen.dart';
import 'screens/kelola_warga/kelola_warga_screen.dart';
import 'screens/pengumuman/pengumuman_screen.dart';
import 'screens/jadwal_ronda/jadwal_ronda_screen.dart';
import 'screens/kegiatan/kegiatan_screen.dart';
import 'screens/kegiatan/detail_kegiatan_screen.dart';

void main() {
  runApp(const SimkatApp());
}

class SimkatApp extends StatelessWidget {
  const SimkatApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SIMKAT',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      home: const LoginScreen(),
      onGenerateRoute: _onGenerateRoute,
    );
  }

  Route<dynamic>? _onGenerateRoute(RouteSettings settings) {
    final arguments = settings.arguments;

    final pengguna = arguments is Map
        ? Map<String, dynamic>.from(arguments)
        : <String, dynamic>{};

    switch (settings.name) {
      case '/dashboard':
        return MaterialPageRoute(
          builder: (_) => DashboardScreen(
            pengguna: pengguna,
          ),
        );

      case '/kelola-warga':
        return MaterialPageRoute(
          builder: (_) => KelolaWargaScreen(
            pengguna: pengguna,
          ),
        );

      case '/pengumuman':
        return MaterialPageRoute(
          builder: (_) => PengumumanScreen(
            pengguna: pengguna,
          ),
        );

      case '/jadwal-ronda':
        return MaterialPageRoute(
          builder: (_) => JadwalRondaScreen(
            pengguna: pengguna,
          ),
        );

      case '/kegiatan':
        return MaterialPageRoute(
          builder: (_) => KegiatanScreen(
            pengguna: pengguna,
          ),
        );

      case '/detail-kegiatan':
        final detailArguments = arguments is Map
            ? Map<String, dynamic>.from(arguments)
            : <String, dynamic>{};

        final kegiatanId = int.tryParse(
          detailArguments['kegiatanId']?.toString() ?? '',
        );

        if (kegiatanId == null) {
          return null;
        }

        final penggunaDetail = detailArguments['pengguna'];
        final role = penggunaDetail is Map
            ? penggunaDetail['role']?.toString()
            : null;

        return MaterialPageRoute(
          builder: (_) => DetailKegiatanScreen(
            kegiatanId: kegiatanId,
            role: role ?? '',
          ),
        );

      default:
        return null;
    }
  }
}
