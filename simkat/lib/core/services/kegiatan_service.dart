import 'api_service.dart';
import 'auth_service.dart';

enum KomponenKegiatan { panitia, rab, transaksi, peserta, dokumentasi }

class KegiatanService {
  // Endpoint berdasarkan role
  static const String _endpointKetuaRT = '/ketua-rt/kegiatan';
  static const String _endpointWargaPJ = '/warga-pj/kegiatan';
  static const String _endpointWargaBiasa = '/warga-biasa/kegiatan';

  // Mengambil token autentikasi
  static Future<String> _getToken() async {
    final token = await AuthService.getToken();

    if (token == null || token.isEmpty) {
      throw Exception('Token autentikasi tidak ditemukan.');
    }

    return token;
  }

  static String _queryDaftarKegiatan({
    required int page,
    String? namaKegiatan,
    String? tanggalDibuat,
    String? status,
  }) {
    final parameters = <String, String>{'page': page.toString()};
    if (namaKegiatan != null && namaKegiatan.trim().isNotEmpty) {
      parameters['nama_kegiatan'] = namaKegiatan.trim();
    }
    if (tanggalDibuat != null && tanggalDibuat.isNotEmpty) {
      parameters['tanggal_dibuat'] = tanggalDibuat;
    }
    if (status != null && status.isNotEmpty) {
      parameters['status'] = status;
    }
    return Uri(queryParameters: parameters).query;
  }

  // =====================================================
  // KETUA RT
  // =====================================================

  // Daftar kegiatan untuk Ketua RT
  static Future<Map<String, dynamic>> getKegiatanKetuaRT({
    int page = 1,
    String? namaKegiatan,
    String? tanggalDibuat,
  }) async {
    final token = await _getToken();

    final query = _queryDaftarKegiatan(
      page: page,
      namaKegiatan: namaKegiatan,
      tanggalDibuat: tanggalDibuat,
    );
    return await ApiService.get('$_endpointKetuaRT?$query', token: token);
  }

  // Detail kegiatan untuk Ketua RT
  static Future<Map<String, dynamic>> getDetailKegiatanKetuaRT(int id) async {
    final token = await _getToken();

    return await ApiService.get('$_endpointKetuaRT/$id', token: token);
  }

  // Menyetujui kegiatan
  static Future<Map<String, dynamic>> setujuiKegiatan(
    int id, {
    String? catatan,
  }) async {
    final token = await _getToken();

    final Map<String, dynamic> body = {};

    if (catatan != null) {
      body['catatan_persetujuan'] = catatan;
    }

    return await ApiService.put(
      '$_endpointKetuaRT/$id/setujui',
      body: body,
      token: token,
    );
  }

  // Menolak kegiatan
  static Future<Map<String, dynamic>> tolakKegiatan(
    int id, {
    required String catatan,
  }) async {
    final token = await _getToken();

    return await ApiService.put(
      '$_endpointKetuaRT/$id/tolak',
      body: {'catatan_persetujuan': catatan},
      token: token,
    );
  }

  // Membatalkan persetujuan kegiatan
  static Future<Map<String, dynamic>> batalkanPersetujuanKegiatan(
    int id,
  ) async {
    final token = await _getToken();

    return await ApiService.put(
      '$_endpointKetuaRT/$id/batalkan-persetujuan',
      token: token,
    );
  }

  // =====================================================
  // WARGA PJ KEGIATAN DAN JADWAL RONDA
  // =====================================================

  // Daftar kegiatan milik Warga PJ
  static Future<Map<String, dynamic>> getKegiatanWargaPJ({
    int page = 1,
    String? namaKegiatan,
    String? tanggalDibuat,
    String? status,
  }) async {
    final token = await _getToken();

    final query = _queryDaftarKegiatan(
      page: page,
      namaKegiatan: namaKegiatan,
      tanggalDibuat: tanggalDibuat,
      status: status,
    );
    return await ApiService.get('$_endpointWargaPJ?$query', token: token);
  }

  // Detail kegiatan milik Warga PJ
  static Future<Map<String, dynamic>> getDetailKegiatanWargaPJ(int id) async {
    final token = await _getToken();

    return await ApiService.get('$_endpointWargaPJ/$id', token: token);
  }

  // Menambahkan kegiatan
  static Future<Map<String, dynamic>> tambahKegiatan(
    Map<String, dynamic> body,
  ) async {
    final token = await _getToken();

    return await ApiService.post(_endpointWargaPJ, body: body, token: token);
  }

  // Mengubah kegiatan
  static Future<Map<String, dynamic>> ubahKegiatan(
    int id,
    Map<String, dynamic> body,
  ) async {
    final token = await _getToken();

    return await ApiService.put(
      '$_endpointWargaPJ/$id',
      body: body,
      token: token,
    );
  }

  // Mengajukan kegiatan untuk persetujuan
  static Future<Map<String, dynamic>> ajukanKegiatan(int id) async {
    final token = await _getToken();

    return await ApiService.put('$_endpointWargaPJ/$id/ajukan', token: token);
  }

  static Future<Map<String, dynamic>> nonaktifkanKegiatan(int id) async {
    final token = await _getToken();

    return ApiService.delete('$_endpointWargaPJ/$id', token: token);
  }

  static Future<Map<String, dynamic>> aktifkanKegiatan(int id) async {
    final token = await _getToken();

    return ApiService.put('$_endpointWargaPJ/$id/aktifkan', token: token);
  }

  static Future<Map<String, dynamic>> tambahKomponenKegiatan(
    int kegiatanId,
    KomponenKegiatan komponen,
    Map<String, dynamic> body,
  ) async {
    return ApiService.post(
      '$_endpointWargaPJ/$kegiatanId/${komponen.name}',
      body: body,
      token: await _getToken(),
    );
  }

  static Future<Map<String, dynamic>> ubahKomponenKegiatan(
    int kegiatanId,
    KomponenKegiatan komponen,
    int komponenId,
    Map<String, dynamic> body,
  ) async {
    return ApiService.put(
      '$_endpointWargaPJ/$kegiatanId/${komponen.name}/$komponenId',
      body: body,
      token: await _getToken(),
    );
  }

  static Future<Map<String, dynamic>> hapusKomponenKegiatan(
    int kegiatanId,
    KomponenKegiatan komponen,
    int komponenId,
  ) async {
    return ApiService.delete(
      '$_endpointWargaPJ/$kegiatanId/${komponen.name}/$komponenId',
      token: await _getToken(),
    );
  }

  // =====================================================
  // WARGA BIASA
  // =====================================================

  // Daftar kegiatan yang disetujui
  static Future<Map<String, dynamic>> getKegiatanWarga({
    int page = 1,
    String? namaKegiatan,
    String? tanggalDibuat,
  }) async {
    final token = await _getToken();

    final query = _queryDaftarKegiatan(
      page: page,
      namaKegiatan: namaKegiatan,
      tanggalDibuat: tanggalDibuat,
    );
    return await ApiService.get('$_endpointWargaBiasa?$query', token: token);
  }

  // Detail kegiatan untuk Warga Biasa
  static Future<Map<String, dynamic>> getDetailKegiatanWarga(int id) async {
    final token = await _getToken();

    return await ApiService.get('$_endpointWargaBiasa/$id', token: token);
  }
}
