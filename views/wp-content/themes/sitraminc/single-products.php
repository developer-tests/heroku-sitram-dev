<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

//get_header('new'); ?>

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
          <h1 style="margin-inline: 30px"><?php printf( __( '%s', 'twentytwelve' ), '<span>' . the_title( '', false ) . '</span>' ); ?></h1>
        </div>
      </div>
    </div>
  </div>
</div>
<!--END SECTION TWO--> 
<?php
			// Start the Loop.
			while ( have_posts() ) :
				the_post(); ?>
<?php if( get_field('product_details_and_options_heading') ) { ?>
<!--START SECTION Three-->
<div class="product_details_section">
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div class="product_detils">
          <h1><?php the_field('product_details_and_options_heading'); ?></h1>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <div class="product_detils_list">
          <?php the_field('product_details_and_options_text'); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php } ?>
<?php if( get_field('product_description') ) { ?>
<!--START SECTION Three-->
<div class="product_details_section">
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div class="simple_product_details">
          <?php the_field('product_description'); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php } ?>
<!--END SECTION Three--> 
<!--START SECTION Four-->
<?php if( get_field('fretures_&_specifications_heading') ) { ?>
<div class="product_details_section">
<div class="container">
<div class="row">
  <div class="col-sm-12">
    <div class="product_detils">
      <h1><?php the_field('fretures_&_specifications_heading'); ?></h1>
    </div>
  </div>
</div>
<div class="row">
<div class="col-sm-12">
<div class="product_detils_list">
<?php the_field('fretures_&_specifications_text'); ?>
<?php } ?>
<!-- DROP DOWN -->
<div class="dropdown_accordion">
<ul id="accordion" class="accordion">
<?php if( get_field('first_heading_content') ) { ?>
<li>
  <div class="link"><i class="fad fa-network-wired"></i><?php the_field('first_heading'); ?><i class="fa fa-chevron-down"></i></div>
  <?php the_field('first_heading_content'); ?>
</li>
<?php } ?>
<?php if( get_field('second_heading_content') ) { ?>
<li>
  <div class="link"><i class="fas fa-ethernet"></i><?php the_field('second_heading'); ?><i class="fa fa-chevron-down"></i></div>
  <?php the_field('second_heading_content'); ?>
</li>
<?php } ?>
<?php if( get_field('third_heading_content') ) { ?>
<li>
  <div class="link"><i class="fas fa-plug"></i><?php the_field('third_heading'); ?><i class="fa fa-chevron-down"></i></div>
  <?php the_field('third_heading_content'); ?>
</li>
<?php } ?>
</ul>
</div>
<!-- DROP DOWN -->
</div>
</div>
</div>
</div>
</div>
<!--END SECTION Four--> 

<!--START SECTION FIVE-->
<div class="product_five_section">
<div class="container">
  <div class="row">
    <div class="col-sm-6">
      <div class="section_five">
        <h1> List of Products </h1>
      </div>
    </div>
    <div class="col-sm-6">
     
    </div>
  </div>
	<div class="simple_product_details">
		 <?php if( single_cat_title('', false) == "Patchcords" ) { ?>
		  <div class="row">
			<div class="col-sm-12">
			  <div class="section_five_buttons">
				<div id="myDIV">
				  <button class="btn btn-outline-primary sdFilter" id="simplexb">Simplex</button>
				  <button class="btn btn-outline-primary sdFilter" id="duplexb">Duplex</button>
				  <button class="btn btn-outline-primary sdFilter" id="duplexb">Show All</button>
				</div>
			  </div>
			</div>
		   </div>
		  <?php } ?>
		<?php if( single_cat_title('', false) == "Connectors" ) { ?>
		  <div class="row">
			<div class="col-sm-12">
			  <div class="section_five_buttons">
				<div id="myDIV">
				  <button class="btn btn-outline-primary sdFilter" >Single Mode</button>
				  <button class="btn btn-outline-primary sdFilter" >Multimode</button>
					<button class="btn btn-outline-primary sdFilter" >Bulk Cable</button>
				  <button class="btn btn-outline-primary sdFilter" >Show All</button>
				</div>
			  </div>
			</div>
		   </div>
		  <?php } ?>
          <?php the_field('list_shortcode'); ?>
        </div>
	
	<?php if( have_rows('list_of_valve_products') ): ?>
	<div class="grid-container">
    <?php while( have_rows('list_of_valve_products') ): the_row(); 
        ?>
