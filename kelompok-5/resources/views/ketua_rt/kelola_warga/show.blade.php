@extends('layouts.app')

@section('title', 'Detail Warga')
@section('header', 'Kelola Warga')

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
        padding: 20px;
    }

    .card table {
        width: 100%;
        min-width: 0;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .card th,
    .card td {
        text-align: left;
        padding: 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: top;
    }

    .card th {
        width: 200px;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
    }

    .card tr:last-child th,
    .card tr:last-child td {
        border-bottom: none;
    }

    .form-actions {
        display: flex;
        gap: 8px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        display: inline-block;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
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
        padding: 8px 16px;
    }

    .btn-secondary {
        background: #e5e7eb;
        color: #111827;
        padding: 8px 16px;
    }
</style>

<div class="page-header">
    <div>
        <h2>Detail Warga</h2>
        <p>Informasi lengkap akun warga.</p>
    </div>

    <a href="{{ route('ketua-rt.kelola-warga.index') }}" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <table>
        <tr>
            <th>Nama</th>
            <td>{{ $pengguna->nama }}</td>
        </tr>
        <tr>
            <th>Username</th>
            <td>{{ $pengguna->username }}</td>
        </tr>
        <tr>
            <th>Gang</th>
            <td>{{ $pengguna->gang ?? '-' }}</td>
        </tr>
        <tr>
            <th>Blok</th>
            <td>{{ $pengguna->blok ?? '-' }}</td>
        </tr>
        <tr>
            <th>Jenis Kelamin</th>
            <td>{{ $pengguna->jenis_kelamin ? ucfirst(str_replace('_', '-', $pengguna->jenis_kelamin)) : '-' }}</td>
        </tr>
        <tr>
            <th>Nomor HP</th>
            <td>{{ $pengguna->no_hp ?? '-' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $pengguna->email ?? '-' }}</td>
        </tr>
        <tr>
            <th>Role</th>
            <td>{{ $pengguna->labelRole() }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst($pengguna->status) }}</td>
        </tr>
        <tr>
            <th>Dinonaktifkan Pada</th>
            <td>
                {{ $pengguna->dinonaktifkan_pada?->format('d-m-Y H:i') ?? '-' }}
            </td>
        </tr>
        <tr>
            <th>Tanggal Dibuat</th>
            <td>
                {{ $pengguna->created_at?->format('d-m-Y H:i') ?? '-' }}
            </td>
        </tr>
        <tr>
            <th>Terakhir Diperbarui</th>
            <td>
                {{ $pengguna->updated_at?->format('d-m-Y H:i') ?? '-' }}
            </td>
        </tr>
    </table>

    <div class="form-actions">
        <a href="{{ route('ketua-rt.kelola-warga.edit', $pengguna->id) }}" class="btn btn-primary">
            Edit Data
        </a>
    </div>
</div>

@endsection
