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

// Lấy tháng hiện tại
$currentMonth = date('m');
$currentYear = date('Y');

// Lấy tháng bắt đầu từ người dùng, nếu không có thì dùng tháng hiện tại
$startMonth = isset($_GET['start_month']) && $_GET['start_month'] !== '' ? $_GET['start_month'] : $currentMonth;

// Nếu không chọn `end_month`, bỏ qua hoặc đặt bằng `start_month`
$endMonth = isset($_GET['end_month']) && $_GET['end_month'] !== '' ? $_GET['end_month'] : $startMonth;

$sql = "
    SELECT 
        DATE_FORMAT(o.placed_on, '%m/%Y') AS placed_month, 
        SUM(oi.total_price) AS monthly_revenue,
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.discount_fee ELSE 0 END) AS monthly_discount, -- Giảm giá chỉ khi Đã giao
        (SELECT SUM(o2.shipping_fee) 
         FROM orders o2 
         WHERE DATE_FORMAT(o2.placed_on, '%m') BETWEEN '$startMonth' AND '$endMonth' 
         AND DATE_FORMAT(o2.placed_on, '%Y') = '$currentYear'
         AND o2.status_order = 'Đã giao') AS total_shipping_fee, -- Phí vận chuyển chỉ khi Đã giao
        SUM(ie.import_price * oi.quantity) AS monthly_import_cost, -- Tổng chi phí nhập hàng (tất cả đơn)
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN ie.import_price * oi.quantity ELSE 0 END) AS import_cost_delivered, -- Chi phí nhập hàng theo đơn giao
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.quantity * oi.price ELSE 0 END) AS actual_revenue, -- Doanh thu thực tế
        SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.quantity * oi.price ELSE 0 END) 
            - SUM(CASE WHEN o.status_order = 'Đã giao' THEN ie.import_price * oi.quantity ELSE 0 END)
            - SUM(CASE WHEN o.status_order = 'Đã giao' THEN oi.discount_fee ELSE 0 END) AS new_profit, -- Lợi nhuận mới sau khi trừ giảm giá
        SUM(CASE WHEN o.status_order = 'Đã hủy' THEN oi.quantity * oi.price ELSE 0 END) AS canceled_revenue -- Doanh thu của đơn hàng bị hủy
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN inventory_entries ie ON oi.product_id = ie.inventory_id
    WHERE DATE_FORMAT(o.placed_on, '%m') BETWEEN '$startMonth' AND '$endMonth'
    AND DATE_FORMAT(o.placed_on, '%Y') = '$currentYear'
    GROUP BY DATE_FORMAT(o.placed_on, '%m/%Y')
    ORDER BY o.placed_on ASC
";

$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$sql_canceled_rate_monthly = "
    SELECT 
        DATE_FORMAT(o.placed_on, '%m/%Y') AS placed_month, -- Lấy tháng và năm
        COUNT(o.order_id) AS total_orders, -- Tổng số đơn hàng trong tháng
        SUM(CASE WHEN o.status_order = 'Đã hủy' THEN 1 ELSE 0 END) AS canceled_orders, -- Tổng số đơn bị hủy
        ROUND(SUM(CASE WHEN o.status_order = 'Đã hủy' THEN 1 ELSE 0 END) / COUNT(o.order_id) * 100, 2) AS canceled_percentage -- Tỷ lệ hủy (%)
    FROM orders o
    GROUP BY DATE_FORMAT(o.placed_on, '%m/%Y') -- Gom nhóm theo tháng
    ORDER BY DATE_FORMAT(o.placed_on, '%Y-%m') ASC; -- Sắp xếp theo tháng
";

$result_canceled_rate_monthly = $conn->query($sql_canceled_rate_monthly);

