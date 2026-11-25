import 'package:flutter/material.dart';

import '../../core/services/jadwal_ronda_service.dart';

class FormJadwalRondaScreen extends StatefulWidget {
  final Map<String, dynamic>? jadwal;

  const FormJadwalRondaScreen({super.key, this.jadwal});

  @override
  State<FormJadwalRondaScreen> createState() => _FormJadwalRondaScreenState();
}

class _FormJadwalRondaScreenState extends State<FormJadwalRondaScreen> {
  static const String _semuaGang = 'Semua Gang';
  static const _daftarHari = [
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
    'Minggu',
  ];

  final _formKey = GlobalKey<FormState>();
  final _keteranganController = TextEditingController();
  final Set<String> _hariTerpilih = {};
  final Set<int> _pesertaTerpilih = {};

  List<Map<String, dynamic>> _daftarPeserta = [];
  String? _gang;
  TimeOfDay? _waktuMulai;
  TimeOfDay? _waktuSelesai;
  String? _pesanError;
  bool _isLoadingPeserta = true;
  bool _isSaving = false;

  bool get _isEditing => widget.jadwal != null;

  @override
  void initState() {
    super.initState();
    final jadwal = widget.jadwal;
    _gang = _semuaGang;
    if (jadwal != null) {
      final hari = jadwal['hari'];
      if (hari is List) {
        _hariTerpilih.addAll(hari.map((item) => item.toString()));
      }
      _gang = jadwal['gang']?.toString();
      _keteranganController.text = jadwal['keterangan']?.toString() ?? '';
      _waktuMulai = _parseWaktu(jadwal['waktu_mulai']);
      _waktuSelesai = _parseWaktu(jadwal['waktu_selesai']);

      final peserta = jadwal['peserta'];
      if (peserta is List) {
        for (final item in peserta) {
          final id = item is Map
              ? int.tryParse(item['id']?.toString() ?? '')
              : int.tryParse(item.toString());
          if (id != null) _pesertaTerpilih.add(id);
        }
      }
    }
    _ambilPeserta();
  }

  @override
  void dispose() {
    _keteranganController.dispose();
    super.dispose();
  }

  TimeOfDay? _parseWaktu(dynamic value) {
    if (value == null) return null;
    final parts = value.toString().split(':');
    if (parts.length < 2) return null;
    final hour = int.tryParse(parts[0]);
    final minute = int.tryParse(parts[1]);
    if (hour == null || minute == null) return null;
    return TimeOfDay(hour: hour, minute: minute);
  }

  Future<void> _ambilPeserta() async {
    try {
      final peserta = await JadwalRondaService.getDaftarPeserta();
      if (!mounted) return;

      setState(() {
        _daftarPeserta = peserta;
        if (_gang != null && _gang != _semuaGang) {
          final pesertaGang = _daftarPeserta
              .where((item) => item['gang']?.toString() == _gang)
              .map((item) => int.tryParse(item['id']?.toString() ?? ''))
              .whereType<int>()
              .toSet();
          _pesertaTerpilih.removeWhere((id) => !pesertaGang.contains(id));
        }
        _isLoadingPeserta = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _pesanError = e.toString().replaceFirst('Exception: ', '');
        _isLoadingPeserta = false;
      });
    }
  }

  List<String> get _daftarGang {
    final gang = _daftarPeserta
        .map((peserta) => peserta['gang']?.toString() ?? '')
        .where((nama) => nama.isNotEmpty && nama != _semuaGang)
        .toSet()
        .toList();
    if (_gang != null &&
        _gang!.isNotEmpty &&
        _gang != _semuaGang &&
        !gang.contains(_gang)) {
      gang.add(_gang!);
    }
    gang.sort();
    return [_semuaGang, ...gang.toSet()];
  }

  List<Map<String, dynamic>> get _pesertaTampil {
    if (_gang == null || _gang == _semuaGang) return _daftarPeserta;
    return _daftarPeserta
        .where((peserta) => peserta['gang']?.toString() == _gang)
        .toList();
  }

  void _ubahGang(String? gangBaru) {
    setState(() {
      _gang = gangBaru;
      if (gangBaru == null || gangBaru == _semuaGang) return;

      final pesertaGang = _daftarPeserta
          .where((peserta) => peserta['gang']?.toString() == gangBaru)
          .map((peserta) => int.tryParse(peserta['id']?.toString() ?? ''))
          .whereType<int>()
          .toSet();
      _pesertaTerpilih.removeWhere((id) => !pesertaGang.contains(id));
    });
  }

  Future<void> _pilihWaktu({required bool mulai}) async {
    final waktu = await showTimePicker(
      context: context,
      initialTime: mulai
          ? (_waktuMulai ?? const TimeOfDay(hour: 20, minute: 0))
          : (_waktuSelesai ?? const TimeOfDay(hour: 22, minute: 0)),
    );
    if (waktu == null || !mounted) return;
    setState(() {
      if (mulai) {
        _waktuMulai = waktu;
      } else {
        _waktuSelesai = waktu;
      }
    });
  }

