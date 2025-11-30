// Add any interactivity if needed
// Example: Smooth scrolling for nav links
const links = document.querySelectorAll('.nav-links li a');

for (const link of links) {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const target = document.querySelector(link.getAttribute('href'));
        target.scrollIntoView({ behavior: 'smooth' });
    });
}
