<?php
/**
 * Amari Navigation Builder
 *
 * Provides a visual editor for the site header:
 *   - Logo (image or text)
 *   - Navigation links with optional mega menu flag
 *   - Header CTA button
 *   - Header style (solid / transparent / sticky)
 *   - Mobile menu behaviour
 *
 * Settings stored in WordPress option `amari_nav_settings`.
 * Accessible via Appearance → Navigation.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AmariNavBuilder {

    private static ?AmariNavBuilder $instance = null;

    public static function instance(): AmariNavBuilder {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu',              [ $this, 'add_menu_page' ] );
        add_action( 'admin_post_amari_save_nav', [ $this, 'handle_save' ] );
        add_action( 'wp_head',                 [ $this, 'output_nav_css' ], 10 );
        add_action( 'wp_ajax_amari_get_nav_settings', [ $this, 'ajax_get_settings' ] );
    }

    /* ──────────────────────────────────────────────────────────
       Defaults
       ────────────────────────────────────────────────────────── */

    public function get_defaults(): array {
        return [
            // Logo
            'logo_type'        => 'text',        // 'text' | 'image'
            'logo_text'        => get_bloginfo('name'),
            'logo_image'       => '',
            'logo_width'       => '160',

            // Header style
            'header_style'     => 'solid',        // 'solid' | 'transparent' | 'sticky'
            'header_bg'        => '#ffffff',
            'header_text'      => '#1a1a2e',
            'header_height'    => '70',

            // CTA button
            'cta_show'         => true,
            'cta_label'        => 'Get Started',
            'cta_url'          => '#',
            'cta_bg'           => '#e94560',
            'cta_text'         => '#ffffff',

            // Mobile menu
            'mobile_breakpoint'=> '768',
            'mobile_menu_bg'   => '#ffffff',
        ];
    }

    public function get_settings(): array {
        $saved = get_option( 'amari_nav_settings', [] );
        return array_merge( $this->get_defaults(), is_array($saved) ? $saved : [] );
    }

    /* ──────────────────────────────────────────────────────────
       Admin Menu
       ────────────────────────────────────────────────────────── */

    public function add_menu_page(): void {
        add_theme_page(
            __( 'Navigation Builder', 'amari' ),
            __( 'Navigation', 'amari' ),
            'edit_theme_options',
            'amari-navigation',
            [ $this, 'render_page' ]
        );
    }

    /* ──────────────────────────────────────────────────────────
       Save
       ────────────────────────────────────────────────────────── */

    public function handle_save(): void {
        check_admin_referer( 'amari_save_nav' );
        if ( ! current_user_can( 'edit_theme_options' ) ) wp_die( 'Forbidden' );

        $post = $_POST;

        $settings = [
            'logo_type'         => sanitize_text_field( $post['logo_type']         ?? 'text' ),
            'logo_text'         => sanitize_text_field( $post['logo_text']         ?? '' ),
            'logo_image'        => esc_url_raw( $post['logo_image']                ?? '' ),
            'logo_width'        => absint( $post['logo_width']                     ?? 160 ),
            'header_style'      => sanitize_text_field( $post['header_style']      ?? 'solid' ),
            'header_bg'         => sanitize_hex_color( $post['header_bg']          ?? '#ffffff' ),
            'header_text'       => sanitize_hex_color( $post['header_text']        ?? '#1a1a2e' ),
            'header_height'     => absint( $post['header_height']                  ?? 70 ),
            'cta_show'          => ! empty( $post['cta_show'] ),
            'cta_label'         => sanitize_text_field( $post['cta_label']         ?? '' ),
            'cta_url'           => esc_url_raw( $post['cta_url']                   ?? '#' ),
            'cta_bg'            => sanitize_hex_color( $post['cta_bg']             ?? '#e94560' ),
            'cta_text'          => sanitize_hex_color( $post['cta_text']           ?? '#ffffff' ),
            'mobile_breakpoint' => absint( $post['mobile_breakpoint']              ?? 768 ),
            'mobile_menu_bg'    => sanitize_hex_color( $post['mobile_menu_bg']     ?? '#ffffff' ),
        ];

        update_option( 'amari_nav_settings', $settings );

        wp_redirect( admin_url( 'themes.php?page=amari-navigation&saved=1' ) );
        exit;
    }

    /* ──────────────────────────────────────────────────────────
       Output header CSS overrides
       ────────────────────────────────────────────────────────── */

    public function output_nav_css(): void {
        $s = $this->get_settings();
        ?>
        <style id="amari-nav-styles">
        .amari-header {
            background-color: <?php echo esc_attr( $s['header_bg'] ); ?>;
            min-height: <?php echo absint( $s['header_height'] ); ?>px;
            <?php if ( $s['header_style'] === 'transparent' ) : ?>
            background-color: transparent !important;
            position: absolute;
            width: 100%;
            z-index: 100;
            <?php elseif ( $s['header_style'] === 'sticky' ) : ?>
            position: sticky;
            top: 0;
            z-index: 9000;
            <?php endif; ?>
        }
        .amari-header .amari-logo,
        .amari-header .amari-nav-link {
            color: <?php echo esc_attr( $s['header_text'] ); ?>;
        }
        .amari-logo-text {
            font-size: 1.4rem;
            font-weight: 800;
            color: <?php echo esc_attr( $s['header_text'] ); ?>;
            text-decoration: none;
        }
        .amari-logo-img {
            width: <?php echo absint( $s['logo_width'] ); ?>px;
            height: auto;
        }
        .amari-header-cta {
            background-color: <?php echo esc_attr( $s['cta_bg'] ); ?> !important;
            color: <?php echo esc_attr( $s['cta_text'] ); ?> !important;
            border-color: <?php echo esc_attr( $s['cta_bg'] ); ?> !important;
        }
        @media (max-width: <?php echo absint( $s['mobile_breakpoint'] ); ?>px) {
            .amari-nav-mobile-menu {
                background: <?php echo esc_attr( $s['mobile_menu_bg'] ); ?>;
            }
        }
        </style>
        <?php
    }

    /* ──────────────────────────────────────────────────────────
       AJAX: get settings for live preview
       ────────────────────────────────────────────────────────── */

    public function ajax_get_settings(): void {
        check_ajax_referer( 'amari_builder_nonce', 'nonce' );
        wp_send_json_success( $this->get_settings() );
    }

    /* ──────────────────────────────────────────────────────────
       Admin Page
       ────────────────────────────────────────────────────────── */

    public function render_page(): void {
        $s    = $this->get_settings();
        $saved = isset( $_GET['saved'] );
        ?>
        <div class="wrap amari-nav-wrap">
            <h1>
                <span style="color:#e94560;font-size:1.1em;">Amari</span>
                Navigation Builder
            </h1>

            <?php if ( $saved ) : ?>
                <div class="notice notice-success is-dismissible"><p>Navigation settings saved!</p></div>
            <?php endif; ?>

            <div class="amari-nav-layout">

                <!-- ── FORM ── -->
                <div class="amari-nav-form-col">
                    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                        <?php wp_nonce_field( 'amari_save_nav' ); ?>
                        <input type="hidden" name="action" value="amari_save_nav">

                        <!-- LOGO -->
                        <div class="amari-nav-card">
                            <h3>Logo</h3>

                            <div class="amari-nav-row">
                                <label>Logo Type</label>
                                <select name="logo_type" id="amari-logo-type" onchange="amariNavPreview()">
                                    <option value="text"  <?php selected($s['logo_type'],'text'); ?>>Text</option>
                                    <option value="image" <?php selected($s['logo_type'],'image'); ?>>Image</option>
                                </select>
                            </div>

                            <div class="amari-nav-row" id="amari-logo-text-row" <?php echo $s['logo_type']==='image' ? 'style="display:none"' : ''; ?>>
                                <label>Logo Text</label>
                                <input type="text" name="logo_text" value="<?php echo esc_attr($s['logo_text']); ?>" oninput="amariNavPreview()">
                            </div>

                            <div class="amari-nav-row" id="amari-logo-image-row" <?php echo $s['logo_type']==='text' ? 'style="display:none"' : ''; ?>>
                                <label>Logo Image URL</label>
                                <div style="display:flex;gap:8px;">
                                    <input type="text" name="logo_image" id="amari-logo-image" value="<?php echo esc_attr($s['logo_image']); ?>" oninput="amariNavPreview()">
                                    <button type="button" class="button" onclick="amariPickLogo()">Choose</button>
                                </div>
                                <?php if ($s['logo_image']) : ?>
                                    <img src="<?php echo esc_url($s['logo_image']); ?>" style="max-height:50px;margin-top:8px;border-radius:4px;">
                                <?php endif; ?>
                            </div>

                            <div class="amari-nav-row">
                                <label>Logo Width (px) — for image logos</label>
                                <input type="number" name="logo_width" value="<?php echo esc_attr($s['logo_width']); ?>" min="40" max="400" oninput="amariNavPreview()">
                            </div>
                        </div>

                        <!-- HEADER STYLE -->
                        <div class="amari-nav-card">
                            <h3>Header Style</h3>

                            <div class="amari-nav-row">
                                <label>Style</label>
                                <select name="header_style" oninput="amariNavPreview()">
                                    <option value="solid"       <?php selected($s['header_style'],'solid'); ?>>Solid (always visible)</option>
                                    <option value="transparent" <?php selected($s['header_style'],'transparent'); ?>>Transparent (over hero)</option>
                                    <option value="sticky"      <?php selected($s['header_style'],'sticky'); ?>>Sticky (follows scroll)</option>
                                </select>
                            </div>

                            <div class="amari-nav-row amari-nav-row-2col">
                                <div>
                                    <label>Background Colour</label>
                                    <input type="color" name="header_bg" value="<?php echo esc_attr($s['header_bg']); ?>" oninput="amariNavPreview()">
                                </div>
                                <div>
                                    <label>Text / Link Colour</label>
                                    <input type="color" name="header_text" value="<?php echo esc_attr($s['header_text']); ?>" oninput="amariNavPreview()">
                                </div>
                            </div>

                            <div class="amari-nav-row">
                                <label>Header Height (px)</label>
                                <input type="number" name="header_height" value="<?php echo esc_attr($s['header_height']); ?>" min="40" max="160" oninput="amariNavPreview()">
                            </div>
                        </div>

                        <!-- CTA BUTTON -->
                        <div class="amari-nav-card">
                            <h3>Header CTA Button</h3>

                            <div class="amari-nav-row">
                                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                                    <input type="checkbox" name="cta_show" value="1" <?php checked($s['cta_show']); ?> onchange="amariNavPreview()">
                                    Show CTA button
                                </label>
                            </div>

                            <div class="amari-nav-row">
                                <label>Button Label</label>
                                <input type="text" name="cta_label" value="<?php echo esc_attr($s['cta_label']); ?>" oninput="amariNavPreview()">
                            </div>

                            <div class="amari-nav-row">
                                <label>Button URL</label>
                                <input type="url" name="cta_url" value="<?php echo esc_attr($s['cta_url']); ?>">
                            </div>

                            <div class="amari-nav-row amari-nav-row-2col">
                                <div>
                                    <label>Button Background</label>
                                    <input type="color" name="cta_bg" value="<?php echo esc_attr($s['cta_bg']); ?>" oninput="amariNavPreview()">
                                </div>
                                <div>
                                    <label>Button Text Colour</label>
                                    <input type="color" name="cta_text" value="<?php echo esc_attr($s['cta_text']); ?>" oninput="amariNavPreview()">
                                </div>
                            </div>
                        </div>

                        <!-- MOBILE -->
                        <div class="amari-nav-card">
                            <h3>Mobile Menu</h3>

                            <div class="amari-nav-row">
                                <label>Collapse at (px)</label>
                                <input type="number" name="mobile_breakpoint" value="<?php echo esc_attr($s['mobile_breakpoint']); ?>" min="320" max="1200">
                            </div>

                            <div class="amari-nav-row">
                                <label>Mobile Menu Background</label>
                                <input type="color" name="mobile_menu_bg" value="<?php echo esc_attr($s['mobile_menu_bg']); ?>">
                            </div>
                        </div>

                        <div style="margin-top:20px;">
                            <button type="submit" class="button button-primary button-large">Save Navigation Settings</button>
                        </div>

                        <p style="margin-top:12px;color:#666;font-size:12px;">
                            Navigation links are managed via
                            <a href="<?php echo esc_url( admin_url('nav-menus.php') ); ?>">Appearance → Menus</a>.
                            Assign your menu to the <strong>Primary Menu</strong> location.
                        </p>
                    </form>
                </div>

                <!-- ── LIVE PREVIEW ── -->
                <div class="amari-nav-preview-col">
                    <div class="amari-nav-preview-label">Live Preview</div>
                    <div class="amari-nav-preview-frame" id="amari-nav-preview">
                        <div class="anp-header" id="anp-header">
                            <div class="anp-logo" id="anp-logo">
                                <?php if ( $s['logo_type'] === 'image' && $s['logo_image'] ) : ?>
                                    <img src="<?php echo esc_url($s['logo_image']); ?>" style="width:<?php echo absint($s['logo_width']); ?>px;height:auto;">
                                <?php else : ?>
                                    <span class="anp-logo-text" id="anp-logo-text"><?php echo esc_html($s['logo_text']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="anp-nav">
                                <span class="anp-link">Home</span>
                                <span class="anp-link">About</span>
                                <span class="anp-link">Services</span>
                                <span class="anp-link">Portfolio</span>
                                <span class="anp-link">Contact</span>
                                <?php if ( $s['cta_show'] ) : ?>
                                    <span class="anp-cta" id="anp-cta"
                                        style="background:<?php echo esc_attr($s['cta_bg']); ?>;color:<?php echo esc_attr($s['cta_text']); ?>">
                                        <?php echo esc_html($s['cta_label']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="anp-body">
                            <div class="anp-hero" style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);color:#fff;">
                                <strong>Your page content appears here</strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /.amari-nav-layout -->
        </div><!-- /.wrap -->

        <style>
        .amari-nav-wrap { max-width: 1200px; }
        .amari-nav-layout { display: grid; grid-template-columns: 440px 1fr; gap: 32px; align-items: start; margin-top: 20px; }
        .amari-nav-card { background:#fff; border:1px solid #e2e6ea; border-radius:10px; padding:20px; margin-bottom:16px; }
        .amari-nav-card h3 { margin:0 0 16px; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.5px; color:#e94560; font-weight:700; }
        .amari-nav-row { margin-bottom:14px; }
        .amari-nav-row label { display:block; font-size:12px; font-weight:600; color:#6b7280; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.4px; }
        .amari-nav-row input[type="text"],
        .amari-nav-row input[type="url"],
        .amari-nav-row input[type="number"],
        .amari-nav-row select { width:100%; padding:8px 10px; border:1px solid #e2e6ea; border-radius:6px; font-size:13px; }
        .amari-nav-row input[type="color"] { width:44px; height:36px; padding:2px; border-radius:6px; border:1px solid #e2e6ea; cursor:pointer; }
        .amari-nav-row-2col { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .amari-nav-preview-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#6b7280; margin-bottom:10px; }
        .amari-nav-preview-frame { border:1px solid #e2e6ea; border-radius:10px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); position:sticky; top:32px; }
        .anp-header { display:flex; align-items:center; justify-content:space-between; padding:0 24px; transition:all 0.2s; }
        .anp-logo-text { font-size:1.3rem; font-weight:800; }
        .anp-nav { display:flex; align-items:center; gap:20px; }
        .anp-link { font-size:13px; font-weight:500; opacity:0.8; cursor:default; }
        .anp-cta { font-size:12px; font-weight:700; padding:8px 18px; border-radius:6px; cursor:default; }
        .anp-body { padding:0; }
        .anp-hero { height:200px; display:flex; align-items:center; justify-content:center; font-size:1rem; }
        </style>

        <script>
        function amariNavPreview() {
            const hdr   = document.getElementById('anp-header');
            const logo  = document.getElementById('anp-logo');
            const cta   = document.getElementById('anp-cta');

            const bg        = document.querySelector('[name="header_bg"]')?.value || '#fff';
            const textColor = document.querySelector('[name="header_text"]')?.value || '#1a1a2e';
            const height    = document.querySelector('[name="header_height"]')?.value || '70';
            const logoType  = document.querySelector('[name="logo_type"]')?.value || 'text';
            const logoText  = document.querySelector('[name="logo_text"]')?.value || '';
            const logoImg   = document.querySelector('[name="logo_image"]')?.value || '';
            const logoW     = document.querySelector('[name="logo_width"]')?.value || '160';
            const ctaShow   = document.querySelector('[name="cta_show"]')?.checked;
            const ctaLabel  = document.querySelector('[name="cta_label"]')?.value || '';
            const ctaBg     = document.querySelector('[name="cta_bg"]')?.value || '#e94560';
            const ctaTxt    = document.querySelector('[name="cta_text"]')?.value || '#fff';

            if (hdr) {
                hdr.style.backgroundColor = bg;
                hdr.style.height = height + 'px';
                hdr.querySelectorAll('.anp-link, .anp-logo-text').forEach(el => el.style.color = textColor);
            }

            if (logo) {
                if (logoType === 'image' && logoImg) {
                    logo.innerHTML = `<img src="${logoImg}" style="width:${logoW}px;height:auto;">`;
                } else {
                    logo.innerHTML = `<span class="anp-logo-text" style="color:${textColor}">${logoText || 'Logo'}</span>`;
                }
            }

            // Show/hide logo rows
            const textRow  = document.getElementById('amari-logo-text-row');
            const imageRow = document.getElementById('amari-logo-image-row');
            if (textRow)  textRow.style.display  = logoType === 'image' ? 'none' : '';
            if (imageRow) imageRow.style.display = logoType === 'text'  ? 'none' : '';

            // CTA
            const navEl = document.querySelector('.anp-nav');
            if (navEl) {
                let ctaEl = document.getElementById('anp-cta');
                if (ctaShow) {
                    if (!ctaEl) {
                        ctaEl = document.createElement('span');
                        ctaEl.id = 'anp-cta';
                        ctaEl.className = 'anp-cta';
                        navEl.appendChild(ctaEl);
                    }
                    ctaEl.textContent = ctaLabel;
                    ctaEl.style.background = ctaBg;
                    ctaEl.style.color = ctaTxt;
                } else if (ctaEl) {
                    ctaEl.remove();
                }
            }
        }

        function amariPickLogo() {
            if (typeof wp !== 'undefined' && wp.media) {
                const frame = wp.media({ title: 'Choose Logo', multiple: false });
                frame.on('select', function() {
                    const url = frame.state().get('selection').first().toJSON().url;
                    document.getElementById('amari-logo-image').value = url;
                    amariNavPreview();
                });
                frame.open();
            } else {
                const url = prompt('Enter logo image URL:');
                if (url) {
                    document.getElementById('amari-logo-image').value = url;
                    amariNavPreview();
                }
            }
        }
        </script>
        <?php
    }
}
