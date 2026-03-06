<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( is_user_logged_in() && current_user_can('edit_posts') && is_singular() ) : ?>
<div class="amari-edit-bar">
    <span>✏️ <strong>Amari</strong></span>
    <a href="<?php echo esc_url( get_edit_post_link() ); ?>">Edit Page</a>
    <?php if ( amari_builder_is_enabled( get_the_ID() ) ) : ?>
        <a href="<?php echo esc_url( add_query_arg('amari_builder', '1', get_edit_post_link()) ); ?>">⚡ Open Builder</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<header class="amari-header" id="site-header">
    <div class="amari-container">
        <div class="amari-header-inner">

            <a href="<?php echo esc_url( home_url('/') ); ?>" class="amari-logo">
                <?php if ( has_custom_logo() ) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <?php bloginfo('name'); ?><span>.</span>
                <?php endif; ?>
            </a>

            <?php if ( has_nav_menu('primary') ) : ?>
                <nav class="amari-nav-wrapper" role="navigation" aria-label="<?php esc_attr_e('Primary Navigation', 'amari'); ?>">
                    <button class="amari-nav-toggle" aria-expanded="false" aria-controls="amari-primary-nav" aria-label="<?php esc_attr_e('Toggle menu', 'amari'); ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <?php wp_nav_menu([
                        'theme_location' => 'primary',
                        'menu_id'        => 'amari-primary-nav',
                        'container'      => false,
                        'menu_class'     => 'amari-nav',
                        'fallback_cb'    => false,
                        'depth'          => 2,
                    ]); ?>
                </nav>
            <?php endif; ?>

        </div>
    </div>
</header>

<main id="main-content" class="amari-main">
