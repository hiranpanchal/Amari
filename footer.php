</main><!-- /#main-content -->

<footer class="amari-footer" id="site-footer">
    <div class="amari-container">
        <div class="amari-row">
            <div class="amari-col amari-col-1-3">
                <a href="<?php echo esc_url( home_url('/') ); ?>" class="amari-logo" style="color:#fff;display:inline-block;margin-bottom:12px;">
                    <?php bloginfo('name'); ?><span style="color:#e94560;">.</span>
                </a>
                <p style="font-size:0.9rem;"><?php bloginfo('description'); ?></p>
            </div>

            <?php if ( has_nav_menu('footer') ) : ?>
            <div class="amari-col amari-col-1-3">
                <h5 style="color:#fff;margin-bottom:16px;"><?php esc_html_e('Navigation', 'amari'); ?></h5>
                <?php wp_nav_menu([
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'amari-footer-nav',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                ]); ?>
            </div>
            <?php endif; ?>

            <div class="amari-col amari-col-1-3">
                <h5 style="color:#fff;margin-bottom:16px;"><?php esc_html_e('Get in Touch', 'amari'); ?></h5>
                <p style="font-size:0.9rem;"><?php esc_html_e('Have a project in mind? We\'d love to hear from you.', 'amari'); ?></p>
                <a href="<?php echo esc_url( get_permalink( get_page_by_path('contact') ) ); ?>" class="amari-btn amari-btn-primary amari-btn-sm" style="margin-top:12px;">
                    <?php esc_html_e('Contact Us', 'amari'); ?>
                </a>
            </div>
        </div>

        <div class="amari-footer-bottom">
            <p>
                &copy; <?php echo esc_html( date('Y') ); ?> <?php bloginfo('name'); ?>.
                <?php esc_html_e('Built with Amari.', 'amari'); ?>
                <?php if ( is_user_logged_in() ) : ?>
                    &mdash; <a href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e('Dashboard', 'amari'); ?></a>
                <?php endif; ?>
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
