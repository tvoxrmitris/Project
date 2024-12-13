<?php
include '../connection/connection.php';
session_start();
$admin_id = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

if (!isset($admin_id)) {
    header('location:../components/login.php');
    exit(); // Terminate script after redirection
}

// Kiểm tra xem người dùng đã đăng xuất chưa
if (isset($_POST['logout'])) {
    session_destroy();
    header('location:../components/login.php');
    exit(); // Terminate script after redirection
}





// Hàm cập nhật số lượng trong cơ sở dữ liệu
function updateQuantityInDatabase($conn, $update_qty_id, $column, $new_qty)
{
    $update_query = mysqli_query($conn, "UPDATE cart SET $column = '$new_qty' WHERE pid='$update_qty_id'");
    if (!$update_query) {
        echo "Có lỗi xảy ra khi cập nhật số lượng trong cơ sở dữ liệu.";
        // Xử lý lỗi tùy ý tại đây, ví dụ: ghi log, thông báo người dùng, vv.
    }
}


// Xử lý xóa sản phẩm từ giỏ hàng
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM cart WHERE id = '$delete_id'") or die('Xóa sản phẩm không thành công');

    header('location:../user/cart.php');
    exit(); // Terminate script after redirection
}

// Xử lý xóa tất cả sản phẩm từ giỏ hàng
if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'") or die('Xóa tất cả sản phẩm không thành công');

    header('location:../user/cart.php');
    exit(); // Terminate script after redirection
}
?>

<style type="text/css">
<?php include '../CSS/main.css'?>
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
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>">
    <title>Home</title>
</head>

