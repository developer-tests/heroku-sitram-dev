<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

$class = $match ? "woof_notise_match" : "woof_notise_not_match";
?>
<div class="woof_notice_result <?php esc_attr_e($class) ?>">
    <span class="dashicons <?php esc_attr_e($match ? "dashicons-yes-alt" : "dashicons-warning") ?>"></span>
    <?php echo wp_kses_post(wp_unslash($notice)) ?>  
</div>
<?php
