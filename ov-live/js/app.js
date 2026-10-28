// OV Live: halte zoeken, het vertrekbord elke 10 seconden verversen en elke seconde aftellen,
// en de bussen die naar de halte komen live op de kaart.
const invoer = document.getElementById('halte');
const keuzelijst = document.getElementById('haltes');
const statusRegel = document.getElementById('status');
const meldingenLijst = document.getElementById('meldingen');
const bord = document.getElementById('bord');
const kaartVak = document.getElementById('kaart');
const ophaalKnop = document.getElementById('haltes-ophalen');
const ophaalStatus = document.getElementById('ophalen-status');
const favorietenBalk = document.getElementById('favorieten');
const favorietKnop = document.getElementById('favoriet');
const buurtKnop = document.getElementById('in-de-buurt');
const buurtLijst = document.getElementById('buurt');
const filterRegel = document.getElementById('filter');

const VERVERS_MS = 10 * 1000;
const WEG_NA_S = 30;           // zo lang na de verwachte vertrektijd blijft een rit nog staan
const OUD_NA_S = 180;          // posities ouder dan dit worden vaag getoond
const INZOOMEN_BINNEN_S = 600; // de kaart past zich aan op bussen die binnen 10 minuten vertrekken
const KLEUR_PER_SOORT = { Bus: '#475569', Tram: '#b45309', Metro: '#be123c', Boot: '#0e7490', Trein: '#1d4ed8' };

let huidigeHalte = null;
let ververser = null;
let zoekTimer = null;
let laatsteTreffers = [];
let ritten = [];
let bijgewerkt = '';
let haltePlek = null;
let lijnFilter = null; // lijnnummer waarop gefilterd wordt, of null voor alles

let kaart = null;
let halteMarker = null;
let voertuigMarkers = new Map(); // sleutel van de rit → marker
let kaartPassen = false;         // na het kiezen van een halte één keer in- of uitzoomen

async function vraag(url, opties) {
    const antwoord = await fetch(url, opties);
    const gegevens = await antwoord.json();
    if (!antwoord.ok) {
        throw new Error(gegevens.fout || 'Er ging iets mis.');
    }
    return gegevens;
}

// localStorage kan geblokkeerd zijn (privévenster); dan onthouden we gewoon niets.
function onthoud(halte) {
    try { localStorage.setItem('ov-live-halte', halte); } catch (e) {}
}

function herinnering() {
    try { return localStorage.getItem('ov-live-halte'); } catch (e) { return null; }
}

function laadFavorieten() {
    try { return JSON.parse(localStorage.getItem('ov-live-favorieten')) || []; } catch (e) { return []; }
}

function bewaarFavorieten(favorieten) {
    try { localStorage.setItem('ov-live-favorieten', JSON.stringify(favorieten)); } catch (e) {}
}

async function zoek() {
    const zoekterm = invoer.value.trim();
    if (zoekterm.length < 2 || laatsteTreffers.includes(invoer.value)) {
        return;
    }
    try {
        laatsteTreffers = await vraag('api.php?actie=zoek&q=' + encodeURIComponent(zoekterm));
    } catch (e) {
        return;
    }
    keuzelijst.replaceChildren(...laatsteTreffers.map(naam => {
        const optie = document.createElement('option');
        optie.value = naam;
        return optie;
    }));
}

function kies(halte) {
    huidigeHalte = halte;
    invoer.value = halte;
    onthoud(halte);
    history.replaceState(null, '', '?halte=' + encodeURIComponent(halte));

    ritten = [];
    bijgewerkt = '';
    lijnFilter = null;
    kaartPassen = true;
    buurtLijst.hidden = true;
    wisKaart();
    toonFavorieten();
    toonBord();
    statusRegel.textContent = 'Vertrektijden laden…';
    startVerversen();
}

function startVerversen() {
    clearInterval(ververser);
    laadVertrektijden();
    ververser = setInterval(laadVertrektijden, VERVERS_MS);
}

async function laadVertrektijden() {
    const halte = huidigeHalte;
    try {
        const gegevens = await vraag('api.php?actie=vertrek&halte=' + encodeURIComponent(halte));
        if (halte !== huidigeHalte) {
            return; // intussen een andere halte gekozen
        }
        ritten = gegevens.ritten;
        bijgewerkt = gegevens.bijgewerkt;
        haltePlek = gegevens.halte;
        toonMeldingen(gegevens.meldingen);
        toonBord();
        toonKaart();
    } catch (e) {
        statusRegel.textContent = e.message;
    }
}

