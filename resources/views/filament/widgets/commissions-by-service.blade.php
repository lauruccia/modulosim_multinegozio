<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Commissioni per servizio
        </x-slot>
        <x-slot name="description">
            Aggiornato in tempo reale · Dati visibili per il tuo ruolo
        </x-slot>

        @php $rows = $this->getRows(); @endphp

        @if(empty($rows))
            <div class="text-center py-8 text-gray-400 text-sm">
                Nessuna pratica trovata. Le commissioni appariranno non appena verranno registrate le prime richieste.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Servizio</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Pratiche</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Attivate</th>
                            <th class="text-center py-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">In attesa</th>
                            <th class="text-right py-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Maturate</th>
                            <th class="text-right py-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Confermate</th>
                            <th class="text-right py-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Liquidate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($rows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $icons = [
                                                'sim'      => '📱',
                                                'luce'     => '💡',
                                                'gas'      => '🔥',
                                                'luce_gas' => '⚡',
                                                'altro'    => '🔧',
                                            ];
                                        @endphp
                                        <span>{{ $icons[$row['type']] ?? '📋' }}</span>
                                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center text-gray-600 dark:text-gray-400">
                                    {{ $row['total'] }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                        {{ $row['activated'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($row['pending'] > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300">
                                            {{ $row['pending'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $this->formatMoney($row['matured']) }}
                                </td>
                                <td class="py-3 px-3 text-right text-gray-600 dark:text-gray-400">
                                    {{ $this->formatMoney($row['confirmed']) }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if($row['paid'] > 0)
                                        <span class="font-semibold text-green-600 dark:text-green-400">
                                            {{ $this->formatMoney($row['paid']) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Totali --}}
                    @php
                        $totMatured   = array_sum(array_column($rows, 'matured'));
                        $totConfirmed = array_sum(array_column($rows, 'confirmed'));
                        $totPaid      = array_sum(array_column($rows, 'paid'));
                        $totTotal     = array_sum(array_column($rows, 'total'));
                        $totActivated = array_sum(array_column($rows, 'activated'));
                    @endphp
                    <tr class="border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30 font-semibold">
                        <td class="py-3 px-3 text-xs uppercase tracking-wider text-gray-500">Totale</td>
                        <td class="py-3 px-3 text-center text-gray-700 dark:text-gray-300">{{ $totTotal }}</td>
                        <td class="py-3 px-3 text-center text-gray-700 dark:text-gray-300">{{ $totActivated }}</td>
                        <td class="py-3 px-3"></td>
                        <td class="py-3 px-3 text-right text-gray-800 dark:text-gray-200">{{ $this->formatMoney($totMatured) }}</td>
                        <td class="py-3 px-3 text-right text-gray-600 dark:text-gray-400">{{ $this->formatMoney($totConfirmed) }}</td>
                        <td class="py-3 px-3 text-right text-green-600 dark:text-green-400">{{ $this->formatMoney($totPaid) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