<div class="col-sm-6  mt-4" style="max-width:100%">
			<p class="valve-product-name"><?php the_sub_field('product_name'); ?></p>
			<p class="valve-details"><?php the_sub_field('series'); ?></p>
			<p class="valve-details"><?php the_sub_field('model'); ?></p>
		    
		   <?php if( single_cat_title('', false) == "Hospitality Furnishing Products" ) { ?>
			
           <img class="valve-images" src="<?php echo get_sub_field('product_image'); ?>" alt="">
				  <?php if( get_sub_field('custom_products') ) { ?>
	<a href="<?php the_sub_field('custom_products'); ?>">
		   <button class="valve-button">View Custom Products</button>
			</a>
				<?php } ?>
		
		   <?php if( get_sub_field('specification') ) { ?>
	       <a href="<?php the_sub_field('specification'); ?>">
		   <button class="button-regular">View Regular Products</button>
			</a>
<?php } ?>
		   <?php } else { ?>
		   <a href="<?php the_sub_field('specification'); ?>">
		   <img class="valve-images" src="<?php echo get_sub_field('product_image'); ?>" alt="">
	 	  <button class="valve-button">Specifications</button></a>
		 <?php } ?>
		
	  </div>

    <?php endwhile; ?>
		</div>
<?php endif; ?>
	
	<?php if( have_rows('list_of_healthcare_products') ): ?>
	<div class="grid-container">
    <?php while( have_rows('list_of_healthcare_products') ): the_row(); 
        ?>
      <div class="col-sm-6  mt-4" style="max-width:100%">
			<p class="valve-product-name"><?php the_sub_field('product_name'); ?></p>
		   <a href="<?php the_sub_field('link'); ?>">
		   <img class="valve-images" src="<?php echo get_sub_field('product_image'); ?>" alt="">
	 	  <button class="button-regular ">View All Products</button></a>
	  </div>
    <?php endwhile; ?>
		</div>
<?php endif; ?>
	
	<?php if( have_rows('paper_products_list') ): ?>
	<ul style="margin-bottom:50px">

    <?php while( have_rows('paper_products_list') ): the_row(); 
        ?><li style="margin-top:20px">
		   <a style="color:black; text-decoration:underline"href="<?php the_sub_field('linktopdf'); ?>">
			   <?php the_sub_field('product_name'); ?>
	 	  </a>
</li>
			
    <?php endwhile; ?>
		</ul>
<?php endif; ?>
	
	<?php if( have_rows('product_images') ): ?>
	<div class="grid-container">
    <?php while( have_rows('product_images') ): the_row(); 
        ?>
      <div class="col-sm-6" style="max-width:100%">
		   <img class="paper-images" src="<?php echo get_sub_field('image_url'); ?>" alt="">
	  </div>
    <?php endwhile; ?>
		</div>
