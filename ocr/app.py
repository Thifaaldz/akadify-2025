from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import pytesseract
from PIL import Image
import re
import os

app = FastAPI(title="Ijazah OCR Service")


class OCRRequest(BaseModel):
    phone: str
    file_path: str
    student_id: str


# ==========================
# Helper Functions
# ==========================
def normalize(text: str) -> str:
    return re.sub(r'\s+', ' ', text).strip().lower()


def extract_tahun_lulus(text: str):
    for line in reversed(text.split('\n')):
        if 'kabupaten' in line or 'kota' in line:
            match = re.search(r'(20\d{2})', line)
            if match:
                return match.group(1)
    return None


# ==========================
# OCR Core Logic
# ==========================
def process_ocr(path: str):
    if not os.path.exists(path):
        raise HTTPException(status_code=404, detail="File not found")

    img = Image.open(path)
    img = img.convert('L')
    img = img.point(lambda x: 0 if x < 140 else 255)

    raw_text = pytesseract.image_to_string(
        img,
        lang='ind+eng',
        config='--oem 3 --psm 6'
    )

    text = raw_text.lower()

    result = {
        "nama": None,
        "nisn": None,
        "sekolah": None,
        "tahun_lulus": None,
        "raw_text": text
    }

    # =====================
    # NISN
    # =====================
    nisn_match = re.search(r'\b\d{10}\b', text)
    if nisn_match:
        result["nisn"] = nisn_match.group()

    # =====================
    # NAMA (BOUNDARY-BASED, OCR-SAFE)
    # =====================
    nama_match = re.search(
        r'menyatakan\s+bahwa\s*[:\-]?\s*(.*?)\s*tempat,\s*tanggal\s*lahir',
        text,
        re.DOTALL | re.IGNORECASE
    )

    if nama_match:
        kandidat = re.sub(r'[^a-z\s]', '', nama_match.group(1))
        kandidat = normalize(kandidat)
        if len(kandidat.split()) >= 2:
            result["nama"] = kandidat

    # =====================
    # TAHUN LULUS
    # =====================
    result["tahun_lulus"] = extract_tahun_lulus(text)

    # =====================
    # SEKOLAH
    # =====================
    for line in text.split('\n'):
        if 'satuan pendidikan' in line or 'saluan pendidikan' in line:
            cleaned = (
                line.replace('satuan pendidikan', '')
                    .replace('saluan pendidikan', '')
                    .replace(':', '')
            )
            result["sekolah"] = normalize(cleaned)
            break

    return result


# ==========================
# API Endpoint (DATA ONLY)
# ==========================
@app.post("/ocr")
def ocr_endpoint(payload: OCRRequest):
    ocr = process_ocr(payload.file_path)

    return {
        "phone": payload.phone,
        "file_path": payload.file_path,
        "student_id": payload.student_id,
        "nama_ocr": ocr["nama"],
        "nisn_ocr": ocr["nisn"],
        "sekolah_ocr": ocr["sekolah"],
        "tahun_lulus_ocr": ocr["tahun_lulus"],
        "raw_text": ocr["raw_text"]
    }
