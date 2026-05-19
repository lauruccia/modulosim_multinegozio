<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Dal</label>
                <input
                    type="date"
                    wire:model.live="from"
                    class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900"
                />
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Al</label>
                <input
                    type="date"
                    wire:model.live="until"
                    class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900"
                />
            </div>

            @if (auth()->user()?->isAdmin())
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Negozio</label>
                    <select
                        wire:model.live="storeId"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900"
                    >
                        <option value="">Tutti i negozi</option>
                        @foreach ($this->stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-950">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Negozio</th>
                        <th class="px-4 py-3 text-right font-semibold">Richieste</th>
                        <th class="px-4 py-3 text-right font-semibold">Attivate</th>
                        <th class="px-4 py-3 text-right font-semibold">In corso</th>
                        <th class="px-4 py-3 text-right font-semibold">Respinte</th>
                        <th class="px-4 py-3 text-right font-semibold">Conversione</th>
                        <th class="px-4 py-3 text-right font-semibold">Fatturato</th>
                        <th class="px-4 py-3 text-right font-semibold">Comm. maturate</th>
                        <th class="px-4 py-3 text-right font-semibold">Comm. confermate</th>
                        <th class="px-4 py-3 text-right font-semibold">Comm. liquidate</th>
                        <th class="px-4 py-3 text-right font-semibold">Ore medie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($this->rows as $row)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $row['store']->name }}</td>
                            <td class="px-4 py-3 text-right">{{ $row['total'] }}</td>
                            <td class="px-4 py-3 text-right">{{ $row['activated'] }}</td>
                            <td class="px-4 py-3 text-right">{{ $row['pending'] }}</td>
                            <td class="px-4 py-3 text-right">{{ $row['rejected'] }}</td>
                            <td class="px-4 py-3 text-right">{{ $row['conversion'] }}%</td>
                            <td class="px-4 py-3 text-right">{{ $this->formatMoney($row['revenue']) }}</td>
                            <td class="px-4 py-3 text-right">{{ $this->formatMoney($row['commissions_matured']) }}</td>
                            <td class="px-4 py-3 text-right">{{ $this->formatMoney($row['commissions_confirmed']) }}</td>
                            <td class="px-4 py-3 text-right">{{ $this->formatMoney($row['commissions_paid']) }}</td>
                            <td class="px-4 py-3 text-right">{{ $row['activation_hours'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-8 text-center text-gray-500">
                                Nessun dato nel periodo selezionato.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
