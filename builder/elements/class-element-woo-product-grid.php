<?php
/**
 * WooCommerce Product Grid Element
 * Displays a grid of WooCommerce products using a WP_Query.
 *
 * @package Amari
 * @since   3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementWooProductGrid extends AmariElement {

    public function get_type(): string  { return 'woo-product-grid'; }
    public function get_label(): string { return __( 'Product Grid', 'amari' ); }
    public function get_group(): string { return 'woocommerce'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="9" height="9" rx="1"/><rect x="13" y="2" width="9" height="9" rx="1"/><rect x="2" y="13" width="9" height="9" rx="1"/><rect x="13" y="13" width="9" height="9" rx="1"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'heading'    => '',
            'columns'    => '3',
            'per_page'   => '6',
            'orderby'    => 'date',
            'order'      => 'DESC',
            'category'   => '',
            'show_price' => true,
            'show_cart'  => true,
            'show_badge' => true,
            'css_class'  => '',
        ];
    }

    public function get_controls(): array {
        return [
            [
                'id'          => 'heading',
                'type'        => 'text',
                'label'       => __( 'Section Heading', 'amari' ),
                'placeholder' => 'Our Products',
            ],
            [
                'id'      => 'columns',
                'type'    => 'select',
                'label'   => __( 'Columns', 'amari' ),
                'options' => [
                    [ 'value' => '2', 'label' => '2 Columns' ],
                    [ 'value' => '3', 'label' => '3 Columns' ],
                    [ 'value' => '4', 'label' => '4 Columns' ],
                    [ 'value' => '5', 'label' => '5 Columns' ],
                ],
            ],
            [
                'id'          => 'per_page',
                'type'        => 'number',
                'label'       => __( 'Products Per Page', 'amari' ),
                'placeholder' => '6',
            ],
            [
                'id'      => 'orderby',
                'type'    => 'select',
                'label'   => __( 'Sort By', 'amari' ),
                'options' => [
                    [ 'value' => 'date',       'label' => 'Newest' ],
                    [ 'value' => 'popularity', 'label' => 'Popularity' ],
                    [ 'value' => 'rating',     'label' => 'Rating' ],
                    [ 'value' => 'price',      'label' => 'Price: Low to High' ],
                    [ 'value' => 'price-desc', 'label' => 'Price: High to Low' ],
                    [ 'value' => 'rand',       'label' => 'Random' ],
                ],
            ],
            [
                'id'          => 'category',
                'type'        => 'text',
                'label'       => __( 'Category Slug (optional)', 'amari' ),
                'placeholder' => 'e.g. clothing',
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
                'label' => __( 'Show Sale / New Badge', 'amari' ),
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

        // WooCommerce must be active
        if ( ! class_exists( 'WooCommerce' ) ) {
            echo '<div class="amari-woo-notice"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e94560" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> WooCommerce is not active.</div>';
            return;
        }

        $cols     = max( 1, min( 6, (int) $s['columns'] ) );
        $per_page = max( 1, min( 50, (int) $s['per_page'] ) );

        // Build WP_Query args
        $query_args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'meta_query'     => [ [ 'key' => '_visibility', 'value' => [ 'catalog', 'visible' ], 'compare' => 'IN' ] ],
        ];

        // Order handling (WC uses meta_key for price)
        switch ( $s['orderby'] ) {
            case 'price':
                $query_args['orderby']  = 'meta_value_num';
                $query_args['meta_key'] = '_price';
                $query_args['order']    = 'ASC';
                break;
            case 'price-desc':
                $query_args['orderby']  = 'meta_value_num';
                $query_args['meta_key'] = '_price';
                $query_args['order']    = 'DESC';
                break;
            case 'popularity':
                $query_args['orderby']  = 'meta_value_num';
                $query_args['meta_key'] = 'total_sales';
                $query_args['order']    = 'DESC';
                break;
            case 'rating':
                $query_args['orderby']  = 'meta_value_num';
                $query_args['meta_key'] = '_wc_average_rating';
                $query_args['order']    = 'DESC';
                break;
            default:
                $query_args['orderby'] = $s['orderby'] ?: 'date';
                $query_args['order']   = 'DESC';
        }

        if ( ! empty( $s['category'] ) ) {
            $query_args['tax_query'] = [ [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $s['category'] ),
            ] ];
        }

        $products = new WP_Query( $query_args );

        $extra_class = $s['css_class'] ? ' ' . esc_attr( $s['css_class'] ) : '';

        echo '<div class="amari-woo-product-grid' . $extra_class . '">';

        if ( ! empty( $s['heading'] ) ) {
            echo '<h2 class="amari-woo-grid-heading">' . esc_html( $s['heading'] ) . '</h2>';
        }

        if ( ! $products->have_posts() ) {
            echo '<p class="amari-woo-empty">No products found.</p>';
        } else {
            echo '<div class="amari-woo-grid" style="--woo-cols:' . $cols . ';">';
            while ( $products->have_posts() ) {
                $products->the_post();
                global $product;
                if ( ! $product ) {
                    $product = wc_get_product( get_the_ID() );
                }
                if ( ! $product ) continue;

                $this->render_product_card( $product, $s );
            }
            wp_reset_postdata();
            echo '</div>';
        }

        echo '</div>';
    }

    private function render_product_card( \WC_Product $product, array $s ): void {
        $id       = $product->get_id();
        $title    = $product->get_name();
        $price    = $product->get_price_html();
        $link     = get_permalink( $id );
        $img      = get_the_post_thumbnail( $id, 'amari-card' );
        $is_sale  = $product->is_on_sale();
        $is_new   = ( strtotime( $product->get_date_created() ) > strtotime( '-30 days' ) );
        $in_stock = $product->is_in_stock();

        echo '<div class="amari-woo-card">';

        // Image / thumbnail
        echo '<div class="amari-woo-card-img">';
        echo '<a href="' . esc_url( $link ) . '" tabindex="-1" aria-hidden="true">';
        if ( $img ) {
            echo $img;
        } else {
            echo '<div class="amari-woo-no-img"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>';
        }
        echo '</a>';

        // Badges
        if ( $s['show_badge'] ) {
            if ( $is_sale ) {
                echo '<span class="amari-woo-badge amari-woo-badge-sale">Sale</span>';
            } elseif ( $is_new ) {
                echo '<span class="amari-woo-badge amari-woo-badge-new">New</span>';
            }
        }

        echo '</div>'; // .amari-woo-card-img

        // Card body
        echo '<div class="amari-woo-card-body">';
        echo '<a href="' . esc_url( $link ) . '" class="amari-woo-card-title">' . esc_html( $title ) . '</a>';

        if ( $s['show_price'] ) {
            echo '<div class="amari-woo-card-price">' . wp_kses_post( $price ) . '</div>';
        }

        if ( $s['show_cart'] ) {
            if ( $in_stock ) {
                echo '<a href="' . esc_url( $product->add_to_cart_url() ) . '"'
                    . ' class="amari-woo-card-btn amari-btn amari-btn-primary add_to_cart_button ajax_add_to_cart"'
                    . ' data-product_id="' . esc_attr( $id ) . '"'
                    . ' aria-label="' . esc_attr( sprintf( __( 'Add %s to cart', 'amari' ), $title ) ) . '">'
                    . __( 'Add to Cart', 'amari' )
                    . '</a>';
            } else {
                echo '<span class="amari-woo-card-btn amari-woo-out-of-stock">' . __( 'Out of Stock', 'amari' ) . '</span>';
            }
        }

        echo '</div>'; // .amari-woo-card-body
        echo '</div>'; // .amari-woo-card
    }
}

AmariBuilder::instance()->register_element( new AmariElementWooProductGrid() );
