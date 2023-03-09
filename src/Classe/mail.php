<?php
  namespace App\Classe;

  use Mailjet\Client;
  use Mailjet\Resources;

  class mail
  {
      private $api_key= '1bf5c8bf332df972ad9788632135060d';
      private $api_key_secret= '74fcd409a477bd9f3779be904f2c1d87';

      public function send($to_email,$to_name,$subject,$content)
      {
          $mj=new Client($this->api_key,$this->api_key_secret,true,['version' => 'v3.1']);
          $body = [
              'Messages' => [
                  [
                      'From' => [
                          'Email' => "ahmed.jomaa@esprit.tn",
                          'Name' => "sustainability hub"
                      ],
                      'To' => [
                          [
                              'Email' => $to_email,
                              'Name' => $to_name
                          ]
                      ],
                      'TemplateID' => 4619535,
                      'TemplateLanguage' => true,
                      'Subject' => $subject,
                      'Variables' => [
                          'content' => $content,
                      ]
                  ]
              ]
          ];
          $response = $mj->post(Resources::$Email, ['body' => $body]);
//          $response->success() && dd($response->getData());
      }
  }