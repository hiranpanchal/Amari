<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementVideo extends AmariElement {

    public function get_type(): string  { return 'video'; }
    public function get_label(): string { return __( 'Video', 'amari' ); }
    public function get_group(): string { return 'media'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'source'     => 'youtube',
            'url'        => '',
            'ratio'      => '16-9',
            'autoplay'   => false,
            'muted'      => false,
            'loop'       => false,
            'controls'   => true,
            'css_class'  => '',
            'animation'  => '',
        ];
    }

    public function get_controls(): array {
        return [
            [
                'id'      => 'source',
                'type'    => 'select',
                'label'   => __( 'Source', 'amari' ),
                'options' => [
                    [ 'value' => 'youtube', 'label' => 'YouTube' ],
                    [ 'value' => 'vimeo',   'label' => 'Vimeo' ],
                    [ 'value' => 'self',    'label' => 'Self-hosted' ],
                ],
            ],
            [
                'id'          => 'url',
                'type'        => 'url',
                'label'       => __( 'Video URL', 'amari' ),
                'placeholder' => 'https://www.youtube.com/watch?v=...',
            ],
            [
                'id'      => 'ratio',
                'type'    => 'select',
                'label'   => __( 'Aspect Ratio', 'amari' ),
                'options' => [
                    [ 'value' => '16-9', 'label' => '16:9 (Widescreen)' ],
                    [ 'value' => '4-3',  'label' => '4:3 (Standard)' ],
                    [ 'value' => '1-1',  'label' => '1:1 (Square)' ],
                    [ 'value' => '9-16', 'label' => '9:16 (Portrait/Reel)' ],
                ],
            ],
            [
                'id'    => 'autoplay',
                'type'  => 'toggle',
                'label' => __( 'Autoplay', 'amari' ),
            ],
            [
                'id'    => 'muted',
                'type'  => 'toggle',
                'label' => __( 'Muted', 'amari' ),
            ],
            [
                'id'    => 'loop',
                'type'  => 'toggle',
                'label' => __( 'Loop', 'amari' ),
            ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );

        if ( empty( $s['url'] ) ) {
            echo '<div class="amari-video-placeholder" style="background:#111;color:#fff;padding:40px;text-align:center;border-radius:8px;">▶ No video URL set</div>';
            return;
        }

        $class = 'amari-video-wrap amari-video-ratio-' . esc_attr( $s['ratio'] );
        if ( $s['css_class'] ) $class .= ' ' . esc_attr( $s['css_class'] );
        $anim  = $this->get_animation_attr( $s );

        echo '<div class="' . esc_attr( $class ) . '"' . $anim . '>';

        switch ( $s['source'] ) {
            case 'youtube':
                echo $this->render_youtube( $s );
                break;
            case 'vimeo':
                echo $this->render_vimeo( $s );
                break;
            default:
                echo $this->render_self_hosted( $s );
        }

        echo '</div>';
    }

    private function extract_youtube_id( string $url ): string {
        preg_match('/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{11})/', $url, $m);
        return $m[1] ?? '';
    }

    private function extract_vimeo_id( string $url ): string {
        preg_match('/vimeo\.com\/(\d+)/', $url, $m);
        return $m[1] ?? '';
    }

    private function render_youtube( array $s ): string {
        $id = $this->extract_youtube_id( $s['url'] );
        if ( ! $id ) return '<p>Invalid YouTube URL</p>';

        $params = [];
        if ( $s['autoplay'] ) $params['autoplay'] = '1';
        if ( $s['muted'] )    $params['mute']     = '1';
        if ( $s['loop'] )     { $params['loop'] = '1'; $params['playlist'] = $id; }
        if ( ! $s['controls'] ) $params['controls'] = '0';

        $src = 'https://www.youtube-nocookie.com/embed/' . $id;
        if ( $params ) $src .= '?' . http_build_query( $params );

        return '<iframe src="' . esc_url( $src ) . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
    }

    private function render_vimeo( array $s ): string {
        $id = $this->extract_vimeo_id( $s['url'] );
        if ( ! $id ) return '<p>Invalid Vimeo URL</p>';

        $params = [ 'dnt' => '1' ];
        if ( $s['autoplay'] ) $params['autoplay'] = '1';
        if ( $s['muted'] )    $params['muted']    = '1';
        if ( $s['loop'] )     $params['loop']     = '1';

        $src = 'https://player.vimeo.com/video/' . $id . '?' . http_build_query( $params );

        return '<iframe src="' . esc_url( $src ) . '" frameborder="0" allowfullscreen allow="autoplay; fullscreen" loading="lazy"></iframe>';
    }

    private function render_self_hosted( array $s ): string {
        $attrs  = '';
        if ( $s['autoplay'] ) $attrs .= ' autoplay';
        if ( $s['muted'] )    $attrs .= ' muted';
        if ( $s['loop'] )     $attrs .= ' loop';
        if ( $s['controls'] ) $attrs .= ' controls';

        return '<video src="' . esc_url( $s['url'] ) . '" style="width:100%;height:100%;object-fit:cover;"' . $attrs . '></video>';
    }
}

AmariBuilder::instance()->register_element( new AmariElementVideo() );