<?php endif; ?>
	
	
	<?php if( get_field('product_image') ) { ?>
<!--START SECTION Three-->
<div class="product_details_section">
  <div class="container">
	  <div class="category_second">
    <div class="col-sm-6  mt-4">
		<?php $image = get_field('product_image'); ?>
						<h5  style="color:#1e75bd;font-size:18px;">
							<?php the_field('product_name'); ?>
		</h5>
						 <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($image); ?>" />
						
      				</div>
	  <a href="<?php the_field('specification'); ?>"><button style="margin-top:30px">Specifications</button></a>
	  </div>
  </div>
</div>
<?php } ?>

	<div class="get_in_touch">
        <button type="button" id="myBtn">Get in touch</button>
        <p id="count_product"><span id="selected">0</span> Items selected</p>
      </div>
	<div class="simple_product_details" style="font-style:italic">
          <?php the_field('disclaimer_text'); ?>
        </div>
	
	<div class="simple_product_details" style="font-style:italic">
          *<?php the_field('return_policy'); ?>
        </div>
    
    <div id="myModal" class="modal"> 
        
        <!-- Modal content -->
        <div class="modal-content">
          <div class="modal-header"> <span class="close">&#8592;</span>
            <div>
              <h2>Order List</h2>
            </div>
            <div>
              <p><span id="selectedp">0</span> Items Selected</p>
            </div>
          </div>
          <div class="modal-body">
           <h1><?php printf( __( '%s', 'twentytwelve' ), '<span>' . single_cat_title( '', false ) . '</span>' ); ?></h1>
          </div>
          <form action="https://sitraminc.com/wp-content/themes/sitraminc/email2.php" method="post" id="myForm">
          <div class="modal-body">
            <div class="products_dropdow_next">
            
              <ul id="accordion_new" class="accordion_new">             
              
              </ul>
            </div>
          </div>
          
          <div class="modal-footer-form">
          <div class="row" style="padding: 0 50px;">
						<div class="col-sm-6">
                      <h1>E-mail us</h1>
                    </div>
                  </div>
            <div class="email_form"  id="form1">
              
                <div class="form-row">
					<div class="col">
                    <input type="text" id="name" name="name" class="form-control" placeholder="Full Legal Name">
                  </div>
                  <div class="col">
                    <input type="text" id="email" name="email" class="form-control" placeholder="Valid Email Address">
                  </div>
                 
                </div>
                <div class="form-row">
					 <div class="col">
                    <input type="text" id="companyName" name="companyName" class="form-control" placeholder="Company Name">
                  </div>
				</div>
				<div class="form-row">
                    <div class="col">
                      <input type="text" id="address1" class="form-control" name="address1" placeholder="Address Line 1">
                      <input type="text" id="address2" class="form-control" name="address2" placeholder="Address Line 2">
                    </div>
                  </div>
                    <div class="form-row">
                      <div class="col">
                      <input type="text" id="city" name="city" class="form-control" placeholder="City">
                      </div>
				</div>
				<div class="form-row">
                      <div class="col">
                      <input type="text" id="state" name="state" class="form-control" placeholder="State/Province">
                      </div>
                    </div>
                    <div class="form-row" id="zip_contry">
                      <div class="col">
                      <input type="text" id="country" name="country" class="form-control" placeholder="Country">
                      </div>
                      <div class="col">
                      <input type="text" id="zip" name="zip" class="form-control" placeholder="Zip">
                      </div>
                    </div>
                <div class="form-row" style="text-align: center;color: red;">
                  <div class="col">
                    <div id="warning"></div>
                  </div>
                </div>
				  <div class="form-row" style="text-align: center;color: red;">
                  <div class="col">
                    <div class="get_in_touch">
					  <!--<input type="submit" value="Submit" id="btn1">--><button type="button" id="btn1" onclick="validate()">SUBMIT</button>
					</div>
                  </div>
                </div>
			  </div>
			  </div>
              </form>
            </div>
            <div class="contect_us" id="form2" style="display: none;">
              <form>
                <div>
                  <h1>Contact Number : + 123 456 789 /  + 123 456 789 </h1>
                 	<div class="only_line"></div>
					<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer </p>
                </div>
              </form>
            </div>
          </div>
          
          
        </div>
      </div>
    
    <!--END SECTION FIVE--> 
<?php endwhile;

			twentytwelve_content_nav( 'nav-below' );
			?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>
        
<?php get_footer(); ?>
