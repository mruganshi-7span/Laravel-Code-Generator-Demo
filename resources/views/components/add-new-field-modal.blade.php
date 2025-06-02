<div wire:show="isAddFieldModalOpen" x-data x-transition.duration.200ms
    x-on:click.self="$wire.isAddFieldModalOpen=false"
    class="fixed top-0 left-0 flex items-center justify-center w-full h-full bg-gray-500 bg-opacity-50 z-50">
    @csrf

    <x-code-generator::modal modalTitle="Add Field">

        <x-slot:closebtn>
            <button x-on:click="$wire.isAddFieldModalOpen=false"
                class="text-gray-500 hover:text-black text-xl">&times;</button>
        </x-slot:closebtn>

        <div class="mt-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Column Name</label>
                <input type="text" placeholder="Enter Name" wire:model.live="column_name"
                    class="w-full border rounded-md p-2 placeholder:text-gray-400 placeholder:text-[16px]" />
                @error('column_name')
                <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
                <p class="text-xs italic text-gray-500 mt-1">Note: Add without special characters</p>
            </div>

            <!-- Data Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Type</label>
                <select id="column_type" class="w-full border rounded-md p-2" name="data_type"
                    wire:model.live="data_type">
                    <x-code-generator::data-type-option />
                </select>
                @error('data_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Validation -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Validation</label>
                <select class="form-control w-full border rounded-md p-2" wire:model.live="column_validation"
                    id="column_validation" name="column_validation">
                    <option value="">Select one</option>
                    <option value="nullable">Nullable</option>
                    <option value="required">Required</option>
                    <option value="unique">Unique</option>
                    <option value="email">Email</option>
                </select>
                @error('column_validation') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Foreign Key Option -->
            <div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="isForeignKey" class="form-checkbox text-indigo-600">
                    <span class="text-sm text-gray-800">Make it a foreign key?</span>
                </div>
                @error('isForeignKey') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>


            @if($this->isForeignKey)

            <div class="bg-white border border-gray-200 rounded-2xl shadow-md p-6 mt-6">

                <!-- Related Table Name -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Related Table Name</label>

                    @if (!empty($this->tableNames))
                    <select wire:model.live="foreignModelName"
                        class="w-full border border-gray-300 rounded-md p-2 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500">
                        <option value="">-- Select Table --</option>
                        @foreach ($this->tableNames as $table)
                        <option value="{{ $table }}">{{ $table }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="text" placeholder="users" wire:model.live="foreignModelName"
                        class="w-full border border-gray-300 rounded-md p-2 placeholder:text-gray-400 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500" />
                    <p class="text-xs italic text-gray-500 mt-1">Note: use plural form, e.g., <code>users</code></p>
                    @endif

                    @error('foreignModelName') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Referenced Column -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Referenced Column</label>
                    @if (!empty($this->fieldNames))
                    <select wire:model.live="referencedColumn"
                        class="w-full border border-gray-300 rounded-md p-2 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500">
                        <option value="">-- Select Field --</option>
                        @foreach ($this->fieldNames as $field)
                        <option value="{{ $field }}">{{ $field }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="text" placeholder="user_id" wire:model.live="referencedColumn"
                        class="w-full border border-gray-300 rounded-md p-2 placeholder:text-gray-400 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500" />
                    @endif

                    @error('referencedColumn') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- ON UPDATE -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">On Update Action</label>
                        <select wire:model.live="onUpdateAction"
                            class="w-full border border-gray-300 rounded-md p-2 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500">
                            <option value="">Select</option>
                            <option value="cascade">Cascade</option>
                            <option value="set null">Set Null</option>
                            <option value="restrict">Restrict</option>
                            <option value="no action">No Action</option>
                        </select>
                        @error('onUpdateAction') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- ON DELETE -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">On Delete Action</label>
                        <select wire:model.live="onDeleteAction"
                            class="w-full border border-gray-300 rounded-md p-2 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500">
                            <option value="">Select</option>
                            <option value="cascade">Cascade</option>
                            <option value="set null">Set Null</option>
                            <option value="restrict">Restrict</option>
                            <option value="no action">No Action</option>
                        </select>
                        @error('onDeleteAction') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            @endif
        </div>

        <x-slot:footer>
            <div class="mr-6">
                <x-code-generator::button title="Cancel" x-on:click="$wire.isAddFieldModalOpen=false" />
            </div>
            <x-code-generator::button wire:click="saveField" title="Add" />
        </x-slot:footer>
    </x-code-generator::modal>
</div>