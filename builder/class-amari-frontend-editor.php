<?php
/**
 * Amari Frontend Editor
 *
 * Loads the full builder overlay on the live frontend for logged-in editors,
 * allowing pages to be edited directly without visiting wp-admin.
 *
 * How it works:
 *  1. A floating "Edit" bar is shown at the bottom of every page for editors.
 *  2. Clicking "Edit with Amari" triggers JS to open the builder overlay.
 *  3. The builder reads/writes the same post meta (_amari_builder_data).
 *  4. On save, the overlay closes and the page reloads to show updated content.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariFrontendEditor {

    /** @var AmariFrontendEditor|null */
    private static $instance = null;

    public static function instance(): AmariFrontendEditor {
        if ( ! self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue_editor' ], 20 );
        add_action( 'wp_footer',          [ $this, 'maybe_render_edit_bar' ] );
        add_action( 'wp_footer',          [ $this, 'maybe_render_builder_overlay' ] );
    }

    private function is_editable(): bool {
        return is_singular()
            && current_user_can( 'edit_post', get_the_ID() )
            && amari_builder_is_enabled( get_the_ID() );
    }

    /* ── Enqueue builder assets on frontend ─────────────────── */

    public function maybe_enqueue_editor(): void {
        if ( ! $this->is_editable() ) return;

        wp_enqueue_style(
            'amari-builder-admin',
            AMARI_URI . '/builder/css/amari-builder-admin.css',
            [],
            AMARI_VERSION
        );
        wp_enqueue_style(
            'amari-frontend-editor',
            AMARI_URI . '/builder/css/amari-frontend-editor.css',
            [ 'amari-builder-admin' ],
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

        wp_enqueue_script(
            'amari-frontend-editor',
            AMARI_URI . '/builder/js/amari-frontend-editor.js',
            [ 'amari-builder' ],
            AMARI_VERSION,
            true
        );

        wp_localize_script( 'amari-builder', 'AmariBuilderConfig', [
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'amari_builder_nonce' ),
            'postId'   => get_the_ID(),
            'themeUri' => AMARI_URI,
            'elements' => AmariBuilder::instance()->get_elements_config(),
            'i18n'     => [
                'confirm_delete'  => __( 'Delete this element?', 'amari' ),
                'confirm_section' => __( 'Delete this section?', 'amari' ),
                'unsaved_changes' => __( 'You have unsaved changes. Leave anyway?', 'amari' ),
                'saving'          => __( 'Saving...', 'amari' ),
                'saved'           => __( 'Saved!', 'amari' ),
                'save_error'      => __( 'Save failed. Please try again.', 'amari' ),
            ],
            'templates' => AmariTemplates::instance()->get_template_index(),
            'isFrontend' => true,
        ]);
    }

    /* ── Floating edit bar ──────────────────────────────────── */

    public function maybe_render_edit_bar(): void {
        if ( ! $this->is_editable() ) return;
        $edit_url = get_edit_post_link( get_the_ID() );
        ?>
        <div id="amari-fe-bar" class="amari-fe-bar" role="toolbar" aria-label="<?php esc_attr_e( 'Amari Editor Controls', 'amari' ); ?>">
            <div class="amari-fe-bar-inner">
                <span class="amari-fe-logo">⚡ Amari</span>
                <div class="amari-fe-bar-actions">
                    <span class="amari-fe-page-name"><?php echo esc_html( get_the_title() ); ?></span>
                    <button class="amari-fe-btn amari-fe-btn-primary" id="amari-fe-open">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit with Amari
                    </button>
                    <a href="<?php echo esc_url( $edit_url ); ?>" class="amari-fe-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                        WP Admin
                    </a>
                    <button class="amari-fe-btn amari-fe-btn-dismiss" id="amari-fe-dismiss" aria-label="Dismiss bar">✕</button>
                </div>
            </div>
        </div>
        <div style="height:52px;"></div><!-- spacer so bar doesn't overlap content -->
        <?php
    }

    /* ── Builder overlay (re-uses admin template) ───────────── */

    public function maybe_render_builder_overlay(): void {
        if ( ! $this->is_editable() ) return;
        // Output the same overlay used in the admin — JS wires it up
        include AMARI_BUILDER_DIR . '/templates/builder-ui.php';
        include AMARI_BUILDER_DIR . '/templates/builder-templates-modal.php';
    }
}
