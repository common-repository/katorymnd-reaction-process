<?php

class Katorymnd_Uraz {

    private $feed_valid;
    private $form_error = array();

    public function __construct() {
        $this->feed_valid = NULL;
    }

    public function valid_name($tsex, $udzy) {
        $this->feed_valid = sanitize_text_field($tsex);

        if (!preg_match("/^\p{L}+$/u", trim($this->feed_valid))) {
            $this->form_error[] = $udzy . __(' required', 'katorymnd-text-domain');
        }
        return $this;
    }

    public function valid_email($lew) {
        $this->feed_valid = sanitize_email(trim($lew));

        if (!is_email($this->feed_valid)) {
            $this->form_error[] = __('Check your email', 'katorymnd-text-domain');
        }
    }

    public function valid_input($pqbg, $oyj) {
        $this->feed_valid = sanitize_text_field($pqbg);

        if (empty($this->feed_valid)) {
            $this->form_error[] = $oyj . __(' required', 'katorymnd-text-domain');
        }
    }

   public function valid_error() {
    if (empty($this->form_error)) return [];

    // Convert error messages to an array of sanitized strings
    $errors = array_map('esc_html', $this->form_error);

    return $errors;
}


   public function valid_success() {
    if (empty($this->form_error)) {
        return esc_html__('Thank you for sending us your comment', 'katorymnd-text-domain');
    }
    return '';
}

    public function save_data() {
        return empty($this->form_error);
    }
}

//$katorymnd_jdpi = new Katorymnd_Uraz();
