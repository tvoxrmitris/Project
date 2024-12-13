<?php
include '../connection/connection.php';
session_start();
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
if (isset($_POST['submit-btn'])) {
    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $number = $_POST['number'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Kiểm tra mật khẩu có khớp không
    if ($password !== $cpassword) {
        echo "Mật khẩu không khớp!";
        exit;
    }

    // Tạo employee_id
    $prefix = "";
    if ($role == "staff") {
        $prefix = "SBNVBH";
    } elseif ($role == "NVNK") {
        $prefix = "SBNVNK";
    }

    // Lấy số thứ tự của employee_id (số thứ tự này cần phải được tính từ cơ sở dữ liệu)
    $sql = "SELECT MAX(employee_id) AS max_id FROM employees WHERE employee_id LIKE '$prefix%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    // Lấy số thứ tự và tăng dần
    $last_id = $row['max_id'];
    $last_number = substr($last_id, -3);
    $next_number = str_pad($last_number + 1, 3, "0", STR_PAD_LEFT);
    $employee_id = $prefix . $next_number;

    // Mã hóa mật khẩu trước khi lưu trữ (sử dụng bcrypt)
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Chèn dữ liệu vào bảng employees
    $sql = "INSERT INTO employees (employee_id, employee_name, employee_number, employee_email, employee_password, employee_type) 
            VALUES ('$employee_id', '$name', '$number', '$email', '$hashed_password', '$role')";

    if ($conn->query($sql) === TRUE) {
        echo "Đăng ký thành công!";
    } else {
        echo "Lỗi: " . $sql . "<br>" . $conn->error;
    }
}
?>



<style type="text/css">
    <?php include '../CSS/style.css';

    ?>.login {
        padding: 40px 0;
        background-color: #fff;
        /* Nền trắng cho phần login */
        text-align: center;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        /* Bóng đổ nhẹ */
        max-width: 50%;
        margin: 0 auto;
    }

    h1 {
        font-size: 36px;
        font-family: 'Georgia', serif;
        color: #000;
        /* Tiêu đề màu đen */
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    /* Nút đăng nhập */
    button {
        background-color: #000;
        /* Màu nền đen cho nút */
        color: #fff;
        /* Chữ trắng */
        border: none;
        padding: 12px 24px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    /* Hiệu ứng khi hover lên nút */
    button:hover {
        background-color: #444;
        /* Đổi thành màu xám khi hover */
        transform: scale(1.05);
    }

    /* Phần login email */
    .loginemail input {
        width: 60%;
        padding: 15px;
        margin: 10px 0;
        border: 2px solid #ccc;
        /* Đường viền xám nhạt */
        border-radius: 5px;
        font-size: 16px;
        background-color: #f4f4f4;
        /* Nền xám nhạt */
    }

    /* Thông báo lỗi và thành công */
    .message {
        background-color: #f8d7da;
        /* Màu thông báo lỗi */
        color: #721c24;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #f5c6cb;
        border-radius: 5px;
    }

    /* Các biểu tượng đóng thông báo */
    .message i {
        cursor: pointer;
    }

    /* Đường viền và hiệu ứng các phần tử khác */
    input[type="text"]:focus {
        border-color: #000;
        /* Đổi màu đường viền khi focus */
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.8);
        /* Hiệu ứng bóng đổ */
    }

    a {
        color: #000;
        /* Liên kết màu đen */
        text-decoration: none;
        font-weight: bold;
    }

    a:hover {
        color: #444;
        /* Màu xám khi hover */
    }

    /* Định dạng cho phần đăng ký */
    .register p {
        font-size: 14px;
        margin-top: 10px;
    }

    .or {
        font-size: 16px;
        font-weight: bold;
        color: #000;
    }

    .continue {
        font-size: 18px;
        color: #333;
        font-weight: 600;
    }

    /* Thêm các phong cách cho nút đăng nhập loại tài khoản */
    .account-type {
        margin: 20px 0;
    }

    .account-btn {
        background-color: #000;
        /* Màu nền đen cho nút */
        color: #fff;
        /* Chữ trắng */
        border: none;
        padding: 12px 24px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .account-btn:hover {
        background-color: #444;
        /* Đổi màu khi hover */
        transform: scale(1.05);
    }

    /* CSS cho phần chọn vai trò (select) */
    .role-select {
        width: 60%;
        padding: 15px;
        margin: 10px 0;
        border: 2px solid #ccc;
        border-radius: 5px;
        background-color: #f4f4f4;
        font-size: 16px;
        color: #000;
        appearance: none;
        /* Loại bỏ kiểu mặc định của select */
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
    }

    /* Hiệu ứng khi select được focus */
    .role-select:focus {
        border-color: #000;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.8);
    }

    /* Định dạng cho các option trong select */
    .role-select option {
        padding: 10px;
        font-size: 16px;
        background-color: #fff;
        color: #000;
    }

    /* Đổi màu cho các option khi hover */
    .role-select option:hover {
        background-color: #f0f0f0;
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
    <title>Seraph Beauty - Đăng Ký</title>
</head>

<body>


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
    <header class="header">
        <div class="flex">
            <img src="../image/seraph.png" width="100">
        </div>
    </header>
    <div class="line"></div>
    <section class="login">
        <div class="header_guest">
            <div class="header-main">
                <h1>Tài Khoản Quản Trị - Seraph Beauty</h1>

                <!-- Form đăng ký tài khoản -->
                <form id="loginForm" method="post">
                    <div class="loginemail">
                        <input type="text" name="name" placeholder="Họ và tên*" required>
                        <input type="tel" name="number" placeholder="Số điện thoại*" required>
                        <input type="email" name="email" placeholder="Email*" required>
                        <select name="role" class="role-select" required>
                            <option value="staff">Nhân viên bán hàng</option>
                            <option value="NVNK">Nhân viên nhập kho</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="input-field">
                            <label for="province">Tỉnh/Thành phố<span>*</span></label>
                            <select id="province" name="province" class="form-control" required>
                                <option value="">Chọn một tỉnh</option>
                                <?php
                                $sql = "SELECT * FROM province";
                                $result = mysqli_query($conn, $sql);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='{$row['province_id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="input-field">
                            <label for="district">Quận/Huyện<span>*</span></label>
                            <select id="district" name="district" class="form-control" required>
                                <option value="">Chọn một quận/huyện</option>
                            </select>
                        </div>

                        <div class="input-field">
                            <label for="wards">Phường/Xã<span>*</span></label>
                            <select id="wards" name="wards" class="form-control" required>
                                <option value="">Chọn một xã</option>
                            </select>
                        </div>







                        <div class="input-field">
                            <label>Tên đường, tòa nhà, số nhà<span>*</span></label>
                            <input type="text" id="flat" name="flat" placeholder="Nhập tên đường, tòa nhà, số nhà">
                        </div>
                        <input type="password" name="password" placeholder="Mật khẩu*" required>
                        <input type="password" name="cpassword" placeholder="Nhập lại mật khẩu*" required>

                        <!-- Chọn vai trò -->


                    </div>

                    <div class="goon">
                        <button type="submit" name="submit-btn">Đăng ký</button>
                    </div>
                </form>
            </div>
        </div>
    </section>


    <div class="line"></div>
</body>
<script>
    $(document).ready(function() {
        var shipping_fee = 0; // Biến lưu trữ phí vận chuyển

        // Khi thay đổi tỉnh
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
                        $('#wards').html('<option value="">Chọn một xã</option>');
                    }
                });
            }
        });

        // Khi thay đổi quận
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
            }
        });

        // Khi thay đổi phường/xã
        $('#wards').change(function() {
            calculateShipping(); // Tính phí vận chuyển khi chọn đủ địa chỉ
        });

        function calculateShipping() {
            var province = $('#province option:selected').text();
            var district = $('#district option:selected').text();
            var ward = $('#wards option:selected').text();

            if (province && district && ward) {
                $.ajax({
                    url: "calculate_shipping.php",
                    method: "POST",
                    dataType: "json",
                    data: {
                        province: province,
                        district: district,
                        ward: ward
                    },
                    success: function(response) {
                        if (response.error) {
                            $('#distance-result').html(response.error);
                            $('#shipping_result').html('');
                            shipping_fee = 0;
                        } else {
                            $('#distance-result').html(+response.distance + " km");
                            $('#shipping_result').html(+response.shipping_fee +
                                " VNĐ");
                            shipping_fee = parseInt(response.shipping_fee);
                            $('#shipping_fee').val(shipping_fee);
                            updateGrandTotal();
                        }
                    },
                    error: function() {
                        $('#distance-result').html('Không thể tính khoảng cách.');
                        $('#shipping_result').html('Không thể tính phí ship.');
                        shipping_fee = 0;
                        updateGrandTotal();
                    }
                });
            }
        }

        function updateGrandTotal() {
            var grand_total = <?php echo $grand_total; ?>;
            var final_total = grand_total + shipping_fee;
            $('#grand_total').val(final_total);
            $('#grand_total_display').html(new Intl.NumberFormat('vi-VN').format(final_total) + " VNĐ");
        }
    });
</script>

</html>