// Brugradar: bruggen op de kaart, elke minuut bijgewerkt. Rood = nu open, oranje = gaat binnenkort open.
// Klik op een brug (op de kaart of in de lijst) voor de geplande openingen en meer gegevens.
const kaartVak = document.getElementById('kaart');
const statusRegel = document.getElementById('status');
const zoekVeld = document.getElementById('zoek');
const alleenOpenKeuze = document.getElementById('alleen-open');
const paneel = document.getElementById('paneel');

const VERVERS_MS = 60 * 1000;
const KLOK_MS = 15 * 1000;          // aftellen ("opent over 3 min") tussendoor bijwerken
const BINNENKORT_S = 15 * 60;       // oranje op de kaart
const LIJST_BINNENKORT_S = 30 * 60; // in de lijst "gaat binnenkort open"
const NEDERLAND = { midden: [52.2, 5.3], zoom: 8 };

let kaart = null;
let bruggen = new Map();   // id → { gegevens, marker }
let gekozen = null;        // id van de brug in het paneel
let klokVerschil = 0;      // servertijd − tijd van deze computer, in ms
let ververser = null;

// ---------- Hulpjes ----------

function nu() {
    return Math.floor((Date.now() + klokVerschil) / 1000);
}

function duur(seconden) {
    const minuten = Math.max(1, Math.round(seconden / 60));
    if (minuten < 60) {
        return `${minuten} min`;
    }
    const uren = Math.floor(minuten / 60);
    return minuten % 60 ? `${uren} u ${minuten % 60} min` : `${uren} u`;
}

function klokTijd(tijdstip) {
    const datum = new Date(tijdstip * 1000);
    const tekst = datum.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' });
    const vandaag = new Date((nu()) * 1000).toDateString();
    if (datum.toDateString() === vandaag) {
        return tekst;
    }
    const morgen = new Date((nu() + 24 * 3600) * 1000).toDateString();
    return (datum.toDateString() === morgen ? 'morgen ' : datum.toLocaleDateString('nl-NL', { weekday: 'short' }) + ' ') + tekst;
}

// Eerste geplande opening die nog niet voorbij is (null als er geen is).
function volgendeOpening(brug) {
    return brug.gepland.find(([begin, eind]) => eind === null || eind > nu()) || null;
}

// open: sensor meldt open · binnenkort: geplande opening nu of binnen 15 min · dicht: de rest
function toestand(brug) {
    if (brug.openSinds) {
        return 'open';
    }
    const volgende = volgendeOpening(brug);
    return volgende && volgende[0] - nu() <= BINNENKORT_S ? 'binnenkort' : 'dicht';
}

function toestandTekst(brug) {
    if (brug.openSinds) {
        return `Open sinds ${klokTijd(brug.openSinds)} (${duur(nu() - brug.openSinds)})`;
    }
    const volgende = volgendeOpening(brug);
    if (!volgende) {
        return 'Dicht · geen opening gepland';
    }
    if (volgende[0] <= nu()) {
        return 'Opening nu gepland, nog niet gemeld';
    }
    return `Dicht · opent over ${duur(volgende[0] - nu())} (${klokTijd(volgende[0])})`;
}

function pastBijFilter(brug) {
    if (alleenOpenKeuze.checked && !brug.openSinds) {
        return false;
    }
    const zoekterm = zoekVeld.value.trim().toLowerCase();
    return !zoekterm || [brug.naam, brug.plaats].some(waarde => waarde.toLowerCase().includes(zoekterm));
}

function herinnerBeeld() {
    try { return JSON.parse(localStorage.getItem('brugradar-beeld')) || NEDERLAND; } catch (e) { return NEDERLAND; }
}

function onthoudBeeld() {
    const midden = kaart.getCenter();
    try {
        localStorage.setItem('brugradar-beeld', JSON.stringify({ midden: [midden.lat, midden.lng], zoom: kaart.getZoom() }));
    } catch (e) {}
}

// ---------- Ophalen ----------

