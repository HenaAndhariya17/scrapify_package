<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PdfOrganize
{
    public function organizePages(UploadedFile $pdf, string $pageOrder): array
    {
        // 1. Validate file size
        $maxSizeKB = 25 * 1024;
        if ($pdf->getSize() / 1024 > $maxSizeKB) {
            throw new Exception("PDF exceeds maximum size of 25MB.");
        }

        // 2. Parse page order (like "3,1,2" or "1-3,5")
        $pages = $this->parsePageOrder($pageOrder);

        if (empty($pages)) {
            throw new Exception("Invalid page order provided.");
        }

        // 3. Save uploaded PDF
        $originalName = pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME);
        $pdfDir = storage_path('app/public/pdfs');
        if (!File::exists($pdfDir)) {
            File::makeDirectory($pdfDir, 0755, true);
        }
        $uploadedPath = $pdfDir . '/' . $originalName . '.pdf';
        $pdf->move($pdfDir, $originalName . '.pdf');

        // 4. Prepare output file
        $outputPdf = $pdfDir . '/' . $originalName . '_organized.pdf';

        // 5. Detect Ghostscript binary
        $gsBinary = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe'
            : 'gs';

        $tempFiles = [];

        // 6. Extract pages individually
        foreach ($pages as $i => $page) {
            $tempFile = $pdfDir . "/temp_page_" . ($i+1) . ".pdf";
            $tempFiles[] = $tempFile;

            $process = new Process([
                $gsBinary,
                '-sDEVICE=pdfwrite',
                '-dNOPAUSE',
                '-dBATCH',
                '-dSAFER',
                "-dFirstPage={$page}",
                "-dLastPage={$page}",
                "-sOutputFile={$tempFile}",
                $uploadedPath
            ]);

            // ✅ Set temp environment for Ghostscript
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $process->setEnv([
                    'TEMP' => 'C:\laragon\tmp',
                    'TMP'  => 'C:\laragon\tmp',
                ]);
            }

            $process->run();

            if (!$process->isSuccessful()) {
                throw new Exception("Error extracting page {$page}: " . $process->getErrorOutput());
            }
        }

        // 7. Merge all temp files into final PDF
        $mergeCommand = array_merge([
            $gsBinary,
            '-dBATCH', '-dNOPAUSE', '-q', '-sDEVICE=pdfwrite',
            "-sOutputFile={$outputPdf}"
        ], $tempFiles);

        $mergeProcess = new Process($mergeCommand);

        // ✅ Set temp environment for merging
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $mergeProcess->setEnv([
                'TEMP' => 'C:\laragon\tmp',
                'TMP'  => 'C:\laragon\tmp',
            ]);
        }

        $mergeProcess->run();

        // Clean up temp files
        foreach ($tempFiles as $file) {
            if (File::exists($file)) File::delete($file);
        }

        if (!$mergeProcess->isSuccessful()) {
            throw new Exception("Error merging pages: " . $mergeProcess->getErrorOutput());
        }

        // 8. Return the organized PDF
        return [
            'filename' => $originalName . '_organized.pdf',
            'file'     => base64_encode(file_get_contents($outputPdf)),
            'url'      => asset('storage/pdfs/' . $originalName . '_organized.pdf'),
            'message'  => 'PDF pages organized successfully!',
        ];
    }

    /**
     * Convert a string like "1-3,5,7-8" into an array of pages [1,2,3,5,7,8]
     */
    private function parsePageOrder(string $order): array
    {
        $pages = [];
        $parts = explode(',', $order);

        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, '-') !== false) {
                [$start, $end] = explode('-', $part);
                $start = (int)$start;
                $end = (int)$end;
                if ($start > 0 && $end >= $start) {
                    $pages = array_merge($pages, range($start, $end));
                }
            } elseif (is_numeric($part)) {
                $pages[] = (int)$part;
            }
        }

        return $pages;
    }
}
