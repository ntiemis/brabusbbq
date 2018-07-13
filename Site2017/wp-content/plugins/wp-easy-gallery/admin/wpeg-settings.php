<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

if (isset($_POST['defaultSettings'])) {
	if(check_admin_referer('wpeg_settings','wpeg_settings')) {
	  $temp_defaults = get_option('wp_easy_gallery_defaults');	
	  $temp_defaults['show_gallery_name'] = isset($_POST['show_gallery_name']) ? sanitize_text_field($_POST['show_gallery_name']) : 'false';
	  $temp_defaults['gallery_name_alignment'] = isset($_POST['gallery_name_alignment']) ? sanitize_text_field($_POST['gallery_name_alignment']) : 'left';
	  $temp_defaults['hide_overlay'] = isset($_POST['hide_overlay']) ? sanitize_text_field($_POST['hide_overlay']) : 'false';
	  $temp_defaults['hide_social'] = isset($_POST['hide_social']) ? sanitize_text_field($_POST['hide_social']) : 'false';
	  $temp_defaults['use_default_style'] = isset($_POST['use_default_style']) ? sanitize_text_field($_POST['use_default_style']) : 'false';
	  $temp_defaults['custom_style'] = isset($_POST['custom_style']) ? sanitize_text_field($_POST['custom_style']) : '';
	  $temp_defaults['drop_shadow'] = isset($_POST['drop_shadow']) ? sanitize_text_field($_POST['drop_shadow']) : 'false';
	  $temp_defaults['display_mode'] = isset($_POST['display_mode']) ? sanitize_text_field($_POST['display_mode']) : 'wp_easy_gallery';
	  $temp_defaults['num_columns'] = isset($_POST['num_columns']) ? intval(sanitize_text_field($_POST['num_columns']) ): 3;
	  
	  update_option('wp_easy_gallery_defaults', $temp_defaults);
	  
	  ?>  
	  <div class="updated"><p><strong>Options saved.</strong></p></div>  
	  <?php
	}
}
$default_options = get_option('wp_easy_gallery_defaults');

