import sys
import os
from PyPDF2 import PdfReader, PdfWriter

def unlock_pdf(input_path, output_path, password):
    try:
        # Open the encrypted PDF
        reader = PdfReader(input_path)

        # Try to decrypt the PDF
        if reader.is_encrypted:
            if reader.decrypt(password) == 0:
                print(f"Failed to decrypt PDF. Incorrect password.")
                sys.exit(1)
        else:
            print("PDF is not encrypted. Copying as is.")

        # Write unlocked PDF
        writer = PdfWriter()
        for page in reader.pages:
            writer.add_page(page)

        with open(output_path, "wb") as f:
            writer.write(f)

        print(f"Unlocked PDF saved to {output_path}")
        sys.exit(0)
    except Exception as e:
        print(f"Error unlocking PDF: {e}")
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python unlock_pdf.py <input_pdf> <output_pdf> <password>")
        sys.exit(1)

    input_pdf = sys.argv[1]
    output_pdf = sys.argv[2]
    password = sys.argv[3]

    unlock_pdf(input_pdf, output_pdf, password)
