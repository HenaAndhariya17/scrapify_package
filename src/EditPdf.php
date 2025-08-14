<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Exception;

class EditPdf
{
    /**
     * Handle demo PDF upload.
     *
     * @param UploadedFile $pdfFile
     * @return array
     * @throws Exception
     */
    public function uploadDemo(UploadedFile $pdfFile): array
    {
        if (!$pdfFile->isValid()) {
            throw new Exception("Invalid PDF file upload.");
        }

        // Create storage folder for demo uploads
        $pdfDir = public_path('storage/edit-pdfs');
        if (!File::exists($pdfDir)) {
            File::makeDirectory($pdfDir, 0755, true);
        }

        // Generate unique filename
        $baseName = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = $baseName . '_' . time() . '.pdf';
        $filePath = $pdfDir . '/' . $filename;

        // Move uploaded file to storage
        $pdfFile->move($pdfDir, $filename);

        return [
            'filename' => $filename,
            'file'     => base64_encode(File::get($filePath)), // Optional base64
            'url'      => asset('storage/edit-pdfs/' . $filename),
        ];
    }

    /**
     * Generic PDF uploader (for editor)
     *
     * @param UploadedFile $pdfFile
     * @param string $folder
     * @return string URL
     * @throws Exception
     */
    public function upload(UploadedFile $pdfFile, string $folder = 'pdfs'): string
    {
        if (!$pdfFile->isValid()) {
            throw new Exception("Invalid PDF file upload.");
        }
        // Create storage folder if it doesn't exist
        $pdfDir = public_path('storage/' . $folder);
        if (!File::exists($pdfDir)) {
            File::makeDirectory($pdfDir, 0755, true);
        }

        $fileName = 'pdf_' . time() . '.pdf';
        $pdfFile->move($pdfDir, $fileName);

        return asset('storage/' . $folder . '/' . $fileName);
    }
}
