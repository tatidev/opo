<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use SendGrid\Mail\Mail;

class SendGridPrep {

    public function __construct()
    {

    }
    
    public function sendEmailHtml($subject, $receivers, $contentHtml, $categories, $attachments=[]){
        # https://github.com/sendgrid/sendgrid-php/blob/main/USE_CASES.md#kitchen-sink---an-example-with-all-settings-used
        require_once("sendgrid-php/sendgrid-php.php");

//        echo "<pre>"; var_dump($subject); var_dump($receivers); var_dump($contentHtml); var_dump($categories); var_dump($attachments);

        $email = new Mail();
        $email->setFrom('shipping@opuzen.com', 'Opuzen');

        $email->setSubject($subject);
        $email->addTos($receivers);

        $email->addContent(
            "text/html",
            $contentHtml
        );

        $categories = array_merge(["Sales Management"], $categories);
        $email->addCategories($categories);

        if(count($attachments) > 0){
            $email->addAttachments($attachments);
        }

        $sendgrid = new \SendGrid('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');
        try {
            $response = $sendgrid->send($email);
            return $response->statusCode();
//        print_r($response->headers());
//        print $response->body() . "\n";
        } catch (Exception $e) {
            return 'Caught exception: '.  $e->getMessage(). "\n";
        }
    }

//    public function sendEmailDynamicTempalate($subject, array $receivers, array $data, array $categories){
//        # https://github.com/sendgrid/sendgrid-php/blob/main/USE_CASES.md#kitchen-sink---an-example-with-all-settings-used
//
//        require_once("../useful/sendgrid-php/sendgrid-php.php");
//        $email = new Mail();
//        $email->setFrom('info@opuzen.com', 'Opuzen');
//
//        $email->setSubject($subject);
//        $email->addTos($receivers);
//
//        $data = [
//            "subject2" => "Example Subject 2",
//            "name2" => "Example Name 2",
//            "city2" => "Orange"
//        ];
//        $email->addDynamicTemplateDatas($data);
//
//        $categories = array_merge(["Sales Management"], $categories);
//        $email->addCategories($categories);
//
//        $sendgrid = new \SendGrid('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');
//        try {
//            $response = $sendgrid->send($email);
//            return $response->statusCode();
////        print_r($response->headers());
////        print $response->body() . "\n";
//        } catch (Exception $e) {
//            return 'Caught exception: '.  $e->getMessage(). "\n";
//        }
//    }


}