<?php
$treinBestanden = array_values(array_filter(
    scandir(__DIR__ . '/../assets/trein/'),
    fn($f) => (bool) preg_match('/\.(png|jpg|gif|svg|webp)$/i', $f)
));
$treinJson = json_encode($treinBestanden);
$treinPad  = ($rootPath ?? './') . 'assets/trein/';
?>
<div id="trein-baan" style="position:fixed;bottom:0;left:0;width:100%;height:80px;pointer-events:none;overflow:hidden;z-index:50;"></div>
<script>
(function () {
    const treinen = <?= $treinJson ?>;
    const baan    = document.getElementById('trein-baan');

    function rijdTrein() {
        if (!treinen.length) return;

        const naam      = treinen[Math.floor(Math.random() * treinen.length)];
        const naarRecht = Math.random() < 0.5;
        const img       = new Image();

        img.onload = function () {
            const schaal = Math.min(1, 70 / img.naturalHeight);
            const w      = Math.round(img.naturalWidth  * schaal);
            const h      = Math.round(img.naturalHeight * schaal);
            const duur   = 7000 + Math.random() * 5000;

            Object.assign(img.style, {
                position:  'absolute',
                bottom:    '4px',
                height:    h + 'px',
                width:     w + 'px',
                transform: 'scaleX(' + (naarRecht ? 1 : -1) + ')',
            });

            const vanX  = naarRecht ? -w                  : window.innerWidth;
            const naarX = naarRecht ? window.innerWidth   : -w;
            img.style.left = vanX + 'px';
            baan.appendChild(img);

            const start = performance.now();
            (function stap(nu) {
                const t = Math.min((nu - start) / duur, 1);
                img.style.left = (vanX + (naarX - vanX) * t) + 'px';
                if (t < 1) {
                    requestAnimationFrame(stap);
                } else {
                    img.remove();
                    setTimeout(rijdTrein, 2000 + Math.random() * 5000);
                }
            })(start);
        };

        img.src = '<?= $treinPad ?>' + naam;
    }

    setTimeout(rijdTrein, 1000 + Math.random() * 3000);
})();
</script>
</body>
</html>
