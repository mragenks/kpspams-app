<?php
// Token rahasia
$token = $_GET['token'] ?? '';
if ($token !== 'SuperRahasiaERP2026') {
    die('Akses Ditolak!');
}

echo "<pre>Memulai proses deployment...\n\n";

$basePath = '/home/kpspams1/kpspams-app';
$phpPath = '/opt/alt/php82/usr/bin/php';

// 1. Pindah ke folder repo dan lakukan git pull
echo "1. Menarik kode terbaru dari GitHub:\n";
echo shell_exec("cd $basePath && git pull origin main 2>&1");

// 2. Eksekusi Artisan Migrate & Cache
echo "\n2. Mengupdate Database & Cache:\n";
echo shell_exec("cd $basePath && $phpPath artisan migrate --force 2>&1");
echo shell_exec("cd $basePath && $phpPath artisan config:cache 2>&1");
echo shell_exec("cd $basePath && $phpPath artisan route:cache 2>&1");
echo shell_exec("cd $basePath && $phpPath artisan view:clear 2>&1");

// 3. Eksekusi NPM Install & Build (Load NVM environment terlebih dahulu)
echo "\n3. Build Aset Frontend (NPM & Vite):\n";
$npmCommand = "export NVM_DIR=\"/home/kpspams1/.nvm\" && [ -s \"\$NVM_DIR/nvm.sh\" ] && \. \"\$NVM_DIR/nvm.sh\" && cd $basePath && npm install 2>&1 && npm run build 2>&1";
echo shell_exec($npmCommand);

echo "\nDeployment Selesai!</pre>";