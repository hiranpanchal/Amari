<?php
/**
 * Template for single Pages.
 * If the Amari Builder is enabled for this page, it renders
 * the builder output; otherwise falls back to the classic editor content.
 */

get_header();

while ( have_posts() ) :
    the_post();

    if ( amari_builder_is_enabled( get_the_ID() ) ) :
        // Render the builder output
        $builder_data = amari_get_builder_data( get_the_ID() );
        if ( $builder_data ) {
            AmariBuilder::instance()->render( $builder_data );
        }
    else :
        // Classic editor fallback
        ?>
        <div class="amari-container amari-no-builder">
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="amari-mb-4">
                    <h1><?php the_title(); ?></h1>
                </header>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        </div>
        <?php
    endif;

endwhile;

get_footer();
