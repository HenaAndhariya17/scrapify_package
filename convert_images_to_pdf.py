from PIL import Image
import sys, os

def convert_images_to_pdf(input_files, output_file):
    images = []
    for file in input_files:
        img = Image.open(file)

        # Convert to RGB to remove alpha
        if img.mode in ("RGBA", "LA"):
            background = Image.new("RGB", img.size, (255, 255, 255))
            background.paste(img, mask=img.split()[-1])
            img = background
        else:
            img = img.convert("RGB")

        images.append(img)

    # Save first image as PDF, append the rest
    images[0].save(output_file, save_all=True, append_images=images[1:])
    print(f"PDF generated: {output_file}")

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python convert_images_to_pdf.py <input_files> <output_file>")
        sys.exit(1)

    input_files = sys.argv[1:-1]
    output_file = sys.argv[-1]

    convert_images_to_pdf(input_files, output_file)
    print(f"PDF generated: {output_file}")
else:
    print("This script is intended to be run from the command line.")   