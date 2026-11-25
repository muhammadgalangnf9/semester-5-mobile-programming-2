import 'package:flutter/material.dart';

import '../../core/services/kegiatan_service.dart';
import 'form_komponen_kegiatan_screen.dart';

class FormKegiatanScreen extends StatefulWidget {
  final Map<String, dynamic>? kegiatan;

  const FormKegiatanScreen({super.key, this.kegiatan});

  @override
  State<FormKegiatanScreen> createState() => _FormKegiatanScreenState();
}

class _FormKegiatanScreenState extends State<FormKegiatanScreen> {
  static const String _pilihanCustom = '__custom__';
  static const List<String> _jenisKegiatanStandar = [
    'Sosial',
    'Keagamaan',
    'Olahraga',
    'Kebersihan',
    'Pendidikan',
    'Peringatan Hari Besar',
    'Rapat',
    'Lainnya',
  ];
  static const List<String> _lokasiStandar = ['Gang', 'Musola', 'Lapangan'];

  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _namaController;
  late final TextEditingController _jenisController;
  late final TextEditingController _lokasiController;
  late final TextEditingController _deskripsiController;
  late final TextEditingController _posterController;
  late DateTime _tanggalMulai;
  late DateTime _tanggalSelesai;
  final Map<KomponenKegiatan, List<Map<String, dynamic>>> _komponen = {
    for (final komponen in KomponenKegiatan.values) komponen: [],
  };
  final Map<KomponenKegiatan, Set<int>> _komponenDihapus = {
    for (final komponen in KomponenKegiatan.values) komponen: {},
  };
  String? _jenisKegiatanPilihan;
  String? _lokasiPilihan;
  int? _kegiatanIdTersimpan;
  bool _isLoadingKomponen = false;
  bool _isSaving = false;
  String? _errorKomponen;

  bool get _isEditing => widget.kegiatan != null;

