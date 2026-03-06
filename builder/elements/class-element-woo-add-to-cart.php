<?php
/**
 * WooCommerce Add to Cart Element
 * Full add-to-cart form with optional quantity selector.
 *
 * @package Amari
 * @since   3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementWooAddToCart extends AmariElement {

    public function get_type(): string  { return 'woo-add-to-cart'; }
    public function get_label(): string { return __( 'Add to Cart Form', 'amari' ); }
    public function get_group(): string { return 'woocommerce'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/><line x1="16" y1="3" x2="16" y2="9"/><line x1="13" y1="6" x2="19" y2="6"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'product_id'    => '',
            'show_qty'      => true,
            'show_price'    => true,
            'button_label'  => '',
            'button_style'  => 'primary',
            'button_size'   => '',
            'align'         => 'left',
            'css_class'     => '',
        ];
    }

    public function get_controls(): array {
        return [
            [
                'id'          => 'product_id',
                'type'        => 'number',
                'label'       => __( 'Product ID', 'amari' ),
                'placeholder' => 'e.g. 42',
            ],
            [
                'id'    => 'show_qty',
                'type'  => 'toggle',
                'label' => __( 'Show Quantity Stepper', 'amari' ),
            ],
            [
                'id'    => 'show_price',
                'type'  => 'toggle',
                'label' => __( 'Show Price Above Button', 'amari' ),
            ],
            [
                'id'          => 'button_label',
                'type'        => 'text',
                'label'       => __( 'Button Text (blank = default)', 'amari' ),
                'placeholder' => 'Add to Cart',
            ],
            [
                'id'      => 'button_style',
                'type'    => 'select',
                'label'   => __( 'Button Style', 'amari' ),
                'options' => [
                    [ 'value' => 'primary',   'label' => 'Primary' ],
                    [ 'value' => 'secondary', 'label' => 'Secondary' ],
                    [ 'value' => 'ghost',     'label' => 'Ghost' ],
                ],
            ],
            [
                'id'      => 'button_size',
                'type'    => 'select',
                'label'   => __( 'Button Size', 'amari' ),
                'options' => [
                    [ 'value' => '',   'label' => 'Default' ],
                    [ 'value' => 'sm', 'label' => 'Small' ],
                    [ 'value' => 'lg', 'label' => 'Large' ],
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
            echo '<div class="amari-woo-notice">Please enter a Product ID.</div>';
            return;
        }

        $product = wc_get_product( $pid );
        if ( ! $product ) {
            echo '<div class="amari-woo-notice">Product #' . $pid . ' not found.</div>';
            return;
        }

        $btn_class = 'amari-btn amari-btn-' . esc_attr( $s['button_style'] );
        if ( $s['button_size'] ) $btn_class .= ' amari-btn-' . esc_attr( $s['button_size'] );

        $extra_class = $s['css_class'] ? ' ' . esc_attr( $s['css_class'] ) : '';

        echo '<div class="amari-woo-atc-wrap' . $extra_class . '" style="text-align:' . esc_attr( $s['align'] ) . ';">';

        if ( $s['show_price'] ) {
            echo '<div class="amari-woo-atc-price">' . wp_kses_post( $product->get_price_html() ) . '</div>';
        }

        if ( ! $product->is_in_stock() ) {
            echo '<p class="amari-woo-out-of-stock-msg">' . esc_html__( 'Out of stock', 'woocommerce' ) . '</p>';
            echo '</div>';
            return;
        }

        // Output the native WooCommerce add_to_cart form
        // This uses WC's built-in template so it works with all product types
        $btn_label = $s['button_label'] ?: $product->add_to_cart_text();

        if ( $product->is_type( 'simple' ) ) {
            echo '<form class="amari-woo-atc-form cart" action="' . esc_url( $product->add_to_cart_url() ) . '" method="post" enctype="multipart/form-data">';

            if ( $s['show_qty'] ) {
                echo '<div class="amari-woo-qty-wrap">';
                echo '<button type="button" class="amari-woo-qty-btn amari-woo-qty-minus" aria-label="Decrease">−</button>';
                echo '<input type="number" class="amari-woo-qty-input qty" name="quantity" value="1" min="1" max="' . esc_attr( $product->get_max_purchase_quantity() ) . '" step="1" aria-label="Quantity">';
                echo '<button type="button" class="amari-woo-qty-btn amari-woo-qty-plus" aria-label="Increase">+</button>';
                echo '</div>';
            } else {
                echo '<input type="hidden" name="quantity" value="1">';
            }

            echo '<input type="hidden" name="add-to-cart" value="' . esc_attr( $pid ) . '">';
            echo '<button type="submit" class="' . esc_attr( $btn_class ) . ' amari-woo-atc-btn add_to_cart_button">' . esc_html( $btn_label ) . '</button>';
            echo '</form>';
        } else {
            // Variable / grouped / external: link to product page
            echo '<a href="' . esc_url( get_permalink( $pid ) ) . '" class="' . esc_attr( $btn_class ) . '">' . esc_html( $btn_label ) . '</a>';
        }

        echo '</div>';
    }
}

AmariBuilder::instance()->register_element( new AmariElementWooAddToCart() );
