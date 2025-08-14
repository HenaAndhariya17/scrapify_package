import sys
import subprocess
import os
import platform

input_file = sys.argv[1]
output_file = sys.argv[2]

# Detect OS
if platform.system().lower().startswith('win'):
    gs_binary = r"C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe"
    os.environ["TEMP"] = r"C:\laragon\tmp"
    os.environ["TMP"] = r"C:\laragon\tmp"
    os.makedirs(r"C:\laragon\tmp", exist_ok=True)
else:
    gs_binary = "gs"  # Linux Hostinger

gs_command = [
    gs_binary,
    "-sDEVICE=pdfwrite",
    "-dCompatibilityLevel=1.4",
    "-dPDFSETTINGS=/screen",
    "-dNOPAUSE",
    "-dQUIET",
    "-dBATCH",
    f"-sOutputFile={output_file}",
    input_file,
]

subprocess.run(gs_command, check=True)
