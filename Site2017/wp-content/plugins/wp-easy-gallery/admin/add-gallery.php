<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

$galleryName = '';
$galleryDescription = '';	  
$slug = '';
$imagepath = '';
$thumbwidth = '';
$thumbheight = '';

$galleryAdded = false;
	
	if(isset($_POST['hcg_add_gallery']))
	{
		if(check_admin_referer('wpeg_add_gallery','wpeg_add_gallery')) {
		  if($_POST['galleryName'] != "") {
			$galleryName = sanitize_text_field($_POST['galleryName']);
			$galleryDescription = sanitize_text_field($_POST['galleryDescription']);	  
			$slug = mb_convert_case(str_replace(" ", "", sanitize_text_field($_POST['galleryName'])), MB_CASE_LOWER, "UTF-8");
			$imagepath = sanitize_text_field(str_replace("\\", "", $_POST['upload_image']));
			$thumbwidth = sanitize_text_field($_POST['gallerythumbwidth']);
			$thumbheight = sanitize_text_field($_POST['gallerythumbheight']);
			
			global $wpdb;
			global $easy_gallery_table;
			
			$gallery = $wpdb->get_row( "SELECT * FROM $wpdb->easyGalleries WHERE slug = '".$slug."'" );
			
			if (count($gallery) > 0) {
				$slug = $slug."-".count($gallery);	
			}
			
			$galleryAdded = $wpdb->insert( $wpdb->easyGalleries, array( 'name' => $galleryName, 'slug' => $slug, 'description' => $galleryDescription, 'thumbnail' => $imagepath, 'thumbwidth' => $thumbwidth, 'thumbheight' => $thumbheight ) );
			
			$galleryNew = $wpdb->get_row( "SELECT * FROM $wpdb->easyGalleries WHERE slug = '".$slug."'" );
			
			if($galleryAdded) {
			?>  
			<div class="updated"><p><strong><?php _e('Gallery Added.' ); ?></strong></p></div>  
			<?php
			}
		  }
		  else {
			  ?>  
			<div class="updated"><p><strong><?php _e('Please enter a gallery name.' ); ?></strong></p></div>  
			<?php
		  }
		}
	}
?>
<div class='wrap wp-easy-gallery-admin'>
	<h2>WP Easy Gallery - Add Galleries</h2>
    <?php
	if($galleryAdded) {
	?>
    <div class="updated"><p>Copy and paste this code into the page or post that you would like to display the gallery.</p>
    <p><input type="text" name="galleryCode" value="[EasyGallery id='<?php echo esc_html($galleryNew->Id); ?>']" size="40" /></p></div>
    <?php }
	else {
	?>
    <p>This is where you can create new galleries. Once the new gallery has been added, a short code will be provided for use in posts.</p>
    <?php } ?>
	<p style="float: right;"><a href="https://www.e-junkie.com/ecom/gb.php?c=single&cl=210569&i=1088504" target="ejejcsingle">Upgrade to WP Easy Gallery Pro</a></p>
    <div style="Clear: both;"></div>
    <form name="hcg_add_gallery_form" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
    <input type="hidden" name="hcg_add_gallery" value="true" />
    <?php wp_nonce_field('wpeg_add_gallery','wpeg_add_gallery'); ?>
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
                <td><input type="text" size="30" name="galleryName" value="<?php echo esc_attr($galleryName); ?>" /></td>
                <td>This name is the internal name for the gallery.<br />Please avoid non-letter characters such as ', ", *, etc.</td>
            </tr>
            <tr>
            	<td><strong>Enter Gallery Description:</strong></td>
                <td><input type="text" size="50" name="galleryDescription" value="<?php echo esc_attr($galleryDescription); ?>" /></td>
                <td>This description is for internal use.</td>
            </tr>
            <tr>
            	<td><strong>Enter Thumbnail Imagepath:</strong></td>
                <td><input id="upload_image" type="text" size="36" name="upload_image" value="<?php echo esc_attr($imagepath); ?>" />
					<input id="upload_image_button" class="button-primary" type="button" value="Upload Image" /></td>
                <td>This is the file path for an optional gallery thumbnail image.  If left blank first gallery image will be thumbnail.</td>
            </tr>
            <tr>
            	<td><strong>Enter Thumbnail Width:</strong></td>
                <td><input type="text" size="10" name="gallerythumbwidth" value="<?php echo esc_attr($thumbwidth); ?>" /></td>
                <td>This is the width of the gallery thumbnail image.</td>
            </tr>
            <tr>
            	<td><strong>Enter Thumbnail Height:</strong></td>
                <td><input type="text" size="10" name="gallerythumbheight" value="<?php echo esc_attr($thumbheight); ?>" /></td>
                <td>This is the height of the gallery thumbnail image.</td>
            </tr>
            <tr>
            	<td class="major-publishing-actions"><input type="submit" name="Submit" class="button-primary" value="Add Gallery" /></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
	</table>
    </form>
<br />   
<?php include('includes/banners.php'); ?>
</div>