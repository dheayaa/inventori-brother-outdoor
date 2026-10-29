@extends('layouts.app')

@section('title', 'Data Alat')
@section('page-title', 'Data Alat')

@section('styles')
    .alat-category { border:1px solid #eee; border-radius:8px; margin-bottom:10px; overflow:hidden; }
    .alat-category:last-child { margin-bottom:0; }
    .alat-category summary { padding:14px 16px; background:#fff8f0; color:#2C1A00; cursor:pointer; font-weight:bold; }
    .alat-category summary:hover { background:#fff1db; }
    .alat-category-count { color:#777; font-size:12px; font-weight:normal; margin-left:6px; }
    .alat-category-content { padding:12px; overflow-x:auto; }
    .alat-category-content table { min-width:900px; }
@endsection

@section('content')
@if($errors->any())
<div class="alert alert-error">
    @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
</div>
@endif

@if(session('user')->role == 'admin')
<div class="section">
    <h2>➕ Tambah Alat</h2>
    <form action="{{ route('alat.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategori as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Nama Alat</label>
                <input type="text" name="nama_alat" placeholder="Contoh: Tenda Dome 4P" required>
            </div>
            <div class="form-group">
                <label>Ukuran (opsional)</label>
                <input type="text" name="ukuran" placeholder="Contoh: 38, 39, L, XL">
            </div>
            <div class="form-group">
                <label>Jenis Barang</label>
                <select name="jenis_alat" id="createJenisAlat" required>
                    <option value="sewa">Disewakan</option>
                    <option value="aksesoris">Aksesori (gratis jika sewa alat utama)</option>
                    <option value="jual">Dijual, tidak disewakan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Stok Total</label>
                <input type="number" name="stok_total" placeholder="Jumlah stok" required>
            </div>
            <div class="form-group" id="createHargaSewaGroup">
                <label>Harga Sewa (untuk satu masa booking)</label>
                <input type="number" name="harga_sewa" id="createHargaSewa" min="0" placeholder="Contoh: 50000">
                <small style="color:#666; margin-top:4px;">Harga tidak bertambah berdasarkan lama hari sewa. Keterlambatan dicatat terpisah sebagai denda.</small>
            </div>
            <div class="form-group">
                <label>Harga Jual (khusus barang dijual)</label>
                <input type="number" name="harga_jual" min="0" placeholder="Contoh: 15000">
            </div>
            <div class="form-group">
                <label>Kondisi</label>
                <select name="kondisi">
                    <option value="baik">Baik</option>
                    <option value="rusak">Rusak</option>
                    <option value="perbaikan">Perbaikan</option>
                </select>
            </div>
            <div class="form-group full">
                <label>Keterangan</label>
                <textarea name="keterangan" rows="2" placeholder="Keterangan (opsional)"></textarea>
            </div>
        </div>
        <br>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endif

<div class="section">
    <h2>📦 Daftar Alat</h2>
    @forelse($alat->groupBy('kategori_id') as $alatDalamKategori)
        @php($kategoriAlat = $alatDalamKategori->first()->kategori)
        <details class="alat-category">
            <summary>
                {{ $kategoriAlat->nama_kategori ?? 'Kategori tidak tersedia' }}
                <span class="alat-category-count">{{ $alatDalamKategori->count() }} jenis alat</span>
            </summary>
            <div class="alat-category-content">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Alat</th>
                            <th>Ukuran</th>
                            <th>Jenis Barang</th>
                            <th>Stok Total</th>
                            <th>Dalam Pencucian</th>
                            <th>Siap Disewa Hari Ini</th>
                            <th>Harga Sewa / Jual</th>
                            <th>Kondisi</th>
                            @if(session('user')->role == 'admin')
                            <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alatDalamKategori as $i => $a)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $a->nama_alat }}</td>
                            <td>{{ $a->ukuran ?: '-' }}</td>
                            <td>
                                @if($a->jenis_alat === 'jual')
                                    Dijual
                                @elseif($a->jenis_alat === 'aksesoris')
                                    Aksesori / dapat gratis
                                @else
                                    Disewakan
                                @endif
                            </td>
                            <td>{{ $a->stok_total }}</td>
                            <td>
                                {{ $a->stok_pencucian }}
                                @if(session('user')->role === 'admin' && $a->stok_pencucian > 0)
                                    <form action="{{ route('alat.pencucian', $a->id) }}" method="POST" style="display:flex; gap:5px; margin-top:6px; align-items:center;">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="jumlah" min="1" max="{{ $a->stok_pencucian }}" value="{{ $a->stok_pencucian }}" required aria-label="Jumlah {{ $a->nama_alat }} yang sudah bersih" style="width:75px; padding:5px;">
                                        <button type="submit" class="btn btn-success">Tandai Siap</button>
                                    </form>
                                @endif
                            </td>
                            <td>{{ $a->stok_tersedia }}</td>
                            <td>
                                @if($a->jenis_alat === 'jual')
                                    Rp {{ number_format($a->harga_jual ?? 0, 0, ',', '.') }} (jual)
                                @else
                                    Rp {{ number_format($a->harga_sewa, 0, ',', '.') }} (sewa)
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $a->kondisi }}">{{ ucfirst($a->kondisi) }}</span></td>
                            @if(session('user')->role == 'admin')
                            <td>
                                <button class="btn btn-warning" onclick="editAlat({{ $a->id }}, {{ $a->kategori_id }}, {{ Js::from($a->nama_alat) }}, {{ Js::from($a->ukuran) }}, {{ Js::from($a->jenis_alat ?? 'sewa') }}, {{ $a->stok_total }}, {{ $a->harga_sewa }}, {{ $a->harga_jual ?? 'null' }}, {{ Js::from($a->kondisi) }}, {{ Js::from($a->keterangan) }})">Edit</button>
                                <form action="{{ route('alat.destroy', $a->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus alat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @empty
        <p style="text-align:center; color:#888;">Belum ada data alat</p>
    @endforelse
