// OV Live kaart: alle bussen, trams, metro's en boten in beeld, en de haltes als je inzoomt.
// Alleen wat in beeld is wordt opgehaald; uitgezoomd als stipjes (canvas), ingezoomd als labels.
const kaartVak = document.getElementById('grote-kaart');
const statusRegel = document.getElementById('status');

const VERVERS_MS = 10 * 1000;
const LABELS_VANAF_ZOOM = 14;
const HALTES_VANAF_ZOOM = 15;
const OUD_NA_S = 180; // posities ouder dan dit worden vaag getoond
const KLEUR_PER_SOORT = { Bus: '#475569', Tram: '#b45309', Metro: '#be123c', Boot: '#0e7490', Trein: '#1d4ed8' };
const NEDERLAND = { midden: [52.2, 5.3], zoom: 8 };

let kaart = null;
let tekenaar = null;               // canvas voor de stipjes: duizenden markers blijven zo vlot
let voertuigen = new Map();        // id → { marker, labels: true/false }
let halteLaag = null;
let ververser = null;
let laadNummer = 0;                // om antwoorden van een oudere aanvraag te negeren

function stukje(tekst, klasse) {
    const span = document.createElement('span');
    span.className = klasse;
    span.textContent = tekst;
    return span;
}

// localStorage kan geblokkeerd zijn (privévenster); dan beginnen we gewoon bij heel Nederland.
function laatsteBeeld() {
    try { return JSON.parse(localStorage.getItem('ov-live-kaartbeeld')) || NEDERLAND; } catch (e) { return NEDERLAND; }
}

function onthoudBeeld() {
    const midden = kaart.getCenter();
    try {
        localStorage.setItem('ov-live-kaartbeeld', JSON.stringify({ midden: [midden.lat, midden.lng], zoom: kaart.getZoom() }));
    } catch (e) {}
}

function kleurVan(voertuig) {
    return voertuig.kleur || KLEUR_PER_SOORT[voertuig.soort] || KLEUR_PER_SOORT.Bus;
}

function popupVan(voertuig) {
    const vak = document.createElement('div');
    const nu = Math.floor(Date.now() / 1000);
    vak.append(stukje(voertuig.soort + (voertuig.lijn ? ' ' + voertuig.lijn : ''), 'popup-titel'));
    vak.append(stukje(voertuig.vervoerder + (voertuig.voertuig ? ' · voertuig ' + voertuig.voertuig : ''), 'popup-regel'));
    if (voertuig.gezien) {
        vak.append(stukje('Positie van ' + Math.max(0, nu - voertuig.gezien) + ' seconden geleden', 'popup-regel zacht'));
    }
    return vak;
}

function labelIcoon(voertuig, oud) {
    const label = stukje(voertuig.lijn || '•', 'voertuig-label' + (oud ? ' oud' : ''));
    label.style.background = kleurVan(voertuig);
    if (voertuig.tekstkleur) {
        label.style.color = voertuig.tekstkleur;
    }
    return L.divIcon({ className: 'voertuig', html: label, iconSize: null });
}

function stipGrootte() {
    return kaart.getZoom() < 11 ? 3 : 5;
}

function maakMarker(voertuig, labels, oud) {
    if (labels) {
        return L.marker(voertuig.p, { icon: labelIcoon(voertuig, oud), riseOnHover: true });
    }
    return L.circleMarker(voertuig.p, {
        renderer: tekenaar, radius: stipGrootte(), weight: 1, color: '#fff',
        fillColor: kleurVan(voertuig), fillOpacity: oud ? .4 : .95,
    });
}

function toonVoertuigen(lijst) {
    const labels = kaart.getZoom() >= LABELS_VANAF_ZOOM;
    const nu = Math.floor(Date.now() / 1000);
    const gezien = new Set();

    for (const voertuig of lijst) {
        gezien.add(voertuig.id);
        const oud = voertuig.gezien && nu - voertuig.gezien > OUD_NA_S;
        const bestaand = voertuigen.get(voertuig.id);
        const uiterlijk = [voertuig.lijn, voertuig.kleur, voertuig.tekstkleur, oud].join('|');

        // Zelfde soort marker: alleen verschuiven (labels schuiven vloeiend via CSS).
        if (bestaand && bestaand.labels === labels) {
            // Label alleen vervangen als het er anders uit moet zien: een nieuw label schuift niet vloeiend mee.
            if (labels && bestaand.uiterlijk !== uiterlijk) {
                bestaand.uiterlijk = uiterlijk;
                bestaand.marker.setIcon(labelIcoon(voertuig, oud));
            }
            bestaand.marker.setLatLng(voertuig.p);
            if (!labels) {
                bestaand.marker.setRadius(stipGrootte());
            }
            bestaand.marker.setPopupContent(popupVan(voertuig));
            continue;
        }
        bestaand?.marker.remove();
        const marker = maakMarker(voertuig, labels, oud).bindPopup(popupVan(voertuig)).addTo(kaart);
        voertuigen.set(voertuig.id, { marker, labels, uiterlijk });
    }

    for (const [id, { marker }] of voertuigen) {
        if (!gezien.has(id)) {
            marker.remove();
            voertuigen.delete(id);
        }
    }
}

