# ส่วนที่ 2: มาตรฐานงานออกแบบและระบบภาพ (Design System & Visual Standards)

**Project:** Digital Business Technology (DBT)
**Design Direction:** Refined Editorial Institutional Design
**Design Philosophy:** Human-Designed Digital Technology
**Technology:** Bootstrap 5.3 + Custom CSS
**Primary Language:** Thai (`th-TH`)
**Secondary Language:** English (`en`)
**Reference:** DBT homepage screenshots supplied by the project owner

ข้อกำหนดในส่วนนี้เป็นมาตรฐานกลางสำหรับทุกหน้าในเว็บไซต์ DBT ทั้งหน้าสาธารณะและระบบบริหารจัดการหลังบ้าน การสร้างหน้าใหม่หรือแก้ไขหน้าเดิมต้องใช้ Design Tokens, Typography, Layout Rules และ Component Standards ชุดเดียวกัน เพื่อให้เว็บไซต์มีอัตลักษณ์ที่สม่ำเสมอทั้งโครงการ

ค่าต่าง ๆ ในเอกสารนี้ถือเป็นค่าเริ่มต้นมาตรฐาน ไม่ใช่ข้อบังคับให้ทุกองค์ประกอบมีขนาดเท่ากัน หากบริบทของเนื้อหาต้องการข้อยกเว้น ต้องรักษาหลักการและเหตุผลของการออกแบบไว้

---

## 2.1 Design Philosophy & Visual Identity

### 2.1.1 Core Direction

ใช้แนวทาง **Refined Editorial Institutional Design** ผสมผสานงานออกแบบบรรณาธิการร่วมสมัยกับความน่าเชื่อถือของสถาบันการศึกษา และความแม่นยำของงานออกแบบดิจิทัล

อัตลักษณ์หลักประกอบด้วย 5 องค์ประกอบ:

1. **Editorial Typography:** หัวเรื่องภาษาไทยขนาดใหญ่ น้ำหนักชัดเจน แบ่งบรรทัดอย่างตั้งใจ และมีลำดับชั้นทางสายตาที่ชัดเจน
2. **Generous Whitespace:** เว้นพื้นที่ระหว่างหัวเรื่อง เนื้อหา รูปภาพ และ Section อย่างเพียงพอ ไม่อัดองค์ประกอบให้เต็มหน้าจอ
3. **Architectural Grid:** ใช้แนวจัดวางที่แม่นยำ เส้นแบ่งบาง และ Grid Pattern สีฟ้าอ่อนเพื่อสร้างโครงสร้างทางภาพ
4. **Institutional Color:** ใช้สีขาว กรมท่า และฟ้าอ่อนเป็นหลัก พร้อมสีส้มอุ่นสำหรับจุดเน้นขนาดเล็ก
5. **Functional Minimalism:** เลือกใช้เฉพาะองค์ประกอบที่ช่วยให้ผู้ใช้เข้าใจเนื้อหา ค้นหาข้อมูล หรือลงมือทำงานได้ง่ายขึ้น

ผลลัพธ์ที่ต้องการคือเว็บไซต์ที่ดูสะอาด สุภาพ ทันสมัย มีบุคลิก และออกแบบมาเพื่อเนื้อหาภาษาไทยจริง ไม่ใช่การนำ Template เว็บไซต์ภาษาอังกฤษมาเปลี่ยนข้อความเป็นภาษาไทย

### 2.1.2 Visual Hierarchy

ทุกหน้าต้องจัดลำดับความสำคัญตามลำดับต่อไปนี้:

1. Page Heading หรือข้อมูลหลักที่ผู้ใช้เข้ามาดู
2. คำอธิบายหรือเนื้อหาที่ช่วยให้เข้าใจบริบท
3. Primary Action หรือข้อมูลสำคัญรอง
4. รายละเอียดประกอบและ Metadata
5. องค์ประกอบตกแต่ง

ห้ามให้องค์ประกอบตกแต่งมี Contrast, ขนาด หรือความหนาแน่นมากกว่าเนื้อหาหลักโดยไม่มีเหตุผล

### 2.1.3 Visual Anti-Patterns

ห้ามใช้เป็นภาษาภาพหลักของเว็บไซต์:

- Neon, Cyberpunk, Rainbow Gradient และ Glow Effect
- Glassmorphism หรือ Floating Card ที่มีเงาฟุ้งขนาดใหญ่
- ภาพหุ่นยนต์ สมองดิจิทัล หรือวงจร 3D ที่ไม่เกี่ยวข้องกับเนื้อหาจริง
- การครอบเนื้อหาทุกชิ้นด้วย Card
- การใส่ไอคอนในทุกหัวข้อโดยไม่มีหน้าที่
- การใช้ Border, Background Tint และ Shadow พร้อมกันทุกองค์ประกอบ
- การใส่หมายเลขนำหน้าทุก Section เพื่อสร้างความรู้สึกทางเทคนิค
- การใช้ตัวอักษรขนาดเล็กหรือสีอ่อนเกินไปเพื่อให้หน้าเว็บดู Minimal
- การบังคับให้ทุก Section มีรูปแบบหรือจำนวนคอลัมน์เหมือนกัน
- การนำดีไซน์ Desktop มาย่อขนาดโดยไม่ปรับโครงสร้างสำหรับ Mobile

