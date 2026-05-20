<?php
if (($_GET['key'] ?? '') !== 'sharers2026') { die('no'); }
$link = '/home/shars/public_html/storage';
if (is_link($link)) {
    unlink($link);
    echo 'Symlink rimosso OK';
} else {
    echo 'Non è un symlink: ' . (file_exists($link) ? 'è una cartella reale' : 'non esiste');
}
