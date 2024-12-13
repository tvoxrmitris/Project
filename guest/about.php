<?php
include '../connection/connection.php';
session_start();
// Kiểm tra xem người dùng có đăng nhập hay không
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = session_id(); // Nếu chưa đăng nhập, sử dụng session_id() làm định danh tạm thời
}
// Truy vấn tất cả sản phẩm trong bảng products
$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);

// Kiểm tra xem có sản phẩm nào không
if (mysqli_num_rows($result) > 0) {
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $products = [];
}
?>

<style type="text/css">
<?php include 'main.css'
?>
</style>


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
    <link class="logoo" rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
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
    <!-- <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home</title>
</head>

<body>
    <!-- <div class="line3"></div> -->
    <?php include '../guest/header_guest.php' ?>

    <div class="line"></div>
    <div class="line"></div>
    <style>
    /* Bao bọc cả hai thành phần */
    .main-container {
        display: flex;
        width: 100%;
        height: 100%;
    }

    /* Products container chiếm 70% */
    .products-container {
        flex: 3;
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        background-color: #f9f9f9;
        border-right: 1px solid #ddd;
    }

    /* Camera container chiếm 30% */
    .camera {
        flex: 7;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-left: 1px solid #ddd;
    }

    /* Căn chỉnh video và canvas */
    .camera-container {
        position: relative;
        width: 100%;
        height: auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #camera,
    #overlay {
        position: absolute;
        width: 1060px;
        height: 900px;
        transform: scaleX(-1);
    }
    </style>
    <div class="main-container">
        <div class="camera">
            <button type="button" class="try-now-btn" onclick="startCamera()">Thử ngay</button>
            <div id="camera-container" class="camera-container" style="display: none;">
                <video id="camera" autoplay></video>
                <canvas id="overlay"></canvas>
                <button type="button" onclick="stopCamera()">X</button>
            </div>
        </div>

        <div class="products-container">
            <?php 
    if (!empty($products)): 
        // Nhóm sản phẩm theo product_subcategory
        $groupedProducts = [];
        foreach ($products as $product) {
            $groupedProducts[$product['product_subcategory']][] = $product;
        }

        // Hiển thị sản phẩm theo từng danh mục
        foreach ($groupedProducts as $subcategory => $items): 
            $displayedNames = []; // Đảm bảo không trùng lặp product_name
            ?>
            <div class="subcategory-group">
                <h2 class="subcategory-title"><?php echo htmlspecialchars($subcategory); ?></h2>
                <div class="product-items">
                    <?php foreach ($items as $product): 
                        if (!in_array($product['product_name'], $displayedNames)): 
                            // Thêm vào mảng theo dõi để tránh trùng lặp
                            $displayedNames[] = $product['product_name'];

                            // Truy vấn lấy color_image tương ứng
                            $colorImages = [];
                            foreach ($products as $p) {
                                if ($p['product_name'] === $product['product_name'] && !empty($p['color_image'])) {
                                    $colorImages[] = $p['color_image'];
                                }
                            }
                            ?>
                    <div class="product-item">
                        <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <div class="color-images">
                            <?php foreach ($colorImages as $colorImage): ?>
                            <img src="../image/colorimage/<?php echo urlencode($colorImage); ?>" alt="Color Image"
                                class="color-image" data-color-image="<?php echo htmlspecialchars($colorImage); ?>"
                                data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>">

                            <?php endforeach; ?>
                        </div>

                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>No products found.</p>
            <?php endif; ?>
        </div>



    </div>


    <!-- Thêm CSS cho các sản phẩm -->
    <style>
    .products-container {
        display: flex;
        flex-direction: column;
        gap: 30px;
        padding: 20px;
        background-color: #f9f9f9;
    }

    .subcategory-group {
        margin-bottom: 20px;
        padding: 20px;
        background: linear-gradient(90deg, #fff, #000);
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .subcategory-title {
        font-size: 1.8em;
        color: #000;
        text-transform: uppercase;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #000;
        letter-spacing: 2px;
    }

    .product-item {
        padding: 20px;
        text-align: center;
        border-radius: 8px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-item:hover {
        transform: scale(1.05);

    }

    .product-item h3 {
        display: block;
        width: 350px;
        font-size: 16px;
        text-align: left;
        overflow-wrap: break-word;
        font-weight: bold;
        color: #000;
    }

    .color-images {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        /* Mỗi hàng 6 cột */
        gap: 10px;
        /* Khoảng cách giữa các hình ảnh */
        margin-top: 10px;
        width: 200%;
    }

    .color-images .color-image {
        width: 50px;
        height: 50px;
        border: 2px solid #000;
        object-fit: cover;

        transition: transform 0.3s ease;
    }

    .color-images .color-image:hover {
        transform: scale(1.2);
        border-color: #333;
    }
    </style>



    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
    <script>
    let videoElement = document.getElementById('camera');
    let canvasElement = document.getElementById('overlay');
    let canvasCtx = canvasElement.getContext('2d');
    let cameraContainer = document.getElementById('camera-container');
    let camera;
    let selectedLipColor = null; // Biến lưu trữ màu môi được chọn

    // Hàm lấy `color_code` từ bảng `code_color` dựa trên `color_name`
    async function getColorCodeFromColorName(colorName) {
        if (!colorName) {
            console.warn("No color_name provided");
            return 'rgba(255, 255, 255, 0)'; // Màu mặc định
        }
        try {
            const response = await fetch(`get_color_code.php?color_name=${encodeURIComponent(colorName)}`);
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

    // Hàm xử lý khi chọn `color_image`
    async function applyLipColorFromSelectedImage(colorImage, productName) {
        try {
            // Gửi yêu cầu lấy `color_name` tương ứng với `color_image` và `product_name`
            const response = await fetch(
                `get_code_color.php?color_image=${encodeURIComponent(colorImage)}&product_name=${encodeURIComponent(productName)}`
            );
            const data = await response.json();
            if (data.color_name) {
                selectedLipColor = await getColorCodeFromColorName(data.color_name);
                if (!camera) {
                    // Nếu camera chưa khởi động, khởi động camera và FaceMesh
                    startCamera();
                } else {
                    console.log(`Selected lip color: ${selectedLipColor}`);
                }
            } else {
                console.error(data.error || "Error: No color name found");
            }
        } catch (error) {
            console.error('Error fetching color name:', error);
        }
    }

    // Bắt đầu camera và áp dụng màu môi
    async function startCamera() {
        try {
            cameraContainer.style.display = 'block'; // Hiển thị container camera
            const constraints = {
                video: {
                    facingMode: 'user', // Camera trước
                },
            };

            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            videoElement.srcObject = stream;

            videoElement.onloadedmetadata = () => {
                const videoWidth = videoElement.videoWidth;
                const videoHeight = videoElement.videoHeight;

                // Đồng bộ kích thước video và canvas
                canvasElement.width = videoWidth;
                canvasElement.height = videoHeight;

                videoElement.style.width = `${videoWidth}px`;
                videoElement.style.height = `${videoHeight}px`;
                canvasElement.style.width = `${videoWidth}px`;
                canvasElement.style.height = `${videoHeight}px`;
            };

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

            faceMesh.onResults((results) => onResults(results, selectedLipColor));

            camera = new Camera(videoElement, {
                onFrame: async () => {
                    if (selectedLipColor) {
                        await faceMesh.send({
                            image: videoElement
                        });
                    }
                },
            });

            camera.start();
        } catch (error) {
            console.error('Error starting camera:', error);
        }
    }

    // Dừng camera
    function stopCamera() {
        cameraContainer.style.display = 'none'; // Ẩn container camera
        canvasElement.style.display = 'none'; // Ẩn canvas
        if (camera) {
            camera.stop();
        }
        if (videoElement.srcObject) {
            const stream = videoElement.srcObject;
            const tracks = stream.getTracks();
            tracks.forEach((track) => track.stop());
            videoElement.srcObject = null;
        }
        canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height); // Xóa canvas
        selectedLipColor = null; // Đặt lại màu môi
    }

    // Hàm xử lý khi nhận kết quả từ FaceMesh
    function onResults(results, lipColor) {
        if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0 && lipColor) {
            const faceLandmarks = results.multiFaceLandmarks[0];

            const upperLipTopIndices = [191, 80, 81, 82, 13, 312, 311, 310, 415, 308];
            const upperLipBottomIndices = [76, 185, 40, 39, 37, 0, 267, 269, 270, 409, 291];
            const lowerLipTopIndices = [61, 76, 178, 14, 402, 324, 318, 317, 402];
            const lowerLipBottomIndices = [61, 146, 91, 181, 84, 17, 314, 405, 291];

            const upperLipTop = upperLipTopIndices.map((index) => faceLandmarks[index]);
            const upperLipBottom = upperLipBottomIndices.map((index) => faceLandmarks[index]);
            const lowerLipTop = lowerLipTopIndices.map((index) => faceLandmarks[index]);
            const lowerLipBottom = lowerLipBottomIndices.map((index) => faceLandmarks[index]);

            if (upperLipTop && upperLipBottom && lowerLipTop && lowerLipBottom) {
                canvasElement.style.display = 'block';

                canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
                canvasCtx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

                canvasCtx.globalCompositeOperation = 'multiply';
                canvasCtx.filter = 'brightness(1.2) saturate(1.3)';

                canvasCtx.fillStyle = lipColor; // Màu được lấy từ API
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
                canvasCtx.globalAlpha = 0.6;
                canvasCtx.fill();

                canvasCtx.globalCompositeOperation = 'source-over';
                canvasCtx.filter = 'none';
                canvasCtx.globalAlpha = 1.0;
            }
        }
    }

    // Thêm sự kiện click vào các hình ảnh `color_image`
    document.querySelectorAll('.color-image').forEach((image) => {
        image.addEventListener('click', (event) => {
            const colorImage = event.target.dataset.colorImage;
            const productName = event.target.dataset.productName;
            applyLipColorFromSelectedImage(colorImage, productName);
        });
    });
    </script>



    <script type="text/javascript" src="../js/script2.js"></script>
</body>

</html>