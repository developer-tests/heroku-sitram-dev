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
<?php $backgroundImg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' ); ?>
<div class="product_landing_banner" style="background: url('<?php echo $backgroundImg[0]; ?>') no-repeat; ">
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div class="product_list">
          <h1><?php the_title(); ?></h1>
        </div>
		  
      </div>
		
    </div>
  </div>
</div>
<div class="product_landing_section">
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div class="product_landing">
          <?php the_content(); ?>
        </div>
      </div>
    </div>
    
  </div>
</div>
<?php get_footer(); ?>