---

## 2.2 Typography System — Thai-First Typography

Typography เป็นองค์ประกอบหลักของอัตลักษณ์ DBT เนื่องจากภาษาไทยมีสระบน สระล่าง วรรณยุกต์ และความหนาแน่นของรูปอักษรที่แตกต่างจากภาษาอังกฤษ การเลือกฟอนต์จึงต้องคำนึงถึงความชัดเจนของรูปอักษร ระยะบรรทัด และการแสดงผลจริงในภาษาไทย

### 2.2.1 Font Families

กำหนดฟอนต์หลักเพียง 3 กลุ่มสำหรับหน้าสาธารณะและ UI ทั่วไป

| Font            | หน้าที่            | การใช้งาน                                                   |
| --------------- | ------------------ | ----------------------------------------------------------- |
| `Kanit`         | Display & Headings | Hero Heading, Page Title, Section Heading และหัวเรื่องสำคัญ |
| `Prompt`        | Body & Interface   | ย่อหน้า Navigation, ปุ่ม ฟอร์ม รายการข่าว และคำอธิบาย       |
| `IBM Plex Mono` | Technical Metadata | รหัสวิชา วันที่ ตัวเลขเชิงเทคนิค และ Eyebrow ภาษาอังกฤษ     |

ใช้ `Sarabun` สำหรับเอกสารทางการและ Print Stylesheet เมื่อจำเป็น ไม่ใช้เป็นฟอนต์หลักของทุกหน้าพร้อมกับ Kanit และ Prompt

**บทบาทของแต่ละฟอนต์ต้องไม่สลับกันตามอำเภอใจ**

- Kanit สร้างบุคลิกและลำดับชั้นของหัวเรื่อง
- Prompt สร้างความต่อเนื่องในการอ่านภาษาไทย
- IBM Plex Mono สร้างความแตกต่างให้ข้อมูลเชิงเทคนิคโดยไม่แย่งความสนใจ

### 2.2.2 Google Fonts Import

โหลดเฉพาะ Weight ที่ใช้งานจริง เพื่อลดจำนวนไฟล์และเวลาโหลด

```html
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

<link
  href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Kanit:wght@400;500;600;700&family=Prompt:wght@400;500;600;700&family=Sarabun:wght@400;500;600&display=swap"
  rel="stylesheet"
/>
```

หากมีการกำหนดนโยบาย CSP หรือจำเป็นต้องทำงานในสภาพแวดล้อมที่ไม่มีอินเทอร์เน็ต ให้พิจารณา Self-host ฟอนต์และกำหนด `font-display: swap` ตามความเหมาะสม

### 2.2.3 Font Tokens

กำหนดใน `assets/css/site.css` และใช้ร่วมกันทุกหน้าสาธารณะ

```css
:root {
  --font-display: "Kanit", sans-serif;
  --font-body: "Prompt", sans-serif;
  --font-mono: "IBM Plex Mono", monospace;
  --font-document: "Sarabun", sans-serif;

  --weight-regular: 400;
  --weight-medium: 500;
  --weight-semibold: 600;
  --weight-bold: 700;

  --fs-display: clamp(2.5rem, 3.5vw + 1rem, 4.5rem);
  --fs-h1: clamp(2rem, 2vw + 1rem, 3rem);
  --fs-h2: clamp(1.5rem, 1.2vw + 1rem, 2rem);
  --fs-h3: clamp(1.125rem, 0.5vw + 1rem, 1.375rem);
  --fs-h4: 1.125rem;
  --fs-body: 1rem;
  --fs-body-sm: 0.9375rem;
  --fs-caption: 0.8125rem;
  --fs-eyebrow: 0.75rem;

  --lh-display: 1.2;
  --lh-heading: 1.35;
  --lh-body: 1.8;
  --lh-compact: 1.5;
}
```

### 2.2.4 Thai Typography Rules

1. **Body Text:** ใช้ Prompt ขนาด 16px เป็นค่ามาตรฐาน และ `line-height: 1.8` เพื่อให้สระและวรรณยุกต์อ่านง่ายบนหน้าจอ
2. **Hero Heading:** ใช้ Kanit น้ำหนัก 600–700 โดยมี Line Height ประมาณ 1.15–1.25 ตามขนาดและจำนวนบรรทัด
3. **Page Heading:** ใช้ Kanit น้ำหนัก 600 เป็นค่าเริ่มต้น ไม่จำเป็นต้องใช้ 700 กับทุกหัวเรื่อง
4. **Section Heading:** ใช้ Kanit น้ำหนัก 600 โดยต้องแยกความแตกต่างจาก Body Text อย่างชัดเจน
5. **Navigation:** ใช้ Prompt น้ำหนัก 500 ขนาดประมาณ 13–15px บน Desktop และประมาณ 14–16px บน Mobile ตามพื้นที่
6. **Metadata:** ใช้ Prompt สำหรับข้อมูลภาษาไทย ส่วน IBM Plex Mono ใช้กับรหัสและตัวเลขที่เหมาะสม
7. **English Eyebrow:** ใช้ IBM Plex Mono ขนาด 11–12px น้ำหนัก 400–500 อาจใช้ Uppercase และ Letter Spacing เล็กน้อย
8. **Thai Letter Spacing:** หลีกเลี่ยงการกำหนด `letter-spacing` เป็นค่าบวกมาก ๆ เพราะอาจทำให้สระ วรรณยุกต์ และจังหวะการอ่านดูผิดธรรมชาติ
9. **Thai Line Wrapping:** ปล่อยให้เบราว์เซอร์จัดการการตัดคำภาษาไทยเป็นค่าเริ่มต้น ห้ามใช้ `word-break: break-all` กับข้อความภาษาไทยทั่วไป
10. **Text Width:** จำกัดความกว้างของย่อหน้าเพื่อให้อ่านง่าย ไม่ยืดบรรทัดยาวเต็ม Desktop