// "nu", "2:05" (onder de 10 minuten, telt per seconde af) of "over 14 min".
function aftellen(seconden) {
    if (seconden < 30) {
        return 'nu';
    }
    if (seconden < 600) {
        return Math.floor(seconden / 60) + ':' + String(seconden % 60).padStart(2, '0');
    }
    return 'over ' + Math.floor(seconden / 60) + ' min';
}

function stukje(tekst, klasse) {
    const span = document.createElement('span');
    span.className = klasse;
    span.textContent = tekst;
    return span;
}

function cel(rij, ...inhoud) {
    const td = document.createElement('td');
    td.append(...inhoud);
    rij.append(td);
    return td;
}

function toonMeldingen(meldingen) {
    meldingenLijst.replaceChildren(...meldingen.map(tekst => {
        const li = document.createElement('li');
        li.textContent = tekst;
        return li;
    }));
    meldingenLijst.hidden = meldingen.length === 0;
}

function toonBord() {
    const tbody = bord.querySelector('tbody');
    tbody.replaceChildren();

    const nu = Math.floor(Date.now() / 1000);
    const nogNietWeg = ritten.filter(rit => rit.verwacht - nu > -WEG_NA_S);
    const zichtbaar = nogNietWeg.filter(pastBijFilter);

    for (const rit of zichtbaar) {
        const rij = document.createElement('tr');
        if (rit.geannuleerd) {
            rij.className = 'geannuleerd';
        }

        const vertrek = cel(rij, stukje(rit.tijd, 'tijd'));
        if (rit.vertraging > 0) {
            vertrek.append(stukje('+' + rit.vertraging, 'vertraging'));
        }
        const over = Math.max(0, rit.verwacht - nu);
        vertrek.append(stukje(aftellen(over), over < 30 ? 'over nu' : 'over'));

        // Klik op het lijnnummer = alleen deze lijn tonen (nog een keer klikken = alles).
        const lijn = document.createElement('button');
        lijn.type = 'button';
        lijn.className = 'lijn lijn-' + rit.soort.toLowerCase();
        lijn.textContent = rit.lijn;
        lijn.title = lijnFilter ? 'Toon alle lijnen' : 'Toon alleen lijn ' + rit.lijn;
        if (rit.kleur) {
            lijn.style.background = rit.kleur;
            lijn.style.color = rit.tekstkleur;
        }
        lijn.addEventListener('click', () => zetFilter(lijnFilter ? null : rit.lijn));
        cel(rij, lijn, stukje(rit.soort, 'soort'));
        cel(rij, rit.perron ? stukje(rit.perron, 'perron') : '').className = 'kolom-perron';

        const naar = cel(rij, stukje(rit.richting, 'richting'));
        if (rit.geannuleerd) {
            naar.append(stukje('Rijdt niet', 'melding'));
        } else if (rit.positie) {
            const knop = document.createElement('button');
            knop.type = 'button';
            knop.className = 'link op-kaart';
            knop.textContent = '● live · toon op kaart';
            knop.addEventListener('click', () => toonVoertuig(rit.sleutel));
            naar.append(knop);
        } else if (rit.live) {
            naar.append(stukje('● live', 'live'));
        }

        cel(rij, stukje(rit.vervoerder, 'vervoerder'));
        tbody.append(rij);
    }
    bord.hidden = zichtbaar.length === 0;
    bord.classList.toggle('zonder-perron', !zichtbaar.some(rit => rit.perron));
    toonFilter();

    if (bijgewerkt) {
        statusRegel.replaceChildren(...(nogNietWeg.length
            ? [stukje('● Live', 'live-teken'), ' · bijgewerkt om ' + bijgewerkt]
            : ['Er vertrekt de komende tijd niets vanaf ' + huidigeHalte + '.']));
    }
}

// ---------- Kaart ----------

function maakKaart() {
    kaartVak.hidden = false;
    kaart = L.map(kaartVak, { scrollWheelZoom: false });
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(kaart);

    // Tijdens het zoomen geen vloeiende beweging, anders lopen de bussen achter de kaart aan.
    kaart.on('zoomstart', () => kaartVak.classList.add('zoomen'));
    kaart.on('zoomend', () => kaartVak.classList.remove('zoomen'));

    // Eigen laag voor de halte, boven de bussen, zodat hij niet onder de labels verdwijnt.
    kaart.createPane('halte').style.zIndex = 650;
}

function voertuigIcoon(rit, oud) {
    const label = document.createElement('span');
    label.className = 'voertuig-label' + (oud ? ' oud' : '');
    label.textContent = rit.lijn;
    label.style.background = rit.kleur || KLEUR_PER_SOORT[rit.soort] || KLEUR_PER_SOORT.Bus;
    if (rit.tekstkleur) {
        label.style.color = rit.tekstkleur;
    }
    return L.divIcon({ className: 'voertuig', html: label, iconSize: null });
}

