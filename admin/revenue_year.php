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

$startYear = isset($_GET['start_year']) ? (int)$_GET['start_year'] : date('Y'); // Mặc định là năm hiện tại
$endYear = isset($_GET['end_year']) && !empty($_GET['end_year']) ? (int)$_GET['end_year'] : $startYear; // Nếu không chọn thì endYear = startYear

// Cập nhật truy vấn SQL
$sql = "
    SELECT 
        DATE_FORMAT(o.placed_on, '%Y') AS placed_year, 
        SUM(oi.total_price) AS yearly_revenue,
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.discount_fee ELSE 0 END) AS yearly_discount, -- Giảm giá chỉ khi Đã giao
        (SELECT SUM(o2.shipping_fee) 
         FROM orders o2 
         WHERE DATE_FORMAT(o2.placed_on, '%Y') BETWEEN '$startYear' AND '$endYear'
         AND o2.status_order = 'Đã giao') AS total_shipping_fee, -- Phí vận chuyển chỉ khi Đã giao
        SUM(ie.import_price * oi.quantity) AS yearly_import_cost, -- Tổng chi phí nhập hàng (tất cả đơn)
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN ie.import_price * oi.quantity ELSE 0 END) AS import_cost_delivered, -- Chi phí nhập hàng theo đơn giao
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.quantity * oi.price ELSE 0 END) AS actual_revenue, -- Doanh thu thực tế
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.quantity * oi.price ELSE 0 END) 
            - SUM(CASE WHEN o.status_order = 'Đã giao' THEN ie.import_price * oi.quantity ELSE 0 END)
            - SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.discount_fee ELSE 0 END) AS new_profit, -- Lợi nhuận mới sau khi trừ giảm giá
        SUM(CASE WHEN o.status_order = 'Đã hủy' THEN oi.quantity * oi.price ELSE 0 END) AS canceled_revenue -- Doanh thu của đơn hàng bị hủy
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN inventory_entries ie ON oi.product_id = ie.inventory_id
    WHERE DATE_FORMAT(o.placed_on, '%Y') BETWEEN '$startYear' AND '$endYear'
    GROUP BY DATE_FORMAT(o.placed_on, '%Y')
    ORDER BY o.placed_on ASC
";

$result = $conn->query($sql);

// Lấy dữ liệu trả về
$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$sql_canceled_rate_yearly = "
    SELECT 
        DATE_FORMAT(o.placed_on, '%Y') AS placed_year, -- Lấy năm
        COUNT(o.order_id) AS total_orders, -- Tổng số đơn hàng trong năm
        SUM(CASE WHEN o.status_order = 'Đã hủy' THEN 1 ELSE 0 END) AS canceled_orders, -- Tổng số đơn bị hủy
        ROUND(SUM(CASE WHEN o.status_order = 'Đã hủy' THEN 1 ELSE 0 END) / COUNT(o.order_id) * 100, 2) AS canceled_percentage -- Tỷ lệ hủy (%)
    FROM orders o
    GROUP BY DATE_FORMAT(o.placed_on, '%Y') -- Gom nhóm theo năm
    ORDER BY DATE_FORMAT(o.placed_on, '%Y') ASC; -- Sắp xếp theo năm
";

$result_canceled_rate_yearly = $conn->query($sql_canceled_rate_yearly);

