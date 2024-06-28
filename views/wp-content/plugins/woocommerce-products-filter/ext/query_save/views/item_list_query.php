<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');
?>
<li class="woof_query_save_item woof_query_save_item_<?php esc_attr_e($key) ?>">
    <?php
    if (!isset($title)) {
	$title = esc_html__('new', 'woocommerce-products-filter');
    }
    ?>
    <a class="woof_link_to_query_save"  href="<?php echo esc_url($link) ?>" target="blank" ><?php esc_html_e($title) ?></a>
    <p class="woof_tooltip"><span class="woof_tooltip_data"><?php echo wp_kses_post(wp_unslash($get)) ?></span>  <span class="woof_icon_save_query"></span></p>   
    <a href="#" class="woof_remove_query_save" data-user="<?php esc_attr_e($user_id) ?>" data-key="<?php esc_attr_e($key) ?>"><img src="<?php echo esc_url($this->settings['delete_image']) ?>" height="12" width="12" alt="" /></a>
    <?php

    if(is_product() AND $show_notice){

       global $product;
       $id = $product->get_id(); 
       if($id){
        ?>
        <div class="woof_query_save_notice woof_query_save_notice_<?php esc_attr_e($key) ?>" data-id="<?php esc_attr_e($id) ?>"  ></div>
        <?php
       }
    }
    ?>
</li>
