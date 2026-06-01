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
        $browsershot = Browsershot::html($html)
            ->format('A4')
            ->landscape()
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->waitUntilNetworkIdle();

        $chromePath = $this->resolveChromePath();
        if ($chromePath) {
            $browsershot->setChromePath($chromePath);
        }

        return $browsershot->pdf();
    }

    private function resolveChromePath(): ?string
    {
        $candidates = [
            // Linux / VPS
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
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

}
