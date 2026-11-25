import 'api_service.dart';
import 'auth_service.dart';

class DashboardService {
  static const String _endpointKetuaRT = '/ketua-rt/dashboard';
  static const String _endpointWargaPJ = '/warga-pj/dashboard';
  static const String _endpointWargaBiasa = '/warga-biasa/dashboard';

  static Future<String> _getToken() async {
    final token = await AuthService.getToken();

    if (token == null || token.isEmpty) {
      throw Exception('Token autentikasi tidak ditemukan.');
    }

    return token;
  }

  static String endpointBerdasarkanRole(String? role) {
    switch (role) {
      case 'ketua_rt':
        return _endpointKetuaRT;
      case 'warga_pj_kegiatan_dan_jadwal_ronda':
        return _endpointWargaPJ;
      case 'warga_biasa':
      default:
        return _endpointWargaBiasa;
    }
  }

  // Mengambil data ringkasan dashboard
  static Future<Map<String, dynamic>> getDashboard({String? role}) async {
    final rolePengguna = role ?? await AuthService.getRole();
    final endpoint = endpointBerdasarkanRole(rolePengguna);

    return await ApiService.get(endpoint, token: await _getToken());
  }
}