$canceled_rate_yearly_data = [];
if ($result_canceled_rate_yearly->num_rows > 0) {
    while ($row = $result_canceled_rate_yearly->fetch_assoc()) {
        $canceled_rate_yearly_data[] = [
            'placed_year' => $row['placed_year'], // Năm
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
    <title>Thống kê doanh thu theo năm</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" type="text/css" href="../CSS/revenue.css?v=1.1 <?php echo time(); ?>">
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>
    <h1 class="revenue_title">
        Thống kê doanh thu theo năm
    </h1>
    <section class="revenue">
        <form method="GET" action="" class="filter-form">
            <div class="filter-type">
                <select name="report_type" id="report_type" onchange="location = this.value;">
                    <option value="revenue_date.php?report_type=daily"
                        <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'daily') ? 'selected' : '' ?>>
                        Thống kê doanh thu theo ngày
                    </option>
                    <option value="revenue_month.php?report_type=monthly"
                        <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'monthly') ? 'selected' : '' ?>>
                        Thống kê doanh thu theo tháng
                    </option>
                    <option value="revenue_year.php"
                        <?= (basename($_SERVER['PHP_SELF']) == 'revenue_year.php') ? 'selected' : '' ?>>
                        Thống kê doanh thu theo năm
                    </option>
                </select>

            </div>

            <!-- Dropdown chọn năm -->
            <div class="revenue-year">
                <!-- Dropdown chọn năm bắt đầu -->
                <div class="filter-year">
                    <label for="start_year">Chọn năm bắt đầu:</label>
                    <select name="start_year" id="start_year" onchange="this.form.submit()" style="margin-right: 2rem;">
                        <?php
                        $currentYear = date('Y');
                        $startYear = isset($_GET['start_year']) ? $_GET['start_year'] : $currentYear;
                        for ($year = 2000; $year <= $currentYear; $year++) {
                            echo "<option value='$year' " . ($startYear == $year ? 'selected' : '') . ">$year</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Dropdown chọn năm kết thúc -->
                <div class="filter-year">
                    <label for="end_year">Chọn năm kết thúc:</label>
                    <select name="end_year" id="end_year" onchange="this.form.submit()" style="width: 105px;">
                        <option value="">-- Chọn năm kết thúc (mặc định năm bắt đầu) --</option>
                        <?php
                        $endYear = isset($_GET['end_year']) ? $_GET['end_year'] : $startYear;
                        for ($year = 2000; $year <= $currentYear; $year++) {
                            echo "<option value='$year' " . ($endYear == $year ? 'selected' : '') . ">$year</option>";
                        }
                        ?>
                    </select>
                </div>


            </div>
        </form>
        <h3 class="revenue_table_title">
            Bảng thống kê chi tiết theo năm
        </h3>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Năm</th>
                        <th>Doanh thu tạm tính (VNĐ)</th>
                        <th>Doanh thu thực tế (VNĐ)</th>
                        <th>Chi phí nhập hàng (VNĐ)</th>
                        <th>Chi phí nhập hàng (Đơn giao) (VNĐ)</th>
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
                                <td><?= $row['placed_year']; ?></td>
                                <td><?= number_format($row['yearly_revenue'], 2); ?></td>
                                <td><?= number_format($row['actual_revenue'], 2); ?></td>
                                <td><?= number_format($row['yearly_import_cost'], 2); ?></td>
                                <td><?= number_format($row['import_cost_delivered'], 2); ?></td>
                                <td><?= number_format($row['canceled_revenue'], 2); ?></td>
                                <td><?= number_format($row['yearly_discount'], 2); ?></td>
                                <td><?= number_format($row['new_profit'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9">Không có dữ liệu thống kê cho năm đã chọn</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>


    </section>
    <h1 class="revenue_title" style="margin-top: 10rem;">
        Biểu đồ thống kê chi tiết
    </h1>

    <!-- Hiển thị biểu đồ -->
    <canvas id="yearlyChart"></canvas>
    <script>
        const ctx = document.getElementById('yearlyChart').getContext('2d');
        const data = <?= json_encode($data); ?>;
        const canceledRateData = <?= json_encode($canceled_rate_yearly_data); ?>;

        const years = [];
        const profits = [];
        const canceledPercentages = [];

        // Xử lý dữ liệu cho biểu đồ
        data.forEach(row => {
            years.push(row.placed_year); // Năm
            profits.push(row.new_profit); // Lợi nhuận
        });

        canceledRateData.forEach(row => {
            canceledPercentages.push(row.canceled_percentage); // Tỷ lệ hủy
        });

        new Chart(ctx, {
            type: 'bar', // Biểu đồ hỗn hợp
            data: {
                labels: years,
                datasets: [{
                        label: 'Lợi Nhuận (VNĐ)',
                        data: profits,
                        type: 'bar', // Biểu đồ cột
                        backgroundColor: 'rgba(88, 214, 141, 0.8)', // Màu xanh lá
                        borderColor: 'rgba(30, 132, 73, 1)',
                        borderWidth: 2,
                        yAxisID: 'y' // Trục y chính
                    },
                    {
                        label: 'Tỷ lệ hủy đơn (%)',
                        data: canceledPercentages,
                        type: 'line', // Biểu đồ đường
                        borderColor: 'rgba(236, 112, 99, 1)', // Màu đỏ
                        backgroundColor: 'rgba(236, 112, 99, 0.3)',
                        borderWidth: 2,
                        tension: 0.4, // Đường cong
                        yAxisID: 'y1' // Trục y phụ
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                if (tooltipItem.dataset.label === 'Tỷ lệ hủy đơn (%)') {
                                    return tooltipItem.dataset.label + ': ' + tooltipItem.raw + ' %';
                                }
                                return tooltipItem.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(
                                    tooltipItem.raw) + ' VNĐ';
                            }
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Lợi Nhuận (VNĐ)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' %';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Tỷ lệ hủy đơn (%)'
                        },
                        grid: {
                            drawOnChartArea: false // Không vẽ lưới trên trục y1
                        }
                    }
                }
            }
        });
    </script>

</body>

</html>