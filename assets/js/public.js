document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // 1. ADVANCED DARK MODE TOGGLE
    // ==========================================
    const themeToggle = document.getElementById('theme-toggle');
    const currentTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const newTheme = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Smooth icon transition effect
            themeToggle.style.transform = 'rotate(360deg)';
            setTimeout(() => { themeToggle.style.transform = 'rotate(0deg)'; }, 400);
        });
    }

    // ==========================================
    // 2. ROBUST BILINGUAL SWITCHER
    // ==========================================
    const langToggle = document.getElementById('lang-toggle');
    let currentLang = localStorage.getItem('lang') || 'id';
    
    function applyLanguage(lang) {
        document.querySelectorAll('[data-lang-id]').forEach(el => {
            // Fade out, change text, fade in for smooth transition
            el.style.opacity = '0.5';
            setTimeout(() => {
                el.textContent = lang === 'en' ? el.getAttribute('data-lang-en') : el.getAttribute('data-lang-id');
                el.style.opacity = '1';
            }, 150);
        });
        localStorage.setItem('lang', lang);
        
        // Update URL without reloading
        const url = new URL(window.location);
        url.searchParams.set('lang', lang);
        window.history.pushState({}, '', url);
    }
    
    // Apply initial language
    applyLanguage(currentLang);
    if (langToggle) langToggle.textContent = currentLang === 'id' ? 'EN' : 'ID';
    
    if (langToggle) {
        langToggle.addEventListener('click', () => {
            currentLang = currentLang === 'id' ? 'en' : 'id';
            applyLanguage(currentLang);
            langToggle.textContent = currentLang === 'id' ? 'EN' : 'ID';
        });
    }

    // ==========================================
    // 3. DYNAMIC NAVBAR SCROLL EFFECT
    // ==========================================
    const navbar = document.querySelector('.navbar');
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        lastScroll = currentScroll;
    }, { passive: true }); // Passive listener for better scroll performance

    // ==========================================
    // 4. MOBILE MENU TOGGLE
    // ==========================================
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            mobileToggle.textContent = navLinks.classList.contains('active') ? '✕' : '☰';
        });
        
        // Close menu when clicking a link
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                mobileToggle.textContent = '☰';
            });
        });
    }

    // ==========================================
    // 5. STAGGERED SCROLL REVEAL ANIMATION (Waterfall Effect)
    // ==========================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Find all sibling elements to apply staggered delay
                const parent = entry.target.parentElement;
                const siblings = Array.from(parent.children).filter(child => child.classList.contains('prodi-card') || child.classList.contains('step-card'));
                const index = siblings.indexOf(entry.target);
                
                // Apply staggered delay (max 0.4s)
                const delay = Math.min(index * 0.1, 0.4);
                entry.target.style.transitionDelay = `${delay}s`;
                
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                
                observer.unobserve(entry.target); // Unobserve after animation for performance
            }
        });
    }, observerOptions);

    // Initialize elements for animation
    document.querySelectorAll('.prodi-card, .step-card, .search-widget').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(40px)';
        el.style.transition = 'opacity 0.7s cubic-bezier(0.25, 0.8, 0.25, 1), transform 0.7s cubic-bezier(0.25, 0.8, 0.25, 1)';
        observer.observe(el);
    });

    // ==========================================
    // 6. ADVANCED FILTER LOGIC (With Reset Animation)
    // ==========================================
    window.filterProdi = function() {
        const jenjang = document.getElementById('filter-jenjang')?.value || '';
        const prodiId = document.getElementById('filter-prodi')?.value || '';
        const cards = document.querySelectorAll('.prodi-card');
        
        cards.forEach((card, index) => {
            const matchJenjang = !jenjang || card.dataset.jenjang === jenjang;
            const matchProdi = !prodiId || card.querySelector('a')?.href.includes(`prodi=${prodiId}`);
            
            if (matchJenjang && matchProdi) {
                card.style.display = 'flex';
                // Re-trigger animation
                setTimeout(() => {
                    card.style.transitionDelay = `${index * 0.05}s`;
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50);
            } else {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => { card.style.display = 'none'; }, 400); // Wait for transition
            }
        });
    };
});