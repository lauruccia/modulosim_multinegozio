<?php
if (($_GET['key'] ?? '') !== 'sharers2026') { http_response_code(403); die('Accesso negato.'); }

$publicHtml  = '/home/shars/public_html';
$storageLink = $publicHtml . '/storage';
$oldTarget   = '/home/shars/repositories/modulosim_multinegozio/storage/app/public';
$newStorage  = $publicHtml . '/storage'; // sarà una vera cartella

echo '<pre>';

// 1. Rimuovi il symlink esistente
if (is_link($storageLink)) {
    unlink($storageLink);
    echo "🗑  Symlink rimosso.\n";
}

// 2. Crea la vera cartella storage/ in public_html
if (!is_dir($newStorage)) {
    mkdir($newStorage, 0755, true);
    echo "📁 Cartella public_html/storage/ creata.\n";
} else {
    echo "📁 Cartella public_html/storage/ già esistente.\n";
}

// 3. Copia i file esistenti da storage/app/public → public_html/storage/
function copyDir($src, $dst) {
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    foreach (scandir($src) as $file) {
        if ($file === '.' || $file === '..') continue;
        $s = $src . '/' . $file;
        $d = $dst . '/' . $file;
        if (is_dir($s)) copyDir($s, $d);
        else copy($s, $d);
    }
}

if (is_dir($oldTarget)) {
    copyDir($oldTarget, $newStorage);
    echo "📋 File copiati da storage/app/public → public_html/storage/\n";
}

// 4. Verifica
$logos = $newStorage . '/stores/logos';
echo "\nFile in public_html/storage/stores/logos/:\n";
if (is_dir($logos)) {
    foreach (scandir($logos) as $f) {
        if ($f === '.' || $f === '..') echo "  ✅ $f\n";
    }
} else {
    echo "  (nessun file — verranno salvati qui dai prossimi upload)\n";
}

echo "\n✅ Fatto! Ora aggiungi questa riga al file .env del server:\n";
echo "PUBLIC_DISK_ROOT=/home/shars/public_html/storage\n";
echo "\n⚠️  ELIMINA questo file dopo l'uso!\n";
echo '</pre>';
