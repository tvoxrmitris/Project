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

    // Kiểm tra điều kiện mật khẩu
    if ($password != $cpassword) {
        $message[] = 'Mật khẩu không khớp, vui lòng thử lại';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\W)(?=.{8,})/', $password)) {
        $message[] = 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm ít nhất 1 chữ cái viết hoa và 1 ký tự đặc biệt';
    } else {
        // Lấy các dữ liệu địa chỉ từ form
        $province_id = $_POST['province'];
        $district_id = $_POST['district'];
        $wards_id = $_POST['wards'];
        $flat = $_POST['flat'];

        // Lấy tên của tỉnh, huyện, xã từ cơ sở dữ liệu
        $province_sql = "SELECT name FROM province WHERE province_id = '$province_id'";
        $province_result = mysqli_query($conn, $province_sql);
        $province_name = mysqli_fetch_assoc($province_result)['name'];

        $district_sql = "SELECT name FROM district WHERE district_id = '$district_id'";
        $district_result = mysqli_query($conn, $district_sql);
        $district_name = mysqli_fetch_assoc($district_result)['name'];

        $wards_sql = "SELECT name FROM wards WHERE wards_id = '$wards_id'";
        $wards_result = mysqli_query($conn, $wards_sql);
        $wards_name = mysqli_fetch_assoc($wards_result)['name'];

        // Địa chỉ đầy đủ
        $full_address = "$flat, $wards_name, $district_name, $province_name";

        // Tạo employee_id
        $prefix = "";
        if ($role == "staff") {
            $prefix = "SBNVBH";
        } elseif ($role == "NVNK") {
            $prefix = "SBNVNK";
        }

        // Lấy số thứ tự của employee_id
        $sql = "SELECT MAX(employee_id) AS max_id FROM employees WHERE employee_id LIKE '$prefix%'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        // Lấy số thứ tự và tăng dần
        $last_id = $row['max_id'];
        $last_number = substr($last_id, -3);
        $next_number = str_pad((int)$last_number + 1, 3, "0", STR_PAD_LEFT);

        $employee_id = $prefix . $next_number;

        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Chèn dữ liệu vào bảng employees
        $sql = "INSERT INTO employees (employee_id, employee_name, employee_number, employee_email, employee_address, employee_password, employee_type) 
                VALUES ('$employee_id', '$name', '$number', '$email', '$full_address', '$hashed_password', '$role')";

        if ($conn->query($sql) === TRUE) {
            $message[] = 'Đăng ký thành công!';
        } else {
            echo "Lỗi: " . $sql . "<br>" . $conn->error;
        }
    }

    // Hiển thị thông báo lỗi nếu có
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo "<p>$msg</p>";
        }
    }
}
?>





<style type="text/css">
<?php include '../CSS/style.css';

?>form {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
}

.loginemail {
    width: 48%;
}

.address-container {
    width: 48%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.address-container input,
.address-container select {
    width: 90%;
    padding: 15px;
    margin: 10px 0;
    border: 2px solid #ccc;
    border-radius: 5px;
    background-color: #f4f4f4;
    font-size: 16px;
}

.address-container select {
    background-color: #f4f4f4;
}

.loginemail input,
.loginemail select {
    width: 90%;
}

.login {
    padding: 40px 0;
    background-color: #fff;
    text-align: center;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    max-width: 50%;
    margin: 0 auto;
}

h1 {
    font-size: 36px;
    font-family: 'Georgia', serif;
    color: #000;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 2rem;
}

.goon {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    /* Căn giữa ngang */
    align-items: center;
    /* Căn giữa dọc nếu cần */
    margin-left: 20rem;
}


button {
    background-color: #000;
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 30px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
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

.address-container .loginemail input {
    width: 188%;
    padding: 15px;
    margin: 10px 0;
    border: 2px solid #ccc;
    /* Đường viền xám nhạt */
    border-radius: 5px;
    font-size: 16px;
    background-color: #f4f4f4;
    /* Nền xám nhạt */
}

/* Phần login email */
.loginemail input {
    width: 90%;
    padding: 15px;
    margin: 10px 0;
    border: 2px solid #ccc;
    /* Đường viền xám nhạt */
    border-radius: 5px;
    font-size: 16px;
    background-color: #f4f4f4;
    /* Nền xám nhạt */
}

button:hover {
    background-color: #444;
    transform: scale(1.05);
}

.message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
}

.message i {
    cursor: pointer;
}

input[type="text"]:focus {
    border-color: #000;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.8);
}

a {
    color: #000;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    color: #444;
}

.goon {
    margin-top: 20px;
}

.account-type {
    margin: 20px 0;
}

.account-btn {
    background-color: #000;
    color: #fff;
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
    transform: scale(1.05);
}

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
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
}

