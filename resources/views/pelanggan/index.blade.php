@extends('layouts.app')

@section('title', 'Data Pelanggan')
@section('page-title', 'Data Pelanggan')

@section('content')
<div class="section">
    <h2>➕ Tambah Pelanggan</h2>
    <form action="{{ route('pelanggan.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label>Nama Pelanggan</label>
                <input type="text" name="nama_pelanggan" placeholder="Nama lengkap" required>
            </div>
            <div class="form-group">
                <label>No HP</label>
                <input type="tel" name="no_hp" placeholder="10–13 angka, contoh: 08123456789" inputmode="numeric" pattern="[0-9]{10,13}" minlength="10" maxlength="13" required>
            </div>
            <div class="form-group">
                <label>NIK</label>
                <input type="text" name="nik" placeholder="16 digit NIK KTP" inputmode="numeric" pattern="[0-9]{16}" minlength="16" maxlength="16" required>
            </div>
            <div class="form-group full">
                <label>Alamat</label>
                <textarea name="alamat" rows="2" placeholder="Alamat lengkap (opsional)"></textarea>
            </div>
        </div>
        <br>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<div class="section">
    <h2>👥 Daftar Pelanggan</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Pelanggan</th>
                <th>No HP</th>
                <th>NIK</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelanggan as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->nama_pelanggan }}</td>
                <td>{{ $p->no_hp }}</td>
                <td>{{ $p->nik }}</td>
                <td>{{ $p->alamat ?? '-' }}</td>
                <td>
                    <button class="btn btn-warning" onclick="editPelanggan({{ $p->id }}, '{{ $p->nama_pelanggan }}', '{{ $p->no_hp }}', '{{ $p->nik }}', '{{ $p->alamat }}')">Edit</button>
                    <form action="{{ route('pelanggan.destroy', $p->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus pelanggan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#888;">Belum ada data pelanggan</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <h3>✏️ Edit Pelanggan</h3>
        <form id="formEdit" method="POST">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" id="editNama" required>
                </div>
                <div class="form-group">
                    <label>No HP</label>
                    <input type="tel" name="no_hp" id="editNoHp" placeholder="10–13 angka" inputmode="numeric" pattern="[0-9]{10,13}" minlength="10" maxlength="13" required>
                </div>
                <div class="form-group">
                    <label>NIK</label>
                    <input type="text" name="nik" id="editNik" placeholder="16 digit NIK KTP" inputmode="numeric" pattern="[0-9]{16}" minlength="16" maxlength="16" required>
                </div>
                <div class="form-group full">
                    <label>Alamat</label>
                    <textarea name="alamat" id="editAlamat" rows="2"></textarea>
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
    function editPelanggan(id, nama, no_hp, nik, alamat) {
        document.getElementById('editNama').value = nama;
        document.getElementById('editNoHp').value = no_hp;
        document.getElementById('editNik').value = nik;
        document.getElementById('editAlamat').value = alamat;
        document.getElementById('formEdit').action = '/pelanggan/' + id;
        document.getElementById('modalEdit').classList.add('active');
    }
    function closeModal() {
        document.getElementById('modalEdit').classList.remove('active');
    }
</script>
@endsection