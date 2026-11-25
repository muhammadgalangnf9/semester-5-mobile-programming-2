import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../core/services/kegiatan_service.dart';

class FormKomponenKegiatanScreen extends StatefulWidget {
  final int kegiatanId;
  final KomponenKegiatan komponen;
  final Map<String, dynamic>? data;
  final bool draftOnly;

  const FormKomponenKegiatanScreen({
    super.key,
    required this.kegiatanId,
    required this.komponen,
    this.data,
    this.draftOnly = false,
  });

  @override
  State<FormKomponenKegiatanScreen> createState() =>
      _FormKomponenKegiatanScreenState();
}

class _FormKomponenKegiatanScreenState
    extends State<FormKomponenKegiatanScreen> {
  static const String _pilihanCustom = '__custom__';
  static const List<String> _daftarJabatan = [
    'Ketua Pelaksana',
    'Sekretaris',
    'Bendahara',
    'Koordinator',
    'Anggota',
  ];
  static const List<String> _daftarGang = [
    'Gang E6',
    'Gang E4/E5',
    'Gang E2/E3',
    'Gang C16/E1',
  ];

  final _formKey = GlobalKey<FormState>();
  final Map<String, TextEditingController> _controllers = {};

  String? _pilihan;
  String? _jabatanPilihan;
  String? _gangPesertaPilihan;
  DateTime _tanggalTransaksi = DateTime.now();
  bool _isSaving = false;

  bool get _isEditing => widget.data != null;

  @override
  void initState() {
    super.initState();
    final nilaiAwal = switch (widget.komponen) {
      KomponenKegiatan.panitia => {
        'nama_panitia': 'nama_panitia',
        'jabatan': 'jabatan',
        'tugas': 'tugas',
      },
      KomponenKegiatan.rab => {
        'nama_kebutuhan': 'nama_kebutuhan',
        'kategori_pengeluaran': 'kategori_pengeluaran',
        'jumlah': 'jumlah',
        'satuan': 'satuan',
        'harga_satuan': 'harga_satuan',
        'sumber_dana': 'sumber_dana',
      },
      KomponenKegiatan.transaksi => {
        'kategori': 'kategori',
        'nominal': 'nominal',
        'keterangan': 'keterangan',
        'link_bukti': 'link_bukti',
      },
      KomponenKegiatan.peserta => {
        'nama': 'nama',
        'gang': 'gang',
        'keterangan': 'keterangan',
      },
      KomponenKegiatan.dokumentasi => {
        'nama_dokumentasi': 'nama_dokumentasi',
        'link_drive': 'link_drive',
      },
    };

    for (final entry in nilaiAwal.entries) {
      _controllers[entry.key] = TextEditingController(
        text: widget.data?[entry.value]?.toString() ?? '',
      );
    }

    if (widget.komponen == KomponenKegiatan.panitia) {
      final jabatan = _controller('jabatan').text;
      _jabatanPilihan = jabatan.isEmpty
          ? null
          : _daftarJabatan.contains(jabatan)
          ? jabatan
          : _pilihanCustom;
    }
    if (widget.komponen == KomponenKegiatan.peserta) {
      final gang = _controller('gang').text;
      _gangPesertaPilihan = gang.isEmpty
          ? null
          : _daftarGang.contains(gang)
          ? gang
          : _pilihanCustom;
    }

    _pilihan = switch (widget.komponen) {
      KomponenKegiatan.transaksi =>
        widget.data?['jenis_transaksi']?.toString() ?? 'pengeluaran',
      KomponenKegiatan.peserta =>
        widget.data?['status_kehadiran']?.toString() ?? 'belum_dikonfirmasi',
      KomponenKegiatan.dokumentasi =>
        widget.data?['jenis']?.toString() ?? 'foto',
      _ => null,
    };

    final tanggal = DateTime.tryParse(
      widget.data?['tanggal_transaksi']?.toString() ?? '',
    );
    if (tanggal != null) _tanggalTransaksi = tanggal;
  }

  @override
  void dispose() {
    for (final controller in _controllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  String get _judul {
    final aksi = _isEditing ? 'Edit' : 'Tambah';
    return switch (widget.komponen) {
      KomponenKegiatan.panitia => '$aksi Panitia',
      KomponenKegiatan.rab => '$aksi RAB',
      KomponenKegiatan.transaksi => '$aksi Transaksi',
      KomponenKegiatan.peserta => '$aksi Peserta',
      KomponenKegiatan.dokumentasi => '$aksi Dokumentasi',
    };
  }

  TextEditingController _controller(String key) => _controllers[key]!;

  Widget _field(
    String key,
    String label, {
    bool required = true,
    TextInputType keyboardType = TextInputType.text,
    int maxLines = 1,
    int? maxLength,
    List<TextInputFormatter>? inputFormatters,
  }) {
    return TextFormField(
      controller: _controller(key),
      keyboardType: keyboardType,
      maxLines: maxLines,
      maxLength: maxLength,
      inputFormatters: inputFormatters,
      decoration: InputDecoration(
        labelText: label,
        border: const OutlineInputBorder(),
      ),
      validator: (value) {
        if (required && (value == null || value.trim().isEmpty)) {
          return '$label wajib diisi.';
        }
        return null;
      },
    );
  }

  Future<void> _pilihTanggalTransaksi() async {
    final tanggal = await showDatePicker(
      context: context,
      initialDate: _tanggalTransaksi,
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (tanggal != null && mounted) {
      setState(() {
        _tanggalTransaksi = DateTime(tanggal.year, tanggal.month, tanggal.day);
      });
    }
  }

  String _formatTanggal(DateTime tanggal) =>
      '${tanggal.year}-${tanggal.month.toString().padLeft(2, '0')}-'
      '${tanggal.day.toString().padLeft(2, '0')}';

  Widget _dropdown({
    required String label,
    required String? value,
    required List<DropdownMenuItem<String>> items,
    required ValueChanged<String?> onChanged,
  }) {
    return DropdownButtonFormField<String>(
      initialValue: value,
      decoration: InputDecoration(
        labelText: label,
        border: const OutlineInputBorder(),
      ),
      items: items,
      onChanged: _isSaving ? null : onChanged,
      validator: (value) => value == null ? '$label wajib dipilih.' : null,
    );
  }

  void _ubahPilihanCustom({
    required String key,
    required String? pilihanSaatIni,
    required String? pilihanBaru,
    required ValueChanged<String?> onChanged,
  }) {
    if (pilihanBaru == _pilihanCustom) {
      if (pilihanSaatIni != _pilihanCustom) _controller(key).clear();
    } else if (pilihanBaru != null) {
      _controller(key).text = pilihanBaru;
    }

    setState(() => onChanged(pilihanBaru));
  }

  List<Widget> _buildFields() {
    return switch (widget.komponen) {
      KomponenKegiatan.panitia => [
        _field('nama_panitia', 'Nama Panitia', maxLength: 150),
        const SizedBox(height: 12),
        _dropdown(
          label: 'Jabatan',
          value: _jabatanPilihan,
          items: [
            ..._daftarJabatan.map(
              (jabatan) =>
                  DropdownMenuItem(value: jabatan, child: Text(jabatan)),
            ),
            const DropdownMenuItem(
              value: _pilihanCustom,
              child: Text('Custom'),
            ),
          ],
          onChanged: (value) => _ubahPilihanCustom(
            key: 'jabatan',
            pilihanSaatIni: _jabatanPilihan,
            pilihanBaru: value,
            onChanged: (pilihan) => _jabatanPilihan = pilihan,
          ),
        ),
        if (_jabatanPilihan == _pilihanCustom) ...[
          const SizedBox(height: 12),
          _field('jabatan', 'Jabatan Custom', maxLength: 100),
        ],
        const SizedBox(height: 12),
        _field('tugas', 'Tugas (opsional)', required: false, maxLines: 3),
      ],
      KomponenKegiatan.rab => [
        _field('nama_kebutuhan', 'Nama Kebutuhan', maxLength: 150),
        const SizedBox(height: 12),
        _field('kategori_pengeluaran', 'Kategori Pengeluaran', maxLength: 100),
        const SizedBox(height: 12),
        _field(
          'jumlah',
          'Jumlah',
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
        ),
        const SizedBox(height: 12),
        _field('satuan', 'Satuan', maxLength: 100),
        const SizedBox(height: 12),
        _field(
          'harga_satuan',
          'Harga Satuan',
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
        ),
        const SizedBox(height: 12),
        _field('sumber_dana', 'Sumber Dana', maxLength: 100),
      ],
      KomponenKegiatan.transaksi => [
        _dropdown(
          label: 'Jenis Transaksi',
          value: _pilihan,
          items: const [
            DropdownMenuItem(value: 'pemasukan', child: Text('Pemasukan')),
            DropdownMenuItem(value: 'pengeluaran', child: Text('Pengeluaran')),
          ],
          onChanged: (value) => setState(() => _pilihan = value),
        ),
        const SizedBox(height: 12),
        _field('kategori', 'Kategori', maxLength: 100),
        const SizedBox(height: 12),
        OutlinedButton.icon(
          onPressed: _isSaving ? null : _pilihTanggalTransaksi,
          icon: const Icon(Icons.calendar_today_outlined),
          label: Text('Tanggal: ${_formatTanggal(_tanggalTransaksi)}'),
        ),
        const SizedBox(height: 12),
        _field(
          'nominal',
          'Nominal',
          keyboardType: const TextInputType.numberWithOptions(decimal: true),
        ),
        const SizedBox(height: 12),
        _field(
          'keterangan',
          'Keterangan (opsional)',
          required: false,
          maxLines: 3,
        ),
        const SizedBox(height: 12),
        _field(
          'link_bukti',
          'Link Bukti (opsional)',
          required: false,
          keyboardType: TextInputType.url,
          maxLength: 500,
        ),
      ],
      KomponenKegiatan.peserta => [
        _field('nama', 'Nama Peserta', maxLength: 150),
        const SizedBox(height: 12),
        _dropdown(
          label: 'Gang',
          value: _gangPesertaPilihan,
          items: [
            ..._daftarGang.map(
              (gang) => DropdownMenuItem(value: gang, child: Text(gang)),
            ),
            const DropdownMenuItem(
              value: _pilihanCustom,
              child: Text('Custom'),
            ),
          ],
          onChanged: (value) => _ubahPilihanCustom(
            key: 'gang',
            pilihanSaatIni: _gangPesertaPilihan,
            pilihanBaru: value,
            onChanged: (pilihan) => _gangPesertaPilihan = pilihan,
          ),
        ),
        if (_gangPesertaPilihan == _pilihanCustom) ...[
          const SizedBox(height: 12),
          _field('gang', 'Gang Custom', maxLength: 100),
        ],
        const SizedBox(height: 12),
        _dropdown(
          label: 'Status Kehadiran',
          value: _pilihan,
          items: const [
            DropdownMenuItem(
              value: 'belum_dikonfirmasi',
              child: Text('Belum Dikonfirmasi'),
            ),
            DropdownMenuItem(value: 'hadir', child: Text('Hadir')),
            DropdownMenuItem(value: 'tidak_hadir', child: Text('Tidak Hadir')),
            DropdownMenuItem(value: 'izin', child: Text('Izin')),
            DropdownMenuItem(value: 'sakit', child: Text('Sakit')),
          ],
          onChanged: (value) => setState(() => _pilihan = value),
        ),
        const SizedBox(height: 12),
        _field(
          'keterangan',
          'Keterangan (opsional)',
          required: false,
          maxLines: 3,
        ),
      ],
      KomponenKegiatan.dokumentasi => [
        _field('nama_dokumentasi', 'Nama Dokumentasi', maxLength: 150),
        const SizedBox(height: 12),
        _dropdown(
          label: 'Jenis Dokumentasi',
          value: _pilihan,
          items: const [
            DropdownMenuItem(value: 'foto', child: Text('Foto')),
            DropdownMenuItem(value: 'video', child: Text('Video')),
          ],
          onChanged: (value) => setState(() => _pilihan = value),
        ),
        const SizedBox(height: 12),
        _field(
          'link_drive',
          'Link Google Drive',
          keyboardType: TextInputType.url,
          maxLength: 500,
        ),
      ],
    };
  }

  Map<String, dynamic> _dataForm() {
    return switch (widget.komponen) {
      KomponenKegiatan.panitia => {
        'nama_panitia': _controller('nama_panitia').text.trim(),
        'jabatan': _controller('jabatan').text.trim(),
        'tugas': _controller('tugas').text.trim(),
      },
      KomponenKegiatan.rab => {
        'nama_kebutuhan': _controller('nama_kebutuhan').text.trim(),
        'kategori_pengeluaran': _controller('kategori_pengeluaran').text.trim(),
        'jumlah': _controller('jumlah').text.trim(),
        'satuan': _controller('satuan').text.trim(),
        'harga_satuan': _controller('harga_satuan').text.trim(),
        'sumber_dana': _controller('sumber_dana').text.trim(),
      },
      KomponenKegiatan.transaksi => {
        'jenis_transaksi': _pilihan,
        'kategori': _controller('kategori').text.trim(),
        'tanggal_transaksi': _formatTanggal(_tanggalTransaksi),
        'nominal': _controller('nominal').text.trim(),
        'keterangan': _controller('keterangan').text.trim(),
        'link_bukti': _controller('link_bukti').text.trim().isEmpty
            ? null
            : _controller('link_bukti').text.trim(),
      },
      KomponenKegiatan.peserta => {
        'nama': _controller('nama').text.trim(),
        'gang': _controller('gang').text.trim(),
        'status_kehadiran': _pilihan,
        'keterangan': _controller('keterangan').text.trim(),
      },
      KomponenKegiatan.dokumentasi => {
        'nama_dokumentasi': _controller('nama_dokumentasi').text.trim(),
        'jenis': _pilihan,
        'link_drive': _controller('link_drive').text.trim(),
      },
    };
  }

  Future<void> _simpan() async {
    if (!_formKey.currentState!.validate()) return;

    if (widget.draftOnly) {
      Navigator.pop(context, _dataForm());
      return;
    }

    setState(() => _isSaving = true);
    try {
      final id = int.tryParse(widget.data?['id']?.toString() ?? '');
      if (_isEditing && id != null) {
        await KegiatanService.ubahKomponenKegiatan(
          widget.kegiatanId,
          widget.komponen,
          id,
          _dataForm(),
        );
      } else {
        await KegiatanService.tambahKomponenKegiatan(
          widget.kegiatanId,
          widget.komponen,
          _dataForm(),
        );
      }

      if (!mounted) return;
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
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(_judul),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            ..._buildFields(),
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
