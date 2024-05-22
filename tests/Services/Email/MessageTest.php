<?php

/**
 * This file is part of Laucov's SMTP Library project.
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
 * @package smtp
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
 */
final class MessageTest extends TestCase
{
    /**
     * Message instance.
     */
    protected Message $message;

    /**
     * @covers ::__toString
     * @covers ::addRecipient
     * @covers ::getContent
     * @covers ::getRecipients
     * @covers ::getReplyRecipient
     * @covers ::getSender
     * @covers ::getSubject
     * @covers ::setContent
     * @covers ::setSender
     * @covers ::setSubject
     */
    public function testCanCompose(): void
    {
        // Setup message.
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
        
        // Assert sender.
        $this->assertSame(
            'Jack Doe <jack.doe@nmail.com>',
            $this->message->getSender(),
        );
        
        // Assert recipients - do not filter.
        $recipients = $this->message->getRecipients();
        $this->assertIsArray($recipients);
        $this->assertCount(6, $recipients);
        $this->assertSame('john.doe@hmail.co.uk', $recipients[0]);
        $this->assertSame('Juan Doe <juan.doe@imail.mx>', $recipients[1]);
        $this->assertSame('João Doe <joao.doe@jmail.com.br>', $recipients[2]);
        $this->assertSame('Jean Doe <jean.doe@kmail.fr>', $recipients[3]);
        $this->assertSame('giovanni.doe@lmail.it', $recipients[4]);
        $this->assertSame('Hans Doe <hans.doe@mmail.de>', $recipients[5]);

        // Assert recipients - main recipients.
        $recipients = $this->message->getRecipients(RcptType::TO);
        $this->assertCount(2, $recipients);
        $this->assertSame('john.doe@hmail.co.uk', $recipients[0]);
        $this->assertSame('Juan Doe <juan.doe@imail.mx>', $recipients[1]);

        // Assert recipients - CC recipients.
        $recipients = $this->message->getRecipients(RcptType::CC);
        $this->assertCount(2, $recipients);
        $this->assertSame('João Doe <joao.doe@jmail.com.br>', $recipients[0]);
        $this->assertSame('Jean Doe <jean.doe@kmail.fr>', $recipients[1]);

        // Assert recipients - BCC recipients.
        $recipients = $this->message->getRecipients(RcptType::BCC);
        $this->assertCount(2, $recipients);
        $this->assertSame('giovanni.doe@lmail.it', $recipients[0]);
        $this->assertSame('Hans Doe <hans.doe@mmail.de>', $recipients[1]);

        // Assert Reply-To header.
        $this->assertNull($this->message->getReplyRecipient());

        // Assert subject.
        $this->assertSame('Greetings, Johns...', $this->message->getSubject());

        // Assert content.
        $this->assertSame(
            'Hello, fellow same-name international companions.' . "\r\n"
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

        // Assert raw message.
        $this->assertSame(
            'From: Jack Doe <jack.doe@nmail.com>'
                . "\r\n"
                . 'Subject: Greetings, Johns...'
                . "\r\n"
                . 'To: john.doe@hmail.co.uk, Juan Doe <juan.doe@imail.mx>'
                . "\r\n"
                . 'cc: João Doe <joao.doe@jmail.com.br>, '
                . 'Jean Doe <jean.doe@kmail.fr>'
                . "\r\n"
                . 'MIME-Version: 1.0'
                . "\r\n"
                . 'Content-Type: TEXT/plain; CHARSET=UTF-8'
                . "\r\n"
                . ''
                . "\r\n"
                . 'Hello, fellow same-name international companions.' . "\r\n"
                . 'I must unfortunately inform you that you are currently '
                . 'cursed, as I was last'
                . "\r\n"
                . 'night, by this electronic message.'
                . "\r\n"
                . 'A very evil witch shall visit you this night if you are '
                . 'not able to forward'
                . "\r\n"
                . 'this letter to at least 5 other people.' . "\r\n",
            (string) $this->message,
        );

        // Test setting a sender without name.
        $message = new Message();
        $message->setSender('johnny.doe@foobar.com');
        $this->assertSame('johnny.doe@foobar.com', $message->getSender());
        $this->assertSame(
            'From: johnny.doe@foobar.com' . "\r\n"
                . 'MIME-Version: 1.0' . "\r\n"
                . 'Content-Type: TEXT/plain; CHARSET=UTF-8' . "\r\n"
                . "\r\n"
                . "\r\n",
            (string) $message,
        );

        // Test sender Reply-To option.
        $message->setSender(
            'salesman.joe@foobar.co.uk',
            'John Doe',
            'sales@foobar.co.uk',
            'Foobar Company',
        );
        $this->assertSame(
            'John Doe <salesman.joe@foobar.co.uk>',
            $message->getSender(),
        );
        $this->assertSame(
            'Foobar Company <sales@foobar.co.uk>',
            $message->getReplyRecipient(),
        );
        $this->assertSame(
            'From: John Doe <salesman.joe@foobar.co.uk>' . "\r\n"
                . 'Reply-To: Foobar Company <sales@foobar.co.uk>' . "\r\n"
                . 'MIME-Version: 1.0' . "\r\n"
                . 'Content-Type: TEXT/plain; CHARSET=UTF-8' . "\r\n"
                . "\r\n"
                . "\r\n",
            (string) $message,
        );
        $message->setSender(
            'salesman.joe@foobar.co.uk',
            null,
            'sales@foobar.co.uk',
        );
        $this->assertSame(
            'salesman.joe@foobar.co.uk',
            $message->getSender(),
        );
        $this->assertSame(
            'sales@foobar.co.uk',
            $message->getReplyRecipient(),
        );
        $this->assertSame(
            'From: salesman.joe@foobar.co.uk' . "\r\n"
                . 'Reply-To: sales@foobar.co.uk' . "\r\n"
                . 'MIME-Version: 1.0' . "\r\n"
                . 'Content-Type: TEXT/plain; CHARSET=UTF-8' . "\r\n"
                . "\r\n"
                . "\r\n",
            (string) $message,
        );
    }

    /**
     * @covers ::__toString
     * @covers ::setContent
     */
    public function testCanChooseBetweenTextAndHtml(): void
    {
        // Set plain text content.
        $this->message->setContent('Hello, World!', false);

        // Assert message.
        $this->assertSame(
            'MIME-Version: 1.0'
                . "\r\n"
                . 'Content-Type: TEXT/plain; CHARSET=UTF-8'
                . "\r\n"
                . ''
                . "\r\n"
                . 'Hello, World!' . "\r\n",
            (string) $this->message,
        );

        // Set HTML content.
        $this->message->setContent('<p>Hello, World!</p>', true);

        // Assert message.
        $this->assertSame(
            'MIME-Version: 1.0'
                . "\r\n"
                . 'Content-Type: TEXT/html; CHARSET=UTF-8'
                . "\r\n"
                . ''
                . "\r\n"
                . '<p>Hello, World!</p>' . "\r\n",
            (string) $this->message,
        );

        // Check if wraps HTML.
        $this->message->setContent(
            '<p>Hello, World!</p>'
                . '<p class="foo bar baz">Hello, Planet!</p>'
                . '<p id="solar-system-paragraph">Hello, Solar System!</p>'
                . '<p><span>Hello, Galaxy!</span></p>'
                . '<p>Hello, Universe!</p>',
            true,
        );
        $this->assertSame(
            'MIME-Version: 1.0'
                . "\r\n"
                . 'Content-Type: TEXT/html; CHARSET=UTF-8'
                . "\r\n"
                . ''
                . "\r\n"
                . '<p>Hello, World!</p><p class="foo bar baz">Hello, Planet!'
                . '</p><p'
                . "\r\n"
                . 'id="solar-system-paragraph">Hello, Solar System!</p><p>'
                . '<span>Hello,'
                . "\r\n"
                . 'Galaxy!</span></p><p>Hello, Universe!</p>'
                . "\r\n",
            (string) $this->message,
        );
    }
    
    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        $this->message = new Message();
    }
}
