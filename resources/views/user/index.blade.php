@extends('layouts.app')

@section('title', 'Data User')
@section('page-title', 'Data User')

@section('content')
<div class="section">
    <h2>➕ Tambah User</h2>
    <form action="{{ route('user.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Nama lengkap" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Username untuk login" required>
            </div>
            <div class="form-group">
                <label>No. HP</label>
                <input type="tel" name="no_hp" placeholder="10–13 angka, contoh: 081234567890" inputmode="numeric" pattern="[0-9]{10,13}" minlength="10" maxlength="13">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min. 6 karakter" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="owner">Owner</option>
                </select>
            </div>
        </div>
        <br>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<div class="section">
    <h2>👤 Daftar User</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>No. HP</th>
                <th>Username</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $i => $u)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $u->nama }}</td>
                <td>{{ $u->no_hp ?: '-' }}</td>
                <td>{{ $u->username }}</td>
                <td><span class="badge badge-{{ $u->role }}">{{ ucfirst($u->role) }}</span></td>
                <td>
                    <button class="btn btn-warning" onclick="editUser({{ $u->id }}, {{ Js::from($u->nama) }}, {{ Js::from($u->username) }}, {{ Js::from($u->no_hp) }}, {{ Js::from($u->role) }})">Edit</button>
                    <form action="{{ route('user.destroy', $u->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus user ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#888;">Belum ada data user</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <h3>✏️ Edit User</h3>
        <form id="formEdit" method="POST">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="editNama" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="editUsername" disabled style="background:#eee;">
                </div>
                <div class="form-group">
                    <label>No. HP</label>
                    <input type="tel" name="no_hp" id="editNoHp" placeholder="10–13 angka" inputmode="numeric" pattern="[0-9]{10,13}" minlength="10" maxlength="13">
                </div>
                <div class="form-group">
                    <label>Password Baru (kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="editRole">
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                    </select>
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
    function editUser(id, nama, username, noHp, role) {
        document.getElementById('editNama').value = nama;
        document.getElementById('editUsername').value = username;
        document.getElementById('editNoHp').value = noHp || '';
        document.getElementById('editRole').value = role;
        document.getElementById('formEdit').action = '/user/' + id;
        document.getElementById('modalEdit').classList.add('active');
    }
    function closeModal() {
        document.getElementById('modalEdit').classList.remove('active');
    }
</script>
@endsection