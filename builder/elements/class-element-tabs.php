<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementTabs extends AmariElement {
    public function get_type(): string  { return 'tabs'; }
    public function get_label(): string { return __( 'Tabs', 'amari' ); }
    public function get_group(): string { return 'interactive'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'tabs' => [
                [ 'label' => 'Design',      'icon' => '🎨', 'content' => '<p>Our design process starts with deep discovery to understand your brand, goals, and audience. We craft pixel-perfect designs that balance aesthetics with usability.</p>' ],
                [ 'label' => 'Development', 'icon' => '⚡', 'content' => '<p>Clean, modern code built on best practices. We develop fast, accessible, and maintainable websites and applications that scale.</p>' ],
                [ 'label' => 'Strategy',    'icon' => '📊', 'content' => '<p>Data-driven decisions backed by research. We align digital strategy with your business goals to drive measurable growth.</p>' ],
            ],
            'style'       => 'underline',
            'align'       => 'left',
            'accent_color'=> '#e94560',
            'css_class'   => '',
        ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'tabs',         'type' => 'repeater', 'label' => 'Tab Items',
              'fields' => [ ['id'=>'label','type'=>'text','label'=>'Tab Label'], ['id'=>'icon','type'=>'text','label'=>'Icon/Emoji'], ['id'=>'content','type'=>'textarea','label'=>'Content (HTML ok)'] ] ],
            [ 'id' => 'style',        'type' => 'select', 'label' => 'Tab Style',
              'options' => [ ['value'=>'underline','label'=>'Underline'], ['value'=>'pills','label'=>'Pills/Filled'], ['value'=>'boxed','label'=>'Boxed'] ] ],
            [ 'id' => 'align',        'type' => 'select', 'label' => 'Tab Alignment',
              'options' => [ ['value'=>'left','label'=>'Left'], ['value'=>'center','label'=>'Center'], ['value'=>'right','label'=>'Right'] ] ],
            [ 'id' => 'accent_color', 'type' => 'color',  'label' => 'Active Color' ],
            [ 'id' => 'css_class',    'type' => 'text',   'label' => 'CSS Class' ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );
        $tabs = $s['tabs'] ?? [];
        if ( empty( $tabs ) ) return;

        $id     = 'tabs-' . uniqid();
        $accent = esc_attr( $s['accent_color'] ?: '#e94560' );
        $class  = 'amari-tabs amari-tabs--' . esc_attr( $s['style'] ?: 'underline' );
        if ( $s['css_class'] ) $class .= ' ' . esc_attr( $s['css_class'] );

        echo "<div class=\"{$class}\" id=\"{$id}\" style=\"--amari-tabs-accent:{$accent};\">";

        // Tab nav
        echo "<div class=\"amari-tabs-nav\" role=\"tablist\" style=\"justify-content:" . esc_attr( $s['align'] ?: 'flex-start' ) . ";\">";
        foreach ( $tabs as $idx => $tab ) {
            $label    = esc_html( $tab['label'] ?? "Tab " . ($idx+1) );
            $icon     = ! empty( $tab['icon'] ) ? '<span class="amari-tabs-icon">' . esc_html( $tab['icon'] ) . '</span> ' : '';
            $selected = $idx === 0 ? 'true' : 'false';
            $panel_id = $id . '-panel-' . $idx;
            $tab_id   = $id . '-tab-' . $idx;
            echo "<button class=\"amari-tab-btn" . ( $idx === 0 ? ' is-active' : '' ) . "\" role=\"tab\" id=\"{$tab_id}\" aria-selected=\"{$selected}\" aria-controls=\"{$panel_id}\" data-tab-target=\"{$panel_id}\">{$icon}{$label}</button>";
        }
        echo "</div>";

        // Tab panels
        echo "<div class=\"amari-tabs-panels\">";
        foreach ( $tabs as $idx => $tab ) {
            $content  = wp_kses_post( $tab['content'] ?? '' );
            $panel_id = $id . '-panel-' . $idx;
            $tab_id   = $id . '-tab-' . $idx;
            $hidden   = $idx !== 0 ? ' hidden' : '';
            echo "<div class=\"amari-tab-panel" . ( $idx === 0 ? ' is-active' : '' ) . "\" id=\"{$panel_id}\" role=\"tabpanel\" aria-labelledby=\"{$tab_id}\"{$hidden}>{$content}</div>";
        }
        echo "</div></div>";
    }
}

AmariBuilder::instance()->register_element( new AmariElementTabs() );
