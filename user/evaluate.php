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

if (isset($_POST['evaluate-btn'])) {
    $order_id_from_url = isset($_GET['order_id']) ? $_GET['order_id'] : '';
    $evaluate_detail = isset($_POST['detail']) ? $_POST['detail'] : '';

    // Kiểm tra xem đơn hàng đã được đánh giá chưa
    $check_evaluation = mysqli_query($conn, "SELECT * FROM `evaluate` WHERE `order_id`='$order_id_from_url'") or die('query failed');

    if (mysqli_num_rows($check_evaluation) > 0) {
        echo '<p>Đơn hàng này đã được đánh giá trước đó!</p>';
    } else {
        // Truy vấn thông tin sản phẩm trong đơn hàng
        $select_order = mysqli_query($conn, "SELECT product_id, product_name, quantity FROM `order_items` 
                                             WHERE order_id='$order_id_from_url'") or die('query failed');

        if (mysqli_num_rows($select_order) > 0) {
            $total_quantity = 0;

            while ($fetch_order = mysqli_fetch_assoc($select_order)) {
                $product_id = $fetch_order['product_id'];
                $product_name = $fetch_order['product_name'];
                $quantity = $fetch_order['quantity'];
                $total_quantity += $quantity;

                // Lấy số sao đánh giá cho sản phẩm cụ thể
                $star_rating = isset($_POST['star_rating_' . $product_id]) ? intval($_POST['star_rating_' . $product_id]) : 0;

                // Kiểm tra và xử lý ảnh
                $evaluate_image_name = '';
                if (isset($_FILES['evaluation_image_' . $product_id]) && $_FILES['evaluation_image_' . $product_id]['error'] === 0) {
                    $image_tmp_name = $_FILES['evaluation_image_' . $product_id]['tmp_name'];
                    $image_name = basename($_FILES['evaluation_image_' . $product_id]['name']);
                    $target_directory = '../image/evaluate/';  // Đường dẫn mới lưu ảnh
                    $target_file = $target_directory . $image_name;

                    // Di chuyển file vào thư mục evaluate
                    if (move_uploaded_file($image_tmp_name, $target_file)) {
                        $evaluate_image_name = $image_name; // Lưu tên ảnh
                    } else {
                        echo '<p>Không thể tải lên hình ảnh!</p>';
                    }
                }

                // Sử dụng prepared statements để chèn dữ liệu và lấy ngày giờ hiện tại từ cơ sở dữ liệu
                $stmt = $conn->prepare("INSERT INTO evaluate (order_id, product_id, user_id, star, product_name, evaluate_detail, evaluate_image, date) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

                $stmt->bind_param('sisisss', $order_id_from_url, $product_id, $user_id, $star_rating, $product_name, $evaluate_detail, $evaluate_image_name);

                if (!$stmt->execute()) {
                    echo '<p>Gửi đánh giá thất bại!</p>';
                }

                $stmt->close();
            }

            // Tính điểm thưởng
            $total_points = $total_quantity * 5;

            // Cập nhật điểm thưởng vào bảng users
            $update_user_points = $conn->prepare("UPDATE `users` SET `point` = `point` + ? WHERE `user_id` = ?");
            $update_user_points->bind_param('ii', $total_points, $user_id);

            if ($update_user_points->execute()) {
                echo '<p>Điểm thưởng đã được cập nhật thành công!</p>';
            } else {
                echo '<p>Cập nhật điểm thưởng thất bại!</p>';
            }

            $update_user_points->close();

            // Sau khi gửi đánh giá thành công, chuyển hướng về trang order.php
            header('Location: order.php');
            exit(); // Đảm bảo dừng mọi thao tác sau khi chuyển hướng
        } else {
            echo '<p>Không tìm thấy đơn hàng với mã này!</p>';
        }
    }
}





?>





<style>
.star {
    color: #ccc;
    cursor: pointer;
    font-size: 24px;
    /* Kích thước ngôi sao */
    transition: color 0.3s ease, font-size 0.2s ease;
    /* Thêm transition cho màu và kích thước */
}

/* Màu của các ngôi sao khi được chọn */
.star.active {
    color: #ffd700;
    /* Màu vàng */
    font-size: 28px;
    /* Kích thước ngôi sao khi được chọn */
}

/* Căn giữa nút "Gửi" */
.evaluate-btn {
    display: block;
    margin: 0 auto;
    text-align: center;
    background-color: #000;
    color: #fff;
    width: 120px;
    /* Đặt chiều rộng cố định */
}

.evaluate-btn:hover {
    background-color: #dcdcdc;
    color: #000;
}

.star-rating-container {
    display: flex;
    align-items: center;
}

.star-rating {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

.rating-text {
    margin-left: 10px;
    /* Khoảng cách giữa sao và văn bản */
    font-size: 16px;
    color: #555;
}
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
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="main.css?v=1.1 <?php echo time(); ?>">
    <title>Home</title>
</head>

<body>
    <?php include '../user/header.php' ?>

    <div class="line"></div>

    <div class="title">
        <h1>Đánh giá đơn hàng</h1>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="evaluate">
            <div class="box-container">
                <?php
                $order_id_from_url = isset($_GET['order_id']) ? $_GET['order_id'] : '';

                if ($order_id_from_url) {
                    // Truy vấn kết hợp hai bảng orders và order_items
                    $select_order = mysqli_query($conn, "SELECT orders.user_name, order_items.product_id, order_items.product_name, order_items.quantity, order_items.price
                    FROM `orders`
                    JOIN `order_items` ON orders.order_id = order_items.order_id
                    WHERE orders.order_id='$order_id_from_url'") or die('query failed');

                    if (mysqli_num_rows($select_order) > 0) {
                ?>
                <div class="box">
                    <?php
                            while ($fetch_order = mysqli_fetch_assoc($select_order)) {
                                $product_id = $fetch_order['product_id'];
                                $product_name = $fetch_order['product_name'];
                                $user_name = $fetch_order['user_name'];
                                $quantity = $fetch_order['quantity'];
                                $price = $fetch_order['price'];

                                // Truy vấn sản phẩm từ bảng products dựa trên product_id
                                $select_product = mysqli_query($conn, "SELECT product_image, color_name FROM `products` WHERE product_id='$product_id'") or die('query failed');

                                if (mysqli_num_rows($select_product) > 0) {
                                    $fetch_product = mysqli_fetch_assoc($select_product);
                                    $images = explode(',', $fetch_product['product_image']);
                                    $color_name = $fetch_product['color_name'];
                            ?>
                    <div class="img-container">
                        <?php
                                        foreach ($images as $image_index => $image) {
                                        ?>
                        <img class="imgshop <?php echo ($image_index !== 0) ? 'hidden' : ''; ?>"
                            src="../image/product/<?php echo $image; ?>" data-index="<?php echo $image_index; ?>">
                        <?php
                                        }
                                        ?>
                    </div>
                    <div class="detail">
                        <div class="name" style="font-weight: bold;"><?php echo $product_name; ?></div>
                        <p>Màu sắc: <?php echo $color_name; ?></p>
                        <p>Số lượng: <?php echo $quantity; ?></p>
                        <p>Giá: <?php echo $price; ?> VND</p>

                        <h5>Chất lượng sản phẩm</h5>
                        <div class="star-rating-container" data-product-id="<?php echo $product_id; ?>">
                            <div class="star-rating">
                                <span class="star" data-value="1">&#9733;</span>
                                <span class="star" data-value="2">&#9733;</span>
                                <span class="star" data-value="3">&#9733;</span>
                                <span class="star" data-value="4">&#9733;</span>
                                <span class="star" data-value="5">&#9733;</span>
                                <input type="hidden" name="star_rating_<?php echo $product_id; ?>"
                                    class="star-rating-input" value="0">
                            </div>
                            <div class="rating-text" id="rating-text-<?php echo $product_id; ?>"></div>
                        </div>
                        <div class="input-field">
                            <label>Ảnh hình đánh giá (nếu có)</label>
                            <input type="file" name="evaluation_image_<?php echo $product_id; ?>" accept="image/*"
                                onchange="previewImage(event, <?php echo $product_id; ?>)"
                                id="file-input-<?php echo $product_id; ?>">
                            <div class="image-preview" id="image-preview-<?php echo $product_id; ?>"
                                style="margin-top: 10px; position: relative;">
                                <!-- Hình ảnh và nút X sẽ được hiển thị tại đây -->
                            </div>
                        </div>


                        <script>
                        function previewImage(event, productId) {
                            const file = event.target.files[0]; // Lấy file đầu tiên được chọn
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    // Tạo thẻ img và nút xóa
                                    const imagePreviewContainer = document.getElementById(
                                        `image-preview-${productId}`);
                                    imagePreviewContainer.innerHTML = `
                <div style="position: relative; display: inline-block;">
                    <img src="${e.target.result}" alt="Preview">
                    <button class="remove-btn" onclick="removeImage(${productId})">×</button>
                </div>
            `;
                                };
                                reader.readAsDataURL(file); // Đọc tệp dưới dạng Data URL
                            }
                        }

                        function removeImage(productId) {
                            // Xóa ảnh hiển thị
                            const imagePreviewContainer = document.getElementById(`image-preview-${productId}`);
                            imagePreviewContainer.innerHTML = "";

                            // Đặt lại giá trị của input file
                            const fileInput = document.getElementById(`file-input-${productId}`);
                            fileInput.value = "";
                        }
                        </script>



                        <div class="input-field">
                            <label>Nội dung đánh giá<span>*</span></label>
                            <textarea placeholder="Hãy chia sẻ nhận xét cho sản phẩm này bạn nhé!" name="detail"
                                required></textarea>
                        </div>
                    </div>
                    <?php
                                } else {
                                    echo '<p>Không tìm thấy sản phẩm: ' . htmlspecialchars($product_name) . '</p>';
                                }
                            }
                            ?>
                </div>
                <?php
                    } else {
                        echo '
                    <div class="empty">
                        <p>Không tìm thấy đơn hàng với mã này!</p>
                    </div>
                ';
                    }
                } else {
                    echo '
                <div class="empty">
                    <p>Không có mã đơn hàng được cung cấp!</p>
                </div>
            ';
                }
                ?>
                <input type="submit" name="evaluate-btn" class="evaluate-btn" value="Gửi">
            </div>
        </div>
    </form>



    <div class="line"></div>

    <?php include '../user/footer.php' ?>

    <script>
    $(document).ready(function() {
        $(".img_order").click(function() {
            var orderId = $(this).data("order-id");
            window.location.href = 'view_order.php?order_id=' + orderId;
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        var dots = document.querySelectorAll('.dot');

        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                var index = parseInt(dot.getAttribute('data-index'));
                var container = dot.closest('.box');
                var images = container.querySelectorAll('.img_order');

                images.forEach(function(img) {
                    img.classList.add('hidden');
                });

                images[index].classList.remove('hidden');

                var productDots = container.querySelectorAll('.dot');
                productDots.forEach(function(d) {
                    d.classList.remove('active');
                });

                dot.classList.add('active');
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const ratingTexts = {
            1: "Tệ",
            2: "Không hài lòng",
            3: "Bình thường",
            4: "Hài lòng",
            5: "Tuyệt vời"
        };

        document.querySelectorAll('.star-rating-container').forEach(function(container) {
            const productId = container.getAttribute('data-product-id');
            const stars = Array.from(container.querySelectorAll('.star'));
            const ratingTextElement = document.getElementById('rating-text-' + productId);
            const input = container.querySelector('.star-rating-input');

            let currentRating = 0;

            function updateStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });
            }

            stars.forEach(function(star, index) {
                star.addEventListener('click', function() {
                    const value = index + 1;
                    currentRating = value;

                    updateStars(value);
                    ratingTextElement.textContent = ratingTexts[value];
                    input.value = value;
                });

                star.addEventListener('mouseover', function() {
                    const hoverValue = index + 1;
                    updateStars(hoverValue);
                    ratingTextElement.textContent = ratingTexts[hoverValue];
                });

                star.addEventListener('mouseout', function() {
                    updateStars(currentRating);
                    ratingTextElement.textContent = currentRating > 0 ? ratingTexts[
                        currentRating] : '';
                });
            });

            container.addEventListener('mouseout', function() {
                updateStars(currentRating);
                ratingTextElement.textContent = currentRating > 0 ? ratingTexts[currentRating] :
                    '';
            });
        });
    });
    </script>

    <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>