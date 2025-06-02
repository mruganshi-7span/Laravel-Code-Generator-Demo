<div class="mb-6">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse  border border-gray-200">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left text-sm">Column</th>
                    <th class="px-4 py-2 text-left text-sm">Data Type</th>
                    <th class="px-4 py-2 text-left text-sm">Validation</th>
                    <th class="px-4 py-2 text-left text-sm">Is ForeignKey</th>
                    <th class="px-4 py-2 text-left text-sm">Related Model</th>
                    <th class="px-4 py-2 text-left text-sm">Referenced Column</th>
                    <th class="px-4 py-2 text-left text-sm">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 py-2 text-gray-600">id</td>
                    <td class="px-4 py-2 text-gray-600">auto_increment</td>
                    <td class="px-4 py-2 text-gray-600">required</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-gray-600">created_at</td>
                    <td class="px-4 py-2 text-gray-600">date_time_picker</td>
                    <td class="px-4 py-2 text-gray-600">required</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-gray-600">updated_at</td>
                    <td class="px-4 py-2 text-gray-600">date_time_picker</td>
                    <td class="px-4 py-2 text-gray-600">required</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-gray-600">created_by</td>
                    <td class="px-4 py-2 text-gray-600">string</td>
                    <td class="px-4 py-2 text-gray-600">optional</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-gray-600">updated_by</td>
                    <td class="px-4 py-2 text-gray-600">string</td>
                    <td class="px-4 py-2 text-gray-600">optional</td>
                </tr>

                <!-- Show If Soft Delete is enabled -->
                @if($softDeleteFile)
                <tr>
                    <td class="px-4 py-2 text-gray-600">deleted_by</td>
                    <td class="px-4 py-2 text-gray-600">string</td>
                    <td class="px-4 py-2 text-gray-600">optional</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-gray-600">deleted_at</td>
                    <td class="px-4 py-2 text-gray-600">date_time_picker</td>
                    <td class="px-4 py-2 text-gray-600">optional</td>
                </tr>
                @endif

                @foreach ($fieldsData as $field)
                <tr class=" even:bg-gray-100 ">
                    <td class="px-4 py-2">{{$field['column_name']}}</td>
                    <td class="px-4 py-2">{{$field['data_type']}}</td>
                    <td class="px-4 py-2">{{$field['column_validation']}}</td>
                    <td class="px-4 py-2">{{ !empty($field['isForeignKey']) ? 'yes' : 'no' }}</td>
                    <td class="px-4 py-2">{{ !empty($field['foreignModelName']) ? $field['foreignModelName'] : '-' }}
                    </td>
                    <td class="px-4 py-2">{{ !empty($field['referencedColumn']) ?$field['referencedColumn']: '-' }}
                    </td>

                    <td class="px-4 py-2">
                        <button wire:click="openDeleteFieldModal('{{ $field['id']}}')">
                            <x-code-generator::delete-svg />
                        </button>
                        <button wire:click="openEditFieldModal('{{ $field['id']}}')">
                            <x-code-generator::edit-svg />
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>