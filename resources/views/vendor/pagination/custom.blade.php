@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="app-pagination-card">
        {{-- Info Ringkasan Data (Tampil di Desktop & Mobile) --}}
        @if (method_exists($paginator, 'total') && $paginator->total() > 0)
            <div class="app-pagination-meta">
                <span class="app-pagination-meta-icon">
                    <ion-icon name="layers-outline"></ion-icon>
                </span>
                <span class="app-pagination-meta-text">
                    Menampilkan <strong class="app-pagination-highlight">{{ $paginator->firstItem() }}</strong> &ndash; <strong class="app-pagination-highlight">{{ $paginator->lastItem() }}</strong> dari <strong class="app-pagination-highlight">{{ $paginator->total() }}</strong> data
                </span>
            </div>
        @endif

        {{-- Mobile Compact View (< 768px) --}}
        <div class="app-pagination-mobile-nav">
            @if ($paginator->onFirstPage())
                <span class="app-page-btn-mobile disabled" aria-disabled="true" title="Halaman Sebelumnya">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                    <span class="app-page-btn-text">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="app-page-btn-mobile" aria-label="Halaman Sebelumnya" title="Halaman Sebelumnya">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                    <span class="app-page-btn-text">Sebelumnya</span>
                </a>
            @endif

            <span class="app-page-badge-mobile">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="app-page-btn-mobile" aria-label="Halaman Selanjutnya" title="Halaman Selanjutnya">
                    <span class="app-page-btn-text">Selanjutnya</span>
                    <ion-icon name="chevron-forward-outline"></ion-icon>
                </a>
            @else
                <span class="app-page-btn-mobile disabled" aria-disabled="true" title="Halaman Selanjutnya">
                    <span class="app-page-btn-text">Selanjutnya</span>
                    <ion-icon name="chevron-forward-outline"></ion-icon>
                </span>
            @endif
        </div>

        {{-- Desktop / Tablet Full Numbers View (>= 640px) --}}
        <ul class="app-pagination-desktop-list">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <li class="app-page-item disabled" aria-disabled="true">
                    <span class="app-page-btn-nav">
                        <ion-icon name="chevron-back-outline"></ion-icon>
                        <span>Sebelumnya</span>
                    </span>
                </li>
            @else
                <li class="app-page-item">
                    <a class="app-page-btn-nav" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                        <ion-icon name="chevron-back-outline"></ion-icon>
                        <span>Sebelumnya</span>
                    </a>
                </li>
            @endif

            {{-- Elemen Nomor Halaman --}}
            @foreach ($elements as $element)
                {{-- String Separator --}}
                @if (is_string($element))
                    <li class="app-page-item disabled" aria-disabled="true">
                        <span class="app-page-number dots">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Link Nomor Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="app-page-item active" aria-current="page">
                                <span class="app-page-number current">{{ $page }}</span>
                            </li>
                        @else
                            <li class="app-page-item">
                                <a class="app-page-number" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <li class="app-page-item">
                    <a class="app-page-btn-nav" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Selanjutnya">
                        <span>Selanjutnya</span>
                        <ion-icon name="chevron-forward-outline"></ion-icon>
                    </a>
                </li>
            @else
                <li class="app-page-item disabled" aria-disabled="true">
                    <span class="app-page-btn-nav">
                        <span>Selanjutnya</span>
                        <ion-icon name="chevron-forward-outline"></ion-icon>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
