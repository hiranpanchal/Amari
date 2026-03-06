<?php
/**
 * Amari Mega Menu — V3
 *
 * Extends WordPress nav menus with mega-menu support:
 *   • Per-menu-item meta fields: enable mega menu, column count, column width
 *   • Custom nav walker that outputs mega-menu markup
 *   • Admin column settings panel (injected into the nav-menu screen)
 *   • Frontend JS/CSS loaded via hooks
 *
 * @package Amari
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AmariMegaMenu {

    /* ------------------------------------------------------------------
       Singleton
    ------------------------------------------------------------------ */

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Admin: inject mega-menu fields into the nav-menu item edit panel
        add_filter( 'wp_setup_nav_menu_item',        [ $this, 'setup_nav_menu_item' ] );
        add_action( 'wp_nav_menu_item_custom_fields', [ $this, 'render_item_fields' ], 10, 4 );
        add_action( 'wp_update_nav_menu_item',        [ $this, 'save_item_fields' ], 10, 3 );
        add_action( 'admin_enqueue_scripts',          [ $this, 'admin_assets' ] );

        // Frontend: use our walker + load CSS/JS
        add_filter( 'wp_nav_menu_args',               [ $this, 'swap_walker' ] );
        add_action( 'wp_enqueue_scripts',             [ $this, 'frontend_assets' ] );
        add_action( 'wp_head',                        [ $this, 'output_dynamic_css' ], 20 );
    }

    /* ------------------------------------------------------------------
       ADMIN: enqueue the nav-menu-screen JS
    ------------------------------------------------------------------ */

    public function admin_assets( string $hook ): void {
        if ( 'nav-menus.php' !== $hook ) return;
        wp_enqueue_style(
            'amari-mega-menu-admin',
            AMARI_URI . '/admin/css/amari-mega-menu-admin.css',
            [],
            AMARI_VERSION
        );
        wp_enqueue_script(
            'amari-mega-menu-admin',
            AMARI_URI . '/admin/js/amari-mega-menu-admin.js',
            [ 'jquery', 'nav-menu' ],
            AMARI_VERSION,
            true
        );
    }

    /* ------------------------------------------------------------------
       ADMIN: attach saved mega-menu settings to menu item object
    ------------------------------------------------------------------ */

    public function setup_nav_menu_item( object $item ): object {
        if ( is_nav_menu_item( $item ) ) {
            $item->amari_mega          = get_post_meta( $item->ID, '_amari_mega_enabled',  true ) ?: '0';
            $item->amari_mega_cols     = get_post_meta( $item->ID, '_amari_mega_cols',     true ) ?: '4';
            $item->amari_mega_width    = get_post_meta( $item->ID, '_amari_mega_width',    true ) ?: 'full';
            $item->amari_mega_heading  = get_post_meta( $item->ID, '_amari_mega_heading',  true ) ?: '1';
            $item->amari_mega_dividers = get_post_meta( $item->ID, '_amari_mega_dividers', true ) ?: '0';
        }
        return $item;
    }

    /* ------------------------------------------------------------------
       ADMIN: render the custom fields panel in the menu item edit box
    ------------------------------------------------------------------ */

    public function render_item_fields( int $item_id, object $item, int $depth, object $args ): void {
        // Only show mega-menu controls on top-level items (depth 0)
        if ( 0 !== $depth ) return;
        wp_nonce_field( 'amari_mega_menu_nonce_' . $item_id, 'amari_mega_nonce_' . $item_id );
        $enabled  = ( '1' === $item->amari_mega );
        $cols     = esc_attr( $item->amari_mega_cols );
        $width    = esc_attr( $item->amari_mega_width );
        $headings = ( '1' === $item->amari_mega_heading );
        $dividers = ( '1' === $item->amari_mega_dividers );
        ?>
        <div class="amari-mega-fields field-amari_mega description description-wide" style="margin-top:12px;border-top:1px solid #ddd;padding-top:12px;">
            <span class="amari-mega-toggle-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                <input type="checkbox" name="amari_mega_enabled[<?php echo $item_id; ?>]"
                       id="amari_mega_enabled_<?php echo $item_id; ?>"
                       value="1" <?php checked( $enabled ); ?>
                       class="amari-mega-checkbox" />
                <label for="amari_mega_enabled_<?php echo $item_id; ?>" style="font-weight:600;color:#e94560;cursor:pointer;">
                    ⚡ Enable Mega Menu
                </label>
            </span>

            <div class="amari-mega-options" style="<?php echo $enabled ? '' : 'display:none;'; ?>background:#f9f9f9;border:1px solid #e2e6ea;border-radius:6px;padding:12px;margin-top:8px;">

                <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#555;">
                    Columns
                    <select name="amari_mega_cols[<?php echo $item_id; ?>]" style="margin-left:8px;">
                        <?php foreach ( [ '2', '3', '4', '5', '6' ] as $n ) : ?>
                            <option value="<?php echo $n; ?>" <?php selected( $cols, $n ); ?>><?php echo $n; ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#555;">
                    Dropdown Width
                    <select name="amari_mega_width[<?php echo $item_id; ?>]" style="margin-left:8px;">
                        <option value="full"      <?php selected( $width, 'full' ); ?>>Full Width (100vw)</option>
                        <option value="container" <?php selected( $width, 'container' ); ?>>Container Width</option>
                        <option value="auto"      <?php selected( $width, 'auto' ); ?>>Auto</option>
                    </select>
                </label>

                <label style="display:flex;align-items:center;gap:6px;margin-bottom:6px;font-size:12px;font-weight:600;color:#555;cursor:pointer;">
                    <input type="checkbox" name="amari_mega_heading[<?php echo $item_id; ?>]"
                           value="1" <?php checked( $headings ); ?> />
                    Show column headings
                </label>

                <label style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#555;cursor:pointer;">
                    <input type="checkbox" name="amari_mega_dividers[<?php echo $item_id; ?>]"
                           value="1" <?php checked( $dividers ); ?> />
                    Show column dividers
                </label>

                <p style="margin-top:10px;font-size:11px;color:#999;line-height:1.5;">
                    Add child items to this menu item — they will be distributed into columns automatically.
                    Drag to reorder. Use the "Description" field on child items for column headings.
                </p>
            </div>
        </div>
        <?php
    }

    /* ------------------------------------------------------------------
       ADMIN: save meta fields
    ------------------------------------------------------------------ */

    public function save_item_fields( int $menu_id, int $menu_item_db_id, array $args ): void {
        if ( ! isset( $_POST[ 'amari_mega_nonce_' . $menu_item_db_id ] )
             || ! wp_verify_nonce( $_POST[ 'amari_mega_nonce_' . $menu_item_db_id ], 'amari_mega_menu_nonce_' . $menu_item_db_id ) ) {
            return;
        }

        $fields = [
            '_amari_mega_enabled'  => 'amari_mega_enabled',
            '_amari_mega_cols'     => 'amari_mega_cols',
            '_amari_mega_width'    => 'amari_mega_width',
            '_amari_mega_heading'  => 'amari_mega_heading',
            '_amari_mega_dividers' => 'amari_mega_dividers',
        ];

        foreach ( $fields as $meta_key => $post_key ) {
            $raw = $_POST[ $post_key ][ $menu_item_db_id ] ?? '';
            update_post_meta( $menu_item_db_id, $meta_key, sanitize_text_field( $raw ) );
        }
    }

    /* ------------------------------------------------------------------
       FRONTEND: swap in our custom walker for primary menu
    ------------------------------------------------------------------ */

    public function swap_walker( array $args ): array {
        if ( isset( $args['theme_location'] ) && 'primary' === $args['theme_location'] ) {
            $args['walker']      = new Amari_Mega_Menu_Walker();
            $args['menu_class'] = ( $args['menu_class'] ?? '' ) . ' amari-nav-menu amari-has-mega';
        }
        return $args;
    }

    /* ------------------------------------------------------------------
       FRONTEND: enqueue mega-menu CSS + JS
    ------------------------------------------------------------------ */

    public function frontend_assets(): void {
        wp_enqueue_style(
            'amari-mega-menu',
            AMARI_URI . '/assets/css/amari-mega-menu.css',
            [ 'amari-style' ],
            AMARI_VERSION
        );
        wp_enqueue_script(
            'amari-mega-menu',
            AMARI_URI . '/assets/js/amari-mega-menu.js',
            [],
            AMARI_VERSION,
            true
        );
    }

    /* ------------------------------------------------------------------
       FRONTEND: output per-item dynamic column overrides
    ------------------------------------------------------------------ */

    public function output_dynamic_css(): void {
        $locations = get_nav_menu_locations();
        if ( empty( $locations['primary'] ) ) return;
        $menu = wp_get_nav_menu_items( $locations['primary'] );
        if ( ! $menu ) return;

        $css = '';
        foreach ( $menu as $item ) {
            if ( '1' !== get_post_meta( $item->ID, '_amari_mega_enabled', true ) ) continue;
            $cols  = (int) ( get_post_meta( $item->ID, '_amari_mega_cols', true ) ?: 4 );
            $width = get_post_meta( $item->ID, '_amari_mega_width', true ) ?: 'full';
            $css .= sprintf(
                '.amari-nav-menu > li.menu-item-%d > .amari-mega-dropdown { --mega-cols: %d; }',
                $item->ID,
                $cols
            );
            if ( 'full' === $width ) {
                $css .= sprintf(
                    '.amari-nav-menu > li.menu-item-%d > .amari-mega-dropdown { width: 100vw; left: 50%%; transform: translateX(-50%%); }',
                    $item->ID
                );
            } elseif ( 'container' === $width ) {
                $css .= sprintf(
                    '.amari-nav-menu > li.menu-item-%d > .amari-mega-dropdown { width: var(--container-width, 1200px); left: 50%%; transform: translateX(-50%%); }',
                    $item->ID
                );
            }
        }

        if ( $css ) {
            echo '<style id="amari-mega-dynamic">' . $css . '</style>' . "\n";
        }
    }
}

