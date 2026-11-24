<?php

defined('ABSPATH') || exit;

// phpcs:disable Generic.Files.LineLength.TooLong
?>
<div class="wrap">
    <div class="pp-columns-wrapper<?php echo $showSideBar ? ' pp-enable-sidebar' : ''; ?>">
        <div class="pp-column-left">
            <div id="backup-panel">
                <div style="display: flex; width: 100%; flex: 1 1 0%; flex-direction: column;">
                    <div style="margin-top: 0.5rem; width: 90%; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; flex-direction: row; justify-content: center; margin-left: 0.25rem; border-radius: 0.75rem; padding: 1.5rem; ">
                        <div style="display: flex; flex-direction: column; margin-top: 0.5rem;">
                            <div style="height: 1.5rem; width: 100%; border-radius: 0.375rem; background-color: #D1D5DB; margin-top: 0.5rem; "></div>
                            <div style="height: 1.5rem; width: 83.333333%; border-radius: 0.375rem; background-color: #D1D5DB; margin-top: 0.5rem; "></div>
                            <div style="height: 17.5rem; width: 75%; border-radius: 0.375rem; background-color: #D1D5DB; margin-top: 0.5rem; "></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($showSideBar) :
            include __DIR__ . '/ad-banner-right-sidebar.php';
        endif; ?>
    </div>
</div>
