@extends('layouts.app')

@section('title', 'Data Kategori')
@section('page-title', 'Data Kategori')

@section('styles')
    .category-equipment-list { margin:8px 0 2px; padding:9px 12px; border-radius:6px; background:#fff8ed; color:#5c4931; }
    .category-equipment-list ul { margin:0; padding-left:20px; }
    .category-equipment-list li { padding:3px 0; }
    .category-equipment-toggle { color:#7a4b0a; font-weight:700; cursor:pointer; }
@endsection

@section('content')
@if(session('user')->role == 'admin')
<div class="section">
    <h2>➕ Tambah Kategori</h2>
    <form action="{{ route('kategori.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" placeholder="Contoh: Tenda, Sleeping Bag, dll" required>
            </div>
        </div>
        <br>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endif

<div class="section">
    <h2>📋 Daftar Kategori</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Kategori</th>
                <th>Daftar Alat</th>
                @if(session('user')->role == 'admin')
                <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($kategori as $i => $k)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $k->nama_kategori }}</td>
                <td>
                    <details>
                        <summary class="category-equipment-toggle">
                            Lihat {{ $k->alat->count() }} jenis alat
                        </summary>
                        <div class="category-equipment-list">
                            @if($k->alat->isNotEmpty())
                                <ul>
                                    @foreach($k->alat as $alatItem)
                                        <li>
                                            {{ $alatItem->nama_alat }}
                                            @if($alatItem->ukuran)
                                                — Ukuran {{ $alatItem->ukuran }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                Belum ada alat di kategori ini.
                            @endif
                        </div>
                    </details>
                </td>
                @if(session('user')->role == 'admin')
                <td>
                    <button class="btn btn-warning" onclick="editKategori({{ $k->id }}, {{ Js::from($k->nama_kategori) }})">Edit</button>
                    <form action="{{ route('kategori.destroy', $k->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus kategori ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ session('user')->role == 'admin' ? 4 : 3 }}" style="text-align:center; color:#888;">Belum ada data kategori</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <h3>✏️ Edit Kategori</h3>
        <form id="formEdit" method="POST">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input type="text" name="nama_kategori" id="editNama" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function editKategori(id, nama) {
        document.getElementById('editNama').value = nama;
        document.getElementById('formEdit').action = '/kategori/' + id;
        document.getElementById('modalEdit').classList.add('active');
    }
    function closeModal() {
        document.getElementById('modalEdit').classList.remove('active');
    }
</script>
@endsection