$canceled_rate_monthly_data = [];
if ($result_canceled_rate_monthly->num_rows > 0) {
    while ($row = $result_canceled_rate_monthly->fetch_assoc()) {
        $canceled_rate_monthly_data[] = [
            'placed_month' => $row['placed_month'], // Tháng
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
    <title>Thống kê doanh thu theo tháng</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" type="text/css" href="../CSS/revenue.css?v=1.1 <?php echo time(); ?>">
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>
    <h1 class="revenue_title">
        <?php if ($startMonth === $endMonth): ?>
            Thống kê doanh thu của tháng <?= $startMonth ?>/<?= $currentYear ?>
        <?php else: ?>
            Thống kê doanh thu từ tháng <?= $startMonth ?>/<?= $currentYear ?> đến tháng
            <?= $endMonth ?>/<?= $currentYear ?>
        <?php endif; ?>
    </h1>
    <section class="revenue">

        <form method="GET" action="" class="filter-form">
            <div class="filter-type">
                <select name="report_type" id="report_type" onchange="location = this.value;">
                    <option value="revenue_date.php?report_type=daily"
                        <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'daily') ? 'selected' : '' ?>>
                        Thống kê doanh thu theo ngày</option>
                    <option value="revenue_month.php?report_type=monthly"
                        <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'monthly') ? 'selected' : '' ?>>
                        Thống kê doanh thu theo tháng</option>
                    <option value="revenue_year.php?report_type=yearly"
                        <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'yearly') ? 'selected' : '' ?>>
                        Thống kê doanh thu theo năm</option>
                </select>
            </div>
            <input type="hidden" name="report_type"
                value="<?= isset($_GET['report_type']) ? $_GET['report_type'] : 'monthly' ?>">
            <div class="revenue-month">
                <div class="filter-month">
                    <label for="start_month">Chọn tháng bắt đầu:</label>
                    <select name="start_month" id="start_month" onchange="this.form.submit()"
                        style="margin-right: 2rem;">
                        <?php
                        for ($month = 1; $month <= 12; $month++) {
                            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT); // Đảm bảo 2 chữ số
                            echo "<option value='$monthStr' " . ($startMonth === $monthStr ? 'selected' : '') . ">Tháng $monthStr</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-month">
                    <label for="end_month">Chọn tháng kết thúc:</label>
                    <select name="end_month" id="end_month" onchange="this.form.submit()" style="width: 105px;">
                        <option value="">-- Chọn tháng kết thúc (mặc định tháng bắt đầu) --</option>
                        <?php
                        for ($month = 1; $month <= 12; $month++) {
                            $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT); // Đảm bảo 2 chữ số
                            echo "<option value='$monthStr' " . ($endMonth === $monthStr ? 'selected' : '') . ">Tháng $monthStr</option>";
                        }
                        ?>
                    </select>
                </div>
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
                        <th>Tháng</th>
                        <th>Doanh thu tạm tính(VNĐ)</th>
                        <th>Doanh thu thực tế (VNĐ)</th>
                        <th>Chi phí nhập hàng (VNĐ)</th>
                        <th>Chi phí nhập hàng (Đơn giao) (VNĐ)</th>
                        <th>Doanh thu bị hủy (VNĐ)</th> <!-- Cột mới -->
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
                                <td><?= $row['placed_month']; ?></td>
                                <td><?= number_format($row['monthly_revenue'], 2); ?></td>
                                <td><?= number_format($row['actual_revenue'], 2); ?></td>
                                <td><?= number_format($row['monthly_import_cost'], 2); ?></td>
                                <td><?= number_format($row['import_cost_delivered'], 2); ?></td>
                                <td><?= number_format($row['canceled_revenue'], 2); ?></td> <!-- Hiển thị cột mới -->
                                <td><?= number_format($row['monthly_discount'], 2); ?></td>
                                <td><?= number_format($row['new_profit'], 2); ?></td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9">Vui lòng chọn tháng để xem thống kê</td>
                        </tr>
                    <?php endif; ?>
                </tbody>


            </table>
        </div>

    </section>

    <h1 class="revenue_title" style="margin-top: 3rem;">
        Biểu đồ thống kê chi tiết
    </h1>
    <!-- Hiển thị biểu đồ -->
    <canvas id="orderChart"></canvas>
    <script>
        const ctx = document.getElementById('orderChart').getContext('2d');
        const data = <?= json_encode($data); ?>;
        const canceledRateData = <?= json_encode($canceled_rate_monthly_data); ?>;

        const months = [];
        const profits = [];
        const cancelRates = [];

        // Map data for profits
        data.forEach(row => {
            months.push(row.placed_month);
            profits.push(row.new_profit); // Lợi nhuận
        });

        // Map data for canceled rates
        canceledRateData.forEach(row => {
            const matchingMonth = months.find(month => month === row.placed_month);
            if (matchingMonth) {
                cancelRates.push(row.canceled_percentage); // Tỷ lệ hủy đơn
            } else {
                cancelRates.push(0); // Nếu không có dữ liệu, mặc định là 0
            }
        });

        // Create the chart
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                        label: 'Lợi Nhuận (VNĐ)',
                        data: profits,
                        borderColor: 'rgba(39, 174, 96, 1)',
                        backgroundColor: 'rgba(46, 204, 113, 0.2)',
                        yAxisID: 'y1', // Liên kết với y1
                        tension: 0.4,
                    },
                    {
                        label: 'Tỷ Lệ Hủy Đơn (%)',
                        data: cancelRates,
                        borderColor: 'rgba(192, 57, 43, 1)',
                        backgroundColor: 'rgba(236, 112, 99, 0.2)',
                        yAxisID: 'y2', // Liên kết với y2
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Biểu đồ lợi nhuận và tỷ lệ hủy đơn',
                    },
                },
                scales: {
                    y1: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Lợi Nhuận (VNĐ)',
                        },
                    },
                    y2: {
                        type: 'linear',
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Tỷ Lệ Hủy Đơn (%)',
                        },
                        grid: {
                            drawOnChartArea: false, // Không vẽ grid của y2 trên y1
                        },
                    },
                },
            },
        });
    </script>

</body>

</html>