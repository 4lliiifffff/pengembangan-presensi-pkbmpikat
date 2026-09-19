/**
 * Presensi Leaflet Shared Module
 * PKBM PIKAT Attendance System
 *
 * Features:
 * - Local Leaflet bundling (No external unpkg/github CDN dependencies)
 * - Auto Dark Mode tile switching (CartoDB Dark Matter <-> OpenStreetMap)
 * - Lightweight SVG DivIcon pins (School/Target, User with pulse, Alternative spots)
 * - Haversine distance calculation, accuracy ring, and dynamic polyline
 * - Unified geolocation and geofence state management
 */

import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Perbaiki bug path default icon Leaflet saat di-bundle bundler modern (Vite)
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
    imagePath: '/images/leaflet/',
});

export function haversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Radius Bumi dalam meter
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

/**
 * Otomatisasi Penataan Tile Peta (Bebas Watermark & Bebas API Key)
 * Menggunakan OpenStreetMap berlisensi terbuka dengan filter CSS Dark Mode yang elegan
 */
export function setupLeafletTileTheme(map, elementOrId) {
    const mapEl = typeof elementOrId === 'string' ? document.getElementById(elementOrId) : elementOrId;
    const osmTileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const cartoApiKey = window.CARTO_API_KEY || null;

    let currentTileLayer = null;

    function isDarkModeActive() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
    }

    function applyTileLayer() {
        const isDark = isDarkModeActive();
        let url = osmTileUrl;
        let attribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
        let subdomains = 'abc';

        // Hanya gunakan CartoDB jika pengguna menyediakan API Key resmi,
        // jika tidak gunakan OpenStreetMap dengan CSS Dark Filter (bebas watermark)
        if (isDark && cartoApiKey) {
            url = `https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png?key=${cartoApiKey}`;
            attribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>';
            subdomains = 'abcd';
        }

        if (!currentTileLayer || currentTileLayer._url !== url) {
            if (currentTileLayer) {
                map.removeLayer(currentTileLayer);
            }

            currentTileLayer = L.tileLayer(url, {
                maxZoom: 20,
                subdomains: subdomains,
                attribution: attribution
            }).addTo(map);
        }

        if (mapEl) {
            if (isDark) {
                mapEl.classList.add('map-dark-theme');
            } else {
                mapEl.classList.remove('map-dark-theme');
            }
        }
    }

    applyTileLayer();

    const themeObserver = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                applyTileLayer();
            }
        }
    });

    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });

    return {
        applyTileLayer,
        isDark: isDarkModeActive,
        destroy: () => themeObserver.disconnect()
    };
}

/**
 * Buat Custom SVG Pin Icons tanpa dependensi asset CDN eksternal
 */
