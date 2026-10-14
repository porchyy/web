<?php
/**
 * Admin Panel Footer
 */
?>
</div><!-- /.container-xl -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="<?= url('assets/js/modal.js') ?>?v=<?= file_exists(ROOT_PATH . '/assets/js/modal.js') ? filemtime(ROOT_PATH . '/assets/js/modal.js') : '1' ?>"></script>
<script src="<?= url('assets/js/app.js') ?>?v=<?= file_exists(ROOT_PATH . '/assets/js/app.js') ? filemtime(ROOT_PATH . '/assets/js/app.js') : '1' ?>"></script>
<script src="<?= url('assets/js/admin-image-upload.js') ?>?v=<?= file_exists(ROOT_PATH . '/assets/js/admin-image-upload.js') ? filemtime(ROOT_PATH . '/assets/js/admin-image-upload.js') : '1' ?>"></script>
<script src="<?= url('assets/js/admin-quill.js') ?>?v=<?= file_exists(ROOT_PATH . '/assets/js/admin-quill.js') ? filemtime(ROOT_PATH . '/assets/js/admin-quill.js') : '1' ?>"></script>
</body>
</html>
