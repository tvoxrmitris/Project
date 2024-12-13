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
    <link rel="shortcut icon" href="../image/seraphh.png" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css"
        integrity="sha512-yHknP1/AwR+yx26cB1y0cjvQUMvEa2PFzt1c9LlS4pRQ5NOTZFWbhBig+X9G9eYW/8m0/4OXNx8pxJ6z57x0dw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"
        integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.js"
        integrity="sha512-eP8DK17a+MOcKHXC5Yrqzd8WI5WKh6F1TIk5QZ/8Lbv+8ssblcz7oGC8ZmQ/ZSAPa7ZmsCU4e/hcovqR8jfJqA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <title>t</title>
    <style>
    .contact-box {
        position: fixed;
        top: 50%;
        right: -500px;
        width: 500px;
        height: 100%;
        padding: 25px;
        background-color: #fff;
        color: #000;
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.5);
        transform: translateY(-50%);
        opacity: 0;
        transition: right 0.5s ease, opacity 0.5s ease;
        z-index: 1000;
    }

    .contact-box.open {
        right: 0;
        opacity: 1;
    }

    .contact-box h1 {
        font-size: 26px;
        font-weight: bold;
        color: #000;
        font-family: "Times New Roman", serif;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 8rem
    }

    .contact-details,
    .email-details {
        margin-bottom: 25px;
        border-bottom: 1px solid #000;
        padding-bottom: 15px;
        font-weight: bold;
    }

    .contact-details span,
    .email-details span {
        display: block;
        font-size: 18px;
        font-family: "Times New Roman", serif;
        font-weight: bold;
        color: #000;
        margin-bottom: 8px;
        text-decoration: underline;
        text-underline-offset: 6px;
    }

    .contact-details p,
    .email-details p {
        font-size: 14px;
        color: #999;
        font-style: italic;
        margin: 0;
    }

    .close-button {
        position: absolute;
        top: 0;
        right: -225px;
        background: none;
        border: none;
        color: #000;
        font-size: 24px;
        cursor: pointer;
        transition: color 0.3s ease;
        z-index: 1001;
        border-radius: 50%;
    }

    .close-button:hover {
        color: #d4af37;
        background-color: rgba(255, 255, 255, 0.1);
    }

    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        /* Tăng độ mờ ở đây */
        opacity: 0;
        transition: opacity 0.5s ease;
        z-index: 999;
        pointer-events: none;
    }

    .overlay.active {
        opacity: 1;
        pointer-events: auto;
    }


    .legacy-box {
        position: fixed;
        top: 50%;
        right: -500px;
        width: 500px;
        height: 100%;
        padding: 30px;
        background-color: #f8f8f8;
        /* Màu nền sáng */
        color: #333;
        /* Màu chữ tối hơn */
        box-shadow: 0px 4px 30px rgba(0, 0, 0, 0.2);
        /* Đổ bóng tinh tế */

        /* Bo tròn các góc */
        transform: translateY(-50%);
        opacity: 0;
        transition: right 0.5s ease, opacity 0.5s ease;
        z-index: 1000;
        font-family: 'Times New Roman', serif;
        /* Phông chữ sang trọng */
        line-height: 1.6;
        /* Khoảng cách dòng tốt hơn */
    }

    .legacy-box h1 {
        font-size: 24px;
        /* Kích thước tiêu đề */
        font-weight: bold;
        /* In đậm */
        color: #000;
        /* Màu chữ đen */
        text-transform: uppercase;
        /* Chữ hoa */
        letter-spacing: 1px;
        /* Khoảng cách chữ */
        margin-bottom: 20px;
        /* Khoảng cách dưới tiêu đề */
    }

    .legacy-box span {
        display: block;
        /* Hiển thị như khối */
        font-size: 16px;
        /* Kích thước chữ thân bài */
        color: #555;
        /* Màu chữ nhạt hơn */
        margin-bottom: 20px;
        /* Khoảng cách dưới đoạn văn */
    }

    .legacy-box.open {
        right: 0;
        opacity: 1;
    }

    .shipping-box {
        position: fixed;
        top: 50%;
        right: -500px;
        width: 550px;
        height: 100%;
        padding: 30px;
        background-color: #ffffff;
        color: #333;
        box-shadow: 0px 4px 30px rgba(0, 0, 0, 0.2);
        transform: translateY(-50%);
        opacity: 0;
        transition: right 0.5s ease, opacity 0.5s ease;
        z-index: 1000;
        font-family: 'Times New Roman', serif;
        line-height: 1.6;
        overflow-y: scroll;
        /* Cho phép cuộn dọc */
    }

    /* Ẩn thanh cuộn cho trình duyệt WebKit (Chrome, Safari, v.v.) */
    .shipping-box::-webkit-scrollbar {
        display: none;
        /* Ẩn thanh cuộn */
    }

    /* Ẩn thanh cuộn cho trình duyệt Firefox */
    .shipping-box {
        scrollbar-width: none;
        /* Ẩn thanh cuộn */
    }


    .shipping-box h1 {
        font-size: 24px;
        font-weight: bold;
        color: #111;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
        border-bottom: 2px solid #333;
        /* Đường gạch chân màu đen */
        padding-bottom: 8px;
    }

    .shipping-box span {
        display: block;
        font-size: 16px;
        color: #666;
        margin-bottom: 20px;
        font-family: 'Georgia', serif;
        /* Font chữ cổ điển */
    }

    .shipping-box.open {
        right: 0;
        opacity: 1;
    }

    .shipping-box p {
        font-size: 16px;
        color: #333;
        margin-bottom: 15px;
        font-style: italic;
    }

    .shipping-box table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Arial', sans-serif;
        margin-top: 20px;
    }

    .shipping-box th,
    .shipping-box td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .shipping-box th {
        background-color: #333;
        color: #fff;
        font-weight: bold;
        font-size: 16px;
    }

    .shipping-box tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .shipping-box tr:hover {
        background-color: #eaeaea;
    }

    .shipping-box::-webkit-scrollbar {
        width: 8px;
    }

    .shipping-box::-webkit-scrollbar-thumb {
        background-color: #333;
        /* Thanh cuộn màu xám đen */
        border-radius: 10px;
    }
    </style>
