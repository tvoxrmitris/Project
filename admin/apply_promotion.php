<?php
// Kết nối cơ sở dữ liệu và bắt đầu phiên
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
if (isset($_POST['apply_all_discount'])) {
    $code_discount = $_POST['code_discount'];
    $discount_percent = $_POST['discount_percent'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $usage_limit = $_POST['usage_limit'];

    // Lấy tất cả product_id từ bảng products dựa trên subcategory đã chọn
    $query = "SELECT product_id FROM products WHERE product_subcategory = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $subcategory);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Chèn tất cả các product_id vào bảng product_promotion
        $insert_query = "INSERT INTO product_promotion (product_id, code_discount, discount_percent, start_date, end_date, category_name, subcategory_name, usage_limit, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $insert_stmt = mysqli_prepare($conn, $insert_query);

        while ($row = mysqli_fetch_assoc($result)) {
            $product_id = $row['product_id'];

            mysqli_stmt_bind_param($insert_stmt, 'isissssi', $product_id, $code_discount, $discount_percent, $start_date, $end_date, $category, $subcategory, $usage_limit);

            // Thực hiện chèn dữ liệu vào bảng product_promotion
            if (!mysqli_stmt_execute($insert_stmt)) {
                $message[] = "Lỗi: Không thể áp dụng giảm giá cho sản phẩm với product_id = $product_id.";
            }
        }

        $message[] = "Giảm giá đã được áp dụng thành công cho danh mục tương ứng!";
        mysqli_stmt_close($insert_stmt);
    } else {
        $message[] = "Không có sản phẩm nào thuộc danh mục phụ '$subcategory'.";
    }

    mysqli_stmt_close($stmt);
}



?>

<style type="text/css">
<?php include '../CSS/style.css';

