<?php
/**
 * Gift Card product add to cart
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Gift Cards
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<script type="text/template" id="tmpl-gift-card-presets">
	<div id="ywgc-choose-design clearfix" class="ywgc-template-design">
		<div class="ywgc-design-list-menu clearfix">
			<?php if ( count( $categories ) > 0 ) : ?>
				<?php
				/**
				 * APPLY_FILTER: yith_ywgc_design_categories_title_text
				 *
				 * Filter the "Categories" title in the gift card form in the product page.
				 *
				 * @param string the title text
				 *
				 * @return string
				 */
				?>
				<h3 class="ywgc-design-categories-title"><?php echo apply_filters( 'yith_ywgc_design_categories_title_text', esc_html__( 'Categories', 'yith-woocommerce-gift-cards' ) ); ?></h3>
				<ul class="ywgc-template-categories">
					<li class="ywgc-template-item ywgc-category-all">
						<a href="#" class="ywgc-show-category ywgc-category-selected" data-category-id="all">
							<?php
							/**
							 * APPLY_FILTER: yith_ywgc_show_all_design_text
							 *
							 * Filter the "All" text displayed in the last gallery image to display all the images in a modal.
							 *
							 * @param string the text
							 *
							 * @return string
							 */
							?>
							<?php echo apply_filters( 'yith_ywgc_show_all_design_text', _x( 'All', 'choose image', 'yith-woocommerce-gift-cards' ) ); ?>
						</a>
					</li>
					<?php
					foreach ( $categories as $item ) :

						$term_slug_array = array();

						foreach ( $selected_categories_unserialized  as $selected_categories ) {

							if ( $selected_categories != 0 ) {
								$term_slug_array[] = get_term( $selected_categories )->slug;
							}
						}

						if ( in_array( 'none', $term_slug_array ) ) {
							continue;
						}

						if ( in_array( $item->term_id, $selected_categories_unserialized ) && $item->term_id != $product->get_id() || in_array( 'all', $term_slug_array ) || count( $selected_categories_unserialized ) == 1 ) :
							?>

							<li class="ywgc-template-item ywgc-category-<?php echo $item->term_id; ?>">
								<a href="#" class="ywgc-show-category" data-category-id="ywgc-category-<?php echo $item->term_id; ?>"><?php echo $item->name; ?></a>
							</li>
						<?php endif; ?>

					<?php endforeach; ?>

				</ul>
			<?php endif; ?>
		</div>
		<div class="ywgc-design-list-modal">

			<?php
			foreach ( $item_categories as $item_id => $categories ) :

				$category_id = str_replace( 'ywgc-category-', '', $categories );

				$category_id = explode( ' ', $category_id );

				foreach ( $category_id as $key => $value ) {
					$category_id = $value;
				}

				$term_slug_array = array();

				foreach ( $selected_categories_unserialized  as $selected_categories ) {

					if ( $selected_categories != 0 ) {
						$term_slug_array[] = get_term( $selected_categories )->slug;
					}
				}

				if ( in_array( 'none', $term_slug_array ) ) {
					continue;
				}

				$aux_prod = wc_get_product( $item_id );

				if ( is_object( $aux_prod ) && $aux_prod instanceof WC_Product_Gift_Card ) {
					continue;
				}

				if ( in_array( $category_id, $selected_categories_unserialized ) && $item_id != $product->get_id() || in_array( 'all', $term_slug_array ) || count( $selected_categories_unserialized ) == 1 ) :
					?>

					<div class="ywgc-design-item <?php echo $categories; ?> template-<?php echo $item_id; ?>">

						<div class="ywgc-preset-image" data-design-id="<?php echo $item_id; ?>"  data-design-url="<?php echo yith_get_attachment_image_url( intval( $item_id ), 'full' ); ?>" >
							<?php echo wp_get_attachment_image( intval( $item_id ), apply_filters( 'yith_ywgc_preset_image_size', 'shop_catalog' ) ); ?>
							<span class="choose-design"><?php _e( 'Choose', 'yith-woocommerce-gift-cards' ); ?></span>
						</div>
						<?php
						if ( ( 'yes' == get_option( 'ywgc_show_preset_title', 'no' ) ) ) :
							$post = get_post( $item_id );
							if ( $post ) :
								?>
								<span class="ywgc-preset-title"><?php echo $post->post_title; ?></span>
							<?php endif; ?>
						<?php endif; ?>

					</div>
				<?php endif; ?>

			<?php endforeach; ?>

		</div>

	</div>
</script>

