<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TagihanBaruNotification extends Notification
{
    use Queueable;

    protected $tagihan;
    protected $bulan;
    protected $tahun;

    public function __construct($tagihan, $bulan, $tahun)
    {
        $this->tagihan = $tagihan;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $namaBulan = \Carbon\Carbon::create()->month($this->bulan)->isoFormat('MMMM');

        return [
            'tipe' => 'tagihan_baru',
            'title' => 'Tagihan Air ' . $namaBulan . ' ' . $this->tahun,
            'message' => 'Tagihan air Anda bulan ini sebesar Rp ' . number_format($this->tagihan->total_tagihan, 0, ',', '.') . ' sudah terbit. Silakan lakukan pembayaran.',
            'url' => route('pelanggan.dashboard'),
            'tagihan_id' => $this->tagihan->id
        ];
    }
}
