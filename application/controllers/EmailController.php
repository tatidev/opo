<?php
class EmailController extends CI_Controller {

    

    public function send_test_email() {
        $environment = getenv('APP_ENV');
        $this->load->library('email');
        $this->email->from('opuzen.email@gmail.com', 'Opuzen Service');
        $this->email->to('development@opuzen.com');
        $this->email->subject('Test Email from '.$environment.' OPUZEN CodeIgniter');
        $this->email->message('<p>This is a <strong>test email</strong> from CodeIgniter.</p>');

        if ($this->email->send()) {
            echo 'Email sent successfully from '.$environment.'!';
        } else {
            echo $this->email->print_debugger();
        }
    }
}