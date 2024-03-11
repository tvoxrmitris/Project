
<style type="text/css">
    <?php
        include 'main.css'
    ?>
</style>

<link rel="stylesheet" type="text/css" href="main.css?v=1.1 <?php echo time();?>">


<!-- <script type="text/javascript" src="script2.js"></script> -->


<header class="header">
        <div class = "flex">
            <!-- <a href="admin_pannel.php" class ="logo"><img src="./image/logo.png"></a> -->
            <img style="cursor: pointer;" src="./image/logo1.png" width="140" onclick="window.location.href='index.php'">

            <nav class = "navbar">
                <a href="index.php">Trang chủ</a>
                <a href="about.php">Về chúng tôi</a>
                <a href="shop.php">Cửa hàng</a>
                <a href="order.php">Đơn hàng</a>
                <a href="contact.php">Liên hệ</a>
            </nav>
            
            
            
            <div class="icons">
                <i class="bi bi-search" id="search-btn"></i>
            
                <i class="bi bi-person" id="user-btn"></i>

                <?php

                    $select_wishlist = mysqli_query($conn, "SELECT * FROM `wishlist` WHERE user_id='$user_id'") or die('query failed');
                    $wishlist_num_rows = mysqli_num_rows($select_wishlist);
                ?>
                <a href="wishlist.php"><i class="bi bi-heart"></i><sup><?php echo $wishlist_num_rows; ?></sup></a>

                <?php
                    $select_cart =  mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id='$user_id'") or die('query failed');
                    $cart_num_rows = mysqli_num_rows($select_cart);
                ?>
                <a href="cart.php"><i class="bi bi-cart"></i><sup><?php echo $cart_num_rows; ?></sup></a>

                <i class="bi bi-list" id="menu-btn"></i>

            <div class="user-box">
                <p>Tên tài khoản: <span><?php echo $_SESSION['user_name']; ?></span></p>
                <p>Email: <span><?php echo $_SESSION['user_email']; ?></span></p>
                <form method="post">
                    <button type="submit" name="logout" class="logout-btn">Đăng xuất</button>
                </form>
            </div>
        
        </div>
    </header>
    

    <script type="text/javascript">
        document.getElementById('search-btn').addEventListener('click', function() {
            var searchBox = document.getElementById('searchBox');

            if (!searchBox) {
                searchBox = document.createElement('input');
                searchBox.setAttribute('type', 'text');
                searchBox.setAttribute('placeholder', 'Tìm kiếm...');
                searchBox.setAttribute('id', 'searchBox');
                searchBox.classList.add('search-box-class');

                var userBtn = document.getElementById('search-btn');
                userBtn.insertAdjacentElement('afterend', searchBox);

                this.classList.toggle('active');
                searchBox.focus();

                searchBox.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') { // Kiểm tra phím nhấn là phím "Enter"
                        var keyword = searchBox.value.trim();
                        if (keyword.length > 0) { // Kiểm tra ô tìm kiếm có dữ liệu không
                            window.location.href = 'search_resuilt.php?search=' + keyword;
                        }
                    }
                });
            } else {
                this.classList.toggle('active');
                searchBox.remove();
            }
        });


</script>


    </script>