ห้ามลดขนาดฟอนต์เพื่อแก้ปัญหา Layout ที่มีความกว้างไม่เหมาะสม ให้แก้ที่ Container, Grid หรือการแบ่งบรรทัดก่อน

### 2.2.5 Typography CSS

```css
body {
  font-family: var(--font-body);
  font-size: var(--fs-body);
  font-weight: var(--weight-regular);
  line-height: var(--lh-body);
  color: var(--c-text);
  font-synthesis: none;
  text-rendering: optimizeLegibility;
}

h1,
h2,
h3,
h4,
h5,
h6,
.heading-display,
.heading-editorial {
  font-family: var(--font-display);
  font-weight: var(--weight-semibold);
  line-height: var(--lh-heading);
  color: var(--c-navy);
  margin-top: 0;
}

h1,
.heading-display {
  font-size: var(--fs-h1);
}

h2 {
  font-size: var(--fs-h2);
}

h3 {
  font-size: var(--fs-h3);
}

p {
  margin-top: 0;
  margin-bottom: 1rem;
}

small,
.text-small {
  font-size: var(--fs-body-sm);
  line-height: var(--lh-body);
}

.eyebrow {
  font-family: var(--font-mono);
  font-size: var(--fs-eyebrow);
  font-weight: var(--weight-medium);
  line-height: var(--lh-compact);
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--c-accent-ink);
}

.text-metadata {
  font-family: var(--font-body);
  font-size: var(--fs-caption);
  line-height: var(--lh-compact);
  color: var(--c-muted);
}

.text-code,
.text-technical {
  font-family: var(--font-mono);
  font-size: 0.8125rem;
  font-variant-numeric: tabular-nums;
}
```

การใช้ `text-rendering: optimizeLegibility` ไม่ใช่สิ่งทดแทนการทดสอบฟอนต์จริง ต้องตรวจสอบรูปอักษรภาษาไทยใน Chrome และอุปกรณ์เป้าหมายด้วย

---

## 2.3 Color Palette & Design Tokens

### 2.3.1 Core Color Tokens

กำหนดชุดสีมาตรฐานดังนี้

| Token            | HEX       | หน้าที่                     |
| ---------------- | --------- | --------------------------- |
| `--c-bg`         | `#FFFFFF` | พื้นหลังหลัก                |
| `--c-bg-alt`     | `#F4F8FA` | พื้นหลัง Section รอง        |
| `--c-blue-100`   | `#E6F1F5` | พื้นหลังแถบข้อมูล           |
| `--c-blue-200`   | `#D5EAF4` | Grid Pattern                |
| `--c-blue-300`   | `#B9DCEB` | เส้นกรอบและรายละเอียดประกอบ |
| `--c-navy`       | `#17324D` | หัวเรื่องและข้อความสำคัญ    |
| `--c-navy-80`    | `#2E4A66` | ข้อความรอง                  |
| `--c-text`       | `#334A60` | Body Text                   |
| `--c-muted`      | `#66798A` | Metadata                    |
| `--c-line`       | `#DCE6EC` | เส้นคั่น                    |
| `--c-accent`     | `#E99A4A` | Accent Rule และจุดเน้น      |
| `--c-accent-ink` | `#A85417` | ข้อความสีส้มเข้ม            |

```css
:root {
  --c-bg: #ffffff;
  --c-bg-alt: #f4f8fa;

  --c-blue-100: #e6f1f5;
  --c-blue-200: #d5eaf4;
  --c-blue-300: #b9dceb;

  --c-navy: #17324d;
  --c-navy-80: #2e4a66;
  --c-text: #334a60;
  --c-muted: #66798a;

  --c-line: #dce6ec;
  --c-accent: #e99a4a;
  --c-accent-ink: #a85417;

  --c-success: #1f7a4d;
  --c-success-bg: #eef7f2;

  --c-danger: #b42318;
  --c-danger-bg: #fdf1f0;

  --c-warning: #8a4b08;
  --c-warning-bg: #fff6e8;

  --c-info: #075985;
  --c-info-bg: #f0f7ff;
}
```

### 2.3.2 Color Application Rules

