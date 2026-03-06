<?php get_header(); ?>

<div class="amari-container amari-no-builder">
    <?php if ( have_posts() ) : ?>
        <div class="amari-row">
            <?php while ( have_posts() ) : the_post(); ?>
                <div class="amari-col amari-col-1-3" style="margin-bottom:32px;">
                    <article id="post-<?php the_ID(); ?>" <?php post_class('amari-card'); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('amari-card', ['style' => 'width:100%;border-radius:8px 8px 0 0;']); ?>
                            </a>
                        <?php endif; ?>
                        <div style="padding:20px;">
                            <h2 style="font-size:1.2rem;margin-bottom:8px;">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <div style="font-size:0.85rem;color:#999;margin-bottom:12px;">
                                <?php the_time('F j, Y'); ?>
                            </div>
                            <p style="font-size:0.9rem;color:#666;"><?php the_excerpt(); ?></p>
                            <a href="<?php the_permalink(); ?>" class="amari-btn amari-btn-secondary amari-btn-sm" style="margin-top:12px;">
                                <?php esc_html_e('Read More', 'amari'); ?>
                            </a>
                        </div>
                    </article>
                </div>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination([ 'mid_size' => 2 ]); ?>

    <?php else : ?>
        <p><?php esc_html_e('No posts found.', 'amari'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
