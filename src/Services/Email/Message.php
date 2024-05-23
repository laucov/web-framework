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

namespace Laucov\WebFwk\Services\Email;

/**
 * Provides methods for building e-mail messages.
 */
class Message
{
    /**
     * Blind carbon copy recipient mailboxes.
     * 
     * @var array<Mailbox>
     */
    protected array $bcc = [];

    /**
     * Carbon copy recipient mailboxes.
     * 
     * @var array<Mailbox>
     */
    protected array $cc = [];

    /**
     * Message main content.
     */
    protected string $content = '';

    /**
     * Sender mailbox.
     */
    protected null|Mailbox $from = null;

    /**
     * Reply-To mailbox.
     */
    protected null|Mailbox $replyTo = null;

    /**
     * Message subject.
     */
    protected null|string $subject = null;

    /**
     * Main recipient mailboxes.
     * 
     * @var array<Mailbox>
     */
    protected array $to = [];

    /**
     * Main content MIME subtype.
     */
    protected string $type = 'plain';

    /**
     * Add a recipient to the message.
     */
    public function addRecipient(
        null|string $address,
        null|string $name = null,
        RecipientType $type = RecipientType::TO,
    ): static {
        $this->{$type->value}[] = new Mailbox($address, $name);
        return $this;
    }

    /**
     * Get the currently set content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get all message recipients.
     * 
     * @return array<Mailbox>
     */
    public function getRecipients(null|RecipientType $filter = null): array
    {
        // Merge if no filter was passed.
        if ($filter === null) {
            return array_merge($this->to, $this->cc, $this->bcc);
        }

        // Get the specific list.
        return $this->{$filter->value};
    }

    /**
     * Get the reply expected recipient (or the "Reply-To" header value).
     */
    public function getReplyRecipient(): null|Mailbox
    {
        return $this->replyTo;
    }

    /**
     * Get the message sender (or the "From" header value).
     */
    public function getSender(): null|Mailbox
    {
        return $this->from;
    }

    /**
     * Get the subject.
     */
    public function getSubject(): null|string
    {
        return $this->subject;
    }

    /**
     * Get the content MIME type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the message body content.
     * 
     * Ensures each line has 78 characters or less.
     */
    public function setContent(string $content, bool $is_html = false): static
    {
        // Empty current content.
        $this->content = '';

        // Set main content subtype option.
        $this->type = $is_html ? 'text/html' : 'text/plain';

        // Get and parse lines.
        $lines = array_map('trim', explode("\n", $content));
        foreach ($lines as $i => $line) {
            // Add line feed.
            if ($i > 0) {
                $this->content .= "\r\n";
            }
            // Parse each word.
            $words = explode(' ', $line);
            $line_length = 0;
            foreach ($words as $word) {
                // Get the word length.
                $word_length = strlen($word);
                // Add space or new line.
                if ($line_length > 0) {
                    if ($line_length + $word_length + 1 > 78) {
                        // Break the line.
                        $this->content .= "\r\n";
                        $line_length = 0;
                    } else {
                        // Add space.
                        $this->content .= ' ';
                        $line_length++;
                    }
                }
                // Add word.
                $this->content .= $word;
                $line_length += $word_length;
            }
        }

        return $this;
    }

    /**
     * Set the reply expected recipient.
     */
    public function setReplyRecipient(
        string $address,
        null|string $name = null,
    ): static {
        $this->replyTo = new Mailbox($address, $name);
        return $this;
    }

    /**
     * Set the message sender.
     */
    public function setSender(
        string $address,
        null|string $name = null,
    ): static {
        $this->from = new Mailbox($address, $name);
        
        return $this;
    }

    /**
     * Set the message subject.
     */
    public function setSubject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }
}
