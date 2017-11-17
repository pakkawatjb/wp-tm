<?php

/**
 * BuddyPress - Members Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - bp_legacy_theme_object_filter()
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

?>

<?php do_action( 'bp_before_members_loop' ); ?>

<?php if ( bp_has_members( bp_ajax_querystring( 'members' ) ) ) : ?>

	<div id="pag-top" class="pagination">

		<div class="pag-count" id="member-dir-count-top">

			<?php bp_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-dir-pag-top">

			<?php bp_members_pagination_links(); ?>

		</div>

	</div>

	<?php do_action( 'bp_before_directory_members_list' ); ?>

	<ul id="members-list" class="item-list" role="main">

		<?php while ( bp_members() ) : bp_the_member(); ?>

			<li>
				<div class="item-avatar">
					<a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar(); ?></a>
					<div class="whoop-friend-review-count">
                        <?php if( bp_is_active( 'friends' )) { ?>
						<span class="whoop-friend-count"><i class="fa fa-users"></i> <?php echo whoop_get_friend_count_for_user(bp_get_member_user_id());//echo friends_get_friend_count_for_user( bp_get_member_user_id()); ?></span>
                        <?php } ?>
						<span class="whoop-review-count"><i class="fa fa-star"></i> <?php $count = geodir_get_review_count_by_user_id(bp_get_member_user_id()); if($count) { echo $count; } else { echo "0";}?></span>
					</div>
				</div>

				<div class="item">
					<div class="item-title">
						<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
					</div>

					<?php do_action( 'bp_directory_members_item' ); ?>

					<?php
					/***
					 * If you want to show specific profile fields here you can,
					 * but it'll add an extra query for each member in the loop
					 * (only one regardless of the number of fields you show):
					 *
					 * bp_member_profile_data( 'field=the field name' );
					 */
					?>
				</div>

				<div class="action">

					<?php //do_action( 'bp_directory_members_actions' ); ?>

				</div>

				<div class="clear"></div>
			</li>

		<?php endwhile; ?>

	</ul>

	<?php do_action( 'bp_after_directory_members_list' ); ?>

	<?php bp_member_hidden_fields(); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="member-dir-count-bottom">

			<?php bp_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-dir-pag-bottom">

			<?php bp_members_pagination_links(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( "Sorry, no members were found.", GEODIRECTORY_FRAMEWORK ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'bp_after_members_loop' ); ?>
