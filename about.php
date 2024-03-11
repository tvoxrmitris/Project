<?php
    include 'connection.php';
    session_start();
    $admin_id = $_SESSION['user_name'];
    $user_id = $_SESSION['user_id'];

    if(!isset($admin_id)){
        header('location:login.php');
    }

    if(isset($_POST['logout'])){
        session_destroy();
        header('location:login.php');
    }
   
?>

<style type="text/css">
    <?php
        include 'main.css'
    ?>
</style>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css" integrity="sha512-17EgCFERpgZKcm0j0fEq1YCJuyAWdz9KUtv1EjVuaOz8pDnh/0nZxmU6BBXwaaxqoi9PQXnRWqlcDB027hgv9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link class="logoo" rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css" integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js" integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js" integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" type="text/css" href="main.css?v=1.1 <?php echo time();?>">
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home</title>
</head>
<body>
    <!-- <div class="line3"></div> -->
    <?php include 'header.php'?>
    <div class="banner">
        <div class="detail">
            <h1>Về chúng tôi</h1>
            <p>Đôi mắt là ngôn từ của trái tim.</p>
            <a href="index.php">Trang chủ</a><span>/về chúng tôi</span>
        </div>
    </div>
    <div class="line"></div>
    <div class="line2"></div>
    <div class="about-us">
        <div class="row">
            <div class="box">
                <div class="title">
                    <span>Giới thiệu về cửa hàng trực tuyến của chúng tôi</span>
                    <h1>Chào mừng bạn đến với cửa hàng của chúng tôi</h1>
                </div>
                <p>
                    Cửa hàng chuyên cung cấp các loại mắt kính – gọng kính với mức giá phù hợp với tâm lý khách hàng và có tính cạnh tranh cao. Panda Eyewear luôn luôn mong muốn làm hài lòng tất cả khách hàng.
                </p>
            </div>
            <div class="img-box">
                <img src="image/aboutus.jpg">
            </div>
        </div>
    </div>
    <div class="line3"></div>

    <div class="testimonial-fluid">
        <h1 class="title">Khách hàng nói gì về chúng tôi?</h1>
        <div class="testimonial-slider">
            <!-- <div class="testimonial-item">
                <img src="./image/profile3.jpg">
                <div class="testimonail-caption">
                   <span>Kiểm tra chất lượng</span>
                    <h1>Mắt kính Panda</h1>
                    <p>"Mình đã đặt mua mắt kính tại website Panda và rất hài lòng với sản phẩm cũng như dịch vụ. Mắt kính được đóng gói cẩn thận, giao hàng nhanh chóng. Sản phẩm đúng như mô tả, chất lượng tốt, phù hợp với nhu cầu của mình. Mình sẽ tiếp tục ủng hộ website này trong thời gian tới."</p>
                </div>
            </div> -->
            <div class="testimonial-item">
                <img src="./image/profile2.jpg">
                <div class="testimonail-caption">
                    <span>Kiểm tra chất lượng</span>
                    <h1>Mắt kính Panda</h1>
                    <p>"Mình là một người rất khó tính trong việc lựa chọn mắt kính. Tuy nhiên, sau khi đặt mua tại website Panda, mình đã hoàn toàn hài lòng. Mắt kính có nhiều mẫu mã đa dạng, phù hợp với mọi nhu cầu. Đội ngũ tư vấn viên rất nhiệt tình, giúp mình lựa chọn được sản phẩm phù hợp nhất. Mình sẽ giới thiệu website này cho bạn bè và người thân."</p>
                </div>
            </div>
            <div class="testimonial-item">
                <img src="./image/profile1.jpg">
                <div class="testimonail-caption">
                     <span>Kiểm tra chất lượng</span>
                    <h1>Mắt kính Panda</h1>
                    <p>Mình rất thích giao diện trang web của cửa hàng. Trang web thiết kế đẹp, dễ sử dụng. Trang web có nhiều thông tin hữu ích, giúp mình dễ dàng tìm kiếm sản phẩm. Mình cũng rất hài lòng với các chương trình khuyến mãi của cửa hàng.</p>
                </div>
            </div>
        </div>
    
    <div class="line3"></div>
    <!-- <div class="discover">
        <div class="detail">
            <h1 class="title">Mắt kính Panda</h1>
            <span> Mua ngay để được giảm 30%!</span>
            <p>Sự ra đời của mắt kính là một trong những phát minh quan trọng nhất trong lịch sử loài người. Nó đã giúp cải thiện thị lực cho hàng triệu người trên thế giới và góp phần vào sự phát triển của khoa học, công nghệ và văn hóa.</p>
            <a href="shop.php" class="btn">Trãi nghiệm ngay</a>
        </div>
        <div class="img-box">
            <img src="image/discover1.jpg">
        </div>
    </div> -->
    
    <div class="line3"></div>
    <script type="text/javascript">
        $('.testimonial-slider').slick({
        dots: true,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 1300,
        lazyLoad: 'ondemand',
        // speed: 300,
        slidesToShow: 1,
        adaptiveHeight: true
    });

    </script>

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

    <!-- <div class="features">      
        <div class="title">
            <h1>Hoàn thiện ý tưởng của khách hàng</h1>
            <span>Tính năng tốt nhất</span>
        </div>
        <div class="row">
        <div class="box">
                <img src="image/support.png">
                <h4>24/7</h4>
                <p>Hỗ trợ tư vấn online 24/7</p>
            </div>
            <div class="box">
                <img src="image/moneyback.png">
                <h4>Đảm bảo hoàn tiền</h4>
                <p>Thanh toán an toàn 100%</p>
            </div>
            <div class="box">
                <img src="image/shipping.png">
                <h4>Giao hàng toàn quốc</h4>
                <p>Giao hàng tặng nơi miễn phí 63 tỉnh thành</p>
            </div>
        </div>
        
    </div>

    <div class="line2"></div>

    <div class="team">
        <div class="title">
            <h1>Đội ngũ của chúng tôi</h1>
            <span>Đội ngũ xuất sắc nhất</span>
        </div>
        <div class="row">
            <div class="box">
                <div class="img-box">
                    <img src="image/profile1.jpg">
                </div>
                <div class="detail">
                    <span>Quản lí tài chính</span>
                    <h4>Trí Minh Võ</h4>
                    <div class="icons">
                        <i class="bi bi-instagram"></i>
                        <i class="bi bi-facebook"></i>
                        <i class="bi bi-spotify"></i>
                        <i class="bi bi-youtube"></i>
                        <i class="bi bi-twitter"></i>
                        <i class="bi bi-snapchat"></i>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="img-box">
                    <img src="image/profile1.jpg">
                </div>
                <div class="detail">
                    <span>Quản lí tài chính</span>
                    <h4>Trí Minh Võ</h4>
                    <div class="icons">
                        <i class="bi bi-instagram"></i>
                        <i class="bi bi-facebook"></i>
                        <i class="bi bi-spotify"></i>
                        <i class="bi bi-youtube"></i>
                        <i class="bi bi-twitter"></i>
                        <i class="bi bi-snapchat"></i>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="img-box">
                    <img src="image/profile1.jpg">
                </div>
                <div class="detail">
                    <span>Quản lí tài chính</span>
                    <h4>Trí Minh Võ</h4>
                    <div class="icons">
                        <i class="bi bi-instagram"></i>
                        <i class="bi bi-facebook"></i>
                        <i class="bi bi-spotify"></i>
                        <i class="bi bi-youtube"></i>
                        <i class="bi bi-twitter"></i>
                        <i class="bi bi-snapchat"></i>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="img-box">
                    <img src="image/profile1.jpg">
                </div>
                <div class="detail">
                    <span>Quản lí tài chính</span>
                    <h4>Trí Minh Võ</h4>
                    <div class="icons">
                        <i class="bi bi-instagram"></i>
                        <i class="bi bi-facebook"></i>
                        <i class="bi bi-spotify"></i>
                        <i class="bi bi-youtube"></i>
                        <i class="bi bi-twitter"></i>
                        <i class="bi bi-snapchat"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="line"></div>
    <div class="project">
        <div class="title">
            <h1>Dự án tốt nhất của chúng tôi</h1>
            <span>Làm thế nào để nó hoạt động</span>
        </div>
        <div class="row">
            <div class="box">
                <img src="image/teamwork.jpg">
            </div>
            <div class="box">
                <img src="image/teamwork1.jpg">
            </div>
        </div>
    </div> -->
    <div class="line2"></div>
    <div class="ideas">
        <div class="title">
            <h1>Chúng tôi và khách hàng rất vui được hợp tác với công ty chúng tôi.</h1>
            <span>Các tính năng của chúng tôi</span>
        </div>
        <div class="row">
            <div class="box">
                <i class="bi bi-grid-1x2-fill"></i>
                <div class="detail">
                    <h2>Lịch sử hình thành</h2>
                    <p>Từ năm 2010 trở lại đây, cùng với sự phát triển của công nghệ, các trang web bán mắt kính đã có những bước phát triển vượt bậc. Các trang web này được thiết kế hiện đại, cung cấp nhiều thông tin hữu ích về sản phẩm, bao gồm hình ảnh, video, thông số kỹ thuật, giá cả,... Người tiêu dùng có thể dễ dàng tham khảo thông tin và lựa chọn sản phẩm phù hợp.</p>
                </div>
            </div>
            <div class="box">
                <i class="bi bi-stack"></i>
                <div class="detail">
                    <h2>Những gì chúng tôi thật sự làm</h2>
                    <p>Chất lượng sản phẩm: Trang web chỉ nên bán các sản phẩm chất lượng cao, đáp ứng nhu cầu của người tiêu dùng.<br>Giá cả cạnh tranh: Trang web cần cung cấp các sản phẩm với giá cả cạnh tranh, phù hợp với túi tiền của người tiêu dùng.<br>Dịch vụ tốt: Trang web cần cung cấp các dịch vụ tiện ích cho người tiêu dùng, chẳng hạn như dịch vụ giao hàng tận nhà, dịch vụ bảo hành,...</p>
                </div>
            </div>
            <div class="box">
                <i class="bi bi-tropical-storm"></i>
                <div class="detail">
                    <h2>Tầm nhìn của chúng tôi</h2>
                    <p>Các trang web bán mắt kính sẽ tiếp tục phát triển mạnh mẽ. Với sự phát triển của công nghệ thực tế ảo (VR), người tiêu dùng sẽ có thể thử kính trực tuyến một cách chân thực và chính xác hơn. Ngoài ra, các trang web bán mắt kính cũng sẽ cung cấp thêm nhiều dịch vụ tiện ích cho người tiêu dùng, chẳng hạn như dịch vụ giao hàng tận nhà, dịch vụ bảo hành,...</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'?>
    
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script> -->
    <script type="text/javascript" src="script2.js"></script>
</body>
</html>