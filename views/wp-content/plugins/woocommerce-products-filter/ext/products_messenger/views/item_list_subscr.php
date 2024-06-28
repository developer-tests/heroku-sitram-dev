<li class="woof_subscr_item woof_subscr_item_<?php esc_attr_e($key) ?>">
    <?php
    if (!isset($counter)) {
	$counter = esc_html__('new', 'woocommerce-products-filter');
    }
    ?>
    <a class="woof_link_to_subscr"  href="<?php echo esc_url($link) ?>" target="blank" >#<?php esc_html_e($counter) ?>.&nbsp;<?php esc_html_e($subscr_lang) ?></a>
    <p class="woof_tooltip"><span class="woof_tooltip_data"><?php echo wp_kses_post(wp_unslash($get)) ?></span>  <span class="woof_icon_subscr"></span></p>   
    <a href="#" class="woof_remove_subscr" data-user="<?php esc_attr_e($user_id) ?>" data-key="<?php esc_attr_e($key) ?>"><img src="<?php echo esc_url($this->settings['delete_image']) ?>" height="12" width="12" alt="" /></a>
</li>
