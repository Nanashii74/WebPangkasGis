        const stores = (window.WebPangkasGIS?.stores || [])
            .map(store => ({
                ...store,
                lat: Number(store.lat),
                lng: Number(store.lng),
                rating: Number(store.rating),
            }));
        const defaultCenter = stores.length ? [stores[0].lat, stores[0].lng] : [-6.1751, 106.8650];
        const markers = [];
        const storeList = document.getElementById('storeList');
        const noData = document.getElementById('noData');
        const filterButtons = document.querySelectorAll('.filter-button');
        const gpsToggleButton = document.getElementById('gpsToggleButton');
        const clearRouteButton = document.getElementById('clearRouteButton');
        const routeFocusPill = document.getElementById('routeFocusPill');
        let currentFilter = 'rating';
        const shopPopupOptions = {
            maxWidth: 300,
            autoPan: true,
            keepInView: true,
            autoPanPaddingTopLeft: [28, 190],
            autoPanPaddingBottomRight: [28, 44],
        };

        function escapeHTML(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function isShopOpen(shop) {
            return shop.status && shop.status.toLowerCase() === 'buka';
        }

        function getShopInitial(shop) {
            return String(shop.nama || 'B').trim().charAt(0).toUpperCase() || 'B';
        }

        function getShopPhoto(shop) {
            const url = String(shop.foto_url || '').trim();
            return url ? url : null;
        }

        function createPhotoPlaceholder(shop) {
            const initial = getShopInitial(shop);
            return `<div class="barber-photo-placeholder" aria-label="Foto belum tersedia">${escapeHTML(initial)}</div>`;
        }

        function createPhotoMarkup(shop, className) {
            const photoUrl = getShopPhoto(shop);
            const alt = shop.foto_alt || `Foto ${shop.nama}`;

            if (!photoUrl) return createPhotoPlaceholder(shop);

            return `<img class="${className}" src="${escapeHTML(photoUrl)}" alt="${escapeHTML(alt)}" loading="lazy" onerror="this.replaceWith(this.nextElementSibling)">${createPhotoPlaceholder(shop)}`;
        }

        function createBarberIcon(shop, active = false) {
            const classes = [
                'barber-marker-pin',
                isShopOpen(shop) ? 'is-open' : 'is-closed',
                active ? 'is-active' : '',
            ].filter(Boolean).join(' ');

            return L.divIcon({
                html: `<div class="${classes}"><span>${escapeHTML(getShopInitial(shop))}</span></div>`,
                className: 'barber-marker',
                iconSize: [34, 34],
                iconAnchor: [17, 34],
                popupAnchor: [0, -30],
            });
        }

        function createShopPopup(shop) {
            const statusClass = isShopOpen(shop) ? 'is-open' : 'is-closed';
            const schedule = shop.jam_buka && shop.jam_tutup
                ? `${escapeHTML(shop.jam_buka)} - ${escapeHTML(shop.jam_tutup)}`
                : 'Jam tidak tersedia';

            return `
                <div class="map-popup">
                    <div class="map-popup-photo-wrap">
                        ${createPhotoMarkup(shop, 'map-popup-photo')}
                    </div>
                    <div class="map-popup-title">${escapeHTML(shop.nama)}</div>
                    <div class="map-popup-address">${escapeHTML(shop.alamat)}</div>
                    <div class="map-popup-meta">
                        <span class="map-popup-pill">Rating ${escapeHTML(shop.rating)} / 5</span>
                        <span class="map-popup-pill ${statusClass}">${escapeHTML(shop.status || 'Tutup')}</span>
                        <span class="map-popup-pill">${schedule}</span>
                    </div>
                </div>
            `;
        }

        const map = L.map('map', {
            zoomControl: false,
            attributionControl: false,
        }).setView(defaultCenter, stores.length ? 13 : 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
        }).addTo(map);

        L.control.zoom({
            position: 'topright'
        }).addTo(map);
        L.control.scale({
            imperial: false,
            position: 'bottomleft'
        }).addTo(map);

        stores.forEach((shop, index) => {
            const marker = L.marker([shop.lat, shop.lng], {
                title: shop.nama,
                icon: createBarberIcon(shop),
            }).addTo(map);

            marker.bindPopup(createShopPopup(shop), shopPopupOptions);

            marker.on('click', () => selectStore(index));
            markers.push(marker);
        });

        function getDistance(lat1, lon1, lat2, lon2) {
            const toRad = deg => deg * Math.PI / 180;
            const R = 6371;
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function sortIndexes(criteria) {
            const order = stores.map((_, index) => index);

            if (criteria === 'alpha') {
                order.sort((a, b) => stores[a].nama.localeCompare(stores[b].nama, 'id', {
                    sensitivity: 'base'
                }));
            } else if (criteria === 'distance') {
                const center = userLocation || [3.5712, 98.6865];
                order.sort((a, b) => {
                    const da = getDistance(center[0], center[1], stores[a].lat, stores[a].lng);
                    const db = getDistance(center[0], center[1], stores[b].lat, stores[b].lng);
                    return da - db;
                });
            } else {
                order.sort((a, b) => Number(stores[b].rating) - Number(stores[a].rating));
            }

            return order;
        }

        function createCard(shop, index) {
            const card = document.createElement('div');
            card.className = 'store-card';
            card.dataset.index = index;

            const body = document.createElement('div');
            body.className = 'store-card-body';

            const photoWrap = document.createElement('div');
            photoWrap.className = 'store-card-photo-wrap';

            const photoUrl = getShopPhoto(shop);
            if (photoUrl) {
                const photo = document.createElement('img');
                photo.className = 'store-card-photo';
                photo.src = photoUrl;
                photo.alt = shop.foto_alt || `Foto ${shop.nama}`;
                photo.loading = 'lazy';
                photo.onerror = () => {
                    photoWrap.innerHTML = createPhotoPlaceholder(shop);
                };
                photoWrap.appendChild(photo);
            } else {
                photoWrap.innerHTML = createPhotoPlaceholder(shop);
            }

            body.appendChild(photoWrap);

            const topRow = document.createElement('div');
            topRow.className = 'top-row mb-3';

            const titleBlock = document.createElement('div');
            const title = document.createElement('h6');
            title.textContent = shop.nama;

            const statusPill = document.createElement('div');
            statusPill.className = `status-pill ${shop.status && shop.status.toLowerCase() === 'buka' ? 'status-buka' : 'status-tutup'}`;
            statusPill.textContent = shop.status || 'Tutup';

            titleBlock.append(title);

            const badge = document.createElement('span');
            badge.className = 'badge';
            badge.textContent = 'Barbershop';

            topRow.append(titleBlock, badge);
            body.appendChild(topRow);

            const secondRow = document.createElement('div');
            secondRow.className = 'd-flex justify-content-between align-items-center gap-3';

            const ratingPill = document.createElement('div');
            ratingPill.className = 'rating-pill';
            ratingPill.textContent = `Rating ${shop.rating} / 5`;
            secondRow.appendChild(ratingPill);
            secondRow.appendChild(statusPill);
            body.appendChild(secondRow);

            const address = document.createElement('p');
            address.className = 'address mb-2';
            address.textContent = shop.alamat;
            body.appendChild(address);

            const schedule = document.createElement('div');
            schedule.className = 'schedule';
            if (shop.jam_buka && shop.jam_tutup) {
                schedule.textContent = `Jam buka ${shop.jam_buka} - ${shop.jam_tutup}`;
            } else {
                schedule.textContent = 'Jam buka tidak tersedia';
            }
            body.appendChild(schedule);

            // Distance element yang bisa di-update
            const distance = document.createElement('div');
            distance.className = 'distance';
            distance.dataset.shopIndex = index;

            // Jika userLocation sudah tersedia, hitung jarak. Jika belum, tampilkan loading
            if (userLocation) {
                const km = getDistance(userLocation[0], userLocation[1], shop.lat, shop.lng);
                distance.textContent = `Jarak ${km.toFixed(2)} km`;
            } else {
                distance.textContent = 'Aktifkan lokasi untuk jarak akurat';
            }

            body.appendChild(distance);

            card.appendChild(body);
            card.addEventListener('click', () => selectStore(index));

            return card;
        }

        function renderStores(order) {
            storeList.innerHTML = '';
            if (!order.length) {
                noData.style.display = 'block';
                return;
            }
            noData.style.display = 'none';

            order.forEach(originalIndex => {
                storeList.appendChild(createCard(stores[originalIndex], originalIndex));
            });
        }

        function setActiveCard(activeIndex) {
            document.querySelectorAll('.store-card').forEach(card => {
                card.classList.toggle('active', Number(card.dataset.index) === activeIndex);
            });
            markers.forEach((marker, index) => {
                if (stores[index]) marker.setIcon(createBarberIcon(stores[index], index === activeIndex));
            });
        }

        function selectStore(index) {
            const shop = stores[index];
            if (!shop) return;

            map.flyTo([shop.lat, shop.lng], 17, {
                duration: 1.2
            });
            setActiveCard(index);

            setTimeout(() => {
                if (markers[index]) {
                    markers[index].openPopup();
                    map.panBy([0, -90], { animate: true, duration: 0.35 });
                }
            }, 300);
        }

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(item => item.classList.remove('active'));
                button.classList.add('active');
                currentFilter = button.dataset.filter;
                renderStores(sortIndexes(currentFilter));
            });
        });

        // NOTE: renderStores initialization moved to INITIALIZATION section below

        // ========== TAHAP 2: FRONTEND FEATURES (IMPROVED) ==========

        // Variable untuk menyimpan routing control, user location, dan state
        let routingControl = null;
        let userLocationMarker = null;
        let userAccuracyCircle = null;
        let userLocation = null;
        let userLocationAccuracy = null;
        let userHeading = null;
        let kelurahanLayer = null;
        let geolocationWatchId = null;
        let lastRouteCalculation = {
            lat: null,
            lng: null
        };
        let currentRouteState = {
            active: false,
            barberIndex: null,
            tracking: false
        };
        let routeUpdateInFlight = false;
        let lastRouteUpdateAt = 0;

        function setGPSButtonState(active, label = null) {
            if (!gpsToggleButton) return;
            gpsToggleButton.classList.toggle('is-active', active);
            const labelEl = gpsToggleButton.querySelector('span:not(.map-tool-dot)');
            if (labelEl && label) labelEl.textContent = label;
        }

        function setRouteUI(active, text = 'Rute aktif') {
            if (clearRouteButton) clearRouteButton.style.display = active ? 'inline-flex' : 'none';
            if (routeFocusPill) {
                routeFocusPill.textContent = text;
                routeFocusPill.classList.toggle('is-visible', active);
            }
        }

        function getAccuracyLabel(accuracy) {
            if (!Number.isFinite(accuracy)) return 'Lokasi Aktif';
            if (accuracy <= 25) return `Akurat ${Math.round(accuracy)}m`;
            if (accuracy <= 100) return `Cukup Akurat ${Math.round(accuracy)}m`;
            return `Kurang Akurat ${Math.round(accuracy)}m`;
        }

        // UTILITY: Update distance di semua cards ketika user location berubah
        function updateAllDistances() {
            // Hanya update elemen .distance, BUKAN tombol route-button
            const distanceElements = document.querySelectorAll('.distance[data-shop-index]');
            distanceElements.forEach(elem => {
                const shopIndex = parseInt(elem.dataset.shopIndex);
                if (!isNaN(shopIndex) && stores[shopIndex]) {
                    if (!userLocation) {
                        elem.textContent = 'Aktifkan lokasi untuk jarak akurat';
                        elem.classList.remove('loading');
                        return;
                    }

                    const shop = stores[shopIndex];
                    const km = getDistance(userLocation[0], userLocation[1], shop.lat, shop.lng);
                    elem.textContent = `Jarak ${km.toFixed(2)} km`;
                    elem.classList.remove('loading');
                }
            });
        }

        // 1. LOAD & TAMPILKAN POLYGON MEDAN MAIMUN + KELURAHAN
        async function loadKelurahanLayer() {
            try {
                const response = await fetch('/maps/kota_medan.geojson');
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const geojsonData = await response.json();

                // Kelurahan resmi di Kecamatan Medan Maimun
                const kelurahanMaimun = ['Hamdan', 'Kampung Baru', 'Sei Mati', 'Sukaraja', 'Jati', 'Aur'];

                // Filter: kecamatan Medan Maimun (level 6) + kelurahannya (level 7)
                // Sei Mati duplikat - pakai yang osm_id -19978256 (polygon lebih besar)
                const filtered = geojsonData.features.filter(f => {
                    const name  = f.properties.name;
                    const level = f.properties.admin_level;
                    const osm   = f.properties.osm_id;
                    if (name === 'Medan Maimun' && level === 6) return true;
                    if (name === 'Sei Mati' && level === 7) return osm === -19978256;
                    return kelurahanMaimun.includes(name) && level === 7;
                });

                if (!filtered.length) {
                    console.warn('Tidak ada data polygon Medan Maimun ditemukan');
                    return;
                }

                console.log(`${filtered.length} polygon ditemukan (1 kecamatan + ${filtered.length - 1} kelurahan)`);

                // Warna per kelurahan
                const warnKelurahan = {
                    'Hamdan'      : '#ef6a5b',
                    'Kampung Baru': '#d9903d',
                    'Sei Mati'    : '#d7ad2f',
                    'Sukaraja'    : '#39a875',
                    'Jati'        : '#4f8fd8',
                    'Aur'         : '#8b6ed1',
                };

                // 1a. Layer kecamatan - border tebal, fill transparan
                const kecamatanFeature = filtered.find(f => f.properties.name === 'Medan Maimun');
                if (kecamatanFeature) {
                    L.geoJSON(kecamatanFeature, {
                        style: {
                            color     : '#1a252f',
                            weight    : 3,
                            opacity   : 0.78,
                            fillColor : '#2c3e50',
                            fillOpacity: 0.03,
                            dashArray : null,
                        }
                    }).bindPopup(`
                        <div style="font-size:13px;">
                            <strong style="font-size:15px;">Kecamatan Medan Maimun</strong><br>
                            <small style="color:#666;">Kota Medan, Sumatera Utara</small><br>
                            <hr style="margin:5px 0; border:none; border-top:1px solid #ddd;">
                            <small>6 Kelurahan</small>
                        </div>
                    `).addTo(map);
                }

                // 1b. Layer kelurahan - fill berwarna dengan label
                const kelurahanFeatures = filtered.filter(f => f.properties.admin_level === 7);
                kelurahanFeatures.forEach(feat => {
                    const nama  = feat.properties.name;
                    const color = warnKelurahan[nama] || '#95a5a6';

                    const layer = L.geoJSON(feat, {
                        style: {
                            color       : color,
                            weight      : 2,
                            opacity     : 0.72,
                            fillColor   : color,
                            fillOpacity : 0.14,
                            dashArray   : '5, 4',
                            lineCap     : 'round',
                        }
                    });

                    layer.bindPopup(`
                        <div style="font-size:12px; min-width:160px;">
                            <strong style="font-size:14px;">
                                <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${color};margin-right:5px;"></span>
                                ${nama}
                            </strong><br>
                            <small style="color:#666;">Kelurahan - Kec. Medan Maimun</small>
                        </div>
                    `);

                    // Highlight on hover
                    layer.on('mouseover', function(e) {
                        e.target.setStyle({ fillOpacity: 0.28, weight: 3, opacity: 0.95 });
                        if (!L.Browser.ie && !L.Browser.opera) e.target.bringToFront();
                    });
                    layer.on('mouseout', function(e) {
                        e.target.setStyle({ fillOpacity: 0.14, weight: 2, opacity: 0.72 });
                    });
                    layer.on('click', function(e) {
                        e.target.openPopup();
                    });

                    layer.addTo(map);
                });

                // Fit peta ke batas kecamatan Medan Maimun
                if (kecamatanFeature) {
                    const bounds = L.geoJSON(kecamatanFeature).getBounds();
                    if (bounds.isValid()) map.fitBounds(bounds, { padding: [40, 40] });
                }

            } catch (error) {
                console.error('Error loading polygon:', error);
            }
        }

        // 2. TRACK USER LOCATION (ROBUST & REAL-TIME)
        function trackUserLocation() {
            if (!navigator.geolocation) {
                console.error('Geolocation not supported');
                alert('Browser Anda tidak mendukung Geolocation API');
                return;
            }

            if (geolocationWatchId) {
                setGPSButtonState(true, 'Lokasi Aktif');
                if (userLocation) map.flyTo(userLocation, 15, { duration: 0.8 });
                return;
            }

            setGPSButtonState(true, 'Mencari Lokasi');

            // Request initial position dengan high accuracy
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    updateUserLocation(position);
                    geolocationWatchId = navigator.geolocation.watchPosition(
                        function(position) {
                            updateUserLocation(position);
                        },
                        function(error) {
                            console.warn('Watch position error:', error.message);
                            if (error.code === error.PERMISSION_DENIED && geolocationWatchId) {
                                navigator.geolocation.clearWatch(geolocationWatchId);
                                geolocationWatchId = null;
                                setGPSButtonState(false, 'Gunakan Lokasi');
                            }
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 5000
                        }
                    );
                },
                function(error) {
                    console.warn('Geolocation permission denied:', error.message);
                    alert('GPS ditolak. Fitur rute dan tracking membutuhkan lokasi asli Anda.');
                    userLocation = null;
                    userLocationAccuracy = null;
                    updateAllDistances();
                    setGPSButtonState(false, 'Gunakan Lokasi');
                }, {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        }

        // Helper function untuk update user location dan UI
        function updateUserLocation(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy;
            const heading = Number.isFinite(position.coords.heading) ? position.coords.heading : null;

            // Validasi 1: Akurasi harus <= 500 meter (GPS belum stabil = skip)
            if (accuracy > 500) {
                console.warn(`Akurasi terlalu rendah (${accuracy.toFixed(0)}m), skip update`);
                setGPSButtonState(true, `GPS Belum Stabil ${Math.round(accuracy)}m`);
                return;
            }

            // Validasi 2: Koordinat harus masuk area Sumatera Utara
            // Bounding box kasar: lat 1.5-4.5, lng 97.5-99.5
            const dalamSumut = lat >= 1.5 && lat <= 4.5 && lng >= 97.5 && lng <= 99.5;
            if (!dalamSumut) {
                console.warn(`Koordinat di luar Sumatera Utara: [${lat.toFixed(4)}, ${lng.toFixed(4)}], skip`);
                return;
            }

            const moved = !userLocation || Math.abs(lat - userLocation[0]) > 0.00005 || Math.abs(lng - userLocation[1]) > 0.00005;
            const accuracyChanged = userLocationAccuracy === null || Math.abs(accuracy - userLocationAccuracy) > 10;
            const headingChanged = userHeading !== heading;

            if (moved || accuracyChanged || headingChanged) {
                userLocation = [lat, lng];
                userLocationAccuracy = accuracy;
                userHeading = heading;
                setGPSButtonState(true, getAccuracyLabel(accuracy));

                // Update marker
                if (userLocationMarker) {
                    userLocationMarker.setLatLng([lat, lng]);
                    userLocationMarker.setIcon(createUserLocationIcon());
                    bindUserLocationPopup(lat, lng, true);
                } else {
                    addUserMarker(lat, lng, true);
                }
                updateAccuracyCircle(lat, lng, accuracy);

                // Update semua jarak di cards
                updateAllDistances();

                // Jika ada rute aktif dan tracking nyala, update rute secara real-time
                if (currentRouteState.active && currentRouteState.tracking && currentRouteState.barberIndex !== null) {
                    updateSnappedRouteMarker();
                }

                if (moved && currentRouteState.active && currentRouteState.tracking && currentRouteState.barberIndex !== null) {
                    updateRouteRealtime(currentRouteState.barberIndex);
                    map.panTo([lat, lng], { animate: true, duration: 0.45 });
                }

                console.log(`Location updated: [${lat.toFixed(6)}, ${lng.toFixed(6)}], Accuracy: ${accuracy.toFixed(0)}m`);
            }
        }

        // Helper function untuk add/update user marker
        function createUserLocationIcon() {
            const isTracking = currentRouteState.tracking;
            const headingStyle = userHeading === null ? '' : ` style="transform: rotate(${userHeading}deg)"`;
            const headingClass = userHeading === null ? '' : ' has-heading';

            return L.divIcon({
                html: `<div class="user-location-marker${isTracking ? ' is-tracking' : ''}${headingClass}"><span${headingStyle}></span></div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17],
                popupAnchor: [0, -17],
                className: 'user-location-icon'
            });
        }

        function updateAccuracyCircle(lat, lng, accuracy) {
            if (!Number.isFinite(accuracy)) return;

            if (!userAccuracyCircle) {
                userAccuracyCircle = L.circle([lat, lng], {
                    radius: accuracy,
                    color: '#2563eb',
                    weight: 1,
                    opacity: 0.26,
                    fillColor: '#2563eb',
                    fillOpacity: 0.08,
                    interactive: false
                }).addTo(map);
                return;
            }

            userAccuracyCircle.setLatLng([lat, lng]);
            userAccuracyCircle.setRadius(accuracy);
        }

        function refreshUserMarkerIcon() {
            if (userLocationMarker) {
                userLocationMarker.setIcon(createUserLocationIcon());
            }
        }

        function bindUserLocationPopup(lat, lng, isAccurate) {
            if (!userLocationMarker) return;

            const accuracyText = isAccurate ? `<small style="color:#64748b;">${escapeHTML(getAccuracyLabel(userLocationAccuracy))}</small>` : '<small style="color:#e74c3c;">GPS belum aktif</small>';

            userLocationMarker.bindPopup(`
                <div style="max-width:220px;">
                    <strong>Lokasi Anda</strong><br>
                    <small>Lat: ${lat.toFixed(6)}</small><br>
                    <small>Lng: ${lng.toFixed(6)}</small><br>
                    ${accuracyText}
                </div>
            `);
        }

        function addUserMarker(lat, lng, isAccurate) {
            if (userLocationMarker) {
                map.removeLayer(userLocationMarker);
            }

            userLocationMarker = L.marker([lat, lng], {
                icon: createUserLocationIcon(),
                title: 'Lokasi Anda'
            }).addTo(map);

            bindUserLocationPopup(lat, lng, isAccurate);
        }

        const DIRECTIONS_URL = window.WebPangkasGIS?.directionsUrl || '/route/directions';

        // Layer untuk garis rute (polyline) dan marker rute
        let routePolyline = null;
        let routeStartMarker = null;
        let routeEndMarker = null;
        let snappedRouteMarker = null;
        let routeLatLngs = [];

        // Icon marker
        const greenIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });
        const redIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        async function fetchRoute(coordinates) {
            const res = await fetch(DIRECTIONS_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ coordinates })
            });

            if (!res.ok) {
                const errText = await res.text();
                throw new Error(`Routing error ${res.status}: ${errText}`);
            }

            return res.json();
        }

        function canUseLocationForRoute() {
            if (!userLocation) {
                alert('Aktifkan lokasi terlebih dahulu lewat tombol "Gunakan Lokasi" di peta.');
                return false;
            }

            if (!Number.isFinite(userLocationAccuracy) || userLocationAccuracy > 150) {
                alert('GPS masih kurang akurat untuk membuat rute. Tunggu beberapa detik sampai status lokasi lebih akurat.');
                return false;
            }

            return true;
        }

        function getRouteSteps(feature) {
            const segments = feature?.properties?.segments || [];
            return segments.flatMap(segment => segment.steps || []).slice(0, 5);
        }

        function getNearestRoutePoint(latLng) {
            if (!routeLatLngs.length) return null;

            let nearest = null;
            let minDistance = Infinity;

            routeLatLngs.forEach(point => {
                const distance = getDistance(latLng[0], latLng[1], point[0], point[1]) * 1000;
                if (distance < minDistance) {
                    minDistance = distance;
                    nearest = point;
                }
            });

            return nearest ? { point: nearest, distance: minDistance } : null;
        }

        function updateSnappedRouteMarker() {
            if (!currentRouteState.tracking || !userLocation || !routeLatLngs.length) {
                if (snappedRouteMarker) {
                    map.removeLayer(snappedRouteMarker);
                    snappedRouteMarker = null;
                }
                return null;
            }

            const nearest = getNearestRoutePoint(userLocation);
            if (!nearest) return null;

            const snappedIcon = L.divIcon({
                html: '<div class="snapped-route-marker"></div>',
                iconSize: [18, 18],
                iconAnchor: [9, 9],
                className: 'snapped-route-icon'
            });

            if (!snappedRouteMarker) {
                snappedRouteMarker = L.marker(nearest.point, {
                    icon: snappedIcon,
                    interactive: false,
                    title: 'Posisi pada rute'
                }).addTo(map);
            } else {
                snappedRouteMarker.setLatLng(nearest.point);
            }

            return nearest;
        }

        // Hapus rute yang ada di peta
        function clearRoute() {
            if (routePolyline)    { map.removeLayer(routePolyline);    routePolyline    = null; }
            if (routeStartMarker) { map.removeLayer(routeStartMarker); routeStartMarker = null; }
            if (routeEndMarker)   { map.removeLayer(routeEndMarker);   routeEndMarker   = null; }
            if (snappedRouteMarker) { map.removeLayer(snappedRouteMarker); snappedRouteMarker = null; }
            routeLatLngs = [];
            currentRouteState = { active: false, barberIndex: null, tracking: false };
            lastRouteCalculation = { lat: null, lng: null };
            lastRouteUpdateAt = 0;
            routeUpdateInFlight = false;
            setRouteUI(false);

            document.querySelectorAll('.route-button').forEach(button => {
                button.disabled = false;
                button.textContent = 'Lihat Rute';
                button.dataset.state = 'initial';
                button.style.background = '';
            });
        }

        // Tampilkan info rute di sidebar card yang aktif
        function showRouteInfo(distanceM, durationSec, barberName, steps = []) {
            const distanceDisplay = distanceM >= 1000
                ? `${(distanceM / 1000).toFixed(2)} km`
                : `${Math.round(distanceM)} m`;
            const totalMin = Math.round(durationSec / 60);
            const jam = Math.floor(totalMin / 60);
            const menit = totalMin % 60;
            const durationText = jam > 0 ? `${jam}j ${menit}m` : `${totalMin} menit`;

            const activeCard = document.querySelector('.store-card.active');
            if (!activeCard) return;

            let routeInfo = activeCard.querySelector('.route-info');
            if (!routeInfo) {
                routeInfo = document.createElement('div');
                routeInfo.className = 'route-info';
                activeCard.querySelector('.store-card-body').appendChild(routeInfo);
            }
            const instructionItems = steps
                .map(step => `<li>${escapeHTML(step.instruction || 'Lanjutkan perjalanan')}</li>`)
                .join('');

            routeInfo.innerHTML = `
                <strong>Rute Perjalanan</strong>
                <div>Jarak tempuh: <strong>${distanceDisplay}</strong></div>
                <div>Estimasi waktu: <strong>${durationText}</strong></div>
                ${instructionItems ? `<ol class="route-steps">${instructionItems}</ol>` : ''}
            `;
        }

        // 3. FUNGSI LIHAT RUTE KE BARBERSHOP - menggunakan ORS
        async function lihatRuteKeBarber(barberLat, barberLng, barberName = 'Barbershop', storeIndex = null) {
            if (!canUseLocationForRoute()) return;

            // Hapus rute lama
            clearRoute();

            const routeBtn = document.querySelector('.route-button[data-shop-index="' + storeIndex + '"]');
            if (routeBtn) {
                routeBtn.disabled = true;
                routeBtn.textContent = 'Memuat rute...';
            }

            try {
                const body = {
                    coordinates: [
                        [userLocation[1], userLocation[0]],   // [lng, lat] format ORS
                        [barberLng, barberLat]
                    ]
                };

                const data = await fetchRoute(body.coordinates);
                const feature = data.features[0];
                const coords  = feature.geometry.coordinates; // [[lng,lat], ...]
                const summary = feature.properties.summary;
                const steps = getRouteSteps(feature);

                // Konversi koordinat ORS [lng,lat] ke Leaflet [lat,lng]
                const latLngs = coords.map(c => [c[1], c[0]]);
                routeLatLngs = latLngs;

                // Gambar polyline rute di peta
                routePolyline = L.polyline(latLngs, {
                    color: '#4A6CF7',
                    weight: 6,
                    opacity: 0.85,
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(map);

                // Marker user utama sudah menjadi titik awal rute.
                routeStartMarker = null;
                routeEndMarker = L.marker([barberLat, barberLng], { icon: redIcon })
                    .bindPopup(`<strong>${barberName}</strong>`)
                    .addTo(map);

                // Zoom peta agar rute terlihat semua
                map.fitBounds(routePolyline.getBounds(), { padding: [60, 60] });

                // Tampilkan info jarak & waktu di sidebar
                showRouteInfo(summary.distance, summary.duration, barberName, steps);

                // Simpan state
                currentRouteState = { active: true, barberIndex: storeIndex, tracking: false };
                setRouteUI(true, `Rute ke ${barberName}`);

                // Update tombol
                if (routeBtn) {
                    routeBtn.disabled = false;
                    routeBtn.textContent = 'Mulai Tracking';
                    routeBtn.dataset.state = 'ready';
                    routeBtn.style.background = '#4A6CF7';
                }

            } catch (err) {
                console.error('ORS routing error:', err);
                clearRoute();
                if (routeBtn) {
                    routeBtn.disabled = false;
                    routeBtn.textContent = 'Lihat Rute';
                    routeBtn.dataset.state = 'initial';
                }
                alert(`Gagal memuat rute ke ${barberName}.\n\nPastikan GPS aktif, koneksi internet tersedia, dan konfigurasi ORS di server benar.`);
            }
        }

        // Update rute saat user bergerak (dipanggil dari updateUserLocation saat tracking aktif)
        async function updateRouteRealtime(barberIndex) {
            if (!userLocation || !currentRouteState.active) return;
            const shop = stores[barberIndex];
            if (!shop) return;

            try {
                if (routeUpdateInFlight) return;

                const now = Date.now();
                const movedKm = lastRouteCalculation.lat === null
                    ? Infinity
                    : getDistance(lastRouteCalculation.lat, lastRouteCalculation.lng, userLocation[0], userLocation[1]);
                const nearest = updateSnappedRouteMarker();
                const offRoute = nearest && nearest.distance > 70;

                if (!offRoute && now - lastRouteUpdateAt < 8000 && movedKm < 0.025) return;

                routeUpdateInFlight = true;
                lastRouteUpdateAt = now;
                lastRouteCalculation = { lat: userLocation[0], lng: userLocation[1] };
                if (offRoute) setRouteUI(true, 'Rute diperbarui dari posisi Anda');

                const data = await fetchRoute([
                    [userLocation[1], userLocation[0]],
                    [shop.lng, shop.lat]
                ]);
                const feature = data.features[0];
                const coords  = feature.geometry.coordinates;
                const summary = feature.properties.summary;
                const steps = getRouteSteps(feature);
                const latLngs = coords.map(c => [c[1], c[0]]);
                routeLatLngs = latLngs;

                // Update polyline
                if (routePolyline) routePolyline.setLatLngs(latLngs);
                else routePolyline = L.polyline(latLngs, { color: '#4A6CF7', weight: 6, opacity: 0.85, lineCap: 'round', lineJoin: 'round' }).addTo(map);

                updateSnappedRouteMarker();

                // Update info di sidebar
                showRouteInfo(summary.distance, summary.duration, shop.nama, steps);
                if (summary.distance <= 50) {
                    setRouteUI(true, `Anda sudah dekat ${shop.nama}`);
                } else if (!offRoute) {
                    setRouteUI(true, `Tracking ke ${shop.nama}`);
                }

            } catch (e) {
                console.warn('Update rute realtime gagal:', e);
            } finally {
                routeUpdateInFlight = false;
            }
        }

        // 4. REAL-TIME TRACKING
        function startRealTimeTracking(barberIndex) {
            currentRouteState.tracking = true;
            currentRouteState.barberIndex = barberIndex;
            const shop = stores[barberIndex];
            refreshUserMarkerIcon();

            const routeBtn = document.querySelector('.route-button[data-shop-index="' + barberIndex + '"]');
            if (routeBtn) {
                routeBtn.textContent = 'Hentikan Tracking';
                routeBtn.dataset.state = 'tracking';
                routeBtn.style.background = '#e74c3c';
            }
            if (shop) setRouteUI(true, `Tracking ke ${shop.nama}`);
            lastRouteCalculation = { lat: null, lng: null };
            lastRouteUpdateAt = 0;
            updateSnappedRouteMarker();
            updateRouteRealtime(barberIndex);
            console.log('Real-time tracking started for barber index:', barberIndex);
        }

        // 4a. STOP REAL-TIME TRACKING
        function stopRealTimeTracking() {
            currentRouteState.tracking = false;
            refreshUserMarkerIcon();
            updateSnappedRouteMarker();
            const shop = currentRouteState.barberIndex !== null ? stores[currentRouteState.barberIndex] : null;
            if (shop) setRouteUI(true, `Rute ke ${shop.nama}`);
            console.log('Real-time tracking stopped');
        }

        function addRouteButtonToCards() {
            const storeCards = document.querySelectorAll('.store-card');
            storeCards.forEach((card) => {
                const originalIndex = parseInt(card.dataset.index); // index asli di array stores
                const body = card.querySelector('.store-card-body');

                if (!body.querySelector('.route-button')) {
                    const routeBtn = document.createElement('button');
                    routeBtn.className = 'route-button';
                    routeBtn.dataset.shopIndex = originalIndex;
                    routeBtn.dataset.state = 'initial';
                    routeBtn.textContent = 'Lihat Rute';
                    routeBtn.type = 'button';

                    routeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const shop = stores[originalIndex];
                        if (!shop) return;

                        const state = routeBtn.dataset.state;

                        if (state === 'initial') {
                            lihatRuteKeBarber(shop.lat, shop.lng, shop.nama, originalIndex);
                        } else if (state === 'ready') {
                            startRealTimeTracking(originalIndex);
                        } else if (state === 'tracking') {
                            stopRealTimeTracking();
                            routeBtn.dataset.state = 'ready';
                            routeBtn.textContent = 'Mulai Tracking';
                            routeBtn.style.background = '#4A6CF7';
                        }
                    });

                    body.appendChild(routeBtn);
                }
            });
        }

        // 5. UPDATE ROUTE BUTTONS AFTER RENDERING
        const originalRenderStores = renderStores;
        renderStores = function(order) {
            originalRenderStores.call(this, order);
            addRouteButtonToCards();
        };

        // ========== INITIALIZATION ==========
        console.log('Initializing GIS Features...');

        // Load kelurahan layer
        loadKelurahanLayer();

        // Initialize store list dengan route buttons
        renderStores(sortIndexes(currentFilter));
        if (stores.length) selectStore(sortIndexes(currentFilter)[0]);

        if (gpsToggleButton) {
            gpsToggleButton.addEventListener('click', trackUserLocation);
        }

        if (clearRouteButton) {
            clearRouteButton.addEventListener('click', clearRoute);
        }

        // Log initialization complete
        setTimeout(() => {
            console.log('All features initialized');
        }, 2000);

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (geolocationWatchId) {
                navigator.geolocation.clearWatch(geolocationWatchId);
            }
        });