- ใช้พื้นหลังขาวเป็นพื้นที่หลัก
- ใช้ Navy กับหัวเรื่องและเนื้อหาที่มีความสำคัญ
- ใช้สีฟ้าอ่อนเป็นพื้นผิวรองหรือรายละเอียดประกอบ
- ใช้สีส้มกับ Accent Rule, Eyebrow และจุดเน้นที่มีความหมาย
- ใช้สีสถานะเฉพาะเมื่อมีสถานะจริง ไม่ใช้เป็นสีตกแต่ง
- ไม่บังคับอัตราส่วนสีเป็นเปอร์เซ็นต์ตายตัว แต่ต้องรักษา White Space และความเด่นของเนื้อหาหลัก

### 2.3.3 Accessibility

- Text Contrast ต้องผ่าน WCAG AA
- ข้อความปกติ: Contrast Ratio อย่างน้อย 4.5:1
- ข้อความขนาดใหญ่: อย่างน้อย 3:1
- สีของขอบหรือองค์ประกอบ UI ที่จำเป็นต่อการรับรู้ต้องมี Contrast อย่างน้อย 3:1 ตามบริบท
- ห้ามใช้ `--c-accent` เป็นข้อความขนาดเล็กบนพื้นขาวโดยไม่ตรวจสอบ Contrast
- ห้ามใช้สีส้มอ่อนกับข้อความสีขาวเป็นคู่สีเริ่มต้น
- ต้องตรวจสอบคู่สีจริงด้วยเครื่องมือตรวจ Contrast ก่อนส่งมอบ

---

## 2.4 Layout, Grid & Spacing

### 2.4.1 Spacing Tokens

```css
:root {
  --s-1: 4px;
  --s-2: 8px;
  --s-3: 12px;
  --s-4: 16px;
  --s-5: 24px;
  --s-6: 32px;
  --s-7: 48px;
  --s-8: 64px;
  --s-9: 96px;
  --s-10: 128px;

  --container-max: 1200px;
  --container-wide: 1296px;

  --header-height: 64px;
  --content-gutter: 24px;

  --radius-sm: 2px;
  --radius-md: 4px;
  --radius-lg: 8px;
}
```

### 2.4.2 Container Standards

- Mobile: ใช้ Padding ด้านข้าง 20–24px
- Tablet: ใช้ Padding ด้านข้างประมาณ 32px
- Desktop: ใช้ Container สูงสุด 1200px
- Wide Desktop: ขยายได้ถึง 1296px เมื่อมีเหตุผลทาง Layout
- เนื้อหาที่เป็นย่อหน้าต้องมีความกว้างจำกัดเพื่อรักษาความอ่านง่าย
- Header, Hero, Main Content และ Footer ต้องใช้แนวจัดวางที่สัมพันธ์กัน

```css
.container-editorial {
  width: min(calc(100% - 48px), var(--container-max));
  margin-inline: auto;
}

.container-editorial-wide {
  width: min(calc(100% - 48px), var(--container-wide));
  margin-inline: auto;
}

.prose {
  max-width: 65ch;
}
```

### 2.4.3 Grid Rules

- ใช้ Bootstrap Grid เป็นฐานสำหรับโครงสร้างทั่วไป
- ใช้ CSS Grid และ Flexbox สำหรับองค์ประกอบที่ต้องการการควบคุมเฉพาะ
- ใช้ Custom CSS เพื่อควบคุมสัดส่วนคอลัมน์ ระยะห่าง และ Responsive Behavior
- ห้ามสร้าง Grid Framework ใหม่ซ้ำกับ Bootstrap โดยไม่จำเป็น
- ห้ามกำหนดความสูงคงที่ให้ Section ที่มีข้อความภาษาไทยหลายบรรทัด

---

## 2.5 Hero Section — Signature Component

Hero เป็นองค์ประกอบที่กำหนดอัตลักษณ์ของหน้าแรก DBT จึงต้องรักษาโครงสร้างและสัดส่วนให้สม่ำเสมอ

### 2.5.1 Desktop Composition

ใช้ Two-Column Editorial Layout:

- Text Column: ประมาณ 45–48%
- Image Column: ประมาณ 52–55%
- ระยะห่างระหว่างคอลัมน์: ประมาณ 24–40px
- Hero Heading: ใช้ Kanit น้ำหนัก 600–700
- Description: ใช้ Prompt ขนาด 16px และ Line Height 1.8
- Accent Rule: กว้าง 28–36px สูง 2–3px
- Image: มีพื้นที่แสดงผลชัดเจน ไม่ถูกบดบังด้วยข้อความ
- Background Grid: อยู่ด้านหลังภาพหรือฝั่งขวาของ Hero

Hero ต้องมีความสมดุลทางสายตา ไม่จำเป็นต้องจัดกึ่งกลางแนวตั้งอย่างเคร่งครัด หากการจัดแนวตาม Baseline ของข้อความและภาพดูดีกว่า

### 2.5.2 Hero CSS