?>.table-container {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

img {
    width: 100px;
    /* Kích thước hình ảnh */
    height: auto;
}

.star-filled {
    color: gold;
    /* Màu sao đầy */
}

.star-empty {
    color: lightgray;
    /* Màu sao rỗng */
}


.action-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.button-container {
    display: flex;
    gap: 10px;
    /* Khoảng cách giữa các nút */
    flex-grow: 1;
    /* Cho phép phần này chiếm nhiều không gian hơn */
    max-width: 90%;
    /* Tăng chiều dài của button-container */
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
    <title>Seraph Beauty - Sản Phẩm Đã Thêm</title>
</head>

<body>
    <?php include '../admin/admin_header.php'; ?>

    <div class="title">
        <h2 style="font-size:50px;">Áp dụng giảm giá</h2>
    </div>

    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '
<div class="message-container">
    <div class="message">
        <span>' . $message . '</span>
        <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
    </div>
</div>

                ';
        }
    }
    ?>
    <?php
    $query = "SELECT code_discount, discount_percent, usage_limit, start_date, end_date FROM promotions";
    $result = mysqli_query($conn, $query);
    ?>

    <section class="add-products form-container">
        <form method="POST" action="" enctype="multipart/form-data">
            <a href="admin_discount.php" class="back-arrow"
                style="text-decoration: none; font-size: 1.5rem;">&#8592;</a>

            <div class="input-field">
                <label>Mã giảm giá<span>*</span></label>
                <select name="code_discount" id="code_discount" required onchange="updateDiscountDetails()">
                    <option value="">Chọn mã giảm giá</option>
                    <?php
                // Kiểm tra xem có kết quả không
                if (mysqli_num_rows($result) > 0) {
                    // Lưu thông tin mã giảm giá, phần trăm và số lượng được áp dụng trong một mảng
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . htmlspecialchars($row['code_discount']) . '" data-discount="' . htmlspecialchars($row['discount_percent']) . '" data-usage="' . htmlspecialchars($row['usage_limit']) . '" data-start="' . htmlspecialchars($row['start_date']) . '" data-end="' . htmlspecialchars($row['end_date']) . '">' . htmlspecialchars($row['code_discount']) . '</option>';
                    }
                } else {
                    echo '<option value="">Không có mã giảm giá nào</option>';
                }
                ?>
                </select>
            </div>

            <div class="input-field">
                <label>Phần trăm giảm giá<span>*</span></label>
                <input type="text" name="discount_percent" id="discount_percent" required readonly>
            </div>

            <div class="input-field">
                <label>Số lượng được áp dụng<span>*</span></label>
                <input type="number" name="usage_limit" id="usage_limit" required readonly>
            </div>

            <div class="input-field">
                <label>Ngày bắt đầu<span>*</span></label>
                <input type="date" name="start_date" id="start_date" required readonly>
            </div>

            <div class="input-field">
                <label>Ngày kết thúc<span>*</span></label>
                <input type="date" name="end_date" id="end_date" required readonly>
            </div>

            <script>
            function updateDiscountDetails() {
                // Lấy giá trị của select
                var select = document.getElementById("code_discount");
                var discountInput = document.getElementById("discount_percent");
                var usageInput = document.getElementById("usage_limit");
                var startInput = document.getElementById("start_date");
                var endInput = document.getElementById("end_date");

                // Lấy giá trị của mã giảm giá đã chọn
                var selectedOption = select.options[select.selectedIndex];

                // Cập nhật giá trị của phần trăm giảm giá, số lượng được áp dụng, ngày bắt đầu và ngày kết thúc
                discountInput.value = selectedOption.getAttribute("data-discount") || '';
                usageInput.value = selectedOption.getAttribute("data-usage") || '';
                startInput.value = selectedOption.getAttribute("data-start") || '';
                endInput.value = selectedOption.getAttribute("data-end") || '';
            }

            function redirectToApplyDiscountProduct() {
                // Lấy giá trị mã giảm giá đã chọn
                var codeDiscount = document.getElementById("code_discount").value;

                // Kiểm tra nếu mã giảm giá được chọn
                if (codeDiscount) {
                    // Thực hiện chuyển hướng và thêm tham số code_discount vào URL
                    window.location.href = "apply_discount_product.php?code_discount=" + codeDiscount;
                } else {
                    alert("Vui lòng chọn mã giảm giá.");
                }
            }
            </script>

            <?php
            // Lấy tất cả danh mục chính
            $query = "SELECT * FROM categories";
            $result = mysqli_query($conn, $query);
            $categories = array();
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $categories[] = $row;
                }
            }

            // Lấy tất cả danh mục phụ
            $query = "SELECT * FROM subcategory";
            $result = mysqli_query($conn, $query);
            $subcategories = array();
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $subcategories[] = $row;
                }
            }
            ?>

            <div class="input-field">
                <label>Danh mục chính<span>*</span></label>
                <select id="mainCategory" name="category">
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['category_id']; ?>" style="text-transform: capitalize;">
                        <?= $category['category_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-field">
                <label>Danh mục phụ<span>*</span></label>
                <select id="subCategory" name="subcategory">
                    <!-- Danh mục phụ sẽ được cập nhật dựa trên danh mục chính được chọn -->
                </select>
            </div>

            <div class="action-buttons">
                <div class="button-container">
                    <input type="submit" name="apply_all_discount" value="Áp dụng cho tất cả">
                    <button type="button" onclick="redirectToApplyDiscountProduct()" class="cancel-button">Áp dụng riêng
                        lẻ</button>
                </div>
            </div>
        </form>
    </section>



    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mainCategorySelect = document.getElementById('mainCategory');
        const subCategorySelect = document.getElementById('subCategory');

        // Chuyển mảng PHP sang đối tượng JavaScript
        const subcategories = <?php echo json_encode($subcategories); ?>;

        function updateSubcategories() {
            const selectedCategoryId = mainCategorySelect.value;

            // Xóa các tùy chọn hiện có
            subCategorySelect.innerHTML = '';

            // Lọc các danh mục phụ dựa trên danh mục chính được chọn
            const filteredSubcategories = subcategories.filter(subcategory => subcategory.category_id ==
                selectedCategoryId);

            // Điền vào dropdown danh mục phụ
            filteredSubcategories.forEach(subcategory => {
                const option = document.createElement('option');
                option.value = subcategory.subcategory_name;
                option.textContent = subcategory.subcategory_name;
                option.style.textTransform = 'capitalize';
                subCategorySelect.appendChild(option);
            });
        }

        // Cập nhật danh mục phụ khi danh mục chính thay đổi
        mainCategorySelect.addEventListener('change', updateSubcategories);

        // Khởi tạo danh mục phụ khi trang được tải
        updateSubcategories();
    });
    </script>


</html>