<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PdfWatermark
{
    public function watermark(UploadedFile $file, string $watermarkText): array
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $inputPath = storage_path("app/public/{$originalName}.pdf");
        $outputFilename = $originalName . '_watermarked.pdf';
        $outputPath = storage_path("app/public/{$outputFilename}");

        // Save uploaded file
        $file->move(dirname($inputPath), basename($inputPath));

        // Run Python script to add watermark
        $scriptPath = __DIR__ . '/../watermark_pdf.py';
        $process = new Process(['python', $scriptPath, $inputPath, $outputPath, $watermarkText]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Python Error: " . $process->getErrorOutput());
        }

        if (!File::exists($outputPath)) {
            throw new Exception("Watermarked file not found.");
        }

        return [
            'filename' => $outputFilename,
            'file' => base64_encode(File::get($outputPath)),
            'url' => asset('storage/' . basename($outputPath)),
        ];
    }
}