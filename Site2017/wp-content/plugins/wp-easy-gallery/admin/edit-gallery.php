<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

global $wpdb;

$galleryResults = $wpdb->get_results( "SELECT * FROM $wpdb->easyGalleries " );

//Select gallery
if(isset($_POST['select_gallery']) || isset($_POST['galleryId'])) {
	if(check_admin_referer('wpeg_gallery','wpeg_gallery')) {
	  $gid = intval((isset($_POST['select_gallery'])) ? esc_sql($_POST['select_gallery']) : esc_sql($_POST['galleryId']));
	  $imageResults = $wpdb->get_results( "SELECT * FROM $wpdb->easyImages WHERE gid = $gid ORDER BY sortOrder ASC" );
	  $gallery = $wpdb->get_row( "SELECT * FROM $wpdb->easyGalleries WHERE Id = $gid" );
	}
}
	
if(isset($_POST['hcg_edit_gallery']))
{
	if(check_admin_referer('wpeg_gallery','wpeg_gallery')) {
	  if($_POST['galleryName'] != "") {
		$galleryName = sanitize_text_field($_POST['galleryName']);
		$galleryDescription = sanitize_text_field($_POST['galleryDescription']);	  
		$slug = mb_convert_case(str_replace(" ", "", sanitize_text_field($_POST['galleryName'])), MB_CASE_LOWER, "UTF-8");
		$imagepath = sanitize_text_field(str_replace("\\", "", $_POST['upload_image']));
		$thumbwidth = sanitize_text_field($_POST['gallerythumbwidth']);
		$thumbheight = sanitize_text_field($_POST['gallerythumbheight']);
		
		if(isset($_POST['hcg_edit_gallery'])) {
			$imageEdited = $wpdb->update( $wpdb->easyGalleries, array( 'name' => $galleryName, 'slug' => $slug, 'description' => $galleryDescription, 'thumbnail' => $imagepath, 'thumbwidth' => $thumbwidth, 'thumbheight' => $thumbheight ), array( 'Id' => intval($_POST['hcg_edit_gallery']) ) );
				
				?>  
				<div class="updated"><p><strong><?php _e('Gallery has been edited.' ); ?></strong></p></div>  
				<?php
		}
	  }
	}
}
if(isset($_POST['hcg_edit_gallery'])) {
	if(check_admin_referer('wpeg_gallery','wpeg_gallery')) {
	  $gid = intval(esc_sql($_POST['hcg_edit_gallery']));
	  $imageResults = $wpdb->get_results( "SELECT * FROM $wpdb->easyImages WHERE gid = $gid ORDER BY sortOrder ASC" );
	  $gallery = $wpdb->get_row( "SELECT * FROM $wpdb->easyGalleries WHERE Id = $gid" );
	}
}
?>
<div class='wrap wp-easy-gallery-admin'>
	<h2>WP Easy Gallery - Edit Galleries</h2>
    <?php if(!isset($_POST['select_gallery']) && !isset($_POST['galleryId']) && !isset($_POST['hcg_edit_gallery'])) { ?>
    <p>Select a galley</p>	
<table class="widefat post fixed wp-easy-gallery-table" id="galleryResults">
	<thead>
    	<tr>
          <th>Gallery Name</th>
          <th>Description</th>
          <th></th>
          <th></th>
        </tr>
    </thead>
    <tfoot>
    	<tr>
          <th>Gallery Name</th>
          <th>Description</th>
          <th></th>
          <th></th>
        </tr>
    </tfoot>
    <tbody>
    	<?php
			foreach($galleryResults as $gallery) {
				?>
                <tr>
                	<td><?php _e($gallery->name); ?></td>
                    <td><?php _e($gallery->description); ?></td>
                    <td></td>
                    <td>
                    	<form name="select_gallery_form" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
                    	<input type="hidden" name="galleryId" value="<?php _e($gallery->Id); ?>" />
                        <input type="hidden" name="galleryName" value="<?php _e($gallery->name); ?>" />
                        <?php wp_nonce_field('wpeg_gallery','wpeg_gallery'); ?>
                        <input type="submit" name="Submit" class="button-primary" value="Select Gallery" />
                		</form>
                    </td>
                </tr>
		<?php } ?>        
    </tbody>
</table>    
    <?php } else if(isset($_POST['select_gallery']) || isset($_POST['galleryId']) || isset($_POST['hcg_edit_gallery'])) { ?>    
    <h3>Gallery: <?php echo esc_html($gallery->name); ?></h3>
    
    <form name="switch_gallery" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="switch" value="true" />
    <p><input type="submit" name="Submit" class="button-primary" value="Switch Gallery" /></p>
    </form>
	
    <p>This is where you can edit existing galleries.</p>
    <p style="float: right;"><a href="https://www.e-junkie.com/ecom/gb.php?c=single&cl=210569&i=1088504" target="ejejcsingle">Upgrade to WP Easy Gallery Pro</a></p>
    <div style="Clear: both;"></div>
    <form name="hcg_add_gallery_form" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
    <input type="hidden" name="hcg_edit_gallery" value="<?php _e($gid); ?>" />
    <?php wp_nonce_field('wpeg_gallery', 'wpeg_gallery'); ?>
    <table class="widefat post fixed eg-table">
    	<thead>
        <tr>
        	<th class="eg-cell-spacer-250">Field Name</th>
            <th>Entry</th>
            <th>Description</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
        	<th>Field Name</th>
            <th>Entry</th>
            <th>Description</th>
        </tr>
        </tfoot>
        <tbody>
        	<tr>
            	<td><strong>Enter Gallery Name:</strong></td>
                <td><input type="text" size="30" name="galleryName" value="<?php echo esc_attr($gallery->name); ?>" /></td>
                <td>This name is the internal name for the gallery.<br />Please avoid non-letter characters such as ', ", *, etc.</td>
            </tr>
            <tr>
            	<td><strong>Enter Gallery Description:</strong></td>
                <td><input type="text" size="50" name="galleryDescription" value="<?php echo esc_attr($gallery->description) ?>" /></td>
                <td>This description is for internal use.</td>
            </tr>
            <tr>
            	<td><strong>Enter Thumbnail Imagepath:</strong></td>
                <td><input id="upload_image" type="text" size="36" name="upload_image" value="<?php echo esc_attr($gallery->thumbnail); ?>" />
					<input id="upload_image_button" class="button-primary" type="button" value="Upload Image" /></td>
                <td>This is the file path for an optional gallery thumbnail image.  If left blank first gallery image will be thumbnail.</td>
            </tr>
            <tr>
            	<td><strong>Enter Thumbnail Width:</strong></td>
                <td><input type="text" size="10" name="gallerythumbwidth" value="<?php echo esc_attr($gallery->thumbwidth); ?>" /></td>
                <td>This is the width of the gallery thumbnail image.</td>
            </tr>
            <tr>
            	<td><strong>Enter Thumbnail Height:</strong></td>
                <td><input type="text" size="10" name="gallerythumbheight" value="<?php echo esc_attr($gallery->thumbheight); ?>" /></td>
                <td>This is the height of the gallery thumbnail image.</td>
            </tr>
            <tr>
            	<td class="major-publishing-actions"><input type="submit" name="Submit" class="button-primary" value="Save Changes" /></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
	</table>
    </form>
    <?php } ?>
    <br />  
<?php include('includes/banners.php'); ?>
</div>