<div class="w-44 flex-shrink-0 mr-8">
<a href="{{ route('code-generator.index') }}" 
       wire:navigate 
       class="py-2 pl-6 pr-2 mb-4 block text-gray-700 {{ request()->routeIs('code-generator.index') ? 'border-y border-red-500 font-medium text-red-500' : '' }}">
        Rest API
    </a>
    <a href="{{ route('code-generator.logs') }}"
       wire:navigate 
       class="py-2 pl-6 pr-2 mb-4 block text-gray-700 {{ request()->routeIs('code-generator.logs') ? 'border-y border-red-500 font-medium text-red-500' : '' }}">
        Logs
    </a>
</div>