<?php
/**
 * Template Name: Gloves Template
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
        <div class="gloves_accordian">
          <?php the_field('product_description'); ?>
        </div>
      </div>
    </div>
  </div>
</div>

	<?php if( have_rows('list_of_healthcare_products') ): ?>
	<div class="container mb-4">
    <div class="row">
        
	<!--<div class="gloves-grid-container">-->
    <?php while( have_rows('list_of_healthcare_products') ): the_row(); 
        ?>
      <div class="col-sm-3  mt-4" style="max-width:100%">
		   <img class="valve-images" src="<?php echo get_sub_field('product_image'); ?>" alt="">
		  <p class="valve-product-name" style="margin-top:20px"><?php the_sub_field('product_name'); ?></p>
		  <?php if(!empty(get_sub_field('link'))) { ?><a href="<?php echo get_sub_field('link') ?>" class="sr-shop-now mt-1">Shop Now</a> <?php } ?>
	  </div>
    <?php endwhile; ?>
		</div>
		
		</div>
     
<?php endif; ?>
	

<div class="product_land_section">
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div>
          <div class="get_in_touch">
            <button type="button" id="myBtn">Get in touch</button>
          </div>
        </div>
		  	<div class="simple_product_details" style="font-style:italic">
          *<?php the_field('return_policy'); ?>
        </div>
        <div id="myModal" class="modal"> 
          <!-- Modal content -->
          <div class="modal-content">
            <div class="modal-header"> <span class="close">←</span>
              <div>
                <h2>Get in touch</h2>
              </div>
            </div>
            <div class="modal-body">
              <h1>Gloves</h1>
			  </div>
               <form action="https://sitraminc.com/wp-content/themes/sitraminc/email_gloves_request.php" method="post" id="myForm" style="margin:50px">
                <div class="row">
                  <div class="col-25">
                    <label for="quantity">Quantity</label>
                  </div>
                  <div class="col-75">
                    <input type="text" id="quantity" name="quantity" placeholder="">
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label for="delivery">Delivery/Pickup from warehouse</label>
                  </div>
                  <div class="col-75">
                    <input type="text" id="delivery" name="delivery" placeholder="">
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label for="size">Sizes Required</label>
                  </div>
                  <div class="col-75">
                    <textarea id="size" name="size" placeholder="Extra-Small, Small, Medium, Large or Extra-Large" style="height:150px"></textarea>
                  </div>
                </div>
            <div class="modal-footer-form">
              <div class="email_form" id="form1" style="">
					<div class="form-row">
						<div class="col">
                      <h1>E-mail us</h1>
                    </div>
                  </div>
                  <div class="form-row">
                  <div class="col">
                    <input type="email" name="email" class="form-control" placeholder="JohnDoe@gmail.com">
                  </div>
                  <div class="col">
                    <input type="text" name="companyname" class="form-control" placeholder="Company Name">
                  </div>
                </div>
                  <div class="form-row">
                  <div class="col">
                    <div id="form_row_address">
                      <input type="text" class="form-control" name="address1" placeholder="Address Line 1">
                      <input type="text" class="form-control" name="address2" placeholder="Address Line 2">
                    </div>
                  </div>
                  <div class="col">
                    <div class="form-row">
                      <div class="col">
                      <input type="text" name="city" class="form-control" placeholder="City">
                       
                      </div>
                      <div class="col">
                      <input type="text" name="state" class="form-control" placeholder="State/Province">
                      
                      </div>
                    </div>
                    <div class="form-row" id="zip_contry">
                      <div class="col">
                      <input type="text" name="country" class="form-control" placeholder="Country">
                     
                      </div>
                      <div class="col">
                      <input type="text" name="zip" class="form-control" placeholder="Zip">
                     
                      </div>
                    </div>
                  </div>
                </div>
                  <div class="form-row" style="text-align: center;color: red;">
                    <div class="col">
                      <div class="get_in_touch">
                        <input type="submit" value="SUBMIT">
                      </div>
                    </div>
                  </div>
				</div>
				   </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php get_footer(); ?>
