<?php
/**
 * WooCommerce Product Price Element
 * Displays the price of a specific product, or the current product in a loop.
 *
 * @package Amari
 * @since   3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementWooPrice extends AmariElement {

    public function get_type(): string  { return 'woo-price'; }
    public function get_label(): string { return __( 'Product Price', 'amari' ); }
    public function get_group(): string { return 'woocommerce'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'product_id'    => '',
            'size'          => 'large',
            'color'         => '',
            'sale_color'    => '#e94560',
            'show_original' => true,
            'align'         => 'left',
            'css_class'     => '',
        ];
    }

    public function get_controls(): array {
        return [
            [
                'id'          => 'product_id',
                'type'        => 'number',
                'label'       => __( 'Product ID (leave blank for current product)', 'amari' ),
                'placeholder' => 'e.g. 42',
            ],
            [
                'id'      => 'size',
                'type'    => 'select',
                'label'   => __( 'Font Size', 'amari' ),
                'options' => [
                    [ 'value' => 'small',  'label' => 'Small' ],
                    [ 'value' => 'medium', 'label' => 'Medium' ],
                    [ 'value' => 'large',  'label' => 'Large' ],
                    [ 'value' => 'xlarge', 'label' => 'X-Large' ],
                ],
            ],
            [
                'id'    => 'color',
                'type'  => 'color',
                'label' => __( 'Price Colour', 'amari' ),
            ],
            [
                'id'    => 'sale_color',
                'type'  => 'color',
                'label' => __( 'Sale Price Colour', 'amari' ),
            ],
            [
                'id'    => 'show_original',
                'type'  => 'toggle',
                'label' => __( 'Show Original (strikethrough) Price on Sale', 'amari' ),
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
        if ( ! $pid ) {
            // Fallback to global product in a product loop
            global $product;
            if ( ! $product ) {
                echo '<div class="amari-woo-notice">Please enter a Product ID.</div>';
                return;
            }
        } else {
            $product = wc_get_product( $pid );
        }

        if ( ! $product ) {
            echo '<div class="amari-woo-notice">Product not found.</div>';
            return;
        }

        $size_map = [ 'small' => '1rem', 'medium' => '1.25rem', 'large' => '1.75rem', 'xlarge' => '2.5rem' ];
        $font_size = $size_map[ $s['size'] ] ?? '1.75rem';

        $inline = 'text-align:' . esc_attr( $s['align'] ) . ';font-size:' . $font_size . ';';
        if ( $s['color'] ) {
            $inline .= 'color:' . esc_attr( $s['color'] ) . ';';
        }

        $extra_class = $s['css_class'] ? ' ' . esc_attr( $s['css_class'] ) : '';

        echo '<div class="amari-woo-price' . $extra_class . '" style="' . $inline . '">';

        if ( $product->is_on_sale() ) {
            if ( $s['show_original'] ) {
                echo '<del class="amari-woo-original-price">' . wc_price( $product->get_regular_price() ) . '</del> ';
            }
            $sale_inline = $s['sale_color'] ? ' style="color:' . esc_attr( $s['sale_color'] ) . ';"' : '';
            echo '<ins class="amari-woo-sale-price"' . $sale_inline . '>' . wc_price( $product->get_sale_price() ) . '</ins>';
        } else {
            echo wp_kses_post( $product->get_price_html() );
        }

        echo '</div>';
    }
}

AmariBuilder::instance()->register_element( new AmariElementWooPrice() );
