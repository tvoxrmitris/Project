<?php
    include '../connection/connection.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js" integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js" integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- <link rel="stylesheet" type="text/css" href="slick.css"> -->
    <link rel="stylesheet" type="text/css" href="../CSS/main.css?v=1.1 <?php echo time();?>">
    <title>Document</title>
</head>
<body>
<h2 style="text-align:center; font-size:50px;">Thương hiệu phổ biến</h2>
    <section class="popular-brands">
        
        <div class="controls">
            <i class="bi bi-chevron-left left"></i>
            <i class="bi bi-chevron-right right"></i>
        </div>

        <?php
            if(isset($message)){
                foreach($message as $message){
                    echo '
                        <div class="message">
                            <span>' . $message . '</span>
                            <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                        </div>
                    ';
                }
            }
        ?>

<div class="brand">

        <div class="popular-brands-content">
        <?php
            // Assuming you have a database connectio

            $select_brands = $conn->prepare("SELECT * FROM `brands`");
            $select_brands->execute();

            $result = $select_brands->get_result();

            // Fetch results and display
            while ($brands = $result->fetch_assoc()) {
        ?>
                <div class="box">
                    <img src="../image/<?= $brands['brand_image']; ?>">
                    <a href="../user/brand_product.php?brand_id=<?= $brands['brand_id']; ?>" class="btn-brand" style="text-transform: uppercase;"><?= $brands['brand_name']; ?></a>

                </div>
        <?php
            }
        ?>
        </div>
    </div>
    </section>
    <script src="jquary.js"></script>
    <script src="slick.js"></script>
    <script type="text/javascript">
        $('.popular-brands-content').slick({
            slidesToShow: 5,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 1500,
            lazyLoad: 'ondemand',
            slidesToShow: 5,
            slidesToScroll: 1,
            nextArrow: $('.left'),
            prevArrow: $('.right'),
            responsive: [
                {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: true,
                    dots: true
                }
                },
                {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
                },
                {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
                }
                // You can unslick at a given breakpoint now by adding:
                // settings: "unslick"
                // instead of a settings object
            ]
            });

        // $('.popular-brands-content').slick({
        //     slidesToShow: 5,
        //     slidesToScroll: 1,
        //     autoplay: true,
        //     autoplaySpeed: 2000,
        // });
    </script>
    <script>
    var dots = document.querySelectorAll('.dot');

// Lặp qua từng dấu chấm và gắn sự kiện click
dots.forEach(function(dot) {
    dot.addEventListener('click', function() {
        // Lấy chỉ số của dấu chấm
        var index = parseInt(dot.getAttribute('data-index'));

        // Lấy ID sản phẩm tương ứng
        var productId = dot.getAttribute('data-product-id');

        // Lấy tất cả các ảnh của sản phẩm
        var container = dot.closest('.box');
        var images = container.querySelectorAll('.imgshop');

        // Ẩn tất cả các ảnh của sản phẩm
        images.forEach(function(img) {
            img.classList.add('hidden');
        });

        // Hiển thị ảnh tương ứng
        images[index].classList.remove('hidden');

        // Bỏ chọn tất cả các dấu chấm của sản phẩm
        var productDots = container.querySelectorAll('.dot');
        productDots.forEach(function(d) {
            d.classList.remove('active');
        });

        // Chọn dấu chấm đã nhấp
        dot.classList.add('active');
    });
});
</script>
</body>
</html>