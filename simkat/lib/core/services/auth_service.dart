import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import 'api_service.dart';

class AuthService {
  static const String _keyToken = 'auth_token';
  static const String _keyPengguna = 'auth_pengguna';

  // Login pengguna
  static Future<Map<String, dynamic>> login({
    required String username,
    required String password,
  }) async {
    try {
      final response = await ApiService.post(
        '/login',
        body: {
          'username': username,
          'password': password,
        },
      );

      final token = response['token'];

      if (token == null || token.toString().isEmpty) {
        throw Exception('Token autentikasi tidak ditemukan.');
      }

      final data = response['data'];
      if (data is! Map) {
        throw Exception(
          'Data pengguna tidak ditemukan pada respons server.',
        );
      }

      final dataPengguna = Map<String, dynamic>.from(data);

      final prefs = await SharedPreferences.getInstance();

      await prefs.setString(_keyToken, token.toString());

      await prefs.setString(
        _keyPengguna,
        jsonEncode(dataPengguna),
      );

      return dataPengguna;
    } on ApiException {
      rethrow;
    }
  }

  // Mengambil token tersimpan
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_keyToken);
  }

  // Mengambil data pengguna tersimpan
  static Future<Map<String, dynamic>?> getPengguna() async {
    final prefs = await SharedPreferences.getInstance();
    final data = prefs.getString(_keyPengguna);

    if (data == null || data.isEmpty) {
      return null;
    }

    return Map<String, dynamic>.from(
      jsonDecode(data),
    );
  }

  // Mengambil role pengguna
  static Future<String?> getRole() async {
    final pengguna = await getPengguna();
    return pengguna?['role']?.toString();
  }

  // Mengambil hak akses pengguna
  static Future<Map<String, dynamic>> getAkses() async {
    final pengguna = await getPengguna();

    if (pengguna == null || pengguna['akses'] == null) {
      return {};
    }

    return Map<String, dynamic>.from(
      pengguna['akses'],
    );
  }

  // Memeriksa status login lokal
  static Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  // Mengambil profil terbaru dari API
  static Future<Map<String, dynamic>> getProfil() async {
    final token = await getToken();

    if (token == null || token.isEmpty) {
      throw Exception('Token autentikasi tidak ditemukan.');
    }

    final response = await ApiService.get(
      '/profil',
      token: token,
    );

    final dataPengguna =
        Map<String, dynamic>.from(response['data']);

    final prefs = await SharedPreferences.getInstance();

    await prefs.setString(
      _keyPengguna,
      jsonEncode(dataPengguna),
    );

    return dataPengguna;
  }

  // Logout pengguna
  static Future<void> logout() async {
    final token = await getToken();

    try {
      if (token != null && token.isNotEmpty) {
        await ApiService.post(
          '/logout',
          token: token,
        );
      }
    } finally {
      await hapusToken();
    }
  }

  // Menghapus token dan data pengguna lokal
  static Future<void> hapusToken() async {
    final prefs = await SharedPreferences.getInstance();

    await prefs.remove(_keyToken);
    await prefs.remove(_keyPengguna);
  }
}
