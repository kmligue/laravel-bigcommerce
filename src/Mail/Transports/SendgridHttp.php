<?php

namespace Limonlabs\Bigcommerce\Mail\Transports;

use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SendgridHttp extends AbstractTransport
{
    protected $client;
    protected $apiUrl;
    protected $apiKey;
    protected $logger;

    public function __construct(Client $client, string $apiUrl, string $apiKey, ?LoggerInterface $logger = null)
    {
        parent::__construct(new EventDispatcher(), $logger);

        $this->client = $client;
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    protected function doSend(SentMessage $message): void
    {
        /** @var Email $email */
        $email = $message->getOriginalMessage();

        $payload = [
            'personalizations' => [
                [
                    'to' => array_map(fn($address) => ['email' => $address->getAddress()], $email->getTo()),
                    'subject' => $email->getSubject(),
                ],
            ],
            'from' => [
                'email' => config('mail.from.address'),
                'name' => config('mail.from.name'),
            ],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $email->getHtmlBody() ?? $email->getTextBody(),
                ],
            ],
        ];
        
        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            $this->logger->info('Email sent successfully', ['response' => $response->getBody()]);
        } catch (RequestException $e) {
            $this->logger->error('Failed to send email', ['error' => $e->getMessage()]);
        }
    }

    public function __toString(): string
    {
        return 'sendgrid-http';
    }
}
