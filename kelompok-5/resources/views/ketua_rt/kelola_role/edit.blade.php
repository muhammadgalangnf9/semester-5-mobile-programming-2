@extends('layouts.app')

@section('title', 'Edit Role - SIPEKAT')
@section('header', 'Edit Role')

@section('content')

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .page-header h2 {
        margin: 0 0 4px;
        font-size: 1.5rem;
    }

    .page-header p {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
    }

    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 24px;
        max-width: 640px;
    }

    .info-box {
        margin-bottom: 24px;
        padding: 12px 16px;
        background: #f9fafb;
        border: 1px solid #f3f4f6;
        border-radius: 6px;
    }

    .info-box .label {
        margin: 0 0 4px;
        font-size: 0.75rem;
        color: #6b7280;
    }

    .info-box .value {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }

    .alert-error {
        margin-bottom: 20px;
        padding: 12px 16px;
        border-radius: 6px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        font-size: 0.875rem;
        color: #991b1b;
    }

    .alert-error p {
        margin: 0 0 8px;
        font-weight: 600;
    }

    .alert-error ul {
        margin: 0;
        padding-left: 18px;
    }

    .alert-error li {
        margin-bottom: 4px;
    }

    .alert-error li:last-child {
        margin-bottom: 0;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
    }

    .form-group label .required {
        color: #dc2626;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 9px 12px;
        font-size: 0.9rem;
        font-family: inherit;
        color: #111827;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #9ca3af;
    }

    .form-hint {
        margin: 6px 0 0;
        font-size: 0.78rem;
        color: #6b7280;
    }

    .error-text {
        margin-top: 6px;
        font-size: 0.8rem;
        color: #dc2626;
    }

    .form-actions {
        display: flex;
        flex-direction: column-reverse;
        gap: 8px;
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    @media (min-width: 640px) {
        .form-actions {
            flex-direction: row;
        }
    }

    .btn {
        display: inline-block;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.875rem;
        font-family: inherit;
        text-decoration: none;
        text-align: center;
        transition: opacity 0.15s;
    }

    .btn:hover {
        opacity: 0.85;
    }

    .btn-primary {
        background: #2563eb;
        color: #fff;
        padding: 9px 18px;
    }

    .btn-secondary {
        background: #e5e7eb;
        color: #111827;
        padding: 9px 18px;
    }
</style>

<div class="page-header">
    <div>
        <h2>Edit Data Role</h2>
        <p>Perbarui informasi role yang dipilih.</p>
    </div>
</div>

<div class="card">

    {{-- Informasi Role --}}
    <div class="info-box">
        <p class="label">ID Role</p>
        <p class="value">#{{ $role->id }}</p>
    </div>

    {{-- Notifikasi Error --}}
    @if ($errors->any())
        <div class="alert-error">
            <p>Terdapat kesalahan pada input:</p>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ketua-rt.kelola-role.update', $role->id) }}"
          method="POST">

        @csrf
        @method('PUT')

        {{-- Nama Role --}}
        <div class="form-group">
            <label for="nama_role">
                Nama Role <span class="required">*</span>
            </label>

            <input type="text"
                   id="nama_role"
                   name="nama_role"
                   value="{{ old('nama_role', $role->nama_role) }}"
                   maxlength="50"
                   required
                   placeholder="Masukkan nama role">

            <p class="form-hint">
                Maksimal 50 karakter. Gunakan huruf, angka, tanda hubung, atau garis bawah.
            </p>

            @error('nama_role')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        {{-- Deskripsi --}}
        <div class="form-group">
            <label for="deskripsi">
                Deskripsi
            </label>

            <textarea id="deskripsi"
                      name="deskripsi"
                      rows="4"
                      maxlength="255"
                      placeholder="Masukkan deskripsi role">{{ old('deskripsi', $role->deskripsi) }}</textarea>

            <p class="form-hint">
                Maksimal 255 karakter.
            </p>

            @error('deskripsi')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tombol Aksi --}}
        <div class="form-actions">

            <a href="{{ route('ketua-rt.kelola-role.index') }}"
               class="btn btn-secondary">
                Batal
            </a>

            <button type="submit"
                    class="btn btn-primary">
                Simpan Perubahan
            </button>

        </div>

    </form>

</div>

@endsection