<x-filament::page>
    <x-filament::section>
        <x-slot name="heading">Daftar Host Group</x-slot>
        <x-slot name="description">Informasi singkat mengenai seluruh host group yang tersedia.</x-slot>

        @if ($this->hostGroups->isEmpty())
            <p class="text-sm text-gray-500">Belum ada group yang terdaftar.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Host</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Host Count</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Count</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($this->hostGroups as $group)
                            <tr>
                                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $group->name ?? 'Tanpa Nama' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $group->group_id }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $group->host }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $group->host_accounts_count }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $group->user_accounts_count }}
                                </td>
                                <td class="px-4 py-2">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-200' => $group->is_active,
                                        'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-200' => ! $group->is_active,
                                    ])>
                                        {{ $group->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                    @if ($group->is_default)
                                        <span class="ml-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200">
                                            Default
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                                    {{ optional($group->created_at)->format('d M Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament::page>
