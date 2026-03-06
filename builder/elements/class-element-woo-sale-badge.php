<?php
/**
 * WooCommerce Sale Badge Element
 * Displays a customisable sale or "new" badge for a product.
 *
 * @package Amari
 * @since   3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementWooSaleBadge extends AmariElement {

    public function get_type(): string  { return 'woo-sale-badge'; }
    public function get_label(): string { return __( 'Sale Badge', 'amari' ); }
    public function get_group(): string { return 'woocommerce'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'product_id'       => '',
            'badge_type'       => 'auto',
            'sale_text'        => 'Sale',
            'new_text'         => 'New',
            'custom_text'      => '',
            'show_percent'     => false,
            'shape'            => 'pill',
            'bg_color'         => '#e94560',
            'text_color'       => '#ffffff',
            'font_size'        => '0.75rem',
            'align'            => 'left',
            'css_class'        => '',
        ];
    }

    public function get_controls(): array {
        return [
            [
                'id'          => 'product_id',
                'type'        => 'number',
                'label'       => __( 'Product ID (blank = current product)', 'amari' ),
                'placeholder' => 'e.g. 42',
            ],
            [
                'id'      => 'badge_type',
                'type'    => 'select',
                'label'   => __( 'Badge Type', 'amari' ),
                'options' => [
                    [ 'value' => 'auto',   'label' => 'Auto (Sale or New, based on product)' ],
                    [ 'value' => 'sale',   'label' => 'Sale only' ],
                    [ 'value' => 'new',    'label' => 'New only' ],
                    [ 'value' => 'custom', 'label' => 'Custom text always' ],
                ],
            ],
            [
                'id'          => 'sale_text',
                'type'        => 'text',
                'label'       => __( 'Sale Badge Text', 'amari' ),
                'placeholder' => 'Sale',
            ],
            [
                'id'    => 'show_percent',
                'type'  => 'toggle',
                'label' => __( 'Show Discount % (e.g. −20%)', 'amari' ),
            ],
            [
                'id'          => 'new_text',
                'type'        => 'text',
                'label'       => __( 'New Badge Text', 'amari' ),
                'placeholder' => 'New',
            ],
            [
                'id'          => 'custom_text',
                'type'        => 'text',
                'label'       => __( 'Custom Badge Text', 'amari' ),
                'placeholder' => 'Hot Deal',
            ],
            [
                'id'      => 'shape',
                'type'    => 'select',
                'label'   => __( 'Shape', 'amari' ),
                'options' => [
                    [ 'value' => 'pill',   'label' => 'Pill / Rounded' ],
                    [ 'value' => 'square', 'label' => 'Square' ],
                    [ 'value' => 'circle', 'label' => 'Circle' ],
                ],
            ],
            [
                'id'    => 'bg_color',
                'type'  => 'color',
                'label' => __( 'Background Colour', 'amari' ),
            ],
            [
                'id'    => 'text_color',
                'type'  => 'color',
                'label' => __( 'Text Colour', 'amari' ),
            ],
            [
                'id'          => 'font_size',
                'type'        => 'text',
                'label'       => __( 'Font Size (CSS value)', 'amari' ),
                'placeholder' => '0.75rem',
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

        if ( ! class_exists( 'WooCommerce' ) ) {
            echo '<div class="amari-woo-notice">WooCommerce is not active.</div>';
            return;
        }

        $pid = intval( $s['product_id'] );
        if ( $pid ) {
            $product = wc_get_product( $pid );
        } else {
            global $product;
        }

        $badge_text = '';

        switch ( $s['badge_type'] ) {
            case 'custom':
                $badge_text = sanitize_text_field( $s['custom_text'] );
                break;

            case 'new':
                if ( $product && strtotime( $product->get_date_created() ) > strtotime( '-30 days' ) ) {
                    $badge_text = sanitize_text_field( $s['new_text'] ) ?: 'New';
                }
                break;

            case 'sale':
                if ( $product && $product->is_on_sale() ) {
                    $badge_text = $this->get_sale_text( $product, $s );
                }
                break;

            case 'auto':
            default:
                if ( $product && $product->is_on_sale() ) {
                    $badge_text = $this->get_sale_text( $product, $s );
                } elseif ( $product && strtotime( $product->get_date_created() ) > strtotime( '-30 days' ) ) {
                    $badge_text = sanitize_text_field( $s['new_text'] ) ?: 'New';
                }
                break;
        }

        if ( ! $badge_text ) {
            // Nothing to show — render placeholder in builder only
            if ( is_admin() ) {
                echo '<div class="amari-woo-notice" style="font-size:0.75rem;">Sale Badge: no qualifying badge for this product/type.</div>';
            }
            return;
        }

        $shape_radius = [ 'pill' => '999px', 'square' => '4px', 'circle' => '50%' ];
        $radius       = $shape_radius[ $s['shape'] ] ?? '999px';

        $inline = sprintf(
            'display:inline-flex;align-items:center;justify-content:center;background:%s;color:%s;font-size:%s;border-radius:%s;padding:%s;font-weight:700;line-height:1;',
            esc_attr( $s['bg_color'] ),
            esc_attr( $s['text_color'] ),
            esc_attr( $s['font_size'] ),
            $radius,
            'circle' === $s['shape'] ? '0.5em' : '0.3em 0.7em'
        );

        $extra_class = $s['css_class'] ? ' ' . esc_attr( $s['css_class'] ) : '';

        echo '<div style="text-align:' . esc_attr( $s['align'] ) . ';">';
        echo '<span class="amari-woo-badge-el' . $extra_class . '" style="' . $inline . '">';
        echo esc_html( $badge_text );
        echo '</span>';
        echo '</div>';
    }

    private function get_sale_text( \WC_Product $product, array $s ): string {
        $base = sanitize_text_field( $s['sale_text'] ) ?: 'Sale';
        if ( $s['show_percent'] ) {
            $regular = (float) $product->get_regular_price();
            $sale    = (float) $product->get_sale_price();
            if ( $regular > 0 && $sale < $regular ) {
                $pct  = round( ( ( $regular - $sale ) / $regular ) * 100 );
                return '−' . $pct . '%';
            }
        }
        return $base;
    }
}

AmariBuilder::instance()->register_element( new AmariElementWooSaleBadge() );
