<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementButton extends AmariElement {

    public function get_type(): string  { return 'button'; }
    public function get_label(): string { return __( 'Button', 'amari' ); }
    public function get_group(): string { return 'basic'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="10" rx="5"/><line x1="8" y1="12" x2="16" y2="12"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'label'      => 'Click Here',
            'url'        => '#',
            'target'     => '_self',
            'style'      => 'primary',
            'size'       => '',
            'align'      => 'left',
            'icon'       => '',
            'full_width' => false,
            'css_class'  => '',
            'animation'  => '',
        ];
    }

    public function get_controls(): array {
        return [
            [
                'id'          => 'label',
                'type'        => 'text',
                'label'       => __( 'Button Text', 'amari' ),
                'placeholder' => 'Click Here',
            ],
            [
                'id'          => 'url',
                'type'        => 'url',
                'label'       => __( 'Link URL', 'amari' ),
                'placeholder' => 'https://',
            ],
            [
                'id'      => 'target',
                'type'    => 'select',
                'label'   => __( 'Open in', 'amari' ),
                'options' => [
                    [ 'value' => '_self',  'label' => 'Same Tab' ],
                    [ 'value' => '_blank', 'label' => 'New Tab' ],
                ],
            ],
            [
                'id'      => 'style',
                'type'    => 'select',
                'label'   => __( 'Style', 'amari' ),
                'options' => [
                    [ 'value' => 'primary',   'label' => 'Primary' ],
                    [ 'value' => 'secondary', 'label' => 'Secondary / Outline' ],
                    [ 'value' => 'ghost',     'label' => 'Ghost / Text' ],
                ],
            ],
            [
                'id'      => 'size',
                'type'    => 'select',
                'label'   => __( 'Size', 'amari' ),
                'options' => [
                    [ 'value' => '',   'label' => 'Default' ],
                    [ 'value' => 'sm', 'label' => 'Small' ],
                    [ 'value' => 'lg', 'label' => 'Large' ],
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
                'id'          => 'css_class',
                'type'        => 'text',
                'label'       => __( 'CSS Class', 'amari' ),
                'placeholder' => 'custom-class',
            ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );

        $wrapper_style = 'text-align:' . esc_attr( $s['align'] );

        echo '<div class="amari-button-wrap" style="' . $wrapper_style . '">';
        $this->render_button( $s );
        echo '</div>';
    }
}

AmariBuilder::instance()->register_element( new AmariElementButton() );
