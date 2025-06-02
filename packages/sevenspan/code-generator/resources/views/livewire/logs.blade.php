<div class="p-6">
    <div class="bg-white rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                <colgroup>
                    <col style="width: 10%;"> 
                    <col style="width: 25%;"> 
                    <col style="width: 10%;">
                    <col style="width: 30%;">
                    <col style="width: 17%">
                    <col style="width: 10%;">
                </colgroup>
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-red-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-red-500 uppercase tracking-wider">
                            File Path
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-red-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-red-500 uppercase tracking-wider">
                            Message
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-red-500 uppercase tracking-wider whitespace-nowrap">
                            Is Override
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-red-500 uppercase tracking-wider">
                            Date
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 align-top break-words">
                            {{ $log->file_type }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 align-top">
                            {{ $log->file_path }}
                        </td>
                        <td class="px-6 py-4 align-top">
                            <!-- Determine the status class based on the status value -->
                            @php
                            $statusValue = $log->status;
                            if (is_object($statusValue) && enum_exists(get_class($statusValue)) &&
                                 property_exists($statusValue, 'value')) {
                                            $statusValue = $statusValue->value;
                                }
                            $statusValue = (string) $statusValue;

                            $statusClass = match ($statusValue) {
                                        'success' => 'bg-green-100 text-green-800',
                                        'error' => 'bg-red-100 text-red-800',
                                        'warning' => 'bg-yellow-100 text-yellow-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                            @endphp
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                {{ ucfirst($statusValue) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 align-top break-words">
                            {{ $log->message }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 align-top text-center">
                            {{ (int) (bool) $log->is_overwrite}}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap align-top">
                            <!-- Calculate the time difference -->
                            @php
                            $created = $log->created_at;
                            $now = now();

                            if ($created->diffInSeconds($now) < 60) { $diff=round($created->diffInSeconds($now)) . 's ago';
                            } elseif ($created->diffInMinutes($now) < 60) { $diff=round($created->
                                    diffInMinutes($now)) . 'm ago';
                            } elseif ($created->diffInHours($now) < 24) { $diff=round($created->
                                        diffInHours($now)) . 'h ago';
                            } elseif ($created->diffInDays($now) < 30) { $diff=round($created->
                                            diffInDays($now)) . 'd ago';
                            } else {
                                    $diff = round($created->diffInMonths($now)) . 'mo ago';
                            }
                            @endphp
                            {{ $diff }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            No logs found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>