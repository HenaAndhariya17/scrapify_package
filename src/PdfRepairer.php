<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PdfRepairer
{
    public function repair(UploadedFile $pdf): array
    {
        $maxSizeKB = 25 * 1024;
        if ($pdf->getSize() / 1024 > $maxSizeKB) {
            throw new Exception("PDF exceeds maximum size of 25MB.");
        }

        $originalName = pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME);
        $pdfDir = public_path('storage/pdfs');
        File::ensureDirectoryExists($pdfDir);

        $inputPath = $pdfDir . '/' . $originalName . '.pdf';
        $outputFilename = $originalName . '-repaired.pdf';
        $outputPath = $pdfDir . '/' . $outputFilename;

        // Move uploaded file
        $pdf->move($pdfDir, $originalName . '.pdf');

        // Run Python script to repair PDF
        $scriptPath = __DIR__ . '/../repair_pdf.py';
        $process = new Process(['python', $scriptPath, $inputPath, $outputPath]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Python Error: " . $process->getErrorOutput());
        }

        if (!File::exists($outputPath)) {
            throw new Exception("Failed to create repaired PDF.");
        }

        return [
            'filename' => $outputFilename,
            'file'     => base64_encode(File::get($outputPath)),
            'url'      => asset('storage/pdfs/' . $outputFilename),
        ];
    }
}