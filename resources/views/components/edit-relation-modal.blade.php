<div wire:show="isRelEditModalOpen" x-data="{ relationType: @entangle('relation_type').live }"
    x-transition.duration.200ms x-on:click.self="$wire.isRelEditModalOpen=false"
    class="fixed top-0 left-0 flex items-center justify-center w-full h-full bg-gray-500 bg-opacity-50 z-50">

    <x-code-generator::modal modalTitle="Update Eloquent Relation">

        <!-- Modal header -->
        <x-slot:closebtn>
            <button x-on:click="$wire.isRelEditModalOpen=false"
                class="text-gray-500 hover:text-black text-xl">&times;</button>
        </x-slot:closebtn>
        <p class="text-xs italic text-gray-500 mt-1">Note: This foreign key data is required for generating the base
            model file. </p>
        <div class="flex flex-col gap-4">
            <div class="flex flex-col">
                <select class="w-full p-2 border border-gray-300 rounded-md" wire:model.live="relation_type">
                    <x-code-generator::relation-option />
                </select>
                @error('relation_type')
                <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="space-y-3">
                <div class="flex gap-2">
                    <div class="w-1/2">

                        <!-- Related Model Name -->
                        @if (!empty($this->modelNames))
                        <select wire:model.live="related_model"
                            class="w-full border border-gray-300 rounded-md p-2 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500">
                            <option value="">-- Related Model --</option>
                            @foreach ($this->modelNames as $table)
                            <option value="{{ $table }}">{{ $table }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="text" placeholder="Related Model" wire:model.live="related_model"
                            class="w-full border border-gray-300 rounded-md p-2 placeholder:text-gray-400 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500" />
                        @endif
                        @error('related_model') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-1/2">
                        <!-- Intermediate Model Name -->
                        @if (!empty($this->modelNames))
                        <select wire:model.live="intermediate_model"
                            class="w-full border border-gray-300 rounded-md p-2 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500"
                            :disabled="!['Has One Through', 'Has Many Through'].includes(relationType)"
                            :class="{ 'bg-gray-100 text-gray-400': !['Has One Through', 'Has Many Through'].includes(relationType) }">
                            <option value=""> -- Intermediate Model --</option>
                            @foreach ($this->modelNames as $table)
                            <option value="{{ $table }}">{{ $table }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="text" wire:model.live="intermediate_model" placeholder="Intermediate Model"
                            class="w-full p-2 border border-gray-300 rounded-md placeholder:text-base"
                            :disabled="!['Has One Through', 'Has Many Through'].includes(relationType)"
                            :class="{ 'bg-gray-100 text-gray-400': !['Has One Through', 'Has Many Through'].includes(relationType) }" />
                        @endif
                        @error('intermediate_model')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-2">
                    <!-- Foreign Key Input -->
                    <div class="w-1/2">
                        @if (!empty($this->columnNames))
                        <select wire:model.live="foreign_key"
                            class="w-full border border-gray-300 rounded-md p-2 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500">
                            <option value="">-- Foreign Key --</option>
                            @foreach ($this->columnNames as $field)
                            <option value="{{ $field }}">{{ $field }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="text" wire:model.live="foreign_key" placeholder="Foreign Key on Related model"
                            class="w-full p-2 border border-gray-300 rounded-md placeholder:text-base" />
                        @endif
                        @error('foreign_key')
                        <span class="block mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Local Key Input -->
                    <div class="w-1/2">
                        <input type="text" wire:model.live="local_key" placeholder="Local Key on Base model"
                            class="w-full p-2 border border-gray-300 rounded-md placeholder:text-base" />
                        @error('local_key')
                        <span class="block mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Extra keys -->
                <div x-show="['Has One Through', 'Has Many Through'].includes($wire.relation_type)" class="flex gap-2">
                    <!--Intermediate Foreign Key -->
                    <div class="w-1/2">
                        @if (!empty($this->intermediateFields))
                        <select wire:model.live="intermediate_foreign_key"
                            class="w-full border border-gray-300 rounded-md p-2 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500">
                            <option value="">-- Intermediate Foreign Key --</option>
                            @foreach ($this-> intermediateFields as $field)
                            <option value="{{ $field }}">{{ $field }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="text" wire:model.live="intermediate_foreign_key"
                            placeholder="Intermediate Foreign Key"
                            class="w-full p-2 border border-gray-300 rounded-md placeholder:text-base" />
                        @endif
                        @error('intermediate_foreign_key')
                        <span class="block mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Intermediate Local Key -->
                    <div class="w-1/2">
                        @if (!empty($this->intermediateFields))
                        <select wire:model.live="intermediate_local_key"
                            class="w-full border border-gray-300 rounded-md p-2 text-gray-700 focus:ring focus:ring-indigo-100 focus:border-indigo-500">
                            <option value=""> -- Intermediate Local Key --</option>
                            @foreach ($this-> intermediateFields as $field)
                            <option value="{{ $field }}">{{ $field }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="text" wire:model.live="intermediate_local_key" placeholder="Intermediate Local Key"
                            class="w-full p-2 border border-gray-300 rounded-md placeholder:text-base" />
                        @endif
                        @error('intermediate_local_key')
                        <span class="block mt-1 text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal footer -->
        <x-slot:footer>
            <div class="mr-6">
                <x-code-generator::button title="Cancel" x-on:click="$wire.isRelEditModalOpen=false" />
            </div>
            <x-code-generator::button wire:click="saveRelation" title="Update" />
        </x-slot:footer>
        </x-modal>
</div>