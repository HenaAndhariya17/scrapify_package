<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PdfProtect
{
    /**
     * Protect a PDF file with a password.
     *
     * @param UploadedFile $pdf
     * @param string $password
     * @return array
     * @throws Exception
     */
    public function protect(UploadedFile $pdf, string $password): array
    {
        $originalName = pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME);
        $originalPath = storage_path("app/public/{$originalName}.pdf");
        $protectedPath = storage_path("app/public/{$originalName}_protected.pdf");

        // Move uploaded file to storage
        $pdf->move(dirname($originalPath), basename($originalPath));

        // Detect Ghostscript binary (Windows or Linux)
        $gsBinary = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe'
            : 'gs';

        // Ghostscript command to encrypt PDF with password
        $gsCommand = [
            $gsBinary,
            '-sDEVICE=pdfwrite',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            "-sOutputFile=$protectedPath",
            "-sOwnerPassword=$password", // owner password
            "-sUserPassword=$password",  // user password
            '-dEncryptionR=3',           // 128-bit encryption
            '-dKeyLength=128',
            '-dPermissions=-4',          // allow printing/copying settings (-4 = no restrictions)
            $originalPath,
        ];

        // Run Ghostscript
        $process = new Process($gsCommand);

        // For Windows, handle temp path
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $process->setEnv([
                'TEMP' => 'C:\laragon\tmp',
                'TMP'  => 'C:\laragon\tmp',
            ]);
        }

        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception('PDF protection failed: ' . $process->getErrorOutput());
        }

        if (!File::exists($protectedPath)) {
            throw new Exception("Protected PDF not found.");
        }

        $protectedContent = File::get($protectedPath);

        // Move final protected PDF to public folder
        $publicPath = public_path('storage/pdfs/' . $originalName . '_protected.pdf');
        File::ensureDirectoryExists(dirname($publicPath));
        File::copy($protectedPath, $publicPath);

        return [
            'filename' => $originalName . '_protected.pdf',
            'file' => base64_encode($protectedContent),
            'url' => asset('storage/pdfs/' . $originalName . '_protected.pdf'),
        ];
    }
}
