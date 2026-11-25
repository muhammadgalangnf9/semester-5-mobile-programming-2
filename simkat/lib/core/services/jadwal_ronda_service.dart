import 'api_service.dart';
import 'auth_service.dart';

class JadwalRondaService {
  static const String _endpointKetuaRT = '/ketua-rt/jadwal-ronda';
  static const String _endpointWargaPJ = '/warga-pj/jadwal-ronda';
  static const String _endpointWargaBiasa = '/warga-biasa/jadwal-ronda';

  // Mengambil token autentikasi
  static Future<String> _getToken() async {
    final token = await AuthService.getToken();

    if (token == null || token.isEmpty) {
      throw Exception('Token autentikasi tidak ditemukan.');
    }

    return token;
  }

  // Menentukan endpoint berdasarkan role
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

  // Mengambil daftar jadwal ronda sesuai role
  static Future<Map<String, dynamic>> getJadwalRonda({
    String? role,
    int page = 1,
  }) async {
    final rolePengguna = role ?? await AuthService.getRole();
    final endpoint = endpointBerdasarkanRole(rolePengguna);

    return await ApiService.get(
      '$endpoint?page=$page',
      token: await _getToken(),
    );
  }

  // Mengambil daftar jadwal ronda untuk pengelola (Warga PJ)
  static Future<Map<String, dynamic>> getJadwalRondaKelola({
    int page = 1,
    String? gang,
    String? hari,
  }) async {
    final parameters = <String, String>{'page': page.toString()};
    if (gang != null && gang != 'Semua Gang') {
      parameters['gang'] = gang;
    }
    if (hari != null && hari != 'Semua Hari') {
      parameters['hari'] = hari;
    }
    final query = Uri(queryParameters: parameters).query;

    return ApiService.get('$_endpointWargaPJ?$query', token: await _getToken());
  }

  // Mengambil daftar peserta ronda
  static Future<List<Map<String, dynamic>>> getDaftarPeserta() async {
    final response = await ApiService.get(
      '$_endpointWargaPJ/peserta',
      token: await _getToken(),
    );

    final data = response['data'];

    if (data is! List) {
      throw Exception('Format daftar peserta ronda tidak valid.');
    }

    return data
        .whereType<Map>()
        .map((item) => Map<String, dynamic>.from(item))
        .toList();
  }

  // Mengambil detail jadwal ronda sesuai role
  static Future<Map<String, dynamic>> getDetailJadwalRonda(
    int id, {
    String? role,
  }) async {
    final rolePengguna = role ?? await AuthService.getRole();
    final endpoint = endpointBerdasarkanRole(rolePengguna);

    return await ApiService.get('$endpoint/$id', token: await _getToken());
  }

  // Mengambil detail jadwal ronda untuk pengelola (Warga PJ)
  static Future<Map<String, dynamic>> getDetailJadwalRondaKelola(int id) async {
    return ApiService.get('$_endpointWargaPJ/$id', token: await _getToken());
  }

  // Menambahkan jadwal ronda (Warga PJ)
  static Future<Map<String, dynamic>> tambahJadwalRonda(
    Map<String, dynamic> data,
  ) async {
    return ApiService.post(
      _endpointWargaPJ,
      body: data,
      token: await _getToken(),
    );
  }

  // Mengubah jadwal ronda (Warga PJ)
  static Future<Map<String, dynamic>> ubahJadwalRonda(
    int id,
    Map<String, dynamic> data,
  ) async {
    return ApiService.put(
      '$_endpointWargaPJ/$id',
      body: data,
      token: await _getToken(),
    );
  }

  // Mengubah status jadwal ronda (Warga PJ)
  static Future<Map<String, dynamic>> ubahStatusJadwalRonda(
    int id,
    String status,
  ) async {
    return ApiService.patch(
      '$_endpointWargaPJ/$id/status',
      body: {'status': status},
      token: await _getToken(),
    );
  }

  // Menghapus/menonaktifkan jadwal ronda (Warga PJ)
  static Future<Map<String, dynamic>> hapusJadwalRonda(int id) async {
    return ApiService.delete('$_endpointWargaPJ/$id', token: await _getToken());
  }
}
