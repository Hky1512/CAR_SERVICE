(() => {
    const base = window.APP_BASE_URL || '';

    // Header + mobile menu
    const topbar = document.getElementById('topbar');
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');

    const updateHeader = () => {
        if (topbar) topbar.classList.toggle('scrolled', window.scrollY > 18);
    };
    window.addEventListener('scroll', updateHeader, { passive: true });
    updateHeader();

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => mainNav.classList.toggle('open'));
        mainNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => mainNav.classList.remove('open'));
        });
    }

    // Reveal animation
    const revealItems = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('visible'));
    }

    // Booking: load available slots, clear stale selection when date changes.
    const dateInput = document.getElementById('dateInput');
    const slotList = document.getElementById('slotList');
    const slotInput = document.getElementById('slotInput');
    const slotValidation = document.getElementById('slotValidation');
    const bookingForm = document.getElementById('bookingForm');
    let pendingSlots = null;

    async function loadSlots(restoreId = 0) {
        if (!dateInput || !slotList || !slotInput || !dateInput.value) return;
        if (pendingSlots) pendingSlots.abort();
        pendingSlots = new AbortController();
        slotList.textContent = 'Đang tải khung giờ...';
        slotInput.value = '';
        if (slotValidation) slotValidation.textContent = '';

        try {
            const response = await fetch(
                `${base}/api/khung-gio.php?ngay=${encodeURIComponent(dateInput.value)}`,
                { headers: { Accept: 'application/json' }, signal: pendingSlots.signal }
            );
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.error || 'Không thể tải khung giờ');
            slotList.replaceChildren();
            payload.data.forEach((slot) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `slot ${slot.con_cho ? '' : 'slot-full'}`;
                button.disabled = !slot.con_cho;
                button.textContent = `${slot.gio_bat_dau} - ${slot.gio_ket_thuc}${slot.con_cho ? '' : ' (Không còn chỗ)'}`;
                button.setAttribute('aria-pressed', 'false');
                const setSelected = () => {
                    slotList.querySelectorAll('.slot').forEach((item) => {
                        item.classList.remove('selected');
                        item.setAttribute('aria-pressed', 'false');
                    });
                    button.classList.add('selected');
                    button.setAttribute('aria-pressed', 'true');
                    slotInput.value = String(slot.ma_khung_gio);
                    if (slotValidation) slotValidation.textContent = '';
                };
                button.addEventListener('click', setSelected);
                slotList.appendChild(button);
                if (slot.con_cho && Number(slot.ma_khung_gio) === Number(restoreId)) setSelected();
            });
            if (!payload.data.length) slotList.textContent = 'Không có khung giờ hoạt động.';
        } catch (error) {
            if (error.name === 'AbortError') return;
            slotList.replaceChildren();
            const message = document.createElement('span');
            message.className = 'error-text';
            message.textContent = error.message || 'Không thể tải khung giờ.';
            slotList.appendChild(message);
        }
    }

    if (dateInput) {
        dateInput.addEventListener('change', () => loadSlots());
        if (dateInput.value) loadSlots(window.APP_SELECTED_SLOT || 0);
    }
    if (bookingForm && slotInput) {
        bookingForm.addEventListener('submit', (event) => {
            if (!slotInput.value || Number(slotInput.value) <= 0) {
                event.preventDefault();
                if (slotValidation) slotValidation.textContent = 'Vui lòng chọn một khung giờ còn chỗ.';
                if (slotList) slotList.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    // Booking: "Khác" requires a note
    const serviceSelect = document.getElementById('serviceSelect');
    const bookingNote = document.getElementById('bookingNote');
    const otherHint = document.getElementById('otherServiceHint');

    function syncOtherService() {
        if (!serviceSelect || !bookingNote || !otherHint) return;
        const option = serviceSelect.options[serviceSelect.selectedIndex];
        const isOther = option?.dataset?.other === '1';

        bookingNote.required = isOther;
        otherHint.style.color = isOther ? '#f0d27b' : '';
        // Tránh tự động chiếm focus khi trang mới tải.
    }

    if (serviceSelect) {
        serviceSelect.addEventListener('change', syncOtherService);
        syncOtherService();
    }

    // Gemini AI suggestion
    const aiButton = document.getElementById('aiSuggest');
    const aiText = document.getElementById('aiText');
    const aiResult = document.getElementById('aiResult');

    if (aiButton && aiText && aiResult) {
        aiButton.addEventListener('click', async () => {
            const description = (aiText.value || '').trim();
            if (description.length < 5) {
                aiResult.textContent = 'Vui lòng mô tả rõ hơn.';
                return;
            }

            aiButton.disabled = true;
            aiResult.textContent = 'Đang phân tích mô tả...';

            try {
                const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
                const response = await fetch(`${base}/api/goi-y-ai.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ mo_ta: description, csrf_token: csrfToken }),
                });

                const payload = await response.json();
                if (!response.ok) throw new Error(payload.error || 'Không thể gợi ý dịch vụ');

                // Không render HTML do mô hình trả về để tránh DOM XSS.
                const title = document.createElement('strong');
                title.textContent = payload.data.ten_dich_vu;
                const reason = document.createElement('p');
                reason.textContent = payload.data.ly_do;
                aiResult.replaceChildren(title, reason);
                if (serviceSelect) {
                    serviceSelect.value = String(payload.data.ma_dich_vu);
                    syncOtherService();
                }
            } catch (error) {
                aiResult.textContent = error.message;
            } finally {
                aiButton.disabled = false;
            }
        });
    }
})();
