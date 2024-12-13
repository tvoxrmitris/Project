<?php
include '../connection/connection.php';
session_start();

// Kiểm tra xem người dùng có đăng nhập hay không
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Nếu chưa đăng nhập, sử dụng session_id() làm định danh tạm thời
    $user_id = session_id();
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();  // Khởi tạo mảng trống nếu chưa có giỏ hàng
}

// Xử lý xóa tất cả sản phẩm từ giỏ hàng
if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'") or die('Xóa tất cả sản phẩm không thành công');
    header('location:../user/cart.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
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
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <title>Seraph Beauty - Giỏ hàng</title>
</head>

<body>
    <?php include '../guest/header_guest.php'; ?>
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
                $total_before_discount = 0; // Biến lưu tổng tiền trước khi giảm
                $total_discount = 0; // Biến lưu tổng số tiền được giảm

                // Kiểm tra xem giỏ hàng trong session có tồn tại không
                if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                    foreach ($_SESSION['cart'] as $key => $fetch_cart) {
                        $product_name = $fetch_cart['product_name'];
                        $quantity = $fetch_cart['quantity'];
                        $product_id = $fetch_cart['product_id'];
                        $product_image = $fetch_cart['product_image'];

                        // Truy vấn sản phẩm từ bảng products
                        $select_product = mysqli_query($conn, "SELECT product_price, color_name, capacity, product_subcategory FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
                        $fetch_product = mysqli_fetch_assoc($select_product);

                        $price = $fetch_product ? $fetch_product['product_price'] : 0;
                        $color_name = $fetch_product ? $fetch_product['color_name'] : 'Unknown Color';
                        $capacity = $fetch_product ? $fetch_product['capacity'] : null;
                        $subcategory = $fetch_product ? $fetch_product['product_subcategory'] : null;

                        // Cộng tiền gốc (chưa giảm giá) vào tổng trước khi giảm
                        $total_before_discount += $price * $quantity;

                        // Kiểm tra khuyến mãi từ bảng product_promotion với điều kiện thời gian
                        // Kiểm tra khuyến mãi từ bảng product_promotion với điều kiện product_id
$current_date = date('Y-m-d'); // Lấy ngày hiện tại
$select_promotion = mysqli_query(
    $conn,
    "SELECT discount_percent 
     FROM `product_promotion` 
     WHERE product_id = '$product_id' 
     AND '$current_date' BETWEEN start_date AND end_date"
)
    or die('Lỗi truy vấn: ' . mysqli_error($conn));

$fetch_promotion = mysqli_fetch_assoc($select_promotion);

// Nếu có khuyến mãi, áp dụng giảm giá
if ($fetch_promotion) {
    $discount_percent = $fetch_promotion['discount_percent'];
    $discount_amount = ($price * ($discount_percent / 100)) * $quantity; // Tính số tiền được giảm
    $total_discount += $discount_amount; // Cộng số tiền được giảm vào tổng
    $price = $price - ($price * ($discount_percent / 100)); // Tính giá sau khi giảm
}


                        // Nếu có khuyến mãi, áp dụng giảm giá
                        if ($fetch_promotion) {
                            $discount_percent = $fetch_promotion['discount_percent'];
                            $discount_amount = ($price * ($discount_percent / 100)) * $quantity; // Tính số tiền được giảm
                            $total_discount += $discount_amount; // Cộng số tiền được giảm vào tổng
                            $price = $price - ($price * ($discount_percent / 100)); // Tính giá sau khi giảm
                        }

                        $total_price = $price * $quantity;
                        $grand_total += $total_price;
                        $total_quantity += $quantity;
                ?>
                <div class="box" id="box-<?php echo $key; ?>">
                    <div class="box-content">
                        <div class="img-container">
                            <img class="imgshop" src="../image/product/<?php echo $product_image; ?>"
                                alt="<?php echo $product_name; ?>">
                        </div>
                        <div class="product-details">
                            <h3 style="color: #666;"><strong> <?php echo $product_name; ?></strong></h3>
                            <p>Màu sắc: <strong><span style="color: #666;"><?php echo $color_name; ?></span></strong>
                            </p>

                            <?php if ($capacity !== null && $capacity !== '') { ?>
                            <p>Dung tích: <strong><span style="color: #666;"><?php echo $capacity; ?></span></strong>
                            </p>
                            <?php } ?>

                            <p>Giá:
                                <?php if ($fetch_promotion) { ?>
                                <strong>
                                    <span style="text-decoration: line-through; color: #999;">
                                        <?php echo number_format($fetch_product['product_price']); ?> VNĐ
                                    </span>
                                </strong>
                                <strong>
                                    <span style="color: #bd0100 !important;">
                                        <?php echo number_format($price); ?> VNĐ
                                    </span>
                                </strong>
                                <?php } else { ?>
                                <strong>
                                    <span style="color: #666;">
                                        <?php echo number_format($fetch_product['product_price']); ?> VNĐ
                                    </span>
                                </strong>
                                <?php } ?>
                            </p>

                            <p>Tổng: <strong><span style="color: #666;"><?php echo number_format($total_price); ?>
                                        VNĐ</span></strong></p>

                            <form action="update_quantity_cart.php" method="post">
                                <select name="quantity" id="quantity-<?php echo $key; ?>" onchange="this.form.submit()">
                                    <?php
                                            for ($i = 1; $i <= 20; $i++) {
                                                echo "<option value='$i'" . ($quantity == $i ? ' selected' : '') . ">$i</option>";
                                            }
                                            ?>
                                </select>
                                <input type="hidden" name="cart_id" value="<?php echo $key; ?>">
                            </form>

                            <form id="delete-form-<?php echo $key; ?>" action="delete_from_cart.php" method="post"
                                style="display:none;">
                                <input type="hidden" name="cart_id" value="<?php echo $key; ?>">
                            </form>
                            <span class="delete-btn" onclick="submitDeleteForm(<?php echo $key; ?>)">Xóa</span>
                        </div>
                        <div id="delete-notification" class="delete-notification">
                            Đã xóa sản phẩm khỏi giỏ hàng
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

            <div class="order-summary">
                <h2>Tóm tắt đơn hàng</h2>
                <ul class="order-detail">
                    <li>
                        Tổng số lượng sản phẩm: <span><?php echo $total_quantity; ?></span>
                    </li>
                    <li>
                        Tổng giá tiền chưa giảm: <span><?php echo number_format($total_before_discount, 0, ',', '.'); ?>
                            VNĐ</span>
                    </li>
                    <li>
                        Tổng số tiền được giảm: <span>-<?php echo number_format($total_discount, 0, ',', '.'); ?>
                            VNĐ</span>
                    </li>
                    <li>
                        Thành tiền: <span><?php echo number_format($grand_total, 0, ',', '.'); ?> VNĐ</span>
                    </li>
                </ul>
                <form action="update_shipping.php" method="post">
                    <input type="hidden" name="total_amount" value="<?php echo $grand_total; ?>">
                </form>

                <?php
                if (!isset($_SESSION['user_id'])) {
                    // Chưa đăng nhập -> Lưu giỏ hàng tạm thời vào session và chuyển hướng đến trang đăng nhập
                    $_SESSION['cart_before_login'] = $_SESSION['cart'];
                    echo '<a href="../components/login.php?redirect=checkout" class="checkout-btn">Đăng nhập để thanh toán</a>';
                } else {
                    // Đã đăng nhập
                    echo '<a href="../components/login.php?redirect=checkout">Đăng nhập để thanh toán</a>';
                }
                ?>
            </div>
        </div>



        <div class="line"></div>
        <div class="line"></div>
        <?php include '../user/footer.php'; ?>
</body>

<script type="text/javascript">
// Lưu trạng thái xóa và vị trí cuộn trước khi tải lại trang
function saveDeleteState(cartId) {
    sessionStorage.setItem("scrollPosition", window.scrollY);
    sessionStorage.setItem("deletedItem", "true");
}

// Khôi phục vị trí cuộn và hiển thị delete-notification sau khi trang tải lại
function restoreScrollPosition() {
    const scrollPosition = sessionStorage.getItem("scrollPosition");
    const deletedItem = sessionStorage.getItem("deletedItem");

    if (scrollPosition !== null) {
        window.scrollTo(0, scrollPosition);
        sessionStorage.removeItem("scrollPosition"); // Xóa sau khi đã khôi phục
    }

    if (deletedItem === "true") {
        $('#delete-notification').fadeIn().addClass('show'); // Hiển thị delete-notification
        setTimeout(function() {
            $('#delete-notification').fadeOut();
        }, 3000);
        sessionStorage.removeItem("deletedItem"); // Xóa trạng thái xóa sau khi hiển thị
    }
}

// Gọi hàm khi nhấn vào nút xóa
function submitDeleteForm(cartId) {
    saveDeleteState(cartId); // Lưu trạng thái xóa và vị trí cuộn

    $.ajax({
        url: '../guest/delete_form_cart.php',
        type: 'POST',
        data: {
            cart_id: cartId
        },
        success: function(response) {
            if (response === 'success') {
                location.reload(); // Tải lại trang để cập nhật giỏ hàng
            } else {
                alert('Bạn có chắc muốn xóa sản phẩm?');
            }
        },
        error: function() {
            alert('Xóa sản phẩm không thành công');
        }
    });
}

// Gọi hàm để khôi phục vị trí cuộn và hiển thị delete-notification khi trang đã tải lại
window.onload = restoreScrollPosition;
</script>


<style>
.delete-notification {
    position: fixed;
    bottom: 20px;
    left: 20px;
    padding: 8px 12px;
    /* Giảm padding để hộp nhỏ hơn */
    background-color: #000;
    color: #fff;
    font-size: 14px;
    /* Giảm kích thước font */
    font-family: "Arial", sans-serif;
    letter-spacing: 0.5px;
    /* Giảm khoảng cách chữ một chút */
    border-radius: 3px;
    /* Giảm bo góc */
    border: 1px solid #fff;
    /* Đường viền mỏng hơn */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    /* Giảm đổ bóng cho hiệu ứng nhẹ nhàng hơn */
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.4s ease, transform 0.4s ease;
    z-index: 1000;
}

/* Hiệu ứng xuất hiện */
.delete-notification.show {
    opacity: 1;
    transform: translateY(0);
}

/* Hiệu ứng hover tinh tế */
.delete-notification.show:hover {
    background-color: #fff;
    color: #000;
    border-color: #000;
}
</style>

</html>