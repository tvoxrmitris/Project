<?php
// Kết nối cơ sở dữ liệu
include '../connection/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $inventory_id = isset($_GET['inventory_id']) ? intval($_GET['inventory_id']) : 0;

    if ($inventory_id > 0) {
        $query = "SELECT quantity_stock FROM inventory_entries WHERE inventory_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $inventory_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo json_encode(['quantity_stock' => $row['quantity_stock']]);
        } else {
            echo json_encode(['quantity_stock' => 0]); // Không tìm thấy
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Invalid inventory_id']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}