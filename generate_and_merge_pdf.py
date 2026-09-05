import os
import sys
from pathlib import Path

# Packages required: reportlab, PyPDF2, markdown2
# This script reads the markdown supplement files, creates a PDF with their content,
# and merges it into the existing final report PDF.

# Define paths
BASE_DIR = Path(__file__).parent
DOCS_DIR = BASE_DIR / "docs"
OUTPUT_PDF = DOCS_DIR / "rapport_buca_vip_final_merged.pdf"
ORIGINAL_PDF = DOCS_DIR / "rapport_buca_vip_final.pdf"
SUPPLEMENT_PDF = DOCS_DIR / "supplements_combined.pdf"

# List of markdown files in the order they should appear
markdown_files = [
    "supplements_introduction.md",
    "supplements_etat_de_l_art.md",
    "supplements_etude_existant.md",
    "supplements_analyse_detaillee.md",
    "supplements_conception_detaillee.md",
    "supplements_code_implementation.md",
    "supplements_tests_and_user_guide.md",
    "supplements_perspectives.md",
    "guide_integration_rapport.md",
]

# ---------- Step 1: Build a temporary PDF from the markdown ----------
from reportlab.lib.pagesizes import A4
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, PageBreak
from reportlab.lib.styles import getSampleStyleSheet
from reportlab.lib.enums import TA_JUSTIFY
import markdown2

styles = getSampleStyleSheet()
# Use justified style for body text
body_style = styles["Normal"].clone('body')
body_style.alignment = TA_JUSTIFY
body_style.fontName = "Helvetica"
body_style.fontSize = 11
body_style.leading = 14

def markdown_to_flowables(md_text):
    """Convert markdown text to a list of ReportLab flowables (Paragraphs)."""
    # Convert markdown to HTML first, then strip tags for plain text paragraphs.
    # For simplicity we keep the raw markdown as plain text paragraphs.
    paragraphs = []
    for line in md_text.splitlines():
        # Skip empty lines to avoid excessive spacing
        if line.strip() == "":
            paragraphs.append(Spacer(1, 6))
            continue
        # Escape special XML characters
        line = line.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
        paragraphs.append(Paragraph(line, body_style))
        paragraphs.append(Spacer(1, 6))
    return paragraphs

# Collect flowables from all markdown files
flowables = []
for md_file in markdown_files:
    md_path = DOCS_DIR / md_file
    if not md_path.is_file():
        print(f"Warning: {md_file} not found, skipping.")
        continue
    with open(md_path, "r", encoding="utf-8") as f:
        md_content = f.read()
    flowables.extend(markdown_to_flowables(md_content))
    flowables.append(PageBreak())

# Create the temporary PDF containing the supplements
print("Generating temporary PDF with supplements...")
SimpleDocTemplate(str(SUPPLEMENT_PDF), pagesize=A4, rightMargin=40, leftMargin=40, topMargin=40, bottomMargin=40).build(flowables)

# ---------- Step 2: Merge the supplement PDF with the original report ----------
from PyPDF2 import PdfReader, PdfWriter

if not ORIGINAL_PDF.is_file():
    print(f"Error: Original PDF '{ORIGINAL_PDF}' not found.")
    sys.exit(1)

print("Merging original report with supplements...")
original_reader = PdfReader(str(ORIGINAL_PDF))
supplement_reader = PdfReader(str(SUPPLEMENT_PDF))
writer = PdfWriter()

# Append pages from original PDF first
for page in original_reader.pages:
    writer.add_page(page)

# Then append supplement pages
for page in supplement_reader.pages:
    writer.add_page(page)

# Write out the merged PDF
with open(str(OUTPUT_PDF), "wb") as out_f:
    writer.write(out_f)

print(f"Merged PDF created at: {OUTPUT_PDF}")
