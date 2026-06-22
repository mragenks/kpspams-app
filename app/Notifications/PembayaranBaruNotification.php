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
        $nominal = $this->pembayaran->nominal_bayar ?? 0;

        return [
            'tipe' => 'pembayaran_baru',
            'title' => 'Pembayaran Baru: ' . $this->pelanggan->nama_pelanggan,
            'message' => $this->pelanggan->nama_pelanggan . ' telah mengirimkan bukti pembayaran sebesar Rp ' . number_format($nominal, 0, ',', '.') . '. Menunggu konfirmasi Anda.',

            'url' => route('admin.pembayaran.show', $this->pembayaran->tagihan_id),
            'tagihan_id' => $this->pembayaran->tagihan_id,
            'pembayaran_id' => $this->pembayaran->id
        ];
    }
}
