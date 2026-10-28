// Vliegradar: vliegtuigen in beeld elke 5 seconden bijwerken, als icoon dat in de vliegrichting draait.
// Klik op een vliegtuig voor de gegevens, de route en het spoor dat het sinds het openen van de pagina vloog.
const kaartVak = document.getElementById('kaart');
const statusRegel = document.getElementById('status');
const zoekVeld = document.getElementById('zoek');
const grondKeuze = document.getElementById('grond');
const paneel = document.getElementById('paneel');

const VERVERS_MS = 5 * 1000;
const MAX_STRAAL_NM = 250;
const SPOOR_PUNTEN = 200;      // zoveel posities per vliegtuig onthouden voor het spoor
const NEDERLAND = { midden: [52.2, 5.3], zoom: 8 };
const VOET_PER_METER = 3.28084;

let kaart = null;
let vliegtuigen = new Map();   // id → { gegevens, marker }
let sporen = new Map();        // id → [[lat, lon], …]
let gekozen = null;            // id van het vliegtuig in het paneel
let spoorLijn = null;
let ververser = null;
let laadNummer = 0;

// ---------- Hulpjes ----------

function getal(waarde, decimalen = 0) {
    return waarde.toLocaleString('nl-NL', { maximumFractionDigits: decimalen });
}

function kleurVoorHoogte(vliegtuig) {
    if (vliegtuig.nood) {
        return '#dc2626';
    }
    if (vliegtuig.grond || vliegtuig.hoogte === null) {
        return '#9ca3af';
    }
    const meter = vliegtuig.hoogte / VOET_PER_METER;
    if (meter < 1000) return '#84cc16';
    if (meter < 3000) return '#14b8a6';
    if (meter < 6000) return '#3b82f6';
    if (meter < 9000) return '#8b5cf6';
    return '#ec4899';
}

function herinnerBeeld() {
    try { return JSON.parse(localStorage.getItem('vliegradar-beeld')) || NEDERLAND; } catch (e) { return NEDERLAND; }
}

function onthoudBeeld() {
    const midden = kaart.getCenter();
    try {
        localStorage.setItem('vliegradar-beeld', JSON.stringify({ midden: [midden.lat, midden.lng], zoom: kaart.getZoom() }));
    } catch (e) {}
}

// ---------- Iconen ----------

// Eenvoudig vliegtuigsilhouet, neus naar boven; draait mee met de koers.
const VLIEGTUIG_PAD = 'M12 2c.8 0 1.3.9 1.3 2v5.2l7.7 4.6v2l-7.7-2.3v4.6l2.3 1.7v1.6L12 20.3l-3.6 1.1v-1.6l2.3-1.7v-4.6L3 15.8v-2l7.7-4.6V4c0-1.1.5-2 1.3-2z';

function icoon(vliegtuig, geselecteerd) {
    const grootte = vliegtuig.grond ? 16 : 24;
    const houder = document.createElement('div');
    houder.className = 'vliegtuig' + (geselecteerd ? ' gekozen' : '') + (vliegtuig.nood ? ' nood' : '');
    houder.innerHTML = `<svg viewBox="0 0 24 24" width="${grootte}" height="${grootte}"><path d="${VLIEGTUIG_PAD}"/></svg>`;
    const svg = houder.firstChild;
    svg.style.transform = `rotate(${vliegtuig.koers ?? 0}deg)`;
    svg.style.fill = kleurVoorHoogte(vliegtuig);
    return L.divIcon({ className: 'vliegtuig-marker', html: houder, iconSize: [grootte, grootte] });
}

// Icoon bijwerken zonder het te vervangen: een nieuw element zou niet vloeiend naar de nieuwe plek schuiven.
// Alleen bij landen/opstijgen (andere grootte) maken we een nieuw icoon.
function werkIcoonBij(item, vliegtuig, geselecteerd) {
    const houder = item.marker.getElement()?.querySelector('.vliegtuig');
    if (!houder || item.gegevens.grond !== vliegtuig.grond) {
        item.marker.setIcon(icoon(vliegtuig, geselecteerd));
        return;
    }
    houder.className = 'vliegtuig' + (geselecteerd ? ' gekozen' : '') + (vliegtuig.nood ? ' nood' : '');
    const svg = houder.firstChild;
    svg.style.transform = `rotate(${vliegtuig.koers ?? 0}deg)`;
    svg.style.fill = kleurVoorHoogte(vliegtuig);
}

