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

// Lấy tên địa phương từ ID
$province_query = mysqli_query($conn, "SELECT name FROM province WHERE province_id = '$province_id'");
$district_query = mysqli_query($conn, "SELECT name FROM district WHERE district_id = '$district_id'");
$wards_query = mysqli_query($conn, "SELECT name FROM wards WHERE wards_id = '$wards_id'");

$province_name = mysqli_fetch_assoc($province_query)['name'];
$district_name = mysqli_fetch_assoc($district_query)['name'];
$wards_name = mysqli_fetch_assoc($wards_query)['name'];

$full_address = "$flat, $wards_name, $district_name, $province_name";

// Kiểm tra nếu có product_id trong URL
if (isset($_GET['product_id'])) {
// Lấy product_id từ URL
$product_id = $_GET['product_id'];

// Lấy chi tiết sản phẩm từ product_id
$select_product = mysqli_query($conn, "SELECT product_price, product_name, quantity_in_stock, product_image FROM `products` WHERE product_id = '$product_id'")
or die('Lỗi truy vấn: ' . mysqli_error($conn));
$fetch_product = mysqli_fetch_assoc($select_product);

if ($fetch_product) {
$price = $fetch_product['product_price'];
$product_name = $fetch_product['product_name'];

// Giả sử product_image lưu nhiều hình ảnh ngăn cách bởi dấu phẩy
$product_images = explode(',', $fetch_product['product_image']);
$first_image = trim($product_images[0]); // Lấy hình ảnh đầu tiên

$quantity_in_stock = $fetch_product['quantity_in_stock'];

// Giả sử số lượng là 1 nếu không chọn từ giỏ hàng
$quantity = 1;
$total_price = $price * $quantity;

// Thêm thông tin vào bảng orders
$insert_order = mysqli_query($conn, "INSERT INTO `orders` (user_id, user_name, user_number, user_email, method, address, total_products, total_price, placed_on, payment_status)
VALUES ('$user_id', '$name', '$number', '$email', '$method', '$full_address', '$quantity', '$total_price', '$placed_on', '$payment_status')")
or die('Lỗi truy vấn: ' . mysqli_error($conn));

// Lấy ID của đơn hàng vừa tạo
$order_id = mysqli_insert_id($conn);

// Thêm vào bảng order_items
$insert_order_item = mysqli_query($conn, "INSERT INTO `order_items` (order_id, product_id, product_name, quantity, price, total_price, product_image)
VALUES ('$order_id', '$product_id', '$product_name', '$quantity', '$price', '$total_price', '$first_image')")
or die('Lỗi truy vấn: ' . mysqli_error($conn));

// Cập nhật số lượng tồn kho trong bảng products
$new_quantity_in_stock = $quantity_in_stock - $quantity;
if ($new_quantity_in_stock < 0) {
    $new_quantity_in_stock=0; // Đảm bảo không giảm xuống số âm
    }

    $update_quantity=mysqli_query($conn, "UPDATE `products` SET quantity_in_stock = '$new_quantity_in_stock' WHERE product_id = '$product_id'" )
    or die('Lỗi truy vấn: ' . mysqli_error($conn));

            echo "<p>Đơn hàng của bạn đã được đặt thành công!</p>";
        } else {
            echo "<p>Sản phẩm không tồn tại.</p>";
        }
    } else {
        // Nếu không có product_id, tiến hành logic đặt hàng với toàn bộ giỏ hàng
        $select_cart = mysqli_query($conn, "SELECT * FROM `cart`") or die(' Lỗi truy vấn: ' . mysqli_error($conn));

        if (mysqli_num_rows($select_cart) > 0) {
            // Tổng số lượng và giá trị đơn hàng
            $grand_total = 0;
            $total_quantity = 0;

            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                $product_id = $fetch_cart[' product_id'];
    $quantity=$fetch_cart['quantity'];

    // Lấy giá sản phẩm từ bảng products
    $select_product=mysqli_query($conn, "SELECT product_price FROM `products` WHERE product_id = '$product_id'" ) or die('Lỗi truy vấn: ' . mysqli_error($conn));
                $fetch_product = mysqli_fetch_assoc($select_product);

                $price = $fetch_product ? $fetch_product[' product_price'] : 0;
    $total_price=$price * $quantity;

    $grand_total +=$total_price;
    $total_quantity +=$quantity;
    }

    // Thêm thông tin vào bảng orders
    $insert_order=mysqli_query($conn, "INSERT INTO `orders` (user_id, user_name, user_number, user_email, method, address, total_products, total_price, placed_on, payment_status) 
                    VALUES ('$user_id', '$name', '$number', '$email', '$method', '$full_address', '$total_quantity', '$grand_total', '$placed_on', '$payment_status')" )
    or die('Lỗi truy vấn: ' . mysqli_error($conn));

            // Lấy ID của đơn hàng vừa tạo
            $order_id = mysqli_insert_id($conn);

            // Duyệt lại giỏ hàng và thêm vào bảng order_items
            mysqli_data_seek($select_cart, 0); // Đặt lại con trỏ của kết quả về đầu
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                $product_id = $fetch_cart[' product_id'];
    $quantity=$fetch_cart['quantity'];
    $product_name=mysqli_real_escape_string($conn, $fetch_cart['product_name']);

    // Giả sử product_image lưu nhiều hình ảnh ngăn cách bởi dấu phẩy
    $product_images=explode(',', $fetch_cart['product_image']);
    $first_image=trim($product_images[0]); // Lấy hình ảnh đầu tiên

    // Lấy giá sản phẩm và số lượng tồn kho từ bảng products
    $select_product=mysqli_query($conn, "SELECT product_price, quantity_in_stock FROM `products` WHERE product_id = '$product_id'" ) or die('Lỗi truy vấn: ' . mysqli_error($conn));
                $fetch_product = mysqli_fetch_assoc($select_product);
                $price = $fetch_product ? $fetch_product[' product_price'] : 0;
    $quantity_in_stock=$fetch_product['quantity_in_stock'];

    $total_price=$price * $quantity;

    // Thêm vào bảng order_items
    $insert_order_item=mysqli_query($conn, "INSERT INTO `order_items` (order_id, product_id, product_name, quantity, price, total_price, product_image)
                        VALUES ('$order_id', '$product_id', '$product_name', '$quantity', '$price', '$total_price', '$first_image')" )
    or die('Lỗi truy vấn: ' . mysqli_error($conn));

                // Cập nhật số lượng tồn kho trong bảng products
                $new_quantity_in_stock = $quantity_in_stock - $quantity;
                if ($new_quantity_in_stock < 0) {
                    $new_quantity_in_stock = 0; // Đảm bảo không giảm xuống số âm
                }

                $update_quantity = mysqli_query($conn, "UPDATE `products` SET quantity_in_stock = ' $new_quantity_in_stock' WHERE product_id='$product_id'")
                    or die('Lỗi truy vấn: ' . mysqli_error($conn));
            }

            // Sau khi đặt hàng, xóa giỏ hàng của người dùng
            mysqli_query($conn, " DELETE FROM `cart`") or die('Lỗi truy vấn: ' . mysqli_error($conn));

            echo "<p>Đơn hàng của bạn đã được đặt thành công!</p>";
        } else {
            echo "<p>Giỏ hàng của bạn trống, không thể đặt hàng.</p>";
        }
    }
}

thêm điều kiện nếu không có product_id trên link sẽ insert dữ liệu trong session cart vào bảng orders và order_items