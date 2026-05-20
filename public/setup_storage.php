<?php
/**
 * Script temporaneo — esegui UNA volta poi ELIMINA questo file.
 * Apri: https://shars.it/setup_storage.php?key=sharers2026
 */
if (($_GET['key'] ?? '') !== 'sharers2026') {
    http_response_code(403);
    die('Accesso negato.');
}

// Percorso corretto del repository Laravel
$projectRoot = '/home/shars/repositories/modulosim_multinegozio';
$target      = $projectRoot . '/storage/app/public';
$link        = __DIR__ . '/storage';

echo '<pre>';
echo "Project root: $projectRoot\n";
echo "Target:       $target\n";
echo "Link:         $link\n\n";

// 1. Crea storage/app/public se non esiste
if (!is_dir($target)) {
    if (mkdir($target, 0775, true)) {
        echo "✅ Cartella storage/app/public creata.\n";
    } else {
        echo "❌ Impossibile creare la cartella target. Creala manualmente in cPanel.\n";
    }
}

// 2. Rimuovi il vecchio symlink sbagliato se esiste
if (is_link($link)) {
    $old = readlink($link);
    unlink($link);
    echo "🗑  Vecchio symlink rimosso (puntava a: $old).\n";
} elseif (file_exists($link)) {
    rename($link, __DIR__ . '/storage_old');
    echo "⚠️  Cartella storage esistente rinominata in storage_old.\n";
}

// 3. Crea il symlink corretto
if (symlink($target, $link)) {
    echo "✅ Symlink creato: $link → $target\n";
} else {
    echo "❌ Errore nella creazione del symlink.\n";
}

// 4. Verifica
if (is_link($link) && is_dir(readlink($link))) {
    echo "\n✅ Verifica OK — storage accessibile correttamente.\n";
} else {
    echo "\n⚠️  Verifica fallita — la cartella target non è raggiungibile.\n";
}

echo "\n⚠️  IMPORTANTE: elimina questo file dopo l'uso!\n";
echo '</pre>';
