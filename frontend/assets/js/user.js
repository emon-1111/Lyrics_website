// ===============================
// Navbar page switch
// ===============================
const navItems = document.querySelectorAll('.navbar .nav-item[data-page]');

navItems.forEach(item => {
  const page = item.getAttribute('data-page');

  // Highlight active page
  if (page && window.location.href.includes(page)) {
    item.classList.add('active');
  }

  // Navigate to page on click
  item.addEventListener('click', () => {
    if (page) window.location.href = page;
  });
});

// ===============================
// Dropdown menu toggle
// ===============================
const menuBtn = document.querySelector('.navbar .fa-bars');
const dropdownMenu = document.getElementById("dropdownMenu");

// Toggle dropdown menu
menuBtn.addEventListener("click", (e) => {
  e.stopPropagation(); // prevent body click from immediately closing
  dropdownMenu.classList.toggle("active");
});

// Close menu when clicking outside
document.body.addEventListener("click", () => {
  dropdownMenu.classList.remove("active");
});

// Navigate on click
document.querySelectorAll(".dropdown-menu li[data-link]").forEach(item => {
  item.addEventListener("click", (e) => {
    e.stopPropagation(); // prevent flicker
    const link = item.getAttribute("data-link");
    window.location.href = link;
  });
});
