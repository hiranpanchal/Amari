<?php
/**
 * Amari Theme Customiser Panel
 *
 * A fast, Appearance → Customiser-style settings page that covers:
 *   - Colour palette (primary, accent, text, background, etc.)
 *   - Typography (heading font, body font, base size, line height)
 *   - Spacing (section padding, container max-width)
 *   - Borders & radius
 *   - Buttons (radius, padding, font weight)
 *
 * All values output as CSS custom properties via wp_head (priority 4)
 * so they override the defaults set in style.css.
 *
 * Settings stored in WordPress option `amari_customiser`.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AmariCustomiser {

    /** @var self|null */
    private static $instance = null;

    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu',                       [ $this, 'add_menu_page' ] );
        add_action( 'admin_post_amari_save_customiser', [ $this, 'handle_save' ] );
        add_action( 'wp_head',                          [ $this, 'output_css_vars' ], 4 );
        add_action( 'admin_head',                       [ $this, 'output_css_vars' ], 4 );
    }

    /* ──────────────────────────────────────────────────────────
       Defaults
       ────────────────────────────────────────────────────────── */

    public function get_defaults(): array {
        return [
            // Colours
            'color_primary'     => '#e94560',
            'color_secondary'   => '#6366f1',
            'color_text'        => '#1a1a2e',
            'color_text_light'  => '#6b7280',
            'color_bg'          => '#ffffff',
            'color_bg_alt'      => '#f8f9fa',
            'color_border'      => '#e5e7eb',
            'color_heading'     => '#0f0f1a',

            // Typography
            'font_heading'      => 'Inter',
            'font_body'         => 'Inter',
            'font_size_base'    => '16',
            'line_height'       => '1.6',
            'font_weight_heading' => '700',

            // Spacing
            'section_padding'   => '80px',
            'container_width'   => '1200px',
            'gap_elements'      => '24px',

            // Shape
            'radius_sm'         => '6px',
            'radius_md'         => '12px',
            'radius_lg'         => '24px',
            'radius_pill'       => '999px',

            // Buttons
            'btn_radius'        => '6px',
            'btn_padding'       => '12px 28px',
            'btn_font_weight'   => '600',
            'btn_font_size'     => '14px',

            // Shadows
            'shadow_sm'         => '0 1px 4px rgba(0,0,0,0.06)',
            'shadow_md'         => '0 4px 16px rgba(0,0,0,0.10)',
            'shadow_lg'         => '0 12px 40px rgba(0,0,0,0.14)',
        ];
    }

    public function get_settings(): array {
        $saved = get_option( 'amari_customiser', [] );
        return array_merge( $this->get_defaults(), is_array($saved) ? $saved : [] );
    }

    /* ──────────────────────────────────────────────────────────
       Output CSS custom properties
       ────────────────────────────────────────────────────────── */

    public function output_css_vars(): void {
        $s = $this->get_settings();
        ?>
        <style id="amari-customiser-vars">
        :root {
            /* Colours */
            --amari-primary:       <?php echo esc_attr( $s['color_primary'] ); ?>;
            --amari-secondary:     <?php echo esc_attr( $s['color_secondary'] ); ?>;
            --amari-text:          <?php echo esc_attr( $s['color_text'] ); ?>;
            --amari-text-light:    <?php echo esc_attr( $s['color_text_light'] ); ?>;
            --amari-bg:            <?php echo esc_attr( $s['color_bg'] ); ?>;
            --amari-bg-alt:        <?php echo esc_attr( $s['color_bg_alt'] ); ?>;
            --amari-border:        <?php echo esc_attr( $s['color_border'] ); ?>;
            --amari-heading-color: <?php echo esc_attr( $s['color_heading'] ); ?>;

            /* Typography */
            --amari-font-heading:     '<?php echo esc_attr( $s['font_heading'] ); ?>', sans-serif;
            --amari-font-body:        '<?php echo esc_attr( $s['font_body'] ); ?>', sans-serif;
            --amari-font-size:        <?php echo absint( $s['font_size_base'] ); ?>px;
            --amari-line-height:      <?php echo esc_attr( $s['line_height'] ); ?>;
            --amari-heading-weight:   <?php echo esc_attr( $s['font_weight_heading'] ); ?>;

            /* Spacing */
            --amari-section-padding:  <?php echo esc_attr( $s['section_padding'] ); ?>;
            --amari-container-width:  <?php echo esc_attr( $s['container_width'] ); ?>;
            --amari-gap:              <?php echo esc_attr( $s['gap_elements'] ); ?>;

            /* Shape */
            --amari-radius-sm:    <?php echo esc_attr( $s['radius_sm'] ); ?>;
            --amari-radius:       <?php echo esc_attr( $s['radius_md'] ); ?>;
            --amari-radius-lg:    <?php echo esc_attr( $s['radius_lg'] ); ?>;
            --amari-radius-pill:  <?php echo esc_attr( $s['radius_pill'] ); ?>;

            /* Buttons */
            --amari-btn-radius:   <?php echo esc_attr( $s['btn_radius'] ); ?>;
            --amari-btn-padding:  <?php echo esc_attr( $s['btn_padding'] ); ?>;
            --amari-btn-weight:   <?php echo esc_attr( $s['btn_font_weight'] ); ?>;
            --amari-btn-size:     <?php echo esc_attr( $s['btn_font_size'] ); ?>;

            /* Shadows */
            --amari-shadow-sm:    <?php echo esc_attr( $s['shadow_sm'] ); ?>;
            --amari-shadow:       <?php echo esc_attr( $s['shadow_md'] ); ?>;
            --amari-shadow-lg:    <?php echo esc_attr( $s['shadow_lg'] ); ?>;
        }
        </style>
        <?php

        // Enqueue Google Fonts if needed
        $fonts = array_unique( array_filter( [ $s['font_heading'], $s['font_body'] ] ) );
        $font_families = array_map( fn($f) => urlencode( $f ) . ':wght@300;400;500;600;700;800', $fonts );
        if ( ! is_admin() && $font_families ) {
            $url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $font_families ) . '&display=swap';
            printf( '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="stylesheet" href="%s">', esc_url($url) );
        }
    }

    /* ──────────────────────────────────────────────────────────
       Save
       ────────────────────────────────────────────────────────── */

    public function handle_save(): void {
        check_admin_referer( 'amari_save_customiser' );
        if ( ! current_user_can( 'edit_theme_options' ) ) wp_die( 'Forbidden' );

        $p = $_POST;
        $color_keys = [ 'color_primary','color_secondary','color_text','color_text_light','color_bg','color_bg_alt','color_border','color_heading','cta_bg','cta_text' ];
        $text_keys  = [ 'font_heading','font_body','section_padding','container_width','gap_elements','radius_sm','radius_md','radius_lg','radius_pill','btn_radius','btn_padding','btn_font_size','shadow_sm','shadow_md','shadow_lg','line_height' ];
        $int_keys   = [ 'font_size_base' ];
        $sel_keys   = [ 'font_weight_heading','btn_font_weight' ];

        $settings = [];
        foreach ( $color_keys as $k ) {
            $settings[$k] = sanitize_hex_color( $p[$k] ?? '' ) ?: $this->get_defaults()[$k];
        }
        foreach ( $text_keys as $k ) {
            $settings[$k] = sanitize_text_field( $p[$k] ?? '' ) ?: $this->get_defaults()[$k];
        }
        foreach ( $int_keys as $k ) {
            $settings[$k] = absint( $p[$k] ?? 16 );
        }
        foreach ( $sel_keys as $k ) {
            $settings[$k] = sanitize_text_field( $p[$k] ?? '700' );
        }

        update_option( 'amari_customiser', $settings );
        wp_redirect( admin_url( 'themes.php?page=amari-customiser&saved=1' ) );
        exit;
    }

    /* ──────────────────────────────────────────────────────────
       Admin Page
       ────────────────────────────────────────────────────────── */

    public function add_menu_page(): void {
        add_theme_page(
            __( 'Theme Customiser', 'amari' ),
            __( 'Customiser', 'amari' ),
            'edit_theme_options',
            'amari-customiser',
            [ $this, 'render_page' ]
        );
    }

    public function render_page(): void {
        $s     = $this->get_settings();
        $saved = isset( $_GET['saved'] );

        $font_options = [
            'Inter','Roboto','Open Sans','Lato','Poppins','Montserrat','Raleway',
            'Playfair Display','Merriweather','Source Serif 4','Nunito','DM Sans','Plus Jakarta Sans',
        ];
        ?>
        <div class="wrap amari-cust-wrap">
            <h1><span style="color:#e94560;">Amari</span> Theme Customiser</h1>

            <?php if ( $saved ) : ?>
                <div class="notice notice-success is-dismissible"><p>Customiser settings saved!</p></div>
            <?php endif; ?>

            <div class="amari-cust-layout">

                <!-- FORM -->
                <div class="amari-cust-form-col">
                    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                        <?php wp_nonce_field( 'amari_save_customiser' ); ?>
                        <input type="hidden" name="action" value="amari_save_customiser">

                        <!-- Colours -->
                        <div class="amari-cust-card">
                            <h3>Colour Palette</h3>
                            <?php
                            $colours = [
                                'color_primary'    => 'Primary / Accent',
                                'color_secondary'  => 'Secondary / Highlight',
                                'color_text'       => 'Body Text',
                                'color_text_light' => 'Muted Text',
                                'color_bg'         => 'Page Background',
                                'color_bg_alt'     => 'Alternate Background',
                                'color_border'     => 'Border Colour',
                                'color_heading'    => 'Heading Colour',
                            ];
                            foreach ( $colours as $key => $label ) : ?>
                                <div class="amari-cust-row amari-cust-color-row">
                                    <label><?php echo esc_html($label); ?></label>
                                    <div class="amari-cust-color-pair">
                                        <input type="color" name="<?php echo esc_attr($key); ?>"
                                               value="<?php echo esc_attr($s[$key]); ?>"
                                               oninput="amariCustPreview(this,'<?php echo esc_js($key); ?>')">
                                        <input type="text" class="amari-cust-color-text"
                                               value="<?php echo esc_attr($s[$key]); ?>"
                                               oninput="this.previousElementSibling.value=this.value;amariCustPreview(this.previousElementSibling,'<?php echo esc_js($key); ?>')">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Typography -->
                        <div class="amari-cust-card">
                            <h3>Typography</h3>

                            <div class="amari-cust-row">
                                <label>Heading Font</label>
                                <select name="font_heading" onchange="amariCustPreview(this,'font_heading')">
                                    <?php foreach ($font_options as $f) : ?>
                                        <option value="<?php echo esc_attr($f); ?>" <?php selected($s['font_heading'],$f); ?>><?php echo esc_html($f); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="amari-cust-row">
                                <label>Body Font</label>
                                <select name="font_body" onchange="amariCustPreview(this,'font_body')">
                                    <?php foreach ($font_options as $f) : ?>
                                        <option value="<?php echo esc_attr($f); ?>" <?php selected($s['font_body'],$f); ?>><?php echo esc_html($f); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="amari-cust-row-2col">
                                <div class="amari-cust-row">
                                    <label>Base Font Size (px)</label>
                                    <input type="number" name="font_size_base" value="<?php echo esc_attr($s['font_size_base']); ?>" min="12" max="24">
                                </div>
                                <div class="amari-cust-row">
                                    <label>Line Height</label>
                                    <input type="number" name="line_height" value="<?php echo esc_attr($s['line_height']); ?>" min="1" max="3" step="0.1">
                                </div>
                            </div>

                            <div class="amari-cust-row">
                                <label>Heading Font Weight</label>
                                <select name="font_weight_heading">
                                    <?php foreach (['400','500','600','700','800'] as $w) : ?>
                                        <option value="<?php echo $w; ?>" <?php selected($s['font_weight_heading'],$w); ?>><?php echo $w; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Spacing -->
                        <div class="amari-cust-card">
                            <h3>Spacing &amp; Layout</h3>

                            <div class="amari-cust-row">
                                <label>Section Padding (top &amp; bottom)</label>
                                <input type="text" name="section_padding" value="<?php echo esc_attr($s['section_padding']); ?>" placeholder="80px">
                            </div>

                            <div class="amari-cust-row">
                                <label>Container Max Width</label>
                                <input type="text" name="container_width" value="<?php echo esc_attr($s['container_width']); ?>" placeholder="1200px">
                            </div>

                            <div class="amari-cust-row">
                                <label>Element Gap</label>
                                <input type="text" name="gap_elements" value="<?php echo esc_attr($s['gap_elements']); ?>" placeholder="24px">
                            </div>
                        </div>

                        <!-- Borders & Radius -->
                        <div class="amari-cust-card">
                            <h3>Borders &amp; Radius</h3>

                            <div class="amari-cust-row-2col">
                                <?php
                                $radii = [ 'radius_sm'=>'Small','radius_md'=>'Medium','radius_lg'=>'Large','radius_pill'=>'Pill' ];
                                foreach ($radii as $k => $lbl) : ?>
                                    <div class="amari-cust-row">
                                        <label><?php echo esc_html($lbl); ?></label>
                                        <input type="text" name="<?php echo esc_attr($k); ?>" value="<?php echo esc_attr($s[$k]); ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="amari-cust-card">
                            <h3>Buttons</h3>

                            <div class="amari-cust-row-2col">
                                <div class="amari-cust-row">
                                    <label>Border Radius</label>
                                    <input type="text" name="btn_radius" value="<?php echo esc_attr($s['btn_radius']); ?>">
                                </div>
                                <div class="amari-cust-row">
                                    <label>Font Size</label>
                                    <input type="text" name="btn_font_size" value="<?php echo esc_attr($s['btn_font_size']); ?>">
                                </div>
                            </div>

                            <div class="amari-cust-row">
                                <label>Padding</label>
                                <input type="text" name="btn_padding" value="<?php echo esc_attr($s['btn_padding']); ?>" placeholder="12px 28px">
                            </div>

                            <div class="amari-cust-row">
                                <label>Font Weight</label>
                                <select name="btn_font_weight">
                                    <?php foreach (['400','500','600','700','800'] as $w) : ?>
                                        <option value="<?php echo $w; ?>" <?php selected($s['btn_font_weight'],$w); ?>><?php echo $w; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div style="margin-top:20px;">
                            <button type="submit" class="button button-primary button-large">Save Customiser Settings</button>
                        </div>
                    </form>
                </div>

                <!-- LIVE PREVIEW -->
                <div class="amari-cust-preview-col">
                    <div class="amari-nav-preview-label">Live Preview</div>
                    <div class="amari-cust-preview" id="amari-cust-preview"
                         style="
                            --p: <?php echo esc_attr($s['color_primary']); ?>;
                            --s: <?php echo esc_attr($s['color_secondary']); ?>;
                            --t: <?php echo esc_attr($s['color_text']); ?>;
                            --h: <?php echo esc_attr($s['color_heading']); ?>;
                            --bg: <?php echo esc_attr($s['color_bg']); ?>;
                            --bg2: <?php echo esc_attr($s['color_bg_alt']); ?>;
                            --br: <?php echo esc_attr($s['radius_md']); ?>;
                            --brd: <?php echo esc_attr($s['color_border']); ?>;
                            font-family: '<?php echo esc_attr($s['font_body']); ?>',sans-serif;
                            font-size: <?php echo absint($s['font_size_base']); ?>px;
                            color: var(--t);
                            background: var(--bg);
                        ">

                        <div style="padding:24px;background:var(--bg);">
                            <h2 style="font-family:'<?php echo esc_attr($s['font_heading']); ?>',sans-serif;color:var(--h);font-weight:<?php echo esc_attr($s['font_weight_heading']); ?>;margin:0 0 8px;">
                                Sample Heading
                            </h2>
                            <p style="color:var(--t);line-height:<?php echo esc_attr($s['line_height']); ?>;margin:0 0 20px;">
                                This is how your body text will look with the selected font and colour settings. Adjust the controls on the left to see changes here in real time.
                            </p>
                            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;">
                                <a href="#" style="display:inline-block;background:var(--p);color:#fff;padding:<?php echo esc_attr($s['btn_padding']); ?>;border-radius:<?php echo esc_attr($s['btn_radius']); ?>;font-weight:<?php echo esc_attr($s['btn_font_weight']); ?>;font-size:<?php echo esc_attr($s['btn_font_size']); ?>;text-decoration:none;">
                                    Primary Button
                                </a>
                                <a href="#" style="display:inline-block;background:var(--s);color:#fff;padding:<?php echo esc_attr($s['btn_padding']); ?>;border-radius:<?php echo esc_attr($s['btn_radius']); ?>;font-weight:<?php echo esc_attr($s['btn_font_weight']); ?>;font-size:<?php echo esc_attr($s['btn_font_size']); ?>;text-decoration:none;">
                                    Secondary Button
                                </a>
                                <a href="#" style="display:inline-block;background:none;border:2px solid var(--p);color:var(--p);padding:<?php echo esc_attr($s['btn_padding']); ?>;border-radius:<?php echo esc_attr($s['btn_radius']); ?>;font-weight:<?php echo esc_attr($s['btn_font_weight']); ?>;font-size:<?php echo esc_attr($s['btn_font_size']); ?>;text-decoration:none;">
                                    Ghost Button
                                </a>
                            </div>
                        </div>

                        <div style="background:var(--bg2);padding:20px;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);">
                            <h3 style="font-family:'<?php echo esc_attr($s['font_heading']); ?>',sans-serif;color:var(--h);font-weight:<?php echo esc_attr($s['font_weight_heading']); ?>;margin:0 0 12px;font-size:1rem;">
                                Card Example
                            </h3>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                                <?php for($i=0;$i<3;$i++): ?>
                                <div style="background:var(--bg);border:1px solid var(--brd);border-radius:var(--br);padding:14px;">
                                    <div style="width:36px;height:36px;border-radius:8px;background:var(--p);opacity:0.15;margin-bottom:10px;"></div>
                                    <strong style="font-size:12px;color:var(--h);">Feature Title</strong>
                                    <p style="font-size:11px;color:#888;margin:4px 0 0;">Short description text.</p>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div style="padding:20px;text-align:center;background:var(--p);">
                            <span style="color:#fff;font-size:12px;font-weight:600;">CTA Section — Primary Colour Background</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <style>
        .amari-cust-wrap { max-width: 1300px; }
        .amari-cust-layout { display:grid; grid-template-columns:460px 1fr; gap:32px; align-items:start; margin-top:20px; }
        .amari-cust-card { background:#fff; border:1px solid #e2e6ea; border-radius:10px; padding:20px; margin-bottom:14px; }
        .amari-cust-card h3 { margin:0 0 16px; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.5px; color:#e94560; font-weight:700; }
        .amari-cust-row { margin-bottom:12px; }
        .amari-cust-row label { display:block; font-size:11px; font-weight:600; color:#6b7280; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.4px; }
        .amari-cust-row input[type="text"],
        .amari-cust-row input[type="number"],
        .amari-cust-row select { width:100%; padding:8px 10px; border:1px solid #e2e6ea; border-radius:6px; font-size:13px; box-sizing:border-box; }
        .amari-cust-row-2col { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .amari-cust-color-row { display:flex; align-items:center; justify-content:space-between; }
        .amari-cust-color-row label { margin-bottom:0; flex:1; }
        .amari-cust-color-pair { display:flex; align-items:center; gap:8px; }
        .amari-cust-color-pair input[type="color"] { width:36px; height:36px; padding:2px; border-radius:6px; border:1px solid #e2e6ea; cursor:pointer; flex-shrink:0; }
        .amari-cust-color-text { width:88px !important; font-size:12px !important; }
        .amari-cust-preview-col { position:sticky; top:32px; }
        .amari-cust-preview { border:1px solid #e2e6ea; border-radius:10px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); }
        </style>

        <script>
        function amariCustPreview(el, key) {
            const val   = el.value;
            const prev  = document.getElementById('amari-cust-preview');
            if (!prev) return;

            // Sync colour text input
            const pair = el.closest('.amari-cust-color-pair');
            if (pair) {
                const txt   = pair.querySelector('.amari-cust-color-text');
                const color = pair.querySelector('input[type="color"]');
                if (txt  && el !== txt)   txt.value   = val;
                if (color && el !== color) color.value = val;
            }

            const map = {
                color_primary:   '--p', color_secondary: '--s',
                color_text:      '--t', color_heading:   '--h',
                color_bg:        '--bg', color_bg_alt:   '--bg2',
                color_border:    '--brd',
            };
            if (map[key]) prev.style.setProperty(map[key], val);
        }
        </script>
        <?php
    }
}
