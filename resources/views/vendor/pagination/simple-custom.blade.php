@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="app-pagination-wrapper">
        <div class="app-pagination-mobile d-flex">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="app-page-btn disabled" aria-disabled="true" title="Halaman Sebelumnya">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="app-page-btn" aria-label="Halaman Sebelumnya" title="Halaman Sebelumnya">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                    <span>Sebelumnya</span>
                </a>
            @endif

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="app-page-btn" aria-label="Halaman Selanjutnya" title="Halaman Selanjutnya">
                    <span>Selanjutnya</span>
                    <ion-icon name="chevron-forward-outline"></ion-icon>
                </a>
            @else
                <span class="app-page-btn disabled" aria-disabled="true" title="Halaman Selanjutnya">
                    <span>Selanjutnya</span>
                    <ion-icon name="chevron-forward-outline"></ion-icon>
                </span>
            @endif
        </div>
    </nav>
@endif
