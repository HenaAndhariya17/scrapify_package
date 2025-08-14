<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Exception;

class WordToPdf
{
    /**
     * Convert a PDF to Word (DOCX)
     */
    public function convert(UploadedFile $pdfFile): array
    {
        if (!$pdfFile->isValid()) {
            throw new Exception("Invalid file upload.");
        }

        $wordDir = storage_path('app/public/word');
        if (!File::exists($wordDir)) {
            File::makeDirectory($wordDir, 0755, true);
        }

        $baseName = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = $baseName . '_' . time() . '.docx';
        $outputPath = $wordDir . '/' . $filename;

        $inputPath = $pdfFile->getRealPath();
        $command = "soffice --headless --convert-to docx --outdir " . escapeshellarg($wordDir) . " " . escapeshellarg($inputPath);
        exec($command, $output, $returnCode);

        $convertedFile = $wordDir . '/' . $baseName . '.docx';

        if ($returnCode !== 0 || !File::exists($convertedFile)) {
            throw new Exception("Failed to convert PDF to Word. Please ensure LibreOffice is installed.");
        }

        File::move($convertedFile, $outputPath);

        return [
            'filename' => $filename,
            'file'     => base64_encode(File::get($outputPath)),
            'url'      => asset('storage/word/' . $filename),
        ];
    }
}
