<?php
/**
 * The template for displaying Archive pages
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each specific one. For example, Twenty Twelve already
 * has tag.php for Tag archives, category.php for Category archives, and
 * author.php for Author archives.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>


    <?php

    $term = get_queried_object();

    $children = get_terms( $term->taxonomy, array(
        'parent'    => $term->term_id,
        'hide_empty' => false
    ) );
$cat_row = 0;
    if ( $children ) { ?>
<?php get_header(); ?>
    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel"> 
  <!--
  <ol class="carousel-indicators">
    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
	<li data-target="#carouselExampleIndicators" data-slide-to="3"></li>
  </ol>
--><?php $term = get_queried_object(); $image = get_field('category_image', $term->taxonomy . '_' . $term->term_id );?>
  <div class="carousel-inner"><div class="carousel-item active"><?php /*?><img class="d-block w-100" src="<?php echo $image; ?>" alt="First slide"><?php */?>
   <img class="d-block w-100" src="<?php echo get_field('banner_image', $term->taxonomy . '_' . $term->term_id ); ?>" alt=""> <?php 
					  /*?> <?php if($term->term_id == '2'){ ?><img class="d-block w-100" src="<?php echo get_template_directory_uri(); ?>/images/cover-image.jpg" alt=""><?php }elseif($term->term_id == '3'){ ?><img class="d-block w-100" src="<?php echo get_template_directory_uri(); ?>/images/cover-image.jpg" alt=""><?php }elseif($term->term_id == '4'){ ?><img class="d-block w-100" src="<?php echo get_template_directory_uri(); ?>/images/cover-image.jpg" alt=""><?php }elseif($term->term_id == '5'){ ?><img class="d-block w-100" src="<?php echo get_template_directory_uri(); ?>/images/cover-image.jpg" alt=""><?php }elseif($term->term_id == '6'){ ?><img class="d-block w-100" src="<?php echo get_template_directory_uri(); ?>/images/cover-image.jpg" alt=""><?php }else{ ?><img class="d-block w-100" src="<?php echo get_template_directory_uri(); ?>/images/cover-image.jpg" alt=""><?php } ?><?php */
	  ?>
      <div class="carousel-caption d-none d-md-block">
        <p>PRODUCT CATELOUGE</p>
        <h5 class="category_page" style="font-weight: 400 !important;"><?php
				/* translators: %s: Category title. */
				printf( __( '%s', 'twentytwelve' ), '<span>' . single_cat_title( '', false ) . '</span>' );
				?></h5>
      </div>
    </div>
  </div>
</div>

<div class="category_second">
  <div class="container">
    <div class="row">
<?php
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}?>
		<?php $category = single_cat_title( '', false );
			if (strpos($category, 'Valves') !== false || strpos($category, 'Manifolds') !== false) {
				console_log("this is valves category so apply different styling");?>
		 <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div class="product_detils_list" style="margin-top:30px; font-weight:semi-bold">
			  Sitram Inc supplies valves and manifolds from world class manufacturers. Our valves are widely used in chemical, petrochemical, oil and gas, off-shore platforms, power generation industries.

        </div>
      </div>
    </div>
  </div>
    			<?php foreach( $children as $subcat )
        		{
					$linkcat = get_field('custom_link', $subcat->taxonomy . '_' . $subcat->term_id); ?>
      				<div class="col-sm-6  mt-4">
						<h1><?php echo $subcat->name; ?></h1>
						 <img class="valves-product-banner" src="<?php echo get_field('category_image', $subcat->taxonomy . "_" . $subcat->term_id); ?>" alt="">
<!--         			<div class="product_banner<?php if($cat_row != 0){?>_<?php echo $cat_row; }?>" style="background-image:url('<?php echo 			get_field('category_image', $subcat->taxonomy . "_" . $subcat->term_id);  ?>'); background-repeat: no-repeat !important; background-size: 100% 100% !important; border:2px solid #eaeaea !important; border-radius: 5px !important;">
          			
					</div> -->
						<a href="<?php if($linkcat != ''){ echo $linkcat;?><?php }else{ ?><?php echo esc_url(get_term_link($subcat, $subcat->taxonomy)); }?>">					  <button style="margin-top:20px">View all products</button></a>
      				</div>
      <?php $cat_row++;}
			}
		else {
			console_log("this is NOT Valves category");
			foreach( $children as $subcat )
        {
	$linkcat = get_field('custom_link', $subcat->taxonomy . '_' . $subcat->term_id); ?>
      <div class="col-sm-6  mt-4">
        <div class="product_banner<?php if($cat_row != 0){?>_<?php echo $cat_row; }?>" style="background-image:url('<?php echo get_field('category_image', $subcat->taxonomy . "_" . $subcat->term_id);  ?>'); background-repeat: no-repeat !important; background-size: 100% 100% !important; border:2px solid #eaeaea !important; border-radius: 5px !important;">
          <h1><?php echo $subcat->name; ?></h1>
          <p> <?php echo $subcat->description; ?> </p>
          <a href="<?php if($linkcat != ''){ echo $linkcat;?><?php }else{ ?><?php echo esc_url(get_term_link($subcat, $subcat->taxonomy)); }?>"><button>View all products</button></a>
        </div>
      </div>
      <?php $cat_row++;}
		}?>
