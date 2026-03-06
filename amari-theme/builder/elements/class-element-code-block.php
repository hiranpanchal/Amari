<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementCodeBlock extends AmariElement {
    public function get_type(): string  { return 'code-block'; }
    public function get_label(): string { return __( 'Custom Code', 'amari' ); }
    public function get_group(): string { return 'advanced'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>';
    }

    public function get_defaults(): array {
        return [ 'code' => '', 'execute' => true ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'code',    'type' => 'textarea', 'label' => 'HTML / Shortcode / Embed Code' ],
            [ 'id' => 'execute', 'type' => 'toggle',   'label' => 'Execute / render output (uncheck to display as code)' ],
        ];
    }

    public function render( array $settings ): void {
        $s = $this->parse_settings( $settings );
        if ( empty( $s['code'] ) ) return;

        if ( $s['execute'] ) {
            // Run shortcodes, allow embeds
            echo do_shortcode( $s['code'] );
        } else {
            echo '<pre class="amari-code-display"><code>' . esc_html( $s['code'] ) . '</code></pre>';
        }
    }
}

AmariBuilder::instance()->register_element( new AmariElementCodeBlock() );
