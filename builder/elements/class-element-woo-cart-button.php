<?php
/**
 * WooCommerce Cart Button Element
 * Renders an "Add to Cart" button for a specified product,
 * or a "View Cart" / mini-cart icon for use in headers/CTAs.
 *
 * @package Amari
 * @since   3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementWooCartButton extends AmariElement {

    public function get_type(): string  { return 'woo-cart-button'; }
    public function get_label(): string { return __( 'Cart Button', 'amari' ); }
    public function get_group(): string { return 'woocommerce'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'mode'       => 'add_to_cart',
            'product_id' => '',
            'label'      => '',
            'style'      => 'primary',
            'size'       => '',
            'show_count' => true,
            'align'      => 'left',
            'css_class'  => '',
        ];
    }

    public function get_controls(): array {
        return [
            [
                'id'      => 'mode',
                'type'    => 'select',
                'label'   => __( 'Button Mode', 'amari' ),
                'options' => [
                    [ 'value' => 'add_to_cart', 'label' => 'Add to Cart (specific product)' ],
                    [ 'value' => 'view_cart',   'label' => 'View Cart' ],
                    [ 'value' => 'cart_icon',   'label' => 'Cart Icon (with item count)' ],
                ],
            ],
            [
                'id'          => 'product_id',
                'type'        => 'number',
                'label'       => __( 'Product ID (Add to Cart mode)', 'amari' ),
                'placeholder' => 'e.g. 42',
            ],
            [
                'id'          => 'label',
                'type'        => 'text',
                'label'       => __( 'Button Label (leave blank for default)', 'amari' ),
                'placeholder' => '',
            ],
            [
                'id'      => 'style',
                'type'    => 'select',
                'label'   => __( 'Style', 'amari' ),
                'options' => [
                    [ 'value' => 'primary',   'label' => 'Primary' ],
                    [ 'value' => 'secondary', 'label' => 'Secondary' ],
                    [ 'value' => 'ghost',     'label' => 'Ghost' ],
                ],
            ],
            [
                'id'      => 'size',
                'type'    => 'select',
                'label'   => __( 'Size', 'amari' ),
                'options' => [
                    [ 'value' => '',   'label' => 'Default' ],
                    [ 'value' => 'sm', 'label' => 'Small' ],
                    [ 'value' => 'lg', 'label' => 'Large' ],
                ],
            ],
            [
                'id'    => 'show_count',
                'type'  => 'toggle',
                'label' => __( 'Show Cart Count Badge', 'amari' ),
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

        $btn_class = 'amari-btn amari-btn-' . esc_attr( $s['style'] );
        if ( $s['size'] ) $btn_class .= ' amari-btn-' . esc_attr( $s['size'] );
        if ( $s['css_class'] ) $btn_class .= ' ' . esc_attr( $s['css_class'] );

        echo '<div style="text-align:' . esc_attr( $s['align'] ) . ';">';

        switch ( $s['mode'] ) {

            case 'view_cart':
                $label = $s['label'] ?: __( 'View Cart', 'amari' );
                $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
                echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="amari-woo-view-cart ' . $btn_class . '">';
                echo esc_html( $label );
                if ( $s['show_count'] && $count > 0 ) {
                    echo ' <span class="amari-woo-count-badge">' . intval( $count ) . '</span>';
                }
                echo '</a>';
                break;

            case 'cart_icon':
                $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
                echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="amari-woo-cart-icon-btn" aria-label="' . esc_attr__( 'Cart', 'amari' ) . '">';
                echo '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>';
                if ( $s['show_count'] ) {
                    echo '<span class="amari-woo-count-badge" data-woo-cart-count>' . intval( $count ) . '</span>';
                }
                echo '</a>';
                break;

            case 'add_to_cart':
            default:
                $pid = intval( $s['product_id'] );
                if ( ! $pid ) {
                    echo '<div class="amari-woo-notice">Please enter a Product ID.</div>';
                    break;
                }
                $product = wc_get_product( $pid );
                if ( ! $product ) {
                    echo '<div class="amari-woo-notice">Product #' . $pid . ' not found.</div>';
                    break;
                }
                $label = $s['label'] ?: $product->add_to_cart_text();
                if ( $product->is_in_stock() ) {
                    echo '<a href="' . esc_url( $product->add_to_cart_url() ) . '"'
                        . ' class="' . esc_attr( $btn_class ) . ' add_to_cart_button ajax_add_to_cart"'
                        . ' data-product_id="' . esc_attr( $pid ) . '"'
                        . ' aria-label="' . esc_attr( sprintf( __( 'Add %s to cart', 'amari' ), $product->get_name() ) ) . '">'
                        . esc_html( $label )
                        . '</a>';
                } else {
                    echo '<a href="' . esc_url( get_permalink( $pid ) ) . '" class="' . esc_attr( $btn_class ) . '">' . __( 'Out of Stock', 'amari' ) . '</a>';
                }
                break;
        }

        echo '</div>';
    }
}

AmariBuilder::instance()->register_element( new AmariElementWooCartButton() );