/* ======================================================================
   CUSTOM WALKER
   ====================================================================== */

class Amari_Mega_Menu_Walker extends Walker_Nav_Menu {

    /* Track which top-level items are mega-menu-enabled */
    private array $mega_items = [];
    /* Track if we are inside a mega dropdown */
    private bool $in_mega = false;
    /* Accumulated child items grouped by heading */
    private array $mega_children = [];
    /* Current top-level item being rendered */
    private ?object $current_top = null;

    /* ------------------------------------------------------------------
       start_lvl — opens a <ul class="sub-menu"> OR a mega dropdown
    ------------------------------------------------------------------ */

    public function start_lvl( &$output, $depth = 0, $args = null ) {
        if ( 0 === $depth && $this->in_mega ) {
            // We'll buffer children and output the mega grid later in end_lvl
            return;
        }
        $indent  = str_repeat( "\t", $depth );
        $output .= "\n{$indent}<ul class=\"sub-menu\">\n";
    }

    /* ------------------------------------------------------------------
       end_lvl — closes the sub-menu OR flushes the mega grid
    ------------------------------------------------------------------ */

    public function end_lvl( &$output, $depth = 0, $args = null ) {
        if ( 0 === $depth && $this->in_mega && $this->current_top ) {
            $output .= $this->render_mega_grid( $this->current_top );
            $this->mega_children = [];
            $this->in_mega       = false;
            $this->current_top   = null;
            return;
        }
        $indent  = str_repeat( "\t", $depth );
        $output .= "{$indent}</ul>\n";
    }

