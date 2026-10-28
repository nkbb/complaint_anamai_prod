# HANDOFF — ระบบรับเรื่องร้องเรียน กรมอนามัย
> อัปเดต: 2026-10-03 | Laravel 12 + Vue 3 | dev: nkbbproch7

## ค้างอยู่ไหน
- ขึ้นระบบภายในแล้ว: https://tcp.anamai.moph.go.th (Docker บน complaint-host 10.100.2.19) — **ยังไม่เปิดใช้จริง**
- รอ dev: แก้ช่องโหว่ 14 ข้อ (Critical 3) + ทดสอบระบบ + Email OTP ตามแนวทางที่ส่งทาง LINE
- จากนั้น: VA Scan → ลบข้อมูลทดสอบ/บัญชี dev → เปลี่ยน domain complaint.anamai.moph.go.th

## เริ่มที่ไหน
- dev: push เข้า `main` แล้วแจ้ง IT ให้ deploy (dev ไม่มีสิทธิ์เข้า server)
- วิธี deploy / config production: repo `doh/oots/complaint/complaint-deploy`
- รายละเอียดช่องโหว่: รายงาน Security Review 2026-09-30 (ขอจาก IT — ไม่เก็บใน repo)

## ข้อควรระวัง
- แอปอยู่หลัง reverse proxy (IIS) — `trustProxies` ใน `bootstrap/app.php` ห้ามลบ
- ห้ามใช้ `config:cache` จนกว่าจะแก้ `KEY_CRYPT` ใน `Helper.php` ให้อ่านผ่าน `config()`
- ห้ามเรียก `UserSeeder` (สร้าง admin/admin) และห้ามเรียก seeder จาก migration
- ห้าม commit `.env` / credential · ย่อรูปก่อน commit (GitLab จำกัด push 50 MiB)
