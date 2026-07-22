<?php

use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;
use Symfony\Component\HttpFoundation\StreamedResponse;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    protected function backupFolder(): string
    {
        return config('backup.backup.name');
    }

    /**
     * All local backup zip files, newest first.
     */
    protected function backupFiles(): array
    {
        $disk = Storage::disk('local');
        $folder = $this->backupFolder();

        return collect($disk->files($folder))
            ->filter(fn (string $path) => str_ends_with($path, '.zip'))
            ->map(fn (string $path) => [
                'path' => $path,
                'name' => basename($path),
                'size' => Number::fileSize($disk->size($path), precision: 2),
                'date' => Jalalian::forge($disk->lastModified($path))->format('Y/m/d H:i'),
                'timestamp' => $disk->lastModified($path),
            ])
            ->when($this->search, fn ($files) => $files->filter(
                fn (array $file) => str_contains($file['name'], $this->search)
            ))
            ->sortByDesc('timestamp')
            ->values()
            ->all();
    }

    #[Computed]
    public function backups(): LengthAwarePaginator
    {
        $files = $this->backupFiles();
        $perPage = config('general.per_page', 10);
        $page = Paginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            array_slice($files, ($page - 1) * $perPage, $perPage),
            count($files),
            $perPage,
            $page,
        );
    }

    /**
     * Make sure the given path points to a backup file and nothing else.
     */
    protected function validatePath(string $path): bool
    {
        return str_starts_with($path, $this->backupFolder().'/')
            && ! str_contains($path, '..')
            && str_ends_with($path, '.zip')
            && Storage::disk('local')->exists($path);
    }

    public function download(string $path): ?StreamedResponse
    {
        if (! $this->validatePath($path)) {
            Flux::toast(variant: 'danger', text: __('general.backup_not_found'));

            return null;
        }

        Log::info('Backup downloaded.', ['path' => $path, 'user_id' => auth()->id()]);

        return Storage::disk('local')->download($path);
    }

    public function delete(string $path): void
    {
        if (! $this->validatePath($path)) {
            Flux::toast(variant: 'danger', text: __('general.backup_not_found'));

            return;
        }

        Storage::disk('local')->delete($path);

        Log::info('Backup deleted.', ['path' => $path, 'user_id' => auth()->id()]);

        unset($this->backups);

        $this->resetPage();

        Flux::toast(__('general.backup_deleted'));
    }

    public function deleteAll(): void
    {
        $paths = array_column($this->backupFiles(), 'path');

        Storage::disk('local')->delete($paths);

        Log::info('All backups deleted.', ['count' => count($paths), 'user_id' => auth()->id()]);

        unset($this->backups);

        $this->resetPage();

        Flux::toast(__('general.backups_deleted'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[On('panels.administrator.system-management.backup.index.refresh')]
    public function refresh(): void
    {
        unset($this->backups);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.backups') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" />
                <flux:breadcrumbs.item>{{ __('general.system_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.backups') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="flex items-center gap-2">
                <flux:button variant="primary" color="red" icon="trash" wire:click="deleteAll" wire:confirm="{{ __('general.are_you_sure') }}">
                    {{ __('general.delete_all_backups') }}
                </flux:button>
                <flux:button variant="primary" color="teal" icon="plus" wire:click="$dispatch('panels.administrator.system-management.backup.create.assign-data')">
                    {{ __('actions.create') }} {{ __('general.backup') }}
                </flux:button>
            </div>
        </div>

        <flux:card>
            <div class="mb-4 flex items-center justify-between gap-4">
                <flux:input wire:model.live.debounce.300ms="search" icon="search" placeholder="{{ __('general.search') }}..." class="max-w-xs" />
                <flux:tooltip content="{{ __('general.refresh') }}">
                    <flux:button size="sm" variant="ghost" icon="refresh-cw" wire:click="refresh" />
                </flux:tooltip>
            </div>

            <flux:table :paginate="$this->backups">
                <flux:table.columns>
                    <flux:table.column>{{ __('general.file_name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.file_size') }}</flux:table.column>
                    <flux:table.column>{{ __('general.created_at') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('general.actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->backups as $backup)
                        <flux:table.row :key="$backup['path']">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:icon.archive class="size-4 text-zinc-400" />
                                    <span dir="ltr">{{ $backup['name'] }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $backup['size'] }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $backup['date'] }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    <flux:tooltip content="{{ __('general.download') }}">
                                        <flux:button size="xs" variant="primary" color="blue" icon="download" icon:variant="outline" wire:click="download('{{ $backup['path'] }}')" />
                                    </flux:tooltip>
                                    <flux:tooltip content="{{ __('general.delete') }}">
                                        <flux:button size="xs" variant="primary" color="red" icon="trash" icon:variant="outline" wire:click="delete('{{ $backup['path'] }}')" wire:confirm="{{ __('general.are_you_sure') }}" />
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" align="center">
                                {{ __('general.no_backups_found') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <livewire:system-management.backup.create :key="'backup-create'" />
</div>