    /* ------------------------------------------------------------------
       start_el — top-level item wrapper OR buffered child item
    ------------------------------------------------------------------ */

    public function start_el( &$output, $data_object, $depth = 0, $args = null, $id = 0 ) {
        $item = $data_object;

        // Determine if this top-level item has mega enabled
        if ( 0 === $depth ) {
            $is_mega = ( '1' === get_post_meta( $item->ID, '_amari_mega_enabled', true ) );
            if ( $is_mega ) {
                $this->in_mega     = true;
                $this->current_top = $item;
                $this->mega_children = [];
            } else {
                $this->in_mega = false;
            }
        }

        // If we're inside a mega and this is a child item, buffer it
        if ( $this->in_mega && $depth > 0 ) {
            $this->mega_children[] = $item;
            $output .= ''; // nothing yet
            return;
        }

        // Normal item rendering
        $indent    = str_repeat( "\t", $depth );
        $classes   = empty( $item->classes ) ? [] : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        if ( $this->in_mega ) {
            $classes[] = 'amari-has-mega';
        }

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
        $id          = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
        $id          = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names . '>';

        $atts           = [];
        $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
        $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
        $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
        $atts['href']   = ! empty( $item->url )        ? $item->url        : '';
        if ( $this->in_mega && 0 === $depth ) {
            $atts['aria-haspopup'] = 'true';
            $atts['aria-expanded'] = 'false';
        }
        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( is_scalar( $value ) && '' !== $value ) {
                $attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
            }
        }

