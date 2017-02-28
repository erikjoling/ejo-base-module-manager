<?php
if ( !current_user_can( 'manage_options' ) )  {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}
?>

<style type="text/css">
	.bottom-buttons { clear: both; }
	.dependancy-good,
	.dependancies-hidden { color: #ddd; }
	.ejo-base-module.unavailable td{
		
	}
</style>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1> 

	<form action="<?php echo esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ) ); ?>" method="post">

		<table class="wp-list-table widefat plugins">
			<thead>
				<tr>
					<td id="cb" class="manage-column column-cb check-column"><?php /* <label class="screen-reader-text" for="cb-select-all-1">Alles selecteren</label><input id="cb-select-all-1" type="checkbox">*/ ?></td>
					<th scope="col" id="name" class="manage-column column-name column-primary">EJO Base Module</th>
					<th scope="col" id="description" class="manage-column column-description">Beschrijving</th>	
				</tr>
			</thead>

			<tbody id="the-list">

				<?php 

				$ejo_base_active_modules = get_option( 'ejo_base_active_modules', array() );
				foreach (EJO_Base::$modules as $id => $module) {
					show_module_row( $module, $ejo_base_active_modules );
				}

				?>

			</tbody>

		</table>

	</form>

</div><!-- END .wrap -->
<?php

function show_module_row($module, $active_modules = null)
{
	if ( ! $active_modules )
            $active_modules = get_option( 'ejo_base_active_modules', array() );

	$menu_page = EJO_Base_Module_Manager::$menu_page;

	$is_available = EJO_Base_Module::is_available( $module['id'] );
	$has_theme_support = EJO_Base_Module::has_theme_support( $module['id'] );
	$is_active = EJO_Base_Module::is_active( $module['id'], $active_modules );

	$classes = ($has_theme_support) ? ' supported' : ' not-supported';
	$active = ($is_active) ? 'active' : 'inactive';
	$available = ($is_available) ? 'available' : 'unavailable';

	?>

	<tr class="<?php echo $active; ?> <?php echo $available; ?> ejo-base-module" data-slug="<?php echo $module['id']; ?>">

		<th scope="row" class="check-column">
			<label class="screen-reader-text" for="checkbox_<?php echo $module['id']; ?>"><?php echo $module['name']; ?> selecteren</label>
			<?php /* <input name="checked[]" value="<?php echo $module['id']; ?>" id="checkbox_<?php echo $module['id']; ?>" type="checkbox"> */ ?>
		</th>

		<td class="plugin-title column-primary">

			<strong><?php echo $module['name']; ?></strong>

			<div class="row-actions visible">

				<?php if ($is_available) : ?>

					<?php if ($is_active) : ?>
						<span class="deactivate">
							<a href="admin.php?page=<?php echo $menu_page; ?>&amp;action=deactivate&amp;module=<?php echo $module['id']; ?>" aria-label="<?php echo __('Deactivate') . ' ' . $module['name']; ?>"><?php _e('Deactivate'); ?></a>
						</span>
					<?php else : ?>
						<span class="activate">
							<a href="admin.php?page=<?php echo $menu_page; ?>&amp;action=activate&amp;module=<?php echo $module['id']; ?>" aria-label="<?php echo __('Activate') . ' ' . $module['name']; ?>"><?php _e('Activate'); ?></a>
						</span>
					<?php endif; // END active check ?>

				<?php else : ?>

					<span><?php EJO_Base_Module::print_why_not_available($module['id']); ?></span>

				<?php endif; // END availability check ?>

			</div>
			<button type="button" class="toggle-row">
				<span class="screen-reader-text"><?php _e('Show more details'); ?></span>
			</button>

		</td>
		<td class="column-description desc">
			<div class="plugin-description">
				<p><?php echo $module['description']; ?></p>
			</div>
			<div class="<?php echo $active; ?> second plugin-version-author-uri">

				<?php EJO_Base_Module::show_dependancies($module['id']); ?>

			</div>
		</td>
	</tr>
	<?php
}


function ejo_base_module_message( $message = 'Instellingen Opgeslagen' )
{
	echo '<div id="message" class="updated notice is-dismissible">';
	echo '<p>'.$message.'</p>';
	echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dit bericht verbergen.</span></button>';
	echo '</div>';
}

