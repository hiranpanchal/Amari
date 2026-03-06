<?php
/**
 * Amari Global Styles System
 *
 * Manages site-wide design tokens (colours, typography, spacing, radius)
 * stored in wp_options and output as CSS custom properties on every page.
 * The admin settings page lives under Appearance → Amari Settings.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariGlobalStyles {

    private static ?AmariGlobalStyles $instance = null;
    private const OPTION_KEY = 'amari_global_styles';

    public static function instance(): AmariGlobalStyles {
        if ( ! self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu',          [ $this, 'register_menu' ] );
        add_action( 'admin_post_amari_save_global_styles', [ $this, 'handle_save' ] );
        add_action( 'wp_head',             [ $this, 'output_css_vars' ], 5 );
        add_action( 'admin_head',          [ $this, 'output_css_vars' ], 5 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    /* ── Defaults ──────────────────────────────────────────── */

    public static function get_defaults(): array {
        return [
            // Colour palette
            'color_primary'     => '#1a1a2e',
            'color_secondary'   => '#16213e',
            'color_accent'      => '#e94560',
            'color_accent2'     => '#6366f1',
            'color_text'        => '#333333',
            'color_text_light'  => '#666666',
            'color_bg'          => '#ffffff',
            'color_bg_alt'      => '#f8f9fa',
            'color_border'      => '#e5e7eb',
            // Typography
            'font_heading'      => 'Inter',
            'font_body'         => 'Inter',
            'font_size_base'    => '16px',
            'font_weight_heading' => '700',
            'line_height_body'  => '1.6',
            'letter_spacing_heading' => '-0.3px',
            // Spacing
            'spacing_xs'        => '8px',
            'spacing_sm'        => '16px',
            'spacing_md'        => '24px',
            'spacing_lg'        => '48px',
            'spacing_xl'        => '80px',
            // Shape
            'border_radius'     => '6px',
            'border_radius_lg'  => '12px',
            // Buttons
            'btn_padding'       => '12px 28px',
            'btn_font_weight'   => '600',
            'btn_border_radius' => '6px',
            // Shadows
            'shadow_sm'         => '0 2px 8px rgba(0,0,0,0.06)',
            'shadow_md'         => '0 4px 20px rgba(0,0,0,0.10)',
            'shadow_lg'         => '0 8px 40px rgba(0,0,0,0.14)',
            // Transitions
            'transition_speed'  => '0.25s',
        ];
    }

    public function get(): array {
        $saved = get_option( self::OPTION_KEY, [] );
        return array_merge( self::get_defaults(), is_array($saved) ? $saved : [] );
    }

    /* ── CSS Variable Output ───────────────────────────────── */

    public function output_css_vars(): void {
        $s = $this->get();
        ?>
        <style id="amari-global-styles">
        :root {
            /* ── Colours ── */
            --amari-primary:        <?php echo esc_attr($s['color_primary']); ?>;
            --amari-secondary:      <?php echo esc_attr($s['color_secondary']); ?>;
            --amari-accent:         <?php echo esc_attr($s['color_accent']); ?>;
            --amari-accent2:        <?php echo esc_attr($s['color_accent2']); ?>;
            --amari-text:           <?php echo esc_attr($s['color_text']); ?>;
            --amari-text-light:     <?php echo esc_attr($s['color_text_light']); ?>;
            --amari-bg:             <?php echo esc_attr($s['color_bg']); ?>;
            --amari-bg-alt:         <?php echo esc_attr($s['color_bg_alt']); ?>;
            --amari-border:         <?php echo esc_attr($s['color_border']); ?>;
            --amari-highlight:      color-mix(in srgb, <?php echo esc_attr($s['color_accent']); ?> 12%, transparent);

            /* ── Typography ── */
            --amari-font-sans:      '<?php echo esc_attr($s['font_body']); ?>', -apple-system, sans-serif;
            --amari-font-heading:   '<?php echo esc_attr($s['font_heading']); ?>', -apple-system, sans-serif;
            --amari-font-size-base: <?php echo esc_attr($s['font_size_base']); ?>;
            --amari-line-height:    <?php echo esc_attr($s['line_height_body']); ?>;

            /* ── Spacing ── */
            --amari-space-xs:   <?php echo esc_attr($s['spacing_xs']); ?>;
            --amari-space-sm:   <?php echo esc_attr($s['spacing_sm']); ?>;
            --amari-space-md:   <?php echo esc_attr($s['spacing_md']); ?>;
            --amari-space-lg:   <?php echo esc_attr($s['spacing_lg']); ?>;
            --amari-space-xl:   <?php echo esc_attr($s['spacing_xl']); ?>;

            /* ── Shape ── */
            --amari-radius:     <?php echo esc_attr($s['border_radius']); ?>;
            --amari-radius-lg:  <?php echo esc_attr($s['border_radius_lg']); ?>;

            /* ── Shadows ── */
            --amari-shadow:     <?php echo esc_attr($s['shadow_sm']); ?>;
            --amari-shadow-md:  <?php echo esc_attr($s['shadow_md']); ?>;
            --amari-shadow-lg:  <?php echo esc_attr($s['shadow_lg']); ?>;

            /* ── Transition ── */
            --amari-transition: <?php echo esc_attr($s['transition_speed']); ?> ease;

            /* ── Buttons ── */
            --amari-btn-padding:       <?php echo esc_attr($s['btn_padding']); ?>;
            --amari-btn-font-weight:   <?php echo esc_attr($s['btn_font_weight']); ?>;
            --amari-btn-radius:        <?php echo esc_attr($s['btn_border_radius']); ?>;
        }
        <?php
        // Apply heading font
        if ( $s['font_heading'] !== 'Inter' ) : ?>
        h1,h2,h3,h4,h5,h6 { font-family: var(--amari-font-heading); }
        <?php endif;
        // Apply body font
        if ( $s['font_body'] !== 'Inter' ) : ?>
        body { font-family: var(--amari-font-sans); }
        <?php endif; ?>
        </style>
        <?php
        // Enqueue custom Google Fonts if needed
        $heading_font = $s['font_heading'];
        $body_font    = $s['font_body'];
        $default_fonts = ['Inter', 'system', '-apple-system'];
        $fonts_to_load = array_unique(array_filter([$heading_font, $body_font], fn($f) => !in_array($f, $default_fonts)));
        if ($fonts_to_load && !is_admin()) {
            $families = implode('&family=', array_map(fn($f) => urlencode($f) . ':wght@300;400;500;600;700;800', $fonts_to_load));
            $url = "https://fonts.googleapis.com/css2?family={$families}&display=swap";
            echo '<link rel="stylesheet" href="' . esc_url($url) . '">';
        }
    }

    /* ── Admin Menu ────────────────────────────────────────── */

    public function register_menu(): void {
        add_theme_page(
            __( 'Amari Settings', 'amari' ),
            __( '⚡ Amari Settings', 'amari' ),
            'manage_options',
            'amari-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    public function enqueue_admin_assets( string $hook ): void {
        if ( strpos($hook, 'amari-settings') === false ) return;
        // Vanilla — no extra deps needed
    }

    /* ── Save Handler ──────────────────────────────────────── */

    public function handle_save(): void {
        if ( ! current_user_can('manage_options') ) wp_die( 'Unauthorised' );
        check_admin_referer( 'amari_global_styles_save' );

        $defaults = self::get_defaults();
        $new = [];
        foreach ( $defaults as $key => $default ) {
            $raw = $_POST[ 'amari_' . $key ] ?? $default;
            $new[$key] = sanitize_text_field( wp_unslash($raw) );
        }
        update_option( self::OPTION_KEY, $new );

        wp_redirect( admin_url('themes.php?page=amari-settings&saved=1') );
        exit;
    }

    /* ── Settings Page ─────────────────────────────────────── */

    public function render_settings_page(): void {
        $s = $this->get();
        $saved = isset($_GET['saved']);
        ?>
        <div class="wrap" style="max-width:900px;">
        <h1 style="display:flex;align-items:center;gap:10px;">⚡ Amari Global Styles <?php if($saved): ?><span style="font-size:13px;background:#22c55e;color:#fff;padding:3px 10px;border-radius:20px;font-weight:600;">✓ Saved</span><?php endif; ?></h1>
        <p style="color:#666;margin-bottom:24px;">Design tokens that apply site-wide. Changes here update CSS custom properties used by all Amari builder elements.</p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('amari_global_styles_save'); ?>
        <input type="hidden" name="action" value="amari_save_global_styles">

        <?php
        $sections = [
            'Colour Palette' => [
                'color_primary'    => [ 'label'=>'Primary Colour',     'type'=>'color', 'desc'=>'Main brand colour (headings, logo)' ],
                'color_secondary'  => [ 'label'=>'Secondary Colour',   'type'=>'color', 'desc'=>'Secondary brand colour' ],
                'color_accent'     => [ 'label'=>'Accent / CTA Colour','type'=>'color', 'desc'=>'Buttons, links, highlights' ],
                'color_accent2'    => [ 'label'=>'Accent 2',           'type'=>'color', 'desc'=>'Alternative accent (gradients, tags)' ],
                'color_text'       => [ 'label'=>'Body Text',          'type'=>'color', 'desc'=>'Default paragraph text' ],
                'color_text_light' => [ 'label'=>'Light Text',         'type'=>'color', 'desc'=>'Captions, secondary text' ],
                'color_bg'         => [ 'label'=>'Background',         'type'=>'color', 'desc'=>'Page background' ],
                'color_bg_alt'     => [ 'label'=>'Alt Background',     'type'=>'color', 'desc'=>'Alternate section backgrounds' ],
                'color_border'     => [ 'label'=>'Border Colour',      'type'=>'color', 'desc'=>'Dividers, card borders' ],
            ],
            'Typography' => [
                'font_heading'      => [ 'label'=>'Heading Font',      'type'=>'font',  'desc'=>'Google Font name for headings' ],
                'font_body'         => [ 'label'=>'Body Font',         'type'=>'font',  'desc'=>'Google Font name for body text' ],
                'font_size_base'    => [ 'label'=>'Base Font Size',    'type'=>'text',  'desc'=>'e.g. 16px' ],
                'font_weight_heading'=> ['label'=>'Heading Weight',   'type'=>'select', 'options'=>['300','400','600','700','800'], 'desc'=>'Font weight for headings' ],
                'line_height_body'  => [ 'label'=>'Body Line Height',  'type'=>'text',  'desc'=>'e.g. 1.6' ],
                'letter_spacing_heading'=>['label'=>'Heading Letter Spacing','type'=>'text','desc'=>'e.g. -0.3px' ],
            ],
            'Spacing & Shape' => [
                'spacing_xs'       => [ 'label'=>'Spacing XS',  'type'=>'text', 'desc'=>'Extra small gap (e.g. 8px)' ],
                'spacing_sm'       => [ 'label'=>'Spacing SM',  'type'=>'text', 'desc'=>'Small gap (e.g. 16px)' ],
                'spacing_md'       => [ 'label'=>'Spacing MD',  'type'=>'text', 'desc'=>'Medium gap (e.g. 24px)' ],
                'spacing_lg'       => [ 'label'=>'Spacing LG',  'type'=>'text', 'desc'=>'Large gap (e.g. 48px)' ],
                'spacing_xl'       => [ 'label'=>'Spacing XL',  'type'=>'text', 'desc'=>'Extra large gap (e.g. 80px)' ],
                'border_radius'    => [ 'label'=>'Border Radius',   'type'=>'text', 'desc'=>'Default radius (e.g. 6px)' ],
                'border_radius_lg' => [ 'label'=>'Border Radius LG','type'=>'text', 'desc'=>'Large radius (e.g. 12px)' ],
            ],
            'Buttons' => [
                'btn_padding'       => [ 'label'=>'Button Padding',       'type'=>'text', 'desc'=>'e.g. 12px 28px' ],
                'btn_font_weight'   => [ 'label'=>'Button Font Weight',   'type'=>'text', 'desc'=>'e.g. 600' ],
                'btn_border_radius' => [ 'label'=>'Button Border Radius', 'type'=>'text', 'desc'=>'e.g. 6px' ],
            ],
        ];

        foreach ( $sections as $title => $fields ) :
        ?>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:24px;margin-bottom:20px;">
        <h2 style="margin:0 0 20px;font-size:1rem;padding-bottom:12px;border-bottom:1px solid #f0f0f0;"><?php echo esc_html($title); ?></h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <?php foreach ($fields as $key => $field) :
            $val = $s[$key] ?? '';
            $input_name = 'amari_' . $key;
        ?>
        <div>
            <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;"><?php echo esc_html($field['label']); ?></label>
            <?php if ($field['type'] === 'color') : ?>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="color" name="<?php echo esc_attr($input_name); ?>_picker" value="<?php echo esc_attr($val); ?>" style="width:40px;height:36px;padding:2px;border-radius:6px;border:1px solid #ddd;cursor:pointer;" oninput="this.nextElementSibling.value=this.value">
                    <input type="text" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($val); ?>" style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;" oninput="if(/^#[0-9a-f]{6}$/i.test(this.value))this.previousElementSibling.value=this.value">
                </div>
            <?php elseif ($field['type'] === 'font') : ?>
                <input type="text" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($val); ?>" style="width:100%;padding:7px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;" placeholder="e.g. Playfair Display">
            <?php elseif ($field['type'] === 'select') : ?>
                <select name="<?php echo esc_attr($input_name); ?>" style="width:100%;padding:7px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
                    <?php foreach ($field['options'] as $opt) : ?>
                        <option value="<?php echo esc_attr($opt); ?>" <?php selected($val, $opt); ?>><?php echo esc_html($opt); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <input type="text" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($val); ?>" style="width:100%;padding:7px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;" placeholder="<?php echo esc_attr($val); ?>">
            <?php endif; ?>
            <?php if (!empty($field['desc'])) : ?>
                <p style="font-size:11px;color:#999;margin:3px 0 0;"><?php echo esc_html($field['desc']); ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div></div>
        <?php endforeach; ?>

        <!-- Live Preview Swatch -->
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:24px;margin-bottom:20px;">
            <h2 style="margin:0 0 16px;font-size:1rem;">Live Colour Preview</h2>
            <div id="amari-colour-swatches" style="display:flex;gap:12px;flex-wrap:wrap;">
                <?php foreach(['color_primary','color_secondary','color_accent','color_accent2','color_text','color_bg_alt','color_border'] as $ck) : ?>
                <div style="text-align:center;">
                    <div style="width:56px;height:56px;border-radius:8px;border:1px solid #eee;background:<?php echo esc_attr($s[$ck]); ?>;margin-bottom:4px;"></div>
                    <div style="font-size:9px;color:#999;"><?php echo esc_html(str_replace('color_','',$ck)); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <p><button type="submit" class="button button-primary" style="background:#e94560;border-color:#e94560;padding:8px 24px;font-size:14px;">💾 Save Global Styles</button></p>
        </form>
        </div>
        <?php
    }
}
