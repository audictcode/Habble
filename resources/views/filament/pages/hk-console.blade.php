<x-filament::page>
    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <div class="text-sm font-semibold text-gray-800">HK Terminal</div>
                @if($lastRunAt)
                    <div class="text-xs text-gray-500">Última ejecución: {{ $lastRunAt }}</div>
                @endif
            </div>

            <div class="p-4 space-y-3">
                <label class="block text-xs uppercase tracking-wider text-gray-500">Preset</label>
                <div class="flex flex-wrap gap-2">
                    <select wire:model="selectedCommand" class="flex-1 min-w-[320px] rounded-lg border-gray-300 bg-white text-gray-900 focus:border-primary-500 focus:ring-primary-500">
                        @foreach($this->getCommandOptions() as $commandKey => $definition)
                            <option value="{{ $commandKey }}">{{ $definition['label'] }} — {{ $definition['command'] }}</option>
                        @endforeach
                    </select>
                    <x-filament::button color="secondary" wire:click="loadPresetCommand">
                        Cargar preset
                    </x-filament::button>
                    <x-filament::button wire:click="runSelectedCommand">
                        Ejecutar preset
                    </x-filament::button>
                </div>

                <label class="block text-xs uppercase tracking-wider text-gray-500">Comando</label>
                <div class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 flex items-center gap-2">
                    <span class="text-gray-600 text-sm">$</span>
                    <input
                        wire:model.defer="commandLine"
                        wire:keydown.enter="runTypedCommand"
                        type="text"
                        class="w-full bg-transparent border-0 text-gray-900 text-sm focus:ring-0 p-0"
                        placeholder="badges:full-sync"
                    />
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-filament::button wire:click="runTypedCommand">
                        Ejecutar
                    </x-filament::button>
                    <x-filament::button color="warning" wire:click="refreshOutputFromLog">
                        Ver log
                    </x-filament::button>
                    <x-filament::button color="secondary" wire:click="clearOutput">
                        Limpiar
                    </x-filament::button>
                </div>

                <pre class="w-full min-h-[360px] rounded-lg border border-gray-200 bg-gray-50 text-gray-800 text-xs p-3 overflow-x-auto leading-5">{{ $commandOutput !== '' ? $commandOutput : 'Sin salida todavía.' }}</pre>
            </div>
        </div>
    </div>
</x-filament::page>
