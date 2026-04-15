<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PembayaranBaruNotification extends Notification
{
    use Queueable;

    protected $pembayaran;
    protected $pelanggan;

    public function __construct($pembayaran, $pelanggan)
    {
        $this->pembayaran = $pembayaran;
        $this->pelanggan = $pelanggan;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        // Sesuaikan nama field 'nominal' dengan field di tabel pembayaran kamu (bisa 'jumlah_bayar' atau 'nominal')
        $nominal = $this->pembayaran->nominal ?? $this->pembayaran->jumlah_bayar ?? 0;

        return [
            'tipe' => 'pembayaran_baru',
            'title' => 'Pembayaran Baru: ' . $this->pelanggan->nama_pelanggan,
            'message' => $this->pelanggan->nama_pelanggan . ' telah mengirimkan bukti pembayaran sebesar Rp ' . number_format($nominal, 0, ',', '.') . '. Menunggu konfirmasi Anda.',

            // Arahkan admin langsung ke halaman pembayaran saat notifikasi diklik
            'url' => route('admin.pembayaran.index')
        ];
    }
}
