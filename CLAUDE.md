# Werkafspraken

Verzameld uit de `CLAUDE.md`-bestanden in `C:\Program Files\Ampps\www` (hulpdiensten, mijnpeutergroep, multi-app).

## Communicatie

- Antwoord in het Nederlands, kort en praktisch; focus op wat belangrijk is, leg geen voor de hand liggende dingen uit.
- Vraag het bij twijfel over wat de gebruiker bedoelt (bijv. met de vraag-tool) in plaats van te gokken.

## Git

- **Commit alleen als de gebruiker erom vraagt**, **push alleen met expliciete toestemming**.
- Commitberichten in het Nederlands, altijd met een body (wat er gewijzigd is en waarom), **zonder `Co-Authored-By`-regel**.
- Controleer vóór elke commit dat er geen geheimen (sleutels, wachtwoorden, e-mailadressen, persoonlijke id's) in de gestagede bestanden staan (`git grep --cached`).

## Code

- Volg de bestaande conventies van het project: kijk bij het maken of wijzigen van een bestand naar vergelijkbare bestanden voor structuur, aanpak en naamgeving.
- Gebruik beschrijvende namen voor variabelen en methodes.
- Kijk eerst of er een bestaand onderdeel is dat je kunt hergebruiken voordat je iets nieuws schrijft.
- Houd de bestaande mappenstructuur aan; maak geen nieuwe hoofdmappen zonder toestemming.
- Wijzig geen dependencies zonder toestemming.
- Maak alleen documentatiebestanden als de gebruiker daarom vraagt.

## Testen

- Maak geen losse verificatiescripts als tests de functionaliteit al dekken; tests zijn belangrijker.
- Verwijder geen tests zonder toestemming.

## Deployen via zip

- Maak een deploy-zip met de Windows-tar: `/c/Windows/System32/tar.exe -a -c -f deploy-JJJJ-MM-DD.zip <bestanden>` (mapstructuur behouden).
- Gebruik **niet** `Compress-Archive` (schrijft paden met `\`) en **niet** de `tar` uit Git Bash (maakt een tar-bestand met de extensie .zip).
- Controleer na het maken dat het bestand met `PK` begint en dat de paden `/` gebruiken.
- Verwijder een oude deploy-zip pas als de gebruiker daarom vraagt.
