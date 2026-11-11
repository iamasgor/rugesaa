@props([
    'origin' => '0 0',
    'rings' => [],
    'stroke' => '#F2F3EC',
    'opacity' => 0.12,
    'viewBox' => '0 0 900 600',
    'aspect' => 'xMidYMid slice',
])

{{-- Decorative contour-line rings from the design. $rings: list of [scale, rotateDegrees]. --}}
<svg viewBox="{{ $viewBox }}" preserveAspectRatio="{{ $aspect }}" aria-hidden="true" {{ $attributes->class('pointer-events-none absolute inset-0 size-full') }}>
    <g transform="translate({{ $origin }})" fill="none" stroke="{{ $stroke }}" stroke-opacity="{{ $opacity }}" stroke-width="1">
        @foreach ($rings as [$scale, $rotate])
            <path transform="scale({{ $scale }}) rotate({{ $rotate }})" vector-effect="non-scaling-stroke" d="M0,-100C60,-105,110,-60,105,0C100,55,70,95,10,100C-50,105,-105,70,-100,10C-95,-50,-60,-95,0,-100Z"></path>
        @endforeach
    </g>
</svg>
