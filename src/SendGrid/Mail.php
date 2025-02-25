<?php

namespace Limonlabs\Bigcommerce\SendGrid;

use SendGrid\Mail\Mail as SendGridMail;

class Mail
{
    protected SendGridMail $mail;

    public function __construct()
    {
        $this->mail = new SendGridMail();
    }

    public function setFrom(string $email, string $name): self
    {
        $this->mail->setFrom($email, $name);

        return $this;
    }

    public function setSubject(string $subject): self
    {
        $this->mail->setSubject($subject);

        return $this;
    }

    public function addTo(string $email, string $name): self
    {
        $this->mail->addTo($email, $name);

        return $this;
    }

    public function addContent(string $type, string $content): self
    {
        $this->mail->addContent($type, $content);

        return $this;
    }

    public function addAttachment(string $content, string $filename): self
    {
        $this->mail->addAttachment($content, $filename);

        return $this;
    }

    public function getMail(): SendGridMail
    {
        return $this->mail;
    }

    public function send(): void
    {
        $sendgrid = new \SendGrid(config('services.sendgrid.api_key'));

        try {
            $response = $sendgrid->send($this->mail);
            
            if ($response->statusCode() !== 202) {
                throw new \Exception('Failed to send email');
            }
        } catch (\Throwable $th) {
            throw new \Exception('Failed to send email');
        }
    }

    public static function getInstance(): Mail
    {
        return new Mail();
    }
}