async function laad() {
    try {
        const antwoord = await fetch('api.php');
        const gegevens = await antwoord.json();
        if (!antwoord.ok) {
            throw new Error(gegevens.fout || 'Er ging iets mis.');
        }
        klokVerschil = gegevens.nu * 1000 - Date.now();
        bewaar(gegevens.bruggen);
        toon();
        const tekst = document.createElement('span');
        tekst.className = 'live-teken';
        tekst.textContent = '● Live';
        statusRegel.replaceChildren(tekst, ` · ${gegevens.bruggen.length} bruggen · bijgewerkt om ${gegevens.tijd}`);
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

function icoon(brug) {
    const geselecteerd = brug.id === gekozen ? ' gekozen' : '';
    return L.divIcon({
        className: 'brug-marker',
        html: `<span class="stip ${toestand(brug)}${geselecteerd}"></span>`,
        iconSize: [16, 16],
    });
}

function bewaar(lijst) {
    const gezien = new Set();
    for (const brug of lijst) {
        gezien.add(brug.id);
        const bestaand = bruggen.get(brug.id);
        if (bestaand) {
            bestaand.gegevens = brug;
        } else {
            const marker = L.marker(brug.p, { title: brug.naam }).on('click', () => kies(brug.id));
            bruggen.set(brug.id, { gegevens: brug, marker });
        }
    }
    for (const [id, { marker }] of bruggen) {
        if (!gezien.has(id)) {
            marker.remove();
            bruggen.delete(id);
        }
    }
}

// Kaart, lijsten en paneel opnieuw opbouwen; ook elke 15 s voor het aftellen.
function toon() {
    const open = [];
    const binnenkort = [];
    for (const { gegevens: brug, marker } of bruggen.values()) {
        if (!pastBijFilter(brug)) {
            marker.remove();
            continue;
        }
        marker.setIcon(icoon(brug));
        marker.setZIndexOffset({ open: 2000, binnenkort: 1000, dicht: 0 }[toestand(brug)]);
        marker.addTo(kaart);

        const volgende = volgendeOpening(brug);
        if (brug.openSinds) {
            open.push(brug);
        } else if (volgende && volgende[0] - nu() <= LIJST_BINNENKORT_S) {
            binnenkort.push(brug);
        }
    }

    open.sort((a, b) => b.openSinds - a.openSinds);
    binnenkort.sort((a, b) => volgendeOpening(a)[0] - volgendeOpening(b)[0]);
    vulLijst('lijst-open', open, 'Geen bruggen open');
    vulLijst('lijst-binnenkort', binnenkort, 'Het komende half uur niets gepland');
    document.getElementById('aantal-open').textContent = open.length || '';

    if (gekozen) {
        vulPaneel();
    }
}

function vulLijst(id, lijst, leegTekst) {
    const regels = lijst.map(brug => {
        const knop = document.createElement('button');
        knop.type = 'button';
        knop.className = 'bruglijst-item' + (brug.id === gekozen ? ' gekozen' : '');
        knop.addEventListener('click', () => kies(brug.id, true));

        const stip = document.createElement('i');
        stip.className = 'stip ' + toestand(brug);
        const naam = document.createElement('strong');
        naam.textContent = brug.naam;
        const uitleg = document.createElement('span');
        const volgende = volgendeOpening(brug);
        uitleg.textContent = [brug.plaats, brug.openSinds
            ? `open sinds ${duur(nu() - brug.openSinds)}`
            : volgende[0] <= nu() ? 'nu gepland' : `over ${duur(volgende[0] - nu())}`].filter(Boolean).join(' · ');

        knop.append(stip, naam, uitleg);
        const regel = document.createElement('li');
        regel.append(knop);
        return regel;
    });
    if (!regels.length) {
        const leeg = document.createElement('li');
        leeg.className = 'leeg';
        leeg.textContent = leegTekst;
        regels.push(leeg);
    }
    document.getElementById(id).replaceChildren(...regels);
}

// ---------- Paneel ----------

function zet(id, tekst) {
    document.getElementById(id).textContent = tekst || '–';
}

function kies(id, naartoe = false) {
    gekozen = id;
    paneel.hidden = false;
    const brug = bruggen.get(id)?.gegevens;
    if (naartoe && brug) {
        kaart.setView(brug.p, Math.max(kaart.getZoom(), 14));
    }
    toon();
}

function vulPaneel() {
    const item = bruggen.get(gekozen);
    if (!item) {
        sluitPaneel();
        return;
    }
    const brug = item.gegevens;
    zet('p-naam', brug.naam);
    zet('p-plaats', brug.plaats);
    const regel = document.getElementById('p-toestand');
    regel.className = 'toestand ' + toestand(brug);
    regel.textContent = toestandTekst(brug);

    const komend = brug.gepland.filter(([begin, eind]) => eind === null || eind > nu());
    const regels = komend.map(([begin, eind]) => {
        const li = document.createElement('li');
        li.textContent = klokTijd(begin) + (eind ? ` – ${klokTijd(eind).replace(/^\D+ /, '')}` : '');
        return li;
    });
    if (!regels.length) {
        const li = document.createElement('li');
        li.className = 'leeg';
        li.textContent = 'Niets gepland (deze brug meldt alleen als hij open is)';
        regels.push(li);
    }
    document.getElementById('p-gepland').replaceChildren(...regels);

    zet('p-openingen', brug.openingen ? String(brug.openingen) : '');
    zet('p-bediening', brug.opAfstand ? 'Op afstand' : 'Ter plaatse of onbekend');
    zet('p-telefoon', brug.telefoon);
    zet('p-code', brug.id);
}

function sluitPaneel() {
    gekozen = null;
    paneel.hidden = true;
    toon();
}

// ---------- Start ----------

if (window.L) {
    const beeld = herinnerBeeld();
    kaart = L.map(kaartVak).setView(beeld.midden, beeld.zoom);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(kaart);
    kaart.on('moveend', onthoudBeeld);

    document.getElementById('sluit').addEventListener('click', sluitPaneel);
    document.addEventListener('keydown', e => e.key === 'Escape' && !paneel.hidden && sluitPaneel());
    zoekVeld.addEventListener('input', toon);
    alleenOpenKeuze.addEventListener('change', toon);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(ververser);
        } else {
            startVerversen();
        }
    });

    startVerversen();
    setInterval(() => !document.hidden && toon(), KLOK_MS);
} else {
    statusRegel.textContent = 'De kaart kon niet geladen worden (geen internet?).';
}
