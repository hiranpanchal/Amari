<?php
/**
 * WooCommerce Single Product Card Element
 * Displays a hand-picked single WooCommerce product.
 *
 * @package Amari
 * @since   3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementWooProductCard extends AmariElement {

    public function get_type(): string  { return 'woo-product-card'; }
    public function get_label(): string { return __( 'Product Card', 'amari' ); }
    public function get_group(): string { return 'woocommerce'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="2" width="18" height="20" rx="2"/><path d="M8 10h8M8 14h5"/><circle cx="12" cy="6" r="2"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'product_id'  => '',
            'style'       => 'card',
            'show_price'  => true,
            'show_cart'   => true,
            'show_badge'  => true,
            'show_desc'   => false,
            'align'       => 'center',
            'css_class'   => '',
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
                'id'      => 'style',
                'type'    => 'select',
                'label'   => __( 'Card Style', 'amari' ),
                'options' => [
                    [ 'value' => 'card',       'label' => 'Card (shadow)' ],
                    [ 'value' => 'flat',       'label' => 'Flat (border)' ],
                    [ 'value' => 'horizontal', 'label' => 'Horizontal' ],
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
                'id'    => 'show_price',
                'type'  => 'toggle',
                'label' => __( 'Show Price', 'amari' ),
            ],
            [
                'id'    => 'show_cart',
                'type'  => 'toggle',
                'label' => __( 'Show Add to Cart', 'amari' ),
            ],
            [
                'id'    => 'show_badge',
                'type'  => 'toggle',
                'label' => __( 'Show Sale Badge', 'amari' ),
            ],
            [
                'id'    => 'show_desc',
                'type'  => 'toggle',
                'label' => __( 'Show Short Description', 'amari' ),
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
            echo '<div class="amari-woo-notice">Please enter a Product ID in the element settings.</div>';
            return;
        }

        $product = wc_get_product( $pid );
        if ( ! $product ) {
            echo '<div class="amari-woo-notice">Product #' . $pid . ' not found.</div>';
            return;
        }

        $style       = in_array( $s['style'], [ 'card', 'flat', 'horizontal' ] ) ? $s['style'] : 'card';
        $extra_class = $s['css_class'] ? ' ' . esc_attr( $s['css_class'] ) : '';

        echo '<div class="amari-woo-single-card amari-woo-style-' . $style . $extra_class . '" style="text-align:' . esc_attr( $s['align'] ) . ';">';

        echo '<div class="amari-woo-card-img">';
        $img = get_the_post_thumbnail( $pid, 'amari-card' );
        echo '<a href="' . esc_url( get_permalink( $pid ) ) . '">';
        echo $img ?: '<div class="amari-woo-no-img"></div>';
        echo '</a>';
        if ( $s['show_badge'] && $product->is_on_sale() ) {
            echo '<span class="amari-woo-badge amari-woo-badge-sale">Sale</span>';
        }
        echo '</div>';

        echo '<div class="amari-woo-card-body">';
        echo '<a href="' . esc_url( get_permalink( $pid ) ) . '" class="amari-woo-card-title">' . esc_html( $product->get_name() ) . '</a>';

        if ( $s['show_desc'] ) {
            $desc = $product->get_short_description();
            if ( $desc ) {
                echo '<div class="amari-woo-card-desc">' . wp_kses_post( $desc ) . '</div>';
            }
        }

        if ( $s['show_price'] ) {
            echo '<div class="amari-woo-card-price">' . wp_kses_post( $product->get_price_html() ) . '</div>';
        }

        if ( $s['show_cart'] ) {
            if ( $product->is_in_stock() ) {
                echo '<a href="' . esc_url( $product->add_to_cart_url() ) . '"'
                    . ' class="amari-woo-card-btn amari-btn amari-btn-primary add_to_cart_button ajax_add_to_cart"'
                    . ' data-product_id="' . esc_attr( $pid ) . '">'
                    . __( 'Add to Cart', 'amari' )
                    . '</a>';
            } else {
                echo '<a href="' . esc_url( get_permalink( $pid ) ) . '" class="amari-woo-card-btn amari-btn amari-btn-secondary">' . __( 'View Product', 'amari' ) . '</a>';
            }
        }

        echo '</div>'; // .amari-woo-card-body
        echo '</div>'; // .amari-woo-single-card
    }
}

AmariBuilder::instance()->register_element( new AmariElementWooProductCard() );
