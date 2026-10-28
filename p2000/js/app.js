// P2000 Live: meldingen van de gekozen regio als lijst en op de kaart, elke 15 seconden bijgewerkt.
const regioKeuze = document.getElementById('regio');
const dienstKnoppen = [...document.querySelectorAll('#diensten .dienst')];
const statusRegel = document.getElementById('status');
const lijst = document.getElementById('lijst');
const kaartVak = document.getElementById('kaart');

const VERVERS_MS = 15 * 1000;
const RECENT_S = 15 * 60;      // zo lang telt een melding als "net binnen" (grotere stip)
const OUD_S = 2 * 3600;        // ouder dan dit: vaag op de kaart
const KLEUREN = { Brandweer: '#dc2626', Ambulance: '#eab308', Politie: '#2563eb', Kustwacht: '#ea580c', KNRM: '#ea580c', Overig: '#6b7280' };
const NEDERLAND = [[50.75, 3.35], [53.55, 7.2]];

let meldingen = [];
let bekend = new Set();        // id's die al eens getoond zijn, om nieuwe te laten oplichten
let eersteKeer = true;
let ververser = null;
let kaart = null;
let stippenLaag = null;
let stippen = new Map();       // id → marker
let kaartPassen = true;        // na het kiezen van een regio één keer in- of uitzoomen

// ---------- Onthouden (per browser) ----------

function onthoud(sleutel, waarde) {
    try { localStorage.setItem('p2000-' + sleutel, JSON.stringify(waarde)); } catch (e) {}
}

function herinner(sleutel, standaard) {
    try { return JSON.parse(localStorage.getItem('p2000-' + sleutel)) ?? standaard; } catch (e) { return standaard; }
}

function gekozenDiensten() {
    return dienstKnoppen.filter(knop => knop.classList.contains('actief')).map(knop => knop.dataset.dienst);
}

function pastBijFilter(melding) {
    const diensten = gekozenDiensten();
    const dienst = melding.dienst === 'KNRM' ? 'Kustwacht' : melding.dienst;
    return diensten.includes(dienst);
}

// ---------- Ophalen ----------

async function laad() {
    try {
        const antwoord = await fetch('api.php?regio=' + encodeURIComponent(regioKeuze.value));
        const gegevens = await antwoord.json();
        if (!antwoord.ok) {
            throw new Error(gegevens.fout || 'Er ging iets mis.');
        }
        meldingen = gegevens.meldingen;
        toon();
        statusRegel.replaceChildren(
            stukje('● Live', 'live-teken'),
            ' · ' + meldingen.filter(pastBijFilter).length + ' meldingen afgelopen etmaal'
                + (gegevens.bijgewerkt ? ' · bijgewerkt om ' + gegevens.bijgewerkt : ''),
        );
    } catch (e) {
        statusRegel.textContent = e.message;
    }
}

function startVerversen() {
    clearInterval(ververser);
    laad();
    ververser = setInterval(laad, VERVERS_MS);
}

// ---------- Weergave ----------

function stukje(tekst, klasse) {
    const span = document.createElement('span');
    span.className = klasse;
    span.textContent = tekst;
    return span;
}

function tijdGeleden(tijd) {
    const seconden = Math.floor(Date.now() / 1000) - tijd;
    if (seconden < 60) {
        return 'net';
    }
    if (seconden < 3600) {
        return Math.floor(seconden / 60) + ' min geleden';
    }
    return Math.floor(seconden / 3600) + ' uur geleden';
}

function klok(tijd) {
    return new Date(tijd * 1000).toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' });
}

function prioKlasse(prio) {
    if (/^(A1|P1)$/.test(prio)) {
        return 'prio hoog';
    }
    if (/^(A2|P2)$/.test(prio)) {
        return 'prio midden';
    }
    return 'prio laag';
}

function toon() {
    const zichtbaar = meldingen.filter(pastBijFilter);
    const regioNamen = Object.fromEntries([...regioKeuze.options].map(optie => [optie.value, optie.textContent]));

    lijst.replaceChildren(...zichtbaar.map(melding => {
        const item = document.createElement('li');
        item.className = 'melding';
        item.style.setProperty('--dienst', KLEUREN[melding.dienst] || KLEUREN.Overig);
        if (!eersteKeer && !bekend.has(melding.id)) {
            item.classList.add('nieuw');
        }

        const kop = document.createElement('div');
        kop.className = 'melding-kop';
        kop.append(stukje(klok(melding.tijd), 'tijd'), stukje(tijdGeleden(melding.tijd), 'geleden'), stukje(melding.dienst, 'dienst-naam'));
        if (melding.prio) {
            kop.prepend(stukje(melding.prio, prioKlasse(melding.prio)));
        }

        const voet = document.createElement('div');
        voet.className = 'melding-voet';
        if (regioKeuze.value === 'alle') {
            voet.append(stukje(regioNamen[melding.regio] || 'Onbekende regio', 'regio-naam'));
        }
        if (melding.p) {
            const knop = document.createElement('button');
            knop.type = 'button';
            knop.className = 'link';
            knop.textContent = melding.ongeveer ? 'plaats op kaart' : 'op kaart';
            knop.addEventListener('click', () => toonOpKaart(melding.id));
            voet.append(knop);
        }

        item.append(kop, stukje(melding.bericht, 'bericht'), eenhedenBlok(melding), voet);
        return item;
    }));

    if (!zichtbaar.length) {
        const leeg = document.createElement('li');
        leeg.className = 'leeg';
        leeg.textContent = 'Geen meldingen voor deze keuze in het afgelopen etmaal.';
        lijst.replaceChildren(leeg);
    }

    meldingen.forEach(melding => bekend.add(melding.id));
    eersteKeer = false;
    toonKaart(zichtbaar);
}

