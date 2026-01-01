N8N_WEBHOOK_URL=http://n8n:5678/webhook-test/ijazah-verification

Solusi 1 (PALING DIREKOMENDASIKAN): Samakan Ownership
Jalankan di host (bukan di container):
sudo chown -R 1000:1000 src/storage/app/ijazah
sudo chmod -R 775 src/storage/app/ijazah


Lalu restart container n8n:

docker compose restart n8n

Verifikasi di container n8n:
docker exec -it akadify_n8n ls -l /home/node/.n8n-files/ijazah


Harus terlihat:

node node ijazah.jpg


Jika sudah, Read/Write File node akan langsung jalan.

Callback from n8n (OCR / verification result)
-------------------------------------------

When n8n finishes OCR or verification, it should POST back to your app at `/api/verifications` with either:

- a `verification_id` (to update the existing `Verification` record) and `valid` (boolean), or
- a `verification_id` and `status` (string `VERIFIED` or `REJECTED`).

Example payloads:

1) Using `valid` boolean:
{
	"verification_id": 123,
	"valid": true,
	"reason": null
}

2) Using explicit `status`:
{
	"verification_id": 123,
	"status": "VERIFIED",
	"reason": null
}

The app will update the existing `Verification` record's `status` and `reason` accordingly and dispatch the `verification_verified` / `verification_rejected` event.