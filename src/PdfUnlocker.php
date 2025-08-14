<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PdfUnlocker
{
    /**
     * Unlock a PDF file.
     *
     * @param UploadedFile $file
     * @param string $password
     * @return array
     * @throws Exception
     */
    public function unlock(UploadedFile $file, string $password): array
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $inputPath = storage_path("app/public/{$originalName}.pdf");
        $outputFilename = $originalName . '_unlocked.pdf';
        $outputPath = storage_path("app/public/{$outputFilename}");

        // Save uploaded file
        $file->move(dirname($inputPath), basename($inputPath));

        // Run Python script to unlock PDF
        $scriptPath = __DIR__ . '/../unlock_pdf.py';
        $process = new Process(['python', $scriptPath, $inputPath, $outputPath, $password]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Python Error: " . $process->getErrorOutput());
        }

        if (!File::exists($outputPath)) {
            throw new Exception("Unlocked file not found.");
        }

        return [
            'filename' => $outputFilename,
            'file' => base64_encode(File::get($outputPath)),
            'url' => asset('storage/' . basename($outputPath)),
        ];
    }
}