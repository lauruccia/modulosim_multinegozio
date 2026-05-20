<?php
if (($_GET['key'] ?? '') !== 'sharers2026') { http_response_code(403); die('Accesso negato.'); }

$publicHtml  = '/home/shars/public_html';
$storageLink = $publicHtml . '/storage';
$storageReal = '/home/shars/repositories/modulosim_multinegozio/storage/app/public';
$testImage   = $storageReal . '/stores/logos';

echo '<pre>';
echo "=== DIAGNOSTICA STORAGE ===\n\n";

echo "1. Symlink {$storageLink} esiste?         " . (file_exists($storageLink) ? '✅ SÌ' : '❌ NO') . "\n";
echo "2. È un symlink?                           " . (is_link($storageLink) ? '✅ SÌ' : '❌ NO') . "\n";
if (is_link($storageLink)) {
    echo "   Punta a: " . readlink($storageLink) . "\n";
}
echo "3. Directory target esiste?                " . (is_dir($storageReal) ? '✅ SÌ' : '❌ NO') . "\n";
echo "4. Cartella logos esiste?                  " . (is_dir($testImage) ? '✅ SÌ' : '❌ NO') . "\n";

echo "\n--- File in logos/ ---\n";
if (is_dir($testImage)) {
    $files = scandir($testImage);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $size = filesize($testImage . '/' . $f);
        $perms = substr(sprintf('%o', fileperms($testImage . '/' . $f)), -4);
        echo "  $f  ($size bytes, perms: $perms)\n";
    }
} else {
    echo "  (cartella non trovata)\n";
}

echo "\n--- Test scrittura ---\n";
$testFile = $storageReal . '/test_write.txt';
if (file_put_contents($testFile, 'ok')) {
    echo "Scrittura in storage: ✅ OK\n";
    // Testa accesso web
    echo "URL test: https://shars.it/storage/test_write.txt\n";
    echo "(apri quell'URL per verificare che il web server segua il symlink)\n";
    unlink($testFile);
} else {
    echo "Scrittura in storage: ❌ FALLITA (problema permessi)\n";
}

echo "\n--- .htaccess ---\n";
$htaccess = $publicHtml . '/.htaccess';
if (file_exists($htaccess)) {
    echo file_get_contents($htaccess);
} else {
    echo "(nessun .htaccess in public_html)\n";
}

echo '</pre>';
