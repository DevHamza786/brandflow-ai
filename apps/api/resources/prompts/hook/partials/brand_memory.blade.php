{{-- Compact brand personalization — avoids full memory_chunks dump (anti-bloat). --}}
@if(!empty($compact_brand_memory))
{!! $compact_brand_memory !!}
@endif