// ---------- Ophalen ----------

// Midden van het kaartbeeld en de straal tot de verste hoek, in zeemijl (max. 250).
function gebied() {
    const midden = kaart.getCenter();
    const hoek = kaart.getBounds().getNorthEast();
    const straal = Math.min(MAX_STRAAL_NM, Math.ceil(midden.distanceTo(hoek) / 1852));
    return { lat: midden.lat.toFixed(3), lon: midden.lng.toFixed(3), straal };
}

async function laad() {
    const nummer = ++laadNummer;
    const { lat, lon, straal } = gebied();
    try {
        const antwoord = await fetch(`api.php?actie=vliegtuigen&lat=${lat}&lon=${lon}&straal=${straal}`);
        const gegevens = await antwoord.json();
        if (!antwoord.ok) {
            throw new Error(gegevens.fout || 'Er ging iets mis.');
        }
        if (nummer !== laadNummer) {
            return; // de kaart is intussen verschoven
        }
        toon(gegevens.vliegtuigen);
        const zichtbaar = [...vliegtuigen.values()].length;
        const tekst = document.createElement('span');
        tekst.className = 'live-teken';
        tekst.textContent = '● Live';
        statusRegel.replaceChildren(tekst, ` · ${getal(zichtbaar)} vliegtuigen · bijgewerkt om ${gegevens.tijd}`
            + (straal >= MAX_STRAAL_NM ? ' · zoom in: verder dan 250 zeemijl van het midden wordt niets getoond' : ''));
    } catch (e) {
        statusRegel.textContent = e.message;
    }
}

function startVerversen() {
    clearInterval(ververser);
    laad();
    ververser = setInterval(laad, VERVERS_MS);
}

// ---------- Tonen ----------

function pastBijZoeken(vliegtuig) {
    const zoekterm = zoekVeld.value.trim().toUpperCase();
    if (!zoekterm) {
        return true;
    }
    return [vliegtuig.vlucht, vliegtuig.reg, vliegtuig.type, vliegtuig.id.toUpperCase()].some(waarde => waarde.toUpperCase().includes(zoekterm));
}

function toon(lijst) {
    const gezien = new Set();
    for (const vliegtuig of lijst) {
        if (vliegtuig.grond && !grondKeuze.checked) {
            continue;
        }
        if (!pastBijZoeken(vliegtuig)) {
            continue;
        }
        gezien.add(vliegtuig.id);

        const spoor = sporen.get(vliegtuig.id) || [];
        const laatste = spoor[spoor.length - 1];
        if (!laatste || laatste[0] !== vliegtuig.p[0] || laatste[1] !== vliegtuig.p[1]) {
            spoor.push(vliegtuig.p);
            sporen.set(vliegtuig.id, spoor.slice(-SPOOR_PUNTEN));
        }

        const bestaand = vliegtuigen.get(vliegtuig.id);
        if (bestaand) {
            werkIcoonBij(bestaand, vliegtuig, vliegtuig.id === gekozen);
            bestaand.gegevens = vliegtuig;
            bestaand.marker.setLatLng(vliegtuig.p);
        } else {
            const marker = L.marker(vliegtuig.p, { icon: icoon(vliegtuig, vliegtuig.id === gekozen), title: vliegtuig.vlucht || vliegtuig.reg })
                .on('click', () => kies(vliegtuig.id))
                .addTo(kaart);
            vliegtuigen.set(vliegtuig.id, { gegevens: vliegtuig, marker });
        }
    }

    for (const [id, { marker }] of vliegtuigen) {
        if (!gezien.has(id)) {
            marker.remove();
            vliegtuigen.delete(id);
        }
    }
    if (gekozen) {
        vulPaneel();
    }
}

// ---------- Paneel ----------

function zet(id, tekst) {
    document.getElementById(id).textContent = tekst || '–';
}

