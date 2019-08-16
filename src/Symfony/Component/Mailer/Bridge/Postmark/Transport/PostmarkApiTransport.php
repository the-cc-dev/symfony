<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Bridge\Postmark\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\SmtpEnvelope;
use Symfony\Component\Mailer\Transport\AbstractApiTransport;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Kevin Verschaeve
 */
class PostmarkApiTransport extends AbstractApiTransport
{
    private const ENDPOINT = 'http://api.postmarkapp.com/email';

    private $key;

    public function __construct(string $key, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        $this->key = $key;

        parent::__construct($client, $dispatcher, $logger);
    }

    public function getName(): string
    {
        return sprintf('api://postmark');
    }

    protected function doSendApi(Email $email, SmtpEnvelope $envelope): ResponseInterface
    {
        $response = $this->client->request('POST', self::ENDPOINT, [
            'headers' => [
                'Accept' => 'application/json',
                'X-Postmark-Server-Token' => $this->key,
            ],
            'json' => $this->getPayload($email, $envelope),
        ]);

        if (200 !== $response->getStatusCode()) {
            $error = $response->toArray(false);

            throw new HttpTransportException(sprintf('Unable to send an email: %s (code %s).', $error['Message'], $error['ErrorCode']), $response);
        }

        return $response;
    }

    private function getPayload(Email $email, SmtpEnvelope $envelope): array
    {
        $payload = [
            'From' => $envelope->getSender()->toString(),
            'To' => implode(',', $this->stringifyAddresses($this->getRecipients($email, $envelope))),
            'Cc' => implode(',', $this->stringifyAddresses($email->getCc())),
            'Bcc' => implode(',', $this->stringifyAddresses($email->getBcc())),
            'Subject' => $email->getSubject(),
            'TextBody' => $email->getTextBody(),
            'HtmlBody' => $email->getHtmlBody(),
            'Attachments' => $this->getAttachments($email),
        ];

        $headersToBypass = ['from', 'to', 'cc', 'bcc', 'subject', 'content-type', 'sender'];
        foreach ($email->getHeaders()->all() as $name => $header) {
            if (\in_array($name, $headersToBypass, true)) {
                continue;
            }

            $payload['Headers'][] = [
                'Name' => $name,
                'Value' => $header->toString(),
            ];
        }

        return $payload;
    }

    private function getAttachments(Email $email): array
    {
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();
            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');
            $disposition = $headers->getHeaderBody('Content-Disposition');

            $att = [
                'Name' => $filename,
                'Content' => $attachment->bodyToString(),
                'ContentType' => $headers->get('Content-Type')->getBody(),
            ];

            if ('inline' === $disposition) {
                $att['ContentID'] = 'cid:'.$filename;
            }

            $attachments[] = $att;
        }

        return $attachments;
    }
}
