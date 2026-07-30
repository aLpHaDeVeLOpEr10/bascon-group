{{--
    The brand panel shown beside the sign-in form on screens ≥1024px.
    Purely decorative — hidden entirely on tablet and mobile.

    $eyebrow and $points are supplied by the including view.
--}}
@php
    $eyebrow = $eyebrow ?? 'BASCON Group';
    $headline = $headline ?? 'Every site, every payment, in one place.';
    $lede = $lede ?? 'Track civil and finishing materials, labour instalments and client payments across all your projects.';
    $points = $points ?? [];
@endphp
<div class="auth-brand-side" aria-hidden="true">
    <span class="auth-orb auth-orb-1"></span>
    <span class="auth-orb auth-orb-2"></span>

    <div class="auth-brand-content">
        <span class="auth-eyebrow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18" /><path d="M5 21V7l7-4 7 4v14" /><path d="M9 21v-6h6v6" />
            </svg>
            {{ $eyebrow }}
        </span>

        <h2 class="auth-headline">{{ $headline }}</h2>
        <p class="auth-lede">{{ $lede }}</p>

        @if (! empty($points))
            <ul class="auth-points">
                @foreach ($points as $point)
                    <li>
                        <span class="point-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </span>
                        <span>{{ $point }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="auth-brand-foot">© {{ date('Y') }} BASCON Group. All rights reserved.</div>
</div>
