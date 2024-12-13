<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "perfume";

// Tạo kết nối
$conn = new mysqli($host, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy giá trị start_date và end_date từ request
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$where_condition = "";
if ($start_date && !$end_date) {
    // Lọc theo 1 ngày (chỉ chọn start_date)
    $where_condition = "WHERE DATE(o.placed_on) = '" . $conn->real_escape_string($start_date) . "'";
} elseif ($start_date && $end_date) {
    // Lọc theo khoảng ngày (chọn cả start_date và end_date)
    $where_condition = "WHERE DATE(o.placed_on) BETWEEN '" . $conn->real_escape_string($start_date) . "' AND '" . $conn->real_escape_string($end_date) . "'";
}
$sql = "
    SELECT 
        DATE_FORMAT(o.placed_on, '%d/%m/%Y') AS placed_date, 
        SUM(oi.total_price) AS daily_revenue,
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.discount_fee ELSE 0 END) AS daily_discount,
        SUM(ie.import_price * oi.quantity) AS daily_import_cost,
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.total_price ELSE 0 END) AS actual_revenue,
        SUM(CASE WHEN o.status_order = 'Đã hủy' THEN oi.total_price ELSE 0 END) AS canceled_revenue,
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN ie.import_price * oi.quantity ELSE 0 END) AS import_cost_delivered,
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.total_price ELSE 0 END)
        - SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.discount_fee ELSE 0 END)
        - SUM(CASE WHEN o.status_order = 'Đã giao' THEN ie.import_price * oi.quantity ELSE 0 END) AS daily_profit
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN inventory_entries ie ON oi.product_id = ie.inventory_id
    $where_condition
    GROUP BY DATE(o.placed_on)
    ORDER BY o.placed_on ASC
";





$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$sql_canceled_rate = "
    SELECT 
        DATE_FORMAT(o.placed_on, '%d/%m/%Y') AS placed_date, 
        COUNT(o.order_id) AS total_orders, -- Tổng số đơn hàng trong ngày
        SUM(CASE WHEN o.status_order = 'Đã hủy' THEN 1 ELSE 0 END) AS canceled_orders, -- Tổng số đơn bị hủy
        ROUND(SUM(CASE WHEN o.status_order = 'Đã hủy' THEN 1 ELSE 0 END) / COUNT(o.order_id) * 100, 2) AS canceled_percentage -- Tỷ lệ hủy (%)
    FROM orders o
    GROUP BY DATE(o.placed_on) -- Gom nhóm theo ngày
    ORDER BY DATE(o.placed_on) ASC; -- Sắp xếp theo ngày
";

$result_canceled_rate = $conn->query($sql_canceled_rate);

$canceled_rate_data = [];
if ($result_canceled_rate->num_rows > 0) {
    while ($row = $result_canceled_rate->fetch_assoc()) {
        $canceled_rate_data[] = [
            'placed_date' => $row['placed_date'],
            'total_orders' => $row['total_orders'], // Tổng số đơn hàng
            'canceled_orders' => $row['canceled_orders'], // Tổng số đơn bị hủy
            'canceled_percentage' => $row['canceled_percentage'], // Tỷ lệ hủy đơn (%)
        ];
    }
}


?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê doanh thu theo ngày</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" type="text/css" href="../CSS/revenue.css?v=1.1 <?php echo time(); ?>">

</head>

