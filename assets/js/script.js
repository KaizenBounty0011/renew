/**
 * Renew Empire - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {

    // --- Mobile Navigation ---
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    if (navToggle) {
        navToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }

    // Mobile dropdown toggle
    document.querySelectorAll('.dropdown > a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                this.parentElement.classList.toggle('active');
            }
        });
    });

    // Close nav on link click (mobile)
    document.querySelectorAll('.nav-menu > li > a:not(.dropdown > a)').forEach(function(link) {
        link.addEventListener('click', function() {
            if (navToggle) navToggle.classList.remove('active');
            if (navMenu) navMenu.classList.remove('active');
        });
    });

    // --- Sticky Navbar ---
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // --- Division Slider ---
    initSliders();

    // --- Countdown Timers ---
    initCountdowns();

    // --- Form Validation ---
    initFormValidation();

    // --- Filter Buttons ---
    initFilters();

    // --- Price Calculators ---
    initPriceCalculators();
});

// === SLIDER ===
function initSliders() {
    document.querySelectorAll('.division-slider').forEach(function(slider) {
        const slides = slider.querySelectorAll('.slide');
        const dots = slider.querySelectorAll('.slider-dot');
        const prevBtn = slider.querySelector('.slider-arrow.prev');
        const nextBtn = slider.querySelector('.slider-arrow.next');
        let current = 0;
        let interval;

        function goToSlide(index) {
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            current = (index + slides.length) % slides.length;
            slides[current].classList.add('active');
            if (dots[current]) dots[current].classList.add('active');
        }

        function startAutoplay() {
            interval = setInterval(() => goToSlide(current + 1), 5000);
        }

        function stopAutoplay() { clearInterval(interval); }

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => { stopAutoplay(); goToSlide(i); startAutoplay(); });
        });

        if (prevBtn) prevBtn.addEventListener('click', () => { stopAutoplay(); goToSlide(current - 1); startAutoplay(); });
        if (nextBtn) nextBtn.addEventListener('click', () => { stopAutoplay(); goToSlide(current + 1); startAutoplay(); });

        if (slides.length > 1) startAutoplay();
    });
}

// === COUNTDOWN ===
function initCountdowns() {
    document.querySelectorAll('[data-countdown]').forEach(function(el) {
        const target = new Date(el.dataset.countdown).getTime();

        function update() {
            const now = new Date().getTime();
            const diff = target - now;

            if (diff <= 0) {
                el.innerHTML = '<span class="countdown-item"><span class="number">--</span><span class="label">Event Started</span></span>';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const secs = Math.floor((diff % (1000 * 60)) / 1000);

            el.innerHTML = `
                <div class="countdown-item"><span class="number">${days}</span><span class="label">Days</span></div>
                <div class="countdown-item"><span class="number">${hours}</span><span class="label">Hours</span></div>
                <div class="countdown-item"><span class="number">${mins}</span><span class="label">Mins</span></div>
                <div class="countdown-item"><span class="number">${secs}</span><span class="label">Secs</span></div>
            `;
        }

        update();
        setInterval(update, 1000);
    });
}

// === FORM VALIDATION ===
function initFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let valid = true;

            // Clear previous errors
            form.querySelectorAll('.field-error').forEach(el => el.remove());
            form.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));

            // Required fields
            form.querySelectorAll('[required]').forEach(function(field) {
                if (!field.value.trim()) {
                    valid = false;
                    showFieldError(field, 'This field is required');
                }
            });

            // Email validation
            form.querySelectorAll('input[type="email"]').forEach(function(field) {
                if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                    valid = false;
                    showFieldError(field, 'Please enter a valid email address');
                }
            });

            // Phone validation
            form.querySelectorAll('input[type="tel"]').forEach(function(field) {
                if (field.value && !/^[\+]?[\d\s\-\(\)]{7,20}$/.test(field.value)) {
                    valid = false;
                    showFieldError(field, 'Please enter a valid phone number');
                }
            });

            if (!valid) e.preventDefault();
        });
    });
}

function showFieldError(field, message) {
    field.classList.add('error');
    const error = document.createElement('div');
    error.className = 'field-error';
    error.style.cssText = 'color:#e63946;font-size:0.8rem;margin-top:4px;';
    error.textContent = message;
    field.parentElement.appendChild(error);
}

// === FILTERS ===
function initFilters() {
    document.querySelectorAll('.filter-bar').forEach(function(bar) {
        bar.querySelectorAll('.filter-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                bar.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                const items = document.querySelectorAll('[data-category]');

                items.forEach(function(item) {
                    if (filter === 'all' || item.dataset.category === filter) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    });
}

// === PRICE CALCULATORS ===
function initPriceCalculators() {
    // Ticket booking calculator
    const qtyInput = document.getElementById('ticket_quantity');
    const typeSelect = document.getElementById('ticket_type');
    const totalDisplay = document.getElementById('total_display');
    const totalInput = document.getElementById('total_amount');

    if (qtyInput && typeSelect && totalDisplay) {
        function calcTicketTotal() {
            const qty = parseInt(qtyInput.value) || 1;
            const price = typeSelect.options[typeSelect.selectedIndex]?.dataset.price || 0;
            const total = qty * parseFloat(price);
            totalDisplay.textContent = formatCurrency(total);
            if (totalInput) totalInput.value = total;
        }

        qtyInput.addEventListener('input', calcTicketTotal);
        typeSelect.addEventListener('change', calcTicketTotal);
        calcTicketTotal();
    }

    // Room reservation calculator
    const checkIn = document.getElementById('check_in_date');
    const checkOut = document.getElementById('check_out_date');
    const roomSelect = document.getElementById('room_id');
    const nightsDisplay = document.getElementById('nights_display');
    const roomTotalDisplay = document.getElementById('room_total_display');
    const totalNightsInput = document.getElementById('total_nights');
    const roomTotalInput = document.getElementById('room_total_amount');

    if (checkIn && checkOut && roomSelect) {
        function calcRoomTotal() {
            const cin = new Date(checkIn.value);
            const cout = new Date(checkOut.value);
            if (cin && cout && cout > cin) {
                const nights = Math.ceil((cout - cin) / (1000 * 60 * 60 * 24));
                const price = roomSelect.options[roomSelect.selectedIndex]?.dataset.price || 0;
                const total = nights * parseFloat(price);
                if (nightsDisplay) nightsDisplay.textContent = nights;
                if (roomTotalDisplay) roomTotalDisplay.textContent = formatCurrency(total);
                if (totalNightsInput) totalNightsInput.value = nights;
                if (roomTotalInput) roomTotalInput.value = total;
            }
        }

        checkIn.addEventListener('change', calcRoomTotal);
        checkOut.addEventListener('change', calcRoomTotal);
        roomSelect.addEventListener('change', calcRoomTotal);

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        checkIn.min = today;
        checkOut.min = today;

        checkIn.addEventListener('change', function() {
            checkOut.min = this.value;
            if (checkOut.value && checkOut.value <= this.value) checkOut.value = '';
        });
    }
}

function formatCurrency(amount) {
    return '₦' + amount.toLocaleString('en-NG', { minimumFractionDigits: 0 });
}

// === SEARCH ===
function searchProducts() {
    const query = document.getElementById('product_search')?.value.toLowerCase() || '';
    document.querySelectorAll('.product-card').forEach(function(card) {
        const name = card.dataset.name?.toLowerCase() || '';
        card.style.display = name.includes(query) ? '' : 'none';
    });
}
