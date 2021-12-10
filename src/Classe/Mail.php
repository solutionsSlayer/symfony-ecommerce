<?php
namespace App\Classe;

use Mailjet\Resources;
use Mailjet\Client;
use Symfony\Component\HttpFoundation\Response;

class Mail
{
    private Client $instance;
    private string $key = 'a6a8ccbbce97a058130b660ca41cdfa2';
    private string $key_secret = '6098dc49cb56e020bf3047ebd862eeae';

    private array $templates = [
        "reset_password" => 3408519,
        "order_success" => 3410637,
        "registration" => 3410864,
        "contact" => 3417936
    ];

    public function __construct()
    {
        $this->instance = new Client($this->key, $this->key_secret,true,['version' => 'v3.1']);
    }

    public function sendEmail($body): \Mailjet\Response
    {
        return $this->instance->post(Resources::$Email, ['body' => $body]);
    }

    public function registration($recipient, $recipient_name, $subject, $content): \Mailjet\Response
    {
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "devordiesenpai@gmail.com",
                        'Name' => "Mailjet Pilot"
                    ],
                    'To' => [
                        [
                            'Email' => $recipient,
                            'Name' => $recipient_name
                        ]
                    ],
                    'TemplateID' => $this->templates["registration"],
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content
                    ]
                ]
            ]
        ];

        return $this->sendEmail($body);
    }

    public function resetPassword(
        $recipient,
        $recipient_name,
        $subject,
        $content): \Mailjet\Response
    {
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "devordiesenpai@gmail.com",
                        'Name' => "Mailjet Pilot"
                    ],
                    'To' => [
                        [
                            'Email' => $recipient,
                            'Name' => $recipient_name
                        ]
                    ],
                    'TemplateID' => $this->templates["reset_password"],
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content
                    ]
                ]
            ]
        ];

        return $this->sendEmail($body);
    }

    public function orderSuccess(
        $recipient,
        $recipient_name,
        $subject,
        $content,
        $firstname,
        $total_price,
        $order_date,
        $order_id): \Mailjet\Response
    {
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "devordiesenpai@gmail.com",
                        'Name' => "Mailjet Pilot"
                    ],
                    'To' => [
                        [
                                'Email' => $recipient,
                            'Name' => $recipient_name
                        ]
                    ],
                    'TemplateID' => $this->templates["order_success"],
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                        'firstname' => $firstname,
                        'total_price' => $total_price,
                        'order_date' => $order_date,
                        'order_id' => $order_id
                    ]
                ]
            ]
        ];

        return $this->sendEmail($body);
    }

    public function contact(
        $recipient,
        $recipient_name,
        $subject,
        $name,
        $content): \Mailjet\Response
    {
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "devordiesenpai@gmail.com",
                        'Name' => "Mailjet Pilot"
                    ],
                    'To' => [
                        [
                            'Email' => $recipient,
                            'Name' => $recipient_name
                        ]
                    ],
                    'TemplateID' => $this->templates["contact"],
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                        'name' => $name
                    ]
                ]
            ]
        ];

        return $this->sendEmail($body);
    }
}