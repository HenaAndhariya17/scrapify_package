<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PdfToJpgConverter
{
    public function convert(UploadedFile $pdf): array
    {
        // 1️⃣ Validate PDF size
        $maxSizeKB = 25 * 1024;
        if ($pdf->getSize() / 1024 > $maxSizeKB) {
            throw new Exception("PDF exceeds maximum size of 25MB.");
        }

        // 2️⃣ Save uploaded PDF to storage
        $originalName = pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME);
        $pdfDir = storage_path('app/public/pdfs');
        File::ensureDirectoryExists($pdfDir);

        $uploadedPath = $pdfDir . '/' . $originalName . '.pdf';
        $pdf->move($pdfDir, $originalName . '.pdf');

        // 3️⃣ Prepare JPG output directory
        $jpgDir = public_path('storage/jpgs');
        File::ensureDirectoryExists($jpgDir);

        // Output pattern (multiple pages => multiple images)
        $outputPattern = $jpgDir . '/' . $originalName . '-%03d.jpg';

        // 4️⃣ Detect Ghostscript binary
        $gsBinary = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe'
            : 'gs';

        // 5️⃣ Build Ghostscript command to convert PDF → JPG
        $gsCommand = [
            $gsBinary,
            '-dNOPAUSE',
            '-dBATCH',
            '-sDEVICE=jpeg',
            '-r300', // Resolution 300 DPI
            "-sOutputFile=$outputPattern",
            $uploadedPath
        ];

        $process = new Process($gsCommand);

        // Set TEMP folder for Windows to avoid temp file issues
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $process->setEnv([
                'TEMP' => 'C:\laragon\tmp',
                'TMP'  => 'C:\laragon\tmp',
            ]);
        }

        // 6️⃣ Run Ghostscript
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Conversion failed: " . $process->getErrorOutput());
        }

        // 7️⃣ Collect converted JPG files
        $jpgFiles = glob($jpgDir . '/' . $originalName . '-*.jpg');
        if (empty($jpgFiles)) {
            throw new Exception("No JPG images were generated.");
        }

        // 8️⃣ Return base64 + URLs for all images
        $result = [];
        foreach ($jpgFiles as $jpgFile) {
            $result[] = [
                'filename' => basename($jpgFile),
                'file' => base64_encode(file_get_contents($jpgFile)),
                'url' => asset('storage/jpgs/' . basename($jpgFile)),
            ];
        }

        return $result;
    }
}
