<?php
/**
 * The template for displaying Search Results pages
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

include('header2.php'); ?>
<!-- <div class="product_landing_banner">
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div class="product_list">
          <h1><?php the_title(); ?></h1>
        </div>
      </div>
    </div>
  </div>
</div> -->
	<div class="product_landing_section">
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div class="product_landing">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<h1 class="page-title" style="margin-top:15%">
				<?php
				/* translators: %s: Search query. */
				printf( __( 'Search Results for: %s', 'twentytwelve' ), '<span>' . get_search_query() . '</span>' );
				?>
				</h1>
			</header>
	<?php twentytwelve_content_nav( 'nav-above' ); ?>

		 <?php
                        // Start the Loop.
                        while ( have_posts() ) : the_post();
                        ?>
                        <ul style="margin-top:40px"><h4><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h4></ul>
                        <?php the_post_thumbnail('medium') ?>
                        <?php echo substr(get_the_excerpt(), 0, 200); ?>
                            <div class="h-readmore"> 
                                <a href="<?php the_permalink(); ?>">Read More</a>
                            </div>
			<hr size="10" noshade>
                        <?php
                        endwhile;
                else :
                // If no content, include the "No posts found" template.
                get_template_part( 'content', 'none' );
                endif;
                ?>       

		 </div>
      </div>
    </div>
    </div></div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
