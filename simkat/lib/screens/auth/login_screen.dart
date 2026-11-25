import 'dart:async';

import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../core/services/api_service.dart';
import '../../core/services/auth_service.dart';
import '../../layouts/auth_layout.dart';
import '../dashboard/dashboard_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final usernameController = TextEditingController();
  final passwordController = TextEditingController();

  static const _keyUsername = 'login_last_username';
  static const _keyLockUntil = 'login_lock_until';
  static const _keyLockUsername = 'login_lock_username';
  static const _keyAttemptUsername = 'login_attempt_username';
  static const _keyRemainingAttempts = 'login_remaining_attempts';

  bool obscurePassword = true;
  bool isLoading = false;
  bool sedangMemuatStatus = true;

  int sisaPercobaan = 5;
  int sisaDetik = 0;

  String pesanError = '';
  String pesanInformasi = '';

  Timer? countdownTimer;

  bool get sedangDikunci => sisaDetik > 0;

  bool get inputAktif =>
      !isLoading && !sedangMemuatStatus && !sedangDikunci;

  @override
  void initState() {
    super.initState();
    _pulihkanStatusLogin();
  }

  @override
  void dispose() {
    countdownTimer?.cancel();
    usernameController.dispose();
    passwordController.dispose();
    super.dispose();
  }

  // Memulihkan username, jumlah percobaan, dan waktu penguncian.
  Future<void> _pulihkanStatusLogin() async {
    final prefs = await SharedPreferences.getInstance();

    final username = prefs.getString(_keyUsername) ?? '';
    final usernameKunci = prefs.getString(_keyLockUsername);
    final waktuBerakhir = prefs.getInt(_keyLockUntil);

    int percobaan = 5;
    int waktuTersisa = 0;

    if (username.isNotEmpty) {
      final usernamePercobaan =
          prefs.getString(_keyAttemptUsername);

      if (usernamePercobaan == username) {
        percobaan =
            prefs.getInt(_keyRemainingAttempts) ?? 5;
      }
    }

    if (usernameKunci == username && waktuBerakhir != null) {
      waktuTersisa = ((waktuBerakhir -
                  DateTime.now().millisecondsSinceEpoch) /
              1000)
          .ceil();

      if (waktuTersisa <= 0) {
        await _hapusDataKunci(prefs);
        waktuTersisa = 0;
        percobaan = 5;
      }
    }

    if (!mounted) return;

    usernameController.text = username;

    setState(() {
      sisaPercobaan = percobaan;
      sedangMemuatStatus = false;
    });

    if (waktuTersisa > 0) {
      _mulaiHitungMundur(waktuTersisa);
    }
  }

  // Memeriksa data percobaan saat username diganti.
  Future<void> _ubahUsername(String username) async {
    final prefs = await SharedPreferences.getInstance();

    if (!mounted) return;

    setState(() {
      pesanError = '';
      pesanInformasi = '';
      sisaPercobaan = 5;
    });

    final usernameTersimpan =
        prefs.getString(_keyAttemptUsername);

    if (usernameTersimpan == username.trim()) {
      final sisa = prefs.getInt(_keyRemainingAttempts) ?? 5;

      if (!mounted) return;

      setState(() {
        sisaPercobaan = sisa;
      });
    }

    final usernameKunci =
        prefs.getString(_keyLockUsername);

    final waktuBerakhir = prefs.getInt(_keyLockUntil);

    if (usernameKunci == username.trim() &&
        waktuBerakhir != null) {
      final sisa = ((waktuBerakhir -
                  DateTime.now().millisecondsSinceEpoch) /
              1000)
          .ceil();

      if (sisa > 0) {
        _mulaiHitungMundur(sisa);
      }
    }
  }

  Future<void> _simpanPercobaan(
    String username,
    int sisa,
  ) async {
    final prefs = await SharedPreferences.getInstance();

    await prefs.setString(_keyUsername, username);
    await prefs.setString(_keyAttemptUsername, username);
    await prefs.setInt(_keyRemainingAttempts, sisa);

    if (!mounted) return;

    setState(() {
      sisaPercobaan = sisa;
    });
  }

  Future<void> _simpanPenguncian(
    String username,
    int detik,
  ) async {
    final prefs = await SharedPreferences.getInstance();

    final waktuBerakhir = DateTime.now()
        .add(Duration(seconds: detik))
        .millisecondsSinceEpoch;

    await prefs.setString(_keyUsername, username);
    await prefs.setString(_keyLockUsername, username);
    await prefs.setInt(_keyLockUntil, waktuBerakhir);

    await prefs.remove(_keyAttemptUsername);
    await prefs.remove(_keyRemainingAttempts);

    if (!mounted) return;

    setState(() {
      sisaPercobaan = 0;
      pesanError = '';
      pesanInformasi = '';
    });

    _mulaiHitungMundur(detik);
  }

  Future<void> _hapusDataKunci(
    SharedPreferences prefs,
  ) async {
    await prefs.remove(_keyLockUsername);
    await prefs.remove(_keyLockUntil);
  }

  void _mulaiHitungMundur(int detik) {
    countdownTimer?.cancel();

    final waktuBerakhir = DateTime.now()
        .add(Duration(seconds: detik));

    void perbaruiWaktu() {
      if (!mounted) return;

      final sisa = (waktuBerakhir
                  .difference(DateTime.now())
                  .inMilliseconds /
              1000)
          .ceil();

      setState(() {
        sisaDetik = sisa > 0 ? sisa : 0;
      });

      if (sisa <= 0) {
        countdownTimer?.cancel();
        _selesaiPenguncian();
      }
    }

    perbaruiWaktu();

    countdownTimer = Timer.periodic(
      const Duration(seconds: 1),
      (_) => perbaruiWaktu(),
    );
  }

  Future<void> _selesaiPenguncian() async {
    final prefs = await SharedPreferences.getInstance();
    await _hapusDataKunci(prefs);

    if (!mounted) return;

    setState(() {
      sisaDetik = 0;
      sisaPercobaan = 5;
      pesanInformasi =
          'Waktu penguncian selesai. Kamu dapat mencoba login kembali.';
    });
  }

  String _formatWaktu(int detik) {
    final menit = detik ~/ 60;
    final sisa = detik % 60;

    return '${menit.toString().padLeft(2, '0')}:'
        '${sisa.toString().padLeft(2, '0')}';
  }

  // Proses login pengguna.
  Future<void> _login() async {
    final username = usernameController.text.trim();
    final password = passwordController.text;

    if (sedangDikunci || isLoading) return;

    if (username.isEmpty || password.isEmpty) {
      setState(() {
        pesanError = 'Username dan password wajib diisi.';
        pesanInformasi = '';
      });
      return;
    }

    setState(() {
      isLoading = true;
      pesanError = '';
      pesanInformasi = '';
    });

    try {
      // Mengambil data pengguna dari API.
      final dataPengguna = await AuthService.login(
        username: username,
        password: password,
      );

      // Reset status percobaan login.
      final prefs = await SharedPreferences.getInstance();

      await prefs.setString(_keyUsername, username);
      await prefs.remove(_keyAttemptUsername);
      await prefs.remove(_keyRemainingAttempts);
      await _hapusDataKunci(prefs);

      if (!mounted) return;

      // Meneruskan data pengguna ke Dashboard.
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (_) => DashboardScreen(
            pengguna: dataPengguna,
          ),
        ),
      );
    } on ApiException catch (e) {
      if (e.statusCode == 401) {
        final sisa = int.tryParse(
              e.data['sisa_percobaan']?.toString() ?? '',
            ) ??
            (sisaPercobaan - 1).clamp(0, 5);

        await _simpanPercobaan(username, sisa);

        if (!mounted) return;

        setState(() {
          pesanError = e.message;
        });
      } else if (e.statusCode == 429) {
        final detik = int.tryParse(
              e.data['sisa_detik']?.toString() ?? '',
            ) ??
            60;

        await _simpanPenguncian(username, detik);
      } else {
        if (!mounted) return;

        setState(() {
          pesanError = e.message;
        });
      }
    } catch (e) {
      if (!mounted) return;

      setState(() {
        pesanError =
            e.toString().replaceFirst('Exception: ', '');
      });
    } finally {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
      }
    }
  }

  Widget _buildPesanError() {
    if (pesanError.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFFEF2F2),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: const Color(0xFFFECACA),
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(
            Icons.error_outline,
            color: Color(0xFFDC2626),
            size: 20,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              pesanError,
              style: const TextStyle(
                color: Color(0xFFB91C1C),
                fontSize: 13,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusPercobaan() {
    final warna = sisaPercobaan <= 2
        ? const Color(0xFFDC2626)
        : const Color(0xFF2563EB);

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Kesempatan login',
                style: TextStyle(
                  color: Color(0xFF475569),
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                ),
              ),
              Text(
                '$sisaPercobaan dari 5',
                style: TextStyle(
                  color: warna,
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: LinearProgressIndicator(
              value: sisaPercobaan / 5,
              minHeight: 8,
              backgroundColor: const Color(0xFFE2E8F0),
              valueColor: AlwaysStoppedAnimation<Color>(warna),
            ),
          ),
          if (sisaPercobaan <= 2) ...[
            const SizedBox(height: 8),
            const Text(
              'Perhatian: kesempatan login hampir habis.',
              style: TextStyle(
                color: Color(0xFFDC2626),
                fontSize: 12,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildPanelPenguncian() {
    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: 12,
        vertical: 10,
      ),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF1F2),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: const Color(0xFFFECACA),
        ),
      ),
      child: Row(
        children: [
          const Icon(
            Icons.lock_clock_rounded,
            size: 28,
            color: Color(0xFFDC2626),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Login Dikunci Sementara',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFFB91C1C),
                  ),
                ),
                const SizedBox(height: 3),
                const Text(
                  'Tunggu hingga waktu penguncian selesai.',
                  style: TextStyle(
                    fontSize: 11,
                    color: Color(0xFF7F1D1D),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            _formatWaktu(sisaDetik),
            style: const TextStyle(
              fontSize: 21,
              fontWeight: FontWeight.bold,
              color: Color(0xFFB91C1C),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPesanInformasi() {
    if (pesanInformasi.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFF0FDF4),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: const Color(0xFFBBF7D0),
        ),
      ),
      child: Row(
        children: [
          const Icon(
            Icons.check_circle_outline,
            color: Color(0xFF15803D),
            size: 20,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              pesanInformasi,
              style: const TextStyle(
                color: Color(0xFF166534),
                fontSize: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return AuthLayout(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Icon(
            Icons.groups_rounded,
            size: 58,
            color: Color(0xFF2563EB),
          ),
          const SizedBox(height: 12),
          const Text(
            'SIMKAT',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 30,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E293B),
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Sistem Informasi Manajemen Kegiatan RT',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 13,
              color: Color(0xFF64748B),
            ),
          ),
          const SizedBox(height: 32),

          const Text(
            'Selamat Datang',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E293B),
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Silakan masuk menggunakan akun Anda.',
            style: TextStyle(
              fontSize: 13,
              color: Color(0xFF64748B),
            ),
          ),
          const SizedBox(height: 24),

          const Text(
            'Username',
            style: TextStyle(fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: usernameController,
            enabled: inputAktif,
            onChanged: _ubahUsername,
            textInputAction: TextInputAction.next,
            decoration: const InputDecoration(
              hintText: 'Masukkan username',
              prefixIcon: Icon(Icons.person_outline),
            ),
          ),
          const SizedBox(height: 18),

          const Text(
            'Password',
            style: TextStyle(fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: passwordController,
            enabled: inputAktif,
            obscureText: obscurePassword,
            onSubmitted: (_) => _login(),
            decoration: InputDecoration(
              hintText: 'Masukkan password',
              prefixIcon: const Icon(Icons.lock_outline),
              suffixIcon: IconButton(
                icon: Icon(
                  obscurePassword
                      ? Icons.visibility_off_outlined
                      : Icons.visibility_outlined,
                ),
                onPressed: inputAktif
                    ? () {
                        setState(() {
                          obscurePassword = !obscurePassword;
                        });
                      }
                    : null,
              ),
            ),
          ),

          const SizedBox(height: 16),

          if (sedangMemuatStatus)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(12),
                child: CircularProgressIndicator(),
              ),
            )
          else if (sedangDikunci)
            _buildPanelPenguncian()
          else
            _buildStatusPercobaan(),

          const SizedBox(height: 12),
          _buildPesanError(),
          _buildPesanInformasi(),

          const SizedBox(height: 22),

          SizedBox(
            height: 48,
            child: ElevatedButton(
              onPressed: inputAktif ? _login : null,
              child: isLoading
                  ? const SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : Text(
                      sedangDikunci
                          ? 'Tunggu ${_formatWaktu(sisaDetik)}'
                          : 'Masuk',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
            ),
          ),

          const SizedBox(height: 24),
          const Text(
            '© 2026 SIMKAT',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 12,
              color: Color(0xFF94A3B8),
            ),
          ),
        ],
      ),
    );
  }
}
