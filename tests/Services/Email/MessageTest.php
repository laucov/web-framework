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

use Laucov\WebFwk\Services\Email\Message;
use Laucov\WebFwk\Services\Email\RecipientType as RcptType;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\WebFwk\Services\Email\Message
 * @covers \Laucov\WebFwk\Services\Email\Mailbox
 */
final class MessageTest extends TestCase
{
    /**
     * Message instance.
     */
    protected Message $message;

    /**
     * @covers ::addRecipient
     * @covers ::getRecipients
     * @covers ::getReplyRecipient
     * @covers ::getSender
     * @covers ::setContent
     * @covers ::setReplyRecipient
     * @covers ::setSender
     * @uses Laucov\WebFwk\Services\Email\Mailbox::__construct
     * @uses Laucov\WebFwk\Services\Email\Mailbox::__toString
     * @uses Laucov\WebFwk\Services\Email\Message::getContent
     * @uses Laucov\WebFwk\Services\Email\Message::setContent
     * @uses Laucov\WebFwk\Services\Email\Message::getSubject
     * @uses Laucov\WebFwk\Services\Email\Message::setSubject
     */
    public function testCanSetAndGetMailboxes(): void
    {
        // Assert sender.
        $this->assertMailbox(
            'jack.doe@nmail.com',
            'Jack Doe',
            'Jack Doe <jack.doe@nmail.com>',
            $this->message->getSender(),
        );

        // Test sender without a mailbox name.
        $this->message->setSender('jack.doe@nmail.com');
        $this->assertMailbox(
            'jack.doe@nmail.com',
            null,
            'jack.doe@nmail.com',
            $this->message->getSender(),
        );

        // Assert recipients - "To" header.
        $to = $this->message->getRecipients(RcptType::TO);
        $this->assertIsArray($to);
        $this->assertCount(2, $to);
        $this->assertMailbox(
            'john.doe@hmail.co.uk',
            null,
            'john.doe@hmail.co.uk',
            $to[0],
        );
        $this->assertMailbox(
            'juan.doe@imail.mx',
            'Juan Doe',
            'Juan Doe <juan.doe@imail.mx>',
            $to[1],
        );

        // Assert recipients - "cc" header.
        $cc = $this->message->getRecipients(RcptType::CC);
        $this->assertIsArray($cc);
        $this->assertCount(2, $cc);
        $this->assertMailbox(
            'joao.doe@jmail.com.br',
            'João Doe',
            'João Doe <joao.doe@jmail.com.br>',
            $cc[0],
        );
        $this->assertMailbox(
            'jean.doe@kmail.fr',
            'Jean Doe',
            'Jean Doe <jean.doe@kmail.fr>',
            $cc[1],
        );

        // Assert recipients - "Bcc" header.
        $bcc = $this->message->getRecipients(RcptType::BCC);
        $this->assertIsArray($bcc);
        $this->assertCount(2, $bcc);
        $this->assertMailbox(
            'giovanni.doe@lmail.it',
            null,
            'giovanni.doe@lmail.it',
            $bcc[0],
        );
        $this->assertMailbox(
            'hans.doe@mmail.de',
            'Hans Doe',
            'Hans Doe <hans.doe@mmail.de>',
            $bcc[1],
        );

        // Assert recipients - no filter.
        $recipients = $this->message->getRecipients();
        $this->assertIsArray($recipients);
        $this->assertCount(6, $recipients);
        $this->assertSame($to[0], $recipients[0]);
        $this->assertSame($to[1], $recipients[1]);
        $this->assertSame($cc[0], $recipients[2]);
        $this->assertSame($cc[1], $recipients[3]);
        $this->assertSame($bcc[0], $recipients[4]);
        $this->assertSame($bcc[1], $recipients[5]);

        // Assert reply recipient (Reply-To).
        $this->assertNull($this->message->getReplyRecipient());

        // Set and assert another reply recipient.
        $this->message->setReplyRecipient('contact@doe.com');
        $this->assertMailbox(
            'contact@doe.com',
            null,
            'contact@doe.com',
            $this->message->getReplyRecipient(),
        );

        // Set a complete mailbox for the reply recipient.
        $this->message->setReplyRecipient('jack@doe.com', 'Jack Inbox');
        $this->assertMailbox(
            'jack@doe.com',
            'Jack Inbox',
            'Jack Inbox <jack@doe.com>',
            $this->message->getReplyRecipient(),
        );
    }

