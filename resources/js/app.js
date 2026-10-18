import 'leaflet/dist/leaflet.css';
import './bootstrap';
import './app-notifications';
import './offline-presensi';
import './push-notification';
import './leaflet-presensi';
import './searchable-select';
import './file-uploader';
import './theme-toggle';

// Global Mobile Filter Accordion Toggle
document.addEventListener('click', function (e) {
    const header = e.target.closest('.laporanFilterHeaderMobile, .filterHeaderMobile');
    if (!header) return;

    const card = header.closest('.laporanFilterCard, .filter-card, .filterCard') || header.parentElement;
    if (!card) return;

    const formBody = card.querySelector('.laporanFilterFormBody, .filterFormBody, form');
    if (!formBody) return;

    const isOpened = formBody.classList.toggle('filter-open');
    const btn = header.querySelector('.btnToggleFilterMobile');
    const text = header.querySelector('.filterToggleText, [id$="FilterToggleText"], [id$="filterToggleText"]');
    const icon = header.querySelector('.filterToggleIcon, [id$="FilterToggleIcon"], [id$="filterToggleIcon"], ion-icon[name*="chevron"]');

    if (btn) btn.setAttribute('aria-expanded', isOpened ? 'true' : 'false');
    if (text) text.textContent = isOpened ? 'Tutup Filter' : 'Buka Filter';
    if (icon) icon.setAttribute('name', isOpened ? 'chevron-up-outline' : 'chevron-down-outline');
});
