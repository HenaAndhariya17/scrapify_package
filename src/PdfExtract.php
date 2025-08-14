<?php


namespace Scrapify\Pdftools;

use Smalot\PdfParser\Parser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process; 
use Illuminate\Support\Str;
use Exception;

class PdfExtract
{
    public function extractPages(UploadedFile $pdf, string $pages): array
    {
        $maxSizeKB = 25 * 1024;
        if ($pdf->getSize() / 1024 > $maxSizeKB) {
            throw new Exception("PDF exceeds maximum size of 25MB.");
        }

        $originalName = pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME);
        $pdfDir = storage_path('app/public/pdfs');
        if (!File::exists($pdfDir)) {
            File::makeDirectory($pdfDir, 0755, true);
        }
        $uploadedPath = $pdfDir . '/' . $originalName . '.pdf';
        $pdf->move($pdfDir, $originalName . '.pdf');

        $outputPdf = $pdfDir . '/' . $originalName . '_extracted.pdf';

        $gsBinary = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe'
            : 'gs';

        $tempPath = storage_path('app/temp');
        if (!File::exists($tempPath)) {
            File::makeDirectory($tempPath, 0755, true);
        }

        // Prepare page numbers
        $pageList = array_map('trim', explode(',', $pages));
        $intermediateFiles = [];

        foreach ($pageList as $index => $page) {
            $outFile = $tempPath . "/part_" . $index . ".pdf";

            $gsCommand = [
                $gsBinary,
                '-sDEVICE=pdfwrite',
                '-dNOPAUSE',
                '-dBATCH',
                '-dSAFER',
                "-dFirstPage={$page}",
                "-dLastPage={$page}",
                "-sOutputFile={$outFile}",
                $uploadedPath
            ];

            $process = new Process($gsCommand);
            $process->setEnv(['TEMP' => $tempPath, 'TMP' => $tempPath]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new Exception("Error extracting page {$page}: " . $process->getErrorOutput());
            }

            $intermediateFiles[] = $outFile;
        }

        // Merge the extracted PDFs into one
        $mergeCommand = array_merge(
            [$gsBinary, '-dBATCH', '-dNOPAUSE', '-q', '-sDEVICE=pdfwrite', "-sOutputFile={$outputPdf}"],
            $intermediateFiles
        );

        $mergeProcess = new Process($mergeCommand);
        $mergeProcess->setEnv(['TEMP' => $tempPath, 'TMP' => $tempPath]);
        $mergeProcess->run();

        if (!$mergeProcess->isSuccessful()) {
            throw new Exception("Error merging extracted pages: " . $mergeProcess->getErrorOutput());
        }

        return [
            'filename' => $originalName . '_extracted.pdf',
            'file'     => base64_encode(file_get_contents($outputPdf)),
            'url'      => asset('storage/pdfs/' . $originalName . '_extracted.pdf'),
            'message'  => 'Selected pages extracted successfully!',
        ];
    }

}