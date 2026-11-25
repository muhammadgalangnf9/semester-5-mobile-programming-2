@extends('layouts.app')

@section('title', 'Pengumuman - SIPEKAT')
@section('header', 'Pengumuman')

@section('content')

@php
    $pengguna = Auth::user();

    $bolehKelola = (int) $pengguna->role_id === 2;

    $labelStatus = [
        'draft' => 'Draft',
        'menunggu_persetujuan' => 'Menunggu Persetujuan',
        'terbit' => 'Disetujui',
        'ditolak' => 'Ditolak',
        'nonaktif' => 'Nonaktif',
    ];
@endphp

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

    .alert-success {
        margin-bottom: 16px;
        padding: 12px 16px;
        border-radius: 6px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        font-size: 0.875rem;
        color: #166534;
    }

    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .card-title {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        color: #111827;
        font-size: 0.95rem;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    thead th {
        text-align: left;
        padding: 12px 16px;
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        white-space: nowrap;
        border-bottom: 1px solid #e5e7eb;
    }

    tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    tbody tr:hover {
        background: #f9fafb;
    }

    .td-name {
        font-weight: 600;
        color: #111827;
    }

    .td-center {
        text-align: center;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-draft {
        background: #fef9c3;
        color: #854d0e;
    }

    .badge-terbit {
        background: #dcfce7;
        color: #166534;
    }

    .badge-nonaktif {
        background: #f3f4f6;
        color: #4b5563;
    }

    .badge-menunggu_persetujuan {
        background: #ffedd5;
        color: #9a3412;
    }

    .badge-ditolak {
        background: #fee2e2;
        color: #991b1b;
    }

    .actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .actions select {
        padding: 6px 8px;
        font-size: 0.8rem;
        color: #374151;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        outline: none;
        cursor: pointer;
    }

    .actions select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .btn {
        display: inline-block;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.8rem;
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
        padding: 8px 16px;
        font-size: 0.85rem;
    }

    .btn-secondary {
        background: #e5e7eb;
        color: #111827;
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    /* Tombol Edit dibuat solid biru seperti Kelola Warga */
    .btn-edit {
        background: #2563eb;
        color: #fff;
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    .pagination {
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
    }
</style>

<div class="page-header">
    <div>
        <h2>Daftar Pengumuman</h2>
        <p>Informasi dan pengumuman untuk warga.</p>
    </div>

    @if ($bolehKelola)
        <a href="{{ route('pengumuman.create') }}" class="btn btn-primary">
            + Tambah Pengumuman
        </a>
    @endif
</div>

@if (session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card">

    <div class="card-title">
        Data Pengumuman
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Judul Pengumuman</th>
                    <th>Pembuat</th>
                    <th>Status</th>
                    <th class="td-center">Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($pengumuman as $item)
                    <tr>
                        <td>
                            {{ $pengumuman->firstItem() + $loop->index }}
                        </td>

                        <td class="td-name">
                            {{ $item->judul }}
                        </td>

                        <td>
                            {{ $item->pengguna?->nama ?? '-' }}
                        </td>

                        <td>
                            <span class="badge badge-{{ $item->status }}">
                                {{ $labelStatus[$item->status] ?? $item->status }}
                            </span>
                            @if (in_array($item->status, ['draft', 'menunggu_persetujuan', 'terbit'], true))
                                <span class="badge badge-terbit">Aktif</span>
                            @endif
                        </td>

                        <td>
                            <div class="actions">

                                <a href="{{ route('pengumuman.show', $item->id) }}"
                                   class="btn btn-secondary">
                                    Detail
                                </a>

                                @if ($bolehKelola)
                                    <a href="{{ route('pengumuman.edit', $item->id) }}"
                                       class="btn btn-edit">
                                        Edit
                                    </a>

                                    @if (in_array($item->status, ['draft', 'ditolak'], true))
                                        <form action="{{ route('pengumuman.ajukan', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">
                                                Ajukan Persetujuan
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('pengumuman.ubah-status', $item->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin mengubah status pengumuman ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status"
                                               value="{{ $item->status === 'nonaktif' ? 'draft' : 'nonaktif' }}">
                                        <button type="submit" class="btn btn-secondary">
                                            {{ $item->status === 'nonaktif' ? 'Aktifkan sebagai Draft' : 'Nonaktifkan' }}
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:25px;">
                            Belum ada data pengumuman.
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    @if ($pengumuman->hasPages())
        <div class="pagination">
            {{ $pengumuman->links() }}
        </div>
    @endif

</div>

@endsection