<?php
include '../connection/connection.php';
session_start();
// Kiểm tra xem người dùng có đăng nhập hay không
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = session_id(); // Nếu chưa đăng nhập, sử dụng session_id() làm định danh tạm thời
}

// Fetch product_id from URL
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id > 0) {
    $select_product_query = mysqli_query($conn, "SELECT * FROM products WHERE product_id = '$product_id'") or die('Query failed: ' . mysqli_error($conn));

    if (mysqli_num_rows($select_product_query) > 0) {
        $product = mysqli_fetch_assoc($select_product_query);
    } else {
        $product = null; // No product found
    }
} else {
    $product = null; // Invalid product_id
}

// Handle adding to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];

    // Lấy số lượng từ input hidden
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $fetch_product = $result->fetch_assoc();
        $product_name = $fetch_product['product_name'];
        $product_image = explode(',', $fetch_product['product_image'])[0];

        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $cart_result = $stmt->get_result();

        if ($cart_result->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
            $stmt->execute();
            $message[] = 'Sản phẩm đã có trong giỏ hàng, số lượng đã được cập nhật!';
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, quantity, product_image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $user_id, $product_id, $product_name, $quantity, $product_image);
            $stmt->execute();
            $message[] = 'Sản phẩm đã được thêm vào giỏ hàng!';
        }
    } else {
        $message[] = 'Sản phẩm không tồn tại!';
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

.star.active {
    color: #ffd700;
    /* Màu vàng */
    font-size: 28px;
    /* Kích thước ngôi sao khi được chọn */
}

.color-swatches {
    margin-top: 20px;
}

.color-swatches h4 {
    margin-bottom: 10px;
    font-size: 16px;
    font-weight: bold;
}

.swatch {
    display: inline-block;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin: 5px;
    cursor: pointer;
    border: 1px solid #ccc;
    transition: transform 0.3s;
}

.swatch:hover {
    transform: scale(1.1);
}

.swatch[title]:hover::after {
    content: attr(title);
    position: absolute;
    background: #fff;
    border: 1px solid #ccc;
    padding: 2px 5px;
    border-radius: 3px;
    font-size: 12px;
    color: #000;
    white-space: nowrap;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    display: block;
}



.try-now-btn {
    position: absolute;
    /* Đặt vị trí tuyệt đối */
    bottom: 20px;
    /* Khoảng cách từ đáy của hình ảnh */
    left: 50%;
    width: 15%;
    /* Đặt nút ở giữa */
    transform: translateX(-50%);
    /* Căn chỉnh chính xác nút ở giữa */
    background-color: #000;
    /* Màu nền cho nút */
    color: white;
    /* Màu chữ */
    padding: 10px 20px;
    /* Padding cho nút */
    border: none;
    /* Bỏ viền */
    border-radius: 5px;
    /* Bo góc nút */
    font-size: 16px;
    /* Kích thước chữ */
    cursor: pointer;
    /* Con trỏ chuột khi hover */
    z-index: 10;
    /* Đảm bảo nút nằm trên hình ảnh */
    opacity: 0.8;
    /* Độ trong suốt nhẹ cho nút */
    transition: opacity 0.3s ease;
    bottom: -42rem;
}

.try-now-btn:hover {
    opacity: 1;
    background-color: #666;
    /* Khi hover, nút sẽ trở nên đậm hơn */
}

/* Thay vì sử dụng display: none */
.view-evaluate .box-container .box.hidden {
    visibility: hidden;
    /* Ẩn phần tử nhưng giữ lại không gian */
    display: none;
}
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/main.css?v=1.1<?php echo time(); ?>">

    <title>Seraph Beauty - Chi Tiết Sản Phẩm</title>
</head>

<body>
    <?php include '../guest/header_guest.php'; ?>

    <section class="view_page">
        <?php
        if (isset($message)) {
            foreach ($message as $msg) {
                echo '
                    <div class="message">
                        <span>' . $msg . '</span>
                        <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                    </div>
                ';
            }
        }
        ?>
        <div class="box-container">
            <?php
            if ($product) {
                $image_names = explode(',', $product['product_image']);
            ?>
            <form method="post" class="box">
                <div class="img-container">
                    <div class="small-imgs" id="small-imgs-container">
                        <?php
                            // Hiển thị ảnh đầu tiên như ảnh nhỏ
                            $first_image_url = '../image/product/' . urlencode(trim($image_names[0])); // Mã hóa URL
                            // Truy vấn để lấy số sao đánh giá cho sản phẩm này
                            $select_star = mysqli_query($conn, "SELECT AVG(star) AS avg_star FROM evaluate WHERE product_id = '$product_id'") or die('Truy vấn đánh giá thất bại: ' . mysqli_error($conn));
                            $fetch_star = mysqli_fetch_assoc($select_star);
                            $average_star = round($fetch_star['avg_star'], 1); // Làm tròn số sao đánh giá
                            ?>
                        <img class="small-img" src="<?php echo $first_image_url; ?>" data-index="0" alt="Product Image"
                            onclick="updateMainImage(this)">
                        <?php
                            // Hiển thị các ảnh nhỏ còn lại
                            for ($index = 1; $index < count($image_names); $index++) {
                                $image_url = '../image/product/' . urlencode(trim($image_names[$index])); // Mã hóa URL
                            ?>
                        <img class="small-img" src="<?php echo $image_url; ?>" data-index="<?php echo $index; ?>"
                            alt="Product Image" onclick="updateMainImage(this)">
                        <?php } ?>
                    </div>
                    <img class="main-img" src="<?php echo $first_image_url; ?>" alt="Main Product Image"
                        id="main-image">
                    <div>
                        <!-- <button type="button" class="try-now-btn" onclick="startCamera()">Thử ngay</button> -->
                        <div id="camera-container" class="camera-container" style="display: none;">
                            <video id="camera" autoplay></video>
                            <canvas id="overlay"></canvas>
                            <button type="button" onclick="stopCamera()">X</button>
                        </div>
                    </div>

                </div>
                <script>
                function updateMainImage(imgElement) {
                    // Lấy URL của hình ảnh nhỏ
                    const newImageSrc = imgElement.src;

                    // Cập nhật hình ảnh chính
                    const mainImage = document.getElementById('main-image');
                    mainImage.src = newImageSrc;

                    // Cập nhật hình ảnh nhỏ được chọn (nếu bạn muốn thay đổi kiểu dáng)
                    const smallImages = document.querySelectorAll('.small-img');
                    smallImages.forEach(smallImg => {
                        smallImg.classList.remove('active'); // Xóa lớp active khỏi tất cả hình ảnh nhỏ
                    });

                    imgElement.classList.add('active'); // Thêm lớp active cho hình ảnh nhỏ đã chọn
                }

                // Tự động tô đen hình ảnh nhỏ đầu tiên khi trang được tải
                document.addEventListener('DOMContentLoaded', function() {
                    const firstSmallImg = document.querySelector('.small-img');
                    if (firstSmallImg) {
                        updateMainImage(
                            firstSmallImg); // Gọi hàm để cập nhật hình ảnh chính và tô đen hình ảnh nhỏ
                    }
                });
                </script>


                <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
                <script>
                let videoElement = document.getElementById('camera');
                let canvasElement = document.getElementById('overlay');
                let canvasCtx = canvasElement.getContext('2d');
                let cameraContainer = document.getElementById('camera-container');
                let camera;

                // Lấy `product_id` từ URL
                function getProductIdFromUrl() {
                    const urlParams = new URLSearchParams(window.location.search);
                    return parseInt(urlParams.get('product_id')) || null;
                }

                // Gửi yêu cầu đến API PHP để lấy mã màu từ cơ sở dữ liệu
                async function getColorFromProductId(productId) {
                    if (!productId) {
                        console.warn("No product_id found in URL");
                        return 'rgba(255, 255, 255, 0)'; // Màu mặc định
                    }
                    try {
                        const response = await fetch(`get_color_code.php?product_id=${productId}`);
                        const data = await response.json();
                        if (data.color_code) {
                            return data.color_code; // Trả về mã màu
                        } else {
                            console.error(data.error || "Error: No color code found");
                            return 'rgba(255, 255, 255, 0)'; // Màu mặc định
                        }
                    } catch (error) {
                        console.error('Error fetching color code:', error);
                        return 'rgba(255, 255, 255, 0)'; // Màu mặc định
                    }
                }

                // Bắt đầu camera và xử lý môi
                async function startCamera() {
                    try {
                        cameraContainer.style.display = 'block';
                        canvasElement.style.display = 'none';

                        const constraints = {
                            video: {
                                facingMode: 'user',
                            },
                        };

                        const stream = await navigator.mediaDevices.getUserMedia(constraints);
                        videoElement.srcObject = stream;

                        videoElement.onloadedmetadata = () => {
                            const videoWidth = videoElement.videoWidth;
                            const videoHeight = videoElement.videoHeight;

                            const pixelRatio = window.devicePixelRatio || 1;

                            canvasElement.width = videoWidth * pixelRatio;
                            canvasElement.height = videoHeight * pixelRatio;

                            canvasElement.style.width = `${videoWidth}px`;
                            canvasElement.style.height = `${videoHeight}px`;

                            canvasCtx.scale(pixelRatio, pixelRatio);

                            videoElement.width = videoWidth;
                            videoElement.height = videoHeight;
                            videoElement.style.width = `${videoWidth}px`;
                            videoElement.style.height = `${videoHeight}px`;
                        };

                        const productId = getProductIdFromUrl();
                        const lipColor = await getColorFromProductId(productId);

                        const faceMesh = new FaceMesh({
                            locateFile: (file) =>
                                `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`,
                        });

                        faceMesh.setOptions({
                            maxNumFaces: 1,
                            refineLandmarks: true,
                            minDetectionConfidence: 0.6,
                            minTrackingConfidence: 0.6,
                        });

                        faceMesh.onResults((results) => onResults(results, lipColor));

                        camera = new Camera(videoElement, {
                            onFrame: async () => {
                                await faceMesh.send({
                                    image: videoElement
                                });
                            },
                        });

                        camera.start();
                    } catch (error) {
                        console.error('Error starting camera:', error);
                    }
                }

                // Dừng camera
                function stopCamera() {
                    cameraContainer.style.display = 'none';
                    canvasElement.style.display = 'none';
                    if (camera) {
                        camera.stop();
                    }
                    if (videoElement.srcObject) {
                        const stream = videoElement.srcObject;
                        const tracks = stream.getTracks();
                        tracks.forEach((track) => track.stop());
                        videoElement.srcObject = null;
                    }
                    canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
                }

                function onResults(results, lipColor) {
                    if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
                        const faceLandmarks = results.multiFaceLandmarks[0];

                        // Các chỉ số landmark cho môi
                        const upperLipTopIndices = [191, 80, 81, 82, 13, 312, 311, 310, 415, 308];
                        const upperLipBottomIndices = [76, 185, 40, 39, 37, 0, 267, 269, 270, 409, 291];
                        const lowerLipTopIndices = [61, 76, 178, 14, 402, 324, 318, 317, 402];
                        const lowerLipBottomIndices = [61, 146, 91, 181, 84, 17, 314, 405, 291];

                        const upperLipTop = upperLipTopIndices.map((index) => faceLandmarks[index]);
                        const upperLipBottom = upperLipBottomIndices.map((index) => faceLandmarks[index]);
                        const lowerLipTop = lowerLipTopIndices.map((index) => faceLandmarks[index]);
                        const lowerLipBottom = lowerLipBottomIndices.map((index) => faceLandmarks[index]);

                        if (upperLipTop && upperLipBottom && lowerLipTop && lowerLipBottom) {
                            canvasElement.style.display = 'block'; // Hiển thị canvas khi tìm thấy môi

                            canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
                            canvasCtx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

                            canvasCtx.globalCompositeOperation = 'multiply';
                            canvasCtx.filter = 'brightness(1.2) saturate(1.3)'; // Tăng sáng và độ bão hòa


                            canvasCtx.fillStyle = lipColor;
                            canvasCtx.beginPath();


                            canvasCtx.moveTo(
                                upperLipTop[0].x * canvasElement.width,
                                upperLipTop[0].y * canvasElement.height
                            );
                            upperLipTop.forEach((point) => {
                                canvasCtx.lineTo(point.x * canvasElement.width, point.y * canvasElement.height);
                            });
                            upperLipBottom.reverse().forEach((point) => {
                                canvasCtx.lineTo(point.x * canvasElement.width, point.y * canvasElement.height);
                            });
                            lowerLipTop.forEach((point) => {
                                canvasCtx.lineTo(point.x * canvasElement.width, point.y * canvasElement.height);
                            });
                            lowerLipBottom.reverse().forEach((point) => {
                                canvasCtx.lineTo(point.x * canvasElement.width, point.y * canvasElement.height);
                            });

                            canvasCtx.closePath();

                            canvasCtx.globalAlpha = 0.6; // Độ trong suốt
                            canvasCtx.fill();

                            canvasCtx.globalCompositeOperation = 'source-over';
                            canvasCtx.filter = 'none';
                            canvasCtx.globalAlpha = 1.0;
                        }
                    }
                }
                </script>



                <style>
                .camera-container {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 710px;
                    /* Chiều rộng camera bằng với width của main-img */
                    height: 800px;
                    /* Chiều cao camera bằng với height của main-img */
                    display: none;
                    /* Ẩn camera mặc định */
                    z-index: 5;
                    /* Đảm bảo camera nằm trên ảnh */
                    transform: translateX(150px);
                    /* Di chuyển camera sang phải */
                }

                #camera,
                #overlay {
                    box-sizing: border-box;
                    /* Đảm bảo kích thước không bị ảnh hưởng bởi padding hoặc border */
                }

                #camera {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    /* Đảm bảo video không bị biến dạng */
                    transform: scaleX(-1);
                }

                #overlay {
                    position: absolute;
                    /* Đặt canvas nằm trên video */
                    top: 0;
                    left: 0;
                    width: 100%;
                    /* Đảm bảo canvas chiếm toàn bộ diện tích camera */
                    height: 100%;
                    /* Đảm bảo canvas chiếm toàn bộ diện tích camera */
                    pointer-events: none;
                    /* Để không chặn các sự kiện chuột */
                    transform: scaleX(-1);
                }

                .camera-container button {
                    position: absolute;
                    top: 0;
                    right: 5px;
                    width: 5%;
                    background-color: rgba(0, 0, 0, 0.7);
                    color: #fff;
                    border: none;
                    padding: 5px 10px;
                    cursor: pointer;
                    z-index: 10;
                    /* Đảm bảo nút ở trên camera */
                }

                .camera-container button:hover {
                    background-color: rgba(0, 0, 0, 0.9);
                    /* Tăng độ tối khi hover */
                }
                </style>


                <div class="product-details">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb1">
                            <li class="breadcrumb-item1"><a href="../guest/guest.php">Trang chủ</a></li>
                            <?php
                                // Kiểm tra xem tham số 'product_id' có trong URL không
                                if (isset($_GET['product_id'])) {
                                    $product_id = intval($_GET['product_id']); // Lấy và chuyển đổi 'product_id' thành số nguyên

                                    // Thực hiện truy vấn để lấy product_name từ bảng products
                                    $stmt = $conn->prepare("SELECT product_name FROM products WHERE product_id = ?");
                                    $stmt->bind_param("i", $product_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $product_name = htmlspecialchars($row['product_name']); // Làm sạch dữ liệu

                                        // Hiển thị product_name trong breadcrumb
                                        echo '<li class="breadcrumb-item1 active" aria-current="page">' . $product_name . '</li>';
                                    } else {
                                        echo '<li class="breadcrumb-item1 active" aria-current="page">Sản phẩm không tồn tại</li>';
                                    }
                                }
                                ?>
                        </ol>
                    </nav>

                    <div class="name">
                        <span class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></span>

                        <?php
                            // Kiểm tra xem product_id có trong session wishlist không
                            $isInWishlist = false;
                            if (isset($_SESSION['wishlist'])) {
                                foreach ($_SESSION['wishlist'] as $item) {
                                    if ($item['product_id'] == $product_id) {
                                        $isInWishlist = true;
                                        break; // Thoát khỏi vòng lặp nếu đã tìm thấy
                                    }
                                }
                            }
                            ?>

                        <!-- <i class="fas fa-heart" style="color: <?php echo $isInWishlist ? 'black' : '#d3d3d3'; ?>;"
                            data-product-id="<?php echo $product_id; ?>" onclick="addToWishlist(this)"></i> -->
                    </div>

                    <script>
                    function addToWishlist(element) {
                        const productId = element.getAttribute('data-product-id'); // Lấy product_id từ data attribute
                        const isInWishlist = element.style.color === 'black'; // Kiểm tra nếu trái tim là màu đen

                        // Gửi yêu cầu AJAX để thêm hoặc xóa sản phẩm khỏi wishlist
                        fetch('../guest/add_wishlist.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    product_id: productId,
                                    action: isInWishlist ? 'remove' : 'add' // Gửi action tương ứng
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Thay đổi màu sắc của trái tim dựa trên trạng thái
                                    element.style.color = isInWishlist ? '#d3d3d3' :
                                        'black'; // Đổi màu khi thêm hoặc xóa

                                    // Thêm lớp active để thu nhỏ trái tim
                                    element.classList.add('active'); // Thay đổi trạng thái active

                                    // Sau một khoảng thời gian, xóa lớp active để phóng to lại
                                    setTimeout(() => {
                                        element.classList.remove('active'); // Xóa trạng thái active
                                    }, 200); // Thời gian tương ứng với thời gian transition
                                } else {
                                    alert('Có lỗi xảy ra.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                    }
                    </script>
                    <style>
                    .heart-icon {
                        transition: transform 1s ease;
                        /* Thêm hiệu ứng chuyển động */
                    }

                    .heart-icon.active {
                        transform: scale(0.5);
                        /* Thu nhỏ trái tim khi ở trạng thái active */
                    }
                    </style>


                    <?php
                        // Lấy product_id từ URL
                        $product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;

                        if ($product_id) {
                            // Truy vấn để lấy thông tin từ bảng products và product_promotion với điều kiện ngày
                            $sql = "SELECT p.product_price, p.product_subcategory, pp.discount_percent, pp.start_date, pp.end_date 
                FROM products p
                LEFT JOIN product_promotion pp 
                ON p.product_subcategory = pp.subcategory_name
                WHERE p.product_id = ?
                AND (pp.start_date IS NULL OR pp.start_date <= NOW()) 
                AND (pp.end_date IS NULL OR pp.end_date >= NOW())";

                            // Chuẩn bị truy vấn
                            if ($stmt = $conn->prepare($sql)) {
                                $stmt->bind_param("i", $product_id);  // Liên kết product_id vào truy vấn
                                $stmt->execute();
                                $result = $stmt->get_result();

                                // Kiểm tra xem có kết quả hay không
                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $product_price = $row['product_price'];
                                    $discount_percent = isset($row['discount_percent']) ? $row['discount_percent'] : null;

                                    // Nếu có discount_percent và trong khoảng ngày hợp lệ, tính giá khuyến mãi
                                    if ($discount_percent) {
                                        $discount_price = $product_price - ($product_price * ($discount_percent / 100));
                                    } else {
                                        $discount_price = $product_price;  // Không có khuyến mãi, giữ nguyên giá gốc
                                    }

                                    // Hiển thị giá trên trang
                                    echo '<div class="price_viewpage">';

                                    // Kiểm tra nếu có discount_percent thì hiển thị giá gốc
                                    if ($discount_percent) {
                                        // Hiển thị giá gốc và gạch ngang để cho biết giá này đã giảm
                                        echo '<span class="original-price" style="text-decoration: line-through; color: grey;">';
                                        echo number_format($product_price, 0, '.', '.') . ' VNĐ';
                                        echo '</span> ';
                                    }

                                    // Hiển thị giá đã giảm (nếu có) hoặc giá gốc nếu không có giảm giá
                                    echo '<style>
                .discount-price {
                    color: #bd0100 !important;
                }
                </style>';

                                    echo '<span class="discount-price">';
                                    echo number_format($discount_price, 0, '.', '.') . ' VNĐ';
                                    echo '</span>';

                                    echo '</div>';
                                } else {
                                    // Nếu không có kết quả nào hoặc không có khuyến mãi hợp lệ, hiển thị giá gốc
                                    $sql_price = "SELECT product_price FROM products WHERE product_id = ?";
                                    if ($stmt_price = $conn->prepare($sql_price)) {
                                        $stmt_price->bind_param("i", $product_id);
                                        $stmt_price->execute();
                                        $result_price = $stmt_price->get_result();
                                        if ($result_price->num_rows > 0) {
                                            $row_price = $result_price->fetch_assoc();
                                            $product_price = $row_price['product_price'];

                                            // Hiển thị giá gốc
                                            echo '<div class="price_viewpage">';
                                            echo '<span class="discount-price">';
                                            echo number_format($product_price, 0, '.', '.') . ' VNĐ';
                                            echo '</span>';
                                            echo '</div>';
                                        } else {
                                            echo "Không tìm thấy sản phẩm.";
                                        }
                                    }
                                }
                            }
                        } else {
                            echo "Không tìm thấy product_id.";
                        }
                        ?>


                    <div class="detail_viewpage"><?php echo $product['product_detail']; ?></div>
                    <div class="star">
                        <?php
                            // Hiển thị số sao đánh giá
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $average_star) {
                                    echo '<span class="star-filled">★</span>'; // Sao đầy
                                } else {
                                    echo '<span class="star-empty">☆</span>'; // Sao rỗng
                                }
                            }

                            // Truy vấn số lượt đánh giá
                            $query = "SELECT COUNT(*) as total_reviews FROM evaluate WHERE product_id = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            $total_reviews = $row['total_reviews'];
                            ?>
                        <div class="reviews"><?php echo $total_reviews; ?> lượt đánh giá</div>
                    </div>

                    <div class="tag-container">
                        <?php
                            $product_id_from_url = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
                            // Bắt đầu truy vấn để lấy tag_id từ product_tags
                            $tag_query = "SELECT tag_id FROM product_tags WHERE product_id = ?";
                            $tag_stmt = $conn->prepare($tag_query);
                            $tag_stmt->bind_param("i", $product_id_from_url);
                            $tag_stmt->execute();
                            $tag_result = $tag_stmt->get_result();

                            // Khởi tạo mảng để lưu tag đã hiển thị
                            $displayed_tags = [];

                            // Nếu tìm thấy tag_id, lấy tag_name và tag_image từ bảng tags
                            if ($tag_result->num_rows > 0) {
                                while ($tag = $tag_result->fetch_assoc()) {
                                    $tag_id = $tag['tag_id'];

                                    // Kiểm tra xem tag_id đã được hiển thị chưa
                                    if (!in_array($tag_id, $displayed_tags)) {
                                        $tags_query = "SELECT tag_name, tag_image FROM tags WHERE tag_id = ?";
                                        $tags_stmt = $conn->prepare($tags_query);
                                        $tags_stmt->bind_param("i", $tag_id);
                                        $tags_stmt->execute();
                                        $tags_result = $tags_stmt->get_result();

                                        if ($tags_result->num_rows > 0) {
                                            $tags_row = $tags_result->fetch_assoc();
                                            $tag_name = htmlspecialchars($tags_row['tag_name']);
                                            $tag_image = !empty($tags_row['tag_image']) ? '../image/tags/' . rawurlencode(trim($tags_row['tag_image'])) : null;

                                            // Hiển thị tag_name và tag_image
                                            echo '<div class="tag">';
                                            if ($tag_image) {
                                                echo '<img src="' . $tag_image . '" alt="' . $tag_name . '" style="width: 20px; height: 20px;"/> ';
                                            }
                                            echo '<span>' . $tag_name . '</span>';
                                            echo '</div>';

                                            // Thêm tag_id vào mảng đã hiển thị
                                            $displayed_tags[] = $tag_id;
                                        }
                                    }
                                }
                            } else {
                            }
                            ?>
                    </div>

                    <div class="color-swatches">
                        <?php
                            if ($product_id_from_url > 0) {
                                // Lấy tên sản phẩm hiện tại
                                $name_query = "SELECT product_name FROM products WHERE product_id = ?";
                                $stmt = $conn->prepare($name_query);
                                $stmt->bind_param("i", $product_id_from_url);
                                $stmt->execute();
                                $name_result = $stmt->get_result();

                                if ($name_result->num_rows > 0) {
                                    $current_product = $name_result->fetch_assoc();
                                    $product_name = $current_product['product_name'];

                                    // Lấy tất cả các màu của sản phẩm có cùng product_name, loại bỏ trùng lặp
                                    $color_query = "SELECT DISTINCT color_name, color_image, product_id 
                            FROM products WHERE product_name = ?";
                                    $stmt = $conn->prepare($color_query);
                                    $stmt->bind_param("s", $product_name);
                                    $stmt->execute();
                                    $color_result = $stmt->get_result();

                                    if ($color_result->num_rows > 0) {
                                        $unique_colors = []; // Mảng lưu các màu đã được thêm vào

                                        // Hiển thị các màu dưới dạng swatch
                                        while ($color = $color_result->fetch_assoc()) {
                                            $color_name = $color['color_name'];

                                            // Kiểm tra nếu màu đã được hiển thị, bỏ qua nếu trùng
                                            if (in_array($color_name, $unique_colors)) {
                                                continue;
                                            }
                                            $unique_colors[] = $color_name; // Thêm vào danh sách đã hiển thị

                                            $color_image = !empty($color['color_image'])
                                                ? '../image/colorimage/' . rawurlencode(trim($color['color_image']))
                                                : null;
                                            $color_product_id = $color['product_id'];
                                            $border_style = ($color_product_id == $product_id_from_url) ? 'border: 1px solid #000;' : '';
                            ?>
                        <div class="swatch"
                            style="background-color: <?php echo htmlspecialchars($color_name); ?>; width: 45px; height: 45px; display: inline-block; position: relative; border-radius: 0;"
                            title="<?php echo htmlspecialchars($color_name); ?>">
                            <div class="image-container"
                                style="width: 100%; height: 100%; overflow: hidden; border-radius: 0;">
                                <?php if ($color_image): ?>
                                <img src="<?php echo $color_image; ?>"
                                    alt="<?php echo htmlspecialchars($color_name); ?>" class="color-image"
                                    style="width: 100%; height: 100%; object-fit: cover; <?php echo $border_style; ?>"
                                    onclick="getProductInfo('<?php echo addslashes($product_name); ?>', '<?php echo addslashes($color_name); ?>', <?php echo $color_product_id; ?>)">
                                <?php else: ?>
                                <span
                                    style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; font-size: 12px; color: #000; <?php echo $border_style; ?>"
                                    onclick="getProductInfo('<?php echo addslashes($product_name); ?>', '<?php echo addslashes($color_name); ?>', <?php echo $color_product_id; ?>)">
                                    <?php echo htmlspecialchars($color_name); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                                        } // Kết thúc vòng lặp hiển thị swatch

                                        // Tạo dropdown cho các màu
                                        echo '<div style="margin-top: 20px; position: relative;">';
                                        $current_color_query = "SELECT product_name, color_name, color_image, detail_color 
                        FROM products WHERE product_id = ?";
                                        $stmt = $conn->prepare($current_color_query);
                                        $stmt->bind_param("i", $product_id_from_url);
                                        $stmt->execute();
                                        $current_color_result = $stmt->get_result();

                                        if ($current_color_result->num_rows > 0) {
                                            $current_color = $current_color_result->fetch_assoc();
                                            $current_color_name = $current_color['color_name'];
                                            $current_detail_color = $current_color['detail_color'];
                                            $current_color_image = !empty($current_color['color_image'])
                                                ? '../image/colorimage/' . rawurlencode(trim($current_color['color_image']))
                                                : '';

                                            echo '<div id="dropdown" class="dropdown" onclick="toggleDropdown()" style="cursor: pointer; padding: 10px; border: 1px solid #000; border-radius: 4px; display: flex; align-items: center; width: 95%;">';

                                            if (!empty($current_color_image)) {
                                                echo '<img style="width: 30px; height: 30px; margin-right: 0.8rem;" 
                src="' . htmlspecialchars($current_color_image) . '" 
                alt="' . htmlspecialchars($current_color_name) . '"/>';
                                            }

                                            echo '<span style="font-weight: bold;">' . htmlspecialchars($current_color_name) . '</span>';
                                            echo '<span style="font-weight: normal; margin-left: 5px; color: #666;">' . htmlspecialchars($current_detail_color) . '</span>';
                                            echo '</div>';

                                            // Lấy tất cả các màu từ bảng products
                                            $all_colors_query = "SELECT color_name, color_image, detail_color, product_id 
                         FROM products 
                         WHERE product_name = ? 
                         ORDER BY color_name"; // Sắp xếp theo tên màu
                                            $stmt = $conn->prepare($all_colors_query);
                                            $stmt->bind_param("s", $product_name);
                                            $stmt->execute();
                                            $all_colors_result = $stmt->get_result();

                                            echo '<div id="color-options" class="dropdown-content" style="display: none; max-height: 250px; overflow-y: auto; width: 95%; border: 1px solid #ccc;">';

                                            // Mảng để theo dõi các màu đã hiển thị
                                            $displayed_colors = [];

                                            while ($color = $all_colors_result->fetch_assoc()) {
                                                $color_name = $color['color_name'];
                                                $color_image = !empty($color['color_image'])
                                                    ? '../image/colorimage/' . rawurlencode(trim($color['color_image']))
                                                    : '';
                                                $detail_color = $color['detail_color'];

                                                // Tạo một khóa duy nhất cho màu sắc
                                                $color_key = $color_image . '|' . $color_name . '|' . $detail_color;

                                                // Kiểm tra xem màu đã được hiển thị chưa
                                                if (!in_array($color_key, $displayed_colors)) {
                                                    // Thêm màu vào danh sách đã hiển thị
                                                    $displayed_colors[] = $color_key;

                                                    echo '<div class="color-option" data-product-id="' . htmlspecialchars($color['product_id']) . '" 
                  onclick="getProductInfo(\'' . addslashes($product_name) . '\', \'' . addslashes($color_name) . '\', ' . $color['product_id'] . ')">';

                                                    if (!empty($color_image)) {
                                                        echo '<img style="width: 30px; height: 30px; margin-right: 0.8rem;" 
                      src="' . htmlspecialchars($color_image) . '" 
                      alt="' . htmlspecialchars($color_name) . '"/>';
                                                    }

                                                    echo '<span style="font-weight: bold;">' . htmlspecialchars($color_name) . '</span>';
                                                    echo '<span style="font-weight: normal; margin-left: 5px; color: #666;">' . htmlspecialchars($detail_color) . '</span>';
                                                    echo '</div>';
                                                }
                                            }

                                            echo '</div>'; // Kết thúc dropdown-content
                                            echo '</div>'; // Kết thúc dropdown
                                        } else {
                                            echo '<p class="empty">Không có màu sắc nào.</p>';
                                        }
                                    }
                                } else {
                                    echo '<p class="empty">Không tìm thấy sản phẩm.</p>';
                                }
                            } else {
                                echo '<p class="empty">ID sản phẩm không hợp lệ.</p>';
                            }
                            ?>
                    </div>


                    <style>
                    .color-option {
                        display: flex;
                        align-items: center;
                        cursor: pointer;
                        padding: 5px;
                        border: 1px solid #ccc;
                        border-radius: 4px;
                        margin-top: 5px;
                        width: 100%;
                    }

                    .color-option:hover {
                        background-color: #f0f0f0;
                        /* Hiệu ứng hover */
                    }

                    .dropdown-content {
                        position: absolute;
                        background-color: #f9f9f9;
                        min-width: 160px;
                        z-index: 2;
                        /* Đảm bảo dropdown-content sẽ nằm trên capacity-container */
                        border: 1px solid #ccc;
                    }

                    .capacity-container {
                        z-index: 1;
                        /* Giữ z-index nhỏ hơn để không nằm trên dropdown-content */
                    }
                    </style>

                    <script>
                    // Hàm để bật/tắt dropdown
                    function toggleDropdown() {
                        const dropdownContent = document.getElementById('color-options');
                        dropdownContent.style.display = (dropdownContent.style.display === 'none' || dropdownContent
                            .style.display === '') ? 'block' : 'none';
                    }

                    // Hàm xử lý sự kiện khi người dùng chọn màu mới từ danh sách
                    function getProductInfo(productName, colorName, productId) {
                        // Chuyển hướng đến trang sản phẩm tương ứng
                        window.location.href = `?product_id=${productId}`;
                    }

                    // Đóng dropdown nếu người dùng nhấp ra ngoài
                    window.onclick = function(event) {
                        if (!event.target.matches('.dropdown')) {
                            const dropdownContent = document.getElementById('color-options');
                            if (dropdownContent.style.display === 'block') {
                                dropdownContent.style.display = 'none';
                            }
                        }
                    }
                    </script>









                    <script>
                    function getProductInfo(productName, colorName, productId) {
                        // Gửi yêu cầu đến máy chủ để kiểm tra sản phẩm
                        var xhr = new XMLHttpRequest();
                        xhr.open("POST", "get_color_info.php", true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4 && xhr.status === 200) {
                                if (xhr.responseText === "found") {
                                    // Nếu tìm thấy, chuyển hướng đến trang sản phẩm
                                    window.location.href = "view_page_guest.php?product_id=" + productId;
                                } else {
                                    alert("Product not found.");
                                }
                            }
                        };

                        // Mã hóa ký tự đặc biệt
                        xhr.send("product_name=" + encodeURIComponent(productName) + "&color_name=" +
                            encodeURIComponent(colorName));
                    }
                    </script>


                    <div class="capacity-container">
                        <?php
                            if (isset($_GET['product_id'])) {
                                $product_id = $_GET['product_id'];

                                // Truy vấn để lấy product_name, color_name và capacity của sản phẩm từ URL
                                $sql = "SELECT product_name, color_name, capacity FROM products WHERE product_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $product_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $product_name_from_url = $row['product_name'];
                                    $color_name_from_url = $row['color_name'];
                                    $current_capacity = $row['capacity'];

                                    // Kiểm tra nếu capacity của sản phẩm hiện tại rỗng
                                    if (!is_null($current_capacity)) {
                                        // Truy vấn tất cả sản phẩm có cùng product_name và color_name
                                        $sql_all = "SELECT capacity, product_id 
            FROM products 
            WHERE product_name = ? 
              AND color_name = ? 
              AND (capacity IS NOT NULL AND capacity != '')";

                                        $stmt_all = $conn->prepare($sql_all);
                                        $stmt_all->bind_param("ss", $product_name_from_url, $color_name_from_url);
                                        $stmt_all->execute();
                                        $result_all = $stmt_all->get_result();

                                        if ($result_all->num_rows > 0) {
                                            $capacities = [];

                                            // Lấy tất cả dung tích và product_id cho các sản phẩm tương ứng
                                            while ($row_all = $result_all->fetch_assoc()) {
                                                $capacities[] = [
                                                    'capacity' => $row_all['capacity'],
                                                    'product_id' => $row_all['product_id']
                                                ];
                                            }

                                            // Đảm bảo có dung tích phù hợp
                                            if (!empty($capacities)) {
                                                $max_capacity = max(array_column($capacities, 'capacity'));
                            ?>

                        <?php foreach ($capacities as $item) {
                                                    $capacity_label = ($item['capacity'] == $max_capacity) ? 'Mini' : 'Tiêu chuẩn';
                                                    $isSelected = ($item['capacity'] == $current_capacity) ? 'selected' : ''; // Kiểm tra nếu dung tích hiện tại khớp
                                                ?>
                        <div class="capacity-box <?php echo $isSelected; ?>"
                            data-product-name="<?php echo addslashes($product_name_from_url); ?>"
                            data-color-name="<?php echo addslashes($color_name_from_url); ?>"
                            data-capacity="<?php echo $item['capacity']; ?>"
                            data-product-id="<?php echo $item['product_id']; ?>">
                            <span class="capacity-label"><?php echo $capacity_label; ?></span>
                            <span><?php echo htmlspecialchars($item['capacity']); ?></span>
                        </div>

                        <?php } ?>

                        <?php
                                            } else {
                                                echo "Không có sản phẩm nào phù hợp.";
                                            }
                                        } else {
                                        }
                                    } else {
                                        echo "Sản phẩm hiện tại không có dung tích.";
                                    }
                                } else {
                                    echo "Không tìm thấy sản phẩm.";
                                }
                            } else {
                                echo "Không có product_id trong URL.";
                            }
                            ?>

                    </div>

                    <script>
                    function getCapacityInfo(productName, colorName, capacity, productId) {
                        console.log("Clicked:", productName, colorName, capacity, productId);
                        const newUrl = window.location.protocol + "//" + window.location.host + window.location
                            .pathname + '?product_id=' + productId;

                        // Đổi URL mà không reload trang
                        window.history.pushState({
                            path: newUrl
                        }, '', newUrl);

                        // Reload lại trang để áp dụng product_id mới
                        window.location.reload();
                    }

                    document.querySelectorAll('.capacity-box').forEach(box => {
                        box.addEventListener('click', function() {
                            // Loại bỏ class 'selected' khỏi tất cả các box
                            document.querySelectorAll('.capacity-box').forEach(box => box.classList
                                .remove('selected'));

                            // Thêm class 'selected' vào box hiện tại
                            box.classList.add('selected');

                            const productName = box.getAttribute('data-product-name');
                            const colorName = box.getAttribute('data-color-name');
                            const capacity = box.getAttribute('data-capacity');
                            const productId = box.getAttribute('data-product-id');

                            getCapacityInfo(productName, colorName, capacity, productId);
                        });
                    });
                    </script>






                    <div class="product-hero__add-to-cart">
                        <div class="quantity-selector">
                            <button type="button" id="decrement-btn"
                                class="quantity-selector__action quantity-selector__action--decrement">−</button>
                            <p id="quantity-display" class="quantity-selector__field p1 bold">1</p>
                            <button type="button" id="increment-btn"
                                class="quantity-selector__action quantity-selector__action--increment">+</button>
                        </div>

                        <!-- Thêm input hidden để lưu số lượng -->
                        <input type="hidden" name="quantity" id="quantity-field" value="1">

                        <!-- Button để thêm sản phẩm vào giỏ hàng -->
                        <input type="hidden" name="product_id" id="product_id" value="<?php echo $product_id; ?>">
                        <button type="submit" id="add-to-cart-btn"
                            class="btn btn--full uppercase product-hero__add-cta btn--primary">Thêm vào giỏ
                            hàng</button>
                    </div>
                    <div class="detail_product">

                    </div>
                </div>
            </form>
            <?php
            } else {
                echo '<div class="message">Sản phẩm không tồn tại!</div>';
            }
            ?>
        </div>

        <section>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>

            <div class="view-evaluate">
                <!-- Filter Section -->
                <div class="filter-section">
                    <h2>Lọc đánh giá</h2>
                    <div class="filter-container">
                        <div class="filter-group">
                            <label for="rating-filter">Đánh giá</label>
                            <select id="rating-filter">
                                <option value="">Xếp hạng</option>
                                <option value="5">5 sao</option>
                                <option value="4">4 sao</option>
                                <option value="3">3 sao</option>
                                <option value="2">2 sao</option>
                                <option value="1">1 sao</option>
                            </select>
                        </div>
                        <!-- Button "Đánh giá có kèm hình ảnh" -->
                        <button id="image-review-btn" class="image-review-btn">
                            Đánh giá có kèm hình ảnh <span class="circle"></span>
                        </button>

                    </div>
                </div>



                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let imageReviewBtn = document.getElementById(
                        'image-review-btn'); // Nút "Đánh giá có kèm hình ảnh"
                    const ratingFilter = document.getElementById('rating-filter'); // Dropdown chọn xếp hạng
                    const reviewBoxes = document.querySelectorAll('.box'); // Lấy tất cả các đánh giá
                    const noReviewsMessage = document.getElementById(
                        'no-reviews-message'); // Thông báo không có đánh giá

                    // Sự kiện khi nhấn vào nút "Đánh giá có kèm hình ảnh"
                    imageReviewBtn.addEventListener('click', function() {
                        // Toggle trạng thái lọc hình ảnh và thêm dấu tick vào vòng tròn
                        imageReviewBtn.classList.toggle('active');
                        filterReviews(); // Lọc lại các đánh giá sau khi nhấn nút
                    });

                    // Lọc đánh giá khi thay đổi giá trị trong dropdown xếp hạng
                    ratingFilter.addEventListener('change', function() {
                        filterReviews();
                    });

                    // Hàm lọc đánh giá
                    function filterReviews() {
                        let selectedRating = ratingFilter.value; // Lấy giá trị xếp hạng được chọn
                        let hasMatchingReviews = false; // Biến kiểm tra có đánh giá phù hợp không
                        let filterByImage = imageReviewBtn.classList.contains(
                            'active'); // Kiểm tra trạng thái của nút lọc hình ảnh

                        // Duyệt qua tất cả các đánh giá
                        reviewBoxes.forEach(function(box) {
                            let starValue = box.getAttribute(
                                'data-star'); // Lấy giá trị sao từ data-star
                            let hasImage = box.querySelector(
                                '.evaluate-image'); // Kiểm tra có hình ảnh không

                            // Kiểm tra các điều kiện lọc
                            let shouldShow = true;
                            if (selectedRating && selectedRating !== starValue) {
                                shouldShow = false; // Nếu không khớp với xếp hạng, ẩn đánh giá
                            }

                            if (filterByImage && !hasImage) {
                                shouldShow =
                                    false; // Nếu đã chọn lọc hình ảnh, chỉ hiển thị đánh giá có hình ảnh
                            }

                            // Hiển thị hoặc ẩn đánh giá
                            if (shouldShow) {
                                box.classList.remove('hidden');
                                hasMatchingReviews = true; // Có ít nhất một đánh giá khớp
                            } else {
                                box.classList.add('hidden');
                            }
                        });

                        // Hiển thị thông báo nếu không có đánh giá phù hợp
                        if (hasMatchingReviews) {
                            noReviewsMessage.style.display = 'none'; // Ẩn thông báo không có đánh giá
                        } else {
                            noReviewsMessage.style.display = 'block'; // Hiển thị thông báo không có đánh giá
                        }
                    }

                    // Gọi filterReviews để lọc ngay khi trang tải xong
                    filterReviews();
                });
                </script>

                <div class="box-container">
                    <p class="empty" id="no-reviews-message" style="display:none; margin-top: 5rem;">Không có đánh giá
                        với xếp
                        hạng này.
                    </p>
                    <?php
                    if ($product) {
                        // Sử dụng JOIN để lấy user_name và evaluate_image từ bảng users và evaluate
                        $select_evaluates = mysqli_query($conn, "
            SELECT evaluate.*, users.user_name 
            FROM evaluate 
            JOIN users ON evaluate.user_id = users.user_id 
            WHERE evaluate.product_id = '$product_id'
        ") or die('Query failed: ' . mysqli_error($conn));

                        if (mysqli_num_rows($select_evaluates) > 0) {
                            while ($fetch_evaluate = mysqli_fetch_assoc($select_evaluates)) {
                    ?>
                    <div class="box" data-star="<?php echo $fetch_evaluate['star']; ?>">
                        <div class="user"><?php echo $fetch_evaluate['user_name']; ?></div>
                        <div class="star">
                            <?php
                                        $yellow_stars = $fetch_evaluate['star'];
                                        $white_stars = 5 - $yellow_stars;
                                        for ($i = 0; $i < $yellow_stars; $i++) {
                                            echo '<i class="bi bi-star-fill"></i>';
                                        }
                                        for ($i = 0; $i < $white_stars; $i++) {
                                            echo '<i class="bi bi-star"></i>';
                                        }
                                        ?>
                        </div>
                        <?php if (!empty($fetch_evaluate['evaluate_image'])) { ?>
                        <div class="evaluate-image">
                            <img src="../image/evaluate/<?php echo $fetch_evaluate['evaluate_image']; ?>"
                                alt="Evaluate Image" class="evaluate-thumbnail">
                        </div>
                        <?php } ?>
                        <div class="comment"><?php echo $fetch_evaluate['evaluate_detail']; ?></div>
                        <div class="date"><?php echo date("d/m/Y H:i", strtotime($fetch_evaluate['date'])); ?></div>
                    </div>

                    <?php
                            }
                        } else {
                            echo '<p class="empty">Sản phẩm này chưa có đánh gía nào.</p>';
                        }
                    }
                    ?>
                </div>



            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Chọn tất cả các hình ảnh đánh giá
                let evaluateImages = document.querySelectorAll('.evaluate-thumbnail');
                let modal = document.createElement('div');
                modal.classList.add('modal');
                let modalImage = document.createElement('img');
                modal.appendChild(modalImage);
                document.body.appendChild(modal);

                // Sự kiện khi nhấn vào ảnh đánh giá
                evaluateImages.forEach(function(img) {
                    img.addEventListener('click', function() {
                        modal.style.display = 'flex'; // Hiển thị modal
                        modalImage.src = img.src; // Đặt nguồn ảnh cho modal
                    });
                });

                // Sự kiện đóng modal khi nhấn vào modal
                modal.addEventListener('click', function() {
                    modal.style.display = 'none'; // Ẩn modal
                });
            });
            document.addEventListener('DOMContentLoaded', function() {
                let incrementButton = document.querySelector('.quantity-selector__action--increment');
                let decrementButton = document.querySelector('.quantity-selector__action--decrement');
                let quantityField = document.querySelector('.quantity-selector__field');
                let hiddenQuantityField = document.getElementById('quantity-field');
                let addToCartButton = document.getElementById('add-to-cart-btn'); // Nút thêm vào giỏ hàng
                let productIdField = document.getElementById('product_id'); // Lấy giá trị product_id

                // Xử lý sự kiện tăng số lượng
                incrementButton.addEventListener('click', function() {
                    let quantity = parseInt(quantityField.textContent);
                    quantityField.textContent = quantity + 1;
                    hiddenQuantityField.value = quantity + 1; // Cập nhật giá trị input ẩn
                    decrementButton.disabled = false;
                });

                // Xử lý sự kiện giảm số lượng
                decrementButton.addEventListener('click', function() {
                    let quantity = parseInt(quantityField.textContent);
                    if (quantity > 1) {
                        quantityField.textContent = quantity - 1;
                        hiddenQuantityField.value = quantity - 1; // Cập nhật giá trị input ẩn
                    }
                    if (parseInt(quantityField.textContent) === 1) {
                        decrementButton.disabled = true;
                    }
                });

                if (parseInt(quantityField.textContent) === 1) {
                    decrementButton.disabled = true;
                }

                // Gửi yêu cầu AJAX khi nhấn nút "Add to Cart"
                addToCartButton.addEventListener('click', function(e) {
                    e.preventDefault(); // Ngăn không cho trang reload

                    let product_id = productIdField.value;
                    let quantity = hiddenQuantityField.value;

                    // Gửi yêu cầu AJAX
                    $.ajax({
                        url: 'add_to_cart_vp.php',
                        method: 'GET',
                        data: {
                            add: product_id,
                            quantity: quantity
                        },
                        success: function(response) {
                            let data = JSON.parse(response);
                            if (data.status === 'success') {
                                // Thay vì hiển thị thông báo, trang sẽ reload lại
                                window.location.reload();
                            }
                        },
                        error: function() {
                            alert('Có lỗi xảy ra, vui lòng thử lại.');
                        }
                    });
                });
            });
            </script>


            <?php include '../guest/footer.php' ?>
</body>

</html>