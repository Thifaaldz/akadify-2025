from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import pytesseract
from PIL import Image
import re
import os

app = FastAPI(title="Ijazah OCR Service")


# ==========================
# Request Schema
# ==========================
class OCRRequest(BaseModel):
    phone: str
    file_path: str


# ==========================
# Helper Functions
# ==========================
def normalize(text: str) -> str:
    return re.sub(r'\s+', ' ', text).strip().lower()


def extract_tahun_lulus(text: str):
    lines = text.split('\n')
    for line in reversed(lines):
        if 'kabupaten' in line or 'kota' in line:
            match = re.search(r'(20\d{2})', line)
            if match:
                return match.group(1)
    return None


# ==========================
# OCR Core Logic
# ==========================
def process_ocr(path: str) -> dict:
    if not os.path.exists(path):
        raise HTTPException(status_code=404, detail="File not found")

    img = Image.open(path)
    img = img.convert('L')
    img = img.point(lambda x: 0 if x < 140 else 255)

    raw_text = pytesseract.image_to_string(
        img,
        lang='ind+eng'
    ).lower()

    result = {
        "nisn": None,
        "nama": None,
        "tahun_lulus": None,
        "sekolah": None,
        "raw_text": raw_text
    }

    # NISN
    nisn_match = re.search(r'\b\d{10}\b', raw_text)
    if nisn_match:
        result["nisn"] = nisn_match.group()

    # Nama
    lines = [l.strip() for l in raw_text.split('\n') if l.strip()]
    for i, line in enumerate(lines):
        if 'dengan ini menyatakan' in line or 'dengan ini menerangkan' in line:
            if i + 1 < len(lines):
                result["nama"] = normalize(lines[i + 1])
                break

    # Tahun Lulus
    result["tahun_lulus"] = extract_tahun_lulus(raw_text)

    # Sekolah
    for line in lines:
        if 'satuan pendidikan' in line or 'saluan pendidikan' in line:
            result["sekolah"] = normalize(
                line.replace('satuan pendidikan', '')
                    .replace('saluan pendidikan', '')
                    .replace(':', '')
            )
            break

    return result


# ==========================
# API Endpoint
# ==========================
@app.post("/ocr")
def ocr_endpoint(payload: OCRRequest):
    ocr_result = process_ocr(payload.file_path)

    return {
        "phone": payload.phone,
        "file_path": payload.file_path,
        **ocr_result
    }