    /**
     * @covers ::getContent
     * @covers ::getSubject
     * @covers ::getType
     * @covers ::setContent
     * @covers ::setSubject
     * @uses Laucov\WebFwk\Services\Email\Mailbox::__construct
     * @uses Laucov\WebFwk\Services\Email\Message::addRecipient
     * @uses Laucov\WebFwk\Services\Email\Message::setSender
     */
    public function testCanSetAndGetContents(): void
    {
        // Assert subject.
        $this->assertSame('Greetings, Johns...', $this->message->getSubject());

        // Assert content.
        // Ensure line-breaks contain carriage returns.
        // Ensure wraps long lines (above 78 characters).
        $this->assertSame('text/plain', $this->message->getType());
        $this->assertSame(
            'Hello, fellow same-name international companions.'
                . "\r\n"
                . 'I must unfortunately inform you that you are currently '
                . 'cursed, as I was last'
                . "\r\n"
                . 'night, by this electronic message.'
                . "\r\n"
                . 'A very evil witch shall visit you this night if you are '
                . 'not able to forward'
                . "\r\n"
                . 'this letter to at least 5 other people.',
            $this->message->getContent(),
        );

        // Set html content.
        $this->message->setContent(
            '<p>Hello, fellow same-name international companions.</p>'
                . "\n"
                . '<p>I must unfortunately inform you that you are '
                . 'currently <span class="very-scary-text">cursed</span>, '
                . 'as I was last night, by this electronic message.</p>'
                . "\n"
                . '<p>A very evil witch shall visit you this night if you '
                . 'are not able to forward this letter to at least 5 other '
                . 'people.</p>',
            true,
        );

        // Assert content.
        $this->assertSame('text/html', $this->message->getType());
        $this->assertSame(
            '<p>Hello, fellow same-name international companions.</p>'
                . "\r\n"
                . '<p>I must unfortunately inform you that you are '
                . 'currently <span'
                . "\r\n"
                . 'class="very-scary-text">cursed</span>, as I was last night, by this electronic'
                . "\r\n"
                . 'message.</p>'
                . "\r\n"
                . '<p>A very evil witch shall visit you this night if you '
                . 'are not able to forward'
                . "\r\n"
                . 'this letter to at least 5 other '
                . 'people.</p>',
            $this->message->getContent(),
        );
    }

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        // Create message.
        $this->message = new Message();
        $this->message
            ->addRecipient('john.doe@hmail.co.uk')
            ->addRecipient('juan.doe@imail.mx', 'Juan Doe', RcptType::TO)
            ->addRecipient('joao.doe@jmail.com.br', 'João Doe', RcptType::CC)
            ->addRecipient('giovanni.doe@lmail.it', null, RcptType::BCC)
            ->addRecipient('jean.doe@kmail.fr', 'Jean Doe', RcptType::CC)
            ->setSender('jack.doe@nmail.com', 'Jack Doe')
            ->setSubject('Greetings, Johns...')
            ->setContent(
                'Hello, fellow same-name international companions.' . PHP_EOL
                    . 'I must unfortunately inform you that you are '
                    . 'currently cursed, as I was last night, by this '
                    . 'electronic message.' . PHP_EOL
                    . 'A very evil witch shall visit you this night if you '
                    . 'are not able to forward this letter to at least 5 '
                    . 'other people.'
            )
            ->addRecipient('hans.doe@mmail.de', 'Hans Doe', RcptType::BCC);
    }

    /**
     * Assert that the tested value is a mailbox object.
     */
    protected function assertMailbox(
        string $address,
        null|string $name,
        string $string,
        mixed $actual,
    ): void {
        // Check if is an object.
        $this->assertIsObject($actual);

        // Check properties.
        $this->assertObjectHasProperty('address', $actual);
        $this->assertSame($address, $actual->address);
        $this->assertObjectHasProperty('name', $actual);
        $this->assertSame($name, $actual->name);

        // Check __toString() result.
        $this->assertSame($string, (string) $actual);
    }
}