function toonHaltes(haltes) {
    halteLaag.clearLayers();
    for (const halte of haltes) {
        const link = document.createElement('a');
        link.href = './?halte=' + encodeURIComponent(halte.naam);
        link.textContent = 'Vertrektijden →';

        const vak = document.createElement('div');
        vak.append(stukje(halte.naam, 'popup-titel'), link);

        L.circleMarker(halte.p, {
            renderer: tekenaar, radius: 5, weight: 2, color: '#1e293b', fillColor: '#fff', fillOpacity: 1,
        }).bindTooltip(halte.naam).bindPopup(vak).addTo(halteLaag);
    }
}

async function laad(metHaltes) {
    const nummer = ++laadNummer;
    const gebied = kaart.getBounds().pad(0.2);
    const zoom = kaart.getZoom();
    const haltesOok = metHaltes && zoom >= HALTES_VANAF_ZOOM;
    const url = 'api.php?actie=kaart&gebied=' + [gebied.getSouth(), gebied.getWest(), gebied.getNorth(), gebied.getEast()]
        .map(getal => getal.toFixed(5)).join(',') + (haltesOok ? '&haltes=1' : '');

    try {
        const antwoord = await fetch(url);
        const gegevens = await antwoord.json();
        if (!antwoord.ok) {
            throw new Error(gegevens.fout || 'Er ging iets mis.');
        }
        if (nummer !== laadNummer) {
            return; // de kaart is intussen verschoven, er komt een nieuwer antwoord
        }
        toonVoertuigen(gegevens.voertuigen);
        if (haltesOok) {
            toonHaltes(gegevens.haltes);
        } else if (zoom < HALTES_VANAF_ZOOM) {
            halteLaag.clearLayers();
        }

        const aantal = gegevens.voertuigen.length.toLocaleString('nl-NL');
        statusRegel.replaceChildren(
            stukje('● Live', 'live-teken'),
            ' · ' + aantal + ' voertuigen in beeld' + (gegevens.feed ? ' · posities van ' + gegevens.feed : ''),
            zoom < HALTES_VANAF_ZOOM ? ' · zoom verder in voor de haltes' : '',
        );
    } catch (e) {
        statusRegel.textContent = e.message;
    }
}

function startVerversen() {
    clearInterval(ververser);
    laad(true);
    ververser = setInterval(() => laad(false), VERVERS_MS);
}

function mijnLocatieKnop() {
    const Knop = L.Control.extend({
        onAdd() {
            const knop = L.DomUtil.create('button', 'kaart-knop');
            knop.type = 'button';
            knop.textContent = '📍';
            knop.title = 'Naar mijn locatie';
            L.DomEvent.disableClickPropagation(knop);
            knop.addEventListener('click', () => kaart.locate({ setView: true, maxZoom: 16 }));
            return knop;
        },
    });
    return new Knop({ position: 'topleft' });
}

if (kaartVak && window.L) {
    const beeld = laatsteBeeld();
    kaart = L.map(kaartVak).setView(beeld.midden, beeld.zoom);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(kaart);
    tekenaar = L.canvas({ padding: 0.3 });
    halteLaag = L.layerGroup().addTo(kaart);
    mijnLocatieKnop().addTo(kaart);

    kaart.on('locationerror', () => {
        statusRegel.textContent = window.isSecureContext
            ? 'Je locatie kon niet bepaald worden.'
            : 'Je locatie opvragen kan alleen via localhost of https.';
    });
    // Tijdens het zoomen geen vloeiende beweging, anders lopen de labels achter de kaart aan.
    kaart.on('zoomstart', () => kaartVak.classList.add('zoomen'));
    kaart.on('zoomend', () => kaartVak.classList.remove('zoomen'));
    kaart.on('moveend', () => {
        onthoudBeeld();
        startVerversen();
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(ververser);
        } else {
            startVerversen();
        }
    });

    startVerversen();
} else if (kaartVak) {
    statusRegel.textContent = 'De kaart kon niet geladen worden (geen internet?).';
}
