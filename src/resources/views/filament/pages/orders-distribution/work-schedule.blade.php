<x-filament-panels::page>
    <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="border-b border-gray-200 px-4 py-3 text-center font-medium dark:border-white/10">
            {{ $monthLabel }}
        </div>

        @if ($rows === [])
            <p class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">
                Добавьте менеджеров в расписании настроек распределения.
            </p>
        @else
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium dark:bg-gray-800">Менеджер</th>
                        @foreach ($days as $day)
                            <th class="px-2 py-2 text-center font-medium">{{ \Illuminate\Support\Carbon::parse($day)->day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="border-t border-gray-200 dark:border-white/10" wire:key="manager-{{ $row['admin_user_id'] }}">
                            <td class="sticky left-0 z-10 bg-white px-3 py-2 whitespace-nowrap dark:bg-gray-900">{{ $row['name'] }}</td>
                            @foreach ($days as $day)
                                <td class="px-2 py-2 text-center">
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300 text-primary-600 dark:border-white/20 dark:bg-gray-900"
                                        wire:model="shifts.{{ $day }}.{{ $row['admin_user_id'] }}"
                                    >
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-filament-panels::page>
