function showPage(id) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}

function toggleMenu() {
    const menu = document.getElementById("menuDropdown");
    menu.classList.toggle("show");
}

// Optional: click outside to close menu
window.addEventListener('click', function(e) {
    const menu = document.getElementById("menuDropdown");
    const menuBtn = document.querySelector('.menu');
    if (!menu.contains(e.target) && !menuBtn.contains(e.target)) {
        menu.classList.remove("show");
    }
});
