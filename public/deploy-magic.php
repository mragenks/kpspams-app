<?php
// Token rahasia
$token = $_GET['token'] ?? '';
if ($token !== 'SuperRahasiaERP2026') {
    die('Akses Ditolak!');
}

echo "<pre>Memulai proses deployment...\n\n";

// 1. Pindah ke folder repo dan lakukan git pull
echo "1. Menarik kode terbaru dari GitHub:\n";
echo shell_exec('cd /home/kpspams1/kpspams-app && git pull origin main 2>&1'); //

// 2. Eksekusi Artisan Migrate & Cache (Jalur disesuaikan menjadi flat)
echo "\n2. Mengupdate Database & Cache:\n";
echo shell_exec('cd /home/kpspams1/kpspams-app && /opt/alt/php82/usr/bin/php artisan migrate --force 2>&1'); //
echo shell_exec('cd /home/kpspams1/kpspams-app && /opt/alt/php82/usr/bin/php artisan config:cache 2>&1'); //
echo shell_exec('cd /home/kpspams1/kpspams-app && /opt/alt/php82/usr/bin/php artisan route:cache 2>&1');
echo shell_exec('cd /home/kpspams1/kpspams-app && /opt/alt/php82/usr/bin/php artisan view:clear 2>&1');

echo "\nDeployment Selesai!</pre>";
