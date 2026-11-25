import 'package:flutter/material.dart';

import '../../core/services/pengumuman_service.dart';

class FormPengumumanScreen extends StatefulWidget {
  final Map<String, dynamic>? pengumuman;

  const FormPengumumanScreen({super.key, this.pengumuman});

  @override
  State<FormPengumumanScreen> createState() => _FormPengumumanScreenState();
}

class _FormPengumumanScreenState extends State<FormPengumumanScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _judulController;
  late final TextEditingController _isiController;
  bool _isSaving = false;

  bool get _isEditing => widget.pengumuman != null;

  @override
  void initState() {
    super.initState();
    _judulController = TextEditingController(
      text: widget.pengumuman?['judul']?.toString() ?? '',
    );
    _isiController = TextEditingController(
      text: widget.pengumuman?['isi']?.toString() ?? '',
    );
  }

  @override
  void dispose() {
    _judulController.dispose();
    _isiController.dispose();
    super.dispose();
  }

  Future<void> _simpan() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);

    try {
      final data = {
        'judul': _judulController.text.trim(),
        'isi': _isiController.text.trim(),
      };
      final id = int.tryParse(widget.pengumuman?['id']?.toString() ?? '');

      if (_isEditing && id != null) {
        await PengumumanService.ubahPengumuman(id, data);
      } else {
        await PengumumanService.tambahPengumuman(data);
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
        title: Text(_isEditing ? 'Edit Pengumuman' : 'Tambah Pengumuman'),
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
              controller: _judulController,
              maxLength: 150,
              decoration: const InputDecoration(
                labelText: 'Judul',
                border: OutlineInputBorder(),
              ),
              validator: (value) => value == null || value.trim().isEmpty
                  ? 'Judul wajib diisi.'
                  : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _isiController,
              minLines: 6,
              maxLines: 12,
              decoration: const InputDecoration(
                labelText: 'Isi Pengumuman',
                alignLabelWithHint: true,
                border: OutlineInputBorder(),
              ),
              validator: (value) => value == null || value.trim().isEmpty
                  ? 'Isi pengumuman wajib diisi.'
                  : null,
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                _isEditing && widget.pengumuman?['status'] == 'terbit'
                    ? 'Setelah diubah, pengumuman akan kembali menunggu '
                          'persetujuan Ketua RT sebelum ditampilkan kepada warga.'
                    : 'Pengumuman disimpan sebagai draft. Setelah disimpan, '
                          'ajukan persetujuan kepada Ketua RT agar dapat diterbitkan.',
                style: TextStyle(fontSize: 13),
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
