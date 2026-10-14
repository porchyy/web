# 0003. สถาปัตยกรรม Domain Service Layer และระบบแคชประสิทธิภาพสูง (60s–120s TTL)

## สถานะ
ยอมรับแล้ว (Accepted)

## บริบท (Context)
ตามข้อกำหนดใน RULES.md (ข้อ 4: Separation of Concerns และข้อ 6: Performance Rules) ระบบจำเป็นต้องขจัดปัญหา "God Component" (ไฟล์เดียวทำหน้าที่ทั้งคิวรีฐานข้อมูล, จัดการฟอร์ม, เรนเดอร์ HTML, และจัดการ Business Logic) ซึ่งส่งผลให้โค้ดยากต่อการทดสอบ มีช่องโหว่ความปลอดภัย และเกิดภาระต่อฐานข้อมูลจากการส่งคิวรีซ้ำๆ

## การตัดสินใจ (Decision)
1. **การแยกเลเยอร์บริการ (Domain Service Layer)**:
   - ตรรกะทางธุรกิจและการติดต่อฐานข้อมูล (Data Access Layer) ทั้งหมดถูกย้ายเข้าสู่คลาสบริการใน `includes/services/` ประกอบด้วย:
     - `AuthService.php`: บริหารจัดการสิทธิ์, การเข้าสู่ระบบ, การตรวจสอบบทบาท และ Argon2id Hashing
     - `CacheService.php`: ระบบจัดเก็บและจัดการแคชข้อมูลระยะสั้น (Short-Lived Caching)
     - `FacultyService.php`: การจัดการคณาจารย์และสมาชิกชมรมวิชาชีพ (Tree APIs & Position Swap)
     - `NewsService.php`: การจัดการบทความข่าวสาร, หมวดหมู่ข่าว และการกรองข้อมูล
     - `ScheduleService.php`: การคำนวณและประมวลผลตารางเรียนมาตรฐาน สอศ.
     - `SettingService.php`: การเข้าถึงและปรับปรุงการตั้งค่าระบบ สถิติหน้าแรก และข้อมูลติดต่อ
   - ห้ามเขียนคำสั่ง SQL หรือเรียกใช้ `q()` ในไฟล์ Presentation (`pages/*.php`) หรือ Controller (`admin/*.php`) โดยเด็ดขาด
2. **ระบบแคชข้อมูลสั้น (60s–120s TTL) และ Instant Invalidation**:
   - กำหนดอายุแคชข้อมูลสาธารณะที่ถูกเรียกบ่อยไว้ที่ 60–120 วินาที ผ่าน `CacheService`
   - เมื่อมีการสร้าง แก้ไข หรือลบข้อมูล (CUD) ผ่านแผงควบคุมหลังบ้าน เมธอดใน Service จะสั่งล้างคีย์แคชที่เกี่ยวข้องทันที (`CacheService::forget()`) เพื่อป้องกันไม่ให้ผู้ดูแลระบบเห็นข้อมูลเก่า
3. **HTTP Header Caching**:
   - หน้าสาธารณะส่ง Header `Cache-Control: public, max-age=60, s-maxage=120, stale-while-revalidate=30`
   - หน้าหลังบ้านส่ง Header `Cache-Control: no-store, no-cache, must-revalidate, private`

## ผลลัพธ์และข้อดี (Consequences)
- สถาปัตยกรรมมีความเป็นโมดูลาร์สูง แยกหน้าที่ชัดเจน (Separation of Concerns)
- หน้าเว็บสาธารณะโหลดเร็วในระดับไม่กี่มิลลิวินาที ลดภาระการทำงานของ MySQL ได้อย่างมหาศาล
- ข้อมูลที่อัปเดตผ่านหลังบ้านสะท้อนผลสู่หน้าแรกและหน้าแสดงผลทันทีโดยไม่มีปัญหาข้อมูลค้างแคช
- โค้ดแต่ละ Service สามารถเขียนแบบทดสอบ Unit/Integration Test ได้อย่างเป็นอิสระ