```css
.hero {
  position: relative;
  isolation: isolate;
  overflow: clip;
  padding-block: clamp(48px, 6vw, 88px);
  background: var(--c-bg);
}

.hero__inner {
  display: grid;
  grid-template-columns:
    minmax(0, 0.9fr)
    minmax(0, 1.1fr);
  align-items: center;
  gap: clamp(24px, 4vw, 48px);
}

.hero__content {
  min-width: 0;
  max-width: 520px;
}

.hero__eyebrow {
  margin-bottom: 20px;
}

.hero__title {
  margin: 0 0 24px;
  font-family: var(--font-display);
  font-size: var(--fs-display);
  font-weight: 700;
  line-height: var(--lh-display);
  color: var(--c-navy);
  overflow-wrap: break-word;
}

.hero__rule {
  width: 32px;
  height: 3px;
  margin-bottom: 20px;
  background: var(--c-accent);
}

.hero__description {
  max-width: 42ch;
  margin-bottom: 28px;
  font-family: var(--font-body);
  font-size: 1rem;
  line-height: 1.8;
  color: var(--c-text);
}

.hero__media {
  position: relative;
  min-width: 0;
  padding: 12px 0 24px 12px;
}

.hero__image {
  display: block;
  width: 100%;
  aspect-ratio: 4 / 5;
  object-fit: cover;
  object-position: center;
}

.hero__caption {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid var(--c-line);
  font-size: 0.8125rem;
  line-height: 1.6;
  color: var(--c-muted);
}
```

### 2.5.3 Hero Grid Pattern

```css
.hero__media::before {
  content: "";
  position: absolute;
  inset: 0 -120px 0 40px;
  z-index: -1;
  pointer-events: none;

  background-image:
    linear-gradient(to right, var(--c-blue-200) 1px, transparent 1px),
    linear-gradient(to bottom, var(--c-blue-200) 1px, transparent 1px);

  background-size: 40px 40px;
}
```

Grid Pattern ต้องมี Contrast ต่ำและไม่รบกวนการอ่าน Caption หรือรายละเอียดภาพ สามารถปรับความถี่และขอบเขตตามขนาดหน้าจอได้

### 2.5.4 Hero Responsive Rules

- Desktop: ใช้ Two-Column Layout
- Tablet: รักษาสองคอลัมน์เมื่อความกว้างเพียงพอ
- Mobile: เปลี่ยนเป็น Single Column
- Mobile: ลดขนาด Heading ด้วย `clamp()` และปรับลำดับภาพตามความสำคัญ
- ห้ามให้ข้อความและภาพซ้อนทับกันโดยไม่ได้ตั้งใจ
- หาก Hero มีข้อมูลสรุปเพิ่มเติม ให้จัดเป็นแถวที่แบ่งด้วยเส้นคั่นแทนการสร้าง Card ซ้อนหลายชั้น

---

## 2.6 Navigation & Header

### 2.6.1 Header Standards

- พื้นหลังขาว
- ความสูง Desktop ประมาณ 64–72px
- มี Border Bottom ขนาด 1px
- Logo และชื่อสาขาอยู่ด้านซ้าย
- เมนูหลักจัดแนวนอนและมีระยะห่างเพียงพอ
- ใช้ไอคอนขนาดเล็กเฉพาะเมื่อช่วยให้เข้าใจหน้าที่
- Active State ใช้เส้นใต้หรือสีข้อความที่แตกต่างอย่างชัดเจน
- หลีกเลี่ยง Shadow หนักและ Header ที่สูงเกินความจำเป็น

### 2.6.2 Navigation Typography

- Font: Prompt
- Weight: 500
- Desktop Size: 13–15px
- Line Height: 1.4–1.6
- ห้ามใช้ IBM Plex Mono กับชื่อเมนูภาษาไทย
- ห้ามบังคับแสดงหมายเลข `01/`, `02/` ในเมนูทุกหน้าหากทำให้เมนูแน่นเกินไป

### 2.6.3 Active and Focus States

Active State ต้องสังเกตได้โดยไม่พึ่งสีเพียงอย่างเดียว เช่น ใช้เส้นใต้ร่วมกับสีข้อความ

Focus State ต้องมองเห็นได้ชัดเจนสำหรับผู้ใช้ Keyboard

---

## 2.7 Editorial Lists & Directory Sections

ส่วนสารบัญและรายการทางลัดเป็นองค์ประกอบสำคัญของเว็บไซต์ DBT

### 2.7.1 Directory Layout

รายการประกอบด้วย 3 ส่วน:

1. Title: ชื่อหมวดหมู่หรือจุดหมาย
2. Description: คำอธิบายสั้น ๆ
3. Indicator: Arrow หรือสัญลักษณ์นำทาง

ใช้ Border Bottom แบ่งแต่ละรายการ แทนการสร้าง Card แยกทุกแถว

### 2.7.2 Directory CSS

```css
.directory-list {
  border-top: 1px solid var(--c-line);
}

.directory-item {
  display: grid;
  grid-template-columns:
    minmax(180px, 0.7fr)
    minmax(0, 1.8fr)
    24px;
  align-items: center;
  gap: 24px;

  padding-block: 24px;
  border-bottom: 1px solid var(--c-line);

  color: inherit;
  text-decoration: none;
  transition:
    background-color 160ms ease,
    color 160ms ease;
}

.directory-item__title {
  margin: 0;
  font-family: var(--font-display);
  font-size: 1.125rem;
  font-weight: 600;
  line-height: 1.5;
  color: var(--c-navy);
}

.directory-item__description {
  margin: 0;
  font-size: 0.875rem;
  line-height: 1.8;
  color: var(--c-muted);
}

.directory-item__icon {
  justify-self: end;
  color: var(--c-navy);
  transition: transform 160ms ease;
}

.directory-item:hover .directory-item__icon {
  transform: translateX(3px);
}

.directory-item:focus-visible {
  outline: 2px solid var(--c-accent-ink);
  outline-offset: 3px;
}
```