  @override
  void initState() {
    super.initState();
    final kegiatan = widget.kegiatan;
    _kegiatanIdTersimpan = int.tryParse(kegiatan?['id']?.toString() ?? '');
    _namaController = TextEditingController(
      text: kegiatan?['nama_kegiatan']?.toString() ?? '',
    );
    _jenisController = TextEditingController(
      text: kegiatan?['jenis_kegiatan']?.toString() ?? '',
    );
    final jenisKegiatan = _jenisController.text;
    _jenisKegiatanPilihan = jenisKegiatan.isEmpty
        ? null
        : _jenisKegiatanStandar.contains(jenisKegiatan)
        ? jenisKegiatan
        : _pilihanCustom;
    _lokasiController = TextEditingController(
      text: kegiatan?['lokasi']?.toString() ?? '',
    );
    final lokasi = _lokasiController.text;
    _lokasiPilihan = lokasi.isEmpty
        ? null
        : _lokasiStandar.contains(lokasi)
        ? lokasi
        : _pilihanCustom;
    _deskripsiController = TextEditingController(
      text: kegiatan?['deskripsi']?.toString() ?? '',
    );
    _posterController = TextEditingController(
      text: kegiatan?['link_poster']?.toString() ?? '',
    );
    final sekarang = DateTime.now();
    _tanggalMulai =
        DateTime.tryParse(kegiatan?['tanggal_mulai']?.toString() ?? '') ??
        sekarang;
    _tanggalSelesai =
        DateTime.tryParse(kegiatan?['tanggal_selesai']?.toString() ?? '') ??
        _tanggalMulai.add(const Duration(hours: 1));

    if (_isEditing) {
      _isLoadingKomponen = true;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _ambilKomponenAwal();
      });
    }
  }

  @override
  void dispose() {
    _namaController.dispose();
    _jenisController.dispose();
    _lokasiController.dispose();
    _deskripsiController.dispose();
    _posterController.dispose();
    super.dispose();
  }

  Future<DateTime?> _pilihTanggalWaktu(DateTime awal) async {
    final tanggal = await showDatePicker(
      context: context,
      initialDate: awal,
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (tanggal == null || !mounted) return null;

    final waktu = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(awal),
    );
    if (waktu == null) return null;

    return DateTime(
      tanggal.year,
      tanggal.month,
      tanggal.day,
      waktu.hour,
      waktu.minute,
    );
  }

  String _formatTanggalWaktu(DateTime value) {
    final tanggal =
        '${value.day.toString().padLeft(2, '0')}/'
        '${value.month.toString().padLeft(2, '0')}/${value.year}';
    final waktu =
        '${value.hour.toString().padLeft(2, '0')}:'
        '${value.minute.toString().padLeft(2, '0')}';
    return '$tanggal $waktu';
  }

  Future<void> _ambilKomponenAwal() async {
    final id = _kegiatanIdTersimpan;
    if (id == null) {
      setState(() {
        _isLoadingKomponen = false;
        _errorKomponen = 'ID kegiatan tidak valid.';
      });
      return;
    }

    setState(() {
      _isLoadingKomponen = true;
      _errorKomponen = null;
    });

    try {
      final response = await KegiatanService.getDetailKegiatanWargaPJ(id);
      final data = response['data'];
      if (data is! Map) {
        throw Exception('Format detail kegiatan tidak valid.');
      }

      final komponenBaru = <KomponenKegiatan, List<Map<String, dynamic>>>{};
      for (final komponen in KomponenKegiatan.values) {
        final daftar = data[komponen.name];
        if (daftar is! List) {
          throw Exception(
            'Format daftar ${_labelKomponen(komponen).toLowerCase()} tidak valid.',
          );
        }
        komponenBaru[komponen] = daftar
            .whereType<Map>()
            .map((item) => Map<String, dynamic>.from(item))
            .toList();
      }

      if (!mounted) return;
      setState(() {
        for (final komponen in KomponenKegiatan.values) {
          _komponen[komponen]!
            ..clear()
            ..addAll(komponenBaru[komponen]!);
        }
        _isLoadingKomponen = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorKomponen = e.toString().replaceFirst('Exception: ', '');
        _isLoadingKomponen = false;
      });
    }
  }

  String _labelKomponen(KomponenKegiatan komponen) => switch (komponen) {
    KomponenKegiatan.panitia => 'Panitia Kegiatan',
    KomponenKegiatan.rab => 'Rencana Anggaran Belanja',
    KomponenKegiatan.transaksi => 'Transaksi Kegiatan',
    KomponenKegiatan.peserta => 'Peserta Kegiatan',
    KomponenKegiatan.dokumentasi => 'Dokumentasi',
  };

  String _judulDataKomponen(
    KomponenKegiatan komponen,
    Map<String, dynamic> data,
  ) => switch (komponen) {
    KomponenKegiatan.panitia => data['jabatan']?.toString() ?? 'Panitia',
    KomponenKegiatan.rab => data['nama_kebutuhan']?.toString() ?? '-',
    KomponenKegiatan.transaksi => data['kategori']?.toString() ?? '-',
    KomponenKegiatan.peserta => data['nama']?.toString() ?? '-',
    KomponenKegiatan.dokumentasi => data['nama_dokumentasi']?.toString() ?? '-',
  };

  String _ringkasanDataKomponen(
    KomponenKegiatan komponen,
    Map<String, dynamic> data,
  ) => switch (komponen) {
    KomponenKegiatan.panitia =>
      '${data['nama_panitia'] ?? '-'} • ${data['tugas'] ?? '-'}',
    KomponenKegiatan.rab =>
      '${data['kategori_pengeluaran'] ?? '-'} • '
          '${data['jumlah'] ?? '-'} ${data['satuan'] ?? ''} • '
          '${data['sumber_dana'] ?? '-'}',
    KomponenKegiatan.transaksi =>
      '${data['jenis_transaksi'] ?? '-'} • ${data['nominal'] ?? '-'}',
    KomponenKegiatan.peserta =>
      '${data['gang'] ?? '-'} • ${data['status_kehadiran'] ?? '-'}',
    KomponenKegiatan.dokumentasi =>
      '${data['jenis'] ?? '-'} • ${data['link_drive'] ?? '-'}',
  };

  Future<void> _bukaFormKomponen(
    KomponenKegiatan komponen, {
    Map<String, dynamic>? data,
  }) async {
    final hasil = await Navigator.push<Map<String, dynamic>>(
      context,
      MaterialPageRoute(
        builder: (_) => FormKomponenKegiatanScreen(
          kegiatanId: _kegiatanIdTersimpan ?? 0,
          komponen: komponen,
          data: data,
          draftOnly: true,
        ),
      ),
    );
    if (hasil == null || !mounted) return;

    final daftar = _komponen[komponen]!;
    if (data == null) {
      setState(() => daftar.add(hasil));
      return;
    }

    final index = daftar.indexOf(data);
    if (index >= 0) {
      setState(() {
        daftar[index] = {...hasil, if (data['id'] != null) 'id': data['id']};
      });
    }
  }

  Future<void> _hapusDraftKomponen(
    KomponenKegiatan komponen,
    Map<String, dynamic> data,
  ) async {
    final konfirmasi = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Hapus data?'),
        content: Text(
          'Data "${_judulDataKomponen(komponen, data)}" akan dihapus.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    if (konfirmasi != true) return;

    final id = int.tryParse(data['id']?.toString() ?? '');
    setState(() {
      if (id != null) _komponenDihapus[komponen]!.add(id);
      _komponen[komponen]!.remove(data);
    });
  }

  Widget _buildBagianKomponen(KomponenKegiatan komponen) {
    final daftar = _komponen[komponen]!;
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        children: [
          ListTile(
            title: Text(_labelKomponen(komponen)),
            subtitle: Text('${daftar.length} item'),
            trailing: IconButton(
              tooltip: 'Tambah ${_labelKomponen(komponen).toLowerCase()}',
              onPressed:
                  _isSaving || _isLoadingKomponen || _errorKomponen != null
                  ? null
                  : () => _bukaFormKomponen(komponen),
              icon: const Icon(Icons.add_circle_outline),
            ),
          ),
          if (daftar.isEmpty)
            const Padding(
              padding: EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Align(
                alignment: Alignment.centerLeft,
                child: Text('Belum ada data.'),
              ),
            )
          else
            ...daftar.map(
              (data) => ListTile(
                title: Text(_judulDataKomponen(komponen, data)),
                subtitle: Text(_ringkasanDataKomponen(komponen, data)),
                trailing: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    IconButton(
                      tooltip: 'Edit',
                      onPressed: _isSaving || _isLoadingKomponen
                          ? null
                          : () => _bukaFormKomponen(komponen, data: data),
                      icon: const Icon(Icons.edit_outlined),
                    ),
                    IconButton(
                      tooltip: 'Hapus',
                      onPressed: _isSaving || _isLoadingKomponen
                          ? null
                          : () => _hapusDraftKomponen(komponen, data),
                      icon: const Icon(Icons.delete_outline),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }

  Map<String, dynamic> _ambilDataResponse(
    Map<String, dynamic> response,
    String deskripsi,
  ) {
    final data = response['data'];
    if (data is! Map) {
      throw Exception('Tidak dapat membaca data $deskripsi dari server.');
    }
    return Map<String, dynamic>.from(data);
  }

  Future<void> _simpanKomponen(int kegiatanId) async {
    for (final komponen in KomponenKegiatan.values) {
      final daftar = _komponen[komponen]!;
      for (var index = 0; index < daftar.length; index++) {
        final item = daftar[index];
        final id = int.tryParse(item['id']?.toString() ?? '');
        if (id != null) {
          await KegiatanService.ubahKomponenKegiatan(
            kegiatanId,
            komponen,
            id,
            item,
          );
        } else {
          final response = await KegiatanService.tambahKomponenKegiatan(
            kegiatanId,
            komponen,
            item,
          );
          final dataResponse = _ambilDataResponse(
            response,
            _labelKomponen(komponen).toLowerCase(),
          );
          final idBaru = int.tryParse(dataResponse['id']?.toString() ?? '');
          if (idBaru == null) {
            throw Exception(
              'ID ${_labelKomponen(komponen).toLowerCase()} baru tidak ditemukan.',
            );
          }
          daftar[index] = {...item, 'id': idBaru};
        }
      }

      for (final id in _komponenDihapus[komponen]!.toList()) {
        await KegiatanService.hapusKomponenKegiatan(kegiatanId, komponen, id);
        _komponenDihapus[komponen]!.remove(id);
      }
    }
  }

  Future<void> _simpan() async {
    if (_isLoadingKomponen || _errorKomponen != null) return;
    if (!_formKey.currentState!.validate()) return;
    if (_tanggalSelesai.isBefore(_tanggalMulai)) {
      _tampilkanPesan('Tanggal selesai tidak boleh sebelum tanggal mulai.');
      return;
    }

    setState(() => _isSaving = true);
    try {
      final data = {
        'nama_kegiatan': _namaController.text.trim(),
        'jenis_kegiatan': _jenisController.text.trim(),
        'lokasi': _lokasiController.text.trim(),
        'deskripsi': _deskripsiController.text.trim(),
        'link_poster': _posterController.text.trim().isEmpty
            ? null
            : _posterController.text.trim(),
        'tanggal_mulai': _tanggalMulai.toIso8601String(),
        'tanggal_selesai': _tanggalSelesai.toIso8601String(),
      };

      var id = _kegiatanIdTersimpan;
      if (id == null) {
        final response = await KegiatanService.tambahKegiatan(data);
        final dataResponse = _ambilDataResponse(response, 'kegiatan');
        id = int.tryParse(dataResponse['id']?.toString() ?? '');
        if (id == null) {
          throw Exception('ID kegiatan baru tidak ditemukan.');
        }
        _kegiatanIdTersimpan = id;
      } else {
        await KegiatanService.ubahKegiatan(id, data);
      }

      await _simpanKomponen(id);
      if (!mounted) return;
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      final pesan = e.toString().replaceFirst('Exception: ', '');
      _tampilkanPesan(
        _kegiatanIdTersimpan == null
            ? pesan
            : 'Kegiatan mungkin sudah tersimpan. $pesan',
      );
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
        title: Text(_isEditing ? 'Edit Kegiatan' : 'Tambah Kegiatan'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _namaController,
              maxLength: 150,
              decoration: const InputDecoration(
                labelText: 'Nama Kegiatan',
                border: OutlineInputBorder(),
              ),
              validator: (value) => value == null || value.trim().isEmpty
                  ? 'Nama kegiatan wajib diisi.'
                  : null,
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: _jenisKegiatanPilihan,
              decoration: const InputDecoration(
                labelText: 'Jenis Kegiatan',
                border: OutlineInputBorder(),
              ),
              items: [
                ..._jenisKegiatanStandar.map(
                  (jenis) => DropdownMenuItem(value: jenis, child: Text(jenis)),
                ),
                const DropdownMenuItem(
                  value: _pilihanCustom,
                  child: Text('Custom'),
                ),
              ],
              onChanged: _isSaving
                  ? null
                  : (value) {
                      setState(() {
                        if (value == _pilihanCustom) {
                          if (_jenisKegiatanPilihan != _pilihanCustom) {
                            _jenisController.clear();
                          }
                        } else {
                          _jenisController.text = value ?? '';
                        }
                        _jenisKegiatanPilihan = value;
                      });
                    },
              validator: (value) =>
                  value == null ? 'Jenis kegiatan wajib dipilih.' : null,
            ),
            if (_jenisKegiatanPilihan == _pilihanCustom) ...[
              const SizedBox(height: 12),
              TextFormField(
                controller: _jenisController,
                maxLength: 100,
                decoration: const InputDecoration(
                  labelText: 'Jenis Kegiatan Custom',
                  border: OutlineInputBorder(),
                ),
                validator: (value) =>
                    _jenisKegiatanPilihan == _pilihanCustom &&
                        (value == null || value.trim().isEmpty)
                    ? 'Jenis kegiatan wajib diisi.'
                    : null,
              ),
            ],
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: _isSaving
                  ? null
                  : () async {
                      final value = await _pilihTanggalWaktu(_tanggalMulai);
                      if (value != null) setState(() => _tanggalMulai = value);
                    },
              icon: const Icon(Icons.event_outlined),
              label: Text('Mulai: ${_formatTanggalWaktu(_tanggalMulai)}'),
            ),
            const SizedBox(height: 8),
            OutlinedButton.icon(
              onPressed: _isSaving
                  ? null
                  : () async {
                      final value = await _pilihTanggalWaktu(_tanggalSelesai);
                      if (value != null) {
                        setState(() => _tanggalSelesai = value);
                      }
                    },
              icon: const Icon(Icons.event_available_outlined),
              label: Text('Selesai: ${_formatTanggalWaktu(_tanggalSelesai)}'),
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: _lokasiPilihan,
              decoration: const InputDecoration(
                labelText: 'Lokasi',
                border: OutlineInputBorder(),
              ),
              items: [
                ..._lokasiStandar.map(
                  (lokasi) =>
                      DropdownMenuItem(value: lokasi, child: Text(lokasi)),
                ),
                const DropdownMenuItem(
                  value: _pilihanCustom,
                  child: Text('Custom'),
                ),
              ],
              onChanged: _isSaving
                  ? null
                  : (value) {
                      setState(() {
                        if (value == _pilihanCustom) {
                          if (_lokasiPilihan != _pilihanCustom) {
                            _lokasiController.clear();
                          }
                        } else {
                          _lokasiController.text = value ?? '';
                        }
                        _lokasiPilihan = value;
                      });
                    },
              validator: (value) =>
                  value == null ? 'Lokasi wajib dipilih.' : null,
            ),
            if (_lokasiPilihan == _pilihanCustom) ...[
              const SizedBox(height: 12),
              TextFormField(
                controller: _lokasiController,
                maxLength: 150,
                decoration: const InputDecoration(
                  labelText: 'Lokasi Custom',
                  border: OutlineInputBorder(),
                ),
                validator: (value) =>
                    _lokasiPilihan == _pilihanCustom &&
                        (value == null || value.trim().isEmpty)
                    ? 'Lokasi wajib diisi.'
                    : null,
              ),
            ],
            const SizedBox(height: 12),
            TextFormField(
              controller: _posterController,
              keyboardType: TextInputType.url,
              decoration: const InputDecoration(
                labelText: 'Link Poster (opsional)',
                border: OutlineInputBorder(),
              ),
              validator: (value) {
                if (value == null || value.trim().isEmpty) return null;
                final uri = Uri.tryParse(value.trim());
                if (uri == null ||
                    !uri.hasScheme ||
                    (uri.scheme != 'http' && uri.scheme != 'https') ||
                    uri.host.isEmpty) {
                  return 'Masukkan URL poster yang valid.';
                }
                return null;
              },
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _deskripsiController,
              minLines: 4,
              maxLines: 8,
              decoration: const InputDecoration(
                labelText: 'Deskripsi',
                alignLabelWithHint: true,
                border: OutlineInputBorder(),
              ),
              validator: (value) => value == null || value.trim().isEmpty
                  ? 'Deskripsi wajib diisi.'
                  : null,
            ),
            const SizedBox(height: 20),
            if (_isLoadingKomponen) ...[
              const LinearProgressIndicator(),
              const SizedBox(height: 12),
              const Text('Memuat data komponen kegiatan...'),
              const SizedBox(height: 12),
            ],
            if (_errorKomponen != null) ...[
              Text(
                'Gagal memuat komponen kegiatan: $_errorKomponen',
                style: const TextStyle(color: Colors.red),
              ),
              TextButton.icon(
                onPressed: _isSaving ? null : _ambilKomponenAwal,
                icon: const Icon(Icons.refresh),
                label: const Text('Coba lagi'),
              ),
            ],
            for (final komponen in KomponenKegiatan.values)
              _buildBagianKomponen(komponen),
            const SizedBox(height: 20),
            FilledButton.icon(
              onPressed:
                  _isSaving || _isLoadingKomponen || _errorKomponen != null
                  ? null
                  : _simpan,
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
