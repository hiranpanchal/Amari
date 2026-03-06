<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementTextBlock extends AmariElement {

    public function get_type(): string  { return 'text-block'; }
    public function get_label(): string { return __( 'Text Block', 'amari' ); }
    public function get_group(): string { return 'basic'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="3" y1="14" x2="21" y2="14"/><line x1="3" y1="18" x2="14" y2="18"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'content'     => '<p>Enter your text here. This is a rich text block that supports <strong>bold</strong>, <em>italic</em>, links, and more.</p>',
            'align'       => '',
            'color'       => '',
            'font_size'   => '',
            'max_width'   => '',
            'css_class'   => '',
            'animation'   => '',
        ];
    }

    public function get_controls(): array {
        return [
            [
                'id'    => 'content',
                'type'  => 'richtext',
                'label' => __( 'Content', 'amari' ),
            ],
            [
                'id'      => 'align',
                'type'    => 'select',
                'label'   => __( 'Text Align', 'amari' ),
                'options' => [
                    [ 'value' => '',       'label' => 'Default' ],
                    [ 'value' => 'left',   'label' => 'Left' ],
                    [ 'value' => 'center', 'label' => 'Center' ],
                    [ 'value' => 'right',  'label' => 'Right' ],
                ],
            ],
            [
                'id'    => 'color',
                'type'  => 'color',
                'label' => __( 'Text Color', 'amari' ),
            ],
            [
                'id'          => 'font_size',
                'type'        => 'text',
                'label'       => __( 'Font Size', 'amari' ),
                'placeholder' => 'e.g. 1.1rem',
            ],
            [
                'id'          => 'max_width',
                'type'        => 'text',
                'label'       => __( 'Max Width', 'amari' ),
                'placeholder' => 'e.g. 800px or 70%',
                'description' => __( 'Optionally constrain the text width for readability.', 'amari' ),
            ],
            [
                'id'    => 'animation',
                'type'  => 'select',
                'label' => __( 'Animation', 'amari' ),
                'options' => [
                    [ 'value' => '',         'label' => 'None' ],
                    [ 'value' => 'fade-in',  'label' => 'Fade In' ],
                    [ 'value' => 'slide-up', 'label' => 'Slide Up' ],
                ],
            ],
            [
                'id'          => 'css_class',
                'type'        => 'text',
                'label'       => __( 'CSS Class', 'amari' ),
                'placeholder' => 'custom-class',
            ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );

        $class = 'amari-text-block';
        if ( $s['css_class'] ) $class .= ' ' . esc_attr( $s['css_class'] );

        $style = $this->build_style([
            'text-align' => $s['align'],
            'color'      => $s['color'],
            'font-size'  => $s['font_size'],
            'max-width'  => $s['max_width'],
        ]);

        $anim = $this->get_animation_attr( $s );

        echo '<div class="' . esc_attr( $class ) . '"'
            . ( $style ? ' style="' . $style . '"' : '' )
            . $anim . '>';
        echo wp_kses_post( $s['content'] );
        echo '</div>';
    }
}

AmariBuilder::instance()->register_element( new AmariElementTextBlock() );
