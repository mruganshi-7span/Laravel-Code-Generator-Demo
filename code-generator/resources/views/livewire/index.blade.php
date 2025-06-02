@extends('code-generator::components.layouts.app')

@section('header')
    <x-code-generator::header />
@endsection

@section('content')
<div class='flex pl-32 pr-32 pt-8'>
    
    <x-code-generator::sidebar />

    <div class="flex-grow min-w-0 bg-white shadow-lg shadow-black/5 rounded-lg border border-grey-200 overflow-hidden">
        
        @if(request()->routeIs('code-generator.index'))
             <livewire:code-generator::rest-api />
        @elseif(request()->routeIs('code-generator.logs'))
             <livewire:code-generator::logs />
        @endif
        
    </div>
</div>

@endsection