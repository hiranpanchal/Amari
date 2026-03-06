<?php
/**
 * AmariElement — Abstract base class for all builder elements.
 *
 * To create a new element:
 *   1. Extend this class
 *   2. Implement get_type(), get_label(), get_controls(), get_defaults(), render()
 *   3. Call AmariBuilder::instance()->register_element( new MyElement() )
 */

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class AmariElement {

    /* --------------------------------------------------------
       Required overrides
       -------------------------------------------------------- */

    /** Unique snake-case identifier, e.g. 'text-block' */
    abstract public function get_type(): string;

    /** Human-readable label shown in the editor */
    abstract public function get_label(): string;

    /**
     * Control definitions for the settings panel.
     * Each control is an array:
     *   [
     *     'id'          => 'unique_key',
     *     'type'        => 'text|textarea|richtext|image|select|color|number|toggle|url|icon',
     *     'label'       => 'Human Label',
     *     'default'     => '',          // optional
     *     'options'     => [],          // for select type: [ ['value'=>'', 'label'=>''] ]
     *     'placeholder' => '',          // optional
     *     'description' => '',          // helper text
     *   ]
     */
    abstract public function get_controls(): array;

    /** Default settings values (keyed by control id) */
    abstract public function get_defaults(): array;

    /**
     * Render the element's frontend HTML.
     *
     * @param array $settings  Merged defaults + saved settings.
     */
    abstract public function render( array $settings ): void;

    /* --------------------------------------------------------
       Optional overrides
       -------------------------------------------------------- */

    /** SVG icon markup or dashicon class shown in element palette */
    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>';
    }

    /** Group for organising in the panel: 'basic', 'media', 'advanced', 'interactive' */
    public function get_group(): string {
        return 'basic';
    }

    /* --------------------------------------------------------
       Helpers available to all elements
       -------------------------------------------------------- */

    /**
     * Merge saved settings with defaults, ensuring all keys exist.
     */
    protected function parse_settings( array $settings ): array {
        return array_merge( $this->get_defaults(), $settings );
    }

    /**
     * Build a CSS style string from an associative array,
     * filtering out empty values.
     *
     * @param array $props  [ 'color' => '#333', 'font-size' => '' ]
     */
    protected function build_style( array $props ): string {
        $parts = [];
        foreach ( $props as $prop => $value ) {
            if ( $value !== '' && $value !== null ) {
                $parts[] = esc_attr( $prop ) . ':' . esc_attr( $value );
            }
        }
        return implode( ';', $parts );
    }

    /**
     * Render a button safely.
     */
    protected function render_button( array $settings, string $text_key = 'label', string $url_key = 'url', string $target_key = 'target', string $style_key = 'style', string $size_key = 'size' ): void {
        $text   = $settings[ $text_key ]   ?? 'Click Here';
        $url    = $settings[ $url_key ]    ?? '#';
        $target = $settings[ $target_key ] ?? '_self';
        $style  = $settings[ $style_key ]  ?? 'primary';
        $size   = $settings[ $size_key ]   ?? '';

        $classes = 'amari-btn amari-btn-' . esc_attr( $style );
        if ( $size ) $classes .= ' amari-btn-' . esc_attr( $size );

        printf(
            '<a href="%s" class="%s" target="%s" rel="%s">%s</a>',
            esc_url( $url ),
            esc_attr( $classes ),
            esc_attr( $target ),
            $target === '_blank' ? 'noopener noreferrer' : '',
            esc_html( $text )
        );
    }

    /**
     * Output inline animation data attribute if set.
     */
    protected function get_animation_attr( array $settings ): string {
        $animation = $settings['animation'] ?? '';
        if ( ! $animation ) return '';
        return ' data-amari-animate="' . esc_attr( $animation ) . '"';
    }
}