async function kies(id) {
    const vorige = gekozen;
    gekozen = id;
    for (const ander of [vorige, id]) {
        const item = vliegtuigen.get(ander);
        if (item) {
            werkIcoonBij(item, item.gegevens, ander === gekozen);
        }
    }
    paneel.hidden = false;
    zet('p-maatschappij', '');
    document.getElementById('p-route').replaceChildren();
    vulPaneel();

    const vlucht = vliegtuigen.get(id)?.gegevens.vlucht;
    if (!vlucht) {
        return;
    }
    zet('p-maatschappij', 'Route opzoeken…');
    try {
        const antwoord = await fetch('api.php?actie=route&vlucht=' + encodeURIComponent(vlucht));
        const route = await antwoord.json();
        if (gekozen !== id) {
            return; // intussen een ander vliegtuig gekozen
        }
        zet('p-maatschappij', route.maatschappij || (route.gevonden ? '' : 'Geen route bekend voor dit vluchtnummer'));
        if (route.van && route.naar) {
            toonRoute(route.van, route.naar);
        }
    } catch (e) {
        zet('p-maatschappij', 'Route kon niet worden opgezocht');
    }
}

function toonRoute(van, naar) {
    const haven = (luchthaven, rol) => {
        const blok = document.createElement('div');
        blok.className = 'haven';
        const code = document.createElement('strong');
        code.textContent = luchthaven.code;
        const naam = document.createElement('span');
        naam.textContent = luchthaven.plaats || luchthaven.naam;
        naam.title = luchthaven.naam + (luchthaven.land ? ', ' + luchthaven.land : '');
        const soort = document.createElement('small');
        soort.textContent = rol;
        blok.append(soort, code, naam);
        return blok;
    };
    const pijl = document.createElement('div');
    pijl.className = 'pijl';
    pijl.textContent = '✈';
    document.getElementById('p-route').replaceChildren(haven(van, 'van'), pijl, haven(naar, 'naar'));
}

function vulPaneel() {
    const item = vliegtuigen.get(gekozen);
    if (!item) {
        // Uit beeld of geland en verdwenen: paneel laten staan met de laatste gegevens, maar zonder spoor.
        spoorLijn?.remove();
        return;
    }
    const v = item.gegevens;
    zet('p-vlucht', v.vlucht || v.reg || v.id.toUpperCase());
    zet('p-type', v.type + (v.militair ? ' (militair)' : ''));
    zet('p-reg', v.reg);
    zet('p-hoogte', v.grond ? 'Op de grond' : v.hoogte !== null
        ? `${getal(v.hoogte)} ft (${getal(Math.round(v.hoogte / VOET_PER_METER / 10) * 10)} m)` : '');
    zet('p-snelheid', v.snelheid !== null ? `${getal(v.snelheid)} kn (${getal(Math.round(v.snelheid * 1.852))} km/u)` : '');
    zet('p-koers', v.koers !== null ? `${Math.round(v.koers)}°` : '');
    zet('p-stijgen', v.stijgen ? `${v.stijgen > 0 ? '↑' : '↓'} ${getal(Math.abs(v.stijgen))} ft/min` : (v.grond ? '' : 'vlak'));
    zet('p-squawk', v.squawk + (v.nood ? ' – noodcode!' : ''));
    zet('p-hex', v.id.toUpperCase());

    const spoor = sporen.get(gekozen) || [];
    if (!spoorLijn) {
        spoorLijn = L.polyline(spoor, { color: kleurVoorHoogte(v), weight: 3, opacity: .8 });
    }
    spoorLijn.setLatLngs(spoor).setStyle({ color: kleurVoorHoogte(v) }).addTo(kaart);
}

function sluitPaneel() {
    const vorige = vliegtuigen.get(gekozen);
    gekozen = null;
    paneel.hidden = true;
    spoorLijn?.remove();
    if (vorige) {
        werkIcoonBij(vorige, vorige.gegevens, false);
    }
}

// ---------- Start ----------

if (window.L) {
    const beeld = herinnerBeeld();
    kaart = L.map(kaartVak).setView(beeld.midden, beeld.zoom);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(kaart);

    // Tijdens het zoomen geen vloeiende beweging, anders lopen de vliegtuigen achter de kaart aan.
    kaart.on('zoomstart', () => kaartVak.classList.add('zoomen'));
    kaart.on('zoomend', () => kaartVak.classList.remove('zoomen'));
    kaart.on('moveend', () => {
        onthoudBeeld();
        startVerversen();
    });

    document.getElementById('sluit').addEventListener('click', sluitPaneel);
    document.addEventListener('keydown', e => e.key === 'Escape' && sluitPaneel());
    zoekVeld.addEventListener('input', laad);
    grondKeuze.addEventListener('change', laad);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(ververser);
        } else {
            startVerversen();
        }
    });

    startVerversen();
} else {
    statusRegel.textContent = 'De kaart kon niet geladen worden (geen internet?).';
}
