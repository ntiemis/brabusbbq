/*
// WP Easy Gallery
// https://labs.hahncreativegroup.com/wordpress-gallery-plugin/
*/

jQuery(document).ready(function(){			
	jQuery(".gallery a[rel^='prettyPhoto']").prettyPhoto({counter_separator_label:' of ', theme:wpegSettings.gallery_theme, overlay_gallery:wpegSettings.show_overlay, social_tools:wpegSettings.show_social});
	
});