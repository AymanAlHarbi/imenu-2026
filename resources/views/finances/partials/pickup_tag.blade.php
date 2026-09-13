{{-- iMenu 2026 — وسم طريقة الاستلام. يُستدعى مرتين: عمودًا على الشاشة العريضة، وتحت رقم الطلب على الجوال. --}}
<span class="is-tag {{ $isCar ? 'car' : '' }}">
    @if($isCar)
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 16 6.5 10h11L19 16"/><rect x="3" y="16" width="18" height="4" rx="1.5"/><circle cx="7.5" cy="20" r="1.5"/><circle cx="16.5" cy="20" r="1.5"/></svg>
        <span>{{ __('From my car') }}</span>
    @else
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h11v6a5 5 0 0 1-5 5h-1a5 5 0 0 1-5-5z"/><path d="M16 10h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M4 21h13"/></svg>
        <span>{{ __('From the cafe') }}</span>
    @endif
</span>
