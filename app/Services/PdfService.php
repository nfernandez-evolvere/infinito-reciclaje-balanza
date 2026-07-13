<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;

class PdfService
{
    public function fromView(string $view, array $data = []): string
    {
        $html = view($view, $data)->render();

        return $this->fromHtml($html);
    }

    public function fromHtml(string $html): string
    {
        // Raíz del problema en producción: bajo php-fpm el proceso corre con
        // HOME=/root (heredado del master, no escribible por www-data). Chromium
        // siempre lanza su chrome_crashpad_handler, que deriva el directorio de la
        // base de crashes de $HOME; al no poder escribir falla con
        // "chrome_crashpad_handler: --database is required" y no arranca el browser.
        // Browsershot lanza el proceso con Process::fromShellCommandline() heredando
        // el entorno de PHP, y browser.cjs hace { ...options.env, ...process.env } —
        // process.env gana, así que setEnvironmentOptions(['HOME'=>...]) no alcanza:
        // hay que setear HOME en el entorno del propio proceso PHP. El worker (CLI)
        // no sufre esto porque arranca con un HOME escribible.
        $homeAnterior = getenv('HOME');
        $this->setHome(sys_get_temp_dir());

        // Directorio de perfil de Chromium único por render: aísla generaciones
        // concurrentes y evita el lock ProcessSingleton sobre un perfil compartido.
        $userDataDir = sys_get_temp_dir().'/browsershot-'.bin2hex(random_bytes(8));

        try {
            $browsershot = Browsershot::html($html)
                ->format('A4')
                ->landscape()
                ->margins(0, 0, 0, 0)
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->noSandbox()
                ->setUserDataDir($userDataDir)
                ->addChromiumArguments([
                    'disable-crash-reporter',  // refuerza la desactivación del crashpad_handler
                    'disable-dev-shm-usage',   // /dev/shm en Docker es de 64 MB → usar /tmp
                ]);

            $chromePath = $this->resolveChromePath();
            if ($chromePath) {
                $browsershot->setChromePath($chromePath);
            }

            $nodePath = $this->resolveNodePath();
            if ($nodePath) {
                $browsershot->setNodeBinary($nodePath);
            }

            return $browsershot->pdf();
        } finally {
            $this->deleteDirectory($userDataDir);
            $this->setHome($homeAnterior !== false ? $homeAnterior : null);
        }
    }

    /**
     * Setea (o restaura) la variable HOME en las tres fuentes que Symfony Process
     * usa para construir el entorno heredado por el proceso de Chromium.
     */
    private function setHome(?string $path): void
    {
        if ($path === null) {
            putenv('HOME');
            unset($_ENV['HOME'], $_SERVER['HOME']);

            return;
        }

        putenv('HOME='.$path);
        $_ENV['HOME'] = $path;
        $_SERVER['HOME'] = $path;
    }

    /**
     * Borra recursivamente el directorio temporal de perfil de Chromium.
     */
    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($path);
    }

    private function resolveChromePath(): ?string
    {
        $candidates = [
            // Docker: Chrome for Testing (build de Google horneado en la imagen).
            // Reemplaza al paquete `chromium` de Debian, que crashea con SIGTRAP
            // al arrancar en WSL2/Docker. Es un symlink estable a /opt.
            '/usr/local/bin/chrome',
            // Docker / Railway (Debian Bookworm)
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            // Linux VPS
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            // macOS
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            // Windows
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function resolveNodePath(): ?string
    {
        $candidates = [
            '/usr/bin/node',
            '/usr/local/bin/node',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
