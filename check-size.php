<?php
$dir = __DIR__ . '/../vendor'; // ກວດສອບໂຟລເດີ vendor ທີ່ຢູ່ລະດັບດຽວກັນ
$size = 0;
$count = 0;

if (!is_dir($dir)) {
    die("ບໍ່ພົບໂຟລເດີ Vendor! ກະລຸນາກວດສອບວ່າວາງໄຟລ໌ນີ້ຖືກບ່ອນຫຼືບໍ່.");
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $size += $file->getSize();
        $count++;
    }
}

echo "<h2>ຂໍ້ມູນໂຟລເດີ Vendor ເທິງ Server:</h2>";
echo "<b>ຂະໜາດລວມ:</b> " . number_format($size / 1048576, 2) . " MB <br>";
echo "<b>ຈຳນວນໄຟລ໌ທັງໝົດ:</b> " . number_format($count) . " ໄຟລ໌";
?>