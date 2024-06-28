<?php
/**
 * Template Name: Front Page Template
 *
 * Description: A page template that provides a key component of WordPress as a CMS
 * by meeting the need for a carefully crafted introductory page. The front page template
 * in Twenty Twelve consists of a page content area for adding text, images, video --
 * anything you'd like -- followed by front-page-only widgets in one or two columns.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel"> 
  <!--
  <ol class="carousel-indicators">
    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
	<li data-target="#carouselExampleIndicators" data-slide-to="3"></li>
  </ol>
-->
  <div class="carousel-inner">
   <?php $row = 1; $args = array('post_type' => 'Banner','posts_per_page' => 4,'order' => 'DESC', );
							$loop = new WP_Query( $args );
							while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
    <div class="carousel-item<?php if($row == 1) {?> active<?php } ?>"> <img class="d-block w-100" src="<?php the_field('banner_image'); ?>" alt="First slide">
      <div class="carousel-caption d-none d-md-block">
        <h5><?php the_field('banner_text'); ?></h5>
        <button class="slider_button" id="myBtn<?php echo $row; ?>">PRODUCT CATEGORIES</button>
        
        <!-- The Modal -->
        
      </div>
    </div>
    <?php $row++; endwhile; wp_reset_query(); ?>
    
    
    
  </div>
  <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev"> <span class="carousel-control-prev-icon" aria-hidden="true"></span> <span class="sr-only">Previous</span> </a> <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next"> <span class="carousel-control-next-icon" aria-hidden="true"></span> <span class="sr-only">Next</span> </a> 
	
	
	</div>
<div id="myModal" class="modal"> 
          
          <!-- Modal content -->
          <div class="modal-content">
<!--             <div class="modal-header"> <span class="close" style="margin-top: 10px;
    margin-right: 10px;
    height: 10px;
    width: 19px;">&times;</span>
            </div> -->
            <div class="modal-body">
				<div class="modal-header"> <span class="close" style="margin-top: 10px;
    margin-right: 10px;
    height: 10px;
    width: 19px;">&times;</span>
            </div>
              <section class="products-section">
              
             <?php $categories = get_categories("taxonomy=categories&parent=0"); ?> 
                <h2>Business & Industrial Products</h2>
				   
                <div class="products-boxes">
                <?php foreach ($categories as $category) { 
					 
					?>
					
                  <div class="product-colum"><a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" rel="bookmark"> <img class="img-responsive" alt="Circuit Protection" src="<?php echo get_field('category_image', $category->taxonomy . "_" . $category->term_id);  ?>" height="170" width="170">			  
                    <p><?php echo $category->name; ?></a> </div><?php } ?>				
                </div>
              </section>
            </div>
            <div class="modal-footer">
              <h3>Sitram Inc.</h3>
            </div>
          </div>
        </div>
<!--START SECTION TWO-->
<?php
			while ( have_posts() ) :
				the_post();
				?>
<div class="section_two">
  <div class="container">
    <div class="row">
      <div class="col-sm-4">
        <div class="three_boxes">
          <div class="top-three">
            <h2><i class="far fa-thumbs-up"></i><?php the_field('integrity_heading'); ?></h2>
            <?php the_field('integrity_description'); ?>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="three_boxes">
          <div class="top-three">
            <h2><i class="fas fa-award"></i><?php the_field('quality_heading'); ?></h2>
            <?php the_field('quality_description'); ?>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="three_boxes">
          <div class="top-three">
            <h2><i class="fal fa-shield-check"></i><?php the_field('reliability_heading'); ?></h2>
            <?php the_field('reliability_description'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--END SECTION TWO--> 
<?php endwhile; // End of the loop. ?>
<!--START SECTION Three-->
<div class="container">
  <div class="row">
    <div class="col-sm-12">
      <div class="our_products"  id="section_three">
        <h1>Business & Industrial Products</h1>
      </div>
    </div>
  </div>
</div>
<div class="section_three">
  <div class="container">
    <div class="row">
    <?php $row1 = 1; $categories = get_categories("taxonomy=categories&parent=0"); ?>
    <?php foreach ($categories as $category) {
		if( strpos($category->name, "Fiber") !== false || strpos($category->name,"Mining") !== false || strpos($category->name, "Valves") !== false ) {
		?> 
<!--     <?php if($row1 == 4) {?><div class="col-sm-2"></div><?php } ?> -->
      <div class="col-sm-4" style="padding-bottom:100px;">
        <div class="three_boxes_third">
          <div class="products_box">
			   <a style="text-decoration: none;" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
            <h2><?php echo $category->name; ?></h2>
			  
            <img src="<?php echo get_field('category_image', $category->taxonomy . "_" . $category->term_id);  ?>" alt="" />
            <p><?php echo $category->description; ?></p>
			  </a>
            <div class="view-all-btn">
              <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" rel="bookmark"><button>VIEW ALL</button></a>
            </div>
          </div>
        </div>
      </div>
<!--       <?php if($row1 == 6) {?><div class="col-sm-2" style="padding-top:50px;"></div><?php } ?> -->
      <?php $row1++;} } ?>
      
      
    </div>
	  
	  <!-- Row #2-->
	  <div class="row">
    <?php $row1 = 1; $categories = get_categories("taxonomy=categories&parent=0"); ?>
    <?php foreach ($categories as $category) {
	
		if( strpos($category->name, "Health") !== false || strpos($category->name,"Hospitality") !== false || strpos($category->name, "Paper") !== false  || strpos($category->name,"Thermal") !== false) {
		?> 
		  
<!--     <?php if($row1 == 4) {?><div class="col-sm-2"></div><?php } ?> -->
      <div class="col-sm-4" style="padding-bottom:100px;">
        <div class="three_boxes_third">
          <div class="products_box">
			  <?php if(strpos($category->name,"Thermal") !== false) {
		?>
			  <a style="text-decoration: none;" href="<?php echo esc_url('https://sitraminc.com/thermal-and-cable-products/'); ?>">
				  <?php } else { ?>
			   <a style="text-decoration: none;" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
				   <?php } ?>
				   
            <h2><?php echo $category->name; ?></h2>
			  
            <img src="<?php echo get_field('category_image', $category->taxonomy . "_" . $category->term_id);  ?>" alt="" />
            <p><?php echo $category->description; ?></p>
			  </a>
			  <?php if(strpos($category->name,"Thermal") !== false) {
		?>
			  <div class="view-all-btn">
              <a href="<?php echo esc_url( 'https://sitraminc.com/thermal-and-cable-products/' ); ?>" rel="bookmark"><button>READ MORE</button></a>
            </div>
			  <?php } else { ?>
            <div class="view-all-btn">
              <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" rel="bookmark"><button>VIEW ALL</button></a>
            </div>
			  <?php } ?>
          </div>
        </div>
      </div>
<!--       <?php if($row1 == 6) {?><div class="col-sm-2" style="padding-top:50px;"></div><?php } ?> -->
      <?php $row1++;} } ?>
      
      
    </div>
    
  </div>
</div>
<!--END SECTION Three--> 

<?php get_footer(); ?>
