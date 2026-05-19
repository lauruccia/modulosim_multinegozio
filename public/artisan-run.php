<?php
// FILE TEMPORANEO - ELIMINARE DOPO L'USO
// Accedere via browser: https://shars.it/artisan-run.php

$base = '/home/shars/repositories/modulosim_multinegozio';
$php  = '/usr/local/bin/php';

$commands = [
    'optimize:clear' => "$php $base/artisan optimize:clear",
    'route:clear'    => "$php $base/artisan route:clear",
    'config:clear'   => "$php $base/artisan config:clear",
    'view:clear'     => "$php $base/artisan view:clear",
    'cache:clear'    => "$php $base/artisan cache:clear",
];

echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;font-size:13px;">';
foreach ($commands as $name => $cmd) {
    echo "\n=== $name ===\n";
    echo htmlspecialchars(shell_exec($cmd . ' 2>&1') ?? '(no output)');
}

echo "\n=== route:list --path=negozi ===\n";
echo htmlspecialchars(shell_exec("$php $base/artisan route:list --path=negozi 2>&1") ?? '');

echo "\n=== route:list --json (negozi grep) ===\n";
$json = shell_exec("$php $base/artisan route:list --json 2>&1");
if ($json) {
    $rows = json_decode($json, true);
    if (is_array($rows)) {
        foreach ($rows as $r) {
            if (isset($r['uri']) && str_contains($r['uri'], 'negozi')) {
                echo htmlspecialchars(json_encode($r, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)) . "\n";
            }
        }
    } else {
        echo htmlspecialchars(substr($json, 0, 2000));
    }
}

echo "\n=== Filesystem check ===\n";
echo "routes/web.php size: " . filesize("$base/routes/web.php") . " bytes\n";
echo "route cache exists: " . (file_exists("$base/bootstrap/cache/routes-v7.php") ? 'YES' : 'NO') . "\n";
echo "config cache exists: " . (file_exists("$base/bootstrap/cache/config.php") ? 'YES' : 'NO') . "\n";
echo "public_html/negozi exists: " . (file_exists('/home/shars/public_html/negozi') ? 'YES' : 'NO') . "\n";

echo "\n=== Last 80 lines of routes/web.php ===\n";
$lines = file("$base/routes/web.php");
$tail  = array_slice($lines, max(0, count($lines) - 80));
echo htmlspecialchars(implode('', $tail));

echo '</pre>';
echo '<p style="color:red;font-weight:bold">ELIMINA QUESTO FILE DAL SERVER DOPO L\'USO!</p>';
