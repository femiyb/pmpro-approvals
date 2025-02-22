<?php
	global $wpdb, $current_user;

	//only admins can get this
if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'pmpro_approvals' ) ) {
	die( 'You do not have permission to perform this action.' );
}

if ( isset( $_REQUEST['l'] ) ) {
	$l = intval( $_REQUEST['l'] );
} else {
	$l = false;
}

if ( ! empty( $_REQUEST['approve'] ) ) {
	PMPro_Approvals::approveMember( intval( $_REQUEST['approve'] ), $l );
} elseif ( ! empty( $_REQUEST['deny'] ) ) {
	PMPro_Approvals::denyMember( intval( $_REQUEST['deny'] ), $l );
} elseif ( ! empty( $_REQUEST['unapprove'] ) ) {
	PMPro_Approvals::resetMember( intval( $_REQUEST['unapprove'] ), $l );
}

	//get the user
if ( empty( $_REQUEST['user_id'] ) ) {
	wp_die( __( 'No user id passed in.', 'pmpro-approvals' ) );
} else {
	$user = get_userdata( intval( $_REQUEST['user_id'] ) );

	//user found?
	if ( empty( $user->ID ) ) {
		wp_die( sprintf( __( 'No user found with ID %d.', 'pmpro-approvals' ), intval( $_REQUEST['user_id'] ) ) );
	}
}
?>
<div class="wrap pmpro_admin">	
	
	<form id="posts-filter" method="get" action="">	
	<h2>
		<?php echo $user->ID; ?> - <?php echo esc_attr( $user->display_name ); ?> (<?php echo esc_attr( $user->user_login ); ?>)
		<a href="<?php echo admin_url( 'user-edit.php?user_id=' . $user->ID ); ?>" class="button button-primary">Edit Profile</a>
	</h2>	
	
	<h3><?php _e( 'Account Information', 'pmpro-approvals' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><label><?php _e( 'User ID', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo $user->ID; ?></td>
		</tr>		
		<tr>
			<th><label><?php _e( 'Username', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo esc_html( $user->user_login ); ?></td>
		</tr>
		<tr>
			<th><label><?php _e( 'Email', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo sanitize_email( $user->user_email ); ?></td>
		</tr>
		<tr>
			<th><label><?php _e( 'Membership Level', 'pmpro-approvals' ); ?></label></th>
			<td>
			<?php
			//Changed this to show Membership Level Name now, so approvers don't need to go back and forth to see what level the user is applying for.
			 $level_details = pmpro_getMembershipLevelForUser( $user->ID );

			 echo esc_html( $level_details->name );
        
			?>
			</td>
		</tr>
		<tr>
			<th><label><?php _e( 'Approval Status', 'pmpro-approvals' ); ?></label></th>
			<td>
			<?php
			//show status here
			if ( PMPro_Approvals::isApproved( $user->ID ) || PMPro_Approvals::isDenied( $user->ID ) ) {
				if ( ! PMPro_Approvals::getEmailConfirmation( $user->ID ) ) {
					_e( 'Email Confirmation Required.', 'pmpro-approvals' );
				} else {
					echo esc_html( PMPro_Approvals::getUserApprovalStatus( $user->ID, null, false ) );
				?>
				[<a href="javascript:askfirst('Are you sure you want to reset approval for <?php echo esc_attr( $user->user_login ); ?>?', '?page=pmpro-approvals&user_id=<?php echo $user->ID; ?>&unapprove=<?php echo $user->ID; ?>');">X</a>]
				<?php
				}   // end of email confirmation check.
			} else {
			?>
													
			<a href="?page=pmpro-approvals&user_id=<?php echo $user->ID; ?>&approve=<?php echo $user->ID; ?>">Approve</a> |
			<a href="?page=pmpro-approvals&user_id=<?php echo $user->ID; ?>&deny=<?php echo $user->ID; ?>">Deny</a>
			<?php
			}
			?>
			</td>
		</tr>
	</table>
	
	<?php
		if ( function_exists( 'pmprorh_getProfileFields' ) ) {
			global $pmprorh_registration_fields, $pmprorh_checkout_boxes;

			//show the fields
			if ( ! empty( $pmprorh_registration_fields ) ) {
				foreach ( $pmprorh_registration_fields as $where => $fields ) {
					$box = pmprorh_getCheckoutBoxByName( $where );
					?>
					<h3><?php echo esc_html( $box->label ); ?></h3>

					<table class="form-table">
					<?php
					//cycle through groups
					foreach ( $fields as $field ) {
						// show field as long as it's not false
						if ( false != $field->profile ) {
						?>
						<tr>
							<th><label><?php echo esc_attr( $field->label ); ?></label></th>
							<?php
							if ( is_array( get_user_meta( $user->ID, $field->name, true ) ) && 'file' === $field->type ) {
								$field = get_user_meta( $user->ID, $field->name, true );
								?>

								<td><a href="<?php echo esc_url( $field['fullurl'] ); ?>" target="_blank" rel="noopener noreferrer"><?php _e( 'View File', 'pmpro-approvals' ); ?></a> (<?php echo esc_attr( $field['filename'] ); ?>)</td>


							<?php } else { 
								$register_helper_fields = get_user_meta( $user->ID, $field->name, true );

								// Get all array option values and break up the array into readable content.
								if ( is_array( $register_helper_fields ) ) {
									 $rh_field_string = '';
									 foreach( $register_helper_fields as $key => $value ) {
										$rh_field_string .= $value . ', ';
									}

									// remove trailing comma from string.
									echo '<td>' . esc_html( rtrim( $rh_field_string, ', ' ) ) . '</td>';
								} else {
									echo '<td>' . esc_html( $register_helper_fields ) . '</td>';
								}
							
 							} ?>
						</tr>
						<?php
						}   //endif
					}
					?>
					</table>
					<?php
				}
			}
		}
	?>
	<a href="?page=pmpro-approvals" class="">&laquo; <?php _e( 'Back to Approvals', 'pmpro-approvals' ); ?></a>
</div>
