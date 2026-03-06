<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementTestimonial extends AmariElement {
    public function get_type(): string  { return 'testimonial'; }
    public function get_label(): string { return __( 'Testimonial', 'amari' ); }
    public function get_group(): string { return 'advanced'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'quote'   => 'This is an outstanding service. Highly recommended to anyone looking for quality and reliability.',
            'name'    => 'Jane Smith',
            'role'    => 'CEO, Acme Corp',
            'avatar'  => '',
            'rating'  => '5',
            'style'   => 'card',
            'css_class' => '',
        ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'quote',  'type' => 'textarea', 'label' => 'Quote' ],
            [ 'id' => 'name',   'type' => 'text',     'label' => 'Name' ],
            [ 'id' => 'role',   'type' => 'text',     'label' => 'Role / Company' ],
            [ 'id' => 'avatar', 'type' => 'image',    'label' => 'Avatar Photo' ],
            [ 'id' => 'rating', 'type' => 'select',   'label' => 'Star Rating',
              'options' => [ ['value'=>'','label'=>'None'], ['value'=>'5','label'=>'★★★★★'], ['value'=>'4','label'=>'★★★★☆'], ['value'=>'3','label'=>'★★★☆☆'] ] ],
            [ 'id' => 'style',  'type' => 'select',   'label' => 'Style',
              'options' => [ ['value'=>'card','label'=>'Card'], ['value'=>'minimal','label'=>'Minimal'] ] ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );
        $is_card = $s['style'] === 'card';
        $wrap_style = $is_card ? 'background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:28px;box-shadow:0 2px 12px rgba(0,0,0,0.06);' : 'padding:16px 0;';

        echo '<blockquote class="amari-testimonial' . ( $s['css_class'] ? ' '.esc_attr($s['css_class']) : '' ) . '" style="' . $wrap_style . '">';

        if ( $s['rating'] ) {
            echo '<div class="amari-testimonial-stars" style="color:#f59e0b;margin-bottom:12px;font-size:1.1rem;">';
            echo str_repeat('★', intval($s['rating']));
            echo '</div>';
        }

        echo '<p class="amari-testimonial-quote" style="font-size:1rem;color:#444;font-style:italic;line-height:1.7;margin-bottom:20px;">';
        echo '"' . esc_html($s['quote']) . '"';
        echo '</p>';

        echo '<footer class="amari-testimonial-author" style="display:flex;align-items:center;gap:12px;">';
        if ( $s['avatar'] ) {
            echo '<img src="' . esc_url($s['avatar']) . '" alt="' . esc_attr($s['name']) . '" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">';
        }
        echo '<div>';
        echo '<cite class="amari-testimonial-name" style="font-weight:700;font-style:normal;color:#1a1a2e;display:block;">' . esc_html($s['name']) . '</cite>';
        if ( $s['role'] ) echo '<span class="amari-testimonial-role" style="font-size:0.85rem;color:#999;">' . esc_html($s['role']) . '</span>';
        echo '</div>';
        echo '</footer>';

        echo '</blockquote>';
    }
}

AmariBuilder::instance()->register_element( new AmariElementTestimonial() );
