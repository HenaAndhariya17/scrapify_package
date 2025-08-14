import sys
import fitz  # PyMuPDF
import os

def add_page_numbers(input_pdf, output_pdf, position="bottom-right"):
    # Open PDF
    pdf = fitz.open(input_pdf)
    total_pages = len(pdf)

    for page_number in range(total_pages):
        page = pdf[page_number]
        width, height = page.rect.width, page.rect.height

        # Page number text
        text = str(page_number + 1)

        # Choose position
        if position == "top-left":
            x, y = 20, 20
        elif position == "top-right":
            x, y = width - 40, 20
        elif position == "bottom-left":
            x, y = 20, height - 30
        else:  # bottom-right default
            x, y = width - 40, height - 30

        page.insert_text((x, y), text, fontsize=12, color=(0, 0, 0))

    pdf.save(output_pdf)
    pdf.close()

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python add_page_numbers.py <input_pdf> <output_pdf> [position]")
        sys.exit(1)

    input_pdf = sys.argv[1]
    output_pdf = sys.argv[2]
    position = sys.argv[3] if len(sys.argv) > 3 else "bottom-right"

    add_page_numbers(input_pdf, output_pdf, position)
    print(f"Page numbers added to: {output_pdf}")
