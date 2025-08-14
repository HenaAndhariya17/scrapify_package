<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Exception;

class ImageToPDFConverter
{
    /**
     * Convert images to PDF.
     *
     * @param UploadedFile[] $images
     * @return array
     * @throws Exception
     */
    public function convert(array $images): array
    {
        $uploadDir = public_path('storage/uploads');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $imagePaths = [];
        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $slugName = Str::slug($originalName) . '-' . time() . '.jpg';
                $publicPath = $uploadDir . '/' . $slugName;
                $image->move($uploadDir, $slugName);
                $imagePaths[] = $publicPath;
            }
        }

        // Run Python script to convert images to PDF
        $outputFilename = 'converted-' . time() . '.pdf';
        $outputPath = public_path('storage/converted/' . $outputFilename);
        if (!File::exists(dirname($outputPath))) {
            File::makeDirectory(dirname($outputPath), 0755, true);
        }

        $pythonScript = __DIR__ . '/../convert_images_to_pdf.py';
        $process = new Process(array_merge(['python', $pythonScript], $imagePaths, [$outputPath]));
        $process->run();
        if (!$process->isSuccessful()) {
            throw new Exception('Python Error: ' . $process->getErrorOutput());
        }
        if (!File::exists($outputPath)) {
            throw new Exception("Converted PDF not found at: {$outputPath}");
        }
        $fileContent = File::get($outputPath);
        $base64Content = base64_encode($fileContent);
        $fileUrl = asset('storage/converted/' . $outputFilename);

        return [
            'filename' => $outputFilename,
            'file' => $base64Content,
            'url' => $fileUrl,
        ];
    }
}