</head>

<body>
    <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
    <df-messenger chat-title="Seraph Beauty" agent-id="9b3c9d53-e2a3-42da-a61c-e036c32c8aa2" language-code="en"
        chat-icon="../image/seraphh.png">
    </df-messenger>

    <style>
    df-messenger {
        --df-messenger-bot-message: #ffffff !important;
        --df-messenger-button-titlebar-color: #fff !important;
        --df-messenger-chat-background-color: #f9f9f9 !important;
        --df-messenger-font-color: #000000 !important;
        --df-messenger-send-icon: #000000 !important;
        --df-messenger-bot-message-font-color: #000000 !important;
    }

    df-messenger .df-bot-message {
        background-color: #ffffff !important;
        color: #222222 !important;
        border: 2px solid #ddd !important;
        border-left: 5px solid #009688 !important;
        border-radius: 15px !important;
        font-family: 'Georgia', serif !important;
        padding: 10px 15px !important;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1) !important;
    }

    df-messenger .df-user-message {
        background-color: #f0f0f0 !important;
        color: #222222 !important;
        border: 2px solid #ccc !important;
        border-right: 5px solid #000 !important;
        border-radius: 15px !important;
        padding: 10px 15px !important;
        font-family: 'Arial', sans-serif !important;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1) !important;
    }

    df-messenger .df-messenger-titlebar {
        font-family: 'Georgia', serif !important;
        font-size: 20px !important;
        font-weight: bold !important;
        color: #fff !important;
        background: linear-gradient(45deg, #000, #222) !important;
        border-bottom: 3px solid #009688 !important;
        text-align: center !important;
        padding: 10px !important;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2) !important;
    }

    df-messenger .df-chat-wrapper {
        box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.3) !important;
        border-radius: 12px !important;
        border: 2px solid #ddd !important;
        overflow: hidden !important;
    }
    </style>




    <div class="overlay" id="overlay"></div>

    <div class="line4"></div>

    <div class="contact-box" id="contactBox">
        <h1>Liên hệ với chúng tôi</h1>

        <div class="contact-details">
            <div style="display: flex; align-items: center;">
                <img src="../image/icons/phone.png" alt=""
                    style="margin-right: 8px; width: 16px; margin-bottom: 0.3rem;">
                <span>Gọi cho chúng tôi: 0922 222 2222</span>
            </div>

            <p>Thứ 2 đến thứ 7 từ 8am đến 11pm</p>
            <p>Chủ nhật từ 9am đến 10pm</p>
        </div>

        <div class="email-details">
            <div style="display: flex; align-items: center;">
                <img src="../image/icons/email.png" alt=""
                    style="margin-right: 8px; width: 16px; margin-bottom: 0.5rem;">
                <span>Địa chỉ email: seraphbeauty22@gmail.com</span>
            </div>

            <p>Thứ 2 đến thứ 7 từ 8am đến 11pm</p>
            <p>Chủ nhật từ 9am đến 10pm</p>
        </div>
        <div style="text-align: center; margin-top: 8rem;">
            <img style="width: 60%;" src="../image/seraph.png" alt="">
        </div>

        <button id="closeContactBox" class="close-button">X</button>
    </div>

    <div class="legacy-box" id="legacyBox">
        <h1>Chính sách</h1>
        <span>Seraph Beauty quan tâm đến quyền riêng tư và bảo vệ dữ liệu của bạn. Chúng tôi cam kết cung cấp tiêu chuẩn
            cao
            nhất về sản phẩm và dịch vụ. Do đó, chúng tôi coi trọng từng khách hàng hiện tại hoặc tiềm năng và đặt mục
            tiêu duy trì việc bảo vệ thích hợp đối với dữ liệu cá nhân/thông tin cá nhân của bạn.<br><br>

            Chính sách Bảo mật của Seraph Beauty giải thích cách Seraph Beauty thu thập và xử lý Dữ liệu của bạn khi bạn
            sử dụng các
            trang web, ứng dụng hoặc trải nghiệm kỹ thuật số khác (“Dịch vụ Kỹ thuật số”), khi bạn mua sản phẩm của
            Seraph Beauty thông qua bất kỳ Dịch vụ Kỹ thuật số nào hoặc thông qua các điểm bán hàng của chúng tôi, khi
            bạn đến
            thăm các điểm bán hàng của chúng tôi, khi bạn điền và nộp một trong các Thẻ Boutique của chúng tôi, khi bạn
            ứng tuyển vào vị trí tại Seraph Beauty hoặc khi bạn tương tác với hoặc được hiển thị nội dung về Seraph
            Beauty, như yêu
            cầu của các luật bảo vệ dữ liệu ở các vùng lãnh thổ mà chúng tôi hoạt động.<br><br>

            Các sửa đổi cụ thể theo địa phương được đề cập bên dưới cũng áp dụng cho người tiêu dùng ở những vùng lãnh
            thổ đó và trong một số trường hợp có thể quy định các tiêu chuẩn khác nhau do luật, quy định và quy tắc địa
            phương áp dụng. Nếu có sự mâu thuẫn, sửa đổi cụ thể theo địa phương sẽ được ưu tiên áp dụng. Để tuân thủ các
            luật về quyền riêng tư dữ liệu tại các tiểu bang của Việt Nam, để biết thông tin về việc Seraph Beauty thu
            thập và xử
            lý dữ liệu từ cư dân tại Hoa Kỳ, vui lòng nhấn vào đây.</span>
        <button id="closeLegacyBox" class="close-button">X</button>
    </div>

    <div class="shipping-box" id="shippingBox">
        <h1>MIỄN PHÍ VẬN CHUYỂN</h1>
        <span>Dưới đây là các ưu đãi vận chuyển miễn phí trên seraphbeauty.com.</span>
        <button id="closeShippingBox" class="close-button">X</button>
        <h1>NỘI ĐỊA</h1>
        <p>• MIỄN PHÍ vận chuyển tiêu chuẩn cho tất cả các đơn hàng ở Việt Nam đối với các khách hàng
            có tài khoản seraphbeauty.com đang hoạt động (vui lòng cho phép từ 1-2 ngày làm việc để xử lý và vận chuyển
            đơn hàng của bạn). Lợi ích này không áp dụng cho các đơn hàng đã được đặt trước khi có tài khoản.</p>
        <p>• MIỄN PHÍ vận chuyển tiêu chuẩn cho tất cả các đơn hàng ở Việt Nam với giá trị từ 1 triệu trở
            lên cho khách hàng không có tài khoản seraphbeauty.com.</p>
        <h1>QUỐC TẾ</h1>
        <p>MIỄN PHÍ vận chuyển quốc tế tiêu chuẩn cho tất cả các đơn hàng có giá trị từ 2 triệu trở lên.</p>
        <h1>CHI PHÍ VẬN CUYỂN + THỜI GIAN GIAO HÀNG</h1>
        <p>Đơn hàng phải được đặt trước 3 giờ triều theo giờ Việt Nam để bắt đầu xử lý trong cùng ngày. Thời gian xử lý
            thường
            mất 1-2 ngày làm việc. Thời gian giao hàng được tính dựa trên các đơn hàng đặt từ thứ Hai đến thứ Sáu.</p>
        <table>
            <thead>
                <tr>
                    <th>Phương Thức Giao Hàng</th>
                    <th>Chi Phí</th>
                    <th>Tổng Thời Gian Giao Hàng (bao gồm thời gian xử lý)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Giao Hàng Tiêu Chuẩn (Dành Cho Tài Khoản Đăng Ký)</td>
                    <td>MIỄN PHÍ</td>
                    <td>4-8 ngày làm việc (tối đa 21 ngày)</td>
                </tr>
                <tr>
                    <td>Giao Hàng Tiêu Chuẩn (Không Phải Tài Khoản Đăng Ký)</td>
                    <td>50.000 VND</td>
                    <td>4-8 ngày làm việc (tối đa 21 ngày)</td>
                </tr>
                <tr>
                    <td>Giao Hàng Nhanh Trong 2 Ngày</td>
                    <td>70.000 VND</td>
                    <td>3-4 ngày làm việc</td>
                </tr>
                <tr>
                    <td>Giao Hàng Nhanh Trong 1 Ngày</td>
                    <td>110.000 VND</td>
                    <td>2-3 ngày làm việc</td>
                </tr>
            </tbody>
        </table>

    </div>

    <footer>
        <div class="inner-footer">
            <div class="card">
                <h3>Chúng tôi có thể giúp gì cho bạn?</h3>
                <ul>
                    <li id="contactLi">Liên hệ</li>
                    <!-- <li onclick="location.href='../components/login.php';" style="cursor: pointer;">Đơn hàng</li> -->
                    <li onclick="openLegacyBox();" style="cursor: pointer;">Chính sách</li>
                    <li id="shippingLi" style="cursor: pointer;">Thời gian giao hàng</li>
                    <li><a style="color: #fff;" href="guest_collection.php?collection=Arcane%20Collection">Bộ sưu tập
                            mới</a></li>

                </ul>
            </div>

            <div class="card">
                <h3>Khám phá Seraph</h3>
                <ul>
                    <li><a style="color: #fff;" href="view_makeupface.php">Trang điểm mặt</a></li>

                    <li><a style="color: #fff;" href="view_eyemakeup.php">Trang điểm mắt</a></li>
                    <li><a style="color: #fff;" href="bodycare.php">Chăm sóc da</a></li>
                    <li><a style="color: #fff;" href="haircare.php">Chăm sóc tóc</a></li>

                </ul>
            </div>
            <div id="modalOverlay" style="display: none;"></div>
            <div class="card">
                <h3>Bản tin</h3>
                <p>Đăng ký để nhận nhiều ưu đãi và ưu đãi độc quyền</p>
                <div class="input-field">
                    <input type="email" id="emailInput" placeholder="Địa chỉ email..." required>
                    <i class="bi bi-envelope" id="submitEmail"></i>
                    <div id="errorMessage" style="display: none; color: red;">Vui lòng nhập địa chỉ email hợp lệ.</div>
                </div>
                <!-- <div class="social-links">
                    <i class="bi bi-instagram"></i>
                    <i class="bi bi-facebook"></i>
                    <i class="bi bi-spotify"></i>
                    <i class="bi bi-youtube"></i>
                    <i class="bi bi-twitter"></i>
                    <i class="bi bi-snapchat"></i>
                </div> -->

                <div id="emailBox">
                    <button id="closeEmailBox">&times;</button>
                    <h2>Đăng ký để nhận thông tin cập nhật và nội dung độc quyền</h2>

                    <form id="newsletterForm" method="POST">
                        <label for="email">*E-mail</label>
                        <input type="email" id="email" name="email" required>

                        <label for="firstName">*Họ và tên</label>
                        <input type="text" id="firstName" name="name" required>

                        <button type="button" id="submitFormButton">Đăng ký</button>
                    </form>

                    <p class="privacy-note" style="color: #666; font-size: 13px;">
                        Seraph Beauty cam kết tôn trọng quyền riêng tư của khách hàng. Dữ liệu cá nhân của bạn sẽ được
                        sử dụng để gửi thông tin về ưu đãi, tin tức...
                        <span id="moreText" style="display: none;">
                            và sự kiện của Seraph Beauty, nhằm quản lý mối quan hệ khách hàng và thương mại. Để biết
                            thêm thông tin về việc xử lý dữ liệu cá nhân của bạn, vui lòng tham khảo Tuyên bố Bảo mật
                            của chúng tôi.
                        </span>
                        <button id="togglePrivacy"
                            style="border: none; background: none; color: #000; cursor: pointer;">Xem thêm</button>
                    </p>
                </div>
            </div>

            <!-- CSS -->
            <style>
            /* Lớp phủ mờ */
            #modalOverlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            /* Hộp email đăng ký */
            #emailBox {
                display: none;
                position: fixed;
                top: 95%;
                left: 50%;
                transform: translate(-50%, -100%);
                width: 40%;
                max-width: 600px;
                height: 90vh;
                background-color: #fff;
                box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.5);
                padding: 30px;
                overflow-y: auto;
                overflow-x: hidden;
                z-index: 1000;
                border-radius: 10px;
                opacity: 0;
                transition: transform 0.5s ease, opacity 0.5s ease;
            }

            /* Hiệu ứng xuất hiện và biến mất */
            .fade-in {
                display: block !important;
                opacity: 1 !important;
                transform: translate(-50%, -50%);
            }

            .fade-out {
                opacity: 0 !important;
                transform: translate(-50%, 100%);
                transition: transform 0.5s ease, opacity 0.5s ease;
            }

            #emailBox h2 {
                text-align: center;
                margin-bottom: 20px;
                color: #000;
                font-family: 'Arial', sans-serif;
                font-size: 1.5em;
            }

            #emailBox form {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            #emailBox label {
                font-size: 1em;
                text-transform: uppercase;
                color: #333;
                font-weight: bold;
            }

            #emailBox input[type="email"],
            #emailBox input[type="text"] {
                width: 100%;
                padding: 12px;
                font-size: 1em;
                border: 1px solid #000;
                background-color: #f9f9f9;
                color: #000;
                border-radius: 5px;
            }

            #emailBox button[type="submit"] {
                width: 100%;
                padding: 14px;
                font-size: 1em;
                color: #fff;
                background-color: #000;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            #emailBox button[type="submit"]:hover {
                background-color: #333;
            }

            .privacy-note {
                font-size: 0.8em;
                color: #555;
                text-align: center;
                margin-top: 20px;
            }

            #closeEmailBox {
                position: absolute;
                top: -20px;
                left: 269px;
                font-size: 17px;
                background: none;
                border: none;
                cursor: pointer;
                color: #000;
                transition: color 0.3s;
            }

            #closeEmailBox:hover {
                color: #333;
            }
            </style>

            <!-- JavaScript -->
            <script>
            document.getElementById("submitEmail").addEventListener("click", function() {
                const emailInput = document.getElementById("emailInput");
                if (emailInput.checkValidity()) {
                    // Hiển thị overlay và emailBox
                    document.getElementById("modalOverlay").style.display = "block";
                    const emailBox = document.getElementById("emailBox");
                    emailBox.classList.remove("fade-out");
                    emailBox.classList.add("fade-in");
                    emailBox.style.display = "block";

                    // Điền giá trị từ emailInput vào trường email trong emailBox
                    document.getElementById("email").value = emailInput.value;

                    setTimeout(() => {
                        emailBox.style.opacity = 1;
                    }, 10);
                } else {
                    alert("Vui lòng nhập địa chỉ email hợp lệ.");
                }
            });


            document.getElementById("submitFormButton").addEventListener("click", function() {
                const emailInput = document.getElementById("email");
                const nameInput = document.getElementById("firstName");

                if (emailInput.checkValidity() && nameInput.checkValidity()) {
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "subscribe.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            alert(xhr.responseText);
                            document.getElementById("newsletterForm").reset();
                        }
                    };

                    xhr.send(
                        `email=${encodeURIComponent(emailInput.value)}&name=${encodeURIComponent(nameInput.value)}`
                    );
                } else {
                    alert("Vui lòng nhập thông tin hợp lệ.");
                }
            });

            document.getElementById("closeEmailBox").addEventListener("click", function() {
                const emailBox = document.getElementById("emailBox");
                emailBox.classList.add("fade-out");
                emailBox.classList.remove("fade-in");
                setTimeout(() => {
                    emailBox.style.display = "none";
                    document.getElementById("modalOverlay").style.display = "none";
                }, 500);
            });
            </script>



    </footer>
    <script>
    $(document).ready(function() {
        // Hiển thị hoặc ẩn contactBox khi nhấn vào Liên hệ
        $("#contactLi").click(function() {
            $("#contactBox").toggleClass("open");
            $("#overlay").toggleClass("active", $("#contactBox").hasClass("open"));
        });

        // Đóng contactBox khi nhấn vào nút "X"
        $("#closeContactBox").click(function() {
            $("#contactBox").removeClass("open");
            $("#overlay").removeClass("active");
        });

        // Hiển thị legacyBox khi nhấn vào Chính sách
        window.openLegacyBox = function() {
            $("#legacyBox").addClass("open");
            $("#overlay").addClass("active");
        };

        // Đóng legacyBox khi nhấn vào nút "X"
        $("#closeLegacyBox").click(function() {
            $("#legacyBox").removeClass("open");
            $("#overlay").removeClass("active");
        });

        // Hiển thị shippingBox khi nhấn vào Thời gian giao hàng
        $("#shippingLi").click(function() {
            $("#shippingBox").addClass("open");
            $("#overlay").addClass("active");
        });

        // Đóng shippingBox khi nhấn vào nút "X"
        $("#closeShippingBox").click(function() {
            $("#shippingBox").removeClass("open");
            $("#overlay").removeClass("active");
        });

        // Đóng overlay khi nhấn vào overlay
        $("#overlay").click(function() {
            $(".contact-box, .legacy-box, .shipping-box").removeClass("open");
            $(this).removeClass("active");
        });
    });
    </script>
</body>

</html>