รายการที่เป็นลิงก์ต้องมีจุดคลิกที่เหมาะสมและสถานะ Focus ชัดเจน การ Hover ใช้เป็นเพียง Feedback เสริมเท่านั้น

---

## 2.8 Statistics & Information Bands

ใช้แถบข้อมูลพื้นหลังฟ้าอ่อนสำหรับแสดงจำนวนบุคลากร ข่าวสาร กิจกรรม หรือข้อมูลสรุป

- ใช้ตัวเลขขนาดใหญ่
- ใช้ข้อความอธิบายที่อยู่ใกล้ตัวเลข
- ใช้เส้นคั่นแทน Card Shadow
- ไม่บังคับให้มีไอคอนทุกช่อง
- ข้อมูลแต่ละช่องต้องมีความสัมพันธ์และลำดับชั้นที่ชัดเจน

```css
.stats-band {
  background: var(--c-blue-100);
  border-block: 1px solid var(--c-blue-300);
}

.stats-band__grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.stats-item {
  padding: 28px 32px;
}

.stats-item + .stats-item {
  border-left: 1px solid var(--c-blue-300);
}

.stats-item__value {
  font-family: var(--font-display);
  font-size: clamp(1.75rem, 2vw, 2.25rem);
  font-weight: 600;
  line-height: 1.2;
  color: var(--c-navy);
}

.stats-item__label {
  margin-top: 4px;
  font-size: 0.875rem;
  line-height: 1.7;
  color: var(--c-text);
}
```

---

## 2.9 Borders, Geometry & Surface Rules

### 2.9.1 Border Radius

```css
:root {
  --radius-sm: 2px;
  --radius-md: 4px;
  --radius-lg: 8px;
}
```

ใช้มุมคมกับกรอบภาพและองค์ประกอบเชิง Editorial ใช้รัศมีเล็กกับปุ่ม ฟอร์ม และ Panel ตามความเหมาะสม

ไม่บังคับให้ทุกองค์ประกอบมีรัศมี 0px และไม่ใช้ Bubble Shape เป็นรูปแบบหลัก

### 2.9.2 Surface Hierarchy

ใช้ความแตกต่างของพื้นผิวเพียงเท่าที่จำเป็น:

1. White: พื้นผิวหลัก
2. Soft Blue/Gray: Section รองหรือ Information Band
3. Border: แบ่งองค์ประกอบที่สัมพันธ์กัน
4. Shadow: ใช้เฉพาะองค์ประกอบที่ลอยอยู่เหนือเนื้อหา เช่น Dropdown หรือ Dialog

ไม่ใช้ Shadow เพื่อสร้างความแตกต่างระหว่าง Section ที่สามารถแบ่งด้วยพื้นที่ว่างได้

### 2.9.3 Corner Marks

Corner Marks ใช้กับภาพ Hero หรือภาพที่ต้องการเน้นกรอบเป็นพิเศษเท่านั้น

- ใช้เส้นบางสี Navy
- ยื่นออกจากกรอบภาพเพียงเล็กน้อย
- ไม่ใช้กับรูปภาพทุกใบ
- ห้ามใช้ร่วมกับกรอบหลายชั้นและเงาหนัก

---

## 2.10 Responsive Design Standards

### 2.10.1 Breakpoints

ใช้ Bootstrap 5.3 เป็นมาตรฐานกลาง

| Viewport      | Range        | Layout                           |
| ------------- | ------------ | -------------------------------- |
| Mobile        | `< 576px`    | Single Column                    |
| Small Tablet  | `576–767px`  | Fluid Layout                     |
| Tablet        | `768–991px`  | Two Columns เมื่อเหมาะสม         |
| Desktop       | `992–1199px` | Editorial Grid                   |
| Large Desktop | `1200px+`    | Full Container                   |
| Wide Desktop  | `1440px+`    | Expanded Container ตามความจำเป็น |

หากต้องใช้เกณฑ์ Mobile Navigation ที่ 768px ให้กำหนดเป็น Project-Specific Breakpoint แยกจาก Bootstrap และใช้ให้เหมือนกันทุกหน้า

### 2.10.2 Mobile Requirements

- Mobile First เป็นแนวทางการจัดลำดับเนื้อหา ไม่ใช่การบังคับให้ทุกองค์ประกอบเริ่มจากขนาดเล็กที่สุด
- ใช้ Padding ด้านข้างประมาณ 20–24px
- รักษา Body Text ที่ 16px เป็นค่าเริ่มต้น
- Touch Target อย่างน้อย 44×44px
- ห้ามเกิด Horizontal Overflow โดยไม่ได้ตั้งใจ
- Hero ต้องจัดลำดับข้อความ ภาพ และ Metadata ใหม่อย่างเหมาะสม
- Directory List ต้องเปลี่ยนเป็นรูปแบบแนวตั้งเมื่อคอลัมน์ไม่เพียงพอ
- ตารางเรียนอาจใช้ Day Tabs และ Timeline บน Mobile
- ใช้ Bottom Navigation เฉพาะเมื่อสอดคล้องกับรูปแบบการใช้งานจริง

