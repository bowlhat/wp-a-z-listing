<?php
/**
 * Default multicolumn template for the A-Z Listing plugin
 *
 * This template will be given the variable `$a_z_query` which is an instance
 * of `A_Z_Listing`.
 *
 * You can override this template by copying this file into your theme
 * directory.
 *
 * @package a-z-listing
 */

/**
 * This value indicates the number of posts to require before a second column
 * is created. However, due to the design of web browsers, the posts will flow
 * evenly between the available columns. E.g. if you have 11 items, a value of
 * 10 here will create two columns with 6 items in the first column and 5 items
 * in the second column.
 */
$_a_z_listing_minpercol = 10;
?>
<div id="az-tabs">
	<div id="letters">
		<div class="az-letters">
			<?php $a_z_query->the_letters(); ?>
		</div>
	</div>
	<?php if ( $a_z_query->have_letters() ) : ?>
	<div id="az-slider">
		<div id="inner-slider">
			<?php
			while ( $a_z_query->have_letters() ) :
				$a_z_query->the_letter();
				?>
				<?php if ( $a_z_query->have_items() ) : ?>
					<?php
					$item_count  = $a_z_query->get_the_letter_count();
					$num_columns = ceil(
						$item_count / $_a_z_listing_minpercol
					);
					?>
					<div class="letter-section"
						id="<?php $a_z_query->the_letter_id(); ?>">
						<h2 class="letter-title">
							<span>
								<?php $a_z_query->the_letter_title(); ?>
							</span>
						</h2>
						<?php $column_class = "max-$num_columns-columns"; ?>
						<ul class="columns <?php echo $column_class; ?>">
							<?php
							while ( $a_z_query->have_items() ) :
								$a_z_query->the_item();
								?>
								<li>
									<a href="<?php $a_z_query->the_permalink(); ?>">
										<?php $a_z_query->the_title(); ?>
									</a>
								</li>
							<?php endwhile; ?>
						</ul>
						<div class="back-to-top">
							<a href="#letters">
								<?php _e( 'Back to top', 'a-z-listing' ); ?>
							</a>
						</div>
					</div>
					<?php
				endif;
			endwhile;
			?>
		</div>
	</div>
	<?php else : ?>
		<p><?php esc_html_e( 'There are no posts included in this index.', 'a-z-listing' ); ?></p>
	<?php endif; ?>
</div>
