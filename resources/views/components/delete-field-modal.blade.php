<div wire:show="isDeleteFieldModalOpen" x-data x-transition.duration.200ms
    x-on:click.self="$wire.isDeleteFieldModalOpen=false"
    class="fixed top-0 left-0 flex items-center justify-center w-full h-full bg-gray-500 bg-opacity-50 z-50">
    <x-code-generator::modal modalTitle="Delete Field">

        <x-slot:closebtn>
            <button x-on:click="$wire.isDeleteFieldModalOpen=false"
                class="text-gray-500 hover:text-black text-xl">&times;</button>
        </x-slot:closebtn>
            <div class="mt-4 space-y-4">
               Are You Sure you want to delete this field?
            </div>

        <x-slot:footer>
            <div class="mr-6">
            <x-code-generator::button title="Cancel" x-on:click="$wire.isDeleteFieldModalOpen=false"/>
          </div>
          <x-code-generator::button wire:click="deleteField" title="Delete" />
        </x-slot:footer>
    </x-code-generator::modal>
</div>