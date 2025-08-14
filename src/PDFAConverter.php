<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PDFAConverter
{
    public function convert(UploadedFile $file): array
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originalPath = storage_path("app/public/{$originalName}.pdf");
        $pdfaPath = storage_path("app/public/{$originalName}_pdfa.pdf");

        // Save uploaded file to storage
        $file->move(dirname($originalPath), basename($originalPath));

        // Detect Ghostscript binary (Windows or Linux)
        $gsBinary = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe'
            : 'gs';

        // Build Ghostscript command for PDF/A-2b
        // Options:
        // -dPDFA=2 (PDF/A-2)
        // -sProcessColorModel=DeviceRGB (standard color)
        // -dUseCIEColor (forces color profiles)
        $gsCommand = [
            $gsBinary,
            '-dPDFA=2',
            '-dBATCH',
            '-dNOPAUSE',
            '-dNOOUTERSAVE',
            '-sColorConversionStrategy=RGB',
            '-sProcessColorModel=DeviceRGB',
            '-dUseCIEColor',
            '-sDEVICE=pdfwrite',
            "-sOutputFile=$pdfaPath",
            $originalPath,
        ];

        // Run Ghostscript process
        $process = new Process($gsCommand);

        // Set Windows temp environment if needed
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $process->setEnv([
                'TEMP' => 'C:\laragon\tmp',
                'TMP'  => 'C:\laragon\tmp',
            ]);
        }

        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception('PDF/A conversion failed: ' . $process->getErrorOutput());
        }

        if (!File::exists($pdfaPath)) {
            throw new Exception("PDF/A file not found.");
        }

        $pdfaContent = File::get($pdfaPath);

        // Move converted PDF/A to public folder
        $publicPath = public_path('storage/pdfs/' . $originalName . '_pdfa.pdf');
        File::ensureDirectoryExists(dirname($publicPath));
        File::copy($pdfaPath, $publicPath);

        // Return result
        return [
            'filename' => $originalName . '_pdfa.pdf',
            'file' => base64_encode($pdfaContent),
            'url' => asset('storage/pdfs/' . $originalName . '_pdfa.pdf'),
        ];
    }
}
