<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

global $wpdb;

if(isset($_POST['galleryId'])) {
	if(check_admin_referer('wpeg_delete_gallery','wpeg_delete_gallery')) {
	  $galleryId = intval($_POST['galleryId']);
	  $wpdb->query( "DELETE FROM $wpdb->easyImages WHERE Id = '".$galleryId."'" );
	  $wpdb->query( "DELETE FROM $wpdb->easyGalleries WHERE Id = '".$galleryId."'" );
		  
	  ?>  
	  <div class="updated"><p><strong><?php _e('Gallery has been deleted.' ); ?></strong></p></div>  
	  <?php
	}
}

$galleryResults = $wpdb->get_results( "SELECT * FROM $wpdb->easyGalleries" );

if (isset($_POST['defaultSettings'])) {
	if(check_admin_referer('wpeg_settings','wpeg_settings')) {
	  $temp_defaults = get_option('wp_easy_gallery_defaults');
	  $temp_defaults['hide_social'] = isset($_POST['hide_social']) ? $_POST['hide_social'] : 'false';
	  	  
	  update_option('wp_easy_gallery_defaults', $temp_defaults);
	  
	  ?>  
	  <div class="updated"><p><strong><?php _e('Options saved.', 'wp-easy-gallery'); ?></strong></p></div>  
	  <?php
	}
}
$default_options = get_option('wp_easy_gallery_defaults');
?>
<div class='wrap wp-easy-gallery-admin'>
	<h2>WP Easy Gallery</h2>
    <p>This is a listing of all galleries.</p>
    <p style="float: right;"><a href="https://www.e-junkie.com/ecom/gb.php?c=single&cl=210569&i=1088504" target="ejejcsingle">Upgrade to WP Easy Gallery Pro</a></p>
    <div style="Clear: both;"></div>
    <table class="widefat post fixed eg-table">
    	<thead>
        <tr>
        	<th>Gallery Name</th>
            <th>Gallery Short Code</th>
            <th>Description</th>
            <th class="eg-cell-spacer-136"></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
        	<th>Gallery Name</th>
            <th>Gallery Short Code</th>
            <th>Description</th>
            <th></th>
        </tr>
        </tfoot>
        <tbody>
        	<?php foreach($galleryResults as $gallery) { ?>				
            <tr>
            	<td><?php echo esc_html($gallery->name); ?></td>
                <td><input type="text" size="30" value="[EasyGallery key='<?php echo esc_attr($gallery->Id); ?>']" /></td>
                <td><?php echo esc_html($gallery->description); ?></td>
                <td class="major-publishing-actions">
                <form name="delete_gallery_<?php echo esc_attr($gallery->Id); ?>" method ="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                	<input type="hidden" name="galleryId" value="<?php echo esc_attr($gallery->Id); ?>" />
                    <?php wp_nonce_field('wpeg_delete_gallery', 'wpeg_delete_gallery'); ?>
                    <input type="submit" name="Submit" class="button-primary" value="Delete Gallery" />
                </form>
                </td>
            </tr>
			<?php } ?>
        </tbody>
     </table>
     <br />
     <h3><?php _e('Default Options', 'wp-easy-gallery'); ?></h3>
     <p>Go to: <a href="?page=wpeg-settings">Settings</a> page.</p>
     <hr />     
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
  }});
});
</script>
</div>