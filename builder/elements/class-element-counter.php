<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementCounter extends AmariElement {
    public function get_type(): string  { return 'counter'; }
    public function get_label(): string { return __( 'Animated Counter', 'amari' ); }
    public function get_group(): string { return 'interactive'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'stats' => [
                [ 'value' => '250',  'suffix' => '+',  'label' => 'Projects Delivered',  'icon' => '🚀' ],
                [ 'value' => '98',   'suffix' => '%',  'label' => 'Client Satisfaction', 'icon' => '⭐' ],
                [ 'value' => '15',   'suffix' => '+',  'label' => 'Years Experience',    'icon' => '🏆' ],
                [ 'value' => '40',   'suffix' => '+',  'label' => 'Team Members',        'icon' => '👥' ],
            ],
            'number_color' => '#e94560',
            'label_color'  => '#666666',
            'number_size'  => '3rem',
            'show_icon'    => true,
            'align'        => 'center',
            'duration'     => '2000',
            'css_class'    => '',
        ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'stats',         'type' => 'repeater', 'label' => 'Stats',
              'fields' => [ ['id'=>'value','type'=>'number','label'=>'Number'], ['id'=>'suffix','type'=>'text','label'=>'Suffix (+ % K etc)'], ['id'=>'label','type'=>'text','label'=>'Label'], ['id'=>'icon','type'=>'text','label'=>'Icon/Emoji'] ] ],
            [ 'id' => 'number_color',  'type' => 'color',  'label' => 'Number Color' ],
            [ 'id' => 'label_color',   'type' => 'color',  'label' => 'Label Color' ],
            [ 'id' => 'number_size',   'type' => 'text',   'label' => 'Number Size', 'placeholder' => '3rem' ],
            [ 'id' => 'show_icon',     'type' => 'toggle', 'label' => 'Show Icons' ],
            [ 'id' => 'align',         'type' => 'select', 'label' => 'Alignment',
              'options' => [ ['value'=>'left','label'=>'Left'], ['value'=>'center','label'=>'Center'], ['value'=>'right','label'=>'Right'] ] ],
            [ 'id' => 'duration',      'type' => 'text',   'label' => 'Animation Duration (ms)', 'placeholder' => '2000' ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );
        $stats = $s['stats'] ?? [];
        if ( empty( $stats ) ) return;

        $num_color  = esc_attr( $s['number_color'] ?: '#e94560' );
        $lbl_color  = esc_attr( $s['label_color'] ?: '#666' );
        $num_size   = esc_attr( $s['number_size'] ?: '3rem' );
        $align      = esc_attr( $s['align'] ?: 'center' );
        $duration   = intval( $s['duration'] ?: 2000 );
        $count      = count( $stats );
        $class      = 'amari-counters';
        if ( $s['css_class'] ) $class .= ' ' . esc_attr( $s['css_class'] );

        echo "<div class=\"{$class}\" style=\"display:grid;grid-template-columns:repeat({$count},1fr);gap:32px;text-align:{$align};\">";

        foreach ( $stats as $stat ) {
            $val    = esc_attr( $stat['value']  ?? '0' );
            $suffix = esc_html( $stat['suffix'] ?? '' );
            $label  = esc_html( $stat['label']  ?? '' );
            $icon   = esc_html( $stat['icon']   ?? '' );

            echo "<div class=\"amari-counter-item\">";
            if ( $s['show_icon'] && $icon ) {
                echo "<div class=\"amari-counter-icon\" style=\"font-size:2rem;margin-bottom:8px;\">{$icon}</div>";
            }
            echo "<div class=\"amari-counter-number\" data-target=\"{$val}\" data-duration=\"{$duration}\" style=\"font-size:{$num_size};font-weight:800;color:{$num_color};line-height:1;\">0{$suffix}</div>";
            echo "<div class=\"amari-counter-label\" style=\"font-size:0.9rem;color:{$lbl_color};margin-top:6px;font-weight:500;\">{$label}</div>";
            echo "</div>";
        }

        echo "</div>";
    }
}

AmariBuilder::instance()->register_element( new AmariElementCounter() );
