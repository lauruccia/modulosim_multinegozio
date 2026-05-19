<?php
// FILE TEMPORANEO - ELIMINARE DOPO L'USO
// Accedere via browser: https://shars.it/artisan-run.php

$base = '/home/shars/repositories/modulosim_multinegozio';
$php  = '/usr/local/bin/php';

$commands = [
    'optimize:clear' => "$php $base/artisan optimize:clear",
    'route:cache'    => "$php $base/artisan route:cache",
    'view:cache'     => "$php $base/artisan view:cache",
];

echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;">';
foreach ($commands as $name => $cmd) {
    echo "\n=== $name ===\n";
    echo htmlspecialchars(shell_exec($cmd . ' 2>&1'));
}

echo "\n=== route:list (negozi) ===\n";
echo htmlspecialchars(shell_exec("$php $base/artisan route:list --path=negozi 2>&1"));

echo '</pre>';
echo '<p style="color:red;font-weight:bold">ELIMINA QUESTO FILE DAL SERVER DOPO L\'USO!</p>';
