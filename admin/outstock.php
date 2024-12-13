<?php
include '../connection/connection.php';
session_start();
if (!isset($_SESSION['employee_email']) || $_SESSION['employee_type'] !== 'super admin') {
    header('location:../components/admin_login.php');
    exit;
}



// Xử lý đăng xuất
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/admin_login.php');
    exit;
}

// Tính tổng số lượng sản phẩm trong bảng products với điều kiện quantity_in_stock <= 15
$total_products_query = "
    SELECT COUNT(*) AS total_products
    FROM products
    WHERE quantity_in_stock <= 15
";

$result = mysqli_query($conn, $total_products_query);
$row = mysqli_fetch_assoc($result);
$total_products = $row['total_products'] ? $row['total_products'] : 0;


// Lấy danh sách các loại sản phẩm để hiển thị trong dropdown
$subcategory_query = "SELECT DISTINCT subcategory_name FROM inventory_entries";
$subcategories = mysqli_query($conn, $subcategory_query);

// Xử lý điều kiện lọc
$subcategory_filter = '';
if (isset($_GET['subcategory']) && !empty($_GET['subcategory'])) {
    $subcategory_filter = mysqli_real_escape_string($conn, $_GET['subcategory']);
}






?>
<style type="text/css">
<?php include '../CSS/style.css';

?>td {
    cursor: pointer;
}

/* Thiết lập chiều rộng tối thiểu cho các cột nếu cần */
th:nth-child(1),
td:nth-child(1) {
    width: 65px;
}


th:nth-child(2),
td:nth-child(2) {
    width: 350px;
}

th:nth-child(3),
td:nth-child(3) {
    width: 150px;
}

th:nth-child(4),
td:nth-child(4) {
    width: 150px;
}

th:nth-child(5),
td:nth-child(5) {
    width: 150px;
}

th:nth-child(6),
td:nth-child(6) {
    width: 110px;
}

th:nth-child(7),
td:nth-child(7) {
    width: 110px;
}

th:nth-child(8),
td:nth-child(8) {
    width: 130px;
}

th:nth-child(9),
td:nth-child(9) {
    width: 150px;
}

th:nth-child(10),
td:nth-child(10) {
    width: 320px;
}

th:nth-child(11),
td:nth-child(11) {
    width: 150px;
}

.table-container {
    overflow-x: auto;

    width: 100%;
}

.notification-box {
    background: #000;
    color: #fff;
    display: none;
    padding: 15px 30px;
    border: 2px solid #fff;
    border-radius: 8px;
    font-family: 'Helvetica Neue', sans-serif;
    text-transform: uppercase;
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 1000;
    opacity: 0;
    animation: slideUp 0.5s forwards;
    transition: opacity 1s ease-out;
}

