<?php
// FILE TEMPORANEO - ELIMINARE DOPO L'USO
// Forza la cancellazione del route cache + config cache + view cache senza rigenerarli.
// Accedere via browser: https://shars.it/fix-404.php

$base = '/home/shars/repositories/modulosim_multinegozio';
$cacheDir = $base . '/bootstrap/cache';

echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;font-size:13px;">';

// 1) Cancella i file di cache che possono causare problemi di routing/binding
$targets = [
    'routes-v7.php',
    'routes-v6.php',
    'config.php',
    'services.php',
    'packages.php',
    'events.php',
];

foreach ($targets as $f) {
    $path = $cacheDir . '/' . $f;
    if (file_exists($path)) {
        if (@unlink($path)) {
            echo "OK   eliminato: $f\n";
        } else {
            echo "FAIL impossibile eliminare: $f (chmod " . substr(sprintf('%o', fileperms($path)), -4) . ")\n";
        }
    } else {
        echo "skip non presente: $f\n";
    }
}

// 2) Cancella view cache
$viewDir = $base . '/storage/framework/views';
if (is_dir($viewDir)) {
    $cleared = 0;
    foreach (glob($viewDir . '/*.php') as $vf) {
        if (@unlink($vf)) $cleared++;
    }
    echo "OK   view cache: $cleared file cancellati\n";
}

// 3) Tenta anche optimize:clear via shell come fallback
$php = '/usr/local/bin/php';
if (file_exists($php)) {
    echo "\n=== optimize:clear ===\n";
    echo htmlspecialchars(shell_exec("$php $base/artisan optimize:clear 2>&1") ?? '(nessun output)');
}

echo "\n=== Stato finale ===\n";
foreach ($targets as $f) {
    echo "$f: " . (file_exists($cacheDir . '/' . $f) ? 'PRESENTE' : 'assente') . "\n";
}

echo "\n";
echo "Ora vai a https://shars.it/negozi/4tacche/attivazione/dati e verifica.\n";
echo "Poi ELIMINA QUESTO FILE dal server (fix-404.php).\n";
echo '</pre>';