<!--        <?php foreach( $children as $subcat )
        {
	$linkcat = get_field('custom_link', $subcat->taxonomy . '_' . $subcat->term_id); ?>
      <div class="col-sm-6  mt-4">
        <div class="product_banner<?php if($cat_row != 0){?>_<?php echo $cat_row; }?>" style="background-image:url('<?php echo get_field('category_image', $subcat->taxonomy . "_" . $subcat->term_id);  ?>'); background-repeat: no-repeat !important; background-size: 100% 100% !important; border:2px solid #eaeaea !important; border-radius: 5px !important;">
          <h1><?php echo $subcat->name; ?></h1>
          <p> <?php echo $subcat->description; ?> </p>
          <a href="<?php if($linkcat != ''){ echo $linkcat;?><?php }else{ ?><?php echo esc_url(get_term_link($subcat, $subcat->taxonomy)); }?>"><button>View all products</button></a>
        </div>
      </div>
      <?php $cat_row++;} ?> -->
      
    </div>
    
    
  </div>
</div>
<?php }else{ ?>
<!--START SECTION TWO-->
<?php include('header2.php'); ?>
<?php  if ( have_posts() ) : ?>
<div class="product_list_banner" 
	 <?php if (get_field('banner_image')) { ?>
	 style="background-image: url(<?php echo get_field('banner_image'); ?>); "> 
	<?php } ?>
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div class="product_list">
          <h1 style="margin-inline: 30px"><?php printf( __( '%s', 'twentytwelve' ), '<span>' . single_cat_title( '', false ) . '</span>' ); ?></h1>
        </div>
      </div>
    </div>
  </div>
</div>
<!--END SECTION TWO-->
<div class="category_second"> 
<div class="container">
<div class="row">
<?php
			// Start the Loop.
			while ( have_posts() ) :
				the_post(); 
				
				
				?>
      									
	<div class="col-sm-6  mt-4">
        <div class="product_banner" style="background-image:url('<?php echo get_field('banner_image');  ?>'); background-repeat: no-repeat !important; background-size: 100% 100% !important; border:2px solid #eaeaea !important; border-radius: 5px !important;">
          <h1><?php echo get_the_title(); ?></h1> 
			<p><?php the_field('product_details_and_options_text'); ?> </p>
          <a href="<?php echo get_the_permalink(); ?>"><button>View product</button></a>
        </div>
    </div>
					

 
<?php endwhile;

			//twentytwelve_content_nav( 'nav-below' );
			?>
</div>
</div>
</div>
		<?php else : ?>
		<div class="category_second"> 
<div class="container">
<div class="row">

			<?php get_template_part( 'content', 'none' ); ?>
			
</div>
</div>
</div>			
		<?php endif; ?>
        
<?php /*?><div class="section_two">
  <div class="container">
    <div class="row">
	<section id="primary" class="site-content">
		<div id="content" role="main">


		<?php  if ( have_posts() ) : ?>
			<header class="archive-header">
				<h1 class="archive-title">
				<?php the_title(); ?>
				</h1>
			</header><!-- .archive-header -->

			<?php
			// Start the Loop.
			while ( have_posts() ) :
				the_post();

				
				the_content();

			endwhile;

			twentytwelve_content_nav( 'nav-below' );
			?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?><?php */?>
<?php } ?>
		
<?php //get_sidebar(); ?>
<?php get_footer(); ?>