@keyframes slideUp {
    0% {
        bottom: -100px;
        opacity: 0;
    }

    100% {
        bottom: 20px;
        opacity: 1;
    }
}
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">


    <title>Seraph Beauty - Sản Phẩm Hết Hàng</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>
    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '
                    <div class="message">
                        <span>' . $message . '</span>
                        <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                    </div>
                ';
        }
    }
    ?>

    <div class="title">
        <h2 style="font-size:50px;">Sản phẩm sắp hết hàng</h2>
    </div>

    <section class="shop">
        <div class="border-wrapper">
            <a href="manage_product.php" class="back-arrow"
                style="text-decoration: none; font-size: 1.5rem; margin-right: 1rem;">&#8592;</a>
            <div class="total-quantity">
                <p style="margin-left: 2rem;"><?php echo number_format($total_products, 0, '.', '.'); ?> Sản Phẩm</p>
            </div>

        </div>
        <div id="notification" class="notification-box"></div>

        <div class="table-container" style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá sản phẩm</th>
                        <th>Màu sắc</th>
                        <th>Mô tả màu sắc</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Thương hiệu</th>
                        <th>Loại sản phẩm</th>
                        <th>Hành động</th> <!-- Thêm cột Hành động -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Lấy dữ liệu từ bảng products với quantity_in_stock = 0
                    // Lấy dữ liệu từ bảng products với quantity_in_stock < 10
                    $select_products = mysqli_query($conn, "
    SELECT *
    FROM products
    WHERE quantity_in_stock < 15
") or die('Truy vấn thất bại: ' . mysqli_error($conn));


                    // Khởi tạo biến đếm cho số thứ tự
                    $count = 1;

                    if (mysqli_num_rows($select_products) > 0) {
                        while ($fetch_products = mysqli_fetch_assoc($select_products)) {
                    ?>
                    <tr>
                        <td style="color: #bd0100; font-weight: bold;"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>';">
                            <?php echo $count++; ?>
                        </td>
                        <td style="color: #bd0100; font-weight: bold;"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>';">
                            <?php echo htmlspecialchars($fetch_products['product_name']); ?>
                        </td>
                        <td style="color: #bd0100; font-weight: bold;"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>';">
                            <?php echo number_format($fetch_products['product_price'], 2) . ' VNĐ'; ?>
                        </td>
                        <td style="color: #bd0100; font-weight: bold;"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>';">
                            <?php echo htmlspecialchars($fetch_products['color_name']); ?>
                        </td>
                        <td style="color: #bd0100; font-weight: bold;"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>';">
                            <?php echo htmlspecialchars($fetch_products['detail_color']); ?>
                        </td>
                        <td style="color: #bd0100; font-weight: bold;"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>';">
                            <?php echo htmlspecialchars($fetch_products['quantity_in_stock']); ?>
                        </td>
                        <td style="color: #bd0100; font-weight: bold;"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>';">
                            <?php echo htmlspecialchars($fetch_products['status']); ?>
                        </td>
                        <td style="color: #bd0100; font-weight: bold;"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>';">
                            <?php echo htmlspecialchars($fetch_products['brand_name']); ?>
                        </td>
                        <td style="color: #bd0100; font-weight: bold;"
                            onclick="window.location.href='edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>';">
                            <?php echo htmlspecialchars($fetch_products['product_subcategory']); ?>
                        </td>
                        <td>
                            <?php
                                    $product_id = $fetch_products['product_id'];

                                    // Kiểm tra sản phẩm trong bảng low_stock_requests
                                    $check_request_query = "SELECT * FROM low_stock_requests WHERE product_id = '$product_id'";
                                    $check_request_result = mysqli_query($conn, $check_request_query);

                                    if (mysqli_num_rows($check_request_result) > 0) {
                                        echo '<a style="color: #bd0100; font-weight: bold;" href="#" onclick="deleteRequest(' . $product_id . '); return false;">Hủy yêu cầu |</a>';
                                    } else {
                                        echo '<a style="color: #bd0100; font-weight: bold;" href="#" onclick="requestStock(' . $product_id . '); return false;">Yêu cầu nhập hàng |</a>';
                                    }
                                    ?>
                            <a style="color: #bd0100; font-weight: bold;"
                                href="edit_product.php?product_id=<?php echo $fetch_products['product_id']; ?>">Sửa
                                |</a>
                            <a style="color: #bd0100; font-weight: bold;"
                                href="delete_product.php?product_id=<?php echo $fetch_products['product_id']; ?>"
                                onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');"> Xóa</a>
                        </td>
                    </tr>


                    <?php
                        }
                    } else {
                        ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-products-message">
                                Không có sản phẩm nào sắp hết hàng.
                            </div>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>
    <script>
    // Hàm yêu cầu nhập hàng
    function requestStock(productId) {
        if (!productId) {
            console.error('Product ID is undefined or null.');
            return;
        }

        fetch('request_stock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=request&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' || data.status === 'exists') {
                    // Lưu thông báo vào localStorage trước khi reload
                    localStorage.setItem('notificationMessage', data.message);
                    location.reload(); // Reload trang
                } else {
                    console.error('Error:', data.message); // Log lỗi nếu có
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }

    // Hàm xóa yêu cầu nhập hàng
    function deleteRequest(productId) {
        if (!productId) {
            console.error('Product ID is undefined or null.');
            return;
        }

        fetch('request_stock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Lưu thông báo vào localStorage trước khi reload
                    localStorage.setItem('notificationMessage', data.message);
                    location.reload(); // Reload trang
                } else {
                    console.error('Error:', data.message); // Log lỗi nếu có
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }

    // Hiển thị thông báo sau khi reload
    window.addEventListener('load', () => {
        const notificationMessage = localStorage.getItem('notificationMessage');
        if (notificationMessage) {
            // Hiển thị thông báo
            showNotification(notificationMessage);
            // Xóa thông báo để không hiển thị lại
            localStorage.removeItem('notificationMessage');
        }
    });

    // Hàm hiển thị thông báo
    function showNotification(message) {
        const notification = document.getElementById('notification');
        notification.textContent = message;

        // Hiển thị hộp thông báo với hiệu ứng
        notification.style.display = 'block';

        // Ẩn thông báo sau 4 giây
        setTimeout(() => {
            notification.style.display = 'none';
        }, 4000); // Giữ thông báo trong 4 giây
    }
    </script>


    <div class="line"></div>
    <script type="text/javascript" src="../js/script.js"></script>

</body>

</html>