        $title        = apply_filters( 'the_title', $item->title, $item->ID );
        $title        = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );
        $item_output  = $args->before ?? '';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= ( $args->link_before ?? '' ) . $title . ( $args->link_after ?? '' );
        if ( $this->in_mega && 0 === $depth ) {
            $item_output .= '<svg class="amari-mega-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>';
        }
        $item_output .= '</a>';
        $item_output .= $args->after ?? '';

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }

    /* ------------------------------------------------------------------
       end_el — close <li> for non-buffered items
    ------------------------------------------------------------------ */

    public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
        // If buffering child items, don't emit anything
        if ( $this->in_mega && $depth > 0 ) return;
        $output .= "</li>\n";
    }

    /* ------------------------------------------------------------------
       render_mega_grid — outputs the mega dropdown HTML
    ------------------------------------------------------------------ */

    private function render_mega_grid( object $top_item ): string {
        $cols     = (int) ( get_post_meta( $top_item->ID, '_amari_mega_cols',     true ) ?: 4 );
        $headings = ( '1' === get_post_meta( $top_item->ID, '_amari_mega_heading',  true ) );
        $dividers = ( '1' === get_post_meta( $top_item->ID, '_amari_mega_dividers', true ) );

        if ( empty( $this->mega_children ) ) return '';

        // Separate direct children (depth 1) from grandchildren (depth 2+)
        // WordPress walker flattens everything — we use parent_id to reconstruct
        $by_parent = [];
        foreach ( $this->mega_children as $child ) {
            $by_parent[ $child->menu_item_parent ][] = $child;
        }

        // Top-level children of the mega item
        $top_children = $by_parent[ $top_item->ID ] ?? [];

        $html  = "\n\t\t<div class=\"amari-mega-dropdown" . ( $dividers ? ' amari-mega-dividers' : '' ) . "\"";
        $html .= ' style="--mega-cols:' . $cols . ';">';
        $html .= "\n\t\t\t<div class=\"amari-mega-inner\">";

        foreach ( $top_children as $col_item ) {
            $html .= "\n\t\t\t\t<div class=\"amari-mega-col\">";

            // Column heading
            if ( $headings ) {
                $heading_text = ! empty( $col_item->description ) ? $col_item->description : $col_item->title;
                $html .= "\n\t\t\t\t\t<div class=\"amari-mega-col-heading\">";
                if ( ! empty( $col_item->url ) && '#' !== $col_item->url ) {
                    $html .= '<a href="' . esc_url( $col_item->url ) . '">' . esc_html( $heading_text ) . '</a>';
                } else {
                    $html .= esc_html( $heading_text );
                }
                $html .= '</div>';
            }

            // Sub-items under this column
            $sub_items = $by_parent[ $col_item->ID ] ?? [];
            if ( $sub_items ) {
                $html .= "\n\t\t\t\t\t<ul class=\"amari-mega-col-list\">";
                foreach ( $sub_items as $sub ) {
                    $html .= "\n\t\t\t\t\t\t<li class=\"menu-item menu-item-" . esc_attr( $sub->ID ) . "\">";
                    $html .= '<a href="' . esc_url( $sub->url ) . '"';
                    if ( $sub->target ) $html .= ' target="' . esc_attr( $sub->target ) . '"';
                    $html .= '>' . esc_html( $sub->title ) . '</a>';
                    $html .= '</li>';
                }
                $html .= "\n\t\t\t\t\t</ul>";
            }

            $html .= "\n\t\t\t\t</div><!-- .amari-mega-col -->";
        }

        $html .= "\n\t\t\t</div><!-- .amari-mega-inner -->";
        $html .= "\n\t\t</div><!-- .amari-mega-dropdown -->";

        return $html;
    }
}