function voertuigPopup(rit) {
    const nu = Math.floor(Date.now() / 1000);
    const vak = document.createElement('div');
    vak.append(
        stukje(rit.soort + ' ' + rit.lijn + ' → ' + rit.richting, 'popup-titel'),
        stukje('Vertrekt hier ' + rit.tijd + (rit.vertraging > 0 ? ' (+' + rit.vertraging + ')' : ''), 'popup-regel'),
        stukje(rit.vervoerder + (rit.voertuig ? ' · voertuig ' + rit.voertuig : ''), 'popup-regel'),
    );
    if (rit.gezien) {
        vak.append(stukje('Positie van ' + Math.max(0, nu - rit.gezien) + ' seconden geleden', 'popup-regel zacht'));
    }
    return vak;
}

function toonKaart() {
    if (!window.L || !haltePlek) {
        return; // Leaflet niet geladen (bijv. offline): dan alleen het bord
    }
    if (!kaart) {
        maakKaart();
    }

    if (!halteMarker) {
        halteMarker = L.circleMarker(haltePlek, {
            pane: 'halte', radius: 8, color: '#fff', weight: 3, fillColor: '#dc2626', fillOpacity: 1,
        }).addTo(kaart);
    }
    halteMarker.setLatLng(haltePlek).bindTooltip(huidigeHalte);

    const nu = Math.floor(Date.now() / 1000);
    const metPositie = ritten.filter(rit => rit.positie && !rit.geannuleerd && pastBijFilter(rit));
    const gezien = new Set();

    for (const rit of metPositie) {
        gezien.add(rit.sleutel);
        const oud = rit.gezien && nu - rit.gezien > OUD_NA_S;
        // Icoon alleen vervangen als het er anders uit moet zien: een nieuw icoon schuift niet vloeiend mee.
        const uiterlijk = [rit.lijn, rit.kleur, rit.tekstkleur, oud].join('|');
        let marker = voertuigMarkers.get(rit.sleutel);
        if (marker) {
            if (marker.options.uiterlijk !== uiterlijk) {
                marker.options.uiterlijk = uiterlijk;
                marker.setIcon(voertuigIcoon(rit, oud));
            }
            marker.setLatLng(rit.positie);
        } else {
            marker = L.marker(rit.positie, { icon: voertuigIcoon(rit, oud), riseOnHover: true, uiterlijk }).addTo(kaart);
            voertuigMarkers.set(rit.sleutel, marker);
        }
        marker.bindPopup(voertuigPopup(rit));
    }

    // Bussen die vertrokken zijn of niet meer in de feed staan van de kaart halen.
    for (const [sleutel, marker] of voertuigMarkers) {
        if (!gezien.has(sleutel)) {
            marker.remove();
            voertuigMarkers.delete(sleutel);
        }
    }

    if (kaartPassen) {
        kaartPassen = false;
        const dichtbij = metPositie.filter(rit => rit.verwacht - nu < INZOOMEN_BINNEN_S).map(rit => rit.positie);
        if (dichtbij.length) {
            kaart.fitBounds(L.latLngBounds([haltePlek, ...dichtbij]), { padding: [30, 30], maxZoom: 16 });
        } else {
            kaart.setView(haltePlek, 15);
        }
    }
}

function toonVoertuig(sleutel) {
    const marker = voertuigMarkers.get(sleutel);
    if (!marker) {
        return;
    }
    kaartVak.scrollIntoView({ behavior: 'smooth', block: 'center' });
    kaart.flyTo(marker.getLatLng(), Math.max(kaart.getZoom(), 15));
    marker.openPopup();
}

function wisKaart() {
    for (const marker of voertuigMarkers.values()) {
        marker.remove();
    }
    voertuigMarkers.clear();
}

// ---------- Filter op lijn ----------

function pastBijFilter(rit) {
    return lijnFilter === null || rit.lijn === lijnFilter;
}

function zetFilter(lijn) {
    lijnFilter = lijn;
    toonBord();
    toonKaart();
}

function toonFilter() {
    filterRegel.hidden = lijnFilter === null;
    if (lijnFilter === null) {
        return;
    }
    const knop = document.createElement('button');
    knop.type = 'button';
    knop.className = 'link';
    knop.textContent = 'toon alle lijnen';
    knop.addEventListener('click', () => zetFilter(null));
    filterRegel.replaceChildren('Alleen lijn ' + lijnFilter + ' · ', knop);
}

// ---------- Favorieten ----------

