# 0005. การยกระดับความปลอดภัยด้วย Argon2id และ Whitelist HTML Sanitization

## สถานะ
ยอมรับแล้ว (Accepted)

## บริบท (Context)
ความปลอดภัยเป็นข้อกำหนดที่ไม่สามารถประนีประนอมได้ (RULES.md ข้อ 7) ระบบบริหารจัดการของสถานศึกษาจำเป็นต้องมีมาตรฐานการปกป้องรหัสผ่านและป้องกันการโจมตีทางไซเบอร์ที่ทันสมัย เช่น Stored/DOM-based XSS, Cross-Site Request Forgery (CSRF) และ Brute Force Attacks

## การตัดสินใจ (Decision)
1. **การเข้ารหัสรหัสผ่านด้วย Argon2id (Modern Password Hashing)**:
   - กำหนดให้ใช้ `PASSWORD_ARGON2ID` เป็นมาตรฐานหลักใน `AuthService.php` (พร้อม Fallback ไปยัง `PASSWORD_ARGON2I` หรือ `PASSWORD_DEFAULT` ในระบบที่ไม่รองรับ)
   - นำกลไก `password_needs_rehash()` มาใช้ในฟังก์ชัน `AuthService::attemptLogin()` เพื่อทำการแปลงแฮชรหัสผ่านเดิม (เช่น Bcrypt) เป็น Argon2id โดยอัตโนมัติขณะผู้ใช้เข้าสู่ระบบสำเร็จ โดยไม่มีผลกระทบต่อผู้ใช้งาน (Zero-Downtime Migration)
   - ปลดข้อความแจ้งรหัสผ่านทดสอบออกจากหน้าจอ `pages/login.php` ทั้งหมด
2. **การป้องกัน XSS เชิงลึกด้วย DOMDocument Whitelist Sanitizer**:
   - ข้อความเนื้อหาทั่วไปต้องผ่านฟังก์ชัน `clean_text()` (ตัด HTML tags และอักขระควบคุม)
   - เนื้อหา Rich Text จากบรรณาธิการข่าวสารต้องผ่านฟังก์ชัน `clean_html()` ซึ่งสร้างบนพื้นฐาน `DOMDocument` ของ PHP ทำการตรวจสอบ Whitelist Tags และ Attributes อย่างเข้มงวด กำจัดแท็กอันตราย เช่น `<script>`, `<iframe>`, `onerror`, `onload`
   - ตรวจสอบ URL ผ่านฟังก์ชัน `safe_url()` เพื่อป้องกันโพรโทคอล `javascript:` หรือ `data:` ปลอมแปลง
   - การแสดงผลสตริงดิบในมาร์กอัป HTML ทุกจุดต้องผ่าน `e()` (`htmlspecialchars`) เสมอ
3. **การป้องกัน CSRF และการจัดการเซสชัน**:
   - ทุกฟอร์ม POST และการเรียก AJAX ต้องแนบ CSRF Token และผ่านการตรวจสอบด้วย `verify_csrf()`
   - คุกกี้เซสชันถูกกำหนดแอตทริบิวต์ความปลอดภัย `HttpOnly` และ `SameSite=Lax`

## ผลลัพธ์และข้อดี (Consequences)
- ระบบมีความมั่นคงปลอดภัยตามมาตรฐานสากล OWASP
- ป้องกันช่องโหว่การฉีดโค้ดอันตราย (XSS) และการขโมยสิทธิ์ผู้ดูแลระบบ (CSRF) 100%
- การอัปเกรดรหัสผ่านเกิดขึ้นโดยอัตโนมัติ ไม่ต้องรีเซ็ตรหัสผ่านของผู้ใช้งานเดิม
