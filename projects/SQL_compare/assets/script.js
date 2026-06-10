document.querySelectorAll('.dropzone').forEach(zone => {
    const input   = zone.querySelector('input');
    const bestand = zone.querySelector('.dz-bestand');

    input.addEventListener('change', () => {
        if (input.files.length) {
            bestand.textContent = input.files[0].name;
            zone.classList.add('gevuld');
        }
    });

    ['dragenter', 'dragover'].forEach(ev =>
        zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add('sleep'); }));
    ['dragleave', 'drop'].forEach(ev =>
        zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.remove('sleep'); }));
    zone.addEventListener('drop', e => {
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
});

const kopieer = document.getElementById('kopieer');
if (kopieer) {
    kopieer.addEventListener('click', () => {
        navigator.clipboard.writeText(document.getElementById('sql-output').value).then(() => {
            kopieer.textContent = 'Gekopieerd!';
            setTimeout(() => kopieer.textContent = 'Kopieer SQL', 1500);
        });
    });
}
