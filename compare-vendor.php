<?php
$mapFile = __DIR__ . '/../vendor-map.json';
$vendorDir = __DIR__ . '/../vendor';

if (!file_exists($mapFile)) {
    die("<h3 style='color:red;'>ບໍ່ພົບໄຟລ໌ vendor-map.json! ກະລຸນາອັບໂຫຼດໄຟລ໌ vendor-map.json ຂຶ້ນມາກ່ອນ.</h3>");
}

$localData = json_decode(file_get_contents($mapFile), true);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vendor Comparison</title>
    <style>
        body { font-family: Phetsarath OT, sans-serif, Tahoma; padding: 20px; background: #f8fafc; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 8px 12px; border: 1px solid #cbd5e1; text-align: left; font-size: 13px; }
        th { background: #f1f5f9; }
        .missing { background: #fee2e2; color: #991b1b; }
        .incomplete { background: #fef3c7; color: #92400e; }
        .ok { background: #dcfce7; color: #166534; display: none; } /* ເຊື່ອງອັນທີ່ຄົບ ເພື່ອໃຫ້ເບິ່ງງ່າຍ */
    </style>
</head>
<body>
    <h2>ລາຍຊື່ Package ທີ່ສູນຫາຍ ຫຼື ໄຟລ໌ບໍ່ຄົບ (ເທິງ Server)</h2>
    <p><i>(ລາຍການທີ່ຄົບຖ້ວນແລ້ວຈະຖືກເຊື່ອງໄວ້ ເພື່ອໃຫ້ທ່ານເຫັນສະເພາະອັນທີ່ມີບັນຫາ)</i></p>

    <table>
        <tr>
            <th>ຊື່ Package (ທີ່ຢູ່ໂຟລເດີ)</th>
            <th>ຈຳນວນໃນເຄື່ອງຄອມ</th>
            <th>ຈຳນວນເທິງ Server</th>
            <th>ສະຖານະ</th>
        </tr>
        <?php
        $missingCount = 0;
        $incompleteCount = 0;

        foreach ($localData as $path => $localFiles) {
            $serverPath = $vendorDir . '/' . $path;

            if (!is_dir($serverPath)) {
                $serverFiles = 0;
                $statusClass = 'missing';
                $statusText = '❌ ສູນຫາຍ (ບໍ່ມີໂຟລເດີນີ້)';
                $missingCount++;
            } else {
                $serverFiles = 0;
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($serverPath, FilesystemIterator::SKIP_DOTS));
                foreach ($it as $f) { if ($f->isFile()) $serverFiles++; }

                if ($serverFiles < $localFiles) {
                    $statusClass = 'incomplete';
                    $statusText = '⚠️ ບໍ່ຄົບ (ຂາດ ' . ($localFiles - $serverFiles) . ' ໄຟລ໌)';
                    $incompleteCount++;
                } else {
                    $statusClass = 'ok';
                    $statusText = '✅ ສົມບູນ';
                }
            }

            if ($statusClass !== 'ok') {
                echo "<tr class='$statusClass'>";
                echo "<td><b>vendor/$path</b></td>";
                echo "<td>$localFiles</td>";
                echo "<td>$serverFiles</td>";
                echo "<td>$statusText</td>";
                echo "</tr>";
            }
        }
        ?>
    </table>
    <br>
    <h3>ສະຫຼຸບ: ໂຟລເດີທີ່ສູນຫາຍ: <span style="color:red;"><?= $missingCount ?></span>, ໄຟລ໌ບໍ່ຄົບ: <span style="color:orange;"><?= $incompleteCount ?></span></h3>
</body>
</html>