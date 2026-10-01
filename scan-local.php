<?php
$vendorDir = __DIR__ . '/vendor';
$data = [];

// ອ່ານໂຟລເດີລະດັບທີ 1 ແລະ 2 ຂອງ vendor
$folders = glob($vendorDir . '/*', GLOB_ONLYDIR);
foreach ($folders as $f1) {
    $name1 = basename($f1);
    if ($name1 === 'bin') continue;

    $subFolders = glob($f1 . '/*', GLOB_ONLYDIR);
    $targetFolders = (empty($subFolders) || $name1 === 'composer') ? [$f1] : $subFolders;

    foreach ($targetFolders as $target) {
        $relPath = str_replace($vendorDir . DIRECTORY_SEPARATOR, '', $target);
        $relPath = str_replace('\\', '/', $relPath); // ສຳລັບ Windows

        $fileCount = 0;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->isFile()) $fileCount++;
        }
        $data[$relPath] = $fileCount;
    }
}

file_put_contents(__DIR__ . '/vendor-map.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "<h3>ສຳເລັດ! ໄດ້ສ້າງໄຟລ໌ vendor-map.json ຮຽບຮ້ອຍແລ້ວ (ມີທັງໝົດ " . count($data) . " Packages).</h3>";