?>
<div class='wrap wp-easy-gallery-admin'>
	<h2>WP Easy Gallery</h2>
    <p style="width: 50%; float: left;">This is a listing of all galleries.</p>
    <p style="float: right;"><a href="http://labs.hahncreativegroup.com/wordpress-plugins/wp-easy-gallery-pro-simple-wordpress-gallery-plugin/?src=wpeg" target="_blank"><strong><em>Try WP Easy Gallery Pro</em></strong></a></p>
    <div style="Clear: both;"></div>
    <form name="save_default_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <?php wp_nonce_field('wpeg_settings','wpeg_settings'); ?>
    <table class="widefat post fixed eg-table">
    	<thead>
        <tr>
        	<th>Property or Attribute</th>
            <th>Value</th>
            <th>Description</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
        	<th>Property or Attribute</th>
            <th>Value</th>
            <th>Description</th>
        </tr>
        </tfoot>
        <tbody>
			<tr>            	
            	<td>Display Mode</td>
                <td>
					<select id="display_mode" name="display_mode">
						<option value="wp_easy_gallery"<?php echo esc_attr(($default_options['display_mode'] == 'wp_easy_gallery') ? " selected" : ""); ?>>WP Easy Gallery</option>
						<option value="wp_default"<?php echo esc_attr(($default_options['display_mode'] == 'wp_default') ? " selected" : ""); ?>>WordPress Default</option>
					</select>
				</td>
                <td>Set the display mode for WP Easy Gallery</td>            
            </tr>
			<tr id="num_columns_wrap" style="display: none;">
            	<td>Number of Columns</td>
                <td><input type="number" name="num_columns" id="num_columns" value="<?php echo esc_attr($default_options['num_columns']); ?>" /></td>
                <td>This is the number of columns per row (for default WordPress gallery view)</td>
            </tr>
			<tr>            	
            	<td>Show Gallery Name</td>
                <td><input type="checkbox" name="show_gallery_name" id="show_gallery_name"<?php echo esc_attr(($default_options['show_gallery_name'] == 'true') ? "checked='checked'" : ""); ?> value="true" /></td>
                <td>Show or Hide gallery name.</td>            
            </tr>
			<tr>            	
            	<td>Gallery Name Alignment</td>
                <td>
					<select id="gallery_name_alignment" name="gallery_name_alignment">
						<option value="left"<?php echo esc_attr(($default_options['gallery_name_alignment'] == 'left') ? " selected" : ""); ?>>Left</option>
						<option value="center"<?php echo esc_attr(($default_options['gallery_name_alignment'] == 'center') ? " selected" : ""); ?>>Center</option>
						<option value="right"<?php echo esc_attr(($default_options['gallery_name_alignment'] == 'right') ? " selected" : ""); ?>>Right</option>
					</select>
				</td>
                <td>Set the text alignment of the gallery name display.</td>            
            </tr>
            <tr class="wp_easy_gallery_settings_wrap">            	
            	<td>Hide Gallery Overlay</td>
                <td><input type="checkbox" name="hide_overlay" id="hide_overlay"<?php echo esc_attr(($default_options['hide_overlay'] == 'true') ? "checked='checked'" : ""); ?> value="true" /></td>
                <td>Show or Hide thumbnail gallery overlay in modal window popup. Check to hide the overlay.</td>            
            </tr>
            <tr class="wp_easy_gallery_settings_wrap">            	
            	<td>Hide Gallery Social Buttons</td>
                <td><input type="checkbox" name="hide_social" id="hide_social"<?php echo esc_attr(($default_options['hide_social'] == 'true') ? "checked='checked'" : ""); ?> value="true" /></td>
                <td>Show or Hide the social sharing buttons in modal window popup. Check to hide the social sharing buttons.</td>            
            </tr>
            <tr class="wp_easy_gallery_settings_wrap">            	
            	<td>Use Default Thumbnail Theme</td>
                <td><input type="checkbox" name="use_default_style" id="use_default_style"<?php echo esc_attr(($default_options['use_default_style'] == 'true') ? "checked='checked'" : ""); ?> value="true" /></td>
                <td>Use default thumbnail style (uncheck to disable new thumbnail CSS).</td>            
            </tr>
			<tr class="wp_easy_gallery_settings_wrap">            	
            	<td>Thumbnail Dropshadow</td>
                <td><input type="checkbox" name="drop_shadow" id="drop_shadow"<?php echo esc_attr(($default_options['drop_shadow'] == 'true') ? "checked='checked'" : ""); ?> value="true" /></td>
                <td>Use default thumbnail dropshadow (uncheck to disable dropshadow CSS).</td>            
            </tr>
            <tr class="wp_easy_gallery_settings_wrap">
            	<td>Custom Thumbnail Style</td>
                <td><textarea name="custom_style" id="custom_style" rows="4" cols="40"><?php echo esc_html( $default_options['custom_style'] ); ?></textarea></td>
                <td>This is where you would add custom styles for the gallery thumbnails.<br />(ex: border: solid 1px #cccccc; padding: 2px; margin-right: 10px;)</td>
            </tr>
            <tr>
            	<td>                
                	<input type="hidden" name="defaultSettings" value="true" />
                    <input type="submit" name="Submit" class="button-primary" value="Save" />                
                </td>
                <td></td>
                <td></td>
            </tr>			
        </tbody>
     </table>
     <br />
<div style="float: left; width: 60%; min-width: 488px;">     
<?php include('includes/banners.php'); ?>
</div>
<div id="rss" style="float: right; width: 25%; height: 700px; padding: 10px; min-width: 165px;">
</div>
<script type="text/javascript">
jQuery(document).ready(function(){			
	jQuery.ajax({url: "<?php echo plugins_url( '/rss.php', __FILE__ ); ?>",success:function(result){
		jQuery("#rss").html(result);
		
			if (jQuery('#display_mode').val() == "wp_default") {
				//show WordPress Gallery Options
				jQuery('#num_columns_wrap').show();
				jQuery('.wp_easy_gallery_settings_wrap').hide();
			}
			else if (jQuery('#display_mode').val() == "wp_easy_gallery") {
				//show WP Easy Gallery Options
				//jQuery('.wp_easy_gallery_settings_wrap').show();
			}
			jQuery('#display_mode').on('change', function() {
				if (jQuery('#display_mode').val() == "wp_default") {
					jQuery('#num_columns_wrap').show();
					jQuery('.wp_easy_gallery_settings_wrap').hide();
				} else {
					jQuery('#num_columns_wrap').hide();
					jQuery('.wp_easy_gallery_settings_wrap').show();
				}
			});
  }});
});
</script>
</div>