### 2.10.3 Tablet Requirements

- รักษาความสัมพันธ์ระหว่าง Text และ Image
- ลดระยะห่างและขนาด Heading อย่างเหมาะสม
- Navigation ต้องไม่ล้นหรือบีบชื่อเมนู
- ฟอร์มแบ่งสองคอลัมน์เฉพาะเมื่อแต่ละช่องยังอ่านง่าย
- ตารางเรียนเลื่อนแนวนอนได้เมื่อพื้นที่ไม่เพียงพอ

### 2.10.4 Desktop Requirements

- จำกัดความกว้างของข้อความ
- ใช้พื้นที่ว่างสร้างลำดับชั้น
- วาง Grid Pattern เฉพาะพื้นที่ที่เหมาะสม
- รักษาความสม่ำเสมอของ Container และแนวจัดวาง
- ห้ามขยายข้อความหรือ Card เพียงเพื่อเติมพื้นที่ว่าง

---

## 2.11 Buttons, Forms & Interactive Components

### 2.11.1 Buttons

แบ่งปุ่มตามระดับความสำคัญ:

- Primary: การกระทำหลัก
- Secondary: การกระทำรอง
- Tertiary: การนำทางหรือการกระทำที่ไม่ต้องเน้น
- Destructive: การลบหรือการกระทำที่มีผลกระทบสูง

กำหนด Primary Action เท่าที่จำเป็นในแต่ละบริบท ห้ามใช้สี Accent กับทุกปุ่ม

### 2.11.2 Forms

- ใช้ Prompt ขนาด 16px
- Input Height อย่างน้อย 48px สำหรับฟอร์มหลัก
- Label ต้องอยู่ในตำแหน่งที่สัมพันธ์กับช่องกรอก
- ใช้ Border สี `--c-line`
- Focus ใช้ Outline ที่เห็นได้ชัด
- Error Message ต้องอธิบายสิ่งที่ต้องแก้ไข
- Placeholder ห้ามใช้แทน Label
- ต้องรองรับ Keyboard และ Touch

### 2.11.3 Focus State

```css
:focus-visible {
  outline: 2px solid var(--c-accent-ink);
  outline-offset: 3px;
}

.form-control:focus,
.form-select:focus {
  border-color: var(--c-navy);
  box-shadow: 0 0 0 3px rgba(23, 50, 77, 0.12);
}
```

---

## 2.12 UI States & Live Data

คงระบบ UI States เดิมของโครงการ โดยใช้ภาษาภาพตาม Design Tokens ชุดเดียวกัน

สถานะข้อมูลหลัก:

1. Loaded
2. Empty
3. Loading
4. Partial / Fallback
5. Error

### 2.12.1 Status Design

- Success: เขียวเข้มบนพื้นเขียวอ่อน
- Error: แดงเข้มบนพื้นแดงอ่อน
- Warning: น้ำตาลอำพันบนพื้นอุ่นอ่อน
- Information: น้ำเงินเข้มบนพื้นฟ้าอ่อน
- Neutral: เทากรมบนพื้นเทาอ่อน

ห้ามใช้สีเพียงอย่างเดียวในการบอกสถานะ

### 2.12.2 Schedule Status

- Current Class: พื้นเขียวอ่อนและ Border เขียวเข้ม
- Next Class: พื้นอำพันอ่อนและ Border อำพันเข้ม
- Today: พื้นฟ้าอ่อนหรือเส้นเน้น
- Conflict: พื้นแดงอ่อนและข้อความแจ้งเตือนชัดเจน

ไม่ใช้ Glow หรือ Gradient หนักเพื่อเน้นคาบเรียน

### 2.12.3 Drag-and-Drop

ใช้ Border, Background Tint และ Cursor เพื่อแสดงสถานะ Dragging, Drop Target และ Conflict

ต้องมี Preview ระหว่างลาก และต้องแยกสถานะ Valid/Invalid อย่างชัดเจน โดยไม่พึ่งการเปลี่ยนสีเพียงอย่างเดียว

---

## 2.13 Motion, Accessibility & Print

### 2.13.1 Motion

- Hover: 120–200ms
- Dropdown/Panel: 160–240ms
- ใช้การเปลี่ยนสีและการเลื่อนเล็กน้อย
- หลีกเลี่ยงการขยายขนาดที่ทำให้ Layout กระโดด
- หลีกเลี่ยง Animation ที่ทำงานตลอดเวลาโดยไม่มีหน้าที่

### 2.13.2 Reduced Motion

