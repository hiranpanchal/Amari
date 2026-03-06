<?php
/**
 * Amari Theme — functions.php
 * Core theme setup, hooks, and builder registration.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AMARI_VERSION',   '2.0.0' );
define( 'AMARI_DIR',       get_template_directory() );
define( 'AMARI_URI',       get_template_directory_uri() );
define( 'AMARI_BUILDER_DIR', AMARI_DIR . '/builder' );

/* ============================================================
   1. THEME SETUP
   ============================================================ */

add_action( 'after_setup_theme', 'amari_setup' );
function amari_setup() {
    load_theme_textdomain( 'amari', AMARI_DIR . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form','comment-form','comment-list','gallery','caption','style','script' ] );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-width'  => true,
        'flex-height' => true,
    ]);

    register_nav_menus([
        'primary'  => __( 'Primary Menu', 'amari' ),
        'footer'   => __( 'Footer Menu', 'amari' ),
    ]);

    set_post_thumbnail_size( 1200, 9999 );
    add_image_size( 'amari-card', 600, 400, true );
    add_image_size( 'amari-wide', 1600, 700, true );
}

/* ============================================================
   2. CONTENT WIDTH
   ============================================================ */

if ( ! isset( $content_width ) ) {
    $content_width = 1200;
}

/* ============================================================
   3. ENQUEUE ASSETS
   ============================================================ */

add_action( 'wp_enqueue_scripts', 'amari_enqueue_scripts' );
function amari_enqueue_scripts() {
    // Google Fonts
    wp_enqueue_style(
        'amari-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap',
        [],
        null
    );

    // Main theme stylesheet
    wp_enqueue_style(
        'amari-style',
        get_stylesheet_uri(),
        [ 'amari-fonts' ],
        AMARI_VERSION
    );

    // Builder frontend styles
    wp_enqueue_style(
        'amari-builder-frontend',
        AMARI_URI . '/builder/css/amari-builder-frontend.css',
        [ 'amari-style' ],
        AMARI_VERSION
    );

    // Theme JS
    wp_enqueue_script(
        'amari-theme',
        AMARI_URI . '/assets/js/amari-theme.js',
        [],
        AMARI_VERSION,
        true
    );
}

/* ============================================================
   4. BUILDER ADMIN ASSETS
   ============================================================ */

add_action( 'admin_enqueue_scripts', 'amari_admin_enqueue' );
function amari_admin_enqueue( $hook ) {
    global $post;

    // Only load builder assets on post/page edit screens
    if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) return;
    if ( ! $post || ! amari_builder_is_enabled( $post->ID ) ) {
        // Still load the "Enable Builder" meta box styles
    }

    wp_enqueue_style(
        'amari-builder-admin',
        AMARI_URI . '/builder/css/amari-builder-admin.css',
        [],
        AMARI_VERSION
    );

    wp_enqueue_media();

    wp_enqueue_script(
        'amari-builder',
        AMARI_URI . '/builder/js/amari-builder.js',
        [ 'jquery', 'wp-util' ],
        AMARI_VERSION,
        true
    );

    wp_localize_script( 'amari-builder', 'AmariBuilderConfig', [
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'amari_builder_nonce' ),
        'postId'    => $post ? $post->ID : 0,
        'themeUri'  => AMARI_URI,
        'elements'  => amari_get_registered_elements_config(),
        'i18n'      => [
            'confirm_delete'   => __( 'Delete this element?', 'amari' ),
            'confirm_section'  => __( 'Delete this section and all its contents?', 'amari' ),
            'unsaved_changes'  => __( 'You have unsaved changes. Leave anyway?', 'amari' ),
            'saving'           => __( 'Saving...', 'amari' ),
            'saved'            => __( 'Saved!', 'amari' ),
            'save_error'       => __( 'Save failed. Please try again.', 'amari' ),
        ],
    ]);
}

/* ============================================================
   5. LOAD BUILDER
   ============================================================ */

require_once AMARI_BUILDER_DIR . '/class-amari-builder.php';
require_once AMARI_BUILDER_DIR . '/class-amari-templates.php';
require_once AMARI_BUILDER_DIR . '/class-amari-frontend-editor.php';
require_once AMARI_DIR         . '/admin/class-amari-global-styles.php';

// Initialize all systems
add_action( 'init', function() {
    AmariBuilder::instance();
    AmariTemplates::instance();
    AmariFrontendEditor::instance();
    AmariGlobalStyles::instance();
});

/* ============================================================
   6. META BOX — ENABLE/DISABLE BUILDER
   ============================================================ */

add_action( 'add_meta_boxes', 'amari_register_meta_boxes' );
function amari_register_meta_boxes() {
    add_meta_box(
        'amari_builder_toggle',
        __( 'Amari Page Builder', 'amari' ),
        'amari_builder_toggle_cb',
        [ 'page', 'post' ],
        'side',
        'high'
    );
}

function amari_builder_toggle_cb( $post ) {
    $enabled = amari_builder_is_enabled( $post->ID );
    wp_nonce_field( 'amari_builder_toggle', 'amari_builder_toggle_nonce' );
    ?>
    <div class="amari-meta-toggle">
        <label class="amari-toggle-label">
            <input type="checkbox" name="amari_builder_enabled" value="1" <?php checked( $enabled ); ?> id="amari_builder_enabled_cb" />
            <span><?php esc_html_e( 'Use Amari Builder for this page', 'amari' ); ?></span>
        </label>
        <?php if ( $enabled ) : ?>
            <a href="#" class="button button-primary" id="amari-launch-builder" style="margin-top:10px;display:block;text-align:center;">
                <?php esc_html_e( '⚡ Open Builder', 'amari' ); ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
}

