<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementAccordion extends AmariElement {
    public function get_type(): string  { return 'accordion'; }
    public function get_label(): string { return __( 'Accordion', 'amari' ); }
    public function get_group(): string { return 'interactive'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'items' => [
                [ 'title' => 'What services do you offer?',       'content' => 'We offer a full range of design and development services tailored to your needs.' ],
                [ 'title' => 'How long does a project take?',      'content' => 'Timelines vary by project scope, but most projects are delivered within 4–8 weeks.' ],
                [ 'title' => 'Do you offer ongoing support?',      'content' => 'Yes — we offer flexible maintenance and support packages for all budgets.' ],
            ],
            'style'           => 'default',
            'open_first'      => true,
            'allow_multiple'  => false,
            'icon_style'      => 'chevron',
            'border_color'    => '#e5e7eb',
            'accent_color'    => '#e94560',
            'css_class'       => '',
        ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'items',          'type' => 'repeater', 'label' => 'Accordion Items',
              'fields' => [ ['id'=>'title','type'=>'text','label'=>'Title'], ['id'=>'content','type'=>'textarea','label'=>'Content'] ] ],
            [ 'id' => 'style',          'type' => 'select', 'label' => 'Style',
              'options' => [ ['value'=>'default','label'=>'Default (bordered)'], ['value'=>'flush','label'=>'Flush / Minimal'], ['value'=>'boxed','label'=>'Boxed (shadow)'] ] ],
            [ 'id' => 'open_first',     'type' => 'toggle', 'label' => 'Open First Item by Default' ],
            [ 'id' => 'allow_multiple', 'type' => 'toggle', 'label' => 'Allow Multiple Open at Once' ],
            [ 'id' => 'icon_style',     'type' => 'select', 'label' => 'Icon',
              'options' => [ ['value'=>'chevron','label'=>'Chevron ›'], ['value'=>'plus','label'=>'Plus +'], ['value'=>'arrow','label'=>'Arrow →'] ] ],
            [ 'id' => 'accent_color',   'type' => 'color',  'label' => 'Accent Color' ],
            [ 'id' => 'border_color',   'type' => 'color',  'label' => 'Border Color' ],
            [ 'id' => 'css_class',      'type' => 'text',   'label' => 'CSS Class' ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );
        $items = $s['items'] ?? [];
        if ( empty( $items ) ) return;

        $id         = 'accordion-' . uniqid();
        $accent     = esc_attr( $s['accent_color'] ?: '#e94560' );
        $border     = esc_attr( $s['border_color'] ?: '#e5e7eb' );
        $multi      = $s['allow_multiple'] ? 'true' : 'false';
        $class      = 'amari-accordion amari-accordion--' . esc_attr( $s['style'] ?: 'default' );
        if ( $s['css_class'] ) $class .= ' ' . esc_attr( $s['css_class'] );

        echo "<div class=\"{$class}\" id=\"{$id}\" data-allow-multiple=\"{$multi}\" style=\"--amari-acc-accent:{$accent};--amari-acc-border:{$border};\">";

        foreach ( $items as $idx => $item ) {
            $title   = esc_html( $item['title']   ?? '' );
            $content = wp_kses_post( $item['content'] ?? '' );
            $open    = ( $idx === 0 && $s['open_first'] );
            $aria    = $open ? 'true' : 'false';
            $item_id = $id . '-item-' . $idx;

            $icon = match ( $s['icon_style'] ) {
                'plus'  => '<span class="amari-acc-icon amari-acc-icon--plus"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></span>',
                'arrow' => '<span class="amari-acc-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></span>',
                default => '<span class="amari-acc-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>',
            };

            echo "<div class=\"amari-acc-item" . ( $open ? ' is-open' : '' ) . "\">";
            echo "<button class=\"amari-acc-trigger\" aria-expanded=\"{$aria}\" aria-controls=\"{$item_id}\">";
            echo "<span class=\"amari-acc-title\">{$title}</span>{$icon}";
            echo "</button>";
            echo "<div class=\"amari-acc-body\" id=\"{$item_id}\" role=\"region\"" . ( !$open ? ' hidden' : '' ) . ">";
            echo "<div class=\"amari-acc-content\">{$content}</div>";
            echo "</div></div>";
        }

        echo "</div>";
    }
}

AmariBuilder::instance()->register_element( new AmariElementAccordion() );
