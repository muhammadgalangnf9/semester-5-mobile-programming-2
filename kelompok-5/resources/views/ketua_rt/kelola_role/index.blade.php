@extends('layouts.app')

@section('title', 'Kelola Role - SIPEKAT')
@section('header', 'Kelola Role')

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

    .alert {
        margin-bottom: 16px;
        padding: 12px 16px;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
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
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
    }

    tbody td {
        padding: 12px;
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

    .actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
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

    .btn-small {
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    .pagination {
        margin-top: 20px;
    }
</style>

<div class="page-header">
    <div>
        <h2>Data Master Role</h2>
        <p>Kelola informasi dan status hak akses berdasarkan role pengguna.</p>
    </div>
</div>

<div class="card">

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Role</th>
                    <th>Deskripsi</th>
                    <th class="td-center">Jumlah Pengguna</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($roles as $role)
                    <tr>

                        <td>
                            {{ $roles->firstItem() + $loop->index }}
                        </td>

                        <td class="td-name">
                            {{ $role->nama_role }}
                        </td>

                        <td>
                            {{ $role->deskripsi ?? '-' }}
                        </td>

                        <td class="td-center">
                            {{ $role->pengguna_count }}
                        </td>

                        <td>
                            <div class="actions">

                                <a href="{{ route('ketua-rt.kelola-role.show', $role->id) }}"
                                   class="btn btn-secondary btn-small">
                                    Detail
                                </a>

                                <a href="{{ route('ketua-rt.kelola-role.edit', $role->id) }}"
                                   class="btn btn-primary btn-small">
                                    Edit
                                </a>

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:25px;">
                            Belum ada data role.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="pagination">
        {{ $roles->links() }}
    </div>

</div>

@endsection