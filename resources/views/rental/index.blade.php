@extends('layouts.app')

@section('title', 'Data Rental')
@section('page-title', 'Data Rental')

@section('styles')
    .rental-mode-options { display:flex; flex-wrap:wrap; gap:12px; margin-top:8px; }
    .rental-mode-option { display:flex; align-items:flex-start; gap:9px; padding:12px 14px; border:1px solid #e8dfd3; border-radius:9px; background:#fff; cursor:pointer; flex:1; min-width:220px; }
    .rental-mode-option:has(input:checked) { border-color:#F5A623; background:#fff8ed; box-shadow:0 0 0 2px rgba(245,166,35,.12); }
    .rental-mode-option input { width:auto; margin-top:3px; accent-color:#b76e00; }
    .rental-mode-option small { display:block; color:#777; margin-top:3px; line-height:1.4; }
    .alat-picker { margin-top:12px; }
    .alat-picker-tools { display:grid; grid-template-columns:minmax(220px, 1fr) minmax(180px, .45fr); gap:10px; margin-bottom:12px; }
    .alat-search-wrap { position:relative; }
    .alat-search-wrap input { padding-left:36px; }
    .alat-search-icon { position:absolute; left:12px; top:9px; color:#888; }
    .alat-picker-summary { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:11px 14px; margin-bottom:12px; border-radius:8px; background:#fff8ed; color:#5c3a0a; font-size:13px; }
    .alat-picker-summary strong { color:#2C1A00; }
    .alat-picker-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:12px; max-height:480px; overflow-y:auto; padding:2px; }
    .alat-item { position:relative; display:flex; flex-direction:column; align-items:stretch; gap:12px; min-width:0; padding:14px; border:1px solid #e9e5df; border-radius:10px; background:#fff; transition:border-color .15s, box-shadow .15s, background .15s; }
    .alat-item:hover { border-color:#edbd70; box-shadow:0 3px 12px rgba(44,26,0,.08); }
    .alat-item.is-selected { border-color:#d99119; background:#fffaf1; }
    .alat-item.is-unavailable { background:#f7f7f7; }
    .alat-item-heading { display:flex; justify-content:space-between; align-items:flex-start; gap:8px; }
    .alat-item-name { color:#2C1A00; font-size:14px; font-weight:700; line-height:1.4; overflow-wrap:anywhere; }
    .alat-size-badge { display:inline-block; margin-top:7px; padding:3px 8px; border-radius:20px; background:#f1ede7; color:#5f4931; font-size:11px; font-weight:600; }
    .alat-price { color:#8a4b08; font-weight:700; font-size:14px; }
    .alat-price small { display:block; color:#888; font-weight:400; font-size:11px; margin-top:2px; }
    .alat-availability { display:flex; align-items:center; gap:6px; color:#287044; font-size:12px; }
    .alat-item.is-unavailable .alat-availability { color:#9d3434; }
    .alat-quantity { display:flex; align-items:center; justify-content:space-between; gap:10px; padding-top:10px; border-top:1px solid #eee9e2; }
    .alat-quantity label { margin:0; font-size:12px; color:#666; }
    .alat-quantity input { max-width:90px; text-align:center; font-weight:700; }
    .alat-empty { display:none; padding:24px; text-align:center; color:#777; border:1px dashed #ddd; border-radius:9px; }
    .rental-help { padding:11px 13px; border-left:3px solid #F5A623; border-radius:5px; background:#fffaf1; color:#6d604e; font-size:12px; line-height:1.55; }
    .customer-search { position:relative; }
    .customer-suggestions { display:none; position:absolute; top:100%; left:0; right:0; z-index:20; max-height:240px; overflow-y:auto; background:#fff; border:1px solid #ddd; border-radius:6px; box-shadow:0 5px 14px rgba(0,0,0,.12); }
    .customer-suggestions.active { display:block; }
    .customer-suggestion { display:block; width:100%; padding:10px 12px; border:0; border-bottom:1px solid #eee; background:#fff; color:#333; text-align:left; cursor:pointer; font-size:13px; }
    .customer-suggestion:last-child { border-bottom:0; }
    .customer-suggestion:hover, .customer-suggestion:focus { background:#fff3df; outline:none; }
    .customer-suggestion small { display:block; color:#777; margin-top:3px; }
    @media (max-width:700px) {
        .alat-picker-tools { grid-template-columns:1fr; }
        .alat-picker-grid { grid-template-columns:1fr; max-height:560px; }
        .alat-picker-summary { align-items:flex-start; flex-direction:column; }
    }
@endsection

@section('content')
@if(session('user')->role == 'admin')
<div class="section">
    <h2>➕ Tambah Rental</h2>
    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    <form action="{{ route('rental.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-group full">
                <label>Jenis Transaksi</label>
                <div class="rental-mode-options">
                    <label class="rental-mode-option">
                        <input type="radio" name="jenis_transaksi" value="langsung" @checked(old('jenis_transaksi', 'langsung') === 'langsung')>
                        <span><strong>Rental Langsung</strong><br><small>Barang diambil hari ini.</small></span>
                    </label>
                    <label class="rental-mode-option">
                        <input type="radio" name="jenis_transaksi" value="booking" @checked(old('jenis_transaksi') === 'booking')>
                        <span><strong>Booking</strong><br><small>Termasuk pesanan WhatsApp yang diambil nanti hari ini.</small></span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label for="customerSearch">Pelanggan</label>
                <div class="customer-search">
                    <input type="text" id="customerSearch" placeholder="Ketik nama atau nomor HP pelanggan..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="customerSuggestions" required>
                    <input type="hidden" name="pelanggan_id" id="customerId">
                    <div id="customerSuggestions" class="customer-suggestions" role="listbox"></div>
                </div>
                <small id="customerSelectionMessage" style="color:#777; margin-top:5px;">Jika nama sama, cocokkan nomor HP atau 4 digit akhir NIK. Untuk pelanggan lama yang sewa lagi, pilih data pelanggan yang sama.</small>
            </div>
            <div class="form-group">
                <label id="rentalDateLabel" for="tglRental">Tanggal Rental</label>
                <input type="date" name="tgl_rental" id="tglRental" value="{{ old('tgl_rental', $today) }}" min="{{ $today }}" @readonly(old('jenis_transaksi', 'langsung') === 'langsung') required>
            </div>
            <div class="form-group">
                <label>Tanggal Kembali</label>
                <input type="date" name="tgl_kembali" id="tglKembali" value="{{ old('tgl_kembali', $tomorrow) }}" min="{{ $tomorrow }}" required>
            </div>
            <div class="form-group full">
                <label>Keterangan</label>
                <textarea name="keterangan" rows="2" placeholder="Keterangan (opsional)"></textarea>
            </div>
            <div class="form-group full">
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="identitas_dititipkan" value="1" style="width:auto;">
                    Kartu identitas sudah dititipkan
                </label>
                <small style="color:#777;">Untuk booking lewat WhatsApp, biarkan tidak dicentang jika pelanggan belum datang. Status bisa diperbarui saat pelanggan menyerahkan identitas.</small>
            </div>
            <div class="form-group full">
                <label>Pilih Alat & Jumlah</label>
                <p id="rentalModeMessage" class="rental-help">Rental langsung memakai tanggal hari ini. Untuk tanggal mendatang, pilih Booking. Stok dihitung sesuai tanggal rental sampai tanggal kembali. Harga berlaku untuk satu masa sewa dan tidak dikalikan jumlah hari; keterlambatan dapat dicatat sebagai denda saat pengembalian.</p>
                <div id="availabilityMessage" role="status" aria-live="polite" style="padding:10px 12px; border-radius:6px; background:#f5f5f5; color:#555; margin:10px 0;">
                    Pilih jenis transaksi dan tanggal rental untuk melihat sisa stok pada rentang tanggal tersebut.
                </div>
                <div class="alat-picker">
                    <div class="alat-picker-tools">
                        <div class="alat-search-wrap">
                            <span class="alat-search-icon" aria-hidden="true">⌕</span>
                            <input type="search" id="alatSearch" placeholder="Cari nama alat atau ukuran..." aria-label="Cari nama alat atau ukuran">
                        </div>
                        <select id="kategoriFilter" aria-label="Filter kategori alat">
                            <option value="">Semua kategori</option>
                            @foreach($kategoriAlat as $kategoriItem)
                                <option value="{{ $kategoriItem->id }}">{{ $kategoriItem->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alat-picker-summary">
                        <span><strong id="alatVisibleCount">{{ $alat->count() }}</strong> alat ditampilkan dari {{ $alat->count() }}</span>
                        <span>Dipilih: <strong id="alatSelectedCount">0 unit</strong> · Total transaksi: <strong id="alatSelectedTotal">Rp 0</strong></span>
                    </div>
                    <div class="alat-picker-grid" id="alatPickerGrid">
                    @forelse($alat as $a)
                    <div class="alat-item {{ $a->tersedia_periode < 1 ? 'is-unavailable' : '' }}" data-alat-id="{{ $a->id }}" data-category="{{ $a->kategori_id }}" data-item-type="{{ $a->jenis_alat ?? 'sewa' }}" data-search="{{ strtolower($a->nama_alat.' '.$a->ukuran.' '.($a->kategori->nama_kategori ?? '')) }}" data-rental-price="{{ $a->harga_sewa }}" data-sale-price="{{ $a->harga_jual ?? 0 }}">
                        <div class="alat-item-heading">
                            <div>
                                <div class="alat-item-name">{{ $a->nama_alat }}</div>
                                @if($a->ukuran)
                                    <span class="alat-size-badge">Ukuran {{ $a->ukuran }}</span>
                                @else
                                    <span class="alat-size-badge">Ukuran belum diisi</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            @if($a->jenis_alat === 'jual')
                                <div class="alat-price">Rp {{ number_format($a->harga_jual ?? 0, 0, ',', '.') }}<small>harga jual · tidak disewakan</small></div>
                            @elseif($a->jenis_alat === 'aksesoris')
                                <div class="alat-price">Rp {{ number_format($a->harga_sewa, 0, ',', '.') }}<small>sewa sendiri · gratis jika sewa alat utama</small></div>
                            @else
                                <div class="alat-price">Rp {{ number_format($a->harga_sewa, 0, ',', '.') }}<small>per masa sewa</small></div>
                            @endif
                            <div class="alat-availability"><span aria-hidden="true">●</span><strong class="stok-periode">{{ $a->tersedia_periode }}</strong> unit tersedia untuk seluruh rentang tanggal pilihan</div>
                            <small class="harga-item-note" style="display:block; margin-top:5px; color:#287044;"></small>
                        </div>
                        <div class="alat-quantity">
                            <label for="alat-{{ $a->id }}">{{ $a->jenis_alat === 'jual' ? 'Jumlah yang dibeli' : 'Jumlah yang disewa' }}</label>
                            <input type="number" id="alat-{{ $a->id }}" name="alat[{{ $a->id }}]" min="0" max="{{ $a->tersedia_periode }}" value="{{ old('alat.'.$a->id, 0) }}" aria-label="Jumlah {{ $a->nama_alat }} ukuran {{ $a->ukuran ?: 'belum diisi' }}">
                        </div>
                    </div>
                    @empty
                    <p style="color:#888; font-size:13px;">Belum ada data alat.</p>
                    @endforelse
                    </div>
                    <div class="alat-empty" id="alatEmptyState">Tidak ada alat yang sesuai dengan pencarian atau kategori.</div>
                </div>
            </div>
        </div>
        <br>
        <button type="submit" id="rentalSubmitButton" class="btn btn-primary">Simpan Rental Langsung</button>
    </form>
</div>
@endif

@if(in_array(session('user')->role, ['admin', 'owner']))
<div class="section">
    <h2>📅 Booking Rental Aktif & Mendatang</h2>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Kode Rental</th>
                    <th>Jenis</th>
                    <th>Tanggal Booking</th>
                    <th>Rincian Alat yang Dipesan</th>
                    <th>Total Bayar</th>
                    <th>Status</th>
                    @if(session('user')->role === 'admin')
                    <th>Ketersediaan</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($bookingMendatang as $booking)
                <tr>
                    <td>{{ $booking->pelanggan->nama_pelanggan ?? '-' }}</td>
                    <td>{{ $booking->kode_rental }}</td>
                    <td>Booking</td>
                    <td>{{ $booking->tgl_rental }} s/d {{ $booking->tgl_kembali }}</td>
                    <td>
                        <details>
                            <summary style="cursor:pointer; color:#8a4b08;">Lihat {{ $booking->detailRental->count() }} jenis alat</summary>
                            <ul style="padding:10px 0 0 20px;">
                                @foreach($booking->detailRental as $detail)
                                    <li>
                                        @if($detail->jenis_item === 'jual')
                                            Dibeli:
                                        @elseif($detail->jenis_item === 'gratis')
                                            Gratis:
                                        @endif
                                        {{ $detail->alat->nama_alat ?? 'Alat tidak tersedia' }} —
                                        Ukuran {{ $detail->ukuran ?: ($detail->alat->ukuran ?? 'belum diisi') }} —
                                        {{ $detail->jumlah }} unit
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    </td>
                    <td>Rp {{ number_format($booking->total_bayar, 0, ',', '.') }}</td>
                    <td>
                        @if($booking->tgl_rental > $today)
                            Booking mendatang
                        @elseif($booking->tgl_rental === $today)
                            Booking hari ini
                        @else
                            {{ ucfirst($booking->effective_status) }}
                        @endif
                    </td>
                    @if(session('user')->role === 'admin')
                    <td>
                        <button type="button" class="btn btn-info cek-stok-booking"
                            data-start="{{ $booking->tgl_rental }}"
                            data-end="{{ $booking->tgl_kembali }}">
                            Cek sisa alat
                        </button>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="{{ session('user')->role === 'admin' ? 7 : 6 }}" style="text-align:center; color:#888;">Belum ada booking aktif atau mendatang</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@if(session('user')->role === 'admin')
<div class="section" id="cekStokBooking">
    <h2>🔎 Cek Sisa Alat untuk Tanggal Booking</h2>
    <p style="color:#666; font-size:13px; margin:-5px 0 15px;">
        Pilih rentang tanggal untuk melihat semua alat yang masih dapat disewa setelah booking yang waktunya bertabrakan dikurangi.
        Bisa juga klik <strong>Cek sisa alat</strong> pada booking di atas. Alat dari rental yang belum diproses kembali atau masih dalam pencucian belum dihitung siap untuk tanggal berikutnya.
    </p>
    <div class="form-grid" style="align-items:end; max-width:700px;">
        <div class="form-group">
            <label for="cekStokMulai">Tanggal mulai</label>
            <input type="date" id="cekStokMulai" min="{{ $today }}" value="{{ $tomorrow }}">
        </div>
        <div class="form-group">
            <label for="cekStokSampai">Tanggal kembali</label>
            <input type="date" id="cekStokSampai" min="{{ now()->addDays(2)->toDateString() }}" value="{{ now()->addDays(2)->toDateString() }}">
        </div>
        <div class="form-group full">
            <button type="button" id="cekStokButton" class="btn btn-primary">Lihat Ketersediaan</button>
        </div>
    </div>
    <div id="cekStokPesan" role="status" aria-live="polite" style="margin:12px 0; color:#666;"></div>
    <div style="overflow-x:auto;">
        <table id="cekStokTabel" hidden>
            <thead>
                <tr>
                    <th>Nama Alat</th>
                    <th>Ukuran</th>
                    <th>Stok Total</th>
                    <th>Dalam Pencucian</th>
                    <th>Tersedia pada tanggal pilihan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="cekStokHasil"></tbody>
        </table>
    </div>
</div>
@endif

<div class="section">
    <h2>🤝 Daftar Rental</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Pelanggan</th>
                <th>Kode Rental</th>
                <th>Jenis</th>
                <th>Rincian Barang Disewa</th>
                <th>Tgl Rental</th>
                <th>Tgl Kembali</th>
                <th>Total Bayar</th>
                <th>Identitas</th>
                <th>Status</th>
                @if(session('user')->role == 'admin')
                <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($rental as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->pelanggan->nama_pelanggan ?? '-' }}</td>
                <td>{{ $r->kode_rental }}</td>
                <td>{{ $r->jenis_transaksi === 'booking' ? 'Booking' : 'Rental Langsung' }}</td>
                <td>
                    <details>
                        <summary style="cursor:pointer; color:#8a4b08; font-weight:600;">
                            Lihat {{ $r->detailRental->count() }} jenis barang
                        </summary>
                        @if($r->detailRental->isNotEmpty())
                            <div style="overflow-x:auto;">
                                <table style="margin-top:10px; min-width:480px;">
                                    <thead>
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Jumlah</th>
                                            <th>Harga Sewa / Jual</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($r->detailRental as $detail)
                                        <tr>
                                            <td>
                                                @if($detail->jenis_item === 'jual')
                                                    Dibeli:
                                                @elseif($detail->jenis_item === 'gratis')
                                                    Gratis:
                                                @endif
                                                {{ $detail->alat->nama_alat ?? 'Alat tidak tersedia' }} —
                                                Ukuran {{ $detail->ukuran ?: ($detail->alat->ukuran ?? 'belum diisi') }}
                                            </td>
                                            <td>{{ $detail->jumlah }} unit</td>
                                            <td>Rp {{ number_format($detail->harga_sewa, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p style="margin-top:10px; color:#888;">Belum ada rincian barang untuk rental ini.</p>
                        @endif
                        <p style="margin-top:10px; text-align:right;">
                            Total transaksi (sewa + penjualan): <strong>Rp {{ number_format($r->total_bayar, 0, ',', '.') }}</strong>
                        </p>
                    </details>
                </td>
                <td>{{ $r->tgl_rental }}</td>
                <td>{{ $r->tgl_kembali }}</td>
                <td>Rp {{ number_format($r->total_bayar, 0, ',', '.') }}</td>
                <td>
                    @if(!$r->identitas_dititipkan)
                        {{ $r->jenis_transaksi === 'booking' ? 'Belum dititipkan' : 'Tidak dititipkan' }}
                    @elseif($r->identitas_dikembalikan)
                        Sudah dikembalikan
                    @else
                        Masih dititipkan
                    @endif
                    @if(session('user')->role === 'admin' && $r->jenis_transaksi === 'booking' && $r->effective_status !== 'dikembalikan')
                        <form action="{{ route('rental.identitas.update', $r->id) }}" method="POST" style="margin-top:7px;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="identitas_dititipkan" value="{{ $r->identitas_dititipkan ? 0 : 1 }}">
                            <button type="submit" class="btn {{ $r->identitas_dititipkan ? 'btn-warning' : 'btn-success' }}">
                                {{ $r->identitas_dititipkan ? 'Ubah: belum dititipkan' : 'Tandai sudah dititipkan' }}
                            </button>
                        </form>
                    @endif
                </td>
                <td>
                    @php($hanyaPenjualan = $r->detailRental->isNotEmpty() && $r->detailRental->every(fn ($detail) => $detail->jenis_item === 'jual'))
                    <span class="badge badge-{{ $hanyaPenjualan ? 'dikembalikan' : $r->effective_status }}">
                        @if($hanyaPenjualan)
                            Penjualan selesai
                        @elseif($r->jenis_transaksi === 'booking' && $r->effective_status !== 'dikembalikan')
                            {{ $r->tgl_rental > $today ? 'Booking mendatang' : ($r->tgl_rental === $today ? 'Booking hari ini' : ucfirst($r->effective_status)) }}
                        @else
                            {{ ucfirst($r->effective_status) }}
                        @endif
                    </span>
                </td>
                @if(session('user')->role == 'admin')
                <td>
                    @if(in_array($r->effective_status, ['dipinjam', 'terlambat'], true))
                    <form action="{{ route('rental.destroy', $r->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus rental ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ session('user')->role == 'admin' ? 11 : 10 }}" style="text-align:center; color:#888;">Belum ada data rental</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
@if(session('user')->role == 'admin')
<script>
    const rentalDate = document.getElementById('tglRental');
    const returnDate = document.getElementById('tglKembali');
    const availabilityMessage = document.getElementById('availabilityMessage');
    const rentalForm = document.querySelector('form[action="{{ route('rental.store') }}"]');
    const rentalDateLabel = document.getElementById('rentalDateLabel');
    const rentalModeMessage = document.getElementById('rentalModeMessage');
    const rentalSubmitButton = document.getElementById('rentalSubmitButton');
    const alatSearch = document.getElementById('alatSearch');
    const kategoriFilter = document.getElementById('kategoriFilter');
    const alatVisibleCount = document.getElementById('alatVisibleCount');
    const alatSelectedCount = document.getElementById('alatSelectedCount');
    const alatSelectedTotal = document.getElementById('alatSelectedTotal');
    const alatEmptyState = document.getElementById('alatEmptyState');
    const customerSearch = document.getElementById('customerSearch');
    const customerId = document.getElementById('customerId');
    const customerSuggestions = document.getElementById('customerSuggestions');
    const customerSelectionMessage = document.getElementById('customerSelectionMessage');
    const customers = @json($pelangganUntukPencarian);
    const availabilityUrl = @json(route('rental.availability'));
    const today = @json($today);
    const tomorrow = @json($tomorrow);
    const inventoryForDateLookup = @json($alatUntukPengecekan);
    let availabilityRequest = 0;
    let availabilityReady = false;
    let customerResults = [];
    let activeCustomerIndex = -1;

    function closeCustomerSuggestions() {
        customerSuggestions.classList.remove('active');
        customerSearch.setAttribute('aria-expanded', 'false');
        activeCustomerIndex = -1;
    }

    function searchCustomers() {
        const query = customerSearch.value.trim().toLocaleLowerCase('id');
        customerId.value = '';
        customerSelectionMessage.textContent = 'Pilih pelanggan dari hasil pencarian agar datanya tercatat dengan benar.';
        customerSelectionMessage.style.color = '#777';
        customerSuggestions.replaceChildren();

        if (!query) {
            customerResults = [];
            closeCustomerSuggestions();
            return;
        }

        customerResults = customers
            .map((customer) => {
                const name = customer.name.toLocaleLowerCase('id');
                const phone = String(customer.phone || '').toLocaleLowerCase('id');
                const nikSuffix = String(customer.nikSuffix || '').toLocaleLowerCase('id');
                const nameIndex = name.indexOf(query);
                const phoneIndex = phone.indexOf(query);
                const nikIndex = nikSuffix.indexOf(query);
                return {
                    ...customer,
                    rank: nameIndex === 0 || phoneIndex === 0 || nikIndex === 0 ? 0 : nameIndex >= 0 || phoneIndex >= 0 || nikIndex >= 0 ? 1 : 2
                };
            })
            .filter((customer) => customer.rank < 2)
            .sort((a, b) => a.rank - b.rank
                || String(b.lastRental || '').localeCompare(String(a.lastRental || ''))
                || a.name.localeCompare(b.name, 'id'))
            .slice(0, 8);

        if (customerResults.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'customer-suggestion';
            empty.textContent = 'Pelanggan tidak ditemukan. Tambahkan dulu di Data Pelanggan.';
            customerSuggestions.append(empty);
            customerSuggestions.classList.add('active');
            customerSearch.setAttribute('aria-expanded', 'true');
            return;
        }

        customerResults.forEach((customer, index) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'customer-suggestion';
            option.role = 'option';
            option.dataset.index = String(index);
            option.setAttribute('aria-selected', String(index === activeCustomerIndex));
            option.append(document.createTextNode(customer.name));
            const phone = document.createElement('small');
            const identity = `HP: ${customer.phone || 'belum diisi'} · NIK akhir: ${customer.nikSuffix || '----'}`;
            const history = customer.lastRental ? ` · Rental terakhir: ${customer.lastRental}` : ' · Belum pernah rental';
            phone.textContent = identity + history;
            option.append(phone);
            customerSuggestions.append(option);
        });
        customerSuggestions.classList.add('active');
        customerSearch.setAttribute('aria-expanded', 'true');
    }

    function selectCustomer(index) {
        const customer = customerResults[index];
        if (!customer) return;
        customerSearch.value = `${customer.name} - ${customer.phone || 'tanpa nomor HP'} (NIK akhir ${customer.nikSuffix || '----'})`;
        customerId.value = customer.id;
        customerSelectionMessage.textContent = customer.lastRental
            ? `Pelanggan dipilih. Terakhir rental pada ${customer.lastRental}; jika ini orang yang sama, gunakan data pelanggan ini lagi.`
            : 'Pelanggan dipilih. Pastikan identitasnya sesuai.';
        customerSelectionMessage.style.color = '#0f5132';
        closeCustomerSuggestions();
    }

    customerSearch.addEventListener('input', () => {
        activeCustomerIndex = -1;
        searchCustomers();
    });
    customerSearch.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' && customerResults.length) {
            event.preventDefault();
            activeCustomerIndex = (activeCustomerIndex + 1) % customerResults.length;
            searchCustomers();
        } else if (event.key === 'ArrowUp' && customerResults.length) {
            event.preventDefault();
            activeCustomerIndex = activeCustomerIndex <= 0 ? customerResults.length - 1 : activeCustomerIndex - 1;
            searchCustomers();
        } else if (event.key === 'Enter' && customerSuggestions.classList.contains('active') && customerResults.length) {
            event.preventDefault();
            selectCustomer(activeCustomerIndex >= 0 ? activeCustomerIndex : 0);
        } else if (event.key === 'Escape') {
            closeCustomerSuggestions();
        }
    });
    customerSuggestions.addEventListener('click', (event) => {
        const option = event.target.closest('.customer-suggestion[data-index]');
        if (option) selectCustomer(Number(option.dataset.index));
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.customer-search')) closeCustomerSuggestions();
    });

    const lookupStart = document.getElementById('cekStokMulai');
    const lookupEnd = document.getElementById('cekStokSampai');
    const lookupButton = document.getElementById('cekStokButton');
    const lookupMessage = document.getElementById('cekStokPesan');
    const lookupTable = document.getElementById('cekStokTabel');
    const lookupResults = document.getElementById('cekStokHasil');
    let stockLookupRequest = 0;

    async function checkStockForDates(start, end) {
        const requestId = ++stockLookupRequest;
        if (!start || !end || end <= start) {
            lookupMessage.textContent = 'Tanggal kembali harus setelah tanggal mulai.';
            lookupMessage.style.color = '#842029';
            lookupTable.hidden = true;
            return;
        }

        lookupStart.value = start;
        lookupEnd.value = end;
        lookupMessage.textContent = `Memeriksa ketersediaan alat untuk ${start} sampai ${end}...`;
        lookupMessage.style.color = '#666';
        lookupTable.hidden = true;
        lookupButton.disabled = true;

        try {
            const params = new URLSearchParams({ tgl_rental: start, tgl_kembali: end });
            const response = await fetch(`${availabilityUrl}?${params}`, {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();
            if (requestId !== stockLookupRequest) return;
            if (!response.ok) {
                throw new Error(result.message || Object.values(result.errors || {}).flat().join(' ') || 'Ketersediaan alat gagal diperiksa.');
            }

            lookupResults.replaceChildren();
            let availableTypes = 0;
            inventoryForDateLookup.forEach((item) => {
                const available = result.availability[item.id] ?? 0;
                const row = document.createElement('tr');
                const values = [
                    item.name,
                    item.size || '-',
                    String(item.total),
                    String(item.cleaning),
                    `${available} unit`,
                    available > 0 ? 'Masih tersedia' : 'Stok habis'
                ];
                values.forEach((value) => {
                    const cell = document.createElement('td');
                    cell.textContent = value;
                    row.append(cell);
                });
                if (available > 0) availableTypes++;
                lookupResults.append(row);
            });

            lookupTable.hidden = false;
            lookupMessage.textContent = `Untuk ${start} sampai ${end}, ada ${availableTypes} jenis alat yang masih tersedia. Booking yang bertabrakan sudah dikurangi; rental yang belum dikembalikan dan barang yang belum ditandai siap tetap dihitung belum tersedia.`;
            lookupMessage.style.color = '#0f5132';
        } catch (error) {
            if (requestId !== stockLookupRequest) return;
            lookupMessage.textContent = error.message;
            lookupMessage.style.color = '#842029';
        } finally {
            if (requestId === stockLookupRequest) lookupButton.disabled = false;
        }
    }

    lookupButton?.addEventListener('click', () => checkStockForDates(lookupStart.value, lookupEnd.value));
    document.querySelectorAll('.cek-stok-booking').forEach((button) => {
        button.addEventListener('click', () => {
            checkStockForDates(button.dataset.start, button.dataset.end);
            document.getElementById('cekStokBooking').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    function updateAlatSelectionSummary() {
        let unitCount = 0;
        let total = 0;
        const cards = [...document.querySelectorAll('.alat-item[data-alat-id]')];
        const hasMainRental = cards.some((card) =>
            card.dataset.itemType === 'sewa' && Number(card.querySelector('input[type="number"]')?.value || 0) > 0
        );
        cards.forEach((card) => {
            const quantityInput = card.querySelector('input[type="number"]');
            const quantity = Number(quantityInput?.value || 0);
            const itemType = card.dataset.itemType;
            const price = itemType === 'jual'
                ? Number(card.dataset.salePrice || 0)
                : itemType === 'aksesoris' && hasMainRental
                    ? 0
                    : Number(card.dataset.rentalPrice || 0);
            const note = card.querySelector('.harga-item-note');
            if (note) {
                note.textContent = quantity > 0 && itemType === 'aksesoris' && hasMainRental
                    ? 'Gratis karena transaksi ini menyewa alat utama.'
                    : itemType === 'jual'
                        ? 'Dihitung sebagai pembelian dan tidak masuk proses pengembalian.'
                        : '';
            }
            unitCount += quantity;
            total += quantity * price;
            card.classList.toggle('is-selected', quantity > 0);
        });
        alatSelectedCount.textContent = `${unitCount} unit`;
        alatSelectedTotal.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(total)}`;
    }

    function filterAlatCards() {
        const search = alatSearch.value.trim().toLocaleLowerCase('id');
        const category = kategoriFilter.value;
        let visibleCount = 0;

        document.querySelectorAll('.alat-item[data-alat-id]').forEach((card) => {
            const matchesSearch = !search || card.dataset.search.includes(search);
            const matchesCategory = !category || card.dataset.category === category;
            const visible = matchesSearch && matchesCategory;
            card.hidden = !visible;
            if (visible) visibleCount++;
        });

        alatVisibleCount.textContent = visibleCount;
        alatEmptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function updateTransactionMode() {
        const mode = rentalForm.querySelector('input[name="jenis_transaksi"]:checked')?.value || 'langsung';
        if (mode === 'langsung') {
            rentalDate.value = today;
            rentalDate.min = today;
            rentalDate.readOnly = true;
            rentalDateLabel.textContent = 'Tanggal Rental (hari ini)';
            returnDate.min = tomorrow;
            rentalModeMessage.textContent = 'Rental langsung: barang diambil hari ini. Untuk tanggal mendatang, pilih Booking. Harga berlaku untuk satu masa sewa; keterlambatan dicatat sebagai denda saat pengembalian.';
            rentalSubmitButton.textContent = 'Simpan Rental Langsung';
        } else {
            rentalDate.readOnly = false;
            rentalDate.min = today;
            if (!rentalDate.value || rentalDate.value < today) {
                rentalDate.value = today;
            }
            rentalDateLabel.textContent = 'Tanggal Mulai Booking';
            rentalModeMessage.textContent = 'Booking: bisa untuk diambil nanti hari ini atau tanggal mendatang. Stok dihitung khusus untuk rentang tanggal yang dipilih.';
            rentalSubmitButton.textContent = 'Simpan Booking';
        }

        const nextDay = new Date(`${rentalDate.value}T00:00:00`);
        nextDay.setDate(nextDay.getDate() + 1);
        const nextDayValue = `${nextDay.getFullYear()}-${String(nextDay.getMonth() + 1).padStart(2, '0')}-${String(nextDay.getDate()).padStart(2, '0')}`;
        if (returnDate.value <= rentalDate.value) {
            returnDate.value = nextDayValue;
        }
        returnDate.min = nextDayValue;
        refreshAvailability();
    }

    async function refreshAvailability() {
        availabilityReady = false;
        const start = rentalDate.value;
        const end = returnDate.value;
        const requestId = ++availabilityRequest;

        if (!start || !end || end <= start) {
            if (availabilityMessage) {
                availabilityMessage.textContent = 'Tanggal kembali harus setelah tanggal rental.';
                availabilityMessage.style.color = '#842029';
                availabilityMessage.style.background = '#f8d7da';
            }
            return;
        }

        if (availabilityMessage) {
            availabilityMessage.textContent = 'Memeriksa stok untuk tanggal pilihan...';
            availabilityMessage.style.color = '#666';
            availabilityMessage.style.background = '#f5f5f5';
        }
        const params = new URLSearchParams({ tgl_rental: start, tgl_kembali: end });

        try {
            const response = await fetch(`${availabilityUrl}?${params}`, {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();
            if (requestId !== availabilityRequest) return;
            if (!response.ok) {
                throw new Error(result.message || Object.values(result.errors || {}).flat().join(' ') || 'Stok tidak dapat diperiksa.');
            }

            document.querySelectorAll('.alat-item[data-alat-id]').forEach((row) => {
                const count = result.availability[row.dataset.alatId] ?? 0;
                const stockCount = row.querySelector('.stok-periode');
                const quantityInput = row.querySelector('input[type="number"]');
                if (stockCount) stockCount.textContent = count;
                if (quantityInput) quantityInput.max = count;
                row.classList.toggle('is-unavailable', count < 1);
            });
            if (availabilityMessage) {
                availabilityMessage.textContent = `Sisa stok sudah dihitung untuk ${start} sampai ${end}. Booking yang bertabrakan sudah dikurangi; barang yang belum dikembalikan dan dibersihkan belum dianggap siap.`;
                availabilityMessage.style.color = '#0f5132';
                availabilityMessage.style.background = '#d1e7dd';
            }
            availabilityReady = true;
        } catch (error) {
            if (requestId !== availabilityRequest) return;
            if (availabilityMessage) {
                availabilityMessage.textContent = error.message;
                availabilityMessage.style.color = '#842029';
                availabilityMessage.style.background = '#f8d7da';
            }
        }
    }

    rentalDate.addEventListener('change', () => {
        const nextDay = new Date(`${rentalDate.value}T00:00:00`);
        nextDay.setDate(nextDay.getDate() + 1);
        const nextDayValue = `${nextDay.getFullYear()}-${String(nextDay.getMonth() + 1).padStart(2, '0')}-${String(nextDay.getDate()).padStart(2, '0')}`;
        if (returnDate.value <= rentalDate.value) {
            returnDate.value = nextDayValue;
        }
        returnDate.min = nextDayValue;
        refreshAvailability();
    });
    returnDate.addEventListener('change', refreshAvailability);
    alatSearch.addEventListener('input', filterAlatCards);
    kategoriFilter.addEventListener('change', filterAlatCards);
    document.querySelectorAll('.alat-item input[type="number"]').forEach((input) => {
        input.addEventListener('input', updateAlatSelectionSummary);
    });
    rentalForm.querySelectorAll('input[name="jenis_transaksi"]').forEach((input) => {
        input.addEventListener('change', updateTransactionMode);
    });
    rentalForm?.addEventListener('submit', (event) => {
        if (!customerId.value) {
            event.preventDefault();
            customerSelectionMessage.textContent = 'Pilih pelanggan dari daftar hasil pencarian sebelum menyimpan rental.';
            customerSelectionMessage.style.color = '#842029';
            customerSearch.focus();
            return;
        }
        if (!availabilityReady) {
            event.preventDefault();
            if (availabilityMessage) {
                availabilityMessage.textContent = 'Tunggu sampai pemeriksaan stok untuk tanggal booking selesai.';
                availabilityMessage.style.color = '#842029';
                availabilityMessage.style.background = '#f8d7da';
            }
        }
    });

    filterAlatCards();
    updateAlatSelectionSummary();
    updateTransactionMode();
</script>
@endif
@endsection