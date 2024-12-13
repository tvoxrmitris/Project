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



// Truy vấn để lấy dữ liệu từ bảng province
$sql = "SELECT * FROM province";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Lỗi truy vấn: ' . mysqli_error($conn));
}

if (isset($_POST['add_sale'])) {
    echo "<pre>";
    print_r($_POST);
    die();
}



if (isset($_POST['order-btn'])) {
    $name = $_POST['name'];
    $number = $_POST['number'];
    $email = $_POST['email'];
    $method = $_POST['method'];
    $province = $_POST['province'];
    $district = $_POST['district'];
    $wards = $_POST['wards'];
    $flat = $_POST['flat'];
    $address = "$flat, $wards, $district, $province";
    $placed_on = date('Y-m-d H:i:s');
    $payment_status = "Chưa thanh toán"; // Trạng thái thanh toán mặc định ban đầu
    $province_id = $_POST['province'];
    $district_id = $_POST['district'];
    $wards_id = $_POST['wards'];

    $province_query = mysqli_query($conn, "SELECT name FROM province WHERE province_id = '$province_id'");
    $district_query = mysqli_query($conn, "SELECT name FROM district WHERE district_id = '$district_id'");
    $wards_query = mysqli_query($conn, "SELECT name FROM wards WHERE wards_id = '$wards_id'");

    $province_name = mysqli_fetch_assoc($province_query)['name'];
    $district_name = mysqli_fetch_assoc($district_query)['name'];
    $wards_name = mysqli_fetch_assoc($wards_query)['name'];

    $full_address = "$flat, $wards_name, $district_name, $province_name";


    // Lấy dữ liệu từ giỏ hàng
    $select_cart = mysqli_query($conn, "SELECT * FROM `cart`") or die('Lỗi truy vấn: ' . mysqli_error($conn));

    if (mysqli_num_rows($select_cart) > 0) {
        // Tổng số lượng và giá trị đơn hàng
        $grand_total = 0;
        $total_quantity = 0;

        while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
            $product_id = $fetch_cart['product_id'];
            $quantity = $fetch_cart['quantity'];

            // Lấy giá sản phẩm từ bảng products
            $select_product = mysqli_query($conn, "SELECT product_price FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
            $fetch_product = mysqli_fetch_assoc($select_product);

            $price = $fetch_product ? $fetch_product['product_price'] : 0;
            $total_price = $price * $quantity;

            $grand_total += $total_price;
            $total_quantity += $quantity;
        }

        // Thêm thông tin vào bảng orders
        $insert_order = mysqli_query($conn, "INSERT INTO `orders` (user_id ,user_name, user_number, user_email, method, address, total_products, total_price, placed_on, payment_status) 
                VALUES ('$user_id', '$name', '$number', '$email', '$method', '$full_address', '$total_quantity', '$grand_total', '$placed_on', '$payment_status')")
            or die('Lỗi truy vấn: ' . mysqli_error($conn));

        // Lấy ID của đơn hàng vừa tạo
        $order_id = mysqli_insert_id($conn);

        // Duyệt lại giỏ hàng và thêm vào bảng order_items
        mysqli_data_seek($select_cart, 0); // Đặt lại con trỏ của kết quả về đầu
        while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
            $product_id = $fetch_cart['product_id'];
            $quantity = $fetch_cart['quantity'];
            $product_name = mysqli_real_escape_string($conn, $fetch_cart['product_name']);
            $product_image = mysqli_real_escape_string($conn, $fetch_cart['product_image']);

            // Lấy giá sản phẩm và số lượng tồn kho từ bảng products
            $select_product = mysqli_query($conn, "SELECT product_price, quantity_in_stock FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
            $fetch_product = mysqli_fetch_assoc($select_product);
            $price = $fetch_product ? $fetch_product['product_price'] : 0;
            $quantity_in_stock = $fetch_product['quantity_in_stock'];

            $total_price = $price * $quantity;

            // Thêm vào bảng order_items
            $insert_order_item = mysqli_query($conn, "INSERT INTO `order_items` (order_id, product_id, product_name, quantity, price, total_price, product_image)
                    VALUES ('$order_id', '$product_id', '$product_name', '$quantity', '$price', '$total_price', '$product_image')")
                or die('Lỗi truy vấn: ' . mysqli_error($conn));

            // Cập nhật số lượng tồn kho trong bảng products
            $new_quantity_in_stock = $quantity_in_stock - $quantity;
            if ($new_quantity_in_stock < 0) {
                $new_quantity_in_stock = 0; // Đảm bảo không giảm xuống số âm
            }

            $update_quantity = mysqli_query($conn, "UPDATE `products` SET quantity_in_stock = '$new_quantity_in_stock' WHERE product_id = '$product_id'")
                or die('Lỗi truy vấn: ' . mysqli_error($conn));
        }

        // Sau khi đặt hàng, xóa giỏ hàng của người dùng
        mysqli_query($conn, "DELETE FROM `cart`") or die('Lỗi truy vấn: ' . mysqli_error($conn));

        // Thông báo hoàn thành
        echo "<p>Đơn hàng của bạn đã được đặt thành công!</p>";
    } else {
        echo "<p>Giỏ hàng của bạn trống, không thể đặt hàng.</p>";
    }
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css"
        integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.4.js"></script>
    <!-- <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon"> -->
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
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>">

    <script src="../js/app.js"></script>
</head>

<body>
    <!-- Include your header here -->
    <?php include '../user/header.php' ?>

    <div class="line"></div>
    <section class="checkout">





        <div class="checkout-container">
            <div class="box-container">
                <?php
                $grand_total = 0;
                $total_quantity = 0;
                $select_cart = mysqli_query($conn, "SELECT * FROM `cart`") or die('Lỗi truy vấn: ' . mysqli_error($conn));

                if (mysqli_num_rows($select_cart) > 0) {
                    while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                        $product_name = $fetch_cart['product_name'];
                        $quantity = $fetch_cart['quantity'];
                        $product_id = $fetch_cart['product_id'];
                        $image_names = explode(',', $fetch_cart['product_image']);

                        $select_product = mysqli_query($conn, "SELECT product_price FROM `products` WHERE product_id = '$product_id'") or die('Lỗi truy vấn: ' . mysqli_error($conn));
                        $fetch_product = mysqli_fetch_assoc($select_product);

                        if ($fetch_product) {
                            $price = $fetch_product['product_price'];
                        } else {
                            $price = 0;
                        }

                        $total_price = $price * $quantity;
                        $grand_total += $total_price;
                        $total_quantity += $quantity;

                        // Display product info here
                    }
                } else {
                    echo '<p class="empty">Chưa có sản phẩm nào được thêm!</p>';
                }
                ?>
            </div>



            <!-- Form only appears once -->
            <div class="checkout-form">

                <form method="post">
                    <div class="input-field">
                        <label>Tên của bạn<span>*</span></label>
                        <input type="text" name="name" placeholder="Hãy nhập tên của bạn">
                    </div>
                    <div class="input-field">
                        <label>Số điện thoại của bạn<span>*</span></label>
                        <input type="number" name="number" placeholder="Hãy nhập số điện thoại của bạn">
                    </div>
                    <div class="input-field">
                        <label>Email của bạn<span>*</span></label>
                        <input type="text" name="email" placeholder="Hãy nhập email của bạn">
                    </div>
                    <div class="input-field">
                        <label>Vui lòng chọn phương thức thanh toán<span>*</span></label>
                        <select name="method">
                            <option selected disabled>Chọn phương thức thanh toán</option>
                            <option value="Thanh toán khi nhận hàng">Thanh toán khi nhận hàng</option>
                            <option value="credit card">Thanh toán bằng MOMO</option>
                            <option value="paytm">Thanh toán bằng AtmMOMO</option>
                        </select>
                    </div>

                    <!-- 
                <button type="submit" class="btn btn-default">Thanh toán COD</button>
                <button type="submit" class="btn btn-danger">Thanh toán MoMo</button>
                <button type="submit" class="btn btn-success">Thanh toán VnPay</button> -->





                    <div class="input-field">
                        <label for="province">Tỉnh/Thành phố<span>*</span></label>
                        <select id="province" name="province" class="form-control">
                            <option value="">Chọn một tỉnh</option>

                            <?php
                            // Truy vấn để lấy dữ liệu từ bảng province
                            $sql = "SELECT * FROM province";
                            $result = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                                <option value="<?php echo $row['province_id'] ?>"><?php echo $row['name'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class="input-field">
                        <label for="district">Quận/Huyện<span>*</span></label>
                        <select id="district" name="district" class="form-control">
                            <option value="">Chọn một quận/huyện</option>
                        </select>
                    </div>

                    <div class="input-field">
                        <label for="wards">Phường/Xã<span>*</span></label>
                        <select id="wards" name="wards" class="form-control">
                            <option value="">Chọn một xã</option>
                        </select>
                    </div>

                    <div class="input-field">
                        <label>Tên đường, tòa nhà, số nhà<span>*</span></label>
                        <input type="text" id="flat" name="flat" placeholder="Nhập tên đường, tòa nhà, số nhà">
                    </div>



                    <input type="submit" name="order-btn" class="btn" value="Đặt ngay">
                </form>
            </div>
        </div>

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
        } else {
            echo '<p class="empty">Đơn hàng không tồn tại!</p>';
            exit;
        }
        ?>

        <div class="order-summary">
            <h2>Tóm tắt đơn hàng</h2>

            <ul class="order-detail">
                <?php
                $grand_total = 0; // Tổng giá tiền cho tất cả sản phẩm

                // Truy vấn các sản phẩm trong đơn hàng từ bảng order_items
                $select_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $select_items->bind_param("i", $order_id);
                $select_items->execute();
                $result_items = $select_items->get_result();

                if ($result_items->num_rows > 0) {
                    while ($fetch_item = $result_items->fetch_assoc()) {
                        $product_name = $fetch_item['product_name'];
                        $quantity = $fetch_item['quantity'];
                        $product_id = $fetch_item['product_id'];
                        $image_names = explode(',', $fetch_item['product_image']);
                        $price = $fetch_item['price'];

                        $total_price = $price * $quantity; // Tổng giá của từng sản phẩm
                        $grand_total += $total_price; // Cộng tổng giá tiền của từng sản phẩm vào tổng chung

                        // Hiển thị sản phẩm trong đơn hàng
                        echo '
                <li>
                    <div class="box">
                        <div class="box-content">
                            <div class="img-container">
                                <img class="imgshop" src="../image/product/' . $image_names[0] . '" alt="' . $product_name . '">
                            </div>
                            <div class="product-details">
                                <h3>' . $product_name . '</h3>
                                <p>SL: ' . $quantity . '</p>
                                <p>Tổng cộng: ' . number_format($total_price, 0, ',', '.') . ' VNĐ</p>
                            </div>                    
                        </div>
                    </div>
                </li>
                ';
                    }
                } else {
                    echo '<p class="empty">Chưa có sản phẩm nào trong đơn hàng!</p>';
                }
                ?>
                <li>
                    <strong>Tổng giá tiền:</strong>
                    <span><?php echo number_format($grand_total, 0, ',', '.'); ?> VNĐ</span>
                </li>
            </ul>
        </div>



        <div class="line2"></div>
        <div class="line2"></div>


        <script type="text/javascript" src="../js/script2.js"></script>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#province').change(function() {
                    var province_id = $(this).val();
                    if (province_id != '') {
                        $.ajax({
                            url: "fetch_district.php",
                            method: "POST",
                            data: {
                                province_id: province_id
                            },
                            success: function(data) {
                                $('#district').html(data);
                                $('#wards').html(
                                    '<option value="">Chọn một xã</option>'
                                ); // Reset wards khi chọn lại tỉnh
                            }
                        });
                    } else {
                        $('#district').html('<option value="">Chọn một quận/huyện</option>');
                        $('#wards').html('<option value="">Chọn một xã</option>');
                    }
                });

                $('#district').change(function() {
                    var district_id = $(this).val();
                    if (district_id != '') {
                        $.ajax({
                            url: "fetch_wards.php",
                            method: "POST",
                            data: {
                                district_id: district_id
                            },
                            success: function(data) {
                                $('#wards').html(data);
                            }
                        });
                    } else {
                        $('#wards').html('<option value="">Chọn một xã</option>');
                    }
                });
            });
        </script>
</body>

</html>