<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PdfaValidator
{
    public function validate(UploadedFile $file): array
    {
        // Sanitize filename
        $originalName = str_replace(' ', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $originalPath = storage_path("app/public/{$originalName}.pdf");

        // Save uploaded PDF
        $file->move(dirname($originalPath), basename($originalPath));

        // Detect Ghostscript binary
        $gsBinary = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe'
            : 'gs';

        // Ghostscript PDF/A validation command
        $gsCommand = [
            $gsBinary,
            '-dPDFA=2',                     // Validate against PDF/A-2
            '-dPDFACompatibilityPolicy=1',  // Fail on first error
            '-dNOPAUSE',
            '-dBATCH',
            '-sDEVICE=nullpage',
            $originalPath,
        ];

        $process = new Process($gsCommand);

        // Handle Windows temp paths
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $process->setEnv([
                'TEMP' => 'C:\laragon\tmp',
                'TMP'  => 'C:\laragon\tmp',
            ]);
        }

        $process->run();

        // Combine Ghostscript outputs
        $validationOutput = trim($process->getErrorOutput() . "\n" . $process->getOutput());

        // Determine status
        $isValid = $process->isSuccessful();

        // Parse simple details
        $status = $isValid ? 'PDF/A Compliant' : 'PDF/A with errors';
        $errors = [];

        if (!$isValid) {
            // Extract lines that indicate errors
            foreach (explode("\n", $validationOutput) as $line) {
                if (stripos($line, 'error') !== false || stripos($line, 'invalid') !== false) {
                    $errors[] = trim($line);
                }
            }

            if (empty($errors)) {
                $errors[] = 'Unknown PDF/A compliance issues detected.';
            }
        }

        return [
            'filename'  => $file->getClientOriginalName(),
            'standard'  => 'PDF/A-2b',
            'iso_name'  => 'ISO 19005-2:2011',
            'conformance_level' => 'Level b',
            'status'    => $status,
            'errors'    => $errors,
        ];
    }
}
