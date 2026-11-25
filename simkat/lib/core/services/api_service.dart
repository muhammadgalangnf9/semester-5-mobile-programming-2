import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl = 'http://127.0.0.1:8000/api';

  // Request POST
  static Future<Map<String, dynamic>> post(
    String endpoint, {
    Map<String, dynamic>? body,
    String? token,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse('$baseUrl$endpoint'),
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              if (token != null) 'Authorization': 'Bearer $token',
            },
            body: jsonEncode(body ?? {}),
          )
          .timeout(const Duration(seconds: 15));

      return _handleResponse(response);
    } on ApiException {
      rethrow;
    } catch (e) {
      throw Exception(_pesanError(e));
    }
  }

  // Request GET
  static Future<Map<String, dynamic>> get(
    String endpoint, {
    String? token,
  }) async {
    try {
      final response = await http
          .get(
            Uri.parse('$baseUrl$endpoint'),
            headers: {
              'Accept': 'application/json',
              if (token != null) 'Authorization': 'Bearer $token',
            },
          )
          .timeout(const Duration(seconds: 15));

      return _handleResponse(response);
    } on ApiException {
      rethrow;
    } catch (e) {
      throw Exception(_pesanError(e));
    }
  }

  // Request PUT
  static Future<Map<String, dynamic>> put(
    String endpoint, {
    Map<String, dynamic>? body,
    String? token,
  }) async {
    try {
      final response = await http
          .put(
            Uri.parse('$baseUrl$endpoint'),
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              if (token != null) 'Authorization': 'Bearer $token',
            },
            body: jsonEncode(body ?? {}),
          )
          .timeout(const Duration(seconds: 15));

      return _handleResponse(response);
    } on ApiException {
      rethrow;
    } catch (e) {
      throw Exception(_pesanError(e));
    }
  }

  // Request PATCH
  static Future<Map<String, dynamic>> patch(
    String endpoint, {
    Map<String, dynamic>? body,
    String? token,
  }) async {
    try {
      final response = await http
          .patch(
            Uri.parse('$baseUrl$endpoint'),
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              if (token != null) 'Authorization': 'Bearer $token',
            },
            body: jsonEncode(body ?? {}),
          )
          .timeout(const Duration(seconds: 15));

      return _handleResponse(response);
    } on ApiException {
      rethrow;
    } catch (e) {
      throw Exception(_pesanError(e));
    }
  }

  // Request DELETE
  static Future<Map<String, dynamic>> delete(
    String endpoint, {
    String? token,
  }) async {
    try {
      final response = await http
          .delete(
            Uri.parse('$baseUrl$endpoint'),
            headers: {
              'Accept': 'application/json',
              if (token != null) 'Authorization': 'Bearer $token',
            },
          )
          .timeout(const Duration(seconds: 15));

      return _handleResponse(response);
    } on ApiException {
      rethrow;
    } catch (e) {
      throw Exception(_pesanError(e));
    }
  }

  // Mengolah response Laravel
  static Map<String, dynamic> _handleResponse(http.Response response) {
    final Map<String, dynamic> data;

    try {
      data = jsonDecode(response.body) as Map<String, dynamic>;
    } catch (_) {
      throw Exception(
        'Respons server tidak dapat dibaca. '
        'Status HTTP: ${response.statusCode}',
      );
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return data;
    }

    throw ApiException(
      message:
          data['pesan']?.toString() ??
          data['message']?.toString() ??
          'Terjadi kesalahan pada server.',
      statusCode: response.statusCode,
      data: data,
    );
  }

  // Mengubah error koneksi menjadi pesan
  static String _pesanError(Object error) {
    if (error is http.ClientException) {
      return 'Tidak dapat terhubung ke server.';
    }

    if (error.toString().contains('TimeoutException')) {
      return 'Koneksi ke server terlalu lama.';
    }

    return 'Terjadi kesalahan koneksi.';
  }
}

// Error khusus dari API
class ApiException implements Exception {
  final String message;
  final int statusCode;
  final Map<String, dynamic> data;

  ApiException({
    required this.message,
    required this.statusCode,
    required this.data,
  });

  @override
  String toString() => message;
}
