<?php
require_once __DIR__ . '/includes/bootstrap.php';

// POST + CSRF only, so a third-party page can't log visitors out via a link/image.
if (!is_post()) {
    redirect('index.php');
}
require_csrf();

destroy_session();
redirect('logged_out.php');
