@props([
'title',
'showPlus' => false,
'loadingTarget' => null,
])

<button {{ $attributes->merge(['class' => 'bg-red-500 text-white py-2 pl-3 pr-4 rounded-lg flex items-center
    justify-center relative']) }}>

    @if ($loadingTarget)
    <svg wire:loading wire:target="{{ $loadingTarget }}" class="animate-spin h-5 w-5 mr-2 text-white"
        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
    @endif

    <span wire:loading.remove wire:target="{{ $loadingTarget }}">
        @if($showPlus)
        <span class="mr-1">+</span>
        @endif
        {{ $title }}
    </span>
</button>