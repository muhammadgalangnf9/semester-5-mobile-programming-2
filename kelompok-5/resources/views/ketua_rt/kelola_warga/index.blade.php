@extends('layouts.app')

@section('title', 'Kelola Warga')
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

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-active {
        background: #dcfce7;
        color: #166534;
    }

    .badge-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .actions {
        display: flex;
        align-items: center;
        gap: 6px;
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

    .btn-danger {
        background: #dc2626;
        color: #fff;
    }

    .btn-success {
        background: #16a34a;
        color: #fff;
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
        <h2>Kelola Warga</h2>
        <p>Kelola data dan akun warga lingkungan RT.</p>
    </div>

    <a href="{{ route('ketua-rt.kelola-warga.create') }}"
       class="btn btn-primary">
        + Tambah Warga
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Gang</th>
                    <th>Blok</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @if ($ketuaRT)
                    <tr>
                        <td>1</td>
                        <td>{{ $ketuaRT->nama }}</td>
                        <td>{{ $ketuaRT->username }}</td>
                        <td>{{ $ketuaRT->gang }}</td>
                        <td>{{ $ketuaRT->blok }}</td>
                        <td>{{ $ketuaRT->labelRole() }}</td>
                        <td>
                            <span class="badge {{ $ketuaRT->status === 'aktif' ? 'badge-active' : 'badge-inactive' }}">
                                {{ ucfirst($ketuaRT->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('ketua-rt.kelola-warga.show', $ketuaRT->id) }}"
                                   class="btn btn-secondary btn-small">
                                    Detail
                                </a>
                                <a href="{{ route('ketua-rt.kelola-warga.edit', $ketuaRT->id) }}"
                                   class="btn btn-primary btn-small">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @endif

                @forelse($warga as $item)
                    <tr>
                        <td>{{ $loop->iteration + $warga->firstItem() - 1 + ($ketuaRT ? 1 : 0) }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->username }}</td>
                        <td>{{ $item->gang }}</td>
                        <td>{{ $item->blok }}</td>
                        <td>{{ $item->labelRole() }}</td>

                        {{-- Status Akun --}}
                        <td>
                            <span class="badge {{ $item->status === 'aktif' ? 'badge-active' : 'badge-inactive' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td>
                            <div class="actions">

                                {{-- Detail --}}
                                <a href="{{ route('ketua-rt.kelola-warga.show', $item->id) }}"
                                   class="btn btn-secondary btn-small">
                                    Detail
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('ketua-rt.kelola-warga.edit', $item->id) }}"
                                   class="btn btn-primary btn-small">
                                    Edit
                                </a>

                                {{-- Aktifkan / Nonaktifkan --}}
                                <form action="{{ route('ketua-rt.kelola-warga.ubah-status', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin {{ $item->status === 'aktif' ? 'menonaktifkan' : 'mengaktifkan' }} akun {{ $item->nama }}?')">

                                    @csrf
                                    @method('PATCH')

                                    @if ($item->status === 'aktif')
                                        <button type="submit"
                                                class="btn btn-danger btn-small">
                                            Nonaktifkan
                                        </button>
                                    @else
                                        <button type="submit"
                                                class="btn btn-success btn-small">
                                            Aktifkan
                                        </button>
                                    @endif

                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 25px;">
                            {{ $ketuaRT ? 'Belum ada data warga.' : 'Data Ketua RT belum tersedia.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $warga->links() }}
    </div>
</div>

@endsection
