<?php
declare(strict_types=1);

if (!empty($flash) && is_array($flash)):
    $flashType = $flash['type'] ?? 'info';
    $flashMessage = $flash['message'] ?? '';
?>
    <div class="admin-flash admin-flash--<?= htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8') ?>">
        <span><?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
<?php endif; ?>
