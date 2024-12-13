// $('.testimonial-slider').slick({
//     autoplay: true,
//     infinite: false,
//     speed: 200,
//     nextArrow: $('.next'),
//     nextArrow: $('.prev')
// })

// $('.testimonial-slider').slick({
//     autoplay: true,
//     infinite: false,
//     speed: 200,
//     nextArrow: $('.next1'),
//     nextArrow: $('.prev1'),
// })


// $('.testimonial-slider').slick({
//     dots: true,
//     infinite: false,
//     speed: 300,
//     slidesToShow: 4,
//     slidesToScroll: 4,
//     responsive: [
//       {
//         breakpoint: 1024,
//         settings: {
//           slidesToShow: 3,
//           slidesToScroll: 3,
//           infinite: true,
//           dots: true
//         }
//       },
//       {
//         breakpoint: 600,
//         settings: {
//           slidesToShow: 2,
//           slidesToScroll: 2
//         }
//       },
//       {
//         breakpoint: 480,
//         settings: {
//           slidesToShow: 1,
//           slidesToScroll: 1
//         }
//       }
//       // You can unslick at a given breakpoint now by adding:
//       // settings: "unslick"
//       // instead of a settings object
//     ]
// });




//                  home slide
const imgBox = document.querySelector('.slider-container');
const slides = document.getElementsByClassName('slideBox');
var i = 0;
function nextSlide() {
    slides[i].classList.remove('active');
    i = (i + 1) % slides.length;
    slides[i].classList.add('active');
}

setInterval(nextSlide, 3000);

function prevSlide() {
    slides[i].classList.remove('active');
    i = (i - 1 + slides.length) % slides.length;
    slides[i].classList.add('active');
}

const header = document.querySelector('header');
function fixedNavbar(){
    header.classList.toggle('scrolled', window.scrollY >0)
}

// fixedNavbar();
window.addEventListener('scroll', fixedNavbar);

const menu = document.querySelector('#menu-btn');
let userBtn = document.querySelector('#user-btn');
console.log(menu)


menu.addEventListener('click', function(){
    let nav = document.querySelector('.navbar');
    nav.classList.toggle('active');
})

userBtn.addEventListener('click', function(){
    let userBox = document.querySelector('.user-box');
    userBox.classList.toggle('active');
})

const closeBtn = document.querySelector('#close-form');


// document.getElementById('cancel-form').addEventListener('click', function(event) {
//     event.preventDefault();
//     window.location.href = 'admin_product.php';
// })



closeBtn.addEventListener('click', ()=>{
    document.querySelector('.update-container').style.display='none'
});

function searchProducts() {
    // Lấy giá trị từ ô nhập liệu
    const searchValue = document.getElementById("search-input").value;
  
    // Tạo truy vấn SQL
    const sql = `SELECT * FROM products WHERE name LIKE '%${searchValue}%'`;
  
    // Thực thi truy vấn
    const products = axios.get(sql);
  
    // Xử lý kết quả truy vấn
    products.then((response) => {
      // Hiển thị kết quả tìm kiếm
      const productsList = document.querySelector(".products-list");
      productsList.innerHTML = "";
  
      for (const product of response.data) {
        const productItem = document.createElement("li");
        productItem.innerHTML = `
          <a href="/product/${product.id}">
            ${product.name}
          </a>
        `;
        productsList.appendChild(productItem);
      }
    });
  }
  
  // Gán sự kiện cho nút submit
  document.querySelector(".search-box button").addEventListener("click", searchProducts);

  const clearInput = () => {
    const input = document.getElementsByTagName("input")[0];
    input.value = "";
  }
  
  const clearBtn = document.getElementById("clear-btn");
  clearBtn.addEventListener("click", clearInput);

  $(document).ready(function() {
    $('#searchBox').keyup(function() {
        var searchText = $(this).val().trim();
        if (searchText != '') {
            $.ajax({
                url: 'search.php',
                type: 'GET',
                data: { search: searchText },
                success: function(response) {
                    $('.search-result').html(response);
                }
            });
        } else {
            $('.search-result').html('');
        }
    });
});


$(document).ready(function() {
  $('#province').change(function() {
      var provinceID = $(this).val();
      if(provinceID) {
          $.ajax({
              type: 'GET',
              url: '../user/ajax_get_district.php', // Đường dẫn tới tệp xử lý AJAX
              data: { province_id: provinceID },
              dataType: 'json', // Chỉ định kiểu dữ liệu trả về từ server là JSON
              success: function(response) {
                  $('#district').empty(); // Xóa tất cả các option cũ trong dropdown của quận/huyện
                  $.each(response, function(key, value) {
                      $('#district').append('<option value="' + value.id + '">' + value.name + '</option>');
                  });
              }
          });
      } else {
          $('#district').empty(); // Nếu không có tỉnh/thành phố được chọn, xóa tất cả các option của dropdown quận/huyện
          $('#district').append('<option value="">Chọn một quận/huyện</option>');
      }
  });
});









