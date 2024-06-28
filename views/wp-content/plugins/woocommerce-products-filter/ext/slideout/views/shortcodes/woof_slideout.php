<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
?>

<div class="woof-slide-out-div <?php esc_attr_e($class) ?>" style="position: absolute; right: 10000px;" data-key="<?php esc_attr_e($key) ?>"  data-image="<?php esc_attr_e($image) ?>"
     data-image_h="<?php esc_attr_e($image_h) ?>" data-image_w="<?php esc_attr_e($image_w) ?>"
     data-mobile="<?php esc_attr_e($mobile_behavior) ?>"  data-action="<?php esc_attr_e($action) ?>" data-location="<?php esc_attr_e($location) ?>"
     data-speed="<?php esc_attr_e($speed) ?>" data-toppos="<?php esc_attr_e($offset) ?>"  data-onloadslideout="<?php esc_attr_e($onloadslideout) ?>"
     data-height="<?php esc_attr_e($height) ?>" data-width="<?php esc_attr_e($width) ?>">
    <span class="woof-handle <?php esc_attr_e($key) ?>" style="" ><?php
        if ($image == "null") {
            esc_html_e($text);
        }
        ?></span>
    <div class="woof-slide-content woof-slide-<?php esc_attr_e($key) ?>">
        <?php echo do_shortcode(esc_attr($content)) ?>
    </div>
</div>

