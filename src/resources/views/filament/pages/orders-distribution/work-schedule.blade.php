<x-filament-panels::page>
    <section class="fi-section fi-section-has-header work-schedule">
        <header class="fi-section-header">
            <div class="fi-section-header-text-ctn">
                <h2 class="fi-section-header-heading">
                    {{ $monthLabel }}
                </h2>
            </div>
        </header>

        <div class="fi-section-content-ctn">
            @if ($rows === [])
                <div class="fi-section-content">
                    <p class="fi-section-header-description">
                        Добавьте менеджеров в расписании настроек распределения.
                    </p>
                </div>
            @else
                <div class="work-schedule-scroll">
                    <table class="fi-ta-table work-schedule-table">
                        <thead>
                            <tr>
                                <th class="work-schedule-manager">Менеджер</th>
                                @foreach ($days as $day)
                                    @php($date = \Illuminate\Support\Carbon::parse($day))
                                    <th @class(['work-schedule-weekend' => $date->isWeekend()])>{{ $date->day }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr wire:key="manager-{{ $row['admin_user_id'] }}">
                                    <td class="work-schedule-manager">{{ $row['name'] }}</td>
                                    @foreach ($days as $day)
                                        @php($date = \Illuminate\Support\Carbon::parse($day))
                                        <td @class(['work-schedule-weekend' => $date->isWeekend()])>
                                            <input
                                                type="checkbox"
                                                class="fi-checkbox-input"
                                                wire:model="shifts.{{ $day }}.{{ $row['admin_user_id'] }}"
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</x-filament-panels::page>
