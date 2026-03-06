<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementDivider extends AmariElement {
    public function get_type(): string  { return 'divider'; }
    public function get_label(): string { return __( 'Divider', 'amari' ); }
    public function get_group(): string { return 'basic'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/></svg>';
    }

    public function get_defaults(): array {
        return [ 'style' => 'solid', 'color' => '#e5e7eb', 'width' => '100%', 'thickness' => '1px', 'margin' => '20px 0' ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'style',     'type' => 'select', 'label' => 'Style',
              'options' => [ ['value'=>'solid','label'=>'Solid'], ['value'=>'dashed','label'=>'Dashed'], ['value'=>'dotted','label'=>'Dotted'] ] ],
            [ 'id' => 'color',     'type' => 'color',  'label' => 'Color' ],
            [ 'id' => 'thickness', 'type' => 'text',   'label' => 'Thickness', 'placeholder' => '1px' ],
            [ 'id' => 'width',     'type' => 'text',   'label' => 'Width',     'placeholder' => '100%' ],
            [ 'id' => 'margin',    'type' => 'text',   'label' => 'Margin',    'placeholder' => '20px 0' ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );
        $style = sprintf(
            'border:none;border-top:%s %s %s;width:%s;margin:%s;display:block;',
            esc_attr( $s['thickness'] ),
            esc_attr( $s['style'] ),
            esc_attr( $s['color'] ),
            esc_attr( $s['width'] ),
            esc_attr( $s['margin'] )
        );
        echo '<hr class="amari-divider" style="' . $style . '">';
    }
}

AmariBuilder::instance()->register_element( new AmariElementDivider() );
