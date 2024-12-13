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

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// Lấy thông tin sản phẩm từ bảng products
$product_query = "SELECT * FROM products WHERE product_id = ?";
$product_stmt = $conn->prepare($product_query);
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
$product = $product_result->fetch_assoc();

$quantity = 1; // Mặc định
$total_price = $quantity * $product['product_price']; // Tính tổng giá cho sản phẩm mặc định

if (isset($_POST['order-btn'])) {
    $quantity = isset($_POST['quantity'][$product_id]) ? intval($_POST['quantity'][$product_id]) : 1;
    $total_price = $quantity * $product['product_price']; // Tính tổng giá cho sản phẩm

    // Lấy dữ liệu từ form
    $user_name = $_POST['name'];
    $user_number = $_POST['number'];
    $user_email = $_POST['email'];
    $method = $_POST['method'];
    $province_id = $_POST['province'];
    $district_id = $_POST['district'];
    $wards_id = $_POST['wards'];
    $flat = $_POST['flat'];

    // Lấy tên của tỉnh, quận, huyện từ cơ sở dữ liệu
    $province_query = mysqli_query($conn, "SELECT name FROM province WHERE province_id = '$province_id'");
    $district_query = mysqli_query($conn, "SELECT name FROM district WHERE district_id = '$district_id'");
    $wards_query = mysqli_query($conn, "SELECT name FROM wards WHERE wards_id = '$wards_id'");

    $province_name = mysqli_fetch_assoc($province_query)['name'];
    $district_name = mysqli_fetch_assoc($district_query)['name'];
    $wards_name = mysqli_fetch_assoc($wards_query)['name'];
    

    // Tạo địa chỉ đầy đủ
    $full_address = "$flat, $wards_name, $district_name, $province_name";

    // Kiểm tra nếu có giá trị số lượng được gửi qua POST
    if (isset($_POST['quantity'][$product_id])) {
        $quantity = intval($_POST['quantity'][$product_id]); // Lấy số lượng từ người dùng chọn
    } else {
        $quantity = 1; // Mặc định nếu không có giá trị nào được chọn
    }

    // Chèn thông tin vào bảng orders
    $sql = "INSERT INTO orders (user_id, user_name, user_number, user_email, method, address, total_products, total_price, placed_on, payment_status) 
            VALUES ('$user_id', '$user_name', '$user_number', '$user_email', '$method', '$full_address', $quantity, $total_price, NOW(), 'Chưa thanh toán')";

if (mysqli_query($conn, $sql)) {
    // Lấy ID của đơn hàng vừa chèn
    $order_id = mysqli_insert_id($conn);

    // Chèn sản phẩm vào bảng order_items
    $product_name = mysqli_real_escape_string($conn, $product['product_name']);
    $product_images = explode(',', $product['product_image']);
    $product_image = $product_images[0];
    $price = $product['product_price'];
    $total_item_price = $quantity * $price;

    // Chèn vào bảng order_items
    $sql_item = "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, total_price) 
                 VALUES ($order_id, $product_id, '$product_name', '$product_image', $quantity, $price, $total_item_price)";
    mysqli_query($conn, $sql_item);

    echo "Đặt hàng thành công!";
} else {
    echo "Lỗi: " . mysqli_error($conn);
}
}


?>






<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.4.js"></script>
    <!-- <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js" integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js" integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- <link rel="stylesheet" type="text/css" href="slick.css"> -->
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time();?>">

    <script src="../js/app.js"></script>
</head>
<body>
    <!-- Include your header here -->
    <?php include '../user/header.php'?>

<div class="line"></div>
    <section class="checkout">
        




    <div class="checkout-container">
        
    <div class="checkout-form">

        <form method="post">
        <div class="order-summary">
                <h2>Tóm tắt đơn hàng</h2>
                <ul class="order-detail">
                <?php
                    // Giả sử $product['product_image'] chứa chuỗi ảnh phân tách bằng dấu phẩy
                    $image_list = explode(',', $product['product_image']);
                    $first_image = isset($image_list[0]) ? $image_list[0] : ''; // Lấy ảnh đầu tiên
                ?>

                <li>
                    <div class="box">
                        <div class="box-content">
                            <div class="img-container">
                                <img class="imgshop" src="../image/<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            </div>

                            <div class="product-details">
                                <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>

                                <!-- Thêm form để submit khi người dùng chọn số lượng -->
                               
                                <div class="input-field">
        <label>Số lượng<span>*</span></label>
        <select id="quantity-<?php echo $product['product_id']; ?>" name="quantity[<?php echo $product['product_id']; ?>]">
            <?php
            $selected_quantity = isset($_POST['quantity'][$product['product_id']]) ? intval($_POST['quantity'][$product['product_id']]) : 1;
            for ($i = 1; $i <= 20; $i++) {
                echo "<option value='$i'" . ($selected_quantity == $i ? ' selected' : '') . ">$i</option>";
            }
            ?>
        </select>
    </div>
            



                            </div>             
                        </div>
                    </div>
                </li>

                <li>
    <strong>Tổng giá tiền:</strong>
    <span id="total-price"><?php echo number_format($total_price, 0, ',', '.'); ?> VNĐ</span>
</li>

            </ul>
            </div>
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
            <div class="input-field">
                <label for="province">Tỉnh/Thành phố<span>*</span></label>
                <select id="province" name="province" class="form-control">
                    <option value="">Chọn một tỉnh</option>
                    <?php
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

    

                




<div class="line2"></div>
<div class="line2"></div>


<script type="text/javascript" src="../js/script2.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hàm cập nhật tổng giá tiền
        function updateTotalPrice() {
            var quantitySelect = document.querySelector('select[name^="quantity"]');
            var quantity = parseInt(quantitySelect.value);
            var pricePerUnit = <?php echo json_encode($product['product_price']); ?>; // Lấy giá sản phẩm từ PHP
            var totalPrice = quantity * pricePerUnit;

            // Cập nhật tổng giá tiền
            document.getElementById('total-price').textContent = totalPrice.toLocaleString('vi-VN') + ' VNĐ';
        }

        // Thêm sự kiện cho dropdown số lượng
        var quantitySelect = document.querySelector('select[name^="quantity"]');
        quantitySelect.addEventListener('change', updateTotalPrice);

        // Cập nhật giá tiền ngay khi load trang
        updateTotalPrice();
    });
</script>

<script> 
    document.querySelectorAll('select[name^="quantity"]').forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit(); // Xóa bỏ đoạn này
        });
    });
</script>


<script>

    $(document).ready(function() {
        $('#province').change(function() {
            var province_id = $(this).val();
            if (province_id != '') {
                $.ajax({
                    url: "fetch_district.php",
                    method: "POST",
                    data: { province_id: province_id },
                    success: function(data) {
                        $('#district').html(data);
                        $('#wards').html('<option value="">Chọn một xã</option>'); // Reset wards khi chọn lại tỉnh
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
                    data: { district_id: district_id },
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