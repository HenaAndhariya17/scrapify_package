<?php


namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PdfCompressor
{
    public function compress(UploadedFile $file): array
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originalPath = storage_path("app/public/{$originalName}.pdf");
        $compressedPath = storage_path("app/public/{$originalName}_compressed.pdf");

        // Save uploaded file to storage
        $file->move(dirname($originalPath), basename($originalPath));

        // Detect Ghostscript binary (Windows or Linux)
        $gsBinary = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe'
            : 'gs';

        // Build Ghostscript command
        $gsCommand = [
            $gsBinary,
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/screen',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            "-sOutputFile=$compressedPath",
            $originalPath,
        ];

        // Run Ghostscript using Symfony Process
        $process = new Process($gsCommand);

        // On Windows, set TEMP folder to avoid Ghostscript temp issues
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $process->setEnv([
                'TEMP' => 'C:\laragon\tmp',
                'TMP'  => 'C:\laragon\tmp',
            ]);
        }

        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception('Compression failed: ' . $process->getErrorOutput());
        }

        if (!File::exists($compressedPath)) {
            throw new Exception("Compressed file not found.");
        }

        $compressedContent = File::get($compressedPath);

        // Move compressed PDF to public folder
        $publicPath = public_path('storage/pdfs/' . $originalName . '_compressed.pdf');
        File::ensureDirectoryExists(dirname($publicPath));
        File::copy($compressedPath, $publicPath);

        // Return result
        return [
            'filename' => $originalName . '_compressed.pdf',
            'file' => base64_encode($compressedContent),
            'url' => asset('storage/pdfs/' . $originalName . '_compressed.pdf'),
        ];
    }

}
