<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Transport\Smtp;

use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * @author Konstantin Myakshin <molodchick@gmail.com>
 */
final class EsmtpTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $encryption = $dsn->getOption('encryption');
        $authMode = $dsn->getOption('auth_mode');
        $port = $dsn->getPort(25);
        $host = $dsn->getHost();

        $transport = new EsmtpTransport($host, $port, $encryption, $authMode, $this->dispatcher, $this->logger);

        if ($user = $dsn->getUser()) {
            $transport->setUsername($user);
        }

        if ($password = $dsn->getPassword()) {
            $transport->setPassword($password);
        }

        return $transport;
    }

    public function supports(Dsn $dsn): bool
    {
        return 'smtp' === $dsn->getScheme();
    }
}
