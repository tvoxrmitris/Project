<?php
    include 'connection.php';
    session_start();
    $admin_id = $_SESSION['admin_name'];

    if(!isset($admin_id)){
        header('location:login.php');
    }

    if(isset($_POST['logout'])){
        session_destroy();
        header('location:login.php');
    }

    //adding product to database
    if(isset($_POST['add_product'])){
        $product_name = mysqli_real_escape_string($conn, $_POST['name']);
        $product_price = mysqli_real_escape_string($conn, $_POST['price']);
        $product_detail = mysqli_real_escape_string($conn, $_POST['detail']);
        $product_brands = mysqli_real_escape_string($conn, $_POST['brand']);
        $product_categories = mysqli_real_escape_string($conn, $_POST['category']);
        $image = $_FILES['image']['name'];
        $image_size = $_FILES['image']['size'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_folder = 'image/' .$image;

        $select_product_name = mysqli_query($conn, "SELECT name FROM products WHERE name = '$product_name'") or die('query failed');
        if(mysqli_num_rows($select_product_name) > 10){
            $message[] = 'Tên sản phẩm đã tồn tại';
        }else {
            $insert_product = mysqli_query($conn, "INSERT INTO products(name, price, product_detail, image, brands, categories)
                VALUES('$product_name', '$product_price', '$product_detail', '$image', '$product_brands', '$product_categories')") or die('query failed');
        if ($insert_product) {
                if ($image_size > 2000000) {
                    $message[] = 'Kích thươc ảnh quá lớn';
                } else {
                    move_uploaded_file($image_tmp_name, $image_folder);
                    $message[] = 'Sản phẩm đã được thêm thành công ';
                }
            } else {
                $message[] = 'Không thể thêm sản phẩm: ' . mysqli_error($conn);
            }
        }
    }

    //delete product from database
    if(isset($_GET['delete'])){
        $delete_id = $_GET['delete'];
        $select_delete_image = mysqli_query($conn, "SELECT image FROM `products` WHERE id='$delete_id'") or die('query failed');
        $fetch_delete_image = mysqli_fetch_assoc($select_delete_image);
        // unlink('image/'.$fetch_delete_image['image']);
        mysqli_query($conn,"DELETE FROM `products` WHERE id = '$delete_id'") or die('query faild');
        mysqli_query($conn,"DELETE FROM `cart` WHERE pid = '$delete_id'") or die('query faild');
        mysqli_query($conn,"DELETE FROM `wishlist` WHERE pid = '$delete_id'") or die('query failed');

        header('location:admin_product.php');
    }

    //update product
    if(isset($_POST['updte_product'])){
        $update_id = $_POST['update_id'];
        $update_name = $_POST['update_name'];
        $update_price = $_POST['update_price'];
        $update_detail = $_POST['update_detail'];
        $update_brand = $_POST['update_brand'];
        $update_category = $_POST['update_category'];
        $update_image = $_FILES['update_image']['name'];
        $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
        $update_image_folder = 'image/'.$update_image;

        $update_query = mysqli_query($conn,"UPDATE `products` SET id ='$update_id', name = '$update_name', price ='$update_price' ,product_detail ='$update_detail', image ='$update_image', brands='$update_brand', categories='$update_category' WHERE id ='$update_id'") or die('query failed');
        if($update_query){
            move_uploaded_file($update_image_tmp_name, $update_image_folder);
            header('location:admin_product.php');
        }
        
        
    }
    if (isset($_POST['cancel-form'])) {
        header('location:admin_product.php');
        exit();
    }

    



?>
<style type="text/css">
    <?php
        include 'style.css';
    ?>
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="image/logo.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" type="text/css" href="style.css?v=1.1 <?php echo time();?>">
    <title>Admin Product</title>
</head>
<body>
    <?php include 'admin_header.php';?>
    <?php
        if(isset($message)){
            foreach($message as $message){
                echo '
                    <div class="message">
                        <span>'.$message.'</span>
                        <i class="bi bi-x-circle" onclick="this.parentElement.remove()"></i>
                    </div>
                ';
            }
        }
    ?>

    <div class="line2"></div>
    <section class="add-products form-container">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="input-field">
                <label>Tên sản phẩm<span>*</span></label>
                <input type="text" name="name" required>
            </div>

            <div class="input-field">
                <label>Giá sản phẩm<span>*</span><br></label>
                <input type="text" name="price" required>
            </div>
            <?php
                $query = "SELECT * FROM categories";
                $result = mysqli_query($conn, $query);
                $categories = array();
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $categories[] = $row;
                    }
                }
            ?>
            <div class="input-field">
                <label>Danh mục<span>*</span></label>
                <select name="category"> 
                    
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['name']; ?>" style="text-transform: capitalize;"><?= $category['name']; ?></option>
                        <?php endforeach; ?>
                </select>
                        <
            </div>

            <?php
                $query = "SELECT * FROM brands";
                $result = mysqli_query($conn, $query);
                $brands = array();
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $brands[] = $row;
                    }
                }
            ?>
            <div class="input-field">
                <label>Thương hiệu<span>*</span></label>
                <select name="brand">
                        
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= $brand['name']; ?>" style="text-transform: capitalize;"><?= $brand['name']; ?></option>
                <?php endforeach; ?>
                    </select>
            </div>
            <div class="input-field">
                <label>Chi tiết sản phẩm<span>*</span></label>
                <textarea name="detail" required></textarea>
            </div>

            <div class="input-field">
                <label>Ảnh sản phẩm<span>*</span></label>
                <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/webp" required>
            </div>
            <input type="submit" name="add_product" value="Thêm sản phẩm" class="btn">
         </form>
    </section>
    <div class="line3"></div>
    <div class="line4"></div>
    <section class="show-products">
        <div class="box-container">
            <?php
                $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
                if (mysqli_num_rows($select_products) > 0) {
                    while ($fetch_products = mysqli_fetch_assoc($select_products)) {
            ?>
            <div class="box">
                <img src="image/<?php echo $fetch_products['image'];?>">
                <p>Giá: <?php echo number_format($fetch_products['price'], 0, '.', '.');?> VND</p>
                
                <h4><?php echo $fetch_products['name'];?></h4>
                <details><?php echo $fetch_products['product_detail'];?></details>
                <a href="admin_product.php?edit=<?php echo $fetch_products['id'];?>" class="edit">Sửa</a>
                <a href="admin_product.php?delete=<?php echo $fetch_products['id'];?>" class="delete" onclick="
                    return confirm('Bạn có chắc muốn xóa sản phẩm này');">Xóa</a>
            </div>
            <?php
                    } 
                }else{
                        echo'
                            <div class="empty">
                                <p>Chưa có sản phẩm được thêm!</p>
                            </div>
                        ';            
                    } 
            ?>

        </div>

    </section>
    


    <div class="line"></div>
    <section class="update-container"> 
        <?php
            if(isset($_GET['edit'])){
                $edit_id = $_GET['edit'];
                $edit_query = mysqli_query($conn, "SELECT * FROM products WHERE id='$edit_id'") or die('query failed');
                if(mysqli_num_rows($edit_query) > 0) {
                    while($fetch_edit = mysqli_fetch_assoc($edit_query)){     
        ?> 
        <form method="POST" enctype="multipart/form-data">
            <img src="image/<?php echo $fetch_edit['image']; ?>">
            <input type="hidden" name="update_id" value="<?php echo $fetch_edit['id']; ?>">
            <input type="text" name="update_name" placeholder="Nhập tên sản phẩm." value="<?php echo $fetch_edit['name']; ?>">
            <input type="number" name="update_price" placeholder="Nhập giá tiền." min="0" value="<?php echo $fetch_edit['price']; ?>">
            <div class="input-field">
                <label>Thương hiệu<span>*</span></label>
                <select name="update_brand">
                        <!-- Lặp qua danh sách thương hiệu để tạo các options -->
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= $brand['name']; ?>" style="text-transform: capitalize;"><?= $brand['name']; ?></option>
                <?php endforeach; ?>
                    </select>
            </div>
            <div class="input-field">
                <label>Danh mục<span>*</span></label>
                <select name="update_category"> 
                    <!-- Lặp qua danh sách danh mục để tạo các options -->
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['name']; ?>" style="text-transform: capitalize;"><?= $category['name']; ?></option>
                        <?php endforeach; ?>
                </select>
                        <!-- <input type="text" name="type" maxlength="100" placeholder="Add product type" require class="box"> -->
            </div>
            <textarea name="update_detail" placeholder="Nhập chi tiết sản phẩm."><?php echo $fetch_edit['product_detail']; ?></textarea>
            <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png, image/webp">
            <input type="submit" name="updte_product" value="Cập nhật" class="edit">
            <!-- <input type="reset" name="" value="Hủy" class="option-btn btn" id="close-form"> -->
            <input type="button" value="Hủy" class="option-btn btn" id="cancel-form">

        </form>     
        <?php
                    }
                }
                echo "<script>document.querySelector('.update-container').style.display='block';</script>";
            } 
        ?>      
    </section>
    <script type="text/javascript">
        document.getElementById('cancel-form').addEventListener('click', function() {
            window.location.href = 'admin_product.php';
        });
    </script>
    <script type="text/javascript" src="script.js"></script>
</body>
</html>