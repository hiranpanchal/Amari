<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementPricingTable extends AmariElement {
    public function get_type(): string  { return 'pricing-table'; }
    public function get_label(): string { return __( 'Pricing Table', 'amari' ); }
    public function get_group(): string { return 'advanced'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'plans' => [
                [ 'name'=>'Starter', 'price'=>'29', 'period'=>'/mo', 'description'=>'Perfect for small businesses', 'badge'=>'', 'features'=>"5 Pages\n10GB Storage\nSSL Certificate\nEmail Support", 'button_label'=>'Get Started', 'button_url'=>'#', 'is_featured'=>false, 'bg_color'=>'', 'badge_color'=>'#6366f1' ],
                [ 'name'=>'Pro',     'price'=>'79', 'period'=>'/mo', 'description'=>'Best for growing businesses', 'badge'=>'Most Popular', 'features'=>"Unlimited Pages\n50GB Storage\nSSL Certificate\nPriority Support\nCustom Domain", 'button_label'=>'Get Started', 'button_url'=>'#', 'is_featured'=>true, 'bg_color'=>'#1a1a2e', 'badge_color'=>'#e94560' ],
                [ 'name'=>'Agency', 'price'=>'149', 'period'=>'/mo', 'description'=>'For teams and agencies', 'badge'=>'', 'features'=>"Unlimited Pages\n200GB Storage\nSSL Certificate\n24/7 Support\nCustom Domain\nWhite Label\nDedicated Manager", 'button_label'=>'Contact Us', 'button_url'=>'#', 'is_featured'=>false, 'bg_color'=>'', 'badge_color'=>'#6366f1' ],
            ],
            'currency'     => '£',
            'accent_color' => '#e94560',
            'css_class'    => '',
        ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'plans',    'type' => 'repeater', 'label' => 'Pricing Plans',
              'fields' => [ ['id'=>'name','type'=>'text','label'=>'Plan Name'], ['id'=>'price','type'=>'text','label'=>'Price'], ['id'=>'period','type'=>'text','label'=>'Period (/mo etc)'], ['id'=>'description','type'=>'text','label'=>'Tagline'], ['id'=>'badge','type'=>'text','label'=>'Badge (e.g. Most Popular)'], ['id'=>'features','type'=>'textarea','label'=>'Features (one per line)'], ['id'=>'button_label','type'=>'text','label'=>'Button Text'], ['id'=>'button_url','type'=>'url','label'=>'Button URL'], ['id'=>'is_featured','type'=>'toggle','label'=>'Featured / Highlighted'] ] ],
            [ 'id' => 'currency',     'type' => 'text',  'label' => 'Currency Symbol', 'placeholder' => '£' ],
            [ 'id' => 'accent_color', 'type' => 'color', 'label' => 'Accent Color' ],
        ];
    }

    public function render( array $settings ): void {
        $s     = $this->parse_settings( $settings );
        $plans = $s['plans'] ?? [];
        if ( empty( $plans ) ) return;

        $currency = esc_html( $s['currency'] ?: '£' );
        $accent   = esc_attr( $s['accent_color'] ?: '#e94560' );
        $cols     = count( $plans );
        $class    = 'amari-pricing-grid';
        if ( $s['css_class'] ) $class .= ' ' . esc_attr( $s['css_class'] );

        echo "<div class=\"{$class}\" style=\"display:grid;grid-template-columns:repeat({$cols},1fr);gap:24px;align-items:stretch;\">";

        foreach ( $plans as $plan ) {
            $featured = ! empty( $plan['is_featured'] );
            $bg       = $featured ? ( esc_attr($plan['bg_color'] ?? '#1a1a2e') ) : '#fff';
            $tc       = $featured ? '#fff' : '#333';
            $border   = $featured ? "border:2px solid {$accent};" : 'border:1px solid #e5e7eb;';
            $shadow   = $featured ? 'box-shadow:0 20px 60px rgba(233,69,96,.25);transform:scale(1.04);position:relative;z-index:2;' : 'box-shadow:0 2px 16px rgba(0,0,0,.06);';

            $name     = esc_html( $plan['name']         ?? '' );
            $price    = esc_html( $plan['price']        ?? '0' );
            $period   = esc_html( $plan['period']       ?? '/mo' );
            $desc     = esc_html( $plan['description']  ?? '' );
            $badge    = esc_html( $plan['badge']        ?? '' );
            $btn_lbl  = esc_html( $plan['button_label'] ?? 'Get Started' );
            $btn_url  = esc_url(  $plan['button_url']   ?? '#' );
            $features = array_filter( array_map( 'trim', explode( "\n", $plan['features'] ?? '' ) ) );
            $badge_c  = esc_attr( $plan['badge_color'] ?? '#e94560' );

            echo "<div class=\"amari-pricing-card\" style=\"background:{$bg};border-radius:16px;padding:36px 28px;display:flex;flex-direction:column;{$border}{$shadow}\">";

            if ( $badge ) {
                echo "<div style=\"background:{$badge_c};color:#fff;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;padding:4px 14px;border-radius:20px;display:inline-block;margin-bottom:16px;align-self:flex-start;\">{$badge}</div>";
            }

            echo "<h3 style=\"font-size:1.1rem;font-weight:700;color:{$tc};margin-bottom:6px;\">{$name}</h3>";
            if ( $desc ) echo "<p style=\"font-size:.85rem;color:" . ($featured?'rgba(255,255,255,.65)':'#999') . ";margin-bottom:20px;\">{$desc}</p>";

            echo "<div class=\"amari-pricing-price\" style=\"margin-bottom:24px;\">";
            echo "<span style=\"font-size:1rem;font-weight:600;color:{$accent};vertical-align:top;margin-top:10px;display:inline-block;\">{$currency}</span>";
            echo "<span style=\"font-size:3.5rem;font-weight:800;color:{$tc};line-height:1;\">{$price}</span>";
            echo "<span style=\"font-size:.9rem;color:" . ($featured?'rgba(255,255,255,.6)':'#999') . ";\">{$period}</span>";
            echo "</div>";

            echo "<ul style=\"list-style:none;padding:0;margin:0 0 28px;flex:1;\">";
            foreach ( $features as $feat ) {
                $check_color = $featured ? '#22c55e' : $accent;
                echo "<li style=\"display:flex;align-items:center;gap:10px;margin-bottom:10px;font-size:.9rem;color:{$tc};\">";
                echo "<svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"{$check_color}\" stroke-width=\"2.5\" style=\"flex-shrink:0;\"><polyline points=\"20 6 9 17 4 12\"/></svg>";
                echo esc_html( $feat );
                echo "</li>";
            }
            echo "</ul>";

            $btn_bg    = $featured ? $accent   : 'transparent';
            $btn_tc    = $featured ? '#fff'    : $accent;
            $btn_border = "2px solid {$accent}";
            echo "<a href=\"{$btn_url}\" style=\"display:block;text-align:center;padding:13px;background:{$btn_bg};color:{$btn_tc};border:{$btn_border};border-radius:8px;font-weight:700;font-size:.9rem;text-decoration:none;transition:all .2s;margin-top:auto;\">{$btn_lbl}</a>";

            echo "</div>";
        }

        echo "</div>";
    }
}

AmariBuilder::instance()->register_element( new AmariElementPricingTable() );
