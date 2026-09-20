{{-- Minimalist & Clean Preloader (Konsisten & Responsif Mobile, Terinspirasi dari suksesmetalabadi) --}}
<div id="app-preloader" class="app-preloader" aria-hidden="true">
    <div class="preloader-content">
        {{-- Brand Logo & Title --}}
        <div class="preloader-brand">
            <img src="{{ asset('assets/img/Logo.jpeg') }}" alt="PKBM Pikat" class="preloader-logo" />
            <div class="preloader-text-group">
                <span class="preloader-title">PKBM PIKAT</span>
                <span class="preloader-subtitle">Presensi &amp; Akademik</span>
            </div>
        </div>

        {{-- Minimalist Thin Animated Loading Bar --}}
        <div class="preloader-bar-wrap" style="width: 140px; height: 4px; border-radius: 9999px; overflow: hidden; position: relative;">
            <div class="preloader-bar-indicator" style="position: absolute; top: 0; left: 0; bottom: 0; height: 100%; width: 100%; border-radius: 9999px;"></div>
        </div>
    </div>
</div>

<script>
    (function () {
        var startTime = Date.now();
        var minDisplay = 400; // Buffer visual minimal 400ms agar animasi bar selalu tampak mengalir mulus

        function dismissPreloader() {
            var el = document.getElementById('app-preloader');
            if (el && !el.classList.contains('preloader-hidden')) {
                var elapsed = Date.now() - startTime;
                var remaining = Math.max(0, minDisplay - elapsed);

                setTimeout(function () {
                    el.classList.add('preloader-hidden');
                    setTimeout(function () {
                        if (el && el.parentNode) {
                            el.parentNode.removeChild(el);
                        }
                    }, 350);
                }, remaining);
            }
        }

        // Jalankan saat seluruh dokumen & aset selesai dimuat
        if (document.readyState === 'complete') {
            dismissPreloader();
        } else {
            window.addEventListener('load', dismissPreloader);
            // Fallback timeout maksimal 2 detik agar tidak menahan pengguna pada koneksi lambat
            setTimeout(dismissPreloader, 2000);
        }

        // Antisipasi cache navigasi browser (bfcache)
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                var el = document.getElementById('app-preloader');
                if (el && el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            }
        });
    })();
</script>
