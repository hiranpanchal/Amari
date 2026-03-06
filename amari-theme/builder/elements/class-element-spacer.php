<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementSpacer extends AmariElement {

    public function get_type(): string  { return 'spacer'; }
    public function get_label(): string { return __( 'Spacer', 'amari' ); }
    public function get_group(): string { return 'basic'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="4" x2="12" y2="20"/><line x1="6" y1="4" x2="18" y2="4"/><line x1="6" y1="20" x2="18" y2="20"/></svg>';
    }

    public function get_defaults(): array {
        return [ 'height' => '40px', 'hide_mobile' => false ];
    }

    public function get_controls(): array {
        return [
            [
                'id'          => 'height',
                'type'        => 'text',
                'label'       => __( 'Height', 'amari' ),
                'placeholder' => '40px',
            ],
            [
                'id'    => 'hide_mobile',
                'type'  => 'toggle',
                'label' => __( 'Hide on Mobile', 'amari' ),
            ],
        ];
    }

    public function render( array $settings ): void {
        $s      = $this->parse_settings( $settings );
        $height = esc_attr( $s['height'] ?: '40px' );
        $class  = 'amari-spacer';
        if ( $s['hide_mobile'] ) $class .= ' amari-hide-mobile';
        echo '<div class="' . esc_attr( $class ) . '" style="height:' . $height . ';display:block;"></div>';
    }
}

AmariBuilder::instance()->register_element( new AmariElementSpacer() );
