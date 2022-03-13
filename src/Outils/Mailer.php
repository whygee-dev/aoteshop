<?php

namespace App\Outils;

use App\Entity\User;
use Mailjet\Client;
use \Mailjet\Resources;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class Mailer {
    private $public_key;
    private $private_key;

    public function __construct()
    {
        $this->public_key = $_ENV["MAIL_PUBLIC_KEY"];
        $this->private_key = $_ENV["MAIL_PRIVATE_KEY"];
    }

    public function send($email, $name, $subject, $title, $content) {
        $mj = new Client($this->public_key, $this->private_key,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "whygee.dev@gmail.com",
                        'Name' => "AOT ESHOP"
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $name
                        ]
                    ],
                    'TemplateID' => 3745733,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'variables' => [
                        'title' => $title,
                        'content' => $content
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
    }

    public function sendResetEmail(User $user, ResetPasswordToken $token) {
        $url = $_ENV["URL"] . "/reset-password/reset/" . $token->getToken();

        $this->send($user->getEmail(), $user->getFullName(),'Votre demande de réinitialisation de mot de passe', 'Réinitialisation de mot de passe',
            "<p>Veuillez suivre ce lien pour réinitialiser votre mot de passe</p>
            <br>
            <a href='$url'>Allons-y</a>
            <br> <br>
         <p>Ce lien expire dans 1 heure.</p>
        ");
    }
}