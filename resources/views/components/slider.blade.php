@props(['name', 'label', 'min' => 0, 'max' => 10, 'value' => 5, 'step' => 1, 'suffix' => '', 'unit' => '', 'hint' => ''])

<div class="bs-slider w-full min-w-0">
    <div class="flex items-center justify-between mb-1.5">
        <label class="text-sm font-medium text-slate-700 truncate">{{ $label }}</label>
        <span class="text-sm font-semibold text-primary-600 flex-shrink-0 ml-2">
            <span class="slider-val">{{ old($name, $value) }}</span>{{ $suffix }}{{ $unit }}
        </span>
    </div>
    <input type="range"
           name="{{ $name }}"
           min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
           value="{{ old($name, $value) }}"
           class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-primary-600">
    @if($hint)
    <div class="flex justify-between text-xs text-slate-400 mt-1">
        <span class="truncate">{{ explode('→', $hint)[0] ?? '' }}</span>
        <span class="truncate">{{ explode('→', $hint)[1] ?? '' }}</span>
    </div>
    @else
    <div class="flex justify-between text-xs text-slate-400 mt-1">
        <span>{{ $min }}</span><span>{{ $max }}</span>
    </div>
    @endif
</div>
