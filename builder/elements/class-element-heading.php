<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementHeading extends AmariElement {

    public function get_type(): string { return 'heading'; }
    public function get_label(): string { return __( 'Heading', 'amari' ); }
    public function get_group(): string { return 'basic'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'text'        => 'Your Heading Here',
            'tag'         => 'h2',
            'align'       => 'left',
            'color'       => '',
            'font_size'   => '',
            'font_weight' => '',
            'css_class'   => '',
            'animation'   => '',
        ];
    }

    public function get_controls(): array {
        return [
            [
                'id'          => 'text',
                'type'        => 'text',
                'label'       => __( 'Heading Text', 'amari' ),
                'placeholder' => __( 'Enter heading...', 'amari' ),
            ],
            [
                'id'      => 'tag',
                'type'    => 'select',
                'label'   => __( 'HTML Tag', 'amari' ),
                'options' => [
                    [ 'value' => 'h1', 'label' => 'H1' ],
                    [ 'value' => 'h2', 'label' => 'H2' ],
                    [ 'value' => 'h3', 'label' => 'H3' ],
                    [ 'value' => 'h4', 'label' => 'H4' ],
                    [ 'value' => 'h5', 'label' => 'H5' ],
                    [ 'value' => 'h6', 'label' => 'H6' ],
                ],
            ],
            [
                'id'      => 'align',
                'type'    => 'select',
                'label'   => __( 'Alignment', 'amari' ),
                'options' => [
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
                'placeholder' => 'e.g. 2.5rem or 40px',
            ],
            [
                'id'      => 'font_weight',
                'type'    => 'select',
                'label'   => __( 'Font Weight', 'amari' ),
                'options' => [
                    [ 'value' => '',    'label' => 'Default' ],
                    [ 'value' => '300', 'label' => 'Light' ],
                    [ 'value' => '400', 'label' => 'Regular' ],
                    [ 'value' => '600', 'label' => 'Semi Bold' ],
                    [ 'value' => '700', 'label' => 'Bold' ],
                    [ 'value' => '800', 'label' => 'Extra Bold' ],
                ],
            ],
            [
                'id'    => 'animation',
                'type'  => 'select',
                'label' => __( 'Animation', 'amari' ),
                'options' => [
                    [ 'value' => '',          'label' => 'None' ],
                    [ 'value' => 'fade-in',   'label' => 'Fade In' ],
                    [ 'value' => 'slide-up',  'label' => 'Slide Up' ],
                    [ 'value' => 'slide-left','label' => 'Slide from Left' ],
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

        $tag      = in_array( $s['tag'], ['h1','h2','h3','h4','h5','h6'] ) ? $s['tag'] : 'h2';
        $class    = 'amari-heading';
        if ( $s['css_class'] ) $class .= ' ' . esc_attr( $s['css_class'] );

        $style = $this->build_style([
            'text-align'  => $s['align'],
            'color'       => $s['color'],
            'font-size'   => $s['font_size'],
            'font-weight' => $s['font_weight'],
        ]);

        $anim = $this->get_animation_attr( $s );

        printf(
            '<%s class="%s"%s%s>%s</%s>',
            $tag,
            esc_attr( $class ),
            $style ? ' style="' . $style . '"' : '',
            $anim,
            esc_html( $s['text'] ),
            $tag
        );
    }
}

// Auto-register
AmariBuilder::instance()->register_element( new AmariElementHeading() );