add_action( 'save_post', 'amari_save_builder_toggle' );
function amari_save_builder_toggle( $post_id ) {
    if ( ! isset( $_POST['amari_builder_toggle_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['amari_builder_toggle_nonce'], 'amari_builder_toggle' ) ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $enabled = isset( $_POST['amari_builder_enabled'] ) ? 1 : 0;
    update_post_meta( $post_id, '_amari_builder_enabled', $enabled );
}

/* ============================================================
   7. AJAX — SAVE BUILDER DATA
   ============================================================ */

add_action( 'wp_ajax_amari_save_builder', 'amari_ajax_save_builder' );
function amari_ajax_save_builder() {
    check_ajax_referer( 'amari_builder_nonce', 'nonce' );

    $post_id = intval( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied.' ] );
    }

    $data = wp_unslash( $_POST['builder_data'] ?? '' );
    $decoded = json_decode( $data, true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        wp_send_json_error( [ 'message' => 'Invalid JSON data.' ] );
    }

    // Sanitize recursively
    $clean = amari_sanitize_builder_data( $decoded );
    update_post_meta( $post_id, '_amari_builder_data', wp_json_encode( $clean ) );
    update_post_meta( $post_id, '_amari_builder_enabled', 1 );

    // Also update the post content for SEO/search indexing
    $content = amari_builder_to_text( $clean );
    wp_update_post([
        'ID'           => $post_id,
        'post_content' => $content,
    ]);

    wp_send_json_success( [ 'message' => 'Saved!', 'post_id' => $post_id ] );
}

/* ============================================================
   8. AJAX — GET BUILDER DATA
   ============================================================ */

add_action( 'wp_ajax_amari_get_builder_data', 'amari_ajax_get_builder_data' );
function amari_ajax_get_builder_data() {
    check_ajax_referer( 'amari_builder_nonce', 'nonce' );

    $post_id = intval( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied.' ] );
    }

    $raw  = get_post_meta( $post_id, '_amari_builder_data', true );
    $data = $raw ? json_decode( $raw, true ) : amari_get_default_builder_data();

    wp_send_json_success( [ 'data' => $data ] );
}

/* ============================================================
   9. HELPER FUNCTIONS
   ============================================================ */

function amari_builder_is_enabled( $post_id ) {
    return (bool) get_post_meta( $post_id, '_amari_builder_enabled', true );
}

function amari_get_builder_data( $post_id ) {
    $raw = get_post_meta( $post_id, '_amari_builder_data', true );
    return $raw ? json_decode( $raw, true ) : null;
}

function amari_get_default_builder_data() {
    return [
        'sections' => [
            [
                'id'       => 'section_' . uniqid(),
                'settings' => [ 'bg_color' => '', 'padding' => '60px 0', 'full_width' => false ],
                'rows'     => [
                    [
                        'id'      => 'row_' . uniqid(),
                        'layout'  => '1-1',
                        'columns' => [
                            [
                                'id'       => 'col_' . uniqid(),
                                'size'     => '1-1',
                                'elements' => [],
                            ]
                        ],
                    ]
                ],
            ]
        ]
    ];
}

function amari_sanitize_builder_data( $data ) {
    if ( ! is_array( $data ) ) return [];
    array_walk_recursive( $data, function( &$value ) {
        if ( is_string( $value ) ) {
            $value = wp_kses_post( $value );
        }
    });
    return $data;
}

function amari_builder_to_text( $data ) {
    $text = '';
    foreach ( $data['sections'] ?? [] as $section ) {
        foreach ( $section['rows'] ?? [] as $row ) {
            foreach ( $row['columns'] ?? [] as $col ) {
                foreach ( $col['elements'] ?? [] as $el ) {
                    $s = $el['settings'] ?? [];
                    if ( ! empty( $s['text'] ) )    $text .= wp_strip_all_tags( $s['text'] )    . "\n";
                    if ( ! empty( $s['content'] ) ) $text .= wp_strip_all_tags( $s['content'] ) . "\n";
                    if ( ! empty( $s['label'] ) )   $text .= wp_strip_all_tags( $s['label'] )   . "\n";
                }
            }
        }
    }
    return trim( $text );
}

function amari_get_registered_elements_config() {
    return AmariBuilder::instance()->get_elements_config();
}

/* ============================================================
   10. CUSTOM POST TYPES — PORTFOLIO
   ============================================================ */

add_action( 'init', 'amari_register_post_types' );
function amari_register_post_types() {
    register_post_type( 'amari_portfolio', [
        'labels' => [
            'name'          => __( 'Portfolio', 'amari' ),
            'singular_name' => __( 'Portfolio Item', 'amari' ),
            'add_new_item'  => __( 'Add New Portfolio Item', 'amari' ),
        ],
        'public'       => true,
        'has_archive'  => true,
        'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'menu_icon'    => 'dashicons-portfolio',
        'rewrite'      => [ 'slug' => 'portfolio' ],
        'show_in_rest' => true,
    ]);

    register_taxonomy( 'amari_portfolio_cat', 'amari_portfolio', [
        'labels'       => [ 'name' => __( 'Portfolio Categories', 'amari' ) ],
        'hierarchical' => true,
        'public'       => true,
        'rewrite'      => [ 'slug' => 'portfolio-category' ],
        'show_in_rest' => true,
    ]);
}

/* ============================================================
   11. INLINE ADMIN STYLES (small meta box tweaks)
   ============================================================ */

add_action( 'admin_head', 'amari_admin_head_styles' );
function amari_admin_head_styles() {
    ?>
    <style>
    .amari-meta-toggle { padding: 4px 0; }
    .amari-toggle-label { display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:500; }
    .amari-toggle-label input { width:16px; height:16px; cursor:pointer; }
    #amari-launch-builder { background: #e94560 !important; border-color: #e94560 !important; }
    </style>
    <?php
}
