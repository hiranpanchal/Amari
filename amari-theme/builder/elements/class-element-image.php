<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementImage extends AmariElement {

    public function get_type(): string  { return 'image'; }
    public function get_label(): string { return __( 'Image', 'amari' ); }
    public function get_group(): string { return 'media'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'url'        => '',
            'alt'        => '',
            'caption'    => '',
            'link_url'   => '',
            'link_target'=> '_self',
            'align'      => 'center',
            'width'      => '100%',
            'border_radius' => '',
            'css_class'  => '',
            'animation'  => '',
        ];
    }

    public function get_controls(): array {
        return [
            [
                'id'    => 'url',
                'type'  => 'image',
                'label' => __( 'Image', 'amari' ),
            ],
            [
                'id'          => 'alt',
                'type'        => 'text',
                'label'       => __( 'Alt Text', 'amari' ),
                'placeholder' => __( 'Describe the image...', 'amari' ),
            ],
            [
                'id'          => 'caption',
                'type'        => 'text',
                'label'       => __( 'Caption', 'amari' ),
                'placeholder' => __( 'Optional caption', 'amari' ),
            ],
            [
                'id'          => 'link_url',
                'type'        => 'url',
                'label'       => __( 'Link URL', 'amari' ),
                'placeholder' => 'https://',
            ],
            [
                'id'      => 'link_target',
                'type'    => 'select',
                'label'   => __( 'Link Target', 'amari' ),
                'options' => [
                    [ 'value' => '_self',  'label' => 'Same Tab' ],
                    [ 'value' => '_blank', 'label' => 'New Tab' ],
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
                'id'          => 'width',
                'type'        => 'text',
                'label'       => __( 'Width', 'amari' ),
                'placeholder' => '100% or 600px',
            ],
            [
                'id'          => 'border_radius',
                'type'        => 'text',
                'label'       => __( 'Border Radius', 'amari' ),
                'placeholder' => 'e.g. 8px or 50%',
            ],
            [
                'id'    => 'animation',
                'type'  => 'select',
                'label' => __( 'Animation', 'amari' ),
                'options' => [
                    [ 'value' => '',        'label' => 'None' ],
                    [ 'value' => 'fade-in', 'label' => 'Fade In' ],
                    [ 'value' => 'zoom-in', 'label' => 'Zoom In' ],
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

        if ( empty( $s['url'] ) ) {
            // Placeholder in editor / empty on frontend
            echo '<div class="amari-image-placeholder" style="background:#f0f0f0;border:2px dashed #ccc;padding:40px;text-align:center;color:#999;border-radius:8px;">';
            echo '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" style="margin:0 auto 8px;display:block"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
            echo '<p>No image selected</p>';
            echo '</div>';
            return;
        }

        $wrapper_style = $this->build_style( [ 'text-align' => $s['align'] ] );
        $img_style     = $this->build_style([
            'width'         => $s['width'],
            'border-radius' => $s['border_radius'],
        ]);

        $class = 'amari-image-wrap';
        if ( $s['css_class'] ) $class .= ' ' . esc_attr( $s['css_class'] );
        $anim = $this->get_animation_attr( $s );

        echo '<figure class="' . esc_attr( $class ) . '"'
            . ( $wrapper_style ? ' style="' . $wrapper_style . '"' : '' )
            . $anim . '>';

        $img = '<img src="' . esc_url( $s['url'] ) . '" alt="' . esc_attr( $s['alt'] ) . '" loading="lazy"'
             . ( $img_style ? ' style="' . $img_style . '"' : '' ) . '>';

        if ( $s['link_url'] ) {
            $rel = $s['link_target'] === '_blank' ? ' rel="noopener noreferrer"' : '';
            echo '<a href="' . esc_url( $s['link_url'] ) . '" target="' . esc_attr( $s['link_target'] ) . '"' . $rel . '>' . $img . '</a>';
        } else {
            echo $img;
        }

        if ( $s['caption'] ) {
            echo '<figcaption class="amari-image-caption">' . esc_html( $s['caption'] ) . '</figcaption>';
        }

        echo '</figure>';
    }
}

AmariBuilder::instance()->register_element( new AmariElementImage() );
