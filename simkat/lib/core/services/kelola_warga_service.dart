import 'api_service.dart';
import 'auth_service.dart';

class KelolaWargaService {
  static const String _endpoint = '/ketua-rt/warga';

  // Mengambil daftar warga
  static Future<Map<String, dynamic>> getWarga({
    String? search,
    String? gang,
    String? blok,
    String? status,
    String? role,
    int page = 1,
  }) async {
    final token = await AuthService.getToken();

    if (token == null || token.isEmpty) {
      throw Exception('Token autentikasi tidak ditemukan.');
    }

    final query = <String, String>{
      'page': page.toString(),
      if (search != null && search.isNotEmpty) 'search': search,
      if (gang != null && gang.isNotEmpty) 'gang': gang,
      if (blok != null && blok.isNotEmpty) 'blok': blok,
      if (status != null && status.isNotEmpty) 'status': status,
      if (role != null && role.isNotEmpty) 'role': role,
    };

    final uri = Uri(
      path: _endpoint,
      queryParameters: query,
    );

    return ApiService.get(
      uri.toString(),
      token: token,
    );
  }

  // Mengambil detail warga
  static Future<Map<String, dynamic>> getDetailWarga(
    int id,
  ) async {
    final token = await AuthService.getToken();

    if (token == null || token.isEmpty) {
      throw Exception('Token autentikasi tidak ditemukan.');
    }

    return ApiService.get(
      '$_endpoint/$id',
      token: token,
    );
  }

  // Menambahkan warga
  static Future<Map<String, dynamic>> tambahWarga({
    required Map<String, dynamic> data,
  }) async {
    final token = await AuthService.getToken();

    if (token == null || token.isEmpty) {
      throw Exception('Token autentikasi tidak ditemukan.');
    }

    return ApiService.post(
      _endpoint,
      body: data,
      token: token,
    );
  }

  // Memperbarui warga
  static Future<Map<String, dynamic>> updateWarga({
    required int id,
    required Map<String, dynamic> data,
  }) async {
    final token = await AuthService.getToken();

    if (token == null || token.isEmpty) {
      throw Exception('Token autentikasi tidak ditemukan.');
    }

    return ApiService.put(
      '$_endpoint/$id',
      body: data,
      token: token,
    );
  }

  // Mengubah status warga
  static Future<Map<String, dynamic>> ubahStatusWarga(
    int id,
  ) async {
    final token = await AuthService.getToken();

    if (token == null || token.isEmpty) {
      throw Exception('Token autentikasi tidak ditemukan.');
    }

    return ApiService.patch(
      '$_endpoint/$id/status',
      token: token,
    );
  }
}
