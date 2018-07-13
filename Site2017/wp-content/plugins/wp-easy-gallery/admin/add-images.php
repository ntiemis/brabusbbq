<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

global $wpdb;

$imageResults = null;

$galleryResults = $wpdb->get_results( "SELECT * FROM $wpdb->easyGalleries" );

//Select gallery
if(isset($_POST['select_gallery']) || isset($_POST['galleryId'])) {
	if(check_admin_referer('wpeg_gallery','wpeg_gallery')) {
	  $gid = intval((isset($_POST['select_gallery'])) ? esc_sql($_POST['select_gallery']) : esc_sql($_POST['galleryId']));
	  $imageResults = $wpdb->get_results( "SELECT * FROM $wpdb->easyImages WHERE gid = $gid ORDER BY sortOrder ASC" );
	  $gallery = $wpdb->get_row( "SELECT * FROM $wpdb->easyGalleries WHERE Id = $gid" );
	}
}

//Add image
if(isset($_POST['galleryId_add']) && !isset($_POST['switch'])) {
	if(check_admin_referer('wpeg_gallery','wpeg_gallery')) {
	  $gid = intval(sanitize_text_field($_POST['galleryId_add']));
	  $imagePath = sanitize_text_field($_POST['upload_image']);
	  $imageTitle = sanitize_text_field($_POST['image_title']);
	  $imageDescription = sanitize_text_field($_POST['image_description']);
	  $sortOrder = intval(sanitize_text_field($_POST['image_sortOrder']));
	  $imageAdded = $wpdb->insert( $wpdb->easyImages, array( 'gid' => $gid, 'imagePath' => $imagePath, 'title' => $imageTitle, 'description' => $imageDescription, 'sortOrder' => $sortOrder ) );
	  
	  if($imageAdded) {
	  ?>
		  <div class="updated"><p><strong><?php _e('Image saved.' ); ?></strong></p></div>  
	  <?php }
	  //Reload images
	  $imageResults = $wpdb->get_results( "SELECT * FROM $wpdb->easyImages WHERE gid = $gid ORDER BY sortOrder ASC" );
	  $gallery = $wpdb->get_row( "SELECT * FROM $wpdb->easyGalleries WHERE Id = $gid" );
	}
}

//Edit/Delete Images
if(isset($_POST['editing_images'])) {
	if(check_admin_referer('wpeg_gallery','wpeg_gallery')) {
		$_POST = stripslashes_deep( $_POST );
		
		$editImageIds = array_map('absint', $_POST['edit_imageId']);
		$imagePaths = array_map('sanitize_text_field', $_POST['edit_imagePath']);
		$imageTitles = array_map('sanitize_text_field', $_POST['edit_imageTitle']);
		$imageDescriptions = array_map('sanitize_text_field', $_POST['edit_imageDescription']);
		$sortOrders = array_map('absint', $_POST['edit_imageSort']);
		$imagesToDelete = isset($_POST['edit_imageDelete']) ? array_map('absint', $_POST['edit_imageDelete']) : array();
	
		$i = 0;
		foreach($editImageIds as $editImageId) {
			if(in_array($editImageId, $imagesToDelete)) {
				$wpdb->query( "DELETE FROM $wpdb->easyImages WHERE Id = '".$editImageId."'" );
				echo "Deleted: ".$imageTitles[$i];
			}
			else {
				$imageEdited = $wpdb->update( $wpdb->easyImages, array( 'imagePath' => $imagePaths[$i], 'title' => $imageTitles[$i], 'description' => $imageDescriptions[$i], 'sortOrder' => $sortOrders[$i] ), array( 'Id' => $editImageId ) );
			}		
			$i++;
		}		  
	  ?>  
	  <div class="updated"><p><strong><?php _e('Images have been edited.' ); ?></strong></p></div>  
	  <?php		
	}
}
if(isset($_POST['editing_gid'])) {
	if(check_admin_referer('wpeg_gallery','wpeg_gallery')) {
	  $gid = intval(sanitize_text_field($_POST['editing_gid']));
	  $imageResults = $wpdb->get_results( "SELECT * FROM $wpdb->easyImages WHERE gid = $gid ORDER BY sortOrder ASC" );
	  $gallery = $wpdb->get_row( "SELECT * FROM $wpdb->easyGalleries WHERE Id = $gid" );
	}
}

$styles = get_option('wp_easy_gallery_defaults');
$show_overlay = ($styles['hide_overlay'] == 'true') ? 'false' : 'true';
$show_social = ($styles['hide_social'] == 'true') ? ', show_social: false' : '';
_e("<script>var wpegSettings = {gallery_theme: '".$styles['gallery_theme']."', show_overlay: ".$show_overlay.$show_social."};</script>");

?>

