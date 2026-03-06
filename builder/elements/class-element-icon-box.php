<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementIconBox extends AmariElement {
    public function get_type(): string  { return 'icon-box'; }
    public function get_label(): string { return __( 'Icon Box', 'amari' ); }
    public function get_group(): string { return 'basic'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'icon'       => '⭐',
            'title'      => 'Feature Title',
            'content'    => 'Describe this feature or benefit in a couple of short sentences.',
            'layout'     => 'top',
            'icon_size'  => '2rem',
            'icon_color' => '#e94560',
            'align'      => 'center',
            'css_class'  => '',
        ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'icon',      'type' => 'text',    'label' => 'Icon / Emoji',  'placeholder' => '⭐ or SVG' ],
            [ 'id' => 'title',     'type' => 'text',    'label' => 'Title' ],
            [ 'id' => 'content',   'type' => 'textarea','label' => 'Description' ],
            [ 'id' => 'layout',    'type' => 'select',  'label' => 'Icon Position',
              'options' => [ ['value'=>'top','label'=>'Top'], ['value'=>'left','label'=>'Left'] ] ],
            [ 'id' => 'icon_size', 'type' => 'text',    'label' => 'Icon Size', 'placeholder' => '2rem' ],
            [ 'id' => 'icon_color','type' => 'color',   'label' => 'Icon Color' ],
            [ 'id' => 'align',     'type' => 'select',  'label' => 'Alignment',
              'options' => [ ['value'=>'left','label'=>'Left'], ['value'=>'center','label'=>'Center'], ['value'=>'right','label'=>'Right'] ] ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );
        $layout = $s['layout'] === 'left' ? 'display:flex;gap:16px;align-items:flex-start;' : '';
        $align  = 'text-align:' . esc_attr( $s['align'] );
        $icon_style = 'font-size:' . esc_attr( $s['icon_size'] ) . ';color:' . esc_attr( $s['icon_color'] ) . ';line-height:1;flex-shrink:0;';

        echo '<div class="amari-icon-box' . ( $s['css_class'] ? ' ' . esc_attr($s['css_class']) : '' ) . '" style="' . $layout . $align . '">';
        echo '<div class="amari-icon-box-icon" style="' . $icon_style . '">' . esc_html( $s['icon'] ) . '</div>';
        echo '<div class="amari-icon-box-body">';
        if ( $s['title'] ) echo '<h4 class="amari-icon-box-title" style="margin-bottom:8px;">' . esc_html( $s['title'] ) . '</h4>';
        if ( $s['content'] ) echo '<p class="amari-icon-box-content" style="margin:0;color:#666;font-size:0.95rem;">' . esc_html( $s['content'] ) . '</p>';
        echo '</div></div>';
    }
}

AmariBuilder::instance()->register_element( new AmariElementIconBox() );
