{{--
    Report KPI Aggregation Cards
    Usage: @include('reports.partials._kpi_cards', ['kpis' => [...]])

    Each KPI item: ['label' => string, 'value' => string|number, 'tone' => 'positive'|'negative'|'neutral'|null]
    - label: The metric name (e.g. "Total Revenue")
    - value: Already-formatted display value (e.g. "$12,345.00")
    - tone:  Optional CSS modifier for color coding
--}}
@php
    $kpis = is_array($kpis ?? null) ? $kpis : [];
    $kpiHeading = $kpiHeading ?? __('Summary');
@endphp
@if(!empty($kpis))
<section class="rpt-kpi-grid" aria-label="{{ $kpiHeading }}" role="region">
    <span class="sr-only">{{ $kpiHeading }}</span>
    @foreach($kpis as $kpi)
        @php
            $tone = $kpi['tone'] ?? null;
            $valClass = 'rpt-kpi-value';
            if ($tone === 'positive') $valClass .= ' rpt-kpi-value--positive';
            elseif ($tone === 'negative') $valClass .= ' rpt-kpi-value--negative';
            elseif ($tone === 'neutral') $valClass .= ' rpt-kpi-value--neutral';
        @endphp
        <figure class="rpt-kpi-card" tabindex="0" role="group" aria-label="{{ $kpi['label'] ?? '' }}">
            <figcaption class="rpt-kpi-label">{{ $kpi['label'] ?? '' }}</figcaption>
            <p class="{{ $valClass }}">{{ $kpi['value'] ?? '-' }}</p>
        </figure>
    @endforeach
</section>
@endif
