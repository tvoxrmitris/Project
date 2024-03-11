const header = document.querySelector('header');
function fixedNavbar(){
    header.classList.toggle('scrolled', window.scrollY >0)
}

fixedNavbar();
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

// const closeBtn = document.querySelector('#close-form');


// document.getElementById('cancel-form').addEventListener('click', function(event) {
//     event.preventDefault();
//     window.location.href = 'admin_product.php';
// });



closeBtn.addEventListener('click', ()=>{
    document.querySelector('.update-container').style.display='none'
})