```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    scroll-behavior: auto !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

### 2.13.3 Print Stylesheet

- ใช้พื้นหลังขาว
- ใช้ Sarabun เมื่อเหมาะกับเอกสารภาษาไทย
- ซ่อน Navigation, ปุ่ม และองค์ประกอบที่ไม่จำเป็นต่อการพิมพ์
- ตารางเรียนต้องแสดงผลครบถ้วน
- หลีกเลี่ยงการตัดแถวตารางหรือเนื้อหาสำคัญข้ามหน้า

---

## 2.14 Bootstrap & Custom CSS Implementation Rules

### 2.14.1 Source of Truth

ใช้ Bootstrap 5.3 สำหรับ:

- Reboot และพื้นฐานของ HTML
- Grid System
- Flexbox Utilities
- Responsive Utilities
- Forms และ Components ที่เหมาะสม

ใช้ Custom CSS ใน `assets/css/site.css` สำหรับ:

- Design Tokens
- Typography
- Hero
- Header และ Navigation
- Editorial Lists
- Statistics Band
- Grid Pattern
- Component States
- Responsive Overrides

ใช้ `assets/css/admin.css` สำหรับ:

- Admin Layout
- Admin Navigation
- Dashboard
- Data Tables
- Scheduler
- Tree Slots
- Admin-Specific Form States

### 2.14.2 CSS Override Policy

- Design Tokens ต้องเป็นแหล่งอ้างอิงหลักของสี ฟอนต์ ระยะห่าง และรูปทรง
- ห้ามกำหนดค่าสีและ Font Family ใหม่แบบกระจัดกระจายโดยไม่มีเหตุผล
- ห้ามเขียน CSS ซ้ำเพื่อแก้ปัญหาเดิมในแต่ละหน้า หากสามารถแก้ที่ Shared Component ได้
- หลีกเลี่ยง `!important` ยกเว้นกรณีจำเป็นต่อ Utility หรือ Reduced Motion
- ห้าม Override Bootstrap Component แบบกว้างจนกระทบทุกหน้าโดยไม่ตั้งใจ
- ห้ามสร้าง CSS Framework ซ้ำกับ Bootstrap
- ใช้ Class ที่สื่อหน้าที่ เช่น `.hero__title` และ `.directory-item` แทน Selector ที่ผูกกับโครงสร้าง HTML มากเกินไป

### 2.14.3 HTML Structure

- ใช้ Semantic HTML
- ใช้ Heading ตามลำดับชั้นจริง
- ใช้ `nav`, `main`, `section`, `article` และ `footer` ตามหน้าที่
- ห้ามใช้ Heading เพียงเพื่อให้ได้ขนาดตัวอักษรที่ต้องการ
- ลิงก์ใช้สำหรับ Navigation
- ปุ่มใช้สำหรับ Action
- ทุก Interactive Component ต้องมี Focus State

---

## 2.15 Design Consistency & Acceptance Checklist

ก่อนส่งมอบหน้าใหม่หรือแก้ไขหน้าเดิม ต้องตรวจสอบ:

### Typography

- [ ] Kanit ใช้กับ Heading และ Prompt ใช้กับ Body/UI ตามมาตรฐาน
- [ ] ภาษาไทยไม่มีสระหรือวรรณยุกต์ซ้อนผิดปกติ
- [ ] Heading แบ่งบรรทัดอย่างเหมาะสม
- [ ] Body Text อ่านง่ายทั้ง Mobile และ Desktop
- [ ] ไม่มีการใช้ Font Weight หรือ Letter Spacing มากเกินไป

### Layout

- [ ] Container และแนวจัดวางสอดคล้องกับหน้าอื่น
- [ ] ระยะห่างมีลำดับชั้นชัดเจน
- [ ] Hero รักษาความสัมพันธ์ระหว่างข้อความและภาพ
- [ ] Directory List ใช้เส้นแบ่งแทน Card ที่ไม่จำเป็น
- [ ] Grid Pattern ไม่รบกวนการอ่าน

### Color & Components

- [ ] ใช้ Color Tokens กลาง
- [ ] Accent Color ไม่ถูกใช้มากเกินไป
- [ ] Borders และ Shadows มีเหตุผล
- [ ] ปุ่มและฟอร์มมีสถานะชัดเจน
- [ ] Contrast ผ่าน WCAG AA ตามบริบท

### Responsive & Accessibility

- [ ] ไม่มี Horizontal Overflow โดยไม่ได้ตั้งใจ
- [ ] Touch Target เหมาะสม
- [ ] Navigation ไม่ล้น
- [ ] Keyboard Focus มองเห็นได้
- [ ] รองรับ Reduced Motion
- [ ] ทดสอบข้อความภาษาไทยบนขนาดหน้าจอจริง

### Engineering

- [ ] ใช้ Bootstrap 5.3 และ Custom CSS ตามหน้าที่
- [ ] ใช้ Shared Components และ Design Tokens
- [ ] ไม่มี CSS ซ้ำซ้อนที่สามารถรวมศูนย์ได้
- [ ] ไม่มีการแก้ปัญหา Layout ด้วยการลดขนาดฟอนต์โดยไม่จำเป็น
- [ ] หน้าใหม่สอดคล้องกับ Visual Reference และไม่สร้างภาษาการออกแบบใหม่โดยไม่มีเหตุผล

**Final Rule:** ทุกหน้าต้องดูเป็นส่วนหนึ่งของเว็บไซต์ DBT เดียวกัน แม้จะมีวัตถุประสงค์และรูปแบบข้อมูลแตกต่างกัน โดยให้ความสำคัญกับความอ่านง่ายของภาษาไทย ลำดับชั้นทางสายตา พื้นที่ว่าง และโครงสร้างที่ชัดเจน มากกว่าการเพิ่มองค์ประกอบตกแต่ง
