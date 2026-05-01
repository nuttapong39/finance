# ระบบบริหารจัดการการเงินและบัญชี (Finance Management System)

> ระบบบริหารจัดการการเงินและบัญชีสำหรับหน่วยงานภาครัฐ พัฒนาด้วย PHP + MySQL รองรับการทำงานบน XAMPP (Local) และ Web Hosting

---

## ภาพรวมระบบ

ระบบจัดการเอกสารทางการเงิน ตั้งแต่การบันทึกเจ้าหนี้การค้า ไปจนถึงการเบิกจ่ายเงินผ่านเช็ค พร้อมระบบแจ้งเตือน LINE และ Dashboard วิเคราะห์ข้อมูล

---

## Features หลัก

### การเงินและบัญชี
- **บันทึกเจ้าหนี้การค้า** — บันทึกรายการสั่งจ่าย พร้อมหักภาษี ณ ที่จ่าย และแยกแหล่งเงิน
- **อนุมัติและจัดทำเช็ค** — ระบบ workflow อนุมัติ → จัดทำเช็ค → เบิกจ่าย
- **บันทึกรับเอกสาร** — รับเอกสารทางการเงินและติดตามสถานะ
- **รายงานค่าใช้จ่าย** — Export รายงานหลายรูปแบบ (Excel, Print)

### แผนงบประมาณ
- **แผนเงินบำรุง** — ติดตามค่าใช้จ่ายตามแผนเงินบำรุง แยกตามหมวดหมู่
- **แผนเงินงบประมาณ** — ติดตามค่าใช้จ่ายตามแผนเงินงบประมาณ
- **ตั้งประมาณการรายปี** — กำหนดวงเงินประมาณการแต่ละรายการต่อปีงบประมาณ

### Dashboard & วิเคราะห์
- **Dashboard** — ภาพรวมค่าใช้จ่าย กราฟรายเดือน แยกแหล่งเงิน Top บริษัท/กลุ่มงาน
- **สถิติและรายงาน** — ค้นหาและกรองรายการตามเงื่อนไขต่างๆ
- **Pipeline สถานะ** — ติดตามรายการแต่ละขั้นตอน

### ระบบแจ้งเตือน LINE
- แจ้งเตือนเมื่อ **บันทึกรายการใหม่** (🟡 รอรับเอกสารการเงิน)
- แจ้งเตือนเมื่อ **อนุมัติ** (🟣 อยู่ระหว่างจัดทำเช็ค)
- แจ้งเตือนเมื่อ **เบิกจ่ายเสร็จ** (🔵 ดำเนินการเบิกจ่าย)
- แจ้งเตือนเมื่อ **สำรองข้อมูลสำเร็จ**

### สำรองข้อมูล
- Export SQL dump ทั้งฐานข้อมูลผ่านเบราว์เซอร์ ดาวน์โหลดมายัง PC ได้ทันที
- ไม่ต้องใช้ `mysqldump` หรือสิทธิ์ Shell บน Hosting

---

## Tech Stack

| ส่วน | เทคโนโลยี |
|------|-----------|
| Backend | PHP 8.x (Procedural) |
| Database | MySQL / MariaDB |
| Frontend | Bootstrap 5.3.3 |
| Charts | Chart.js 4.4.3 |
| Icons | Google Material Symbols |
| Alerts | SweetAlert2 |
| Datepicker | Flatpickr (Thai Buddhist calendar) |
| Notification | MOPH ALERT API (LINE Flex Message) |
| Server | XAMPP (Apache + MySQL) |

---

## โครงสร้างไฟล์

```
finance/
├── index.php               # หน้า Login
├── login_process.php       # ประมวลผล Login
├── header.php              # Navigation bar (ทุกหน้าใช้ร่วมกัน)
├── dashboard.php           # Dashboard วิเคราะห์ข้อมูล
│
├── accounting.php          # บันทึกเจ้าหนี้การค้า/รายการสั่งจ่าย
├── save.php                # บันทึกข้อมูลลง DB
├── finance.php             # รายการรอดำเนินการ
├── approved.php            # อนุมัติรายการ
├── cheque.php              # จัดการเช็ค
├── addcheque.php           # บันทึกเลขที่เช็ค
├── receive.php             # รับเอกสาร
├── paid.php                # รายการที่จ่ายแล้ว
├── paidment.php            # บันทึกการจ่าย
│
├── plan.php                # แผนค่าใช้จ่ายเงินบำรุง
├── planbudget.php          # แผนค่าใช้จ่ายงบประมาณ
├── setplan.php             # ตั้งค่าประมาณการ
├── statistics.php          # สถิติและรายงาน
│
├── backup.php              # สำรองข้อมูล SQL
├── notify_helper.php       # Helper ส่ง LINE MOPH ALERT
├── moph_alert_config.php   # Config key ระบบแจ้งเตือน (ไม่ commit)
├── connect_db.php          # เชื่อมต่อ Database
│
├── css/
│   └── theme.css           # Custom CSS หลัก
└── pic/                    # รูปภาพและ assets
```

---

## การติดตั้ง

### 1. ติดตั้ง XAMPP
ดาวน์โหลด [XAMPP](https://www.apachefriends.org/) และเปิดใช้งาน **Apache** และ **MySQL**

### 2. Clone โปรเจกต์
```bash
git clone https://github.com/nuttapong39/finance.git C:/xampp/htdocs/finance
```

### 3. สร้างฐานข้อมูล
- เปิด phpMyAdmin: `http://localhost/phpmyadmin`
- สร้างฐานข้อมูลใหม่ เช่น `finance_db`
- Import ไฟล์ SQL (จาก backup.php หรือไฟล์ `.sql` ที่มี)

### 4. ตั้งค่าเชื่อมต่อ DB
แก้ไขไฟล์ `connect_db.php`:
```php
$conn = new mysqli('localhost', 'root', '', 'finance_db');
```

### 5. ตั้งค่า MOPH ALERT (ถ้าต้องการใช้แจ้งเตือน LINE)
แก้ไขไฟล์ `moph_alert_config.php`:
```php
define('MOPH_CLIENT_KEY', 'your-client-key');
define('MOPH_SECRET_KEY', 'your-secret-key');
```

### 6. เปิดใช้งาน
เปิดเบราว์เซอร์: `http://localhost/finance`

---

## Workflow การเบิกจ่าย

```
บันทึกรายการ (accounting.php)
        ↓ 🟡 รอรับเอกสารการเงิน
รับเอกสาร (receive.php)
        ↓
อนุมัติรายการ (finance.php → approved.php)
        ↓ 🟣 อยู่ระหว่างจัดทำเช็ค
จัดทำเช็ค (cheque.php → addcheque.php)
        ↓ 🔵 ดำเนินการเบิกจ่าย
เบิกจ่ายเสร็จสิ้น (paid.php)
```

---

## ความต้องการของระบบ

- PHP >= 7.4 (แนะนำ 8.x)
- MySQL >= 5.7 หรือ MariaDB >= 10.4
- Apache Web Server
- Extension: `mysqli`, `curl`, `mbstring`

---

## License

โปรเจกต์นี้พัฒนาสำหรับใช้งานภายในหน่วยงาน
© 2025 nuttapong39
