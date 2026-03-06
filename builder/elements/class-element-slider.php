<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementSlider extends AmariElement {
    public function get_type(): string  { return 'slider'; }
    public function get_label(): string { return __( 'Image Slider', 'amari' ); }
    public function get_group(): string { return 'media'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><polyline points="9 18 15 12 9 6" style="opacity:.4"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'slides' => [
                [ 'image' => '', 'title' => 'Slide One',   'subtitle' => 'A compelling subtitle that draws visitors in', 'button_label' => 'Learn More', 'button_url' => '#', 'overlay' => '0.4' ],
                [ 'image' => '', 'title' => 'Slide Two',   'subtitle' => 'Showcase your best work and key messages here', 'button_label' => 'Get Started', 'button_url' => '#', 'overlay' => '0.4' ],
                [ 'image' => '', 'title' => 'Slide Three', 'subtitle' => 'Drive visitors toward your most important goal', 'button_label' => 'Contact Us', 'button_url' => '#', 'overlay' => '0.4' ],
            ],
            'height'       => '520px',
            'autoplay'     => true,
            'interval'     => '5000',
            'show_arrows'  => true,
            'show_dots'    => true,
            'transition'   => 'fade',
            'text_align'   => 'center',
            'text_color'   => '#ffffff',
            'css_class'    => '',
        ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'slides',      'type' => 'repeater', 'label' => 'Slides',
              'fields' => [ ['id'=>'image','type'=>'image','label'=>'Background Image'], ['id'=>'title','type'=>'text','label'=>'Title'], ['id'=>'subtitle','type'=>'text','label'=>'Subtitle'], ['id'=>'button_label','type'=>'text','label'=>'Button Text'], ['id'=>'button_url','type'=>'url','label'=>'Button URL'], ['id'=>'overlay','type'=>'text','label'=>'Overlay Opacity (0–1)'] ] ],
            [ 'id' => 'height',      'type' => 'text',     'label' => 'Slider Height', 'placeholder' => '520px' ],
            [ 'id' => 'autoplay',    'type' => 'toggle',   'label' => 'Autoplay' ],
            [ 'id' => 'interval',    'type' => 'text',     'label' => 'Autoplay Interval (ms)', 'placeholder' => '5000' ],
            [ 'id' => 'transition',  'type' => 'select',   'label' => 'Transition',
              'options' => [ ['value'=>'slide','label'=>'Slide'], ['value'=>'fade','label'=>'Fade'] ] ],
            [ 'id' => 'show_arrows', 'type' => 'toggle',   'label' => 'Show Arrows' ],
            [ 'id' => 'show_dots',   'type' => 'toggle',   'label' => 'Show Dots' ],
            [ 'id' => 'text_align',  'type' => 'select',   'label' => 'Text Alignment',
              'options' => [ ['value'=>'left','label'=>'Left'], ['value'=>'center','label'=>'Center'], ['value'=>'right','label'=>'Right'] ] ],
            [ 'id' => 'text_color',  'type' => 'color',    'label' => 'Text Color' ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );
        $slides   = $s['slides'] ?? [];
        if ( empty( $slides ) ) return;

        $id         = 'slider-' . uniqid();
        $height     = esc_attr( $s['height'] ?: '520px' );
        $transition = esc_attr( $s['transition'] ?: 'fade' );
        $autoplay   = $s['autoplay'] ? 'true' : 'false';
        $interval   = intval( $s['interval'] ?: 5000 );
        $text_align = esc_attr( $s['text_align'] ?: 'center' );
        $text_color = esc_attr( $s['text_color'] ?: '#fff' );
        $class      = 'amari-slider amari-slider--' . $transition;
        if ( $s['css_class'] ) $class .= ' ' . esc_attr( $s['css_class'] );

        echo "<div class=\"{$class}\" id=\"{$id}\" data-autoplay=\"{$autoplay}\" data-interval=\"{$interval}\" style=\"--slider-h:{$height};position:relative;overflow:hidden;height:{$height};\">";
        echo "<div class=\"amari-slider-track\" style=\"display:flex;height:100%;transition:" . ($transition==='slide' ? 'transform .6s cubic-bezier(.25,.46,.45,.94)' : 'none') . ";\">";

        foreach ( $slides as $idx => $slide ) {
            $bg_img  = ! empty( $slide['image'] ) ? 'background-image:url(' . esc_url( $slide['image'] ) . ');' : 'background:#1a1a2e;';
            $overlay = floatval( $slide['overlay'] ?? 0.4 );
            $title   = esc_html( $slide['title']        ?? '' );
            $sub     = esc_html( $slide['subtitle']     ?? '' );
            $btn_lbl = esc_html( $slide['button_label'] ?? '' );
            $btn_url = esc_url(  $slide['button_url']   ?? '#' );

            echo "<div class=\"amari-slide\" style=\"min-width:100%;height:100%;position:relative;{$bg_img}background-size:cover;background-position:center;flex-shrink:0;\">";
            echo "<div style=\"position:absolute;inset:0;background:rgba(0,0,0,{$overlay});\"></div>";
            echo "<div class=\"amari-slide-content\" style=\"position:relative;z-index:2;height:100%;display:flex;flex-direction:column;align-items:" . ($text_align==='center'?'center':($text_align==='right'?'flex-end':'flex-start')) . ";justify-content:center;text-align:{$text_align};padding:40px 60px;color:{$text_color};\">";
            if ( $title ) echo "<h2 style=\"font-size:clamp(1.6rem,4vw,3rem);font-weight:800;margin-bottom:12px;color:{$text_color};\">{$title}</h2>";
            if ( $sub )   echo "<p style=\"font-size:1.1rem;opacity:.9;max-width:560px;margin-bottom:24px;\">{$sub}</p>";
            if ( $btn_lbl ) echo "<a href=\"{$btn_url}\" class=\"amari-btn amari-btn-primary\">{$btn_lbl}</a>";
            echo "</div></div>";
        }

        echo "</div>"; // .track

        if ( $s['show_arrows'] && count($slides) > 1 ) {
            echo "<button class=\"amari-slider-arrow amari-slider-prev\" aria-label=\"Previous\"><svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.5\"><polyline points=\"15 18 9 12 15 6\"/></svg></button>";
            echo "<button class=\"amari-slider-arrow amari-slider-next\" aria-label=\"Next\"><svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.5\"><polyline points=\"9 18 15 12 9 6\"/></svg></button>";
        }

        if ( $s['show_dots'] && count($slides) > 1 ) {
            echo "<div class=\"amari-slider-dots\" style=\"position:absolute;bottom:20px;left:50%;transform:translateX(-50%);display:flex;gap:8px;z-index:10;\">";
            foreach ( $slides as $idx => $_ ) {
                echo "<button class=\"amari-slider-dot" . ( $idx===0 ? ' is-active' : '' ) . "\" data-index=\"{$idx}\" aria-label=\"Slide " . ($idx+1) . "\"></button>";
            }
            echo "</div>";
        }

        echo "</div>"; // .slider
    }
}

AmariBuilder::instance()->register_element( new AmariElementSlider() );
