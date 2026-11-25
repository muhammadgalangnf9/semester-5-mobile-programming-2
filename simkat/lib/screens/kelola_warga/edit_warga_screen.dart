import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../core/services/kelola_warga_service.dart';

class EditWargaScreen extends StatefulWidget {
  final int wargaId;

  const EditWargaScreen({super.key, required this.wargaId});

  @override
  State<EditWargaScreen> createState() => _EditWargaScreenState();
}

class _EditWargaScreenState extends State<EditWargaScreen> {
  final _formKey = GlobalKey<FormState>();

  final _namaController = TextEditingController();
  final _usernameController = TextEditingController();
  final _noHpController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _nomorBlokController = TextEditingController();

  String? _gang;
  String? _blokAwalan;
  String? _jenisKelamin;
  String? _roleId;

  bool _isLoading = true;
  bool _isSaving = false;
  bool _sembunyikanPassword = true;

  static const List<String> _daftarGang = [
    'Gang E6',
    'Gang E4/E5',
    'Gang E2/E3',
    'Gang C16/E1',
  ];

  static const Map<String, List<String>> _daftarAwalanBlok = {
    'Gang C16/E1': ['C16', 'E1'],
    'Gang E2/E3': ['E2', 'E3'],
    'Gang E4/E5': ['E4', 'E5'],
    'Gang E6': ['E6'],
  };

  @override
  void initState() {
    super.initState();
    _ambilDetailWarga();
  }

  @override
  void dispose() {
    _namaController.dispose();
    _usernameController.dispose();
    _noHpController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _nomorBlokController.dispose();
    super.dispose();
  }

  Future<void> _ambilDetailWarga() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final response = await KelolaWargaService.getDetailWarga(widget.wargaId);

      if (!mounted) return;

      final data = Map<String, dynamic>.from(response['data'] as Map);

      final role = data['role'];

      String? roleId = data['role_id']?.toString();

      if (roleId == null && role is Map) {
        roleId = role['id']?.toString();
      }

      final blok = data['blok']?.toString().split('/');
      final awalanBlok = blok != null && blok.length == 2 ? blok[0] : null;
      final nomorBlok = blok != null && blok.length == 2 ? blok[1] : '';

      setState(() {
        _namaController.text = data['nama']?.toString() ?? '';
        _usernameController.text = data['username']?.toString() ?? '';
        _noHpController.text = data['no_hp']?.toString() ?? '';
        _emailController.text = data['email']?.toString() ?? '';

        _gang = data['gang']?.toString();
        _blokAwalan = _daftarAwalanBlok[_gang]?.contains(awalanBlok) == true
            ? awalanBlok
            : null;
        _nomorBlokController.text = _blokAwalan == null ? '' : nomorBlok;
        _jenisKelamin = data['jenis_kelamin']?.toString();
        _roleId = roleId;

        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _isLoading = false;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _simpanPerubahan() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isSaving = true;
    });

    final data = <String, dynamic>{
      'nama': _namaController.text.trim(),
      'username': _usernameController.text.trim(),
      'gang': _gang,
      'blok': '$_blokAwalan/${_nomorBlokController.text.trim()}',
      'jenis_kelamin': _jenisKelamin,
      'no_hp': _noHpController.text.trim().isEmpty
          ? null
          : _noHpController.text.trim(),
      'email': _emailController.text.trim().isEmpty
          ? null
          : _emailController.text.trim(),
      'role_id': int.parse(_roleId!),
    };

    // Password hanya dikirim jika ingin diganti.
    if (_passwordController.text.isNotEmpty) {
      data['password'] = _passwordController.text;
    }

