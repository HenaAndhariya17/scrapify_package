import sys
import os
import pikepdf

def repair_pdf(input_pdf, output_pdf):
    try:
        # Open and save to repair the PDF
        with pikepdf.open(input_pdf) as pdf:
            pdf.save(output_pdf, linearize=True)
        print(f"Repaired PDF saved at: {output_pdf}")
    except Exception as e:
        # Try fallback using PyMuPDF
        import fitz
        try:
            doc = fitz.open(input_pdf)
            doc.save(output_pdf)
            doc.close()
            print(f"Fallback repair successful: {output_pdf}")
        except Exception as e2:
            print(f"Failed to repair PDF: {e2}")
            sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python repair_pdf.py <input_pdf> <output_pdf>")
        sys.exit(1)

    input_pdf = sys.argv[1]
    output_pdf = sys.argv[2]

    # Ensure directories exist
    os.makedirs(os.path.dirname(output_pdf), exist_ok=True)

    repair_pdf(input_pdf, output_pdf)
