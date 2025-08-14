<?php

namespace Scrapify\PdfTools;

use Illuminate\Support\Facades\File;
use Exception;

class HtmlToPdfConverter
{
    private $apiKey = '51d467909c0705a4c176311b281345a7'; // Replace with your pdflayer API key

    /**
     * Convert HTML or URL to PDF using pdflayer API
     */
    public function convert(string $htmlOrUrl): array
    {
        // Create output directory
        $outputDir = storage_path('app/public/html-pdfs');
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        // Generate filename & path
        $timestamp = time();
        $fileName  = "html_to_pdf_{$timestamp}.pdf";
        $pdfPath   = $outputDir . DIRECTORY_SEPARATOR . $fileName;

        // pdflayer API endpoint — free plan uses HTTP
        $apiUrl = "http://api.pdflayer.com/api/convert?access_key={$this->apiKey}";

        // Detect URL or HTML input
        if (filter_var($htmlOrUrl, FILTER_VALIDATE_URL)) {
            $apiUrl .= "&document_url=" . urlencode($htmlOrUrl);
        } else {
            $apiUrl .= "&document_html=" . urlencode($htmlOrUrl);
        }

        try {
            // Fetch the PDF
            $pdfData = @file_get_contents($apiUrl);

            if (!$pdfData) {
                throw new Exception("Failed to fetch PDF from pdflayer API.");
            }

            // Check for API JSON error
            $decoded = json_decode($pdfData, true);
            if (is_array($decoded) && isset($decoded['error'])) {
                throw new Exception("pdflayer error: " . $decoded['error']['info']);
            }

            // Save PDF
            File::put($pdfPath, $pdfData);

        } catch (\Throwable $e) {
            throw new Exception("HTML to PDF conversion failed: " . $e->getMessage());
        }

        if (!File::exists($pdfPath)) {
            throw new Exception("Failed to generate PDF file.");
        }

        return [
            'success'  => true,
            'filename' => $fileName,
            'url'      => asset('storage/html-pdfs/' . $fileName),
            'path'     => $pdfPath,
            'binary'   => $pdfData
        ];
    }
}
