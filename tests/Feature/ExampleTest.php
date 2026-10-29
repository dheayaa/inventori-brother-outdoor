<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailRental;
use App\Models\Kategori;
use App\Models\Pelanggan;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_daily_customer_total_only_for_today(): void
    {
        $user = User::create([
            'nama' => 'Admin',
            'username' => 'admin_daily_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        Pelanggan::create([
            'nama_pelanggan' => 'Andi',
            'no_hp' => '0811111111',
            'nik' => '1111111111111111',
            'alamat' => 'Bandung',
        ]);

        Pelanggan::create([
            'nama_pelanggan' => 'Budi',
            'no_hp' => '0822222222',
            'nik' => '2222222222222222',
            'alamat' => 'Bogor',
        ]);

        Pelanggan::create([
            'nama_pelanggan' => 'Cici',
            'no_hp' => '0833333333',
            'nik' => '3333333333333333',
            'alamat' => 'Jakarta',
        ]);

        Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-101',
            'pelanggan_id' => 1,
            'user_id' => $user->id,
            'tgl_rental' => now()->toDateString(),
            'tgl_kembali' => now()->addDay()->toDateString(),
            'total_bayar' => 150000,
            'status' => 'dipinjam',
        ]);

        Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-102',
            'pelanggan_id' => 2,
            'user_id' => $user->id,
            'tgl_rental' => now()->toDateString(),
            'tgl_kembali' => now()->addDay()->toDateString(),
            'total_bayar' => 200000,
            'status' => 'dipinjam',
        ]);

        Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd', strtotime('-1 day')).'-103',
            'pelanggan_id' => 3,
            'user_id' => $user->id,
            'tgl_rental' => now()->subDay()->toDateString(),
            'tgl_kembali' => now()->toDateString(),
            'total_bayar' => 180000,
            'status' => 'dikembalikan',
        ]);

        $response = $this->withSession(['user' => $user])->get('/dashboard');

        $response->assertOk()
            ->assertViewHas('totalPelanggan', 2);
    }

    public function test_owner_cannot_access_admin_only_actions(): void
    {
        $owner = User::create([
            'nama' => 'Owner',
            'username' => 'owner_'.time(),
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->withSession(['user' => $owner])->post('/kategori', [
            'nama_kategori' => 'Tenda Baru',
            'keterangan' => 'Test',
        ])->assertStatus(403);

        $this->withSession(['user' => $owner])->get('/pengembalian')->assertStatus(403);
    }

    public function test_admin_can_manage_categories_without_descriptions(): void
    {
        $admin = User::create([
            'nama' => 'Admin Kategori',
            'username' => 'admin_kategori_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->withSession(['user' => $admin])
            ->get('/kategori')
            ->assertOk()
            ->assertDontSee('Keterangan');

        $this->withSession(['user' => $admin])
            ->post('/kategori', [
                'nama_kategori' => 'Tenda',
            ])
            ->assertRedirect('/kategori');

        $this->assertDatabaseHas('kategori', [
            'nama_kategori' => 'Tenda',
        ]);
    }

    public function test_admin_can_add_and_update_user_phone_numbers(): void
    {
        $admin = User::create([
            'nama' => 'Admin User',
            'username' => 'admin_user_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->withSession(['user' => $admin])
            ->post('/user', [
                'nama' => 'User Telepon',
                'username' => 'user_telepon_'.time(),
                'no_hp' => '081234567890',
                'password' => 'password',
                'role' => 'owner',
            ])
            ->assertRedirect('/user');

        $user = User::where('nama', 'User Telepon')->firstOrFail();
        $this->assertSame('081234567890', $user->no_hp);
        $this->withSession(['user' => $admin])
            ->get('/user')
            ->assertOk()
            ->assertSee('No. HP')
            ->assertSee('081234567890');

        $this->withSession(['user' => $admin])
            ->put('/user/'.$user->id, [
                'nama' => 'User Telepon',
                'no_hp' => '089876543210',
                'role' => 'owner',
            ])
            ->assertRedirect('/user');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'no_hp' => '089876543210',
        ]);
    }

    public function test_user_phone_number_must_have_between_10_and_13_digits_when_creating_and_editing(): void
    {
        $admin = User::create([
            'nama' => 'Admin Validasi HP',
            'username' => 'admin_validasi_hp_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->withSession(['user' => $admin])
            ->post('/user', [
                'nama' => 'User HP Pendek',
                'username' => 'user_hp_pendek_'.time(),
                'no_hp' => '12345',
                'password' => 'password',
                'role' => 'owner',
            ])
            ->assertSessionHasErrors('no_hp');

        $this->withSession(['user' => $admin])
            ->post('/user', [
                'nama' => 'User HP Panjang',
                'username' => 'user_hp_panjang_'.time(),
                'no_hp' => '0812345678901234',
                'password' => 'password',
                'role' => 'owner',
            ])
            ->assertSessionHasErrors('no_hp');

        $user = User::create([
            'nama' => 'User HP',
            'username' => 'user_hp_edit_'.time(),
            'no_hp' => '081234567890',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->withSession(['user' => $admin])
            ->put('/user/'.$user->id, [
                'nama' => 'User HP',
                'no_hp' => '12345',
                'role' => 'owner',
            ])
            ->assertSessionHasErrors('no_hp');

        $this->withSession(['user' => $admin])
            ->put('/user/'.$user->id, [
                'nama' => 'User HP',
                'no_hp' => '0812345678901234',
                'role' => 'owner',
            ])
            ->assertSessionHasErrors('no_hp');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'no_hp' => '081234567890',
        ]);
    }

    public function test_admin_cannot_delete_the_last_owner_account(): void
    {
        $admin = User::create([
            'nama' => 'Admin User',
            'username' => 'admin_last_owner_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $owner = User::create([
            'nama' => 'Owner User',
            'username' => 'owner_last_'.time(),
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->withSession(['user' => $admin])
            ->delete('/user/'.$owner->id)
            ->assertRedirect('/user')
            ->assertSessionHas('error', 'Tidak bisa menghapus satu-satunya akun owner!');

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_customer_nik_and_phone_lengths_are_validated_when_creating_and_editing(): void
    {
        $admin = User::create([
            'nama' => 'Admin Pelanggan Validasi',
            'username' => 'admin_pelanggan_validasi_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $validCustomer = [
            'nama_pelanggan' => 'Pelanggan Valid',
            'no_hp' => '081234567890',
            'nik' => '1234567890123456',
        ];

        $this->withSession(['user' => $admin])
            ->post('/pelanggan', array_merge($validCustomer, [
                'no_hp' => '123',
                'nik' => '123456789012345',
            ]))
            ->assertSessionHasErrors(['no_hp', 'nik']);

        $this->withSession(['user' => $admin])
            ->post('/pelanggan', array_merge($validCustomer, [
                'no_hp' => '0812345678901234',
                'nik' => '12345678901234567',
            ]))
            ->assertSessionHasErrors(['no_hp', 'nik']);

        $this->withSession(['user' => $admin])
            ->post('/pelanggan', $validCustomer)
            ->assertRedirect('/pelanggan');

        $customer = Pelanggan::where('nik', $validCustomer['nik'])->firstOrFail();
        $this->withSession(['user' => $admin])
            ->put('/pelanggan/'.$customer->id, array_merge($validCustomer, [
                'no_hp' => '123',
            ]))
            ->assertSessionHasErrors('no_hp');

        $this->withSession(['user' => $admin])
            ->put('/pelanggan/'.$customer->id, array_merge($validCustomer, [
                'nik' => '123456789012345',
            ]))
            ->assertSessionHasErrors('nik');

        $this->withSession(['user' => $admin])
            ->put('/pelanggan/'.$customer->id, $validCustomer)
            ->assertRedirect('/pelanggan');

        $this->assertDatabaseHas('pelanggan', [
            'id' => $customer->id,
            'no_hp' => $validCustomer['no_hp'],
            'nik' => $validCustomer['nik'],
        ]);
    }

    public function test_admin_can_create_an_item_without_entering_an_item_code(): void
    {
        $admin = User::create([
            'nama' => 'Admin Alat',
            'username' => 'admin_alat_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Alat',
            'keterangan' => 'Uji kode alat otomatis',
        ]);

        $this->withSession(['user' => $admin])
            ->post('/alat', [
                'kategori_id' => $kategori->id,
                'nama_alat' => 'Sepatu Ukuran 40',
                'ukuran' => '40',
                'stok_total' => 2,
                'harga_sewa' => 30000,
                'kondisi' => 'baik',
            ])
            ->assertRedirect('/alat');

        $alat = Alat::where('nama_alat', 'Sepatu Ukuran 40')->firstOrFail();
        $this->assertStringStartsWith('SYS-', $alat->kode_alat);

        $this->withSession(['user' => $admin])
            ->get('/alat')
            ->assertOk()
            ->assertSee('Sepatu Ukuran 40')
            ->assertSee('40')
            ->assertDontSee('Kode Alat')
            ->assertDontSee($alat->kode_alat);
    }

    public function test_items_for_sale_do_not_require_a_rental_price(): void
    {
        $admin = User::create([
            'nama' => 'Admin Barang Jual',
            'username' => 'admin_barang_jual_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Barang Jual',
            'keterangan' => 'Uji harga jual',
        ]);

        $this->withSession(['user' => $admin])
            ->post('/alat', [
                'kategori_id' => $kategori->id,
                'nama_alat' => 'Jas Hujan Jual',
                'jenis_alat' => 'jual',
                'stok_total' => 4,
                'harga_jual' => 25000,
                'kondisi' => 'baik',
            ])
            ->assertRedirect('/alat')
            ->assertSessionHasNoErrors();

        $alat = Alat::where('nama_alat', 'Jas Hujan Jual')->firstOrFail();
        $this->assertSame(0, $alat->harga_sewa);
        $this->assertSame(25000, $alat->harga_jual);

        $this->withSession(['user' => $admin])
            ->put('/alat/'.$alat->id, [
                'kategori_id' => $kategori->id,
                'nama_alat' => 'Jas Hujan Jual',
                'jenis_alat' => 'jual',
                'stok_total' => 4,
                'harga_jual' => 30000,
                'kondisi' => 'baik',
            ])
            ->assertRedirect('/alat')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('alat', [
            'id' => $alat->id,
            'harga_sewa' => 0,
            'harga_jual' => 30000,
        ]);
    }

    public function test_dashboard_shows_monthly_income_and_late_rentals(): void
    {
        $admin = User::create([
            'nama' => 'Admin 2',
            'username' => 'admin2_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        Pelanggan::create([
            'nama_pelanggan' => 'Doni',
            'no_hp' => '0844444444',
            'nik' => '4444444444444444',
            'alamat' => 'Surabaya',
        ]);

        $rentalBulanIni = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-201',
            'pelanggan_id' => 1,
            'user_id' => $admin->id,
            'tgl_rental' => now()->toDateString(),
            'tgl_kembali' => now()->addDay()->toDateString(),
            'total_bayar' => 250000,
            'status' => 'dipinjam',
        ]);
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Penjualan Dashboard',
            'keterangan' => 'Uji total penjualan dashboard admin',
        ]);
        $barangJual = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Jas Hujan Dashboard',
            'kode_alat' => 'JAS-HUJAN-DASH',
            'stok_total' => 5,
            'stok_tersedia' => 5,
            'harga_sewa' => 0,
            'harga_jual' => 20000,
            'jenis_alat' => 'jual',
            'kondisi' => 'baik',
        ]);
        DetailRental::create([
            'rental_id' => $rentalBulanIni->id,
            'alat_id' => $barangJual->id,
            'jumlah' => 1,
            'jenis_item' => 'jual',
            'harga_sewa' => 20000,
            'subtotal' => 20000,
        ]);

        Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-202',
            'pelanggan_id' => 1,
            'user_id' => $admin->id,
            'tgl_rental' => now()->startOfMonth()->subDay()->toDateString(),
            'tgl_kembali' => now()->startOfMonth()->subDays(2)->toDateString(),
            'total_bayar' => 300000,
            'status' => 'dipinjam',
        ]);

        $response = $this->withSession(['user' => $admin])->get('/dashboard');

        $response->assertOk()
            ->assertViewHas('pendapatanBulanIni', 250000)
            ->assertViewHas('penjualanBulanIni', 20000)
            ->assertViewHas('jumlahRentalTerlambat', 1)
            ->assertSee('Total Pemasukan Bulan Ini')
            ->assertSee('Total Penjualan Bulan Ini')
            ->assertSee('20.000');
    }

    public function test_owner_dashboard_shows_period_financial_and_inventory_statistics(): void
    {
        $owner = User::create([
            'nama' => 'Owner Statistik',
            'username' => 'owner_statistik_'.time(),
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Statistik',
            'no_hp' => '081234567891',
            'nik' => '1234567890123401',
            'alamat' => 'Semarang',
        ]);
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Statistik',
            'keterangan' => 'Uji statistik owner',
        ]);

        $tenda = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Tenda Statistik',
            'kode_alat' => 'TND-STAT-1',
            'stok_total' => 4,
            'stok_tersedia' => 4,
            'stok_pencucian' => 1,
            'harga_sewa' => 50000,
            'kondisi' => 'baik',
        ]);
        $mug = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Mug Statistik',
            'kode_alat' => 'MUG-STAT-1',
            'stok_total' => 5,
            'stok_tersedia' => 5,
            'harga_sewa' => 5000,
            'jenis_alat' => 'aksesoris',
            'kondisi' => 'baik',
        ]);
        $handWarmer = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Hand Warmer Statistik',
            'kode_alat' => 'HW-STAT-1',
            'stok_total' => 3,
            'stok_tersedia' => 3,
            'harga_sewa' => 0,
            'harga_jual' => 15000,
            'jenis_alat' => 'jual',
            'kondisi' => 'baik',
        ]);

        $rental = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-OWNER-1',
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $owner->id,
            'tgl_rental' => now()->toDateString(),
            'tgl_kembali' => now()->addDay()->toDateString(),
            'total_bayar' => 65000,
            'status' => 'dipinjam',
        ]);
        DetailRental::create([
            'rental_id' => $rental->id,
            'alat_id' => $tenda->id,
            'jumlah' => 1,
            'jenis_item' => 'sewa',
            'harga_sewa' => 50000,
            'subtotal' => 50000,
        ]);
        DetailRental::create([
            'rental_id' => $rental->id,
            'alat_id' => $mug->id,
            'jumlah' => 1,
            'jenis_item' => 'gratis',
            'harga_sewa' => 0,
            'subtotal' => 0,
        ]);
        DetailRental::create([
            'rental_id' => $rental->id,
            'alat_id' => $handWarmer->id,
            'jumlah' => 1,
            'jenis_item' => 'jual',
            'harga_sewa' => 15000,
            'subtotal' => 15000,
        ]);

        $fine = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-OWNER-2',
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $owner->id,
            'tgl_rental' => now()->startOfMonth()->subDay()->toDateString(),
            'tgl_kembali' => now()->startOfMonth()->subDays(2)->toDateString(),
            'tgl_kembali_aktual' => now()->toDateString(),
            'total_bayar' => 0,
            'denda' => 2000,
            'status' => 'dikembalikan',
        ]);

        $sale = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-OWNER-3',
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $owner->id,
            'tgl_rental' => now()->toDateString(),
            'tgl_kembali' => now()->toDateString(),
            'total_bayar' => 15000,
            'status' => 'dikembalikan',
        ]);
        DetailRental::create([
            'rental_id' => $sale->id,
            'alat_id' => $handWarmer->id,
            'jumlah' => 1,
            'jenis_item' => 'jual',
            'harga_sewa' => 15000,
            'subtotal' => 15000,
        ]);

        $this->withSession(['user' => $owner])
            ->get('/dashboard?bulan='.now()->format('Y-m'))
            ->assertOk()
            ->assertSee('Statistik Usaha')
            ->assertSee('Pemasukan Harian')
            ->assertSee('Tenda Statistik')
            ->assertViewHas('pemasukanSewaOwner', 50000)
            ->assertViewHas('pemasukanPenjualanOwner', 30000)
            ->assertViewHas('pemasukanDendaOwner', 2000)
            ->assertViewHas('totalPemasukanOwner', 82000)
            ->assertViewHas('jumlahRentalOwner', 1)
            ->assertViewHas('jumlahPelangganOwner', 1)
            ->assertViewHas('jumlahRentalBerjalanOwner', 1)
            ->assertViewHas('jumlahUnitDirentalOwner', 2)
            ->assertViewHas('jumlahAlatTersediaOwner', 6)
            ->assertViewHas('jumlahPencucianOwner', 1);
    }

    public function test_return_page_notifies_when_rental_is_due_today_and_not_returned(): void
    {
        $admin = User::create([
            'nama' => 'Admin Pengembalian',
            'username' => 'admin_kembali_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Jatuh Tempo',
            'no_hp' => '0856666666',
            'nik' => '6666666666666666',
            'alamat' => 'Semarang',
        ]);

        $rental = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-401',
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $admin->id,
            'tgl_rental' => now()->subDay()->toDateString(),
            'tgl_kembali' => now()->toDateString(),
            'total_bayar' => 50000,
            'status' => 'dipinjam',
        ]);

        $this->withSession(['user' => $admin])
            ->get('/pengembalian')
            ->assertOk()
            ->assertSee('Pelanggan Jatuh Tempo')
            ->assertSee('pukul');
    }

    public function test_rental_is_late_after_the_store_closing_time_for_its_return_date(): void
    {
        $rentalWeekday = new Rental([
            'tgl_kembali' => '2026-10-15',
            'status' => 'dipinjam',
        ]);
        $rentalFriday = new Rental([
            'tgl_kembali' => '2026-10-16',
            'status' => 'dipinjam',
        ]);
        $rentalSaturday = new Rental([
            'tgl_kembali' => '2026-10-17',
            'status' => 'dipinjam',
        ]);

        $this->travelTo(Carbon::parse('2026-10-15 22:59:59', 'Asia/Jakarta'));
        $this->assertSame('dipinjam', $rentalWeekday->effective_status);
        $this->assertSame('2026-10-15 23:00', $rentalWeekday->batas_waktu_kembali->format('Y-m-d H:i'));

        $this->travelTo(Carbon::parse('2026-10-15 23:00:00', 'Asia/Jakarta'));
        $this->assertSame('terlambat', $rentalWeekday->effective_status);

        $this->travelTo(Carbon::parse('2026-10-16 23:59:59', 'Asia/Jakarta'));
        $this->assertSame('dipinjam', $rentalFriday->effective_status);
        $this->assertSame('2026-10-17 00:00', $rentalFriday->batas_waktu_kembali->format('Y-m-d H:i'));
        $this->assertSame('dipinjam', $rentalSaturday->effective_status);

        $this->travelTo(Carbon::parse('2026-10-17 00:00:00', 'Asia/Jakarta'));
        $this->assertSame('terlambat', $rentalFriday->effective_status);
        $this->assertSame('dipinjam', $rentalSaturday->effective_status);

        $this->travelTo(Carbon::parse('2026-10-18 00:00:00', 'Asia/Jakarta'));
        $this->assertSame('terlambat', $rentalSaturday->effective_status);

        $this->travelBack();
    }

    public function test_report_export_routes_are_available_for_admin_and_owner(): void
    {
        $admin = User::create([
            'nama' => 'Admin Export',
            'username' => 'admin_export_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $owner = User::create([
            'nama' => 'Owner Export',
            'username' => 'owner_export_'.time(),
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->withSession(['user' => $admin])
            ->get('/laporan/rental')
            ->assertOk()
            ->assertSee('Wonosobo,')
            ->assertSee('name="nama_penandatangan"', false)
            ->assertSee('Admin,')
            ->assertSee('Admin Export')
            ->assertDontSee('Owner Export');
        $this->withSession(['user' => $owner])
            ->get('/laporan/rental?nama_penandatangan=Owner%20Pengganti')
            ->assertOk()
            ->assertSee('Wonosobo,')
            ->assertSee('Owner,')
            ->assertSee('Owner Pengganti')
            ->assertDontSee('Admin Export');

        $this->withSession(['user' => $admin])
            ->get('/laporan/rental/export/pdf')
            ->assertStatus(200);
        $this->withSession(['user' => $owner])
            ->get('/laporan/rental/export/pdf?nama_penandatangan=Owner%20Pengganti')
            ->assertStatus(200);
        $this->withSession(['user' => $admin])->get('/laporan/rental/export/excel')->assertStatus(200);
    }

    public function test_income_report_counts_rentals_and_fines_on_their_income_dates(): void
    {
        $admin = User::create([
            'nama' => 'Admin Laporan',
            'username' => 'admin_laporan_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Laporan',
            'no_hp' => '0866666666',
            'nik' => '6666666666666666',
            'alamat' => 'Bandung',
        ]);

        $rental = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-401',
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $admin->id,
            'tgl_rental' => now()->subDay()->toDateString(),
            'tgl_kembali' => now()->toDateString(),
            'tgl_kembali_aktual' => now()->toDateString(),
            'total_bayar' => 100000,
            'denda' => 30000,
            'status' => 'dikembalikan',
        ]);
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Penjualan Laporan',
            'keterangan' => 'Uji penjualan di laporan',
        ]);
        $barangJual = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Jas Hujan Laporan',
            'kode_alat' => 'JAS-HUJAN-LAPORAN',
            'stok_total' => 3,
            'stok_tersedia' => 3,
            'harga_sewa' => 0,
            'harga_jual' => 15000,
            'jenis_alat' => 'jual',
            'kondisi' => 'baik',
        ]);
        $barangSewa = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Tenda Laporan',
            'kode_alat' => 'TENDA-LAPORAN',
            'stok_total' => 2,
            'stok_tersedia' => 2,
            'harga_sewa' => 85000,
            'kondisi' => 'baik',
        ]);
        DetailRental::create([
            'rental_id' => $rental->id,
            'alat_id' => $barangSewa->id,
            'jumlah' => 1,
            'jenis_item' => 'sewa',
            'harga_sewa' => 85000,
            'subtotal' => 85000,
        ]);
        DetailRental::create([
            'rental_id' => $rental->id,
            'alat_id' => $barangJual->id,
            'jumlah' => 1,
            'jenis_item' => 'jual',
            'harga_sewa' => 15000,
            'subtotal' => 15000,
        ]);

        $response = $this->withSession(['user' => $admin])->get('/laporan/rental?periode=harian');

        $response->assertOk()
            ->assertViewHas('totalBayar', 100000)
            ->assertViewHas('totalPenjualan', 15000)
            ->assertViewHas('totalDenda', 30000)
            ->assertViewHas('totalPemasukan', 130000)
            ->assertSee('Total Penjualan')
            ->assertSee('15.000')
            ->assertViewHas('laporanPemasukan', function ($rows) {
                return $rows->count() === 2
                    && $rows->sum('total_bayar') === 100000
                    && $rows->sum('total_penjualan') === 15000
                    && $rows->sum('total_denda') === 30000
                    && $rows->sum('jumlah_rental') === 1
                    && $rows->firstWhere('key', now()->subDay()->toDateString())['jumlah_rental'] === 1
                    && $rows->firstWhere('key', now()->toDateString())['jumlah_rental'] === 0;
            });

        $csv = $this->withSession(['user' => $admin])
            ->get('/laporan/rental/export/excel?periode=harian')
            ->assertOk()
            ->assertSee('Total Penjualan')
            ->assertSee('15000');
    }

    public function test_rental_report_shows_all_rented_items_and_total_due_in_detail_and_csv(): void
    {
        $admin = User::create([
            'nama' => 'Admin Detail Rental',
            'username' => 'admin_detail_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Rombongan Detail',
            'no_hp' => '0877777777',
            'nik' => '7777777777777777',
            'alamat' => 'Bandung',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Detail',
            'keterangan' => 'Uji rincian rental',
        ]);

        $rental = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-402',
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $admin->id,
            'tgl_rental' => now()->subDays(2)->toDateString(),
            'tgl_kembali' => now()->subDay()->toDateString(),
            'tgl_kembali_aktual' => now()->toDateString(),
            'total_bayar' => 550000,
            'denda' => 10000,
            'status' => 'dikembalikan',
        ]);

        for ($i = 1; $i <= 11; $i++) {
            $alat = Alat::create([
                'kategori_id' => $kategori->id,
                'nama_alat' => 'Alat Rombongan '.$i,
                'kode_alat' => 'ROMB-'.$i,
                'stok_total' => 2,
                'stok_tersedia' => 1,
                'harga_sewa' => 25000,
                'kondisi' => 'baik',
            ]);

            DetailRental::create([
                'rental_id' => $rental->id,
                'alat_id' => $alat->id,
                'jumlah' => 1,
                'harga_sewa' => 25000,
                'subtotal' => 50000,
            ]);
        }

        $this->withSession(['user' => $admin])
            ->get('/laporan/rental')
            ->assertOk()
            ->assertSee('Lihat 11 jenis alat')
            ->assertSee('Alat Rombongan 11')
            ->assertSee('Rp 560.000');

        $csv = $this->withSession(['user' => $admin])
            ->get('/laporan/rental/export/excel')
            ->assertOk()
            ->assertSee('Rincian Alat')
            ->assertSee('Alat Rombongan 11')
            ->assertSee('560000');
    }

    public function test_admin_can_record_a_deposited_identity_when_creating_a_rental(): void
    {
        $admin = User::create([
            'nama' => 'Admin Identitas',
            'username' => 'admin_identitas_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Identitas',
            'no_hp' => '0888888888',
            'nik' => '8888888888888888',
            'alamat' => 'Bandung',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Identitas',
            'keterangan' => 'Uji titipan identitas',
        ]);

        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Tenda Identitas',
            'kode_alat' => 'TND-IDENTITAS',
            'stok_total' => 2,
            'stok_tersedia' => 2,
            'harga_sewa' => 50000,
            'kondisi' => 'baik',
        ]);

        $this->withSession(['user' => $admin])
            ->post('/rental', [
                'pelanggan_id' => $pelanggan->id,
                'tgl_rental' => now()->toDateString(),
                'tgl_kembali' => now()->addDays(2)->toDateString(),
                'alat' => [$alat->id => 1],
                'identitas_dititipkan' => '1',
            ])
            ->assertRedirect('/rental');

        $this->assertDatabaseHas('rental', [
            'pelanggan_id' => $pelanggan->id,
            'identitas_dititipkan' => 1,
            'identitas_dikembalikan' => 0,
            'total_bayar' => 50000,
        ]);
        $this->assertDatabaseHas('detail_rental', [
            'alat_id' => $alat->id,
            'jumlah' => 1,
            'harga_sewa' => 50000,
            'subtotal' => 50000,
        ]);

        $this->withSession(['user' => $admin])
            ->get('/rental')
            ->assertOk()
            ->assertSee('Masih dititipkan');
    }

    public function test_admin_can_update_identity_deposit_status_for_a_booking(): void
    {
        $admin = User::create([
            'nama' => 'Admin Update Identitas',
            'username' => 'admin_update_identitas_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Booking Identitas',
            'no_hp' => '0877777777',
            'nik' => '7777777777777777',
            'alamat' => 'Bandung',
        ]);

        $booking = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-IDENTITAS',
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $admin->id,
            'tgl_rental' => now()->addDays(3)->toDateString(),
            'tgl_kembali' => now()->addDays(5)->toDateString(),
            'total_bayar' => 50000,
            'identitas_dititipkan' => false,
            'identitas_dikembalikan' => false,
            'status' => 'dipinjam',
            'jenis_transaksi' => 'booking',
        ]);

        $this->withSession(['user' => $admin])
            ->put('/rental/'.$booking->id.'/identitas', [
                'identitas_dititipkan' => '1',
            ])
            ->assertRedirect('/rental');

        $this->assertDatabaseHas('rental', [
            'id' => $booking->id,
            'identitas_dititipkan' => 1,
            'identitas_dikembalikan' => 0,
        ]);

        $this->withSession(['user' => $admin])
            ->put('/rental/'.$booking->id.'/identitas', [
                'identitas_dititipkan' => '0',
            ])
            ->assertRedirect('/rental');

        $this->assertDatabaseHas('rental', [
            'id' => $booking->id,
            'identitas_dititipkan' => 0,
            'identitas_dikembalikan' => 0,
        ]);
    }

    public function test_customer_search_distinguishes_same_names_and_shows_recent_rental(): void
    {
        $admin = User::create([
            'nama' => 'Admin Cari Pelanggan',
            'username' => 'admin_cari_pelanggan_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $pelangganLama = Pelanggan::create([
            'nama_pelanggan' => 'Andi',
            'no_hp' => '0811111111',
            'nik' => '1111222233334444',
            'alamat' => 'Semarang',
        ]);
        Pelanggan::create([
            'nama_pelanggan' => 'Andi',
            'no_hp' => '0822222222',
            'nik' => '5555666677778888',
            'alamat' => 'Demak',
        ]);

        Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-CARI',
            'pelanggan_id' => $pelangganLama->id,
            'user_id' => $admin->id,
            'tgl_rental' => now()->subDays(5)->toDateString(),
            'tgl_kembali' => now()->subDays(3)->toDateString(),
            'total_bayar' => 30000,
            'status' => 'dikembalikan',
        ]);

        $this->withSession(['user' => $admin])
            ->get('/rental')
            ->assertOk()
            ->assertSee('Jika nama sama, cocokkan nomor HP atau 4 digit akhir NIK')
            ->assertSee('0811111111')
            ->assertSee('0822222222')
            ->assertSee('4444')
            ->assertSee('8888')
            ->assertSee(now()->subDays(5)->toDateString());
    }

    public function test_rental_form_explicitly_shows_when_an_item_size_is_missing(): void
    {
        $admin = User::create([
            'nama' => 'Admin Ukuran Kosong',
            'username' => 'admin_ukuran_kosong_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        Kategori::create([
            'nama_kategori' => 'Kategori Ukuran Kosong',
            'keterangan' => 'Uji keterangan ukuran alat',
        ]);

        Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Pencarian',
            'no_hp' => '081234567890',
            'nik' => '1234567890123456',
            'alamat' => 'Semarang',
        ]);

        Alat::create([
            'kategori_id' => 1,
            'nama_alat' => 'Sepatu Tanpa Ukuran',
            'kode_alat' => 'SPT-NO-SIZE',
            'stok_total' => 1,
            'stok_tersedia' => 1,
            'harga_sewa' => 30000,
            'kondisi' => 'baik',
        ]);

        $this->withSession(['user' => $admin])
            ->get('/rental')
            ->assertOk()
            ->assertSee('Ketik nama atau nomor HP pelanggan')
            ->assertSee('Pelanggan Pencarian')
            ->assertSee('Ukuran belum diisi')
            ->assertSee('Cari nama alat atau ukuran')
            ->assertSee('Semua kategori')
            ->assertSee('Dipilih:')
            ->assertSee('Jumlah yang disewa')
            ->assertSee('Sepatu Tanpa Ukuran');
    }

    public function test_future_bookings_reserve_stock_only_for_overlapping_dates(): void
    {
        $admin = User::create([
            'nama' => 'Admin Booking',
            'username' => 'admin_booking_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Booking',
            'no_hp' => '0899999999',
            'nik' => '9999999999999999',
            'alamat' => 'Bandung',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Booking',
            'keterangan' => 'Uji jadwal stok booking',
        ]);

        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Tenda Booking',
            'ukuran' => 'L',
            'kode_alat' => 'TND-BOOKING',
            'stok_total' => 3,
            'stok_tersedia' => 3,
            'harga_sewa' => 50000,
            'kondisi' => 'baik',
        ]);

        $mulaiBookingLama = now()->addDays(2)->toDateString();
        $selesaiBookingLama = now()->addDays(3)->toDateString();
        $bookingLama = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-501',
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $admin->id,
            'tgl_rental' => $mulaiBookingLama,
            'tgl_kembali' => $selesaiBookingLama,
            'total_bayar' => 100000,
            'status' => 'dipinjam',
            'jenis_transaksi' => 'booking',
        ]);

        DetailRental::create([
            'rental_id' => $bookingLama->id,
            'alat_id' => $alat->id,
            'jumlah' => 2,
            'ukuran' => 'L',
            'harga_sewa' => 50000,
            'subtotal' => 100000,
        ]);

        $this->withSession(['user' => $admin])
            ->get(route('rental.availability', [
                'tgl_rental' => $mulaiBookingLama,
                'tgl_kembali' => $selesaiBookingLama,
            ]))
            ->assertOk()
            ->assertJsonPath('availability.'.$alat->id, 1);

        $hariSetelahBooking = now()->addDays(4)->toDateString();
        $hariBerikutnya = now()->addDays(5)->toDateString();
        $this->withSession(['user' => $admin])
            ->get(route('rental.availability', [
                'tgl_rental' => $hariSetelahBooking,
                'tgl_kembali' => $hariBerikutnya,
            ]))
            ->assertOk()
            ->assertJsonPath('availability.'.$alat->id, 1);

        $this->withSession(['user' => $admin])
            ->post('/rental', [
                'jenis_transaksi' => 'langsung',
                'pelanggan_id' => $pelanggan->id,
                'tgl_rental' => $mulaiBookingLama,
                'tgl_kembali' => $selesaiBookingLama,
                'alat' => [$alat->id => 1],
            ])
            ->assertSessionHasErrors('tgl_rental');

        $this->withSession(['user' => $admin])
            ->post('/rental', [
                'jenis_transaksi' => 'booking',
                'pelanggan_id' => $pelanggan->id,
                'tgl_rental' => $mulaiBookingLama,
                'tgl_kembali' => $selesaiBookingLama,
                'alat' => [$alat->id => 1],
            ])
            ->assertRedirect('/rental');

        $hariIni = now()->toDateString();
        $this->withSession(['user' => $admin])
            ->post('/rental', [
                'jenis_transaksi' => 'booking',
                'pelanggan_id' => $pelanggan->id,
                'tgl_rental' => $hariIni,
                'tgl_kembali' => now()->addDay()->toDateString(),
                'alat' => [$alat->id => 1],
            ])
            ->assertRedirect('/rental');

        $bookingHariIni = Rental::where('pelanggan_id', $pelanggan->id)
            ->whereDate('tgl_rental', $hariIni)
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('booking', $bookingHariIni->jenis_transaksi);

        $this->withSession(['user' => $admin])
            ->get(route('rental.availability', [
                'tgl_rental' => $hariIni,
                'tgl_kembali' => now()->addDay()->toDateString(),
            ]))
            ->assertOk()
            ->assertJsonPath('availability.'.$alat->id, 2);

        $rentalBaru = Rental::where('pelanggan_id', $pelanggan->id)
            ->whereDate('tgl_rental', $mulaiBookingLama)
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('booking', $rentalBaru->jenis_transaksi);
        $this->assertDatabaseHas('detail_rental', [
            'rental_id' => $rentalBaru->id,
            'alat_id' => $alat->id,
            'ukuran' => 'L',
        ]);

        $this->withSession(['user' => $admin])
            ->get('/rental')
            ->assertOk()
            ->assertSee('unit tersedia untuk seluruh rentang tanggal pilihan')
            ->assertSee('Termasuk pesanan WhatsApp yang diambil nanti hari ini.')
            ->assertSee('Booking hari ini')
            ->assertSee('Booking Rental Aktif')
            ->assertSee('Cek Sisa Alat untuk Tanggal Booking')
            ->assertSee('Lihat Ketersediaan')
            ->assertSee('Cek sisa alat')
            ->assertSee('Tenda Booking')
            ->assertSee('Pelanggan Booking')
            ->assertSee('Rincian Barang Disewa')
            ->assertSee('Ukuran L')
            ->assertSee('Harga Sewa')
            ->assertSee('2 unit')
            ->assertSee('Rp 100.000');

        $owner = User::create([
            'nama' => 'Owner Booking',
            'username' => 'owner_booking_'.time(),
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->withSession(['user' => $owner])
            ->get('/rental')
            ->assertOk()
            ->assertSee('Tenda Booking')
            ->assertSee('Rincian Barang Disewa')
            ->assertSee('Ukuran L')
            ->assertSee('Harga Sewa')
            ->assertSee('2 unit')
            ->assertSee('Rp 100.000');

        $this->withSession(['user' => $admin])
            ->post('/rental', [
                'jenis_transaksi' => 'booking',
                'pelanggan_id' => $pelanggan->id,
                'tgl_rental' => $mulaiBookingLama,
                'tgl_kembali' => $selesaiBookingLama,
                'alat' => [$alat->id => 1],
            ])
            ->assertSessionHasErrors('alat');
    }

    public function test_admin_can_record_fine_manually_when_returning_item(): void
    {
        $admin = User::create([
            'nama' => 'Admin Denda',
            'username' => 'admin_denda_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Denda Manual',
            'no_hp' => '0855555555',
            'nik' => '5555555555555555',
            'alamat' => 'Semarang',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Manual',
            'keterangan' => 'Untuk uji manual denda',
        ]);

        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Tenda Uji',
            'kode_alat' => 'TND-TEST-1',
            'stok_total' => 2,
            'stok_tersedia' => 2,
            'harga_sewa' => 50000,
            'kondisi' => 'baik',
            'keterangan' => 'Uji manual denda',
        ]);

        $rental = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-301',
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $admin->id,
            'tgl_rental' => now()->subDays(5)->toDateString(),
            'tgl_kembali' => now()->subDays(2)->toDateString(),
            'total_bayar' => 250000,
            'identitas_dititipkan' => true,
            'status' => 'dipinjam',
        ]);

        DetailRental::create([
            'rental_id' => $rental->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
            'harga_sewa' => 50000,
            'subtotal' => 50000,
        ]);

        $this->withSession(['user' => $admin])
            ->post('/pengembalian/'.$rental->id, [
                'tgl_kembali_aktual' => now()->toDateString(),
                'denda' => 150000,
                'identitas_dikembalikan' => '1',
            ])
            ->assertRedirect('/pengembalian');

        $tanggalAktual = Carbon::parse(now()->toDateString())
            ->locale('id')
            ->translatedFormat('j F Y');
        $this->assertStringContainsString(
            'Tanggal kembali aktual: '.$tanggalAktual,
            session('success')
        );
        $this->withSession(['user' => $admin])
            ->get('/pengembalian')
            ->assertOk()
            ->assertSee('Daftar Rental & Pengembalian', false)
            ->assertSee('Tgl Jatuh Tempo')
            ->assertSee('Tgl Dikembalikan')
            ->assertSee(now()->subDays(2)->locale('id')->translatedFormat('j F Y'))
            ->assertSee(now()->locale('id')->translatedFormat('j F Y'))
            ->assertDontSee('Riwayat Pengembalian');
        $this->assertDatabaseHas('rental', [
            'id' => $rental->id,
            'denda' => 150000,
            'identitas_dititipkan' => 1,
            'identitas_dikembalikan' => 1,
        ]);
        $this->assertDatabaseHas('alat', [
            'id' => $alat->id,
            'stok_pencucian' => 1,
        ]);
        $this->withSession(['user' => $admin])
            ->get(route('rental.availability', [
                'tgl_rental' => now()->addDay()->toDateString(),
                'tgl_kembali' => now()->addDays(2)->toDateString(),
            ]))
            ->assertJsonPath('availability.'.$alat->id, 1);

        $this->withSession(['user' => $admin])
            ->get('/alat')
            ->assertOk()
            ->assertSee('Dalam Pencucian')
            ->assertSee('Tandai Siap')
            ->assertViewHas('alat', fn ($alatList) => $alatList->firstWhere('id', $alat->id)->stok_tersedia === 1);

        $this->withSession(['user' => $admin])
            ->put('/alat/'.$alat->id.'/pencucian', ['jumlah' => 1])
            ->assertRedirect('/alat');

        $this->assertDatabaseHas('alat', [
            'id' => $alat->id,
            'stok_pencucian' => 0,
        ]);

        $this->withSession(['user' => $admin])
            ->get('/alat')
            ->assertViewHas('alat', fn ($alatList) => $alatList->firstWhere('id', $alat->id)->stok_tersedia === 2);
        $this->withSession(['user' => $admin])
            ->get(route('rental.availability', [
                'tgl_rental' => now()->addDay()->toDateString(),
                'tgl_kembali' => now()->addDays(2)->toDateString(),
            ]))
            ->assertJsonPath('availability.'.$alat->id, 2);
    }

    public function test_admin_can_edit_identity_return_status_after_rental_is_completed(): void
    {
        $admin = User::create([
            'nama' => 'Admin Identitas',
            'username' => 'admin_identitas_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Identitas',
            'no_hp' => '081234567890',
            'nik' => '1234567890123456',
            'alamat' => 'Bandung',
        ]);
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Identitas',
            'keterangan' => 'Uji status identitas',
        ]);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Alat Identitas',
            'kode_alat' => 'ALT-IDENTITAS-1',
            'stok_total' => 1,
            'stok_tersedia' => 1,
            'harga_sewa' => 50000,
            'kondisi' => 'baik',
            'keterangan' => 'Uji status identitas',
        ]);
        $rental = Rental::create([
            'kode_rental' => 'RNT-'.date('Ymd').'-IDENTITAS',
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $admin->id,
            'tgl_rental' => now()->subDay()->toDateString(),
            'tgl_kembali' => now()->toDateString(),
            'tgl_kembali_aktual' => now()->toDateString(),
            'total_bayar' => 50000,
            'identitas_dititipkan' => true,
            'identitas_dikembalikan' => false,
            'status' => 'dikembalikan',
        ]);
        DetailRental::create([
            'rental_id' => $rental->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
            'harga_sewa' => 50000,
            'subtotal' => 50000,
        ]);

        $this->withSession(['user' => $admin])
            ->get('/pengembalian')
            ->assertOk()
            ->assertSee('Edit Identitas')
            ->assertSee('Masih dititipkan');

        $this->withSession(['user' => $admin])
            ->put('/pengembalian/'.$rental->id.'/identitas', [
                'identitas_dikembalikan' => '1',
            ])
            ->assertRedirect('/pengembalian')
            ->assertSessionHas('success', 'Status identitas diperbarui: kartu sudah dikembalikan ke pelanggan.');

        $this->assertDatabaseHas('rental', [
            'id' => $rental->id,
            'status' => 'dikembalikan',
            'identitas_dititipkan' => 1,
            'identitas_dikembalikan' => 1,
        ]);
    }

    public function test_accessories_are_free_with_main_rental_and_sales_are_counted(): void
    {
        $admin = User::create([
            'nama' => 'Admin Item Campuran',
            'username' => 'admin_item_campuran_'.time(),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Item Campuran',
            'no_hp' => '085123456789',
            'nik' => '1234567890123499',
            'alamat' => 'Semarang',
        ]);
        $kategori = Kategori::create([
            'nama_kategori' => 'Kategori Item Campuran',
            'keterangan' => 'Uji sewa gratis dan barang jual',
        ]);

        $alatUtama = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Tenda Utama',
            'kode_alat' => 'TND-MIX-1',
            'stok_total' => 2,
            'stok_tersedia' => 2,
            'harga_sewa' => 50000,
            'kondisi' => 'baik',
        ]);
        $aksesoris = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Mug',
            'kode_alat' => 'MUG-MIX-1',
            'stok_total' => 5,
            'stok_tersedia' => 5,
            'harga_sewa' => 5000,
            'jenis_alat' => 'aksesoris',
            'kondisi' => 'baik',
        ]);
        $barangJual = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Hand Warmer',
            'kode_alat' => 'HW-MIX-1',
            'stok_total' => 3,
            'stok_tersedia' => 3,
            'harga_sewa' => 0,
            'harga_jual' => 15000,
            'jenis_alat' => 'jual',
            'kondisi' => 'baik',
        ]);

        $tanggalKembali = now()->addDay()->toDateString();
        $this->withSession(['user' => $admin])
            ->post('/rental', [
                'jenis_transaksi' => 'langsung',
                'pelanggan_id' => $pelanggan->id,
                'tgl_rental' => now()->toDateString(),
                'tgl_kembali' => $tanggalKembali,
                'alat' => [
                    $alatUtama->id => 1,
                    $aksesoris->id => 1,
                    $barangJual->id => 1,
                ],
            ])
            ->assertRedirect('/rental');

        $campuran = Rental::latest('id')->firstOrFail();
        $this->assertSame(65000, $campuran->total_bayar);
        $this->assertDatabaseHas('detail_rental', [
            'rental_id' => $campuran->id,
            'alat_id' => $aksesoris->id,
            'jenis_item' => 'gratis',
            'subtotal' => 0,
        ]);
        $this->assertDatabaseHas('detail_rental', [
            'rental_id' => $campuran->id,
            'alat_id' => $barangJual->id,
            'jenis_item' => 'jual',
            'harga_sewa' => 15000,
            'subtotal' => 15000,
        ]);

        $this->withSession(['user' => $admin])
            ->post('/rental', [
                'jenis_transaksi' => 'langsung',
                'pelanggan_id' => $pelanggan->id,
                'tgl_rental' => now()->toDateString(),
                'tgl_kembali' => $tanggalKembali,
                'alat' => [$aksesoris->id => 1],
            ])
            ->assertRedirect('/rental');

        $aksesorisSendiri = Rental::latest('id')->firstOrFail();
        $this->assertSame(5000, $aksesorisSendiri->total_bayar);
        $this->assertDatabaseHas('detail_rental', [
            'rental_id' => $aksesorisSendiri->id,
            'alat_id' => $aksesoris->id,
            'jenis_item' => 'sewa',
            'subtotal' => 5000,
        ]);

        $this->withSession(['user' => $admin])
            ->post('/rental', [
                'jenis_transaksi' => 'langsung',
                'pelanggan_id' => $pelanggan->id,
                'tgl_rental' => now()->toDateString(),
                'tgl_kembali' => $tanggalKembali,
                'alat' => [$barangJual->id => 1],
            ])
            ->assertRedirect('/rental');

        $penjualanSaja = Rental::latest('id')->firstOrFail();
        $this->assertSame('dikembalikan', $penjualanSaja->status);
        $this->assertSame(15000, $penjualanSaja->total_bayar);
        $this->assertDatabaseHas('detail_rental', [
            'rental_id' => $penjualanSaja->id,
            'alat_id' => $barangJual->id,
            'jenis_item' => 'jual',
        ]);
        $this->withSession(['user' => $admin])
            ->get('/pengembalian')
            ->assertOk()
            ->assertDontSee($penjualanSaja->kode_rental);

        $this->withSession(['user' => $admin])
            ->get('/laporan/rental')
            ->assertOk()
            ->assertSee('Gratis')
            ->assertSee('Dijual')
            ->assertSee('Penjualan selesai')
            ->assertViewHas('totalRental', 2)
            ->assertViewHas('laporanPemasukan', fn ($rows) => $rows->sum('jumlah_rental') === 2
                && $rows->sum('total_bayar') === 85000);

        $this->withSession(['user' => $admin])
            ->get(route('rental.availability', [
                'tgl_rental' => now()->addDay()->toDateString(),
                'tgl_kembali' => now()->addDays(2)->toDateString(),
            ]))
            ->assertJsonPath('availability.'.$barangJual->id, 1);
    }
}
