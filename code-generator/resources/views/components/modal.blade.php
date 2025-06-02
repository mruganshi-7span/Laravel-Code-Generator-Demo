@props([
'modalTitle' => 'Default Title'
])

<div class="relative bg-white rounded-xl shadow-lg w-full max-w-2xl p-6">
    <!-- Modal header -->
    <div class="flex justify-between items-center border-b pb-3">
        <h3 class="text-xl font-semibold text-gray-900">
            {{ $modalTitle }}
        </h3>
        {{ $closebtn }}
    </div>

    <!-- Modal content -->
    <div class="py-4">
        {{ $slot }}
    </div>

    <!-- Modal footer -->
    <div class="pt-4 flex justify-end">
        {{ $footer }}
    </div>
</div>