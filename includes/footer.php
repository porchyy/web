<?php
/**
 * Global Footer Component
 * Includes mobile bottom navigation and scripts
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$activeNav = $activeNav ?? '';
?>
</main>

<footer class="site-footer">
  <div class="container-xl">
    <div class="row gy-4 gx-lg-5">
      <div class="col-12 col-md-5">
        <p class="eyebrow eyebrow-light">00 / DIRECTORY INFORMATION</p>
        <h2 class="footer-title"><?= e(SITE_NAME) ?></h2>
        <p class="footer-sub"><?= e(SITE_NAME_EN) ?> · <?= e(INSTITUTE_NAME) ?></p>
        <p class="mt-3 text-white-50" style="font-size: .875rem; max-width: 38ch; line-height: 1.6;">
          มุ่งเน้นการพัฒนากำลังคนด้านเทคโนโลยีสารสนเทศ ธุรกิจดิจิทัล และการประยุกต์ใช้นวัตกรรมเพื่อสังคมยุคใหม่
        </p>
      </div>

      <div class="col-12 col-md-7">
        <p class="eyebrow eyebrow-light">CONTACT & LOCATION</p>
        <ul class="footer-contact">
          <li>
            <?= icon('phone', 18) ?>
            <div>
              <span class="d-block text-white-50" style="font-size: 11px; font-family: var(--font-mono);">PHONE</span>
              <a href="tel:<?= e(setting('contact_phone', CONTACT_PHONE)) ?>"><?= e(setting('contact_phone', CONTACT_PHONE)) ?></a>
            </div>
          </li>
          <li>
            <?= icon('mail', 18) ?>
            <div>
              <span class="d-block text-white-50" style="font-size: 11px; font-family: var(--font-mono);">EMAIL</span>
              <a href="mailto:<?= e(setting('contact_email', CONTACT_EMAIL)) ?>"><?= e(setting('contact_email', CONTACT_EMAIL)) ?></a>
            </div>
          </li>
          <li>
            <?= icon('facebook', 18) ?>
            <div>
              <span class="d-block text-white-50" style="font-size: 11px; font-family: var(--font-mono);">COMMUNITY</span>
              <a href="<?= e(setting('contact_facebook_url', CONTACT_FACEBOOK_URL)) ?>" target="_blank" rel="noopener noreferrer">
                <?= e(setting('contact_facebook_name', CONTACT_FACEBOOK_NAME)) ?>
              </a>
            </div>
          </li>
          <li>
            <?= icon('map-pin', 18) ?>
            <div>
              <span class="d-block text-white-50" style="font-size: 11px; font-family: var(--font-mono);">LOCATION</span>
              <span><?= e(setting('contact_address', CONTACT_ADDRESS)) ?></span>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <div class="footer-base">
      <span>© <?= date('Y') ?> <?= e(SITE_SHORT) ?>. ALL RIGHTS RESERVED.</span>
      <span>CURRICULUM SPEC // HUMAN-DESIGNED DIGITAL TECH</span>
    </div>
  </div>
</footer>

<!-- Mobile Bottom Navigation (Sticky at screen bottom for 360-430px viewports) -->
<nav class="nav-bottom" aria-label="เมนูหลักสำหรับมือถือ">
  <a href="<?= url('index.php') ?>" <?= $activeNav === 'home' ? 'aria-current="page"' : '' ?>>
    <?= icon('home', 20) ?>
    <span>หน้าหลัก</span>
  </a>
  <a href="<?= url('about.php') ?>" <?= $activeNav === 'about' ? 'aria-current="page"' : '' ?>>
    <?= icon('info', 20) ?>
    <span>แผนก</span>
  </a>
  <a href="<?= url('news.php') ?>" <?= $activeNav === 'news' ? 'aria-current="page"' : '' ?>>
    <?= icon('news', 20) ?>
    <span>ข่าวสาร</span>
  </a>
  <a href="<?= url('schedule.php') ?>" <?= $activeNav === 'schedule' ? 'aria-current="page"' : '' ?>>
    <?= icon('calendar', 20) ?>
    <span>ตาราง</span>
  </a>
  <a href="<?= url('teachers.php') ?>" <?= $activeNav === 'teachers' ? 'aria-current="page"' : '' ?>>
    <?= icon('users', 20) ?>
    <span>บุคลากร</span>
  </a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
