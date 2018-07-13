<?php
/**
 * Customizer functionality for the Footer credits.
 *
 * @package Hestia
 * @since Hestia 1.0
 */

/**
 * Hook controls for General section to Customizer.
 *
 * @since Hestia 1.0
 * @modified 1.1.34
 */
function hestia_general_footer_customize_register( $wp_customize ) {

	$selective_refresh = isset( $wp_customize->selective_refresh ) ? true : false;

	$wp_customize->add_section(
		'hestia_footer_content', array(
			'title' => esc_html__( 'Footer Options', 'hestia' ),
			'priority' => 50,
		)
	);

	$wp_customize->add_setting(
		'hestia_alternative_footer_style', array(
			'default' => false,
			'sanitize_callback' => 'hestia_sanitize_checkbox',
			'transport' => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'hestia_alternative_footer_style', array(
			'label' => esc_html__( 'Enable Alternative Footer Style?', 'hestia' ),
			'section' => 'hestia_footer_content',
			'priority' => 40,
			'type' => 'checkbox',
		)
	);

}

add_action( 'customize_register', 'hestia_general_footer_customize_register' );


/**
 * Add selective refresh for footer controls.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @since 1.1.34
 * @access public
 */
function hestia_register_footer_partials( $wp_customize ) {
	$wp_customize->selective_refresh->add_partial(
		'hestia_general_credits', array(
			'selector'            => 'footer .hestia-bottom-footer-content .copyright',
			'settings'            => 'hestia_general_credits',
			'render_callback'     => 'hestia_general_credits_callback',
		)
	);

	$wp_customize->selective_refresh->add_partial(
		'hestia_copyright_alignment', array(
			'selector'            => 'footer .hestia-bottom-footer-content',
			'settings'            => 'hestia_copyright_alignment',
			'render_callback'     => 'hestia_copyright_alignment_callback',
		)
	);

}
add_action( 'customize_register', 'hestia_register_footer_partials' );

/**
 * Sanitize alignment control.
 *
 * @since 1.1.34
 * @param string $value Control output.
 * @return string
 */
function hestia_sanitize_alignment_options( $value ) {
	$value = sanitize_text_field( $value );
	$valid_values = array(
		'left',
		'center',
		'right',
	);

	if ( ! in_array( $value, $valid_values ) ) {
		wp_die( 'Invalid value, go back and try again.' );
	}

	return $value;
}

/**
 * Callback function for Copyright control.
 *
 * @return string
 * @since 1.1.34
 */
function hestia_general_credits_callback() {
	return get_theme_mod( 'hestia_general_credits' );
}

/**
 * Callback function for copyright alignment.
 *
 * @since 1.1.34
 */
function hestia_copyright_alignment_callback() {
	hesta_bottom_footer_content( true );
}