  String _formatWaktu(TimeOfDay? waktu) {
    if (waktu == null) return 'Pilih waktu';
    return '${waktu.hour.toString().padLeft(2, '0')}:'
        '${waktu.minute.toString().padLeft(2, '0')}';
  }

  Future<void> _simpan() async {
    if (!_formKey.currentState!.validate()) return;
    if (_hariTerpilih.isEmpty) {
      _tampilkanPesan('Pilih minimal satu hari ronda.');
      return;
    }
    if (_pesertaTerpilih.isEmpty) {
      _tampilkanPesan('Pilih minimal satu peserta ronda.');
      return;
    }
    if (_waktuMulai == null || _waktuSelesai == null) {
      _tampilkanPesan('Waktu mulai dan selesai wajib dipilih.');
      return;
    }
    if (_waktuMulai == _waktuSelesai) {
      _tampilkanPesan('Waktu mulai dan selesai tidak boleh sama.');
      return;
    }

    setState(() => _isSaving = true);
    try {
      final data = {
        'hari': _daftarHari.where(_hariTerpilih.contains).toList(),
        'peserta': _pesertaTerpilih.toList(),
        'waktu_mulai': _formatWaktu(_waktuMulai),
        'waktu_selesai': _formatWaktu(_waktuSelesai),
        'gang': _gang,
        'keterangan': _keteranganController.text.trim(),
      };
      final id = int.tryParse(widget.jadwal?['id']?.toString() ?? '');

      if (_isEditing && id != null) {
        await JadwalRondaService.ubahJadwalRonda(id, data);
      } else {
        await JadwalRondaService.tambahJadwalRonda(data);
      }

      if (!mounted) return;
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      _tampilkanPesan(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  void _tampilkanPesan(String pesan) {
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(pesan), backgroundColor: Colors.red));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(_isEditing ? 'Edit Jadwal Ronda' : 'Tambah Jadwal Ronda'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
      ),
      body: _isLoadingPeserta
          ? const Center(child: CircularProgressIndicator())
          : _pesanError != null
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Text(_pesanError!, textAlign: TextAlign.center),
              ),
            )
          : Form(
              key: _formKey,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  const Text(
                    'Hari Ronda',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 4,
                    children: _daftarHari.map((hari) {
                      return FilterChip(
                        label: Text(hari),
                        selected: _hariTerpilih.contains(hari),
                        onSelected: _isSaving
                            ? null
                            : (selected) {
                                setState(() {
                                  if (selected) {
                                    _hariTerpilih.add(hari);
                                  } else {
                                    _hariTerpilih.remove(hari);
                                  }
                                });
                              },
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: _isSaving
                              ? null
                              : () => _pilihWaktu(mulai: true),
                          icon: const Icon(Icons.access_time),
                          label: Text('Mulai ${_formatWaktu(_waktuMulai)}'),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: _isSaving
                              ? null
                              : () => _pilihWaktu(mulai: false),
                          icon: const Icon(Icons.access_time),
                          label: Text('Selesai ${_formatWaktu(_waktuSelesai)}'),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    initialValue: _gang,
                    decoration: const InputDecoration(
                      labelText: 'Filter Gang',
                      border: OutlineInputBorder(),
                    ),
                    items: _daftarGang
                        .map(
                          (gang) =>
                              DropdownMenuItem(value: gang, child: Text(gang)),
                        )
                        .toList(),
                    onChanged: _isSaving ? null : _ubahGang,
                    validator: (value) =>
                        value == null ? 'Gang wajib dipilih.' : null,
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Peserta Ronda',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 6),
                  if (_daftarPeserta.isEmpty)
                    const Text('Tidak ada warga aktif yang dapat dipilih.')
                  else if (_pesertaTampil.isEmpty)
                    Text('Tidak ada warga aktif di ${_gang ?? _semuaGang}.')
                  else
                    ..._pesertaTampil.map((peserta) {
                      final id = int.tryParse(peserta['id'].toString());
                      if (id == null) return const SizedBox.shrink();
                      final nama = peserta['nama']?.toString() ?? '-';
                      final gang = peserta['gang']?.toString() ?? '-';
                      final blok = peserta['blok']?.toString() ?? '-';
                      return CheckboxListTile(
                        value: _pesertaTerpilih.contains(id),
                        contentPadding: EdgeInsets.zero,
                        title: Text(nama),
                        subtitle: Text('$gang • Blok $blok'),
                        onChanged: _isSaving
                            ? null
                            : (selected) {
                                setState(() {
                                  if (selected == true) {
                                    _pesertaTerpilih.add(id);
                                  } else {
                                    _pesertaTerpilih.remove(id);
                                  }
                                });
                              },
                      );
                    }),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _keteranganController,
                    minLines: 2,
                    maxLines: 4,
                    decoration: const InputDecoration(
                      labelText: 'Keterangan',
                      border: OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 20),
                  FilledButton.icon(
                    onPressed: _isSaving ? null : _simpan,
                    icon: _isSaving
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Icon(Icons.save_outlined),
                    label: Text(_isSaving ? 'Menyimpan...' : 'Simpan'),
                  ),
                ],
              ),
            ),
    );
  }
}
