<?php
if (($_GET['key'] ?? '') !== 'sharers2026') { die('no'); }

$publicStorage = '/home/shars/public_html/storage';
$sourceLogos   = '/home/shars/repositories/modulosim_multinegozio/storage/app/public/stores/logos';

echo '<pre>';

// 1. Elimina ricorsivamente tutto public_html/storage (senza seguire symlink)
function deleteDir(string $path): void {
    if (is_link($path)) { unlink($path); return; }
    if (!is_dir($path)) { unlink($path); return; }
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        deleteDir($path . '/' . $item);
    }
    rmdir($path);
}

if (file_exists($publicStorage) || is_link($publicStorage)) {
    deleteDir($publicStorage);
    echo "🗑  public_html/storage/ eliminata.\n";
}

// 2. Crea struttura pulita
mkdir($publicStorage . '/stores/logos', 0755, true);
echo "📁 Creata public_html/storage/stores/logos/\n";

// 3. Copia solo i file jpg/png dai logos originali
$copied = 0;
if (is_dir($sourceLogos)) {
    foreach (scandir($sourceLogos) as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','svg','webp','ico'])) {
            copy($sourceLogos . '/' . $file, $publicStorage . '/stores/logos/' . $file);
            echo "  ✅ Copiato: $file\n";
            $copied++;
        }
    }
}
echo "\n$copied file copiati.\n";
echo "\n✅ Fatto! Ora aggiungi al .env del server:\nPUBLIC_DISK_ROOT=/home/shars/public_html/storage\n";
echo "\n⚠️  ELIMINA questo file!\n";
echo '</pre>';
