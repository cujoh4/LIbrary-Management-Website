/* ============================================================
   Torang Baca — JavaScript murni (tanpa library).
   - Landing overlay (sekali per sesi, lewati jika sudah login)
   - Interaksi input rating bintang
   - Validasi form sederhana sisi klien
   - Konfirmasi aksi hapus / tandai lunas
   ============================================================ */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initLandingOverlay();
        initNavToggle();
        initScrollReveal();
        initStarPreview();
        initFormValidation();
        initConfirm();
        autoDismissFlash();
    });

    function initLandingOverlay() {
        var overlay = document.getElementById('landing-overlay');
        if (!overlay) return;

        // Sudah pernah dismiss — overlay tetap tersembunyi (display:none dari HTML)
        if (sessionStorage.getItem('landing_seen')) return;

        // Kunjungan pertama — tampilkan overlay & tahan hero
        var hero = document.querySelector('.hero-fullscreen');
        overlay.style.display = 'flex';
        if (hero) hero.classList.add('hero-waiting');

        var btnGuest = document.getElementById('btn-guest');
        if (btnGuest) {
            btnGuest.addEventListener('click', function () {
                overlay.classList.add('fade-out');
                sessionStorage.setItem('landing_seen', '1');

                if (hero) {
                    setTimeout(function () {
                        hero.classList.remove('hero-waiting');
                        hero.classList.add('hero-reveal');
                    }, 180);
                }

                setTimeout(function () { overlay.style.display = 'none'; }, 720);
            });
        }
    }

    function initScrollReveal() {
        var cards = document.querySelectorAll('.book-card');
        if (!cards.length) return;

        if (!('IntersectionObserver' in window)) {
            cards.forEach(function (c) { c.classList.add('visible'); });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry, i) {
                if (entry.isIntersecting) {
                    var card = entry.target;
                    var delay = parseInt(card.getAttribute('data-delay') || '0', 10);
                    setTimeout(function () { card.classList.add('visible'); }, delay);
                    observer.unobserve(card);
                }
            });
        }, { threshold: 0.12 });

        cards.forEach(function (card, i) {
            card.setAttribute('data-delay', Math.min(i * 60, 360));
            observer.observe(card);
        });
    }

    function initNavToggle() {
        var toggle = document.getElementById('nav-toggle');
        var nav    = document.getElementById('main-nav');
        if (!toggle || !nav) return;

        toggle.addEventListener('click', function () {
            var expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!expanded));
            nav.classList.toggle('is-open', !expanded);
        });

        // Tutup nav saat klik di luar header
        document.addEventListener('click', function (e) {
            if (!toggle.contains(e.target) && !nav.contains(e.target)) {
                toggle.setAttribute('aria-expanded', 'false');
                nav.classList.remove('is-open');
            }
        });
    }

    /**
     * Sorot bintang saat kursor melewati input rating.
     * Struktur HTML memakai radio terbalik (direction: rtl).
     */
    function initStarPreview() {
        var groups = document.querySelectorAll('.star-input');
        groups.forEach(function (group) {
            var labels = Array.prototype.slice.call(group.querySelectorAll('label'));
            var output = group.parentNode.querySelector('.star-value');

            labels.forEach(function (label) {
                label.addEventListener('click', function () {
                    var val = label.getAttribute('data-value');
                    if (output) {
                        output.textContent = val + ' dari 5 bintang';
                    }
                });
            });
        });
    }

    /**
     * Validasi form bertanda [data-validate]: cek field required & email.
     */
    function initFormValidation() {
        var forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var ok = true;
                clearErrors(form);

                form.querySelectorAll('[required]').forEach(function (field) {
                    if (!field.value.trim()) {
                        showError(field, 'Bagian ini wajib diisi.');
                        ok = false;
                    }
                });

                var email = form.querySelector('input[type="email"]');
                if (email && email.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                    showError(email, 'Format email tidak valid.');
                    ok = false;
                }

                var pw = form.querySelector('input[data-min]');
                if (pw && pw.value && pw.value.length < parseInt(pw.getAttribute('data-min'), 10)) {
                    showError(pw, 'Minimal ' + pw.getAttribute('data-min') + ' karakter.');
                    ok = false;
                }

                // Form rating wajib memilih bintang.
                var starGroup = form.querySelector('.star-input');
                if (starGroup && !starGroup.querySelector('input:checked')) {
                    var anchor = starGroup.parentNode;
                    showError(anchor, 'Silakan pilih jumlah bintang.');
                    ok = false;
                }

                if (!ok) {
                    e.preventDefault();
                }
            });
        });
    }

    function showError(field, msg) {
        var div = document.createElement('div');
        div.className = 'field-error';
        div.textContent = msg;
        var ref = field.classList && field.classList.contains('form-control') ? field : field;
        (ref.parentNode || ref).appendChild(div);
        if (ref.focus) { /* fokus pertama */ }
    }

    function clearErrors(form) {
        form.querySelectorAll('.field-error').forEach(function (el) { el.remove(); });
    }

    /**
     * Minta konfirmasi sebelum aksi penting (hapus / tandai lunas).
     */
    function initConfirm() {
        document.querySelectorAll('[data-confirm]').forEach(function (el) {
            el.addEventListener('submit', function (e) {
                if (!window.confirm(el.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            });
        });
    }

    /**
     * Sembunyikan pesan flash setelah beberapa detik.
     */
    function autoDismissFlash() {
        document.querySelectorAll('.flash').forEach(function (el) {
            setTimeout(function () {
                el.style.transition = 'opacity 0.6s';
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 650);
            }, 6000);
        });
    }
})();
