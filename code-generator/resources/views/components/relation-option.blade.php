@props(['relationType'=>''])

<option value="">Select Relation</option>
@php
$relations = [
    'One to One',
    'One to Many',
    'Many to Many',
    'Has One Through',
    'Has Many Through',
    'One To One (Polymorphic)',
    'One To Many (Polymorphic)',
    'Many To Many (Polymorphic)',
];
@endphp

@foreach ($relations as $relation)
<option value="{{ $relation }}" {{ $relation===$relationType ? 'selected' : '' }}>
    {{ $relation }}
</option>
@endforeach