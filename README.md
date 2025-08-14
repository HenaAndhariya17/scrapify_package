📄 Scrapify PDF Tools Library

A Laravel package for **all-in-one PDF processing** — merge, split, compress, convert, OCR, watermark, protect, and more.
Includes **20+ tools** for complete document control.

---

## 📦 Installation

```bash
composer require scrapify-dev/image-tools
```

---

## ⚙️ Requirements

* **PHP**: `^8.0`
* **Laravel**: `^9.0 | ^10.0 | ^11.0 | ^12.0`
* **Dependencies:**

  * `php`: `^8.0`
  * `illuminate/support`: `^9.0|^10.0|^11.0|^12.0`
  * `dompdf/dompdf`: `^3.1`
  * `phpoffice/phpword`: `^1.4`
  * `phpoffice/phpspreadsheet`: `^4.4`
  * `smalot/pdfparser`: `2.12.0`
  * `setasign/fpdi`: `^2.3`
  * `tecnickcom/tcpdf`: `^6.7`
  * `spatie/pdf-to-image`: `^2.3`
  * `symfony/process`: `^6.0|^7.0`

---

## 📑 Tools Overview & Usage Examples

### 1️⃣ Merge PDF

```php
use Scrapify\PdfTools\PdfMerge;

$pdfMerge = new PdfMerge();
$pdfMerge->merge(
    [public_path('file1.pdf'), public_path('file2.pdf')],
    public_path('merged.pdf')
);
```

### 2️⃣ Split PDF

```php
use Scrapify\PdfTools\PdfSplit;

$pdfSplit = new PdfSplit();
$pdfSplit->split(public_path('file.pdf'), storage_path('app/public/splits'));
```

### 3️⃣ Compress PDF

```php
use Scrapify\PdfTools\PdfCompressor;

$compressor = new PdfCompressor();
$compressor->compress(public_path('big.pdf'), public_path('small.pdf'));
```

### 4️⃣ Office to PDF

```php
use Scrapify\PdfTools\OfficeToPdf;

$converter = new OfficeToPdf();
$converter->convert(public_path('document.docx'), public_path('output.pdf'));
```

### 5️⃣ PDF OCR

```php
use Scrapify\PdfTools\PdfOcr;

$ocr = new PdfOcr();
$text = $ocr->extractText(public_path('scanned.pdf'));
```

### 6️⃣ Rotate PDF

```php
use Scrapify\PdfTools\PdfRotate;

$rotator = new PdfRotate();
$rotator->rotate(public_path('file.pdf'), 90, public_path('rotated.pdf'));
```

### 7️⃣ PDF to JPG

```php
use Scrapify\PdfTools\PdfToImage;

$pdfToImage = new PdfToImage(public_path('file.pdf'));
$pdfToImage->saveImage(public_path('images/page_%d.jpg'));
```

### 8️⃣ Image to PDF

```php
use Scrapify\PdfTools\ImageToPdf;

$imageToPdf = new ImageToPdf();
$imageToPdf->convert([public_path('img1.png'), public_path('img2.jpg')], public_path('output.pdf'));
```

### 9️⃣ Unlock PDF

```php
use Scrapify\PdfTools\PdfUnlock;

$unlocker = new PdfUnlock();
$unlocker->unlock(public_path('locked.pdf'), 'password', public_path('unlocked.pdf'));
```

### 🔟 Watermark PDF

```php
use Scrapify\PdfTools\PdfWatermark;

$watermark = new PdfWatermark();
$watermark->apply(public_path('file.pdf'), public_path('logo.png'), public_path('watermarked.pdf'));
```

### 1️⃣1️⃣ Page Number PDF

```php
use Scrapify\PdfTools\PdfPageNumber;

$pageNumber = new PdfPageNumber();
$pageNumber->addNumbers(public_path('file.pdf'), public_path('numbered.pdf'));
```

### 1️⃣2️⃣ Repair PDF

```php
use Scrapify\PdfTools\PdfRepair;

$repair = new PdfRepair();
$repair->repair(public_path('corrupt.pdf'), public_path('fixed.pdf'));
```

### 1️⃣3️⃣ PDF to PDF/A

```php
use Scrapify\PdfTools\PdfToPdfA;

$pdfToPdfA = new PdfToPdfA();
$pdfToPdfA->convert(public_path('file.pdf'), public_path('archival.pdf'));
```

### 1️⃣4️⃣ Protect PDF

```php
use Scrapify\PdfTools\PdfProtect;

$protect = new PdfProtect();
$protect->protect(public_path('file.pdf'), 'ownerpass', 'userpass', public_path('protected.pdf'));
```

### 1️⃣5️⃣ Validate PDF/A

```php
use Scrapify\PdfTools\PdfValidatePdfA;

$validator = new PdfValidatePdfA();
$isValid = $validator->validate(public_path('archival.pdf'));
```

### 1️⃣6️⃣ Extract PDF

```php
use Scrapify\PdfTools\PdfExtract;

$extract = new PdfExtract();
$extract->extract(public_path('file.pdf'), [1, 3, 5], public_path('extracted.pdf'));
```

### 1️⃣7️⃣ Organize PDF

```php
use Scrapify\PdfTools\PdfOrganize;

$organize = new PdfOrganize();
$organize->reorder(public_path('file.pdf'), [3, 1, 2], public_path('reordered.pdf'));
```

### 1️⃣8️⃣ HTML to PDF

```php
use Scrapify\PdfTools\HtmlToPdf;

$htmlToPdf = new HtmlToPdf();
$htmlToPdf->convert('<h1>Hello</h1>', public_path('output.pdf'));
```

### 1️⃣9️⃣ Edit PDF

```php
use Scrapify\PdfTools\PdfEdit;

$editor = new PdfEdit();
$editor->addText(public_path('file.pdf'), 'Confidential', public_path('edited.pdf'));
```

### 2️⃣0️⃣ PDF to Word

```php
use Scrapify\PdfTools\PdfToWord;

$pdfToWord = new PdfToWord();
$pdfToWord->convert(public_path('file.pdf'), public_path('output.docx'));
```