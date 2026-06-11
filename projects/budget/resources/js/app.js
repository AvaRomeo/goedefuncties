import './bootstrap';

document.addEventListener('click', (e) => {
    const kleurKnop = e.target.closest('.kleur-knop');
    if (kleurKnop) {
        const kleur = kleurKnop.dataset.kleur;
        document.getElementById('kleur-input').value = kleur;

        document.querySelectorAll('.kleur-knop').forEach((k) => {
            k.classList.remove('border-gray-800', 'scale-110');
            k.classList.add('border-transparent');
        });

        kleurKnop.classList.add('border-gray-800', 'scale-110');
        kleurKnop.classList.remove('border-transparent');
    }

    const icoonKnop = e.target.closest('.icoon-knop');
    if (icoonKnop) {
        const icoon = icoonKnop.dataset.icoon;
        document.getElementById('icoon-input').value = icoon;

        document.querySelectorAll('.icoon-knop').forEach((k) => {
            k.classList.remove('border-indigo-500', 'bg-indigo-50', 'text-indigo-600');
            k.classList.add('border-transparent', 'bg-gray-100', 'text-gray-600');
        });

        icoonKnop.classList.add('border-indigo-500', 'bg-indigo-50', 'text-indigo-600');
        icoonKnop.classList.remove('border-transparent', 'bg-gray-100', 'text-gray-600');
    }
});
