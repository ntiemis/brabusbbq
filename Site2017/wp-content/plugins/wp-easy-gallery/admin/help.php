<div class='wrap wp-easy-gallery-admin'>
	<h2>WP Easy Gallery - Help (FAQ)</h2>
    <p><em><strong>How do I use WP Easy Gallery?</strong></em></p>
    <p>Here is video tutorial on setting up and using WP Easy Gallery: <a href="http://labs.hahncreativegroup.com/2012/07/creating-wordpress-image-galleries-with-wp-easy-gallery/" target="_blank">Creating WordPress Image Galleries with WP Easy Gallery</a></p>
    <p><em><strong>What is the best way to change the theme styles for Easy Gallery Pro, I see that there are different themes in the CSS file (Dark Square, Facebook, etc.)?</strong></em></p>
<p>Through the plugin editor in the admin panel, select this file &#8216;<strong>wp-easy-gallery-pro/js/EasyGalleryLoader.js</strong>&#8216;. This is the code you should see:</p>
<p><code>jQuery(document).ready(function(){<br />
jQuery(".gallery a[rel^='prettyPhoto']").prettyPhoto({counter_separator_label:' of ',theme:'light_rounded',overlay_gallery:true});<br />
});</code></p>
<p>You can change the theme by changing the highlighted parameter above in the file and saving. The list of possible themes are: <em>dark_rounded, dark_square, facebook, light_rounded (default), light_square</em>. Theme names correspond to the folders in the &#8216;images\prettyPhoto&#8217; directory.</p>
<br />
<?php include('includes/banners.php'); ?>
</div>