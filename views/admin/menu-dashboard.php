<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// define
define('AZOTRACKING_MENU', array('Dashboard', 'Support'));

global $pagenow;
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$current_page_slug = $page ? $page : '';
?>

<ul class="azo-tracking-menu-dashboard">
    <?php
    foreach (AZOTRACKING_MENU as $menu_item):
        $page_slug = AZOTRACKING_SLUG . '-' . strtolower($menu_item);
        ?>
        <li <?php echo ($page_slug == $current_page_slug) ? ' class="active"' : ''; ?>>
            <a href="admin.php?page=<?php echo esc_html($page_slug); ?>"><?php echo esc_html($menu_item); ?></a>
        </li>
    <?php endforeach; ?>
</ul>