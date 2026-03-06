<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AmariElementContactForm extends AmariElement {
    public function get_type(): string  { return 'contact-form'; }
    public function get_label(): string { return __( 'Contact Form', 'amari' ); }
    public function get_group(): string { return 'interactive'; }

    public function get_icon(): string {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
    }

    public function get_defaults(): array {
        return [
            'show_name'    => true,
            'show_phone'   => false,
            'show_subject' => true,
            'submit_text'  => 'Send Message',
            'success_msg'  => 'Thank you! We\'ll be in touch soon.',
            'email_to'     => '',
            'css_class'    => '',
        ];
    }

    public function get_controls(): array {
        return [
            [ 'id' => 'show_name',    'type' => 'toggle', 'label' => 'Show Name Field' ],
            [ 'id' => 'show_phone',   'type' => 'toggle', 'label' => 'Show Phone Field' ],
            [ 'id' => 'show_subject', 'type' => 'toggle', 'label' => 'Show Subject Field' ],
            [ 'id' => 'submit_text',  'type' => 'text',   'label' => 'Submit Button Text' ],
            [ 'id' => 'success_msg',  'type' => 'text',   'label' => 'Success Message' ],
            [ 'id' => 'email_to',     'type' => 'text',   'label' => 'Send To (email)', 'placeholder' => get_option('admin_email') ],
        ];
    }

    public function render( array $settings ): void {
        $s       = $this->parse_settings( $settings );
        $form_id = 'amari-form-' . uniqid();
        $nonce   = wp_create_nonce( 'amari_contact_form' );

        $class = 'amari-contact-form';
        if ( $s['css_class'] ) $class .= ' ' . esc_attr( $s['css_class'] );
        ?>
        <form class="<?php echo esc_attr($class); ?>" id="<?php echo esc_attr($form_id); ?>" method="post" novalidate>
            <input type="hidden" name="action" value="amari_contact_form">
            <input type="hidden" name="nonce"  value="<?php echo esc_attr($nonce); ?>">
            <input type="hidden" name="email_to" value="<?php echo esc_attr($s['email_to'] ?: get_option('admin_email')); ?>">

            <div class="amari-form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <?php if ( $s['show_name'] ) : ?>
                <div class="amari-form-group" style="grid-column:span 1;">
                    <label for="<?php echo $form_id; ?>_name">Name <span style="color:#e94560">*</span></label>
                    <input type="text" id="<?php echo $form_id; ?>_name" name="contact_name" required placeholder="Your name">
                </div>
                <?php endif; ?>

                <div class="amari-form-group" style="grid-column:span 1;">
                    <label for="<?php echo $form_id; ?>_email">Email <span style="color:#e94560">*</span></label>
                    <input type="email" id="<?php echo $form_id; ?>_email" name="contact_email" required placeholder="your@email.com">
                </div>

                <?php if ( $s['show_phone'] ) : ?>
                <div class="amari-form-group" style="grid-column:span 1;">
                    <label for="<?php echo $form_id; ?>_phone">Phone</label>
                    <input type="tel" id="<?php echo $form_id; ?>_phone" name="contact_phone" placeholder="+1 (000) 000-0000">
                </div>
                <?php endif; ?>

                <?php if ( $s['show_subject'] ) : ?>
                <div class="amari-form-group" style="grid-column:span 2;">
                    <label for="<?php echo $form_id; ?>_subject">Subject</label>
                    <input type="text" id="<?php echo $form_id; ?>_subject" name="contact_subject" placeholder="How can we help?">
                </div>
                <?php endif; ?>

                <div class="amari-form-group" style="grid-column:span 2;">
                    <label for="<?php echo $form_id; ?>_message">Message <span style="color:#e94560">*</span></label>
                    <textarea id="<?php echo $form_id; ?>_message" name="contact_message" rows="5" required placeholder="Your message..."></textarea>
                </div>

                <div style="grid-column:span 2;">
                    <button type="submit" class="amari-btn amari-btn-primary">
                        <?php echo esc_html( $s['submit_text'] ?: 'Send Message' ); ?>
                    </button>
                </div>
            </div>

            <div class="amari-form-response" style="display:none;margin-top:16px;padding:16px;border-radius:8px;"></div>
        </form>
        <?php
    }
}

AmariBuilder::instance()->register_element( new AmariElementContactForm() );

/* ============================================================
   AJAX handler for contact form submissions
   ============================================================ */
add_action( 'wp_ajax_amari_contact_form',        'amari_handle_contact_form' );
add_action( 'wp_ajax_nopriv_amari_contact_form', 'amari_handle_contact_form' );

function amari_handle_contact_form() {
    check_ajax_referer( 'amari_contact_form', 'nonce' );

    $name    = sanitize_text_field( $_POST['contact_name']    ?? '' );
    $email   = sanitize_email(      $_POST['contact_email']   ?? '' );
    $phone   = sanitize_text_field( $_POST['contact_phone']   ?? '' );
    $subject = sanitize_text_field( $_POST['contact_subject'] ?? 'Contact Form Submission' );
    $message = sanitize_textarea_field( $_POST['contact_message'] ?? '' );
    $to      = sanitize_email( $_POST['email_to'] ?? get_option('admin_email') );

    if ( ! $email || ! $message ) {
        wp_send_json_error([ 'message' => 'Please fill in all required fields.' ]);
    }

    $body  = "Name: {$name}\n";
    $body .= "Email: {$email}\n";
    if ( $phone ) $body .= "Phone: {$phone}\n";
    $body .= "\nMessage:\n{$message}";

    $sent = wp_mail( $to, $subject, $body, [
        'From: ' . get_bloginfo('name') . ' <noreply@' . parse_url(home_url(), PHP_URL_HOST) . '>',
        'Reply-To: ' . $name . ' <' . $email . '>',
    ]);

    if ( $sent ) {
        wp_send_json_success([ 'message' => 'Thank you! We\'ll be in touch soon.' ]);
    } else {
        wp_send_json_error([ 'message' => 'Sorry, the message could not be sent. Please try again.' ]);
    }
}
