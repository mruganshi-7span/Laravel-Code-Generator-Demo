<div wire:show="isNotificationModalOpen" x-data x-cloak x-transition.duration.200ms
    class="fixed top-0 left-0 flex items-center justify-center w-full h-full bg-gray-500 bg-opacity-50 z-50"
    x-on:click.self="$wire.isNotificationModalOpen=false">

    <x-code-generator::modal modalTitle="Notification">
        <x-slot:closebtn>
            <button x-on:click="$wire.isNotificationModalOpen=false"
                class="text-gray-500 hover:text-black text-xl">&times;</button>
        </x-slot:closebtn>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Class Name</label>
                <input wire:model.live="class_name" type="text" placeholder="Enter name"
                    class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" />
                @error('class_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Data</label>
                <input wire:model.live="data" type="text" placeholder="Enter Data"
                    class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" />
                @error('data') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-400 mt-1">Example: user_id: 1</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Subject</label>
                <input wire:model.live="subject" type="text" placeholder="Enter Subject"
                    class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" />
                @error('subject') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Body</label>
                <input wire:model.live="body" type="text" placeholder="Enter Text"
                    class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400" />
                @error('body') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <x-slot:footer>
            <div class="mr-6">
                <x-code-generator::button title="Cancel" x-on:click="$wire.isNotificationModalOpen=false" />
            </div>
            <x-code-generator::button wire:click="saveNotification" title="Add" />
        </x-slot:footer>
        </x-code-generator::modal>
</div>