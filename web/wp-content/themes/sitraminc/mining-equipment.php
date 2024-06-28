<?php
/**
 * Template Name: Mining Equipment Template
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

	<div class="product_landing_banner" style="background-image: url( <?php the_field('category_image'); ?>)";>
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
          <?php the_field('product_description'); ?>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <div class="product_detils_landing">
          <?php the_field('types_of_parts'); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="product_five_section">
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div>
          <div class="get_in_touch">
            <button type="button" id="myBtn">Get in touch</button>
          </div>
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
            <div class="modal-header"> <span class="close">←</span>
              <div>
                <h2>Get in touch</h2>
              </div>
				
            </div>
			  
 			   <div class="modal-body">
           <h1>Mining Products</h1>
          </div> 
			  <form action="https://sitraminc.com/wp-content/themes/sitraminc/email2.php" method="post" id="myForm">
<div class="email_form"  id="form2" style="margin-left:10%;">
	 <div class="row">
                  <div class="col-25">
                    <label for="equiptype">Equipment Type</label>
                  </div>
                  <div class="col-75">
                    <input type="text" id="equiptype" name="equiptype" placeholder="" style="margin-left:20px;">
                  </div>
	</div>
                <div class="row">
                  <div class="col-25">
                    <label for="compPrice">Competitive Prices</label>
                  </div>
                  <div class="col-75">
                    <input type="text" id="compPrice" name="compPrice" placeholder="" style="margin-left:20px;">
                  </div>
                </div>
             <div class="row">
                  <div class="col-25">
                    <label for="country">Required Replacement part Description</label>
                  </div>
                  <div class="col-75">
                    <textarea id="subject" name="replacementpart" placeholder="Shipper Shaft, Pinion 2nd Reduction, etc." style="margin-left:20px;" ></textarea>
                  </div>
                </div>
                 <div class="row">
                  <div class="col-25">
                    <label for="partNumber">Manufacturer's
                      Part Number</label>
                  </div>
                  <div class="col-75">
                    <input type="text" id="partNumber" name="partNumber" placeholder="" style="margin-left:20px;">
                  </div>
	</div></div>
          
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
					  <button type="button" id="btn1" onclick="validate()">SUBMIT</button>
					</div>
                  </div>
                </div>
			  </div>
			  </div>
              </form>
			  
			  
<!--              <div class="modal-body">
               <h1>Mining Products</h1>
              <form action="https://sitraminc.com/wp-content/themes/sitraminc/email2.php" method="post" id="myForm">
                 <div class="row">
                  <div class="col-25">
                    <label for="equiptype">Equipment Type</label>
                  </div>
                  <div class="col-75">
                    <input type="text" id="equiptype" name="equiptype" placeholder="">
                  </div>
                </div> 
                 <div class="row">
                  <div class="col-25">
                    <label for="compPrice">Competitive Prices</label>
                  </div>
                  <div class="col-75">
                    <input type="text" id="compPrice" name="compPrice" placeholder="">
                  </div>
                </div> 
                 <div class="row">
                  <div class="col-25">
                    <label for="country">Required Replacement part Description</label>
                  </div>
                  <div class="col-75">
                    <textarea id="subject" name="replacementpart" placeholder="Shipper Shaft, Pinion 2nd Reduction, etc." style="height:150px"></textarea>
                  </div>
                </div> 
                 <div class="row">
                  <div class="col-25">
                    <label for="partNumber">Manufacturer's
                      Part Number</label>
                  </div>
                  <div class="col-75">
                    <input type="text" id="partNumber" name="partNumber" placeholder="">
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
 				<div class="modal-footer-form">
          <div class="row" style="padding: 0 50px;">
						<div class="col-sm-6">
                      <h1>E-mail us main</h1>
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
 				  <input type="submit" value="Submit" id="btn1"><button type="button" id="btn1" onclick="validate()">SUBMIT</button>
						</div>
                  </div>
                </div>
			  </div>
			  </div>
                </form>
              </div> -->
            </div>
          </div>
			
        </div>
      </div>
    </div>
<?php get_footer(); ?>
