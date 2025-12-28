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