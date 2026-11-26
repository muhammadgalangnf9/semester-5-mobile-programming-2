import 'api_service.dart';
import 'auth_service.dart';

class PengumumanService {
  static const String _endpointKetuaRT = '/ketua-rt/pengumuman';
  static const String _endpointWargaPJ = '/warga-pj/pengumuman';
  static const String _endpointWargaBiasa = '/warga-biasa/pengumuman';

  static String _queryDaftarPengumuman({
    required int page,
    String? namaPengumuman,
    String? tanggalDibuat,
    String? status,
  }) {
    final parameters = <String, String>{'page': page.toString()};
    if (namaPengumuman != null && namaPengumuman.trim().isNotEmpty) {
      parameters['nama_pengumuman'] = namaPengumuman.trim();
    }
    if (tanggalDibuat != null && tanggalDibuat.isNotEmpty) {
      parameters['tanggal_dibuat'] = tanggalDibuat;
    }
    if (status != null && status.isNotEmpty) {
      parameters['status'] = status;
    }
    return Uri(queryParameters: parameters).query;
  }

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

  // Mengambil daftar pengumuman sesuai role pengguna
  static Future<Map<String, dynamic>> getPengumuman({
    String? role,
    int page = 1,
    String? namaPengumuman,
    String? tanggalDibuat,
    String? status,
  }) async {
    final rolePengguna = role ?? await AuthService.getRole();
    final endpoint = endpointBerdasarkanRole(rolePengguna);
    final query = _queryDaftarPengumuman(
      page: page,
      namaPengumuman: namaPengumuman,
      tanggalDibuat: tanggalDibuat,
      status: status,
    );

    return await ApiService.get(
      '$endpoint?$query',
      token: await _getToken(),
    );
  }

  // Mengambil daftar pengumuman untuk pengelola (Warga PJ)
  static Future<Map<String, dynamic>> getPengumumanKelola({
    int page = 1,
    String? namaPengumuman,
    String? tanggalDibuat,
    String? status,
  }) async {
    final query = _queryDaftarPengumuman(
      page: page,
      namaPengumuman: namaPengumuman,
      tanggalDibuat: tanggalDibuat,
      status: status,
    );
    return ApiService.get(
      '$_endpointWargaPJ?$query',
      token: await _getToken(),
    );
  }

  // Mengambil detail pengumuman sesuai role pengguna
  static Future<Map<String, dynamic>> getDetailPengumuman(
    int id, {
    String? role,
  }) async {
    final rolePengguna = role ?? await AuthService.getRole();
    final endpoint = endpointBerdasarkanRole(rolePengguna);

    return await ApiService.get('$endpoint/$id', token: await _getToken());
  }

  // Mengambil detail pengumuman untuk pengelola (Warga PJ)
  static Future<Map<String, dynamic>> getDetailPengumumanKelola(int id) async {
    return ApiService.get('$_endpointWargaPJ/$id', token: await _getToken());
  }

  // Menambahkan pengumuman (Warga PJ)
  static Future<Map<String, dynamic>> tambahPengumuman(
    Map<String, dynamic> data,
  ) async {
    return ApiService.post(
      _endpointWargaPJ,
      body: data,
      token: await _getToken(),
    );
  }

  // Mengubah pengumuman (Warga PJ)
  static Future<Map<String, dynamic>> ubahPengumuman(
    int id,
    Map<String, dynamic> data,
  ) async {
    return ApiService.put(
      '$_endpointWargaPJ/$id',
      body: data,
      token: await _getToken(),
    );
  }

  static Future<Map<String, dynamic>> ajukanPersetujuanPengumuman(
    int id,
  ) async {
    return ApiService.patch(
      '$_endpointWargaPJ/$id/ajukan',
      body: const {},
      token: await _getToken(),
    );
  }

  static Future<Map<String, dynamic>> setujuiPengumuman(
    int id, {
    String? catatan,
  }) async {
    return ApiService.patch(
      '$_endpointKetuaRT/$id/setujui',
      body: {'catatan_persetujuan': catatan},
      token: await _getToken(),
    );
  }

  static Future<Map<String, dynamic>> tolakPengumuman(
    int id, {
    required String catatan,
  }) async {
    return ApiService.patch(
      '$_endpointKetuaRT/$id/tolak',
      body: {'catatan_persetujuan': catatan},
      token: await _getToken(),
    );
  }

  // Mengubah status pengumuman (Warga PJ)
  static Future<Map<String, dynamic>> ubahStatusPengumuman(
    int id,
    String status,
  ) async {
    return ApiService.patch(
      '$_endpointWargaPJ/$id/status',
      body: {'status': status},
      token: await _getToken(),
    );
  }

  // Menghapus/menonaktifkan pengumuman (Warga PJ)
  static Future<Map<String, dynamic>> hapusPengumuman(int id) async {
    return ApiService.delete('$_endpointWargaPJ/$id', token: await _getToken());
  }
}
