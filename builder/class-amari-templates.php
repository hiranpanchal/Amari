<?php
/**
 * Amari Template Library
 *
 * Provides a curated library of pre-built section templates
 * that can be inserted into any page via the builder UI.
 * Templates are defined as builder JSON (sections array) so they
 * slot directly into the existing data model.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariTemplates {

    /** @var AmariTemplates|null */
    private static $instance = null;

    public static function instance(): AmariTemplates {
        if ( ! self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_ajax_amari_get_templates',    [ $this, 'ajax_get_templates' ] );
        add_action( 'wp_ajax_amari_get_template',     [ $this, 'ajax_get_template' ] );
    }

    /* ── AJAX ────────────────────────────────────────────────── */

    public function ajax_get_templates(): void {
        check_ajax_referer( 'amari_builder_nonce', 'nonce' );
        wp_send_json_success( $this->get_template_index() );
    }

    public function ajax_get_template(): void {
        check_ajax_referer( 'amari_builder_nonce', 'nonce' );
        $id = sanitize_key( $_POST['template_id'] ?? '' );
        $tpl = $this->get_template( $id );
        if ( ! $tpl ) wp_send_json_error( [ 'message' => 'Template not found.' ] );
        wp_send_json_success( $tpl );
    }

    /* ── Public API ──────────────────────────────────────────── */

    public function get_template_index(): array {
        $index = [];
        foreach ( $this->get_all_templates() as $id => $tpl ) {
            $index[] = [
                'id'          => $id,
                'name'        => $tpl['name'],
                'category'    => $tpl['category'],
                'description' => $tpl['description'] ?? '',
                'thumbnail'   => $tpl['thumbnail']   ?? '',
                'tags'        => $tpl['tags']         ?? [],
            ];
        }
        return $index;
    }

    public function get_template( string $id ): ?array {
        return $this->get_all_templates()[$id] ?? null;
    }

    /* ── Template Definitions ────────────────────────────────── */

    private function get_all_templates(): array {
        $uid = function() { return '_' . substr( md5( uniqid('', true) ), 0, 8 ); };

        return apply_filters( 'amari_templates', [

            /* ══ HERO ══════════════════════════════════════════ */

            'hero-bold' => [
                'name'        => 'Bold Hero',
                'category'    => 'Hero',
                'description' => 'Full-width dark hero with headline, sub and two CTA buttons',
                'tags'        => ['hero','dark','bold'],
                'sections'    => [
                    [
                        'id'       => $uid(),
                        'settings' => [ 'bg_color' => '#1a1a2e', 'padding' => '100px 0', 'full_width' => false ],
                        'rows'     => [[
                            'id'      => $uid(),
                            'columns' => [[
                                'id'   => $uid(), 'size' => '1-1', 'elements' => [
                                    [ 'id'=>$uid(), 'type'=>'heading',    'settings'=>[ 'text'=>'We Build Brands That Stand Out', 'tag'=>'h1', 'align'=>'center', 'color'=>'#ffffff', 'font_size'=>'clamp(2rem,5vw,4rem)', 'font_weight'=>'800' ] ],
                                    [ 'id'=>$uid(), 'type'=>'text-block', 'settings'=>[ 'content'=>'<p>A full-service creative studio specialising in brand identity, web design, and digital strategy. Let\'s create something remarkable together.</p>', 'align'=>'center', 'color'=>'rgba(255,255,255,0.75)', 'font_size'=>'1.15rem' ] ],
                                    [ 'id'=>$uid(), 'type'=>'spacer',     'settings'=>[ 'height'=>'24px' ] ],
                                    [ 'id'=>$uid(), 'type'=>'button',     'settings'=>[ 'label'=>'Start a Project', 'url'=>'#contact', 'style'=>'primary', 'align'=>'center', 'size'=>'lg' ] ],
                                ],
                            ]],
                        ]],
                    ],
                ],
            ],

            'hero-split' => [
                'name'        => 'Split Hero',
                'category'    => 'Hero',
                'description' => 'Two-column hero — text left, image right',
                'tags'        => ['hero','split','image'],
                'sections'    => [
                    [
                        'id'       => $uid(),
                        'settings' => [ 'bg_color' => '#f8f9fa', 'padding' => '80px 0' ],
                        'rows'     => [[
                            'id'      => $uid(),
                            'columns' => [
                                [
                                    'id'=>$uid(), 'size'=>'1-2', 'elements'=>[
                                        [ 'id'=>$uid(), 'type'=>'heading',    'settings'=>[ 'text'=>'Design-Led Digital Experiences', 'tag'=>'h1', 'align'=>'left', 'font_weight'=>'800', 'font_size'=>'2.8rem' ] ],
                                        [ 'id'=>$uid(), 'type'=>'text-block', 'settings'=>[ 'content'=>'<p>We partner with ambitious businesses to create websites, apps, and brand identities that drive real results.</p>', 'align'=>'left', 'color'=>'#555' ] ],
                                        [ 'id'=>$uid(), 'type'=>'spacer',     'settings'=>[ 'height'=>'20px' ] ],
                                        [ 'id'=>$uid(), 'type'=>'button',     'settings'=>[ 'label'=>'View Our Work', 'url'=>'#portfolio', 'style'=>'primary', 'align'=>'left' ] ],
                                    ],
                                ],
                                [
                                    'id'=>$uid(), 'size'=>'1-2', 'elements'=>[
                                        [ 'id'=>$uid(), 'type'=>'image', 'settings'=>[ 'url'=>'', 'alt'=>'Hero Image', 'align'=>'center', 'border_radius'=>'16px', 'width'=>'100%' ] ],
                                    ],
                                ],
                            ],
                        ]],
                    ],
                ],
            ],

            /* ══ FEATURES ══════════════════════════════════════ */

            'features-3col' => [
                'name'        => '3-Column Features',
                'category'    => 'Features',
                'description' => 'Three icon boxes highlighting key services or features',
                'tags'        => ['features','icons','services'],
                'sections'    => [
                    [
                        'id'       => $uid(),
                        'settings' => [ 'bg_color' => '#ffffff', 'padding' => '80px 0' ],
                        'rows'     => [
                            [
                                'id'      => $uid(),
                                'columns' => [[
                                    'id'=>$uid(), 'size'=>'1-1', 'elements'=>[
                                        [ 'id'=>$uid(), 'type'=>'heading',    'settings'=>[ 'text'=>'Why Choose Amari', 'tag'=>'h2', 'align'=>'center', 'font_weight'=>'800' ] ],
                                        [ 'id'=>$uid(), 'type'=>'text-block', 'settings'=>[ 'content'=>'<p>We combine beautiful design with proven strategy to deliver results that matter.</p>', 'align'=>'center', 'color'=>'#666' ] ],
                                        [ 'id'=>$uid(), 'type'=>'spacer',     'settings'=>[ 'height'=>'48px' ] ],
                                    ],
                                ]],
                            ],
                            [
                                'id'      => $uid(),
                                'columns' => [
                                    [ 'id'=>$uid(), 'size'=>'1-3', 'elements'=>[ [ 'id'=>$uid(), 'type'=>'icon-box', 'settings'=>[ 'icon'=>'🎨', 'title'=>'Beautiful Design', 'content'=>'Every pixel crafted with intention. We balance aesthetics with usability to create interfaces people love.', 'align'=>'center', 'icon_color'=>'#e94560', 'icon_size'=>'2.5rem' ] ] ] ],
                                    [ 'id'=>$uid(), 'size'=>'1-3', 'elements'=>[ [ 'id'=>$uid(), 'type'=>'icon-box', 'settings'=>[ 'icon'=>'⚡', 'title'=>'Fast Performance', 'content'=>'Optimised for speed at every level — from server configuration to front-end rendering.', 'align'=>'center', 'icon_color'=>'#6366f1', 'icon_size'=>'2.5rem' ] ] ] ],
                                    [ 'id'=>$uid(), 'size'=>'1-3', 'elements'=>[ [ 'id'=>$uid(), 'type'=>'icon-box', 'settings'=>[ 'icon'=>'📈', 'title'=>'Growth Focused', 'content'=>'Data-driven decisions that align with your business objectives and drive measurable growth.', 'align'=>'center', 'icon_color'=>'#22c55e', 'icon_size'=>'2.5rem' ] ] ] ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            /* ══ ABOUT ═════════════════════════════════════════ */

            'about-split' => [
                'name'        => 'About Split',
                'category'    => 'About',
                'description' => 'Image left, text and stats right',
                'tags'        => ['about','story','split'],
                'sections'    => [
                    [
                        'id'       => $uid(),
                        'settings' => [ 'bg_color' => '#f8f9fa', 'padding' => '80px 0' ],
                        'rows'     => [[
                            'id'      => $uid(),
                            'columns' => [
                                [
                                    'id'=>$uid(), 'size'=>'1-2', 'elements'=>[
                                        [ 'id'=>$uid(), 'type'=>'image', 'settings'=>[ 'url'=>'', 'alt'=>'About Us', 'align'=>'center', 'border_radius'=>'12px', 'width'=>'100%' ] ],
                                    ],
                                ],
                                [
                                    'id'=>$uid(), 'size'=>'1-2', 'elements'=>[
                                        [ 'id'=>$uid(), 'type'=>'heading',    'settings'=>[ 'text'=>'Our Story', 'tag'=>'h6', 'color'=>'#e94560', 'font_size'=>'0.85rem', 'font_weight'=>'700' ] ],
                                        [ 'id'=>$uid(), 'type'=>'heading',    'settings'=>[ 'text'=>'Two Decades of Creative Excellence', 'tag'=>'h2', 'font_weight'=>'800', 'font_size'=>'2.2rem' ] ],
                                        [ 'id'=>$uid(), 'type'=>'text-block', 'settings'=>[ 'content'=>'<p>Founded in 2004, we\'ve grown from a small design studio into a full-service creative agency. Our team of 40+ specialists brings together expertise in branding, web development, and digital strategy.</p><p>We believe great design is more than just aesthetics — it\'s a business tool that drives growth, builds trust, and creates meaningful connections between brands and their audiences.</p>', 'color'=>'#555' ] ],
                                        [ 'id'=>$uid(), 'type'=>'counter',    'settings'=>[ 'stats'=>[ ['value'=>'250','suffix'=>'+','label'=>'Projects Delivered','icon'=>'🚀'], ['value'=>'98','suffix'=>'%','label'=>'Client Satisfaction','icon'=>'⭐'] ], 'align'=>'left', 'number_color'=>'#e94560', 'number_size'=>'2.5rem' ] ],
                                    ],
                                ],
                            ],
                        ]],
                    ],
                ],
            ],

            /* ══ STATS ═════════════════════════════════════════ */

            'stats-dark' => [
                'name'        => 'Stats Dark',
                'category'    => 'Stats',
                'description' => 'Animated counter block on dark background',
                'tags'        => ['stats','counter','dark'],
                'sections'    => [
                    [
                        'id'       => $uid(),
                        'settings' => [ 'bg_color' => '#1a1a2e', 'padding' => '80px 0' ],
                        'rows'     => [[
                            'id'      => $uid(),
                            'columns' => [[
                                'id'=>$uid(), 'size'=>'1-1', 'elements'=>[
                                    [ 'id'=>$uid(), 'type'=>'counter', 'settings'=>[ 'number_color'=>'#e94560', 'label_color'=>'rgba(255,255,255,0.65)', 'number_size'=>'3.5rem', 'align'=>'center', 'stats'=>[ ['value'=>'250','suffix'=>'+','label'=>'Projects','icon'=>'🚀'], ['value'=>'40','suffix'=>'+','label'=>'Team Members','icon'=>'👥'], ['value'=>'15','suffix'=>'yr','label'=>'Experience','icon'=>'🏆'], ['value'=>'98','suffix'=>'%','label'=>'Satisfaction','icon'=>'⭐'] ] ] ],
                                ],
                            ]],
                        ]],
                    ],
                ],
            ],

            /* ══ TESTIMONIALS ══════════════════════════════════ */

            'testimonials-3col' => [
                'name'        => '3 Testimonials',
                'category'    => 'Testimonials',
                'description' => 'Three testimonial cards side by side',
                'tags'        => ['testimonials','reviews','social proof'],
                'sections'    => [
                    [
                        'id'       => $uid(),
                        'settings' => [ 'bg_color' => '#f8f9fa', 'padding' => '80px 0' ],
                        'rows'     => [
                            [ 'id'=>$uid(), 'columns'=>[[ 'id'=>$uid(), 'size'=>'1-1', 'elements'=>[
                                [ 'id'=>$uid(), 'type'=>'heading', 'settings'=>[ 'text'=>'What Our Clients Say', 'tag'=>'h2', 'align'=>'center', 'font_weight'=>'800' ] ],
                                [ 'id'=>$uid(), 'type'=>'spacer',  'settings'=>[ 'height'=>'40px' ] ],
                            ]]] ],
                            [ 'id'=>$uid(), 'columns'=>[
                                [ 'id'=>$uid(), 'size'=>'1-3', 'elements'=>[ [ 'id'=>$uid(), 'type'=>'testimonial', 'settings'=>[ 'quote'=>'Working with this team was an absolute pleasure. They delivered beyond our expectations, on time and on budget.', 'name'=>'Sarah Mitchell', 'role'=>'CEO, TechVentures', 'rating'=>'5', 'style'=>'card' ] ] ] ],
                                [ 'id'=>$uid(), 'size'=>'1-3', 'elements'=>[ [ 'id'=>$uid(), 'type'=>'testimonial', 'settings'=>[ 'quote'=>'The rebrand completely transformed how our customers perceive us. We\'ve seen a 40% increase in enquiries since launch.', 'name'=>'James Clarke', 'role'=>'MD, Clarke & Sons', 'rating'=>'5', 'style'=>'card' ] ] ] ],
                                [ 'id'=>$uid(), 'size'=>'1-3', 'elements'=>[ [ 'id'=>$uid(), 'type'=>'testimonial', 'settings'=>[ 'quote'=>'Professional, creative, and genuinely invested in our success. We wouldn\'t hesitate to recommend them to anyone.', 'name'=>'Emily Watson', 'role'=>'Marketing Dir, Bloom Co.', 'rating'=>'5', 'style'=>'card' ] ] ] ],
                            ]],
                        ],
                    ],
                ],
            ],

            /* ══ PRICING ════════════════════════════════════════ */

            'pricing-3plan' => [
                'name'        => '3-Plan Pricing',
                'category'    => 'Pricing',
                'description' => 'Three pricing tiers with features lists',
                'tags'        => ['pricing','plans','saas'],
                'sections'    => [
                    [
                        'id'       => $uid(),
                        'settings' => [ 'bg_color' => '#ffffff', 'padding' => '80px 0' ],
                        'rows'     => [
                            [ 'id'=>$uid(), 'columns'=>[[ 'id'=>$uid(), 'size'=>'1-1', 'elements'=>[
                                [ 'id'=>$uid(), 'type'=>'heading', 'settings'=>[ 'text'=>'Simple, Transparent Pricing', 'tag'=>'h2', 'align'=>'center', 'font_weight'=>'800' ] ],
                                [ 'id'=>$uid(), 'type'=>'text-block', 'settings'=>[ 'content'=>'<p>No hidden fees. Cancel anytime. Start free for 14 days.</p>', 'align'=>'center', 'color'=>'#666' ] ],
                                [ 'id'=>$uid(), 'type'=>'spacer', 'settings'=>[ 'height'=>'48px' ] ],
                            ]]] ],
                            [ 'id'=>$uid(), 'columns'=>[[ 'id'=>$uid(), 'size'=>'1-1', 'elements'=>[
                                [ 'id'=>$uid(), 'type'=>'pricing-table', 'settings'=>[] ],
                            ]]] ],
                        ],
                    ],
                ],
            ],

            /* ══ CTA ════════════════════════════════════════════ */

            'cta-centered' => [
                'name'        => 'Centred CTA',
                'category'    => 'CTA',
                'description' => 'Bold call-to-action with heading, text, and button',
                'tags'        => ['cta','conversion'],
                'sections'    => [
                    [
                        'id'       => $uid(),
                        'settings' => [ 'bg_color' => '#e94560', 'padding' => '80px 0' ],
                        'rows'     => [[
                            'id'      => $uid(),
                            'columns' => [[
                                'id'=>$uid(), 'size'=>'1-1', 'elements'=>[
                                    [ 'id'=>$uid(), 'type'=>'heading',    'settings'=>[ 'text'=>'Ready to Transform Your Online Presence?', 'tag'=>'h2', 'align'=>'center', 'color'=>'#ffffff', 'font_weight'=>'800', 'font_size'=>'2.5rem' ] ],
                                    [ 'id'=>$uid(), 'type'=>'text-block', 'settings'=>[ 'content'=>'<p>Join hundreds of businesses who\'ve already unlocked their full digital potential with Amari.</p>', 'align'=>'center', 'color'=>'rgba(255,255,255,0.85)' ] ],
                                    [ 'id'=>$uid(), 'type'=>'spacer',     'settings'=>[ 'height'=>'24px' ] ],
                                    [ 'id'=>$uid(), 'type'=>'button',     'settings'=>[ 'label'=>'Get in Touch', 'url'=>'#contact', 'style'=>'secondary', 'align'=>'center', 'size'=>'lg' ] ],
                                ],
                            ]],
                        ]],
                    ],
                ],
            ],

            /* ══ FAQ ════════════════════════════════════════════ */

            'faq-accordion' => [
                'name'        => 'FAQ Accordion',
                'category'    => 'FAQ',
                'description' => 'Frequently asked questions in accordion layout',
                'tags'        => ['faq','accordion','questions'],
                'sections'    => [
                    [
                        'id'       => $uid(),
                        'settings' => [ 'bg_color' => '#f8f9fa', 'padding' => '80px 0' ],
                        'rows'     => [[
                            'id'      => $uid(),
                            'columns' => [
                                [ 'id'=>$uid(), 'size'=>'1-3', 'elements'=>[
                                    [ 'id'=>$uid(), 'type'=>'heading',    'settings'=>[ 'text'=>'Frequently Asked', 'tag'=>'h2', 'font_weight'=>'800' ] ],
                                    [ 'id'=>$uid(), 'type'=>'text-block', 'settings'=>[ 'content'=>'<p>Everything you need to know. Can\'t find your answer? <a href="#">Get in touch</a>.</p>', 'color'=>'#666' ] ],
                                ]],
                                [ 'id'=>$uid(), 'size'=>'2-3', 'elements'=>[
                                    [ 'id'=>$uid(), 'type'=>'accordion', 'settings'=>[ 'open_first'=>true, 'style'=>'default' ] ],
                                ]],
                            ],
                        ]],
                    ],
                ],
            ],

            /* ══ CONTACT ════════════════════════════════════════ */

            'contact-split' => [
                'name'        => 'Contact Split',
                'category'    => 'Contact',
                'description' => 'Contact form alongside address and info',
                'tags'        => ['contact','form'],
                'sections'    => [
                    [
                        'id'       => $uid(),
                        'settings' => [ 'bg_color' => '#ffffff', 'padding' => '80px 0' ],
                        'rows'     => [[
                            'id'      => $uid(),
                            'columns' => [
                                [ 'id'=>$uid(), 'size'=>'1-2', 'elements'=>[
                                    [ 'id'=>$uid(), 'type'=>'heading',    'settings'=>[ 'text'=>'Let\'s Talk', 'tag'=>'h2', 'font_weight'=>'800' ] ],
                                    [ 'id'=>$uid(), 'type'=>'text-block', 'settings'=>[ 'content'=>'<p>Whether you have a project in mind or just want to say hello — we\'d love to hear from you.</p><p>📍 123 Studio Road, London, W1A 1AA<br>📞 +44 20 7123 4567<br>✉️ hello@yourstudio.com</p>', 'color'=>'#555' ] ],
                                ]],
                                [ 'id'=>$uid(), 'size'=>'1-2', 'elements'=>[
                                    [ 'id'=>$uid(), 'type'=>'contact-form', 'settings'=>[ 'show_name'=>true, 'show_phone'=>true, 'show_subject'=>true, 'submit_text'=>'Send Message' ] ],
                                ]],
                            ],
                        ]],
                    ],
                ],
            ],

        ]);
    }
}