.role-select:focus {
    border-color: #000;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.8);
}

.role-select option {
    padding: 10px;
    font-size: 16px;
    background-color: #fff;
    color: #000;
}

.role-select option:hover {
    background-color: #f0f0f0;
}

.back-arrow {
    position: absolute;
    top: 205px;
    left: 390px;
    /* Khoảng cách từ phía trái */
    font-size: 30px;
    /* Kích thước dấu mũi tên */
    color: #000;
    /* Màu sắc của mũi tên */
    text-decoration: none;
    /* Xóa gạch chân */
}

.back-arrow:hover {
    color: #666;
    /* Màu khi hover */
}

span {
    text-align: center;
    font-size: 1rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    font-weight: bold;
}
</style>
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
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="../CSS/style.css?v=1.1 <?php echo time(); ?>">
    <title>Seraph Beauty - Đăng Ký</title>
</head>

<body>



    <header class="header">
        <div class="flex">
            <img src="../image/seraph.png" width="100">
        </div>
    </header>
    <div class="line"></div>
    <section class="login">
        <div class="header_guest">
            <div class="header-main">
                <a href="manage_user.php" class="back-arrow">&#8592;</a> <!-- Thêm dấu mũi tên -->
                <h1>Tài Khoản Quản Trị - Seraph Beauty</h1>

                <form id="loginForm" method="post">
                    <div class="loginemail">
                        <span style="margin-right: 9rem;">Thông tin cá nhân</span>
                        <input type="text" name="name" placeholder="Họ và tên*" required>
                        <input type="tel" name="number" placeholder="Số điện thoại*" required>
                        <input type="email" name="email" placeholder="Email*" required>
                        <span style="margin-right: 10rem;">Thông tin địa chỉ</span>
                        <select class="role-select" id="province" name="province" class="form-control" required>
                            <option value="">Chọn một tỉnh</option>
                            <?php
                            $sql = "SELECT * FROM province";
                            $result = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='{$row['province_id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>

                        <select class="role-select" id="district" name="district" class="form-control" required>
                            <option value="">Chọn một quận/huyện</option>
                        </select>
                        <select class="role-select" id="wards" name="wards" class="form-control" required>
                            <option value="">Chọn một xã</option>
                        </select>

                        <input type="text" id="flat" name="flat" placeholder="Nhập tên đường, tòa nhà, số nhà">
                    </div>

                    <!-- Div bọc phần nhập địa chỉ, để căn sang bên phải -->
                    <div class="address-container">
                        <span style="margin-right: 17rem;">Chức vụ</span>
                        <select name="role" class="role-select" required>
                            <option value="staff">Nhân viên bán hàng</option>
                            <option value="NVNK">Nhân viên nhập kho</option>
                            <!-- <option value="admin">Admin</option> -->
                        </select>

                        <div class="loginemail">
                            <span style="margin-right: 5rem;">Mật khẩu</span>
                            <input type="password" name="password" placeholder="Mật khẩu*" required>
                            <input type="password" name="cpassword" placeholder="Nhập lại mật khẩu*" required>
                        </div>
                    </div>

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
    $('#province').change(function() {
        var province_id = $(this).val();

        // In ra province_id trên Console
        console.log("Province ID:", province_id);

        if (province_id) {
            $.ajax({
                url: 'get_districts.php', // File PHP xử lý
                type: 'POST',
                data: {
                    province_id: province_id
                },
                success: function(data) {
                    console.log("Dữ liệu huyện nhận được từ server:",
                        data); // In dữ liệu trả về trên Console
                    var districts = JSON.parse(data);
                    $('#district').empty(); // Xóa các option cũ
                    $('#district').append(
                        '<option value="">Chọn một quận/huyện</option>'); // Option mặc định

                    $.each(districts, function(index, district) {
                        $('#district').append('<option value="' + district
                            .district_id + '">' + district.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Lỗi khi tải quận/huyện:", error); // In lỗi ra Console
                }
            });
        } else {
            $('#district').empty();
            $('#district').append('<option value="">Chọn một quận/huyện</option>');
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
});
</script>


</html>