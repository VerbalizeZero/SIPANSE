@if ($paginator->hasPages())
    {{-- Root komponen pagination: hanya dirender kalau total halaman lebih dari 1 --}}
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">

        {{-- Layout MOBILE (sm ke bawah): hanya tombol Previous/Next, tanpa nomor halaman --}}
        <div class="flex gap-2 items-center justify-between sm:hidden">

            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-400 bg-slate-100 border border-slate-300 cursor-not-allowed leading-5 rounded-md">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 rounded-md hover:text-slate-800 hover:bg-slate-100 focus:outline-none focus:ring ring-slate-200 focus:border-slate-300 active:bg-slate-200 active:text-slate-800 transition ease-in-out duration-150">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 rounded-md hover:text-slate-800 hover:bg-slate-100 focus:outline-none focus:ring ring-slate-200 focus:border-slate-300 active:bg-slate-200 active:text-slate-800 transition ease-in-out duration-150">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-400 bg-slate-100 border border-slate-300 cursor-not-allowed leading-5 rounded-md">
                    {!! __('pagination.next') !!}
                </span>
            @endif

        </div>

        {{-- Layout DESKTOP (sm ke atas): tampilkan ringkasan "Showing..." + nomor halaman --}}
        <div class="hidden sm:flex sm:flex-col sm:items-center sm:gap-2">

            {{-- Ringkasan data halaman saat ini: contoh "Showing 1 to 10 of 120 results" --}}
            <div class="flex justify-center">
                <p class="text-sm text-slate-600 leading-5">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            {{-- Kontainer tombol-tombol pagination (prev, nomor halaman, next) --}}
            <div class="flex justify-center">
                <span class="inline-flex rtl:flex-row-reverse shadow-sm rounded-md">

                    {{-- Previous Page Link:
                         - Jika di halaman pertama: disabled
                         - Jika bukan halaman pertama: link ke halaman sebelumnya --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="inline-flex items-center px-2 py-2 text-sm font-medium text-slate-400 bg-slate-100 border border-slate-300 cursor-not-allowed rounded-l-md leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-2 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-l-md leading-5 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring ring-slate-200 focus:border-slate-300 active:bg-slate-200 active:text-slate-700 transition ease-in-out duration-150" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements:
                         Laravel mengirim $elements berisi:
                         1) string "..." (separator),
                         2) array [pageNumber => url] untuk link angka halaman.
                         Bentuk ini dipengaruhi oleh onEachSide(). --}}
                    @foreach ($elements as $element)
                        {{-- Separator "..." untuk menandakan ada range halaman yang disembunyikan --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-500 bg-slate-50 border border-slate-300 cursor-default leading-5">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array nomor halaman yang benar-benar ditampilkan --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                {{-- Halaman aktif: bukan link, hanya badge/label aktif --}}
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-800 bg-slate-200 border border-slate-300 cursor-default leading-5">{{ $page }}</span>
                                    </span>
                                {{-- Halaman non-aktif: clickable link --}}
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 hover:text-slate-800 hover:bg-slate-100 focus:outline-none focus:ring ring-slate-200 focus:border-slate-300 active:bg-slate-200 active:text-slate-800 transition ease-in-out duration-150" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link:
                         - Jika masih ada halaman berikutnya: aktif
                         - Jika sudah halaman terakhir: disabled --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-r-md leading-5 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring ring-slate-200 focus:border-slate-300 active:bg-slate-200 active:text-slate-700 transition ease-in-out duration-150" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-slate-400 bg-slate-100 border border-slate-300 cursor-not-allowed rounded-r-md leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
