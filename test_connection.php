<?php
try {
    // Test với computer name
    $pdo = new PDO(
        "sqlsrv:Server=DESKTOP-ICMA202,1433;Database=master",
        "sa",
        "29kingdragon"
    );
    echo "Connection 1 SUCCESS\n";
} catch (Exception $e) {
    echo "Connection 1 FAILED: " . $e->getMessage() . "\n";
}

try {
    // Test với localhost
    $pdo = new PDO(
        "sqlsrv:Server=localhost,1433;Database=master",
        "sa",
        "29kingdragon"
    );
    echo "Connection 2 SUCCESS\n";
} catch (Exception $e) {
    echo "Connection 2 FAILED: " . $e->getMessage() . "\n";
}

try {
    // Test với 127.0.0.1
    $pdo = new PDO(
        "sqlsrv:Server=127.0.0.1,1433;Database=master",
        "sa",
        "29kingdragon"
    );
    echo "Connection 3 SUCCESS\n";
} catch (Exception $e) {
    echo "Connection 3 FAILED: " . $e->getMessage() . "\n";
}
?>
