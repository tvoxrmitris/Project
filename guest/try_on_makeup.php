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
    <title>FaceMesh Lip Detection</title>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/facemesh"></script>
</head>

<body>
    <!-- <div class="line3"></div> -->
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
        width: 1045px;
        height: 787px;
        top: -102;
        left: -21;
        transform: scaleX(-1);
    }

    .products-container {
        max-height: 800px;
        /* Giới hạn chiều cao */
        overflow-y: auto;
        /* Thanh cuộn dọc */
        padding: 10px;
        /* Khoảng cách bên trong */
        border: 1px solid #ccc;
        /* Viền để dễ phân biệt */
        background-color: #f9f9f9;
        /* Màu nền */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        /* Hiệu ứng đổ bóng */
    }

    #camera-container button {
        position: absolute;
        top: -110px;
        left: -30px;

        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        color: #000;
        transition: all 0.3s ease;

    }

    #camera-container button:hover {

        /* Màu đậm hơn khi hover */
        transform: scale(1.1);
        /* Phóng to nhẹ khi hover */
    }

    .try-now-btn {
        background-color: black;
        color: white;
        border: 2px solid black;
        padding: 12px 25px;
        border-radius: 25px;
        /* Bo tròn nút */
        font-size: 18px;
        font-family: 'Georgia', serif;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
    }

    .try-now-btn:hover {
        background-color: white;
        color: black;
        border: 2px solid black;
        box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.3);
    }

    .guest-link {
        color: black;
        text-decoration: none;
        background-color: white;
        padding: 12px 25px;
        border-radius: 25px;
        font-size: 18px;
        font-family: 'Georgia', serif;
        text-transform: uppercase;
        border: 2px solid black;
        transition: all 0.3s ease-in-out;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
    }

    .guest-link:hover {
        background-color: black;
        color: white;
        border: 2px solid white;
        box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.3);
    }
    </style>
    <div class="main-container">
        <div class="camera">
            <button type="button" class="try-now-btn" onclick="startCamera()">Thử ngay</button>
            <a href="guest.php" class="guest-link">Trở về</a>
            <div id="camera-container" class="camera-container" style="display: none;">
                <video id="camera" autoplay></video>
                <canvas id="overlay"></canvas>
                <button type="button" onclick="stopCamera()">X</button>
            </div>
        </div>


        <div class="products-container">
            <?php
        if (!empty($products)):
            // Truy vấn để lấy danh sách product_id từ bảng code_color
            $query = "SELECT DISTINCT product_id FROM code_color";
            $result = $conn->query($query);
            $validProductIds = [];

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $validProductIds[] = $row['product_id'];
                }
            }

            // Nhóm sản phẩm theo subcategory
            $groupedProducts = [];
            foreach ($products as $product) {
                if (in_array($product['product_id'], $validProductIds)) {
                    $groupedProducts[$product['product_subcategory']][] = $product;
                }
            }

            foreach ($groupedProducts as $subcategory => $items):
                $displayedNames = [];
        ?>
            <div class="subcategory-group" data-product-subcategory="<?php echo htmlspecialchars($subcategory); ?>">
                <h2 class="subcategory-title"><?php echo htmlspecialchars($subcategory); ?></h2>
                <div class="product-items">
                    <?php foreach ($items as $product):
                        if (!in_array($product['product_name'], $displayedNames)):
                            $displayedNames[] = $product['product_name'];
                            $colorImages = [];
                            foreach ($products as $p) {
                                if ($p['product_name'] === $product['product_name'] 
                                    && $p['product_subcategory'] === $subcategory 
                                    && !empty($p['color_image'])
                                    && in_array($p['product_id'], $validProductIds)) {
                                    $colorImages[] = [
                                        'color_image' => $p['color_image'],
                                        'product_id' => $p['product_id']
                                    ];
                                }
                            }
                    ?>
                    <div class="product-item">
                        <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <div class="color-images">
                            <?php foreach ($colorImages as $colorImageData): ?>
                            <img src="../image/colorimage/<?php echo urlencode($colorImageData['color_image']); ?>"
                                alt="Color Image" class="color-image"
                                data-color-image="<?php echo htmlspecialchars($colorImageData['color_image']); ?>"
                                data-product-id="<?php echo htmlspecialchars($colorImageData['product_id']); ?>"
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


    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3@7.8.0/dist/d3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-delaunay@5.0.1/dist/d3-delaunay.min.js"></script>

    <script>
    let videoElement = null;
    let camera = null;
    let faceMesh = null;
    let isLipDetectionEnabled = false; // Trạng thái phát hiện môi
    let currentEffectType = null; // Lưu trạng thái hiệu ứng hiện tại
    let currentEffectColor = null; // Lưu trạng thái màu hiện tại
    let selectedColorImage = null; // Biến lưu trạng thái color_image hiện tại


    // Hàm khởi động camera
    function startCamera() {
        videoElement = document.getElementById('camera');
        const cameraContainer = document.getElementById('camera-container');
        cameraContainer.style.display = 'block';

        faceMesh = new FaceMesh({
            locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`,
        });

        faceMesh.setOptions({
            maxNumFaces: 1,
            refineLandmarks: true,
            minDetectionConfidence: 0.6,
            minTrackingConfidence: 0.6,
        });

        faceMesh.onResults((results) => {
            if (currentEffectType && currentEffectColor) {
                onResults(results, currentEffectColor, currentEffectType);
            }
        });

        camera = new Camera(videoElement, {
            onFrame: async () => {
                if (isLipDetectionEnabled) {
                    await faceMesh.send({
                        image: videoElement
                    });
                }
            },
            width: 640,
            height: 480,
        });
        camera.start();
        console.log("Camera và FaceMesh đã được khởi động.");
    }

    // Hàm dừng camera
    function stopCamera() {
        const cameraContainer = document.getElementById('camera-container');
        cameraContainer.style.display = 'none';

        if (camera) camera.stop();
        if (faceMesh) faceMesh.close();
        isLipDetectionEnabled = false; // Dừng phát hiện
    }


    //lip border
    const lipbordertopIndices = [61, 185, 40, 39, 37, 0, 167, 269, 270, 409, 291];
    const lipborderbottomIndices = [61, 146, 91, 181, 84, 17, 314, 405, 321, 375, 291];

    // Các chỉ số landmarks cho vùng mắt
    const leftEyetopRegionIndices = [247, 30, 29, 27, 28, 56, 190];
    const leftEyebottomRegionIndices = [113, 225, 224, 223, 222, 221, 180];

    // Vị trí landmarks vùng môi
    const upperLipTopIndices = [191, 80, 81, 82, 13, 312, 311, 310, 415, 308];
    const upperLipBottomIndices = [76, 185, 40, 39, 37, 0, 267, 269, 270, 409, 291];
    const lowerLipTopIndices = [61, 76, 178, 14, 402, 324, 318, 317, 402];
    const lowerLipBottomIndices = [61, 146, 91, 181, 84, 17, 314, 405, 291];

    //vị trí landmarks vùng lông mi
    const upperLeftEyelashIndices = [246, 161, 160, 159, 158, 157, 173];
    const upperRightEyelashIndices = [362, 385, 386, 387, 388, 466, 263];


    const leftEyelinerIndices = [246, 130, 113, 246, 161, 160, 159, 158, 157, 173, 133]; // Mí mắt trái
    const rightEyelinerIndices = [362, 398, 384, 385, 386, 387, 388, 466, 263, 342, 359, 263]; // Mí mắt phải
    const fullfaceIndices = [152, 148, 176, 149, 150, 136, 172, 58, 132, 93, 234, 127, 162, 21, 54, 103, 67, 109, 10,
        338, 297, 332, 284, 251, 389, 356, 454, 323, 361, 288, 397, 365, 379, 378, 400, 377, 152
    ];

    const leftcheekIndices = [147, 137, 227, 116, 117, 118, 101, 205, 187];
    const rightcheekIndices = [411, 376, 366, 447, 345, 345, 347, 330, 425];



    // Hàm xử lý kết quả FaceMesh
    function onResults(results, effectColor, effectType) {
        const canvas = document.getElementById('overlay');
        const canvasCtx = canvas.getContext('2d');
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;

        // Xóa canvas cũ
        canvasCtx.clearRect(0, 0, canvas.width, canvas.height);

        if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
            const landmarks = results.multiFaceLandmarks[0];

            if (effectType === "lips") {
                const upperLipTop = upperLipTopIndices.map((index) => landmarks[index]);
                const upperLipBottom = upperLipBottomIndices.map((index) => landmarks[index]);
                const lowerLipTop = lowerLipTopIndices.map((index) => landmarks[index]);
                const lowerLipBottom = lowerLipBottomIndices.map((index) => landmarks[index]);

                canvasCtx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
                canvasCtx.globalCompositeOperation = 'multiply';
                canvasCtx.fillStyle = effectColor;
                canvasCtx.globalAlpha = 0.6;

                canvasCtx.beginPath();
                canvasCtx.moveTo(upperLipTop[0].x * canvas.width, upperLipTop[0].y * canvas.height);
                upperLipTop.forEach((point) => canvasCtx.lineTo(point.x * canvas.width, point.y * canvas.height));
                upperLipBottom.reverse().forEach((point) => canvasCtx.lineTo(point.x * canvas.width, point.y * canvas
                    .height));
                lowerLipTop.forEach((point) => canvasCtx.lineTo(point.x * canvas.width, point.y * canvas.height));
                lowerLipBottom.reverse().forEach((point) => canvasCtx.lineTo(point.x * canvas.width, point.y * canvas
                    .height));
                canvasCtx.closePath();
                canvasCtx.fill();
            } else if (effectType === "lip_border") {
                const upperLipBottom = upperLipBottomIndices.map((index) => landmarks[index]);
                const lowerLipBottom = lowerLipBottomIndices.map((index) => landmarks[index]);

                canvasCtx.globalCompositeOperation = 'source-over';
                canvasCtx.strokeStyle = effectColor;
                canvasCtx.lineWidth = 4;
                canvasCtx.globalAlpha = 1.0;
                canvasCtx.filter = "blur(1.5px)";

                canvasCtx.beginPath();
                upperLipBottom.forEach((point) => canvasCtx.lineTo(point.x * canvas.width, point.y * canvas.height));
                canvasCtx.stroke();

                canvasCtx.beginPath();
                lowerLipBottom.forEach((point) => canvasCtx.lineTo(point.x * canvas.width, point.y * canvas.height));
                canvasCtx.stroke();
            } else if (effectType === "Eyeliner") {
                // Lấy các chỉ số của mí trên mắt trái và mắt phải
                const leftEyeliner = leftEyelinerIndices.map((index) => landmarks[index]);
                const rightEyeliner = rightEyelinerIndices.map((index) => landmarks[index]);

                // Thiết lập các thuộc tính vẽ
                canvasCtx.globalCompositeOperation = 'multiply'; // Chế độ hòa trộn
                canvasCtx.strokeStyle = effectColor; // Màu eyeliner
                canvasCtx.lineJoin = 'round'; // Làm mượt các góc
                canvasCtx.lineCap = 'round'; // Làm mượt các đầu đường vẽ
                canvasCtx.globalAlpha = 0.9; // Độ trong suốt

                // Hàm vẽ eyeliner
                function drawEyeliner(eyelinerPoints) {
                    canvasCtx.beginPath();
                    eyelinerPoints.forEach((point, index) => {
                        // Tính khoảng cách giữa điểm hiện tại và điểm tiếp theo
                        const nextPoint = eyelinerPoints[index + 1];
                        if (nextPoint) {
                            const distance = Math.sqrt(
                                Math.pow(nextPoint.x - point.x, 2) +
                                Math.pow(nextPoint.y - point.y, 2)
                            );

                            // Điều chỉnh độ dày eyeliner theo khoảng cách (mềm mại ở đầu và dày hơn ở giữa)
                            canvasCtx.lineWidth = Math.min(4, Math.max(2, distance * 1.5));
                        }

                        // Vẽ eyeliner trên từng đoạn
                        canvasCtx.lineTo(point.x * canvas.width, point.y * canvas.height);
                    });
                    canvasCtx.stroke();

                    // Áp dụng làm mờ viền để eyeliner nhìn mềm mại hơn
                    canvasCtx.filter = "blur(10px)";
                    canvasCtx.stroke();
                    canvasCtx.filter = "none"; // Reset filter sau khi vẽ
                }

                // Vẽ eyeliner cho mí trái
                drawEyeliner(leftEyeliner);

                // Vẽ eyeliner cho mí phải
                drawEyeliner(rightEyeliner);

                // Reset chế độ hòa trộn
                canvasCtx.globalCompositeOperation = 'source-over';
                canvasCtx.globalAlpha = 1.0; // Reset alpha
            } else if (effectType === "eyeshadow") {
                // Chọn các điểm landmark theo chỉ số đã cung cấp
                const leftEyeTopRegion = leftEyetopRegionIndices.map((index) => landmarks[index]);
                const leftEyeBottomRegion = leftEyebottomRegionIndices.map((index) => landmarks[index]);

                // Thiết lập chế độ vẽ và màu sắc
                canvasCtx.globalCompositeOperation = 'multiply';
                canvasCtx.fillStyle = effectColor;
                canvasCtx.globalAlpha = 0.5;

                // Vẽ vùng eyeshadow bên mắt trái
                canvasCtx.beginPath();
                leftEyeTopRegion.forEach((point, index) => {
                    const x = point.x * canvas.width;
                    const y = point.y * canvas.height;
                    if (index === 0) {
                        canvasCtx.moveTo(x, y);
                    } else {
                        canvasCtx.lineTo(x, y);
                    }
                });

                // Vẽ phần mắt dưới ngược chiều
                leftEyeBottomRegion.reverse().forEach((point) => {
                    const x = point.x * canvas.width;
                    const y = point.y * canvas.height;
                    canvasCtx.lineTo(x, y);
                });

                // Kết thúc vùng và tô màu
                canvasCtx.closePath();
                canvasCtx.fill();
            } else if (effectType === "mascara") {
                // Lấy các chỉ số của lông mi trên cho cả hai mắt
                const upperLeftEyelash = upperLeftEyelashIndices.map((index) => landmarks[index]);
                const upperRightEyelash = upperRightEyelashIndices.map((index) => landmarks[index]);

                // Thiết lập thuộc tính vẽ
                canvasCtx.globalCompositeOperation = 'source-over';

                // Hàm vẽ lông mi ngắn và mềm mại
                function drawShortMascara(eyelashPoints, isUpper = true) {
                    eyelashPoints.forEach((point, idx) => {
                        const centerX = point.x * canvas.width;
                        const centerY = point.y * canvas.height;

                        // Thông số lông mi
                        const totalLashes = 9; // Tăng mật độ lông mi
                        const baseAngle = isUpper ? -Math.PI / 2 : Math.PI / 2; // Hướng lông mi
                        const angleRange = Math.PI / 8; // Phân tán góc cho lông mi
                        const maxLength = isUpper ? 6 : 5; // Lông mi trên dài hơn một chút
                        const lengthVariance = 1.5; // Độ biến thiên chiều dài (1.5px)

                        for (let i = -4; i <= 4; i++) {
                            const angle = baseAngle + i * (angleRange / totalLashes); // Góc từng sợi
                            const length = maxLength - Math.random() *
                                lengthVariance; // Chiều dài ngắn và ngẫu nhiên

                            // Tạo điểm điều khiển (độ cong mạnh mẽ)
                            const controlX = centerX + Math.cos(angle) * length *
                                0.8; // Điều chỉnh để tạo độ cong mạnh hơn
                            const controlY = centerY + Math.sin(angle) * length *
                                0.7; // Điều chỉnh để tạo độ cong mạnh hơn

                            // Điểm cuối (lông mi cong vào)
                            const endX = centerX + Math.cos(angle) * length;
                            const endY = centerY + Math.sin(angle) * length;

                            // Vẽ lông mi bằng đường cong
                            canvasCtx.beginPath();
                            canvasCtx.moveTo(centerX, centerY);
                            canvasCtx.quadraticCurveTo(controlX, controlY, endX, endY);

                            // Độ dày mảnh hơn (0.1-0.3px)
                            canvasCtx.lineWidth = Math.random() * 0.1 + 0.07; // Mảnh hơn
                            canvasCtx.strokeStyle =
                                `rgba(0, 0, 0, ${Math.random() * 0.2 + 0.3})`; // Alpha ngẫu nhiên
                            // canvasCtx.filter = "blur(0.7px)"; // Làm mềm nét vẽ
                            canvasCtx.stroke();
                        }
                    });
                }


                // Vẽ lông mi cho mắt trái
                drawShortMascara(upperLeftEyelash, true);

                // Vẽ lông mi cho mắt phải
                drawShortMascara(upperRightEyelash, true);
            }




            if (effectType === "blush") {
                // Lấy tọa độ các điểm landmark cho má trái và má phải
                const leftCheek = leftcheekIndices.map((index) => landmarks[index]);
                const rightCheek = rightcheekIndices.map((index) => landmarks[index]);

                // Thiết lập các thuộc tính hiệu ứng
                canvasCtx.globalCompositeOperation = 'source-over';
                canvasCtx.fillStyle = effectColor; // Màu của hiệu ứng
                canvasCtx.globalAlpha = 0.3; // Làm màu nhạt hơn
                canvasCtx.filter = "blur(5px)"; // Làm mờ để tạo hiệu ứng tự nhiên

                // Vẽ hiệu ứng cho má trái
                canvasCtx.beginPath();
                canvasCtx.moveTo(leftCheek[0].x * canvas.width, leftCheek[0].y * canvas.height);
                leftCheek.forEach((point) => canvasCtx.lineTo(point.x * canvas.width, point.y * canvas.height));
                canvasCtx.closePath();
                canvasCtx.fill();

                // Vẽ hiệu ứng cho má phải
                canvasCtx.beginPath();
                canvasCtx.moveTo(rightCheek[0].x * canvas.width, rightCheek[0].y * canvas.height);
                rightCheek.forEach((point) => canvasCtx.lineTo(point.x * canvas.width, point.y * canvas.height));
                canvasCtx.closePath();
                canvasCtx.fill();
            } else if (effectType === "foundation") {
                // Lấy các chỉ số landmark khuôn mặt từ fullfaceIndices
                const faceLandmarks = fullfaceIndices.map(index => landmarks[index]);

                // Thiết lập chế độ hòa trộn để giữ kết cấu da
                canvasCtx.globalCompositeOperation = 'soft-light'; // Làm sáng tối tự nhiên với kết cấu da
                canvasCtx.globalAlpha = 0.6; // Độ mờ của lớp kem nền (60%)

                // Tạo gradient chuyển đổi từ màu kem nền sang trong suốt
                const points = faceLandmarks.map(point => [point.x * canvas.width, point.y * canvas.height]);
                const gradient = canvasCtx.createLinearGradient(
                    points[0][0], points[0][1], // Điểm bắt đầu (đỉnh khuôn mặt)
                    points[Math.floor(points.length / 2)][0], points[Math.floor(points.length / 2)][
                        1
                    ] // Điểm giữa khuôn mặt
                );
                gradient.addColorStop(0, effectColor); // Màu chính của kem nền
                gradient.addColorStop(1, "transparent"); // Rìa chuyển đổi sang trong suốt
                canvasCtx.fillStyle = gradient; // Thiết lập gradient làm màu tô

                // Vẽ đường bao quanh khuôn mặt
                canvasCtx.beginPath();
                points.forEach((point, index) => {
                    const [x, y] = point;
                    if (index === 0) {
                        canvasCtx.moveTo(x, y); // Điểm đầu tiên
                    } else {
                        canvasCtx.lineTo(x, y); // Vẽ đường nối đến điểm tiếp theo
                    }
                });
                canvasCtx.closePath(); // Đóng hình polygon

                // Tô màu vùng khuôn mặt dựa trên hình polygon
                canvasCtx.fill();
            }
        }
    }

    let selectedEffects = {}; // Object lưu trạng thái hiệu ứng cho từng danh mục

    document.querySelectorAll('.color-image').forEach((image) => {
        image.addEventListener('click', async (event) => {
            const clickedImage = event.target; // Lấy color_image được nhấn
            const productID = clickedImage.getAttribute('data-product-id');
            const colorName = clickedImage.getAttribute('data-color-name');
            const subcategory = clickedImage.closest('.subcategory-group').getAttribute(
                'data-product-subcategory');

            // Kiểm tra nếu danh mục này đã có hiệu ứng
            if (selectedEffects[subcategory]?.element === clickedImage) {
                console.log(`Loại bỏ hiệu ứng của danh mục: ${subcategory}`);
                currentEffectType = null;
                currentEffectColor = null;
                isLipDetectionEnabled = false;

                // Xóa canvas khi hiệu ứng bị loại bỏ
                const canvas = document.getElementById('overlay');
                const canvasCtx = canvas.getContext('2d');
                canvasCtx.clearRect(0, 0, canvas.width, canvas.height);

                // Xóa trạng thái hiệu ứng của danh mục
                delete selectedEffects[subcategory];
                return;
            }

            // Nếu chọn một màu mới
            try {
                const response = await fetch('./get_code_color.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productID,
                        color_name: colorName
                    }),
                });
                const data = await response.json();

                if (data.success && data.color_code) {
                    // Loại bỏ hiệu ứng cũ trong cùng danh mục (nếu có)
                    if (selectedEffects[subcategory]) {
                        console.log(`Thay thế hiệu ứng cũ của danh mục: ${subcategory}`);
                        const canvas = document.getElementById('overlay');
                        const canvasCtx = canvas.getContext('2d');
                        canvasCtx.clearRect(0, 0, canvas.width, canvas.height);
                    }

                    // Áp dụng hiệu ứng mới
                    currentEffectColor = data.color_code;

                    if (subcategory === "Kẻ Viền Môi") {
                        currentEffectType = "lip_border";
                    } else if (subcategory === "Son Môi") {
                        currentEffectType = "lips";
                    } else if (subcategory === "Mascara") {
                        currentEffectType = "mascara"; // Gán hiệu ứng mascara
                    } else if (subcategory === "Kem Nền") {
                        currentEffectType = "foundation"; // Gán hiệu ứng kem nền
                    } else if (subcategory === "Phấn Má") {
                        currentEffectType = "blush";
                    } else if (subcategory === "Phấn Mắt") {
                        currentEffectType = "eyeshadow"; // Gán hiệu ứng phấn mắt
                    } else {
                        currentEffectType = "Eyeliner";
                    }





                    console.log(`Hiệu ứng áp dụng cho danh mục ${subcategory}:`,
                        currentEffectType,
                        "Màu sắc:", currentEffectColor);

                    isLipDetectionEnabled = true; // Bật phát hiện môi

                    // Lưu trạng thái hiệu ứng mới
                    selectedEffects[subcategory] = {
                        element: clickedImage,
                        color: currentEffectColor,
                        type: currentEffectType,
                    };
                } else {
                    console.error("Không tìm thấy mã màu.");
                }
            } catch (error) {
                console.error("Lỗi khi lấy màu sắc:", error);
            }
        });
    });
    </script>






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







</body>

</html>