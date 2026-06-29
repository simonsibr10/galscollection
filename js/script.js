let navbar = document.querySelector('.header .flex .navbar');
let profile = document.querySelector('.header .flex .profile');

let menuBtn = document.querySelector('#menu-btn');
let userBtn = document.querySelector('#user-btn');

if(menuBtn){
   menuBtn.onclick = (e) =>{
      e.stopPropagation();
      navbar.classList.toggle('active');
      profile.classList.remove('active');
   }
}

if(userBtn){
   userBtn.onclick = (e) =>{
      e.stopPropagation();
      profile.classList.toggle('active');
      navbar.classList.remove('active');
   }
}

if(profile){
   profile.onclick = (e) =>{
      e.stopPropagation();
   }
}

document.addEventListener('click', () =>{
   if(navbar) navbar.classList.remove('active');
   if(profile) profile.classList.remove('active');
});

window.onscroll = () =>{
   if(navbar) navbar.classList.remove('active');
   if(profile) profile.classList.remove('active');
};

let mainImage = document.querySelector('.quick-view .box .row .image-container .main-image img');
let subImages = document.querySelectorAll('.quick-view .box .row .image-container .sub-image img');

if(mainImage && subImages.length > 0){
   subImages.forEach(images =>{
      images.onclick = () =>{
         let src = images.getAttribute('src');
         mainImage.src = src;
      }
   });
}