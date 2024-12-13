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


$brand_name = "";
if (isset($_GET['brand_id'])) {
    $brand_id = $_GET['brand_id'];

    // Truy vấn để lấy tên thương hiệu từ ID
    $select_brand_name = $conn->prepare("SELECT brand_name FROM brands WHERE brand_id = ?");
    $select_brand_name->bind_param("i", $brand_id);
    $select_brand_name->execute();
    $result_brand_name = $select_brand_name->get_result();

    // Kiểm tra và gán tên thương hiệu nếu có kết quả trả về từ cơ sở dữ liệu
    if ($result_brand_name->num_rows > 0) {
        $brand_name = $result_brand_name->fetch_assoc()['brand_name'];
    }
}







?>



<style type="text/css">
    <?php include '../CSS/main.css' ?>
</style>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <!-- Các phần head của bạn -->
</head>

<body>

    <?php include '../admin/admin_header.php'; ?>

    <section class="shop">
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

        <div class="detail" style="text-align: center;">
            <h1 style="font-size: 50px;">Thương hiệu <?php echo $brand_name; ?></h1>
        </div>



        <div class="box-container">

            <?php
            if (isset($_GET['brand_id'])) {
                $brand_id = $_GET['brand_id'];

                // Lấy tên thương hiệu dựa trên ID thương hiệu đã chọn
                $select_brands = $conn->prepare("SELECT brand_name FROM brands WHERE brand_id = ?");
                $select_brands->bind_param("i", $brand_id);
                $select_brands->execute();
                $result_brands = $select_brands->get_result();

                if ($result_brands->num_rows > 0) {
                    $brand_name = $result_brands->fetch_assoc()['brand_name'];

                    // Lấy sản phẩm liên quan đến tên thương hiệu đã chọn
                    $status = 'Còn hàng';
                    $select_products = $conn->prepare("SELECT * FROM `products` WHERE BINARY brand_name = ? AND status = ?");
                    $select_products->bind_param("ss", $brand_name, $status);
                    $select_products->execute();

                    $result_products = $select_products->get_result();

                    if ($result_products->num_rows > 0) {
                        while ($fetch_products = $result_products->fetch_assoc()) {
                            $image_names = explode(',', $fetch_products['product_image']);
                            $selectedCapacity = '10ml';
            ?>
                            <!-- Hiển thị thông tin sản phẩm -->
                            <form method="post" class="box">
                                <div class="img-container">
                                    <?php foreach ($image_names as $index => $image_name) { ?>
                                        <img class="imgshop <?php if ($index !== 0) echo 'hidden'; ?>"
                                            src="../image/<?php echo $image_name; ?>" data-index="<?php echo $index; ?> ">
                                    <?php } ?>
                                    <div class="status" style="z-index:1" ;><?php echo $status; ?></div>
                                </div>

                                <div class="dot-container" style="z-index:1" ;>
                                    <?php for ($i = 0; $i < count($image_names); $i++) { ?>
                                        <span class="dot <?php if ($i === 0) echo 'active'; ?>" data-index="<?php echo $i; ?>"
                                            data-product-id="<?php echo $fetch_products['product_id']; ?>"></span>
                                    <?php } ?>
                                </div>
                                <div class="priceshop">
                                    <?php
                                    // Hiển thị giá cho dung tích 10ml
                                    echo number_format($fetch_products['pricefor10ml'], 0, '.', '.') . 'VNĐ';
                                    ?>
                                </div>
                                <div class="name" style="z-index:1" ;><?php echo $fetch_products['product_name']; ?></div>
                                <div class="capacity-buttons" style="z-index:1" ;>
                                    <button type="button" onclick="selectCapacity(event, '10ml')" data-capacity="10ml"
                                        <?php if ($selectedCapacity == '10ml') echo 'class="selected"'; ?>>10ml</button>
                                    <button type="button" onclick="selectCapacity(event, '50ml')" data-capacity="50ml"
                                        <?php if ($selectedCapacity == '50ml') echo 'class="selected"'; ?>>50ml</button>
                                    <button type="button" onclick="selectCapacity(event, '75ml')" data-capacity="75ml"
                                        <?php if ($selectedCapacity == '75ml') echo 'class="selected"'; ?>>75ml</button>
                                    <button type="button" onclick="selectCapacity(event, '100ml')" data-capacity="100ml"
                                        <?php if ($selectedCapacity == '100ml') echo 'class="selected"'; ?>>100ml</button>
                                    <input type="hidden" name="selectedCapacity" value="<?php echo $selectedCapacity; ?>">

                                </div>

                                <input type="hidden" name="product_id" value="<?php echo $fetch_products['product_id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo $fetch_products['product_name']; ?>">
                                <input type="hidden" name="product_image" value="<?php echo $fetch_products['product_image']; ?>">

                                <div class="form-buttons" style="z-index:1;">
                                    <a href="edit_product_brand.php?edit=<?php echo $fetch_products['product_id']; ?>"
                                        class="edit">Chỉnh sửa</a>
                                    <a href="edit_product_brand.php?delete=<?php echo $fetch_products['product_id']; ?>" class="delete"
                                        onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này');">Xóa</a>
                                </div>
                            </form>
            <?php
                        }
                    } else {
                        echo "Không tìm thấy sản phẩm cho thương hiệu này.";
                    }
                } else {
                    echo "Không tìm thấy thương hiệu.";
                }
            } else {
                echo "Không có ID thương hiệu được cung cấp.";
            }
            ?>
        </div>

        </div>
        <div class="title">
            <h1 style="font-size: 45px;">Sản phẩm đang hết hàng</h1>
        </div>

        <div class="box-container">
            <?php
            if (isset($_GET['brand_id'])) {
                $brand_id = $_GET['brand_id'];

                // Lấy tên thương hiệu dựa trên ID thương hiệu đã chọn
                $select_brands = $conn->prepare("SELECT brand_name FROM brands WHERE brand_id = ?");
                $select_brands->bind_param("i", $brand_id);
                $select_brands->execute();
                $result_brands = $select_brands->get_result();

                if ($result_brands->num_rows > 0) {
                    $brand_name = $result_brands->fetch_assoc()['brand_name'];

                    // Lấy sản phẩm liên quan đến tên thương hiệu đã chọn
                    $status = 'Hết hàng';
                    $select_products = $conn->prepare("SELECT * FROM `products` WHERE BINARY brand_name = ? AND status = ?");
                    $select_products->bind_param("ss", $brand_name, $status);
                    $select_products->execute();

                    $result_products = $select_products->get_result();

                    if ($result_products->num_rows > 0) {
                        while ($fetch_products = $result_products->fetch_assoc()) {
                            $image_names = explode(',', $fetch_products['product_image']);
                            $selectedCapacity = '10ml';
            ?>
                            <!-- Hiển thị thông tin sản phẩm -->
                            <form method="post" class="box">
                                <div class="img-container">
                                    <?php foreach ($image_names as $index => $image_name) { ?>
                                        <img class="imgshop <?php if ($index !== 0) echo 'hidden'; ?>"
                                            src="../image/<?php echo $image_name; ?>" data-index="<?php echo $index; ?> ">
                                    <?php } ?>
                                    <div class="status" style="z-index:1" ;><?php echo $status; ?></div>
                                </div>

                                <div class="dot-container" style="z-index:1" ;>
                                    <?php for ($i = 0; $i < count($image_names); $i++) { ?>
                                        <span class="dot <?php if ($i === 0) echo 'active'; ?>" data-index="<?php echo $i; ?>"
                                            data-product-id="<?php echo $fetch_products['product_id']; ?>"></span>
                                    <?php } ?>
                                </div>
                                <div class="priceshop">
                                    <?php
                                    // Hiển thị giá cho dung tích 10ml
                                    echo number_format($fetch_products['pricefor10ml'], 0, '.', '.') . 'VNĐ';
                                    ?>
                                </div>
                                <div class="name" style="z-index:1" ;><?php echo $fetch_products['product_name']; ?></div>
                                <div class="capacity-buttons" style="z-index:1" ;>
                                    <button type="button" onclick="selectCapacity(event, '10ml')" data-capacity="10ml"
                                        <?php if ($selectedCapacity == '10ml') echo 'class="selected"'; ?>>10ml</button>
                                    <button type="button" onclick="selectCapacity(event, '50ml')" data-capacity="50ml"
                                        <?php if ($selectedCapacity == '50ml') echo 'class="selected"'; ?>>50ml</button>
                                    <button type="button" onclick="selectCapacity(event, '75ml')" data-capacity="75ml"
                                        <?php if ($selectedCapacity == '75ml') echo 'class="selected"'; ?>>75ml</button>
                                    <button type="button" onclick="selectCapacity(event, '100ml')" data-capacity="100ml"
                                        <?php if ($selectedCapacity == '100ml') echo 'class="selected"'; ?>>100ml</button>
                                    <input type="hidden" name="selectedCapacity" value="<?php echo $selectedCapacity; ?>">

                                </div>

                                <input type="hidden" name="product_id" value="<?php echo $fetch_products['product_id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo $fetch_products['product_name']; ?>">
                                <input type="hidden" name="product_image" value="<?php echo $fetch_products['product_image']; ?>">
                                <div class="form-buttons" style="z-index:1;">
                                    <a href="edit_product_brand?edit=<?php echo $fetch_products['product_id']; ?>" class="edit">Chỉnh
                                        sửa</a>
                                    <a href="edit_product_brand.php?delete=<?php echo $fetch_products['product_id']; ?>" class="delete"
                                        onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này');">Xóa</a>
                                </div>
                            </form>
            <?php
                        }
                    } else {
                        echo "Thương hiệu không có sản phẩm hết hàng";
                    }
                } else {
                    echo "Không tìm thấy thương hiệu.";
                }
            } else {
                echo "Không có ID thương hiệu được cung cấp.";
            }
            ?>
        </div>

        </div>





    </section>
    <div class="line"></div>

    <script type="text/javascript">
        document.getElementById('cancel-form').addEventListener('click', function() {
            window.location.href = '../admin/edit_product_brand.php';
        });
    </script>

    <script>
        function selectCapacity(event, capacity) {
            var container = event.target.closest('.box');
            var capacityButtons = container.querySelectorAll('.capacity-buttons button');
            capacityButtons.forEach(function(button) {
                button.classList.remove('selected');
            });
            event.target.classList.add('selected');
            var selectedCapacity = capacity;
            container.querySelector('input[name="selectedCapacity"]').value =
                selectedCapacity; // Cập nhật giá trị dung tích cho trường input ẩn

            var productId = container.querySelector('input[name="product_id"]').value;
            // Gửi selectedCapacity đến server để lấy giá tương ứng từ cơ sở dữ liệu
            $.ajax({
                url: '../user/get_price.php',
                type: 'POST',
                data: {
                    productId: productId,
                    selectedCapacity: selectedCapacity
                },
                success: function(response) {
                    container.querySelector('.priceshop').innerHTML = response;
                }
            });
        }
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Lấy tất cả các điểm đánh dấu
            var dots = document.querySelectorAll('.dot');

            // Lặp qua từng điểm đánh dấu và thêm sự kiện click
            dots.forEach(function(dot) {
                dot.addEventListener('click', function() {
                    // Lấy chỉ số của điểm đánh dấu được nhấp
                    var index = parseInt(dot.getAttribute('data-index'));

                    // Lấy ID sản phẩm tương ứng
                    var productId = dot.getAttribute('data-product-id');

                    // Lấy container của sản phẩm
                    var container = dot.closest('.box');

                    // Lấy tất cả các ảnh của sản phẩm
                    var images = container.querySelectorAll('.imgshop');

                    // Ẩn tất cả các ảnh của sản phẩm và bỏ đi lớp 'active' khỏi các điểm đánh dấu
                    images.forEach(function(image) {
                        image.classList.add('hidden');
                    });
                    dots.forEach(function(d) {
                        d.classList.remove('active');
                    });

                    // Hiển thị ảnh tương ứng với điểm đánh dấu được nhấp và thêm lớp 'active' cho điểm đánh dấu đó
                    images[index].classList.remove('hidden');
                    dot.classList.add('active');
                });
            });
        });
    </script>




    <script src="http://cdnjs.cloudflare.com/ajax.libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script type="text/javascript" src="../js/script2.js"></script>

</body>

</html>