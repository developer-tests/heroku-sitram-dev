<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

include('header2.php'); ?>
<?php
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}?>

<?php $backgroundImg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' ); ?>
<?php $category = single_cat_title( '', false );
			if (strpos($category, 'Gloves') !== false )
				$backgroundImg = array("https://sitraminc.com/wp-content/uploads/2022/01/Untitled-design-51.png");?>
<?php $category = single_cat_title( '', false );
			if (strpos($category, 'Consumer Electronics') !== false )
			{
				$backgroundImg = array("https://sitraminc.com/wp-content/uploads/2022/02/Untitled-design-53.png");
				$titleFontColor = '#FFFFFF';}
elseif ((strpos($category, 'Industrial') !== false )){
	
				$backgroundImg = array("https://sitraminc.com/wp-content/uploads/2022/02/Untitled-design-60.png");
				$titleFontColor = '#FFFFFF';
}
elseif ((strpos($category, 'Fiber') !== false )){
	
				$backgroundImg = array("https://sitraminc.com/wp-content/uploads/2022/02/Untitled-design-63.png");
				
};
?>
<div class="product_landing_banner" style="background: url('<?php echo $backgroundImg[0]; ?>') no-repeat; ">
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div class="product_list">
          <h1 style="color: <?php echo $titleFontColor; ?>; margin-left:-20px"><?php the_title(); ?></h1>
        </div>
      </div>
      
    </div>
  </div>
</div>
<div class="product_landing_section">
  <div class="container">
    <div class="row">
      <?php if (strpos($category, 'Consumer Electronics') !== false  || (strpos($category, 'Gloves') !== false ) || (strpos($category, 'Industrial') !== false ) || (strpos($category, 'Fiber') !== false )){ ?>
      <div class="col-sm-3">
           
          <?php if ( is_active_sidebar( 'woo-product-sidebar' ) ) : ?><?php dynamic_sidebar( 'woo-product-sidebar' ); ?><?php endif; ?>
      </div>
      <?php } ?> 
      <?php if (strpos($category, 'Consumer Electronics') !== false  || (strpos($category, 'Gloves') !== false ) || (strpos($category, 'Industrial') !== false ) || (strpos($category, 'Fiber') !== false )){ ?>
      <div class="col-sm-9">
      <?php }else{ ?>
      
      <div class="col-sm-12">
          <?php } ?>
          
        <div class="product_landing">
          <?php the_content(); ?>
        </div>
      </div>
    </div>
    
  </div>
</div>
<?php get_footer(); ?>
