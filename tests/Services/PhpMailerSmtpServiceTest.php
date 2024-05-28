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

declare(strict_types=1);

namespace Tests\Services;

use Laucov\WebFwk\Config\Smtp;
use Laucov\WebFwk\Services\Email\Message;
use Laucov\WebFwk\Services\Email\RecipientType as RcptType;
use Laucov\WebFwk\Services\PhpMailerSmtpService as SmtpService;
use Laucov\WebFwk\Services\PhpMailerSmtpService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Services\PhpMailerSmtpService
 */
class PhpMailerSmtpServiceTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::createMailer
     * @covers ::send
     * @covers ::setupMailer
     * @uses Laucov\WebFwk\Services\Email\Mailbox::__construct
     * @uses Laucov\WebFwk\Services\Email\Message::addRecipient
     * @uses Laucov\WebFwk\Services\Email\Message::getContent
     * @uses Laucov\WebFwk\Services\Email\Message::getRecipients
     * @uses Laucov\WebFwk\Services\Email\Message::getReplyRecipient
     * @uses Laucov\WebFwk\Services\Email\Message::getSender
     * @uses Laucov\WebFwk\Services\Email\Message::getSubject
     * @uses Laucov\WebFwk\Services\Email\Message::getType
     * @uses Laucov\WebFwk\Services\Email\Message::setContent
     * @uses Laucov\WebFwk\Services\Email\Message::setReplyRecipient
     * @uses Laucov\WebFwk\Services\Email\Message::setSender
     * @uses Laucov\WebFwk\Services\Email\Message::setSubject
     */
    public function testCanSetupAndSendEmails(): void
    {
        // Create service.
        $config = new Smtp();
        $service = new class ($config) extends SmtpService {
            public PHPMailer $mailer;
            public function createMailer(): PHPMailer
            {
                return $this->mailer;
            }
        };

        // Setup global configuration.
        $config->host = 'smtp.foobar.co.uk';
        $config->user = 'john.doe@foobar.co.uk';
        $config->password = 'helloworld1970';

        // Create mock #1.
        $mock = $this->createMock(PHPMailer::class);
        $service->mailer = $mock;
        $mock
            ->expects($this->once())
            ->method('isSMTP');
        $mock
            ->expects($this->once())
            ->method('send');

        // Expect sender to be added.
        $mock
            ->expects($this->once())
            ->method('setFrom')
            ->with('john.doe@foobar.co.uk');

        // Expect recipients to be added.
        $mock
            ->expects($this->exactly(2))
            ->method('addAddress')
            ->withConsecutive(
                ['jimmy.vixay@inlook.com', 'Jimmy'],
                ['sabrina.durrah@fmail.com', 'Sabrina'],
            );
        $mock
            ->expects($this->exactly(2))
            ->method('addCC')
            ->withConsecutive(
                ['natalia.kogut@foobar.co.uk'],
                ['elias.dykeman@foobar.co.uk'],
            );
        $mock
            ->expects($this->once())
            ->method('addBCC')
            ->with('leonard.goughnour@baz.co.uk');
        $mock
            ->expects($this->once())
            ->method('isHTML')
            ->with(false);

        // Create message.
        $message = new Message();
        $message
            ->addRecipient('jimmy.vixay@inlook.com', 'Jimmy')
            ->addRecipient('sabrina.durrah@fmail.com', 'Sabrina')
            ->addRecipient('natalia.kogut@foobar.co.uk', null, RcptType::CC)
            ->addRecipient('elias.dykeman@foobar.co.uk', null, RcptType::CC)
            ->addRecipient('leonard.goughnour@baz.co.uk', null, RcptType::BCC)
            ->setSubject('Lorem ipsum')
            ->setContent(<<<TXT
                Dear recipients,

                I'd like to share some lorem ipsum test with you:
                
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Quisque hendrerit mauris ex, eget vulputate ante hendrerit ac.

                Best regards,
                John Doe.
                TXT);
        $service->send($message);

        // Check mailer properties.
        $this->assertSame(PHPMailer::ENCRYPTION_SMTPS, $mock->SMTPSecure);
        $this->assertSame('smtp.foobar.co.uk', $mock->Host);
        $this->assertSame(465, $mock->Port);
        $this->assertSame(true, $mock->SMTPAuth);
        $this->assertSame('john.doe@foobar.co.uk', $mock->Username);
        $this->assertSame('helloworld1970', $mock->Password);
        $this->assertSame('Lorem ipsum', $mock->Subject);
        $this->assertSame(
            <<<TXT
                Dear recipients,\r
                \r
                I'd like to share some lorem ipsum test with you:\r
                \r
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.\r
                Quisque hendrerit mauris ex, eget vulputate ante hendrerit ac.\r
                \r
                Best regards,\r
                John Doe.
                TXT,
            $mock->Body,
        );
        $this->assertSame('UTF-8', $mock->CharSet);

        // Create mock #2.
        $mock = $this->createMock(PHPMailer::class);
        $service->mailer = $mock;
        $mock
            ->expects($this->once())
            ->method('isSMTP');
        $mock
            ->expects($this->once())
            ->method('send');

        // Test "From" fallback with Smtp->from.
        $config->fromAddress = 'johnny.doe@othermail.com';
        $config->fromName = 'Johnny';
        $mock
            ->expects($this->once())
            ->method('setFrom')
            ->with('johnny.doe@othermail.com', 'Johnny');
        $service->send($message);

        // Create mock #3.
        $mock = $this->createMock(PHPMailer::class);
        $service->mailer = $mock;
        $mock
            ->expects($this->once())
            ->method('isSMTP');
        $mock
            ->expects($this->once())
            ->method('send');

        // Test explicit "From" and "Reply-To" from message.
        // Test HTML content.
        $message
            ->setSender('noreply@automail.com', 'Someone')
            ->setReplyRecipient('contact@foomail.com', 'Contact Us')
            ->setContent('<p>Hello, World!</p>', true);
        $mock
            ->expects($this->once())
            ->method('setFrom')
            ->with('noreply@automail.com', 'Someone');
        $mock
            ->expects($this->once())
            ->method('addReplyTo')
            ->with('contact@foomail.com', 'Contact Us');
        $mock
            ->expects($this->once())
            ->method('isHTML')
            ->with(true);
        $service->send($message);
        $this->assertSame('<p>Hello, World!</p>', $mock->Body);
        $this->assertSame('UTF-8', $mock->CharSet);

        // Just ensure the service actually uses PHPMailer.
        $service = new class ($config) extends PhpMailerSmtpService {
            public function getMailer(): PHPMailer
            {
                return $this->createMailer();
            }
        };
        $this->assertInstanceOf(PHPMailer::class, $service->getMailer());

        // Test if sends.
        // Ensure the exception thrown refers to not finding the host.
        // Ensure no other exception is thrown BEFORE sending the e-mail.
        $config->host = 'localhost';
        $config->port = 8888;
        $this->expectException(\PHPMailer\PHPMailer\Exception::class);
        $this->expectExceptionMessage('SMTP Error: Could not connect to SMTP host. Failed to connect to server');
        $service->send($message);
    }
}
