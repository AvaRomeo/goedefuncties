<?php
$treinBestanden = array_values(array_filter(
    scandir(__DIR__ . '/../assets/trein/'),
    fn($f) => (bool) preg_match('/\.(png|jpg|gif|svg|webp)$/i', $f)
));
$treinJson = json_encode($treinBestanden);
$treinPad  = ($rootPath ?? './') . 'assets/trein/';
?>
<style>
    #trein-baan {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 80px;
        pointer-events: none;
        overflow: hidden;
        z-index: 50;
    }

    #trein-rails {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 14px;
        background: repeating-linear-gradient(90deg, #5c4030 0px, #5c4030 10px, transparent 10px, transparent 36px);
    }

    #trein-rails::before,
    #trein-rails::after {
        content: '';
        position: absolute;
        left: 0;
        width: 100%;
        height: 3px;
        background: #7a7a7a;
    }

    #trein-rails::before {
        top: 1px;
    }

    #trein-rails::after {
        top: 8px;
    }

    #trein-bovenleiding {
        position: absolute;
        inset: 0;
        bottom: 14px;
        background: repeating-linear-gradient(90deg, transparent 0, transparent 149px, #5a5a5a 149px, #5a5a5a 151px);
    }

    #trein-bovenleiding::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 1px;
        background: #bbb;
    }

    #trein-perron {
        position: absolute;
        bottom: 14px;
        left: 50%;
        width: 240px;
        height: 22px;
        transform: translateX(-50%);
        background-color: #c4bdb4;
        background-image: linear-gradient(rgba(86, 78, 72, 0.28) 0 1px, transparent 1px),
            linear-gradient(90deg, rgba(86, 78, 72, 0.28) 0 1px, transparent 1px),
            linear-gradient(180deg, #cec8be 0%, #b8b2a8 100%);
        background-size: 18px 18px, 18px 18px, 100% 100%;
        border-top: 2px solid #6e6560;
        z-index: 1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.22);
        overflow: visible;
    }

    /* Gele NS veiligheidsstrook aan de spoorrand */
    #trein-perron::before {
        content: '';
        position: absolute;
        bottom: 3px;
        left: 0;
        right: 0;
        height: 4px;
        background: #FFCD00;
    }

    /* Tactiele geleidestrook (ribbels) boven gele strook */
    #trein-perron .studs {
        position: absolute;
        left: 0;
        right: 0;
        height: 4px;
        pointer-events: none;
        opacity: 0.7;
    }

    #trein-perron .studs.top {
        display: none;
    }

    #trein-perron .studs.bottom {
        bottom: 8px;
        background: repeating-linear-gradient(90deg, #9a9390 0 3px, transparent 3px 18px);
    }

    /* Donkere betonnen perronrand */
    #trein-perron::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #5a5450;
        box-shadow: 0 2px 3px rgba(0, 0, 0, 0.25);
    }

    #trein-perron-dak {
        position: absolute;
        bottom: 100%;
        left: 15px;
        right: 15px;
        height: 12px;
        background: #2d3340;
        border-radius: 1px 1px 0 0;
    }

    #trein-perron-dak::before {
        content: '';
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(90deg, transparent 0, transparent 37px, #3d4450 37px, #3d4450 40px);
    }

    #trein-perron-dak::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: #4a5568;
    }
</style>
<style>
    #trein-baan img {
        z-index: 60;
        will-change: left;
    }

    /* Platform sign (blank blue panel with posts) */
    #perron-sign {
        position: absolute;
        bottom: 64px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 12px;
        z-index: 70;
        pointer-events: none;
    }

    #perron-sign .panel {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        height: 12px;
        background: linear-gradient(180deg, #0b4b86 0%, #072d56 100%);
        border-radius: 2px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.28);
        border: 1px solid rgba(0, 0, 0, 0.18);
    }

    #perron-sign .post {
        display: none;
    }

    /* posts positioned relative to the track container so they always reach the platform */
    .perron-post {
        position: absolute;
        bottom: 36px;
        width: 4px;
        height: 28px;
        background: #3a3a3a;
        z-index: 69;
        border-radius: 1px 1px 0 0;
    }

    .perron-post.left {
        left: calc(50% - 30px);
    }

    .perron-post.right {
        left: calc(50% + 26px);
    }
</style>
<div id="trein-baan">
    <div id="trein-bovenleiding"></div>
    <div id="trein-perron">
        <div id="trein-perron-dak"></div>
        <div class="studs top"></div>
        <div class="studs bottom"></div>
    </div>
    <div id="perron-sign">
        <div class="panel"></div>
    </div>
    <div class="perron-post left"></div>
    <div class="perron-post right"></div>
    <div id="trein-rails"></div>
</div>
<script>
    (function() {
        const treinen = <?= $treinJson ?>;
        const baan = document.getElementById('trein-baan');
        const PERRON_W = 240;

        function animeer(el, van, naar, duur, gereed) {
            const start = performance.now();
            (function stap(nu) {
                const t = Math.min((nu - start) / duur, 1);
                el.style.left = (van + (naar - van) * t) + 'px';
                t < 1 ? requestAnimationFrame(stap) : gereed();
            })(start);
        }

        function rijdTrein() {
            if (!treinen.length) return;

            const naam = treinen[Math.floor(Math.random() * treinen.length)];
            const naarRecht = Math.random() < 0.5;
            const stoptAanPerron = Math.random() < 0.35;
            const img = new Image();

            img.onload = function() {
                const schaal = Math.min(1, 70 / img.naturalHeight);
                const w = Math.round(img.naturalWidth * schaal);
                const h = Math.round(img.naturalHeight * schaal);

                Object.assign(img.style, {
                    position: 'absolute',
                    bottom: '4px',
                    height: h + 'px',
                    width: w + 'px',
                    transform: 'scaleX(' + (naarRecht ? 1 : -1) + ')',
                    zIndex: 60,
                });

                const vanX = naarRecht ? -w : window.innerWidth;
                const naarX = naarRecht ? window.innerWidth : -w;
                const rect = document.getElementById('trein-perron').getBoundingClientRect();
                // Centreer de trein: plaats het midden van de afbeelding in het midden van het perron
                const perronCenter = rect.left + rect.width / 2;
                let stopX = Math.round(perronCenter - w / 2);
                // Zorg dat stopX binnen het traject van vanX→naarX blijft
                const minX = Math.min(vanX, naarX);
                const maxX = Math.max(vanX, naarX);
                stopX = Math.min(Math.max(stopX, minX), maxX);
                const opRoute = naarRecht ? (stopX > vanX) : (stopX < vanX);
                const duur = 7000 + Math.random() * 5000;

                img.style.left = vanX + 'px';
                baan.appendChild(img);

                if (stoptAanPerron && opRoute) {
                    const deel = Math.abs(stopX - vanX) / Math.abs(naarX - vanX);
                    animeer(img, vanX, stopX, duur * deel, () => {
                        setTimeout(() => {
                            animeer(img, stopX, naarX, duur * (1 - deel), () => {
                                img.remove();
                                setTimeout(rijdTrein, 2000 + Math.random() * 5000);
                            });
                        }, 2000 + Math.random() * 2000);
                    });
                } else {
                    animeer(img, vanX, naarX, duur, () => {
                        img.remove();
                        setTimeout(rijdTrein, 2000 + Math.random() * 5000);
                    });
                }
            };

            img.src = '<?= $treinPad ?>' + naam;
        }

        setTimeout(rijdTrein, 1000 + Math.random() * 3000);
    })();
</script>
</body>

</html>