export function createPinIcon(type = 'target') {
    if (type === 'user') {
        return L.divIcon({
            className: 'leaflet-custom-marker marker-user',
            html: `
                <div class="user-pulse-marker">
                    <div class="user-pulse-ring"></div>
                    <div class="user-pulse-core">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M6 20v-2a6 6 0 0 1 12 0v2"></path>
                        </svg>
                    </div>
                </div>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -18]
        });
    }

    if (type === 'gray') {
        return L.divIcon({
            className: 'leaflet-custom-marker marker-alt',
            html: `
                <div class="pin-svg-wrapper pin-alt">
                    <svg viewBox="0 0 24 32" width="22" height="30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 0C5.373 0 0 5.373 0 12c0 8.5 12 20 12 20s12-11.5 12-20c0-6.627-5.373-12-12-12z" fill="#64748b"/>
                        <circle cx="12" cy="11" r="4.5" fill="#f8fafc"/>
                    </svg>
                </div>
            `,
            iconSize: [22, 30],
            iconAnchor: [11, 30],
            popupAnchor: [0, -28]
        });
    }

    // Default: 'target' / Lokasi Presensi (Merah / Brand)
    return L.divIcon({
        className: 'leaflet-custom-marker marker-target',
        html: `
            <div class="pin-svg-wrapper pin-target">
                <svg viewBox="0 0 24 32" width="28" height="36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <filter id="pin-shadow" x="-20%" y="-10%" width="140%" height="130%">
                            <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#000000" flood-opacity="0.35"/>
                        </filter>
                    </defs>
                    <path d="M12 0C5.373 0 0 5.373 0 12c0 9 12 20 12 20s12-11 12-20c0-6.627-5.373-12-12-12z" fill="#ef4444" filter="url(#pin-shadow)"/>
                    <circle cx="12" cy="11" r="5" fill="#ffffff"/>
                    <circle cx="12" cy="11" r="2.5" fill="#b91c1c"/>
                </svg>
            </div>
        `,
        iconSize: [28, 36],
        iconAnchor: [14, 36],
        popupAnchor: [0, -34]
    });
}

/**
 * Inisialisasi Peta Presensi Terpadu
 *
 * @param {Object} options
 * @param {string} options.elementId - ID elemen DOM peta (e.g. 'leafletMap')
 * @param {number} options.targetLat - Latitude target
 * @param {number} options.targetLng - Longitude target
 * @param {number} options.targetRadius - Radius toleransi (meter)
 * @param {string} options.targetNama - Nama lokasi presensi target
 * @param {string} [options.targetAlamat] - Alamat lokasi presensi target
 * @param {Array}  [options.lokasiList] - Daftar semua lokasi alternatif [{latitude, longitude, nama_lokasi, radius_meter}]
 * @param {Function} [options.onDistanceUpdate] - Callback saat jarak/posisi user terupdate
 * @param {Function} [options.onTargetChange] - Callback saat target berubah
 */
export function initPresensiMap(options) {
    const config = {
        elementId: 'leafletMap',
        targetLat: -6.2088,
        targetLng: 106.8456,
        targetRadius: 50,
        targetNama: 'Lokasi Presensi',
        targetAlamat: '',
        lokasiList: [],
        onDistanceUpdate: null,
        onTargetChange: null,
        ...options
    };

    const mapEl = document.getElementById(config.elementId);
    if (!mapEl) {
        console.warn(`[PresensiMap] Element #${config.elementId} tidak ditemukan.`);
        return null;
    }

    mapEl.classList.remove('d-none');
    mapEl.style.display = 'block';

    const safeLat = (typeof config.targetLat === 'number' && !isNaN(config.targetLat)) ? config.targetLat : parseFloat(config.targetLat);
    const safeLng = (typeof config.targetLng === 'number' && !isNaN(config.targetLng)) ? config.targetLng : parseFloat(config.targetLng);
    const safeRadius = (typeof config.targetRadius === 'number' && !isNaN(config.targetRadius)) ? config.targetRadius : parseInt(config.targetRadius);

    let currentTargetLat = (!isNaN(safeLat)) ? safeLat : -7.8011945;
    let currentTargetLng = (!isNaN(safeLng)) ? safeLng : 110.364917;
    let currentTargetRadius = (!isNaN(safeRadius)) ? safeRadius : 100;
    let currentTargetNama = config.targetNama || 'PKBM Pikat';
    let currentTargetAlamat = config.targetAlamat || '';

    let userLat = null;
    let userLng = null;
    let userAccuracy = null;
    let lastWithinZone = null;

    // Inisialisasi Leaflet Map
    const map = L.map(config.elementId, {
        zoomControl: true,
        attributionControl: true
    }).setView([currentTargetLat, currentTargetLng], 17);

    // Pastikan ukuran peta disesuaikan setelah elemen terlihat
    setTimeout(() => {
        if (map) map.invalidateSize();
    }, 150);

    // Setup Auto Dark Mode Tile Switching
    const themeController = setupLeafletTileTheme(map, mapEl);

    // Layer Marker & Shapes
    let targetMarker = L.marker([currentTargetLat, currentTargetLng], {
        icon: createPinIcon('target')
    }).addTo(map);
    targetMarker.bindPopup(`<b>${currentTargetNama}</b><br>Titik Presensi (Radius Maksimal: ${currentTargetRadius} m)`);

    let geofenceCircle = L.circle([currentTargetLat, currentTargetLng], {
        color: '#0284c7',
        fillColor: '#38bdf8',
        fillOpacity: 0.22,
        weight: 2,
        radius: currentTargetRadius
    }).addTo(map);

    let userMarker = null;
    let accuracyCircle = null;
    let trackPolyline = null;
    let altMarkersGroup = L.layerGroup().addTo(map);

    function renderAlternativeMarkers() {
        altMarkersGroup.clearLayers();
        if (!config.lokasiList || config.lokasiList.length === 0) return;

        config.lokasiList.forEach((lok) => {
            const lLat = parseFloat(lok.latitude);
            const lLng = parseFloat(lok.longitude);
            if (isNaN(lLat) || isNaN(lLng)) return;

            // Lewati jika koordinat sama dengan current target
            if (Math.abs(lLat - currentTargetLat) > 0.00001 || Math.abs(lLng - currentTargetLng) > 0.00001) {
                const m = L.marker([lLat, lLng], { icon: createPinIcon('gray') });
                m.bindPopup(`<b>${lok.nama_lokasi}</b><br>Radius: ${lok.radius_meter}m<br><small class="text-muted">Pilih di opsi lokasi jika ingin absen di titik ini</small>`);
                altMarkersGroup.addLayer(m);
            }
        });
    }

    renderAlternativeMarkers();

    function updateTarget(lat, lng, radius, nama, alamat = '', list = null) {
        currentTargetLat = parseFloat(lat);
        currentTargetLng = parseFloat(lng);
        currentTargetRadius = parseInt(radius, 10);
        currentTargetNama = nama || 'Lokasi Presensi';
        currentTargetAlamat = alamat || '';
        if (list) {
            config.lokasiList = list;
        }

        targetMarker.setLatLng([currentTargetLat, currentTargetLng]);
        targetMarker.setPopupContent(`<b>${currentTargetNama}</b><br>Titik Presensi (Radius Maksimal: ${currentTargetRadius} m)`);

        geofenceCircle.setLatLng([currentTargetLat, currentTargetLng]);
        geofenceCircle.setRadius(currentTargetRadius);

        renderAlternativeMarkers();

        if (userLat !== null && userLng !== null) {
            updateUserLocation(userLat, userLng, userAccuracy);
        } else {
            map.setView([currentTargetLat, currentTargetLng], 17);
        }

        if (typeof config.onTargetChange === 'function') {
            config.onTargetChange({
                lat: currentTargetLat,
                lng: currentTargetLng,
                radius: currentTargetRadius,
                nama: currentTargetNama,
                alamat: currentTargetAlamat
            });
        }
    }

    function updateUserLocation(lat, lng, accuracy = null) {
        userLat = parseFloat(lat);
        userLng = parseFloat(lng);
        userAccuracy = accuracy ? parseFloat(accuracy) : null;

        const distance = haversineDistance(userLat, userLng, currentTargetLat, currentTargetLng);
        const isWithin = distance <= currentTargetRadius;

        // Update / create User Marker
        if (userMarker) {
            userMarker.setLatLng([userLat, userLng]);
        } else {
            userMarker = L.marker([userLat, userLng], {
                icon: createPinIcon('user')
            }).addTo(map);
        }

        userMarker.bindPopup(`<b>Lokasi Anda</b><br>Jarak ke ${currentTargetNama}: <b>${Math.round(distance)} meter</b>${accuracy ? `<br><small class="text-muted">Akurasi GPS: &plusmn;${Math.round(accuracy)}m</small>` : ''}`);

        // Update Accuracy Circle jika akurasi tersedia
        if (accuracy && accuracy > 0) {
            if (accuracyCircle) {
                accuracyCircle.setLatLng([userLat, userLng]);
                accuracyCircle.setRadius(accuracy);
            } else {
                accuracyCircle = L.circle([userLat, userLng], {
                    radius: accuracy,
                    color: '#3b82f6',
                    fillColor: '#60a5fa',
                    fillOpacity: 0.12,
                    weight: 1,
                    dashArray: '3, 4'
                }).addTo(map);
            }
        }

        // Update Polyline garis penghubung user ke target
        const polylineColor = isWithin ? '#10b981' : (distance > 200 ? '#ef4444' : '#f59e0b');
        if (trackPolyline) {
            trackPolyline.setLatLngs([[userLat, userLng], [currentTargetLat, currentTargetLng]]);
            trackPolyline.setStyle({
                color: polylineColor,
                dashArray: isWithin ? null : '6, 6',
                opacity: isWithin ? 0.4 : 0.85,
                weight: 3.5
            });
        } else {
            trackPolyline = L.polyline([[userLat, userLng], [currentTargetLat, currentTargetLng]], {
                color: polylineColor,
                weight: 3.5,
                dashArray: isWithin ? null : '6, 6',
                opacity: isWithin ? 0.4 : 0.85
            }).addTo(map);
        }

        // Update Geofence Circle Color
        if (isWithin) {
            geofenceCircle.setStyle({ color: '#16a34a', fillColor: '#4ade80', fillOpacity: 0.28 });
        } else {
            geofenceCircle.setStyle({ color: '#dc2626', fillColor: '#f87171', fillOpacity: 0.28 });
        }

        // Pastikan ukuran container Leaflet valid sebelum fitBounds
        if (map) {
            map.invalidateSize();
        }

        // Fit Bounds agar kedua titik tampak jelas
        const bounds = L.latLngBounds([
            [currentTargetLat, currentTargetLng],
            [userLat, userLng]
        ]);
        map.fitBounds(bounds, { padding: [35, 35], maxZoom: 18 });

        // Trigger callback
        if (typeof config.onDistanceUpdate === 'function') {
            config.onDistanceUpdate({
                distance: distance,
                isWithin: isWithin,
                accuracy: userAccuracy,
                userLat: userLat,
                userLng: userLng,
                targetLat: currentTargetLat,
                targetLng: currentTargetLng,
                targetRadius: currentTargetRadius,
                targetNama: currentTargetNama,
                zoneChanged: lastWithinZone !== null && lastWithinZone !== isWithin
            });
        }

        lastWithinZone = isWithin;
    }

    return {
        map,
        updateUserLocation,
        setTarget: updateTarget,
        renderAlternativeMarkers,
        getDistance: () => {
            if (userLat === null || userLng === null) return null;
            return haversineDistance(userLat, userLng, currentTargetLat, currentTargetLng);
        },
        isWithinRadius: () => {
            if (userLat === null || userLng === null) return false;
            return haversineDistance(userLat, userLng, currentTargetLat, currentTargetLng) <= currentTargetRadius;
        },
        invalidateSize: () => {
            if (map) map.invalidateSize();
        },
        destroy: () => {
            if (themeController) themeController.destroy();
            map.remove();
        }
    };
}

// Attach ke window agar mudah diakses dari template Blade
if (typeof window !== 'undefined') {
    window.L = L;
    window.createPresensiMap = initPresensiMap;
    window.haversineDistance = haversineDistance;
    window.setupLeafletTileTheme = setupLeafletTileTheme;
    window.createPinIcon = createPinIcon;
}
