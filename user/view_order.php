<?php
include '../connection/connection.php';
session_start();
$admin_id = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

if (!isset($admin_id)) {
    header('location:../components/login.php');
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/login.php');
}



?>

<style type="text/css">
    <?php include 'main.css';
    ?>
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css"
        integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"
        integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js"
        integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- <link rel="stylesheet" type="text/css" href="slick.css"> -->
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="main.css?v=1.1 <?php echo time(); ?>">
    <title>Home</title>
</head>

<body>
    <!-- <div class="line3"></div> -->
    <?php include '../user/header.php' ?>

    <div class="line"></div>
    <div class="line3"></div>


    <section class="order">

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




        <div class="order-container">
            <div class="box-container">
                <?php
                $grand_total = 0;
                $total_quantity = 0;
                $order_id_from_url = $_GET['order_id']; // Lấy order_id từ URL

                $select_order_items = mysqli_query($conn, "SELECT * FROM `order_items` WHERE order_id='$order_id_from_url'") or die('query failed');

                if (mysqli_num_rows($select_order_items) > 0) {
                    while ($fetch_order_item = mysqli_fetch_assoc($select_order_items)) {
                        // Lấy thông tin từ bảng order_items
                        $product_name = $fetch_order_item['product_name'];
                        $quantity = $fetch_order_item['quantity'];
                        $product_id = $fetch_order_item['product_id']; // Lấy product_id để so sánh
                        $image_names = explode(',', $fetch_order_item['product_image']); // Tách ảnh nếu có nhiều

                        // Truy vấn để lấy product_price, color_name và capacity từ bảng products
                        $select_product = mysqli_query(
                            $conn,
                            "SELECT product_price, color_name, capacity 
                     FROM `products` 
                     WHERE product_id = '$product_id'"
                        ) or die('Lỗi truy vấn: ' . mysqli_error($conn));

                        $fetch_product = mysqli_fetch_assoc($select_product);

                        // Kiểm tra nếu sản phẩm tồn tại trong bảng products
                        if ($fetch_product) {
                            $price = $fetch_product['product_price'];
                            $color_name = $fetch_product['color_name'];
                            $capacity = $fetch_product['capacity'];
                        } else {
                            $price = 0; // Đặt giá trị mặc định nếu sản phẩm không tồn tại
                            $color_name = 'Không xác định';
                            $capacity = '';
                        }

                        $total_price = $price * $quantity;
                        $grand_total += $total_price;
                        $total_quantity += $quantity;
                ?>
                        <div class="box">
                            <div class="box-content">
                                <div class="img-container">
                                    <img class="imgshop" src="../image/product/<?php echo $image_names[0]; ?>"
                                        alt="<?php echo $product_name; ?>">
                                </div>
                                <div class="product-details">
                                    <!-- Hiển thị product_name và color_name ngăn cách bởi dấu "-" -->
                                    <h3><?php echo $product_name . ' - ' . $color_name; ?></h3>

                                    <!-- Kiểm tra nếu capacity không rỗng mới hiển thị -->
                                    <?php if (!empty($capacity)): ?>
                                        <p>Dung tích: <strong><?php echo $capacity; ?></strong></p>
                                    <?php endif; ?>

                                    <p>Giá: <strong><?php echo number_format($price); ?>VNĐ</strong></p>
                                    <p>SL: <strong><?php echo number_format($quantity); ?></strong></p>
                                    <p>Tổng: <strong><?php echo number_format($total_price); ?>VNĐ</strong></p>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<p class="empty">Chưa có sản phẩm nào được thêm!</p>';
                }
                ?>
            </div>



            <div class="line2"></div>

            <?php


            // Lấy order_id từ GET parameter
            $order_id = $_GET['order_id'];

            // Truy vấn dữ liệu từ bảng orders theo order_id
            $query = "SELECT * FROM orders WHERE order_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Kiểm tra xem có đơn hàng nào không
            if ($result->num_rows > 0) {
                $fetch_orders = $result->fetch_assoc();
            }
            ?>

            <div class="order-summary">
                <h2>Thông tin đơn hàng</h2>

                <div class="detail">

                    <p>Ngày đặt: <span><strong><?php echo $fetch_orders['placed_on']; ?></strong></span></p>
                    <p>Tên: <strong><span><?php echo $fetch_orders['user_name']; ?></span></strong></p>
                    <p>Số điện thoại: <strong><span><?php echo $fetch_orders['user_number']; ?></span></strong></p>
                    <p>Email: <strong><span><?php echo $fetch_orders['user_email']; ?></span></strong></p>
                    <p>Địa chỉ: <strong><span><?php echo $fetch_orders['address']; ?></span></strong></p>
                    <p>Hình thức thanh toán: <strong><span><?php echo $fetch_orders['method']; ?></span></strong></p>
                    <p>Số lượng sản phẩm: <strong><span><?php echo $fetch_orders['total_products']; ?></span></strong>
                    </p>
                    <p>Tổng số tiền:
                        <strong><span><?php echo number_format($fetch_orders['total_price'], 0, ',', '.') . ' VNĐ'; ?></span></strong>
                    </p>
                    <p>Trạng thái đơn hàng: <strong><span><?php echo $fetch_orders['payment_status']; ?></span></strong>
                    </p>


                    <!-- Form hủy đơn và mua lại -->
                    <form id="cancelForm" method="post">
                        <input type="submit" name="cancel-btn" class="btn" value="Hủy Đơn">
                        <input type="submit" name="buy-again-btn" class="btn" value="Mua Lại">
                    </form>

                    <!-- Phần hủy đơn và lý do hủy -->
                    <div id="cancelSection" style="display: none;">
                        <h2>Chọn Lý Do Bạn Muốn Hủy Đơn</h2>
                        <form action="" method="post">
                            <label for="reason">Lý Do:</label>
                            <select name="reason" id="reason">
                                <option value="Muốn thay đổi địa chỉ giao hàng">Muốn thay đổi địa chỉ giao hàng</option>
                                <option value="Tìm thấy chỗ khác rẻ hơn">Tìm thấy chỗ khác rẻ hơn</option>
                                <option value="Thay đổi đơn hàng(Kích thước, sản phẩm,...)">Thay đổi đơn hàng(Kích
                                    thước, sản phẩm,...)</option>
                                <option value="Thủ tục thanh toán quá rắc rối">Thủ tục thanh toán quá rắc rối</option>
                                <option value="Lý do khác">Lý do khác</option>
                            </select>
                            <input type="hidden" name="order_id" value="<?php echo $fetch_orders['order_id']; ?>">
                            <input type="submit" id="confirm-cancel-btn" name="confirm-cancel" class="btn"
                                value="Xác Nhận">
                        </form>
                    </div>
                </div>
            </div>
    </section>

    <div class="line"></div>
    <div class="line"></div>
    <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
    <df-messenger intent="WELCOME" chat-title="Chatbot" agent-id="9b3c9d53-e2a3-42da-a61c-e036c32c8aa2"
        language-code="en">
    </df-messenger>
    <?php include '../user/footer.php' ?>

    <style>
        #cancelSection {
            display: block;
            /* Ẩn ban đầu */
        }
    </style>

    <script>
        // Khi người dùng nhấn vào nút "Hủy Đơn", hiển thị phần tử
        document.getElementById("cancel-btn").addEventListener("click", function() {
            // Hiển thị cancelSection
            document.getElementById("cancelSection").style.display = "block";

        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("cancelForm").addEventListener("submit", function(event) {
                event.preventDefault(); // Ngăn chặn hành vi mặc định của form
                document.getElementById("cancelSection").style.display = "block"; // Hiển thị cancelSection
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Lắng nghe sự kiện nhấn vào nút "Hủy Đơn"
            document.getElementById("cancel-btn").addEventListener("click", function() {
                // Hiển thị modal xác nhận
                document.getElementById("cancelModal").style.display = "block";
            });

            // Lắng nghe sự kiện nhấn vào nút "Xác Nhận" trong modal
            document.getElementById("confirmCancelBtn").addEventListener("click", function() {
                // Nếu người dùng xác nhận, thực hiện hành động hủy đơn hàng
                document.getElementById("cancelForm").submit();
            });

            // Lắng nghe sự kiện nhấn vào nút đóng modal hoặc nút hủy
            var modal = document.getElementById("cancelModal");
            var closeBtn = document.getElementsByClassName("close")[0];
            var cancelBtn = document.getElementById("cancelCancelBtn");

            closeBtn.onclick = function() {
                // Ẩn modal khi người dùng nhấn vào nút đóng
                modal.style.display = "none";
            }

            cancelBtn.onclick = function() {
                // Ẩn modal khi người dùng nhấn vào nút hủy
                modal.style.display = "none";
            }

            // Đóng modal nếu người dùng nhấp bất kỳ đâu ngoài modal
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var cancelBtn = document.getElementById("cancel-btn");
            var modal = document.getElementById("cancelModal");
            var closeBtn = document.getElementsByClassName("close")[0];
            var confirmCancelBtn = document.getElementById("confirmCancelBtn");
            var cancelCancelBtn = document.getElementById("cancelCancelBtn");

            cancelBtn.addEventListener("click", function() {
                document.getElementById("cancelSection").style.display = "block";
                modal.style.display = "block";
            });

            confirmCancelBtn.addEventListener("click", function() {
                document.getElementById("cancelForm").submit();
            });

            closeBtn.onclick = function() {
                modal.style.display = "none";
            }

            cancelCancelBtn.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("confirm-cancel-btn").addEventListener("click", function(event) {
                event.preventDefault(); // Ngăn chặn hành động mặc định của form
                var reason = document.getElementById("reason").value; // Lấy giá trị của lý do từ select box
                var order_id = <?php echo $_GET['order_id']; ?>; // Lấy order_id từ URL

                // Gửi dữ liệu lý do điều chỉnh đến server bằng Ajax
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "process_cancel.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Xử lý phản hồi từ server (nếu cần)
                        alert(xhr.responseText); // Hiển thị phản hồi từ server
                        // Sau khi xử lý xong, chuyển hướng về trang order.php
                        window.location.href = "order.php";
                    }
                };
                xhr.send("reason=" + encodeURIComponent(reason) + "&order_id=" +
                    order_id); // Gửi dữ liệu lý do và order_id bằng phương thức POST
            });
        });
    </script>

    <script>
        document.getElementById('cancel-form').addEventListener('submit', function(event) {
            if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')) {
                event.preventDefault(); // Ngăn chặn gửi biểu mẫu nếu người dùng không xác nhận
            }
        });
    </script>



    <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
    <df-messenger chat-title="Xin chào!" agent-id="9b3c9d53-e2a3-42da-a61c-e036c32c8aa2" language-code="en">
    </df-messenger>

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>