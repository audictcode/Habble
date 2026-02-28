<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HkConsole extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-terminal';

    protected static ?string $navigationGroup = 'Panel';

    protected static ?string $navigationLabel = 'Consola HK';

    protected static ?string $slug = 'console';

    protected static string $view = 'filament.pages.hk-console';

    public ?string $selectedCommand = null;

    public string $commandLine = '';

    public string $commandOutput = '';

    public ?string $lastRunAt = null;

    public function mount(): void
    {
        $commands = array_keys($this->getCommandOptions());
        if (!$this->selectedCommand || !in_array($this->selectedCommand, $commands, true)) {
            $this->selectedCommand = $commands[0] ?? null;
        }

        $this->loadPresetCommand();
    }

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        if (!$user) {
            return false;
        }

        return !$user->disabled && (int) $user->rank >= 7;
    }

    public function getCommandOptions(): array
    {
        return [
            'habble_list' => [
                'label' => 'Listar comandos Habble',
                'command' => 'habble:list',
                'arguments' => [],
            ],
            'academy_config' => [
                'label' => 'Limpiar caché de configuración',
                'command' => 'academy:config',
                'arguments' => [],
            ],
            'badges_import_full' => [
                'label' => 'Import badges HabboAssets (completo)',
                'command' => 'badges:import-habboassets',
                'background' => true,
                'arguments' => [
                    '--all' => true,
                    '--skip-html-metadata' => true,
                ],
            ],
            'badges_repair_metadata' => [
                'label' => 'Repair metadata badges',
                'command' => 'badges:repair-metadata',
                'background' => true,
                'arguments' => [
                    '--set-published' => true,
                ],
            ],
            'badges_full_sync' => [
                'label' => 'Full sync badges',
                'command' => 'badges:full-sync',
                'background' => true,
                'arguments' => [],
            ],
            'badges_full_sync_all' => [
                'label' => 'Full sync badges (completo)',
                'command' => 'badges:full-sync',
                'background' => true,
                'arguments' => [],
            ],
            'furnis_import_full' => [
                'label' => 'Import furnis HabboAssets (multi-hotel)',
                'command' => 'furnis:import-habboassets',
                'background' => true,
                'arguments' => [
                    '--hotels' => 'es,com,com.br,de,fr,it,nl,fi,tr',
                    '--category' => 'auto',
                ],
            ],
            'furnis_habbofurni_full' => [
                'label' => 'Import furnis Habbofurni (completo)',
                'command' => 'furnis:import-habbofurni',
                'background' => true,
                'arguments' => [
                    '--paths' => 'furniture',
                ],
            ],
            'furnis_sync_external' => [
                'label' => 'Sync furnis externo (HabboAssets + Habbofurni)',
                'command' => 'furnis:sync-external',
                'background' => true,
                'arguments' => [],
            ],
        ];
    }

    public function runSelectedCommand(): void
    {
        @set_time_limit(0);

        $selectedCommand = trim((string) $this->selectedCommand);
        $commands = $this->getCommandOptions();
        $allowed = array_keys($commands);

        if ($selectedCommand === '' || !in_array($selectedCommand, $allowed, true)) {
            $this->commandOutput = 'Error: comando inválido o no permitido.';
            $this->lastRunAt = now()->format('d/m/Y H:i:s');
            Notification::make()
                ->title('Comando inválido')
                ->danger()
                ->send();
            return;
        }

        try {
            $selected = $commands[$selectedCommand];
            $commandName = (string) ($selected['command'] ?? '');
            $arguments = is_array($selected['arguments'] ?? null) ? $selected['arguments'] : [];

            if ($commandName === '') {
                throw new \RuntimeException('Comando no configurado.');
            }

            $runInBackground = (bool) ($selected['background'] ?? false)
                || $this->shouldForceBackground($commandName);

            if ($runInBackground) {
                $this->runInBackground($commandName, $arguments);
                return;
            }

            Artisan::call($commandName, $arguments);
            $this->appendOutput('$ ' . $this->buildCommandString($commandName, $arguments));
            $this->appendOutput(trim(Artisan::output()));
            $this->lastRunAt = now()->format('d/m/Y H:i:s');

            Notification::make()
                ->title('Comando ejecutado')
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            $this->commandOutput = 'Error: ' . $exception->getMessage();
            $this->lastRunAt = now()->format('d/m/Y H:i:s');

            Notification::make()
                ->title('Error al ejecutar comando')
                ->danger()
                ->send();
        }
    }

    public function clearOutput(): void
    {
        $this->commandOutput = '';
        $this->lastRunAt = null;
    }

    public function loadPresetCommand(): void
    {
        $selectedKey = trim((string) $this->selectedCommand);
        $options = $this->getCommandOptions();
        if ($selectedKey === '' || !isset($options[$selectedKey])) {
            return;
        }

        $preset = $options[$selectedKey];
        $commandName = (string) ($preset['command'] ?? '');
        $arguments = is_array($preset['arguments'] ?? null) ? $preset['arguments'] : [];
        $this->commandLine = $this->buildCommandString($commandName, $arguments);
    }

    public function runTypedCommand(): void
    {
        @set_time_limit(0);

        $line = trim((string) $this->commandLine);
        if ($line === '') {
            $this->commandOutput = 'Error: escribe un comando.';
            $this->lastRunAt = now()->format('d/m/Y H:i:s');
            return;
        }

        if (preg_match('/[|;&`$<>]/', $line)) {
            $this->commandOutput = 'Error: no se permiten operadores de shell en esta consola.';
            $this->lastRunAt = now()->format('d/m/Y H:i:s');
            return;
        }

        try {
            [$commandName, $arguments] = $this->parseCommandLine($line);

            $this->appendOutput('$ ' . $line);

            if ($this->shouldForceBackground($commandName)) {
                $this->runInBackground($commandName, $arguments);
                return;
            }

            Artisan::call($commandName, $arguments);
            $this->appendOutput(trim(Artisan::output()));
            $this->lastRunAt = now()->format('d/m/Y H:i:s');

            Notification::make()
                ->title('Comando ejecutado')
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            $this->appendOutput('Error: ' . $exception->getMessage());
            $this->lastRunAt = now()->format('d/m/Y H:i:s');

            Notification::make()
                ->title('Error al ejecutar comando')
                ->danger()
                ->send();
        }
    }

    public function refreshOutputFromLog(): void
    {
        $logPath = storage_path('logs/hk-console-commands.log');
        if (!File::exists($logPath)) {
            $this->commandOutput = "Log no encontrado:\n{$logPath}";
            $this->lastRunAt = now()->format('d/m/Y H:i:s');
            return;
        }

        $lines = @file($logPath, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines) || count($lines) === 0) {
            $this->commandOutput = "Log vacío todavía:\n{$logPath}";
            $this->lastRunAt = now()->format('d/m/Y H:i:s');
            return;
        }

        $tail = implode(PHP_EOL, array_slice($lines, -180));
        $this->commandOutput = "Log:\n{$logPath}\n\n{$tail}";
        $this->lastRunAt = now()->format('d/m/Y H:i:s');
    }

    private function runInBackground(string $commandName, array $arguments): void
    {
        $configuredBinary = trim((string) config('academy.panel.console_php_binary', 'php'));
        $phpBinary = $this->resolvePhpBinary($configuredBinary);
        $artisan = base_path('artisan');
        $logPath = storage_path('logs/hk-console-commands.log');
        $basePath = base_path();
        $lockPath = storage_path('app/hk-console/' . $this->slugForLock($commandName) . '.lock');
        $pidPath = storage_path('app/hk-console/' . $this->slugForLock($commandName) . '.pid');

        if (!File::exists($logPath)) {
            @File::put($logPath, '');
        }
        if (!File::isDirectory(dirname($lockPath))) {
            @File::makeDirectory(dirname($lockPath), 0755, true);
        }

        $commandString = $commandName;
        foreach ($arguments as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $commandString .= ' ' . $key;
                }
                continue;
            }
            $commandString .= ' ' . $key . '=' . escapeshellarg((string) $value);
        }

        if (File::exists($lockPath) && !$this->isBackgroundProcessRunning($pidPath)) {
            @File::delete($lockPath);
            @File::delete($pidPath);
        }

        @File::put($lockPath, now()->toDateTimeString());

        if (DIRECTORY_SEPARATOR === '\\' || $this->isBackgroundExecUnavailable()) {
            $this->runInCurrentProcess($commandName, $arguments, $commandString, $logPath, $lockPath, $pidPath);
            return;
        }

        $exec = sprintf(
            'cd %s && printf %s >> %s 2>&1 && ( %s %s %s >> %s 2>&1 < /dev/null & echo $! > %s )',
            escapeshellarg($basePath),
            escapeshellarg(PHP_EOL . '>>> [' . now()->format('Y-m-d H:i:s') . '] ' . $commandString . PHP_EOL),
            escapeshellarg($logPath),
            escapeshellarg($phpBinary),
            escapeshellarg($artisan),
            $commandString,
            escapeshellarg($logPath),
            escapeshellarg($pidPath)
        );

        $execOutput = [];
        $execCode = 0;
        @exec($exec, $execOutput, $execCode);

        if ($execCode !== 0) {
            $this->appendOutput("No se pudo lanzar en segundo plano (exit {$execCode}). Ejecutando en primer plano...");
            $this->runInCurrentProcess($commandName, $arguments, $commandString, $logPath, $lockPath, $pidPath);
            return;
        }

        $this->lastRunAt = now()->format('d/m/Y H:i:s');
        $this->appendOutput("Comando lanzado en segundo plano:\n{$commandString}\n\nLog:\n{$logPath}");

        Notification::make()
            ->title('Comando lanzado en segundo plano')
            ->success()
            ->send();
    }

    private function runInCurrentProcess(
        string $commandName,
        array $arguments,
        string $commandString,
        string $logPath,
        string $lockPath,
        string $pidPath
    ): void {
        @File::append(
            $logPath,
            PHP_EOL . '>>> [' . now()->format('Y-m-d H:i:s') . '] ' . $commandString . PHP_EOL
        );

        try {
            $this->appendOutput('$ ' . $this->buildCommandString($commandName, $arguments));

            $exitCode = Artisan::call($commandName, $arguments);
            $output = trim((string) Artisan::output());
            if ($output !== '') {
                $this->appendOutput($output);
                @File::append($logPath, PHP_EOL . $output . PHP_EOL);
            }

            $this->lastRunAt = now()->format('d/m/Y H:i:s');

            if ($exitCode === 0) {
                Notification::make()
                    ->title('Comando ejecutado')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Comando finalizó con errores')
                    ->warning()
                    ->send();
            }
        } catch (\Throwable $exception) {
            $message = 'Error: ' . $exception->getMessage();
            $this->appendOutput($message);
            @File::append($logPath, PHP_EOL . $message . PHP_EOL);
            $this->lastRunAt = now()->format('d/m/Y H:i:s');

            Notification::make()
                ->title('Error al ejecutar comando')
                ->danger()
                ->send();
        } finally {
            @File::delete($lockPath);
            @File::delete($pidPath);
        }
    }

    private function isBackgroundExecUnavailable(): bool
    {
        if (!function_exists('exec')) {
            return true;
        }

        return $this->isFunctionDisabled('exec');
    }

    private function isFunctionDisabled(string $functionName): bool
    {
        $disabled = (string) ini_get('disable_functions');
        if (trim($disabled) === '') {
            return false;
        }

        $disabledFunctions = array_map('trim', explode(',', strtolower($disabled)));
        return in_array(strtolower($functionName), $disabledFunctions, true);
    }

    private function resolvePhpBinary(string $configuredBinary): string
    {
        $candidates = collect([
            $configuredBinary,
            '/Applications/MAMP/bin/php/php8.3.30/bin/php',
            '/Applications/MAMP/bin/php/php8.4.17/bin/php',
            '/Applications/MAMP/bin/php/php8.5.2/bin/php',
            '/Applications/MAMP/bin/php/php7.4.33/bin/php',
            '/usr/bin/php',
            'php',
        ])->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->map(fn ($path) => trim((string) $path))
            ->unique()
            ->values();

        foreach ($candidates as $candidate) {
            if ($candidate === 'php') {
                return $candidate;
            }

            if (
                is_file($candidate)
                && is_executable($candidate)
                && !str_contains(strtolower($candidate), 'php-cgi')
            ) {
                return $candidate;
            }
        }

        return 'php';
    }

    private function slugForLock(string $commandName): string
    {
        return trim(preg_replace('/[^a-z0-9\-_]+/i', '-', strtolower($commandName)), '-');
    }

    private function isBackgroundProcessRunning(string $pidPath): bool
    {
        if (!File::exists($pidPath)) {
            return false;
        }

        $pid = (int) trim((string) @File::get($pidPath));
        if ($pid <= 0) {
            return false;
        }

        if (function_exists('posix_kill')) {
            return @posix_kill($pid, 0);
        }

        return false;
    }

    private function shouldForceBackground(string $commandName): bool
    {
        $heavyCommands = [
            'badges:full-sync',
            'badges:import-habboassets',
            'badges:repair-metadata',
            'furnis:import-habboassets',
            'furnis:import-habbofurni',
            'furnis:sync-external',
        ];

        return in_array(trim($commandName), $heavyCommands, true);
    }

    private function parseCommandLine(string $line): array
    {
        $tokens = $this->tokenize($line);
        if (count($tokens) === 0) {
            throw new \RuntimeException('Comando vacío.');
        }

        $commandName = array_shift($tokens);
        if (!is_string($commandName) || trim($commandName) === '') {
            throw new \RuntimeException('Comando inválido.');
        }
        $commandName = trim($commandName);

        $arguments = [];
        for ($i = 0; $i < count($tokens); $i++) {
            $token = (string) $tokens[$i];

            if (!Str::startsWith($token, '--')) {
                throw new \RuntimeException("Argumento no soportado: {$token}. Usa opciones --flag o --clave=valor.");
            }

            if (str_contains($token, '=')) {
                [$key, $value] = explode('=', $token, 2);
                if (trim($key) === '') {
                    continue;
                }
                $arguments[$key] = $this->normalizeCliValue((string) $value);
                continue;
            }

            $next = $tokens[$i + 1] ?? null;
            if (is_string($next) && !Str::startsWith($next, '--')) {
                $arguments[$token] = $this->normalizeCliValue($next);
                $i++;
                continue;
            }

            $arguments[$token] = true;
        }

        return [$commandName, $arguments];
    }

    private function tokenize(string $line): array
    {
        preg_match_all('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\']*)\'|(\S+)/', $line, $matches, PREG_SET_ORDER);
        $tokens = [];

        foreach ($matches as $match) {
            if (isset($match[1]) && $match[1] !== '') {
                $tokens[] = stripcslashes($match[1]);
                continue;
            }

            if (isset($match[2]) && $match[2] !== '') {
                $tokens[] = $match[2];
                continue;
            }

            if (isset($match[3]) && $match[3] !== '') {
                $tokens[] = $match[3];
            }
        }

        return $tokens;
    }

    private function normalizeCliValue(string $value): string
    {
        $value = trim($value);
        $value = trim($value, "\"'");
        return trim($value);
    }

    private function buildCommandString(string $commandName, array $arguments): string
    {
        $commandString = trim($commandName);
        foreach ($arguments as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                continue;
            }

            if (is_bool($value)) {
                if ($value) {
                    $commandString .= ' ' . $key;
                }
                continue;
            }

            $commandString .= ' ' . $key . '=' . escapeshellarg((string) $value);
        }

        return trim($commandString);
    }

    private function appendOutput(string $chunk): void
    {
        $chunk = trim($chunk);
        if ($chunk === '') {
            return;
        }

        $current = trim((string) $this->commandOutput);
        if ($current === '') {
            $this->commandOutput = $chunk;
            return;
        }

        $this->commandOutput = $current . PHP_EOL . PHP_EOL . $chunk;
    }
}
