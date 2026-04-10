@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            {{-- Info --}}
            <div class="text-muted small">
                <i class="fas fa-database me-1"></i>
                Menampilkan <strong>{{ $paginator->firstItem() ?? 0 }}</strong>
                sampai <strong>{{ $paginator->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $paginator->total() }}</strong> data
            </div>

            {{-- Pagination Links --}}
            <ul class="pagination mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="fas fa-chevron-left me-1"></i> Sebelumnya
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="fas fa-chevron-left me-1"></i> Sebelumnya
                        </a>
                    </li>
                @endif

                {{-- First Page --}}
                @if ($paginator->currentPage() > 3)
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    @if ($paginator->currentPage() > 4)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                @endif

                {{-- Pagination Elements --}}
                @foreach (range(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

                {{-- Last Page --}}
                @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                    @if ($paginator->currentPage() < $paginator->lastPage() - 3)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                            Selanjutnya <i class="fas fa-chevron-right ms-1"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            Selanjutnya <i class="fas fa-chevron-right ms-1"></i>
                        </span>
                    </li>
                @endif
            </ul>

            {{-- Go to Page --}}
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">
                    <i class="fas fa-arrow-right me-1"></i>Lompat ke:
                </span>
                <select class="form-select form-select-sm w-auto page-selector" style="cursor: pointer; width: 110px;">
                    @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                        <option value="{{ $paginator->url($i) }}" {{ $i == $paginator->currentPage() ? 'selected' : '' }}>
                            Halaman {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
        </div>
    </nav>
@else
    <div class="text-center text-muted py-3">
        <i class="fas fa-info-circle me-1"></i> Menampilkan semua data ({{ $paginator->total() }} total)
    </div>
@endif

@push('scripts')
<script>
    // Auto redirect when select changes
    document.querySelectorAll('.page-selector').forEach(select => {
        select.addEventListener('change', function() {
            window.location.href = this.value;
        });
    });
</script>
@endpush
