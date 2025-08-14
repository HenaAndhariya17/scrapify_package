import sys
import os
from PyPDF2 import PdfReader, PdfWriter
from reportlab.pdfgen import canvas
from reportlab.lib.pagesizes import letter
from reportlab.lib.units import inch
import io

def create_watermark(watermark_text):
    """Create a temporary watermark PDF in memory"""
    packet = io.BytesIO()
    c = canvas.Canvas(packet, pagesize=letter)
    
    # Set transparency for watermark
    c.setFont("Helvetica-Bold", 50)
    c.setFillGray(0.5, 0.3)  # 50% gray, 30% opacity
    c.saveState()
    
    # Rotate text and position it diagonally
    c.translate(300, 400)
    c.rotate(45)
    c.drawCentredString(0, 0, watermark_text)
    c.restoreState()
    c.save()
    
    packet.seek(0)
    return PdfReader(packet)

def add_watermark(input_pdf, output_pdf, watermark_text):
    watermark_pdf = create_watermark(watermark_text)
    watermark_page = watermark_pdf.pages[0]

    reader = PdfReader(input_pdf)
    writer = PdfWriter()

    for page in reader.pages:
        page.merge_page(watermark_page)  # Overlay watermark
        writer.add_page(page)

    with open(output_pdf, "wb") as output_file:
        writer.write(output_file)

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print("Usage: python watermark_pdf.py <input_pdf> <output_pdf> <watermark_text>")
        sys.exit(1)

    input_pdf = sys.argv[1]
    output_pdf = sys.argv[2]
    watermark_text = sys.argv[3]

    if not os.path.exists(input_pdf):
        print(f"Error: Input PDF '{input_pdf}' not found")
        sys.exit(1)

    add_watermark(input_pdf, output_pdf, watermark_text)
    print(f"Watermarked PDF saved to {output_pdf}")
