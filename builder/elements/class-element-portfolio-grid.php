<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementPortfolioGrid extends AmariElement {
    public function get_type(): string  { return 'portfolio-grid'; }
    public function get_label(): string { return __( 'Portfolio Grid', 'amari' ); }
    public function get_group(): string { return 'advanced'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'columns'      => '3',
            'posts_per_page' => '9',
            'category'     => '',
            'show_filter'  => true,
            'show_title'   => true,
            'show_excerpt' => false,
            'gap'          => '24px',
            'css_class'    => '',
        ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'columns',       'type' => 'select', 'label' => 'Columns',
              'options' => [ ['value'=>'2','label'=>'2 Columns'], ['value'=>'3','label'=>'3 Columns'], ['value'=>'4','label'=>'4 Columns'] ] ],
            [ 'id' => 'posts_per_page','type' => 'number', 'label' => 'Items to Show' ],
            [ 'id' => 'show_filter',   'type' => 'toggle', 'label' => 'Show Filter Bar' ],
            [ 'id' => 'show_title',    'type' => 'toggle', 'label' => 'Show Title' ],
            [ 'id' => 'show_excerpt',  'type' => 'toggle', 'label' => 'Show Excerpt' ],
            [ 'id' => 'gap',           'type' => 'text',   'label' => 'Grid Gap', 'placeholder' => '24px' ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );

        $args = [
            'post_type'      => 'amari_portfolio',
            'posts_per_page' => intval( $s['posts_per_page'] ?: 9 ),
            'post_status'    => 'publish',
        ];
        if ( $s['category'] ) {
            $args['tax_query'] = [[ 'taxonomy' => 'amari_portfolio_cat', 'field' => 'slug', 'terms' => $s['category'] ]];
        }

        $query = new WP_Query( $args );
        $cols  = intval( $s['columns'] ?: 3 );
        $col_width = ( 100 / $cols ) . '%';
        $gap   = esc_attr( $s['gap'] ?: '24px' );

        // Filter bar
        if ( $s['show_filter'] ) {
            $terms = get_terms([ 'taxonomy' => 'amari_portfolio_cat', 'hide_empty' => true ]);
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                echo '<div class="amari-portfolio-filter" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:32px;">';
                echo '<button class="amari-filter-btn active" data-filter="*">All</button>';
                foreach ( $terms as $term ) {
                    echo '<button class="amari-filter-btn" data-filter="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</button>';
                }
                echo '</div>';
            }
        }

        // Grid
        echo '<div class="amari-portfolio-grid" style="display:flex;flex-wrap:wrap;gap:' . $gap . ';">';

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $thumb   = get_the_post_thumbnail_url( null, 'amari-card' );
                $terms   = get_the_terms( get_the_ID(), 'amari_portfolio_cat' );
                $slugs   = $terms ? implode( ' ', wp_list_pluck($terms, 'slug') ) : '';

                echo '<div class="amari-portfolio-item" data-categories="' . esc_attr($slugs) . '" style="width:calc(' . $col_width . ' - ' . $gap . ');flex-shrink:0;">';
                echo '<a href="' . esc_url(get_permalink()) . '" class="amari-portfolio-link">';
                echo '<div class="amari-portfolio-thumb" style="position:relative;overflow:hidden;border-radius:8px;aspect-ratio:4/3;">';
                if ( $thumb ) {
                    echo '<img src="' . esc_url($thumb) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy" style="width:100%;height:100%;object-fit:cover;transition:transform 0.4s ease;">';
                } else {
                    echo '<div style="width:100%;height:100%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#999;">No Image</div>';
                }
                echo '<div class="amari-portfolio-overlay" style="position:absolute;inset:0;background:rgba(26,26,46,0.7);opacity:0;transition:opacity 0.3s ease;display:flex;align-items:center;justify-content:center;">';
                echo '<span style="color:#fff;font-weight:600;font-size:0.9rem;">View Project →</span>';
                echo '</div>';
                echo '</div>';

                if ( $s['show_title'] || $s['show_excerpt'] ) {
                    echo '<div class="amari-portfolio-info" style="padding:12px 4px;">';
                    if ( $s['show_title'] ) echo '<h4 style="font-size:1rem;margin-bottom:4px;">' . esc_html(get_the_title()) . '</h4>';
                    if ( $s['show_excerpt'] ) echo '<p style="font-size:0.85rem;color:#666;margin:0;">' . esc_html(get_the_excerpt()) . '</p>';
                    if ( $terms && ! is_wp_error($terms) ) {
                        echo '<div style="margin-top:6px;">';
                        foreach ($terms as $t) {
                            echo '<span style="font-size:0.75rem;color:#e94560;background:#fef0f0;padding:2px 8px;border-radius:20px;margin-right:4px;">' . esc_html($t->name) . '</span>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                }
                echo '</a></div>';
            }
            wp_reset_postdata();
        } else {
            echo '<p style="color:#999;padding:40px;text-align:center;width:100%;">No portfolio items found.</p>';
        }

        echo '</div>'; // .amari-portfolio-grid
    }
}

AmariBuilder::instance()->register_element( new AmariElementPortfolioGrid() );