</div>

<div class="modal-overlay" id="modalEdit">
    <div class="modal" style="width:550px">
        <h3>✏️ Edit Alat</h3>
        <form id="formEdit" method="POST">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori_id" id="editKategori" required>
                        @foreach($kategori as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama Alat</label>
                    <input type="text" name="nama_alat" id="editNama" required>
                </div>
                <div class="form-group">
                    <label>Ukuran (opsional)</label>
                    <input type="text" name="ukuran" id="editUkuran" placeholder="Contoh: 38, 39, L, XL">
                </div>
                <div class="form-group">
                    <label>Jenis Barang</label>
                    <select name="jenis_alat" id="editJenisAlat" required>
                        <option value="sewa">Disewakan</option>
                        <option value="aksesoris">Aksesori (gratis jika sewa alat utama)</option>
                        <option value="jual">Dijual, tidak disewakan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stok Total</label>
                    <input type="number" name="stok_total" id="editStokTotal" required>
                </div>
                <div class="form-group" id="editHargaSewaGroup">
                    <label>Harga Sewa</label>
                    <input type="number" name="harga_sewa" id="editHarga" min="0" required>
                </div>
                <div class="form-group">
                    <label>Harga Jual (khusus barang dijual)</label>
                    <input type="number" name="harga_jual" id="editHargaJual" min="0">
                </div>
                <div class="form-group">
                    <label>Kondisi</label>
                    <select name="kondisi" id="editKondisi">
                        <option value="baik">Baik</option>
                        <option value="rusak">Rusak</option>
                        <option value="perbaikan">Perbaikan</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Keterangan</label>
                    <textarea name="keterangan" id="editKeterangan" rows="2"></textarea>
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
    function updateRentalPriceField(typeSelect, priceGroup, priceInput) {
        const isForSale = typeSelect.value === 'jual';
        priceGroup.hidden = isForSale;
        priceInput.disabled = isForSale;
        priceInput.required = !isForSale;
    }

    const createJenisAlat = document.getElementById('createJenisAlat');
    const createHargaSewaGroup = document.getElementById('createHargaSewaGroup');
    const createHargaSewa = document.getElementById('createHargaSewa');
    createJenisAlat.addEventListener('change', () => {
        updateRentalPriceField(createJenisAlat, createHargaSewaGroup, createHargaSewa);
    });
    updateRentalPriceField(createJenisAlat, createHargaSewaGroup, createHargaSewa);
    document.querySelector('form[action="{{ route('alat.store') }}"]').addEventListener('submit', () => {
        updateRentalPriceField(createJenisAlat, createHargaSewaGroup, createHargaSewa);
    });

    const editJenisAlat = document.getElementById('editJenisAlat');
    const editHargaSewaGroup = document.getElementById('editHargaSewaGroup');
    const editHarga = document.getElementById('editHarga');
    editJenisAlat.addEventListener('change', () => {
        updateRentalPriceField(editJenisAlat, editHargaSewaGroup, editHarga);
    });
    document.getElementById('formEdit').addEventListener('submit', () => {
        updateRentalPriceField(editJenisAlat, editHargaSewaGroup, editHarga);
    });

    function editAlat(id, kategori_id, nama, ukuran, jenis_alat, stok_total, harga, harga_jual, kondisi, keterangan) {
        document.getElementById('editKategori').value = kategori_id;
        document.getElementById('editNama').value = nama;
        document.getElementById('editUkuran').value = ukuran || '';
        document.getElementById('editJenisAlat').value = jenis_alat;
        document.getElementById('editStokTotal').value = stok_total;
        document.getElementById('editHarga').value = harga;
        updateRentalPriceField(editJenisAlat, editHargaSewaGroup, editHarga);
        document.getElementById('editHargaJual').value = harga_jual ?? '';
        document.getElementById('editKondisi').value = kondisi;
        document.getElementById('editKeterangan').value = keterangan;
        document.getElementById('formEdit').action = '/alat/' + id;
        document.getElementById('modalEdit').classList.add('active');
    }
    function closeModal() {
        document.getElementById('modalEdit').classList.remove('active');
    }
</script>
@endsection