<div class='wrap wp-easy-gallery-admin'>
	<h2>WP Easy Gallery</h2>    
    <p>Add new images to gallery</p>
	<?php if(!isset($_POST['select_gallery']) && !isset($_POST['galleryId']) && !isset($_POST['galleryId_add']) && !isset($_POST['editing_images'])) { ?>
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
    <?php } else if(isset($_POST['select_gallery']) || isset($_POST['galleryId']) || isset($_POST['galleryId_add']) || isset($_POST['editing_images'])) { ?>    
    <h3>Gallery: <?php echo esc_html($gallery->name); ?></h3>
    <form name="switch_gallery" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="switch" value="true" />
    <p style="float: left;"><input type="submit" name="Submit" class="button-primary" value="Switch Gallery" /></p>
    </form>
    <p style="float: right;"><a href="https://www.e-junkie.com/ecom/gb.php?c=single&cl=210569&i=1088504" target="ejejcsingle">Upgrade to WP Easy Gallery Pro</a></p>
    <div style="Clear: both;"></div>
    
    <form name="add_image_form" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
    <input type="hidden" name="galleryId_add" value="<?php _e($gallery->Id); ?>" />
    <?php wp_nonce_field('wpeg_gallery','wpeg_gallery'); ?>
    <table class="widefat post fixed eg-table">
    	<thead>
        <tr>
            <th class="eg-cell-spacer-340">Image Path</th>
            <th class="eg-cell-spacer-150">Image Title</th>
            <th>Image Description</th>
            <th>Sort Order</th>
            <th class="eg-cell-spacer-115"></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th>Image Path</th>
            <th>Image Title</th>
            <th>Image Description</th>
            <th>Sort Order</th>
            <th></th>
        </tr>
        </tfoot>
        <tbody>
        	<tr>
            	<td><input id="upload_image" type="text" size="36" name="upload_image" value="" />
					<input id="upload_image_button" class="button-primary" type="button" value="Upload Image" /></td>
                <td><input type="text" name="image_title" size="36" value="" /></td>
                <td><input type="text" name="image_description" size="36" value="" /></td>
                <td><input type="number" name="image_sortOrder" size="3" value="" /></td>
                <td class="major-publishing-actions"><input type="submit" name="Submit" class="button-primary" value="Add Image" /></td>
            </tr>        	
        </tbody>
     </table>
     </form>
     <?php } ?>
     <?php
	 if(count($imageResults) > 0) {
	 ?>
     <br />
     <hr />
     <p>Edit existing images in this gallery</p>
    <table class="widefat post fixed eg-table">
    	<thead>
        <tr>
        	<th class="eg-cell-spacer-80">Image Preview</th>
            <th class="eg-cell-spacer-700">Image Info</th>
            <th></th>            
        </tr>
        </thead>        
        <tbody>
<form name="edit_image_form" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">	
<input type="hidden" name="editing_gid" value="<?php _e($gallery->Id); ?>" />
<input type="hidden" name="editing_images" value="true" />
<?php wp_nonce_field('wpeg_gallery', 'wpeg_gallery'); ?>	
        	<?php foreach($imageResults as $image) { ?>				
            <tr>
            	<td><a onclick="var images=['<?php echo esc_js($image->imagePath); ?>']; var titles=['<?php echo esc_js($image->title); ?>']; var descriptions=['<?php echo esc_js($image->description); ?>']; jQuery.prettyPhoto.open(images,titles,descriptions);" style="cursor: pointer;"><img src="<?php echo esc_attr($image->imagePath); ?>" width="75" alt="<?php echo esc_attr($image->title); ?>" /></a><br /><i><?php _e('Click to preview', 'wp-easy-gallery-pro'); ?></i></td>
                <td>                	
                	<input type="hidden" name="edit_gId[]" value="<?php echo esc_attr($image->gid); ?>" />
					<input type="hidden" name="edit_imageId[]" value="<?php echo esc_attr($image->Id); ?>" />                                        
                	<p><strong>Image Path:</strong> <input type="text" name="edit_imagePath[]" size="75" value="<?php echo esc_attr($image->imagePath); ?>" /></p>
                    <p><strong>Image Title:</strong> <input type="text" name="edit_imageTitle[]" size="75" value="<?php echo esc_attr($image->title); ?>" /></p>
                    <p><strong>Image Description:</strong> <input type="text" name="edit_imageDescription[]" size="75" value="<?php echo esc_attr($image->description); ?>" /></p>
                    <p><strong>Sort Order:</strong> <input type="number" name="edit_imageSort[]" size="4" value="<?php echo esc_attr($image->sortOrder); ?>" /></p>
					<p><strong>Delete Image?</strong> <input type="checkbox" name="edit_imageDelete[]" value="<?php echo esc_attr($image->Id); ?>" /></p>
                </td>
                <td></td>                
            </tr>
			<?php } ?>
        </tbody>		
     </table>
	 <p class="major-publishing-actions left-float eg-right-margin"><input type="submit" name="Submit" class="button-primary" value="Save Changes" /></p>
     </form>
	 <div style="clear:both;"></div>
     <?php } ?>
     <br />   
<?php include('includes/banners.php'); ?>
</div>