// resources/js/map-picker.js

import L from "leaflet";
import "leaflet/dist/leaflet.css";

// Fix marker icon paths when using Vite
import icon2x from "leaflet/dist/images/marker-icon-2x.png";
import icon    from "leaflet/dist/images/marker-icon.png";
import shadow  from "leaflet/dist/images/marker-shadow.png";

L.Icon.Default.mergeOptions({
    iconRetinaUrl: icon2x,
    iconUrl: icon,
    shadowUrl: shadow,
});

/* ===========================
   SIGNUP: DRAGGABLE PICKER
   data-map-picker
   =========================== */

function initMapPicker(el) {
    if (!el || el.__mapPickerInited) return;
    el.__mapPickerInited = true;

    // Regina defaults
    const startLat = parseFloat(el.dataset.lat || "50.4452");
    const startLng = parseFloat(el.dataset.lng || "-104.6189"); // fixed: negative

    const latSel      = el.dataset.latTarget;  // e.g. "[data-lat-input]"
    const lngSel      = el.dataset.lngTarget;  // e.g. "[data-lng-input]"
    const locateBtnSel= el.dataset.locateBtn;  // e.g. "#use-my-location"

    const latInput = latSel ? document.querySelector(latSel) : null;
    const lngInput = lngSel ? document.querySelector(lngSel) : null;

    const map = L.map(el, { zoomControl: true }).setView([startLat, startLng], 11);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>',
        maxZoom: 19,
    }).addTo(map);

    const marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);

    function setLatLng(lat, lng) {
        if (latInput) {
            latInput.value = Number(lat).toFixed(6);
            latInput.dispatchEvent(new Event("input", { bubbles: true }));
        }
        if (lngInput) {
            lngInput.value = Number(lng).toFixed(6);
            lngInput.dispatchEvent(new Event("input", { bubbles: true }));
        }
    }

    marker.on("dragend", () => {
        const { lat, lng } = marker.getLatLng();
        setLatLng(lat, lng);
    });

    map.on("click", (e) => {
        const { lat, lng } = e.latlng;
        marker.setLatLng([lat, lng]);
        setLatLng(lat, lng);
    });

    map.whenReady(() => {
        map.invalidateSize();
        setTimeout(() => map.invalidateSize(), 80);
    });

    if (locateBtnSel) {
        const btn = document.querySelector(locateBtnSel);
        btn?.addEventListener("click", () => {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition((pos) => {
                const { latitude, longitude } = pos.coords;
                map.setView([latitude, longitude], 13);
                marker.setLatLng([latitude, longitude]);
                setLatLng(latitude, longitude);
            });
        });
    }
}

function bootPickers() {
    document.querySelectorAll("[data-map-picker]").forEach(initMapPicker);
}

/* ===========================
   LISTINGS MODAL: READONLY VIEW
   data-map-view
   =========================== */

function initMapView(el) {
    if (!el || el.__mapViewInited) return;
    el.__mapViewInited = true;

    const lat    = parseFloat(el.dataset.lat || "0");
    const lng    = parseFloat(el.dataset.lng || "0");
    const radius = parseInt(el.dataset.radius || "1500", 10);

    if (Number.isNaN(lat) || Number.isNaN(lng) || (lat === 0 && lng === 0)) return;

    const map = L.map(el, { zoomControl: true, attributionControl: true })
        .setView([lat, lng], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OSM",
        maxZoom: 19,
    }).addTo(map);

    // Privacy circle only (no exact marker)
    L.circle([lat, lng], {
        radius,
        weight: 1,
        fillOpacity: 0.15,
    }).addTo(map);

    map.whenReady(() => {
        map.invalidateSize();
        setTimeout(() => map.invalidateSize(), 50);
    });
}

function bootViews() {
    document.querySelectorAll("[data-map-view]").forEach(initMapView);
}

/* ===========================
   BOOTSTRAP HOOKS
   =========================== */

// First load
document.addEventListener("DOMContentLoaded", () => {
    bootPickers();
    bootViews();
});

// Livewire lifecycles
document.addEventListener("livewire:load", () => {
    bootPickers();
    bootViews();
});
document.addEventListener("livewire:init", () => {
    bootPickers();
    bootViews();
});
document.addEventListener("livewire:navigated", () => {
    bootPickers();
    bootViews();
});

// Custom events from components
// - Signup step 2 triggers this (picker)
document.addEventListener("map:init", bootPickers);
// - Listings modal triggers this (viewer)
document.addEventListener("detail-map:init", bootViews);

// MutationObserver fallback (handles partial renders)
const mo = new MutationObserver((muts) => {
    for (const m of muts) {
        if (m.addedNodes?.length) {
            for (const n of m.addedNodes) {
                if (n.nodeType !== 1) continue;

                if (n.matches?.("[data-map-picker]")) {
                    initMapPicker(n);
                } else if (n.matches?.("[data-map-view]")) {
                    initMapView(n);
                }

                if (n.querySelectorAll) {
                    n.querySelectorAll("[data-map-picker]").forEach(initMapPicker);
                    n.querySelectorAll("[data-map-view]").forEach(initMapView);
                }
            }
        }
    }
});
mo.observe(document.documentElement, { childList: true, subtree: true });
