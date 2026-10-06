/**
 * ORBITAL ARCHIVE - Navigation & Scroll Controller
 */

class NavigationController {
  constructor() {
    this.header = document.querySelector('.orbital-header');
    this.navItems = document.querySelectorAll('.nav-item');
    this.sections = document.querySelectorAll('section[id]');

    this.init();
  }

  init() {
    // Header scroll background effect
    window.addEventListener('scroll', () => {
      if (window.scrollY > 40) {
        this.header?.classList.add('scrolled');
      } else {
        this.header?.classList.remove('scrolled');
      }
    });

    // Smooth scroll for nav items
    this.navItems.forEach(item => {
      item.addEventListener('click', (e) => {
        const href = item.getAttribute('href');
        if (href && href.startsWith('#')) {
          e.preventDefault();
          const target = document.querySelector(href);
          if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
          }
        }
      });
    });

    // IntersectionObserver for active section pill indicator
    const observerOptions = {
      root: null,
      rootMargin: '-30% 0px -50% 0px',
      threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.getAttribute('id');
          this.navItems.forEach(item => {
            if (item.getAttribute('href') === `#${id}`) {
              item.classList.add('active');
            } else {
              item.classList.remove('active');
            }
          });
        }
      });
    }, observerOptions);

    this.sections.forEach(sec => observer.observe(sec));
  }
}

window.addEventListener('DOMContentLoaded', () => {
  new NavigationController();
});