    try {
      final response = await KelolaWargaService.updateWarga(
        id: widget.wargaId,
        data: data,
      );

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            response['pesan']?.toString() ?? 'Data warga berhasil diperbarui.',
          ),
          backgroundColor: Colors.green,
        ),
      );

      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isSaving = false;
        });
      }
    }
  }

  Widget _buildTextField({
    required String label,
    required TextEditingController controller,
    required IconData icon,
    TextInputType keyboardType = TextInputType.text,
    bool wajib = true,
    bool password = false,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      obscureText: password && _sembunyikanPassword,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 14,
          vertical: 14,
        ),
        suffixIcon: password
            ? IconButton(
                onPressed: () {
                  setState(() {
                    _sembunyikanPassword = !_sembunyikanPassword;
                  });
                },
                icon: Icon(
                  _sembunyikanPassword
                      ? Icons.visibility_off
                      : Icons.visibility,
                ),
              )
            : null,
      ),
      validator: (value) {
        if (wajib && (value == null || value.trim().isEmpty)) {
          return '$label wajib diisi.';
        }

        if (password && value != null && value.isNotEmpty && value.length < 8) {
          return 'Password minimal 8 karakter.';
        }

        if (label == 'Email' &&
            value != null &&
            value.trim().isNotEmpty &&
            !RegExp(r'^[^@]+@[^@]+\.[^@]+$').hasMatch(value.trim())) {
          return 'Format email tidak valid.';
        }

        return null;
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Edit Warga'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
      ),
      backgroundColor: const Color(0xFFF8FAFC),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Form(
              key: _formKey,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  const Text(
                    'Informasi Akun Warga',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Ubah informasi akun warga sesuai data terbaru.',
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                  ),
                  const SizedBox(height: 20),

                  _buildTextField(
                    label: 'Nama Lengkap',
                    controller: _namaController,
                    icon: Icons.person_outline,
                  ),
                  const SizedBox(height: 14),

                  _buildTextField(
                    label: 'Username',
                    controller: _usernameController,
                    icon: Icons.account_circle_outlined,
                  ),
                  const SizedBox(height: 14),

                  DropdownButtonFormField<String>(
                    initialValue: _gang,
                    isExpanded: true,
                    decoration: InputDecoration(
                      labelText: 'Gang',
                      prefixIcon: const Icon(Icons.location_on_outlined),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    items: _daftarGang.map((gang) {
                      return DropdownMenuItem(value: gang, child: Text(gang));
                    }).toList(),
                    onChanged: (value) {
                      setState(() {
                        _gang = value;
                        _blokAwalan = null;
                        _nomorBlokController.clear();
                      });
                    },
                    validator: (value) {
                      if (value == null) {
                        return 'Gang wajib dipilih.';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 14),

                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        flex: 3,
                        child: DropdownButtonFormField<String>(
                          initialValue: _blokAwalan,
                          isExpanded: true,
                          decoration: InputDecoration(
                            labelText: 'Awalan Blok',
                            prefixIcon: const Icon(Icons.home_work_outlined),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                          ),
                          items: [
                            const DropdownMenuItem<String>(
                              child: Text('Pilih'),
                            ),
                            ...(_daftarAwalanBlok[_gang] ?? []).map(
                              (awalan) => DropdownMenuItem(
                                value: awalan,
                                child: Text(awalan),
                              ),
                            ),
                          ],
                          onChanged: _gang == null
                              ? null
                              : (value) {
                                  setState(() {
                                    _blokAwalan = value;
                                  });
                                },
                          validator: (value) {
                            if (value == null) {
                              return 'Awalan blok wajib dipilih.';
                            }
                            return null;
                          },
                        ),
                      ),
                      const Padding(
                        padding: EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 18,
                        ),
                        child: Text('/', style: TextStyle(fontSize: 18)),
                      ),
                      Expanded(
                        flex: 2,
                        child: TextFormField(
                          controller: _nomorBlokController,
                          enabled: _blokAwalan != null,
                          keyboardType: TextInputType.number,
                          inputFormatters: [
                            FilteringTextInputFormatter.digitsOnly,
                          ],
                          decoration: InputDecoration(
                            labelText: 'Nomor',
                            hintText: '04',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Nomor blok wajib diisi.';
                            }
                            return null;
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Pilih awalan sesuai gang, lalu ketik nomor blok.',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                  ),
                  const SizedBox(height: 14),

                  DropdownButtonFormField<String>(
                    initialValue: _jenisKelamin,
                    isExpanded: true,
                    decoration: InputDecoration(
                      labelText: 'Jenis Kelamin',
                      prefixIcon: const Icon(Icons.people_outline),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    items: const [
                      DropdownMenuItem(
                        value: 'laki_laki',
                        child: Text('Laki-laki'),
                      ),
                      DropdownMenuItem(
                        value: 'perempuan',
                        child: Text('Perempuan'),
                      ),
                    ],
                    onChanged: (value) {
                      setState(() {
                        _jenisKelamin = value;
                      });
                    },
                    validator: (value) {
                      if (value == null) {
                        return 'Jenis kelamin wajib dipilih.';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 14),

                  _buildTextField(
                    label: 'Nomor HP',
                    controller: _noHpController,
                    icon: Icons.phone_outlined,
                    keyboardType: TextInputType.phone,
                    wajib: false,
                  ),
                  const SizedBox(height: 14),

                  _buildTextField(
                    label: 'Email',
                    controller: _emailController,
                    icon: Icons.email_outlined,
                    keyboardType: TextInputType.emailAddress,
                    wajib: false,
                  ),
                  const SizedBox(height: 14),

                  _buildTextField(
                    label: 'Password Baru',
                    controller: _passwordController,
                    icon: Icons.lock_outline,
                    password: true,
                    wajib: false,
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Kosongkan jika tidak ingin mengubah password.',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                  ),
                  const SizedBox(height: 14),

                  DropdownButtonFormField<String>(
                    initialValue: _roleId,
                    isExpanded: true,
                    decoration: InputDecoration(
                      labelText: 'Role',
                      prefixIcon: const Icon(
                        Icons.admin_panel_settings_outlined,
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    items: _roleId == '1'
                        ? const [
                            DropdownMenuItem(
                              value: '1',
                              child: Text('Ketua RT'),
                            ),
                          ]
                        : const [
                            DropdownMenuItem(
                              value: '2',
                              child: Text('Warga PJ Kegiatan dan Jadwal Ronda'),
                            ),
                            DropdownMenuItem(
                              value: '3',
                              child: Text('Warga Biasa'),
                            ),
                          ],
                    onChanged: (value) {
                      setState(() {
                        _roleId = value;
                      });
                    },
                    validator: (value) {
                      if (value == null) {
                        return 'Role wajib dipilih.';
                      }
                      return null;
                    },
                  ),

                  const SizedBox(height: 24),

                  SizedBox(
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: _isSaving ? null : _simpanPerubahan,
                      icon: _isSaving
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Icon(Icons.save_outlined),
                      label: Text(
                        _isSaving ? 'Menyimpan...' : 'Simpan Perubahan',
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF2563EB),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
