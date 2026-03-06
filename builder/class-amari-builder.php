<?php
/**
 * AmariBuilder — Core builder singleton.
 *
 * Responsibilities:
 *  - Element registration
 *  - Frontend rendering (JSON → HTML)
 *  - Builder UI injection (admin)
 *  - Config output for JS
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AmariBuilder {

    /** @var AmariBuilder|null */
    private static ?AmariBuilder $instance = null;

    /** @var AmariElement[] */
    private array $elements = [];

    /* --------------------------------------------------------
       Singleton
       -------------------------------------------------------- */

    public static function instance(): AmariBuilder {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_element_base();
        $this->load_elements();
        $this->register_hooks();
    }

    /* --------------------------------------------------------
       Load / Register Elements
       -------------------------------------------------------- */

    private function load_element_base(): void {
        require_once AMARI_BUILDER_DIR . '/elements/class-element-base.php';
    }

    private function load_elements(): void {
        $element_files = [
            // V1 Core elements
            'heading'        => 'class-element-heading.php',
            'text-block'     => 'class-element-text-block.php',
            'image'          => 'class-element-image.php',
            'button'         => 'class-element-button.php',
            'spacer'         => 'class-element-spacer.php',
            'video'          => 'class-element-video.php',
            'divider'        => 'class-element-divider.php',
            'icon-box'       => 'class-element-icon-box.php',
            'portfolio-grid' => 'class-element-portfolio-grid.php',
            'contact-form'   => 'class-element-contact-form.php',
            'code-block'     => 'class-element-code-block.php',
            'testimonial'    => 'class-element-testimonial.php',
            // V2 Advanced elements
            'accordion'      => 'class-element-accordion.php',
            'tabs'           => 'class-element-tabs.php',
            'counter'        => 'class-element-counter.php',
            'slider'         => 'class-element-slider.php',
            'pricing-table'  => 'class-element-pricing-table.php',
        ];

        foreach ( $element_files as $type => $file ) {
            $path = AMARI_BUILDER_DIR . '/elements/' . $file;
            if ( file_exists( $path ) ) {
                require_once $path;
            }
        }

        // Allow plugins/child themes to register elements
        do_action( 'amari_register_elements', $this );
    }

    /**
     * Register an element type.
     *
     * @param AmariElement $element
     */
    public function register_element( AmariElement $element ): void {
        $this->elements[ $element->get_type() ] = $element;
    }

    /**
     * Get a registered element by type.
     */
    public function get_element( string $type ): ?AmariElement {
        return $this->elements[ $type ] ?? null;
    }

    /**
     * Return all elements config for JS.
     */
    public function get_elements_config(): array {
        $config = [];
        foreach ( $this->elements as $type => $element ) {
            $config[ $type ] = [
                'type'     => $type,
                'label'    => $element->get_label(),
                'icon'     => $element->get_icon(),
                'group'    => $element->get_group(),
                'controls' => $element->get_controls(),
                'defaults' => $element->get_defaults(),
            ];
        }
        return $config;
    }

    /* --------------------------------------------------------
       Hooks
       -------------------------------------------------------- */

    private function register_hooks(): void {
        // Inject builder UI on admin page/post edit screens
        add_action( 'edit_form_after_title', [ $this, 'maybe_inject_builder_ui' ] );

        // Full-screen builder overlay trigger
        add_action( 'admin_footer', [ $this, 'output_builder_templates' ] );
    }

    /* --------------------------------------------------------
       Admin: Builder UI Injection
       -------------------------------------------------------- */

    public function maybe_inject_builder_ui( \WP_Post $post ): void {
        if ( ! in_array( $post->post_type, [ 'page', 'post' ] ) ) return;

        // Output the builder mount point; JS will hydrate it
        echo '<div id="amari-builder-mount" data-post-id="' . esc_attr( $post->ID ) . '"></div>';
    }

    public function output_builder_templates(): void {
        global $post;
        if ( ! $post ) return;
        if ( ! in_array( $post->post_type, [ 'page', 'post' ] ) ) return;

        // Inline JS templates used by the builder
        include AMARI_BUILDER_DIR . '/templates/builder-ui.php';
        include AMARI_BUILDER_DIR . '/templates/builder-templates-modal.php';
    }

    /* --------------------------------------------------------
       Frontend: Render builder JSON → HTML
       -------------------------------------------------------- */

    public function render( array $data ): void {
        if ( empty( $data['sections'] ) ) return;

        foreach ( $data['sections'] as $section ) {
            $this->render_section( $section );
        }
    }

    private function render_section( array $section ): void {
        $settings  = $section['settings'] ?? [];
        $id        = esc_attr( $section['id'] ?? '' );
        $bg_color  = esc_attr( $settings['bg_color'] ?? '' );
        $bg_image  = esc_url( $settings['bg_image'] ?? '' );
        $padding   = esc_attr( $settings['padding'] ?? '60px 0' );
        $classes   = 'amari-section';
        if ( ! empty( $settings['full_width'] ) ) $classes .= ' amari-section-full-width';
        if ( ! empty( $settings['css_class'] ) )  $classes .= ' ' . esc_attr( $settings['css_class'] );

        $style_parts = [];
        if ( $bg_color )  $style_parts[] = 'background-color:' . $bg_color;
        if ( $bg_image )  $style_parts[] = 'background-image:url(' . $bg_image . ');background-size:cover;background-position:center';
        if ( $padding )   $style_parts[] = 'padding:' . $padding;

        $style = implode( ';', $style_parts );

        echo '<section id="' . $id . '" class="' . esc_attr( $classes ) . '" style="' . esc_attr( $style ) . '">';

        $container_open  = ! empty( $settings['full_width'] ) ? '<div class="amari-section-inner">' : '<div class="amari-container">';
        $container_close = '</div>';

        echo $container_open;

        foreach ( $section['rows'] ?? [] as $row ) {
            $this->render_row( $row );
        }

        echo $container_close;
        echo '</section>';
    }

    private function render_row( array $row ): void {
        $id      = esc_attr( $row['id'] ?? '' );
        $classes = 'amari-row amari-builder-row';
        if ( ! empty( $row['settings']['css_class'] ) ) {
            $classes .= ' ' . esc_attr( $row['settings']['css_class'] );
        }

        // Vertical alignment
        $v_align = $row['settings']['v_align'] ?? 'top';
        $align_map = [ 'top' => 'flex-start', 'center' => 'center', 'bottom' => 'flex-end' ];
        $style = 'align-items:' . ( $align_map[ $v_align ] ?? 'flex-start' );

        echo '<div id="' . $id . '" class="' . esc_attr( $classes ) . '" style="' . esc_attr( $style ) . '">';

        foreach ( $row['columns'] ?? [] as $col ) {
            $this->render_column( $col );
        }

        echo '</div>';
    }

    private function render_column( array $col ): void {
        $size    = $col['size'] ?? '1-1';
        $id      = esc_attr( $col['id'] ?? '' );
        $classes = 'amari-col amari-col-' . esc_attr( $size );
        if ( ! empty( $col['settings']['css_class'] ) ) {
            $classes .= ' ' . esc_attr( $col['settings']['css_class'] );
        }

        $style_parts = [];
        if ( ! empty( $col['settings']['padding'] ) ) {
            $style_parts[] = 'padding:' . esc_attr( $col['settings']['padding'] );
        }
        $style = implode( ';', $style_parts );

        echo '<div id="' . $id . '" class="' . esc_attr( $classes ) . '"' . ( $style ? ' style="' . $style . '"' : '' ) . '>';

        foreach ( $col['elements'] ?? [] as $element_data ) {
            $this->render_element( $element_data );
        }

        echo '</div>';
    }

    private function render_element( array $element_data ): void {
        $type    = $element_data['type']     ?? '';
        $id      = $element_data['id']       ?? '';
        $settings = $element_data['settings'] ?? [];

        $element = $this->get_element( $type );
        if ( ! $element ) return;

        echo '<div class="amari-element amari-element-' . esc_attr( $type ) . '" id="el-' . esc_attr( $id ) . '">';
        $element->render( $settings );
        echo '</div>';
    }
}
