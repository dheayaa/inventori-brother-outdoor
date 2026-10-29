@extends('layouts.app')

@section('title', 'Pengembalian')
@section('page-title', 'Pengembalian Alat')

@section('content')
@if($rentalPerluPerhatian->isNotEmpty())
<div role="alert" style="background:#f8d7da; border:1px solid #f1aeb5; border-left:5px solid #dc3545; color:#842029; border-radius:8px; padding:15px 18px; margin-bottom:20px;">
    <strong>Perlu perhatian: {{ $rentalPerluPerhatian->count() }} rental mendekati atau melewati batas waktu pengembalian.</strong>
    <ul style="margin:10px 0 0 20px; line-height:1.7;">
        @foreach($rentalPerluPerhatian as $item)
            <li>
                <strong>{{ $item->pelanggan->nama_pelanggan }}</strong> — {{ $item->kode_rental }},
                @if($item->batas_waktu_kembali->isFuture())
                    batas pengembalian {{ $item->batas_waktu_kembali->locale('id')->translatedFormat('l, j F Y') }}
                    pukul {{ $item->batas_waktu_kembali->format('H.i') }}
                @else
                    terlambat sejak {{ $item->batas_waktu_kembali->locale('id')->translatedFormat('l, j F Y') }}
                    pukul {{ $item->batas_waktu_kembali->format('H.i') }}
                @endif
            </li>
        @endforeach
    </ul>
</div>
@endif

<div class="section">
    <h2>✅ Daftar Rental & Pengembalian</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Pelanggan</th>
                <th>Kode Rental</th>
                <th>Tgl Rental</th>
                <th>Tgl Jatuh Tempo</th>
                <th>Tgl Dikembalikan</th>
                <th>Total Bayar</th>
                <th>Identitas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rental as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->pelanggan->nama_pelanggan }}</td>
                <td>{{ $r->kode_rental }}</td>
                <td>{{ \Illuminate\Support\Carbon::parse($r->tgl_rental)->locale('id')->translatedFormat('j F Y') }}</td>
                <td>{{ \Illuminate\Support\Carbon::parse($r->tgl_kembali)->locale('id')->translatedFormat('j F Y') }}</td>
                <td>
                    {{ $r->tgl_kembali_aktual
                        ? \Illuminate\Support\Carbon::parse($r->tgl_kembali_aktual)->locale('id')->translatedFormat('j F Y')
                        : '-' }}
                </td>
                <td>Rp {{ number_format($r->total_bayar, 0, ',', '.') }}</td>
                <td>
                    @if(!$r->identitas_dititipkan)
                        Tidak dititipkan
                    @elseif($r->identitas_dikembalikan)
                        <strong style="color:#198754;">Sudah dikembalikan</strong>
                    @else
                        <strong style="color:#b45309;">Masih dititipkan</strong>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $r->effective_status }}">{{ ucfirst($r->effective_status) }}</span>
                </td>
                <td>
                    @if($r->tgl_kembali_aktual)
                        @if($r->identitas_dititipkan)
                            <button class="btn btn-warning" onclick="editIdentitas({{ $r->id }}, {{ $r->identitas_dikembalikan ? 'true' : 'false' }})">Edit Identitas</button>
                        @else
                            <span>Tidak perlu tindakan</span>
                        @endif
                    @else
                        <button class="btn btn-primary" onclick="prosesKembali({{ $r->id }}, {{ $r->identitas_dititipkan ? 'true' : 'false' }})">Proses</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center; color:#888;">Tidak ada data rental atau pengembalian</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modalEditIdentitas">
    <div class="modal">
        <h3>✏️ Edit Status Identitas</h3>
        <form id="formEditIdentitas" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="statusIdentitas">Status kartu identitas</label>
                <select name="identitas_dikembalikan" id="statusIdentitas" required>
                    <option value="0">Masih dititipkan</option>
                    <option value="1">Sudah dikembalikan ke pelanggan</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeEditIdentitas()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalProses">
    <div class="modal">
        <h3>✅ Proses Pengembalian</h3>
        <form id="formProses" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom:15px">
                <label>Tanggal Kembali Aktual (tanggal pelanggan datang)</label>
                <input type="date" name="tgl_kembali_aktual" id="tglKembaliAktual" required>
                <small style="color:#777; margin-top:4px;">Tanggal ini akan dicatat sebagai tanggal alat benar-benar dikembalikan, termasuk jika pelanggan datang terlambat.</small>
            </div>
            <div class="form-group" style="margin-bottom:15px">
                <label>Nominal Denda (opsional)</label>
                <input type="number" name="denda" id="dendaManual" min="0" step="1000" placeholder="Masukkan nominal denda jika ada">
            </div>
            <div class="form-group" id="identitasKembaliGroup" style="display:none; margin-bottom:15px">
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="identitas_dikembalikan" id="identitasDikembalikan" value="1" style="width:auto;">
                    Kartu identitas sudah dikembalikan ke pelanggan
                </label>
            </div>
            <p style="font-size:12px; color:#888; line-height:1.6;">
                Denda diisi manual oleh admin sesuai keputusan. Tidak ada patokan tetap per hari.<br>
                Barang yang dikembalikan otomatis masuk pencucian dan baru tersedia disewa setelah ditandai siap di halaman Data Alat.<br>
                Jika tidak ada denda, biarkan kosong atau isi 0. Jika identitas belum dikembalikan, biarkan ceklis kosong agar tercatat masih dititipkan.
            </p>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Proses Pengembalian</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function prosesKembali(id, identitasDititipkan) {
        document.getElementById('tglKembaliAktual').value = '{{ now()->toDateString() }}';
        document.getElementById('formProses').action = '/pengembalian/' + id;
        document.getElementById('identitasKembaliGroup').style.display = identitasDititipkan ? 'block' : 'none';
        document.getElementById('identitasDikembalikan').checked = false;
        document.getElementById('modalProses').classList.add('active');
    }
    function closeModal() {
        document.getElementById('modalProses').classList.remove('active');
    }
    function editIdentitas(id, sudahDikembalikan) {
        document.getElementById('formEditIdentitas').action = '/pengembalian/' + id + '/identitas';
        document.getElementById('statusIdentitas').value = sudahDikembalikan ? '1' : '0';
        document.getElementById('modalEditIdentitas').classList.add('active');
    }
    function closeEditIdentitas() {
        document.getElementById('modalEditIdentitas').classList.remove('active');
    }
</script>
@endsection