<body>
    <?php include '../user/header.php' ?>

    <div class="line"></div>

    <section class="cart">

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




        <div class="cart-container">
            <div class="box-container">
                <?php
        $grand_total = 0;
        $total_quantity = 0;
        $total_discount = 0; // Biến để lưu tổng số tiền được giảm
        $original_grand_total = 0; // Biến để lưu tổng giá tiền chưa giảm

        // Lấy user_id từ session (giả sử user_id đã được lưu khi người dùng đăng nhập)
        $user_id = $_SESSION['user_id']; // Điều này yêu cầu bạn phải lưu user_id trong session khi người dùng đăng nhập

        // Truy vấn tất cả sản phẩm trong giỏ hàng của người dùng hiện tại
        $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));

        if (mysqli_num_rows($select_cart) > 0) {
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                $product_id = $fetch_cart['product_id'];
                $quantity = $fetch_cart['quantity'];

                // Lấy thông tin sản phẩm từ bảng `products`
                $select_product = mysqli_query($conn, "SELECT product_name, product_image, product_price, color_name, capacity, product_subcategory FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
                $fetch_product = mysqli_fetch_assoc($select_product);

                $product_name = $fetch_product['product_name'];
                $product_image = $fetch_product['product_image'];
                $original_price = $fetch_product['product_price']; // Lưu giá gốc
                $price = $original_price; // Giá hiện tại
                $color_name = $fetch_product['color_name'] ?? 'Unknown Color';
                $capacity = $fetch_product['capacity'];
                $subcategory_name = $fetch_product['product_subcategory'];

                // Lấy discount_percent từ bảng `product_promotions` nếu có và kiểm tra thời gian
                $current_date = date('Y-m-d'); // Lấy thời gian hiện tại
                $select_promotion = mysqli_query($conn, "SELECT discount_percent FROM `product_promotion` WHERE subcategory_name = '$subcategory_name' AND start_date <= '$current_date' AND end_date >= '$current_date'") or die('Lỗi truy vấn: ' . mysqli_error($conn));

                $discount_percent = 0;
                if (mysqli_num_rows($select_promotion) > 0) {
                    $fetch_promotion = mysqli_fetch_assoc($select_promotion);
                    $discount_percent = $fetch_promotion['discount_percent'];

                    // Tính lại giá sau khi áp dụng khuyến mãi
                    $price = $original_price * (1 - $discount_percent / 100);
                }

                // Tính tổng giá tiền trước và sau giảm giá cho mỗi sản phẩm
                $total_price = $price * $quantity;
                $grand_total += $total_price;
                $total_quantity += $quantity;

                $original_total_price = $original_price * $quantity; // Tổng tiền gốc của sản phẩm
                $original_grand_total += $original_total_price;

                // Tính số tiền được giảm cho sản phẩm này
                $discount_amount = ($original_price - $price) * $quantity;
                $total_discount += $discount_amount;
        ?>
                <div class="box" id="box-<?php echo $fetch_cart['cart_id']; ?>">
                    <div class="box-content">
                        <div class="img-container">
                            <?php
                        // Tách chuỗi hình ảnh bằng dấu phẩy và lấy hình ảnh đầu tiên
                        $images = explode(',', $product_image);
                        $first_image = trim($images[0]); // Loại bỏ khoảng trắng nếu có
                    ?>
                            <img class="imgshop" src="../image/product/<?php echo $first_image; ?>"
                                alt="<?php echo $product_name; ?>">
                        </div>

                        <div class="product-details">
                            <h3 style="color: #666;"><strong><?php echo $product_name; ?></strong></h3>
                            <p>Màu sắc: <strong><span style="color: #666;"><?php echo $color_name; ?></span></strong>
                            </p>

                            <?php if ($capacity !== null && $capacity !== '') { ?>
                            <p>Dung tích: <strong><span style="color: #666;"><?php echo $capacity; ?></span></strong>
                            </p>
                            <?php } ?>

                            <p>Giá:
                                <?php if ($discount_percent > 0) { ?>
                                <strong><span
                                        style="color: #999; text-decoration: line-through;"><?php echo number_format($original_price); ?>
                                        VNĐ</span></strong>
                                <strong><span style="color: #bd0100 !important;"><?php echo number_format($price); ?>
                                        VNĐ</span></strong>
                                <?php } else { ?>
                                <strong><span style="color: #666;"><?php echo number_format($original_price); ?>
                                        VNĐ</span></strong>
                                <?php } ?>
                            </p>

                            <p>Tổng: <strong><span style="color: #666;"><?php echo number_format($total_price); ?>
                                        VNĐ</span></strong></p>

                            <form action="update_quantity_cart.php" method="post">
                                <?php
                            // Kiểm tra xem có thông báo lỗi trong session không và hiển thị thông báo
                            if (isset($_SESSION['error_message'])) {
                                echo "<div class='notification'>" . $_SESSION['error_message'] . "</div>";
                                // Xóa thông báo lỗi sau khi đã hiển thị
                                unset($_SESSION['error_message']);
                            }
                        ?>

                                <select name="quantity" id="quantity-<?php echo $fetch_cart['cart_id']; ?>"
                                    onchange="this.form.submit()">
                                    <?php
                            for ($i = 1; $i <= 20; $i++) {
                                echo "<option value='$i'" . ($quantity == $i ? ' selected' : '') . ">$i</option>";
                            }
                            ?>
                                </select>
                                <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['cart_id']; ?>">
                            </form>

                            <form id="delete-form-<?php echo $fetch_cart['cart_id']; ?>"
                                action="../user/delete_from_cart.php" method="post" style="display:none;">
                                <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['cart_id']; ?>">
                            </form>
                            <span class="delete-btn"
                                onclick="submitDeleteForm(<?php echo $fetch_cart['cart_id']; ?>)">Xóa</span>
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


            <script>
            window.onload = function() {
                // Kiểm tra xem có thông báo lỗi trong session không
                <?php if (isset($_SESSION['error_message'])): ?>
                // Hiển thị hộp thông báo
                var notification = document.querySelector('.notification');
                notification.classList.add('show');

                // Ẩn hộp thông báo sau 5 giây
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 5000);
                <?php endif; ?>
            };
            </script>
            <style>
            /* Hộp thông báo */
            .notification {
                position: fixed;
                bottom: 20px;
                left: 20px;
                background-color: #f44336;
                color: white;
                padding: 15px;
                border-radius: 5px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                z-index: 9999;
                display: none;
            }

            /* Hiển thị hộp thông báo khi có lỗi */
            .notification.show {
                display: block;
            }
            </style>
            <div class="line2"></div>

            <div class="order-summary">
                <h2>Tóm tắt đơn hàng</h2>

                <ul class="order-detail">
                    <li>
                        Tổng số lượng sản phẩm:
                        <span><strong><?php echo $total_quantity; ?></strong></span>
                    </li>

                    <li>
                        Tổng giá tiền chưa giảm:
                        <span><strong><?php echo number_format($original_grand_total, 0, ',', '.'); ?>
                                VNĐ</strong></span>
                    </li>

                    <li>
                        Tổng số tiền được giảm:
                        <span style="color: #bd0100 !important;"><strong>-<?php echo number_format($total_discount, 0, ',', '.'); ?>
                                VNĐ</strong></span>
                    </li>

                    <li>
                        <?php $final_total = $grand_total; ?>
                        Thành tiền:
                        <span><strong><?php echo number_format($final_total, 0, ',', '.'); ?> VNĐ</strong></span>
                    </li>
                </ul>

                <form method="post">
                    <input type="hidden" name="total_amount" value="<?php echo $final_total; ?>">
                </form>

                <a href="checkout.php" class="checkout-btn">Thanh toán</a>
            </div>
        </div>



        <div class="line"></div>
        <div class="line2"></div>
        <script>
        var dots = document.querySelectorAll('.dot');

        // Lặp qua từng dấu chấm và gắn sự kiện click
        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                // Lấy chỉ số của dấu chấm
                var index = parseInt(dot.getAttribute('data-index'));

                // Lấy ID sản phẩm tương ứng
                var productId = dot.getAttribute('data-product-id');

                // Lấy tất cả các ảnh của sản phẩm
                var container = dot.closest('.box');
                var images = container.querySelectorAll('.imgshop');

                // Ẩn tất cả các ảnh của sản phẩm
                images.forEach(function(img) {
                    img.classList.add('hidden');
                });

                // Hiển thị ảnh tương ứng
                images[index].classList.remove('hidden');

                // Bỏ chọn tất cả các dấu chấm của sản phẩm
                var productDots = container.querySelectorAll('.dot');
                productDots.forEach(function(d) {
                    d.classList.remove('active');
                });

                // Chọn dấu chấm đã nhấp
                dot.classList.add('active');
            });
        });
        </script>

        <script>
        function submitDeleteForm(cartId) {
            // Hiển thị một cảnh báo xác nhận trước khi xóa
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                // Tìm form xóa và gửi nó
                document.getElementById('delete-form-' + cartId).submit();
            }
        }
        </script>





        <?php include '../user/footer.php' ?>

        <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>