<body>
    <?php include '../admin/admin_header.php'; ?>
    <h1 class="revenue_title">
        Thống kê doanh thu theo ngày
    </h1>
    <section class="revenue">
        <form method="GET" action="" class="filter-form">
            <div class="filter-type">
                <select name="report_type" id="report_type" onchange="location = this.value;">
                    <option value="revenue_date.php?report_type=daily"
                        <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'daily') ? 'selected' : '' ?>>Thống
                        kê
                        doanh thu theo ngày</option>
                    <option value="revenue_month.php?report_type=monthly"
                        <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'monthly') ? 'selected' : '' ?>>
                        Thống kê
                        doanh thu theo tháng</option>
                    <option value="revenue_year.php?report_type=yearly"
                        <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'yearly') ? 'selected' : '' ?>>Thống
                        kê
                        doanh thu theo năm</option>
                </select>
            </div>
            <div class="filter-date">
                <label for="start_date">Từ ngày:</label>
                <input type="date" name="start_date" id="start_date"
                    value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>" onchange="this.form.submit()">

                <label for="end_date">Đến ngày:</label>
                <input type="date" name="end_date" id="end_date"
                    value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>" onchange="this.form.submit()">
            </div>
        </form>



        <h3 class="revenue_table_title">
            Bảng thống kê chi tiết
        </h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Ngày</th>
                        <th>Doanh thu tạm tính (VNĐ)</th>
                        <th>Doanh thu thực tế (VNĐ)</th>
                        <th>Chi phí nhập hàng (VNĐ)</th>
                        <th>Chi phí nhập hàng theo đơn giao (VNĐ)</th>
                        <th>Doanh thu bị hủy (VNĐ)</th>
                        <th>Giảm giá (VNĐ)</th>
                        <th>Lợi nhuận (VNĐ)</th>

                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)) : ?>
                    <?php $stt = 1; ?>
                    <?php foreach ($data as $row) : ?>
                    <tr>
                        <td><?= $stt++; ?></td>
                        <td><?= $row['placed_date']; ?></td>
                        <td><?= number_format($row['daily_revenue'], 2); ?></td>
                        <td><?= number_format($row['actual_revenue'], 2); ?></td>
                        <td><?= number_format($row['daily_import_cost'], 2); ?></td>
                        <td><?= number_format($row['import_cost_delivered'], 2); ?></td>
                        <td><?= number_format($row['canceled_revenue'], 2); ?></td>
                        <td><?= number_format($row['daily_discount'], 2); ?></td>
                        <td><?= number_format($row['daily_profit'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="9">Không có dữ liệu</td>
                    </tr>
                    <?php endif; ?>
                </tbody>



            </table>
        </div>


    </section>
    <div class="line3"></div>

    <h1 class="revenue_title" style="margin-top: 3rem;">
        Biểu đồ thống kê chi tiết
    </h1>
    <!-- Hiển thị biểu đồ -->
    <canvas id="orderChart"></canvas>
    <script>
    // Lấy dữ liệu từ PHP
    const data = <?= json_encode($data); ?>; // Dữ liệu doanh thu, lợi nhuận
    const canceledRateData = <?= json_encode($canceled_rate_data); ?>; // Dữ liệu tỷ lệ hủy đơn

    // Khởi tạo mảng để lưu dữ liệu
    const dates = [];
    const profits = [];
    const canceledPercentages = [];

    // Xử lý dữ liệu lợi nhuận từ mảng `data`
    data.forEach(row => {
        dates.push(row.placed_date); // Lấy ngày từ dữ liệu doanh thu
        profits.push(parseFloat(row.daily_profit)); // Lợi nhuận
    });

    // Xử lý dữ liệu tỷ lệ hủy đơn từ mảng `canceledRateData`
    canceledRateData.forEach(row => {
        if (!dates.includes(row.placed_date)) {
            dates.push(row.placed_date); // Đảm bảo tất cả các ngày đều được hiển thị
        }
        canceledPercentages.push(parseFloat(row.canceled_percentage)); // Tỷ lệ hủy đơn
    });

    // Lấy ngữ cảnh của canvas để vẽ biểu đồ
    const ctx = document.getElementById('orderChart').getContext('2d');

    // Tạo biểu đồ với Chart.js
    new Chart(ctx, {
        type: 'line', // Loại biểu đồ
        data: {
            labels: dates, // Trục x là ngày
            datasets: [{
                    label: 'Lợi Nhuận', // Dữ liệu lợi nhuận
                    data: profits,
                    backgroundColor: 'rgba(88, 214, 141, 0.2)', // Màu nền xanh lá pastel
                    borderColor: 'rgba(30, 132, 73, 1)', // Màu đường xanh lá đậm
                    borderWidth: 3,
                    fill: true, // Tô nền phía dưới
                    tension: 0.4, // Đường cong mượt
                },
                {
                    label: 'Tỷ lệ hủy đơn (%)', // Dữ liệu tỷ lệ hủy đơn
                    data: canceledPercentages,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Màu nền xanh nhạt
                    borderColor: 'rgba(75, 192, 192, 1)', // Màu đường xanh đậm
                    borderWidth: 2,
                    fill: false, // Không tô nền
                    tension: 0.3, // Đường cong nhẹ
                    yAxisID: 'percentage', // Trục y riêng cho tỷ lệ hủy
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            const label = tooltipItem.dataset.label;
                            const value = tooltipItem.raw;

                            // Hiển thị đúng định dạng
                            if (label === 'Lợi Nhuận') {
                                return `${label}: ${new Intl.NumberFormat('vi-VN').format(value)} VNĐ`;
                            } else if (label === 'Tỷ lệ hủy đơn (%)') {
                                return `${label}: ${value} %`;
                            }
                        },
                    },
                    backgroundColor: 'rgba(0, 0, 0, 0.8)', // Nền tooltip đen
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                },
                legend: {
                    labels: {
                        font: {
                            size: 14,
                            family: 'Arial',
                        },
                        color: '#333333', // Màu chữ của chú thích
                    },
                },
            },
            scales: {
                y: { // Trục y chính cho lợi nhuận
                    type: 'linear',
                    position: 'left',
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                        },
                        color: '#333333',
                        font: {
                            size: 12,
                        },
                    },
                    grid: {
                        color: '#eeeeee',
                    },
                },
                percentage: { // Trục y riêng cho tỷ lệ hủy đơn
                    type: 'linear',
                    position: 'right',
                    ticks: {
                        callback: function(value) {
                            return value + ' %';
                        },
                        color: '#333333',
                        font: {
                            size: 12,
                        },
                    },
                    grid: {
                        color: '#eeeeee',
                    },
                },
                x: { // Trục x
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0,
                        color: '#333333',
                        font: {
                            size: 12,
                        },
                    },
                    grid: {
                        color: '#eeeeee',
                    },
                },
            },
        },
    });
    </script>



</body>

</html>