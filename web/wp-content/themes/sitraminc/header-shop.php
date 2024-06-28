<?php
/**
 * The Header template for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) & !(IE 8)]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="https://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>">
<?php // Loads HTML5 JavaScript file to add support for HTML5 elements in older IE versions. ?>
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js?ver=3.7.0" type="text/javascript"></script>
<![endif]-->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" >
<!-- JS, Popper.js, and jQuery --> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/popper.min.js"></script> 
<script src="<?php echo get_template_directory_uri(); ?>/assets/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">	
	
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"/>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/media.css">
<script src="<?php echo get_template_directory_uri(); ?>/assets/script.js"></script>
<?php if(is_archive()){ ?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/category_style.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/media_cat.css">
<?php } ?>
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="product_list_header sit-header-light" id="main-header">
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
  <div class="container"> <a href="<?php echo get_home_url(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/images/1-Logo-Horizontal-Light-BG-product.png" /></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        
         <div class="row mob-row">
            <div class="col-10">
                <img class="text-center" src="https://sitraminc.com/wp-content/themes/sitraminc/images/1-Logo-Horizontal-Light-BG-footer.png">
            </div>
             <div class="col-2">
                      <button class="navbar-toggler close-btn" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon-close"></span> </button>
            </div>
            </div>
   
        
        
      <div class="search">
        <form role="search" method="get" class="search-form example" action="<?php echo home_url( '/' ); ?>">
          <input type="text" placeholder="Search" style="color:black !important;"  name="s">
			 <input type="hidden" name="post_type" value="products" /> <!-- // hidden 'products' value -->
          <button type="submit"><i class="fa fa-search"></i></button>
        </form>
      </div>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item"> <a class="nav-link" href="<?php echo get_home_url(); ?>/#section_three">Products	</a> </li>
		  <li class="nav-item"> <a class="nav-link" href="<?php echo get_home_url(); ?>/shop/">Shop</a> </li>
        <li class="nav-item"> <a class="nav-link" href="<?php echo get_home_url(); ?>/contact/">Contact Us</a> </li>
        <!--
        <li class="nav-item">
          <a class="nav-link" href="#">Portfolio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Contact</a>
        </li>
-->
      </ul>
    </div>
  </div>
</nav>
</div>
