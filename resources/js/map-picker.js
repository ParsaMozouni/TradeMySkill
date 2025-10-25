// resources/js/map-picker.js

import L from "leaflet";

// ✅ Fix marker icon issue with Vite (required!)
import icon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";

import shadow from "leaflet/dist/images/marker-shadow.png";
L.Icon.Default.mergeOptions({
    iconRetinaUrl: icon2x,
    iconUrl: markerIcon,
    shadowUrl: shadow,
});

function initMapPicker(el) {
    if (!el || el.__mapInited) return;
    el.__mapInited = true;

    const startLat = parseFloat(el.dataset.lat || "50.4452");
    const startLng = parseFloat(el.dataset.lng || "104.6189");
    const latSel = el.dataset.latTarget;
    const lngSel = el.dataset.lngTarget;
    const locateBtnSel = el.dataset.locateBtn;

    const latInput = document.querySelector(latSel);
    const lngInput = document.querySelector(lngSel);

    const map = L.map(el, { zoomControl: true }).setView([startLat, startLng], 11);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>',
        maxZoom: 19,
    }).addTo(map);

    const marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);

    function setLatLng(lat, lng) {
        if (latInput) { latInput.value = Number(lat).toFixed(6); latInput.dispatchEvent(new Event("input", { bubbles: true })); }
        if (lngInput) { lngInput.value = Number(lng).toFixed(6); lngInput.dispatchEvent(new Event("input", { bubbles: true })); }
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
        setTimeout(() => map.invalidateSize(), 100);
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

function bootAll() {
    document.querySelectorAll("[data-map-picker]").forEach(initMapPicker);
}

// First load
document.addEventListener("DOMContentLoaded", bootAll);

// Livewire v3 hooks (cover different lifecycles)
document.addEventListener("livewire:load", bootAll);
document.addEventListener("livewire:init", bootAll);
document.addEventListener("livewire:navigated", bootAll);

// Custom event from your component when switching to step 2
document.addEventListener("map:init", bootAll);

// Fallback: observe DOM mutations (handles partial re-renders)
const mo = new MutationObserver((muts) => {
    for (const m of muts) {
        if (m.addedNodes?.length) {
            for (const n of m.addedNodes) {
                if (n.nodeType === 1 && n.matches?.("[data-map-picker]")) {
                    initMapPicker(n);
                } else if (n.querySelectorAll) {
                    n.querySelectorAll("[data-map-picker]").forEach(initMapPicker);
                }
            }
        }
    }
});
mo.observe(document.documentElement, { childList: true, subtree: true });