function toonFavorieten() {
    const favorieten = laadFavorieten();
    favorietenBalk.replaceChildren(...favorieten.map(halte => {
        const chip = document.createElement('span');
        chip.className = 'favoriet' + (halte === huidigeHalte ? ' actief' : '');

        const kiesKnop = document.createElement('button');
        kiesKnop.type = 'button';
        kiesKnop.textContent = halte;
        kiesKnop.addEventListener('click', () => kies(halte));

        const wisKnop = document.createElement('button');
        wisKnop.type = 'button';
        wisKnop.className = 'wis';
        wisKnop.textContent = '×';
        wisKnop.title = 'Verwijder uit favorieten';
        wisKnop.addEventListener('click', () => {
            bewaarFavorieten(laadFavorieten().filter(f => f !== halte));
            toonFavorieten();
        });

        chip.append(kiesKnop, wisKnop);
        return chip;
    }));
    favorietenBalk.hidden = favorieten.length === 0;

    const isFavoriet = favorieten.includes(huidigeHalte);
    favorietKnop.disabled = !huidigeHalte;
    favorietKnop.textContent = isFavoriet ? '★ Favoriet' : '☆ Bewaar';
    favorietKnop.title = isFavoriet ? 'Verwijder uit favorieten' : 'Bewaar deze halte als favoriet';
}

function wisselFavoriet() {
    const favorieten = laadFavorieten();
    bewaarFavorieten(favorieten.includes(huidigeHalte)
        ? favorieten.filter(f => f !== huidigeHalte)
        : [...favorieten, huidigeHalte]);
    toonFavorieten();
}

// ---------- Haltes in de buurt ----------

function zoekInDeBuurt() {
    buurtLijst.hidden = false;
    // Locatie mag alleen via https of localhost; via een gewoon IP-adres weigert de browser het.
    if (!navigator.geolocation || !window.isSecureContext) {
        buurtLijst.textContent = 'Je locatie opvragen kan alleen via localhost of https.';
        return;
    }
    buurtLijst.textContent = 'Locatie bepalen…';
    navigator.geolocation.getCurrentPosition(async plek => {
        try {
            const haltes = await vraag('api.php?actie=buurt&lat=' + plek.coords.latitude + '&lon=' + plek.coords.longitude);
            buurtLijst.replaceChildren(...haltes.map(halte => {
                const knop = document.createElement('button');
                knop.type = 'button';
                knop.append(stukje(halte.naam, 'buurt-naam'), stukje(afstand(halte.meters), 'buurt-afstand'));
                knop.addEventListener('click', () => kies(halte.naam));
                return knop;
            }));
        } catch (e) {
            buurtLijst.textContent = e.message;
        }
    }, fout => {
        buurtLijst.textContent = fout.code === fout.PERMISSION_DENIED
            ? 'Je browser geeft je locatie niet door. Sta locatie toe voor deze pagina en probeer het opnieuw.'
            : 'Je locatie kon niet bepaald worden.';
    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 });
}

function afstand(meters) {
    return meters < 1000 ? meters + ' m' : (meters / 1000).toLocaleString('nl-NL', { maximumFractionDigits: 1 }) + ' km';
}

// ---------- Haltelijst ----------

async function haltesOphalen() {
    ophaalKnop.disabled = true;
    ophaalStatus.textContent = 'Bezig met ophalen, dit duurt ongeveer een minuut…';
    try {
        const gegevens = await vraag('api.php?actie=haltes_bijwerken', { method: 'POST' });
        ophaalStatus.textContent = gegevens.aantal.toLocaleString('nl-NL') + ' haltes opgehaald.';
        setTimeout(() => location.reload(), 1000);
    } catch (e) {
        ophaalStatus.textContent = e.message;
        ophaalKnop.disabled = false;
    }
}

ophaalKnop?.addEventListener('click', haltesOphalen);

if (invoer) {
    invoer.addEventListener('input', () => {
        if (laatsteTreffers.includes(invoer.value)) {
            kies(invoer.value); // gekozen uit de lijst
            invoer.blur();
            return;
        }
        clearTimeout(zoekTimer);
        zoekTimer = setTimeout(zoek, 200);
    });

    favorietKnop.addEventListener('click', wisselFavoriet);
    buurtKnop.addEventListener('click', zoekInDeBuurt);
    toonFavorieten();

    // Elke seconde de aftelklok bijwerken; nieuwe gegevens alleen als het tabblad zichtbaar is.
    setInterval(() => huidigeHalte && toonBord(), 1000);
    document.addEventListener('visibilitychange', () => {
        if (!huidigeHalte) {
            return;
        }
        if (document.hidden) {
            clearInterval(ververser);
        } else {
            startVerversen();
        }
    });

    const halte = new URLSearchParams(location.search).get('halte') || herinnering();
    if (halte) {
        laatsteTreffers = [halte];
        kies(halte);
    }
}
