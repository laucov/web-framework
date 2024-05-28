<?php

/**
 * This file is part of Laucov's Web Framework project.
 *
 * Copyright 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package web-framework
 *
 * @author Rafael Covaleski Pereira <rafael.covaleski@laucov.com>
 *
 * @license <http://www.apache.org/licenses/LICENSE-2.0> Apache License 2.0
 *
 * @copyright © 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 */

namespace Laucov\WebFwk\Services;

use Laucov\WebFwk\Config\Smtp;
use Laucov\WebFwk\Services\Email\Message;
use Laucov\WebFwk\Services\Email\RecipientType as RcptType;
use Laucov\WebFwk\Services\Interfaces\SmtpServiceInterface;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Provides an interface to configurable e-mail features.
 */
class PhpMailerSmtpService implements SmtpServiceInterface
{
    /**
     * Create the service instance.
     */
    public function __construct(
        /**
         * Configuration instance.
         */
        protected Smtp $config,
    ) {
    }

    /**
     * Send an e-mail message.
     */
    public function send(Message $message): void
    {
        // Create the mailer instance.
        $mailer = $this->createMailer();
        $this->setupMailer($mailer);

        // Set sender ("From").
        $sender = $message->getSender();
        $from_address = $sender->address
            ?? $this->config->fromAddress
            ?? $this->config->user;
        $from_name = $sender->name ?? $this->config->fromName;
        $mailer->setFrom($from_address, $from_name);

        // Set expected reply recipient ("Reply-To").
        $reply_to = $message->getReplyRecipient();
        if ($reply_to !== null) {
            $mailer->addReplyTo($reply_to->address, $reply_to->name ?? '');
        }

        // Set recipients.
        foreach ($message->getRecipients(RcptType::TO) as $mailbox) {
            $mailer->addAddress($mailbox->address, $mailbox->name ?? '');
        }
        foreach ($message->getRecipients(RcptType::CC) as $mailbox) {
            $mailer->addCC($mailbox->address, $mailbox->name ?? '');
        }
        foreach ($message->getRecipients(RcptType::BCC) as $mailbox) {
            $mailer->addBCC($mailbox->address, $mailbox->name ?? '');
        }

        // Set message information.
        $mailer->Subject = $message->getSubject();
        $mailer->Body = $message->getContent();
        $mailer->isHTML($message->getType() === 'text/html');

        // Send.
        $mailer->send();
    }

    /**
     * Create a new mailer instance.
     */
    protected function createMailer(): PHPMailer
    {
        return new PHPMailer(true);
    }

    /**
     * Setup a `PHPMailer` according to stored settings.
     */
    protected function setupMailer(PHPMailer $mailer): void
    {
        // Set server options.
        $mailer->isSMTP();
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mailer->Host = $this->config->host;
        $mailer->Port = $this->config->port;
        $mailer->SMTPAuth = true;
        $mailer->Username = $this->config->user;
        $mailer->Password = $this->config->password;
    }
}