// Wie is er gealarmeerd: roepnummers uit het bericht als labels, daaronder de capcodes met omschrijving.
// Monitor- en groepscodes alarmeren niemand specifiek; die staan alleen als aantal (met de lijst als tooltip).
function eenhedenBlok(melding) {
    const blok = document.createElement('div');
    blok.className = 'eenheden';

    if (melding.roepnummers.length) {
        const rij = document.createElement('div');
        rij.className = 'roepnummers';
        for (const roepnummer of melding.roepnummers) {
            const label = document.createElement('span');
            label.className = 'roepnummer';
            label.append(stukje(roepnummer.nummer, 'nummer'), ' ' + roepnummer.soort);
            rij.append(label);
        }
        blok.append(rij);
    }

    const echt = melding.eenheden.filter(eenheid => !eenheid.groep);
    const groep = melding.eenheden.filter(eenheid => eenheid.groep);
    if (echt.length) {
        const lijstje = document.createElement('ul');
        lijstje.className = 'capcode-lijst';
        for (const eenheid of echt) {
            const regel = document.createElement('li');
            regel.title = 'Capcode ' + eenheid.capcode;
            regel.append(eenheid.naam);
            if (eenheid.extra) {
                regel.append(stukje(' · ' + eenheid.extra, 'zacht'));
            }
            lijstje.append(regel);
        }
        blok.append(lijstje);
    }
    if (groep.length) {
        const rest = stukje('+ ' + groep.length + (groep.length === 1 ? ' monitor-/groepscode' : ' monitor-/groepscodes'), 'groepscodes');
        rest.title = groep.map(eenheid => eenheid.capcode + ' ' + eenheid.naam).join('\n');
        blok.append(rest);
    }
    return blok;
}

// ---------- Kaart ----------

function maakKaart() {
    kaart = L.map(kaartVak, { scrollWheelZoom: true }).fitBounds(NEDERLAND);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(kaart);
    stippenLaag = L.layerGroup().addTo(kaart);
}

function popupVan(melding) {
    const vak = document.createElement('div');
    vak.append(
        stukje((melding.prio ? melding.prio + ' · ' : '') + melding.dienst + ' · ' + klok(melding.tijd), 'popup-titel'),
        stukje(melding.bericht, 'popup-regel'),
    );
    const eenheden = [
        ...melding.roepnummers.map(roepnummer => roepnummer.nummer + ' ' + roepnummer.soort),
        ...melding.eenheden.filter(eenheid => !eenheid.groep).map(eenheid => eenheid.naam),
    ];
    if (eenheden.length) {
        vak.append(stukje('Gealarmeerd: ' + eenheden.join(', '), 'popup-regel zacht'));
    }
    if (melding.ongeveer) {
        vak.append(stukje('Alleen de plaats is bekend, niet het adres.', 'popup-regel zacht'));
    }
    return vak;
}

function toonKaart(zichtbaar) {
    if (!window.L) {
        kaartVak.hidden = true; // Leaflet niet geladen (bijv. offline): dan alleen de lijst
        return;
    }
    if (!kaart) {
        maakKaart();
    }

    const nu = Math.floor(Date.now() / 1000);
    stippenLaag.clearLayers();
    stippen.clear();

    // Oudste eerst tekenen, zodat de nieuwste bovenop liggen.
    const metPlek = zichtbaar.filter(melding => melding.p);
    for (const melding of [...metPlek].reverse()) {
        const leeftijd = nu - melding.tijd;
        const stip = L.circleMarker(melding.p, {
            radius: leeftijd < RECENT_S ? 10 : 6,
            weight: 2,
            color: '#fff',
            dashArray: melding.ongeveer ? '3 3' : null,
            fillColor: KLEUREN[melding.dienst] || KLEUREN.Overig,
            fillOpacity: leeftijd > OUD_S ? 0.35 : 0.9,
        }).bindPopup(popupVan(melding)).addTo(stippenLaag);
        stippen.set(melding.id, stip);
    }

    if (kaartPassen) {
        kaartPassen = false;
        if (regioKeuze.value !== 'alle' && metPlek.length) {
            kaart.fitBounds(L.latLngBounds(metPlek.map(melding => melding.p)), { padding: [30, 30], maxZoom: 12 });
        } else {
            kaart.fitBounds(NEDERLAND);
        }
    }
}

function toonOpKaart(id) {
    const stip = stippen.get(id);
    if (!stip) {
        return;
    }
    kaartVak.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    kaart.flyTo(stip.getLatLng(), Math.max(kaart.getZoom(), 13));
    stip.openPopup();
}

// ---------- Filters ----------

const bewaardeRegio = herinner('regio', null);
if ([...regioKeuze.options].some(optie => optie.value === bewaardeRegio)) {
    regioKeuze.value = bewaardeRegio;
}
const bewaardeDiensten = herinner('diensten', null);
if (bewaardeDiensten) {
    dienstKnoppen.forEach(knop => knop.classList.toggle('actief', bewaardeDiensten.includes(knop.dataset.dienst)));
}
dienstKnoppen.forEach(knop => knop.setAttribute('aria-pressed', knop.classList.contains('actief')));

regioKeuze.addEventListener('change', () => {
    onthoud('regio', regioKeuze.value);
    kaartPassen = true;
    eersteKeer = true;
    startVerversen();
});

dienstKnoppen.forEach(knop => knop.addEventListener('click', () => {
    knop.classList.toggle('actief');
    knop.setAttribute('aria-pressed', knop.classList.contains('actief'));
    onthoud('diensten', gekozenDiensten());
    toon();
}));

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        clearInterval(ververser);
    } else {
        startVerversen();
    }
});

startVerversen();
