<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\MeterController;
use App\Http\Controllers\Admin\PembayaranController;
use App\Http\Controllers\Admin\AkunKasController;
use App\Http\Controllers\Admin\ArusKasController;
use App\Http\Controllers\Admin\DepositController;
use App\Http\Controllers\Admin\InventarisController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Ketua\DashboardController as KetuaDashboardController;
use App\Http\Controllers\Ketua\LaporanController as KetuaLaporanController;
use App\Http\Controllers\Ketua\ArusKasController as KetuaArusKasController;
use App\Http\Controllers\Pelanggan\DashboardController as PelangganDashboardController;
use App\Http\Middleware\PreventBackHistory;

Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'ketua') {
            return redirect()->route('ketua.dashboard');
        } elseif ($role === 'pencatat') {
            return redirect()->route('pencatat.dashboard');
        } elseif ($role === 'pelanggan') {
            return redirect()->route('pelanggan.dashboard');
        }
    }
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'admin', 'prevent-back-history'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('pelanggan', PelangganController::class);
    Route::resource('meter', MeterController::class);

    // PEMBAYARAN ROUTES
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran', [PembayaranController::class, 'store'])->name('pembayaran.store');
    Route::get('/pembayaran/bayar-sekaligus', [PembayaranController::class, 'bayarSekaligusForm'])->name('pembayaran.bayar_sekaligus_form');
    Route::post('/pembayaran/bayar-sekaligus', [PembayaranController::class, 'bayarSekaligusStore'])->name('pembayaran.bayar_sekaligus_store');

    // RUTE KUSTOM KONFIRMASI DAN TOLAK HARUS DI ATAS rute {pembayaran} agar tidak dianggap sebagai ID
    Route::post('/pembayaran/konfirmasi/{id}', [PembayaranController::class, 'confirm'])->name('pembayaran.confirm');
    Route::delete('/pembayaran/tolak/{id}', [PembayaranController::class, 'reject'])->name('pembayaran.reject');

    // Rute SHOW dengan parameter {pembayaran} ditaruh di bawah agar tidak membajak rute kustom
    Route::get('/pembayaran/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayaran.show');

    // AKUN KAS & ARUS KAS
    Route::resource('akun-kas', AkunKasController::class)->parameters([
        'akun-kas' => 'akunKas'
    ]);
    Route::resource('arus-kas', ArusKasController::class)->parameters([
        'arus-kas' => 'arusKas'
    ]);
    Route::get('/mutasi', [ArusKasController::class, 'mutasi'])->name('mutasi.create');
    Route::post('/mutasi', [ArusKasController::class, 'storeMutasi'])->name('mutasi.store');

    // INVENTARIS
    Route::resource('inventaris', InventarisController::class)->parameters([
        'inventaris' => 'inventaris'
    ]);
    Route::post('/inventaris/{inventaris}/mutasi', [InventarisController::class, 'mutasiStok'])->name('inventaris.mutasi');

    // LAPORAN
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');
    Route::post('/laporan/arus-kas/excel', [LaporanController::class, 'exportExcelArusKas'])->name('laporan.aruskas.excel');
    Route::post('/laporan/tagihan/excel', [LaporanController::class, 'exportExcelTagihan'])->name('laporan.tagihan.excel');
    Route::post('/laporan/inventaris/excel', [LaporanController::class, 'exportExcelInventaris'])->name('laporan.inventaris.excel');
    Route::get('/laporan/aruskas/cetak-pdf', [LaporanController::class, 'exportPDF'])->name('laporan.aruskas.pdf');
    Route::get('/laporan/tagihan/cetak-pdf', [LaporanController::class, 'exportTagihanPDF'])->name('laporan.tagihan.pdf');
    Route::get('/laporan/inventaris/cetak-pdf', [LaporanController::class, 'exportInventarisPDF'])->name('laporan.inventaris.pdf');

    // USER MANAGEMENT
    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/user', [UserController::class, 'store'])->name('user.store');
    Route::get('/user/{user}', [UserController::class, 'show'])->name('user.show');
    Route::post('/user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('user.reset-password');
    Route::post('/user/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('user.toggle-status');
    Route::get('/user/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('user.edit');
    Route::put('/user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('user.update');

    //DEPOSIT ROUTES
    Route::get('/deposit', [DepositController::class, 'index'])->name('deposit.index');
    Route::post('/deposit', [DepositController::class, 'store'])->name('deposit.store');
    Route::post('/pembayaran/bayar-saldo/{tagihan}', [PembayaranController::class, 'payWithSaldo'])->name('pembayaran.bayar_saldo');

    // PENGATURAN TARIF
    Route::get('/pengaturan-tarif', [App\Http\Controllers\Admin\PengaturanTarifController::class, 'index'])->name('pengaturan-tarif.index');
    Route::post('/pengaturan-tarif', [App\Http\Controllers\Admin\PengaturanTarifController::class, 'update'])->name('pengaturan-tarif.update');
    Route::post('/pengaturan-tarif/unlock', [App\Http\Controllers\Admin\PengaturanTarifController::class, 'unlock'])->name('pengaturan-tarif.unlock');
    Route::post('/pengaturan-tarif/lock', [App\Http\Controllers\Admin\PengaturanTarifController::class, 'lock'])->name('pengaturan-tarif.lock');

    // ROUTE NOTIFIKASI
    Route::post('/notifikasi/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notif.markAllRead');
});

/*
|--------------------------------------------------------------------------
| Ketua Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'ketua', 'prevent-back-history'])->prefix('ketua')->name('ketua.')->group(function () {
    Route::get('/dashboard', [KetuaDashboardController::class, 'index'])->name('dashboard');

    // Laporan Ketua (Hanya Lihat & Cetak)
    Route::get('/laporan', [KetuaLaporanController::class, 'index'])->name('laporan.index');
    Route::post('/laporan/aruskas/excel', [KetuaLaporanController::class, 'exportExcelArusKas'])->name('laporan.aruskas.excel');
    Route::post('/laporan/tagihan/excel', [KetuaLaporanController::class, 'exportExcelTagihan'])->name('laporan.tagihan.excel');
    Route::post('/laporan/inventaris/excel', [KetuaLaporanController::class, 'exportExcelInventaris'])->name('laporan.inventaris.excel');

    // PDF Routes Ketua
    Route::get('/laporan/aruskas/cetak-pdf', [LaporanController::class, 'exportPDF'])->name('laporan.aruskas.pdf');
    Route::get('/laporan/tagihan/cetak-pdf', [LaporanController::class, 'exportTagihanPDF'])->name('laporan.tagihan.pdf');
    Route::get('/laporan/inventaris/cetak-pdf', [LaporanController::class, 'exportInventarisPDF'])->name('laporan.inventaris.pdf');

    Route::get('/arus-kas', [KetuaArusKasController::class, 'index'])->name('arus-kas.index');
});

/*
|--------------------------------------------------------------------------
| Pelanggan Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'pelanggan', 'prevent-back-history'])->prefix('pelanggan')->name('pelanggan.')->group(function () {
    Route::get('/dashboard', [PelangganDashboardController::class, 'index'])->name('dashboard');
    Route::post('/pembayaran/submit', [PelangganDashboardController::class, 'submitBukti'])->name('pembayaran.submit');
    Route::post('/pelanggan/submit-bukti', [App\Http\Controllers\Pelanggan\DashboardController::class, 'submitBukti'])->name('pelanggan.pembayaran.submit');

    // NOTIFIKASI PELANGGAN
    Route::post('/notifikasi/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notif.markAllRead');
    Route::get('/tagihan/{id}/surat', [\App\Http\Controllers\Pelanggan\DashboardController::class, 'suratTagihan'])->name('tagihan.surat');
});

/*
|--------------------------------------------------------------------------
| Pencatat Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'pencatat', 'prevent-back-history'])->prefix('pencatat')->name('pencatat.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Pencatat\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/meter', [\App\Http\Controllers\Pencatat\MeterController::class, 'index'])->name('meter.index');
    Route::get('/meter/create', [\App\Http\Controllers\Pencatat\MeterController::class, 'create'])->name('meter.create');
    Route::post('/meter', [\App\Http\Controllers\Pencatat\MeterController::class, 'store'])->name('meter.store');
});
