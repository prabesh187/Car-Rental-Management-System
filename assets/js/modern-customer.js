/**
 * Modern Customer Interface JavaScript
 * Handles theme switching, animations, and enhanced user interactions
 */

class ModernCustomer {
    constructor() {
        this.init();
    }

    init() {
        this.setupThemeToggle();
        this.setupScrollEffects();
        this.setupAnimations();
        this.setupFormEnhancements();
        this.setupCarCards();
        this.loadSavedTheme();
        this.setupMobileMenu();
        this.setupSearchEnhancements();
    }

    // Theme Management
    setupThemeToggle() {
        // Create theme toggle button if it doesn't exist
        if (!document.querySelector('.theme-toggle')) {
            this.createThemeToggle();
        }

        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }
    }

    createThemeToggle() {
        const themeToggle = document.createElement('div');
        themeToggle.className = 'theme-toggle';
        themeToggle.innerHTML = `
            <i class="fa fa-moon-o" id="theme-icon"></i>
            <span id="theme-text">Dark</span>
        `;
        document.body.appendChild(themeToggle);
    }

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('customer-theme', newTheme);
        
        this.updateThemeToggleUI(newTheme);
        this.animateThemeTransition();
    }

    updateThemeToggleUI(theme) {
        const themeIcon = document.getElementById('theme-icon');
        const themeText = document.getElementById('theme-text');
        
        if (themeIcon && themeText) {
            if (theme === 'dark') {
                themeIcon.className = 'fa fa-sun-o';
                themeText.textContent = 'Light';
            } else {
                themeIcon.className = 'fa fa-moon-o';
                themeText.textContent = 'Dark';
            }
        }
    }

    loadSavedTheme() {
        const savedTheme = localStorage.getItem('customer-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        this.updateThemeToggleUI(savedTheme);
    }

    animateThemeTransition() {
        document.body.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 300);
    }

    // Scroll Effects
    setupScrollEffects() {
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar-custom');
            if (navbar) {
                if (window.scrollY > 50) {
                    navbar.style.background = 'var(--nav-bg)';
                    navbar.style.backdropFilter = 'blur(10px)';
                    navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
                } else {
                    navbar.style.background = 'var(--nav-bg)';
                    navbar.style.boxShadow = 'none';
                }
            }
        });

        // Parallax effect for hero section
        window.addEventListener('scroll', () => {
            const heroSection = document.querySelector('.hero-section');
            if (heroSection) {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.5;
                heroSection.style.transform = `translateY(${rate}px)`;
            }
        });

        // Reveal animations on scroll
        this.setupScrollReveal();
    }

    setupScrollReveal() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe elements for animation
        const elementsToAnimate = document.querySelectorAll('.modern-card, .car-card, .feature-card');
        elementsToAnimate.forEach(el => {
            observer.observe(el);
        });
    }

    // Animation Setup
    setupAnimations() {
        // Stagger animation for cards
        const cards = document.querySelectorAll('.car-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });

        // Hover effects for interactive elements
        this.setupHoverEffects();

        // Loading animations
        this.setupLoadingAnimations();
    }

    setupHoverEffects() {
        // Enhanced button hover effects
        const buttons = document.querySelectorAll('.btn-modern');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.02)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Card tilt effect
        const cards = document.querySelectorAll('.modern-card, .car-card');
        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 10;
                const rotateY = (centerX - x) / 10;
                
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
            });
        });
    }

    setupLoadingAnimations() {
        // Show loading spinner for form submissions
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', () => {
                this.showLoadingOverlay();
            });
        });
    }

    // Form Enhancements
    setupFormEnhancements() {
        // Enhanced form validation
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            // Add modern styling
            if (!input.classList.contains('form-control-modern')) {
                input.classList.add('form-control-modern');
            }

            // Real-time validation
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));

            // Floating label effect
            this.setupFloatingLabels(input);
        });

        // Form submission enhancement
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    this.showNotification('Please fix the errors in the form', 'error');
                }
            });
        });
    }

    setupFloatingLabels(input) {
        const label = input.previousElementSibling;
        if (label && label.tagName === 'LABEL') {
            label.classList.add('form-label-modern');
            
            input.addEventListener('focus', () => {
                label.style.transform = 'translateY(-25px) scale(0.8)';
                label.style.color = 'var(--primary-color)';
            });
            
            input.addEventListener('blur', () => {
                if (!input.value) {
                    label.style.transform = 'translateY(0) scale(1)';
                    label.style.color = 'var(--text-secondary)';
                }
            });
        }
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Clear existing errors
        this.clearFieldError(field);

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        }

        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }

        // Phone validation
        if (field.type === 'tel' && value) {
            const phoneRegex = /^[0-9+\-\s()]+$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid phone number';
            }
        }

        if (!isValid) {
            this.showFieldError(field, errorMessage);
        }

        return isValid;
    }

    validateForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isFormValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isFormValid = false;
            }
        });

        return isFormValid;
    }

    showFieldError(field, message) {
        field.style.borderColor = 'var(--danger-color)';
        field.style.boxShadow = '0 0 0 3px rgba(231, 76, 60, 0.1)';
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.cssText = `
            color: var(--danger-color);
            font-size: 12px;
            margin-top: 5px;
            animation: fadeInUp 0.3s ease-out;
        `;
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    clearFieldError(field) {
        field.style.borderColor = '';
        field.style.boxShadow = '';
        
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    // Car Cards Enhancement
    setupCarCards() {
        const carCards = document.querySelectorAll('.car-card');
        
        carCards.forEach(card => {
            // Add loading state for images
            const img = card.querySelector('img');
            if (img) {
                img.addEventListener('load', () => {
                    img.style.opacity = '1';
                });
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease';
            }

            // Enhanced click effects
            card.addEventListener('click', function(e) {
                if (!e.target.closest('button') && !e.target.closest('a')) {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                }
            });

            // Add favorite functionality
            this.addFavoriteButton(card);
        });
    }

    addFavoriteButton(card) {
        const favoriteBtn = document.createElement('button');
        favoriteBtn.className = 'favorite-btn';
        favoriteBtn.innerHTML = '<i class="fa fa-heart-o"></i>';
        favoriteBtn.style.cssText = `
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        `;
        
        favoriteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const icon = favoriteBtn.querySelector('i');
            if (icon.classList.contains('fa-heart-o')) {
                icon.className = 'fa fa-heart';
                favoriteBtn.style.color = 'var(--danger-color)';
                this.showNotification('Added to favorites!', 'success');
            } else {
                icon.className = 'fa fa-heart-o';
                favoriteBtn.style.color = '';
                this.showNotification('Removed from favorites', 'info');
            }
        });
        
        card.style.position = 'relative';
        card.appendChild(favoriteBtn);
    }

    // Mobile Menu
    setupMobileMenu() {
        const navbarToggle = document.querySelector('.navbar-toggle');
        const navbarCollapse = document.querySelector('.navbar-collapse');
        
        if (navbarToggle && navbarCollapse) {
            navbarToggle.addEventListener('click', () => {
                navbarCollapse.classList.toggle('in');
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.navbar') && navbarCollapse.classList.contains('in')) {
                    navbarCollapse.classList.remove('in');
                }
            });
        }
    }

    // Search Enhancements
    setupSearchEnhancements() {
        const searchInputs = document.querySelectorAll('input[type="search"], input[name*="search"]');
        
        searchInputs.forEach(input => {
            // Add search icon
            this.addSearchIcon(input);
            
            // Real-time search suggestions
            let searchTimeout;
            input.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, 300);
            });
        });
    }

    addSearchIcon(input) {
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        
        const icon = document.createElement('i');
        icon.className = 'fa fa-search';
        icon.style.cssText = `
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        `;
        
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        wrapper.appendChild(icon);
        
        input.style.paddingRight = '45px';
    }

    handleSearch(query) {
        if (query.length < 2) return;
        
        // Implement search logic here
        console.log('Searching for:', query);
        
        // Show loading state
        this.showSearchLoading();
        
        // Simulate API call
        setTimeout(() => {
            this.hideSearchLoading();
            // Update results
        }, 500);
    }

    // Utility Methods
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--card-bg);
            color: var(--text-primary);
            padding: 15px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border-left: 4px solid var(--${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'}-color);
            animation: slideInRight 0.3s ease-out;
            max-width: 300px;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    showLoadingOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(overlay);
        
        return () => overlay.remove();
    }

    showSearchLoading() {
        // Implementation for search loading state
    }

    hideSearchLoading() {
        // Implementation to hide search loading state
    }

    // Smooth scrolling for anchor links
    setupSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.modernCustomer = new ModernCustomer();
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .notification {
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .notification::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }
`;
document.head.appendChild(style);