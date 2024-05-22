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
     * @var array<string>
     */
    protected array $bcc = [];

    /**
     * Carbon copy recipient mailboxes.
     * 
     * @var array<string>
     */
    protected array $cc = [];

    /**
     * Message main content.
     */
    protected string $content = '';

    /**
     * Sender mailbox.
     */
    protected null|string $from = null;

    /**
     * Reply-To mailbox.
     */
    protected null|string $replyTo = null;

    /**
     * Message subject.
     */
    protected null|string $subject = null;

    /**
     * Main recipient mailboxes.
     * 
     * @var array<string>
     */
    protected array $to = [];

    /**
     * Main content MIME subtype.
     */
    protected string $type = 'plain';

    /**
     * Gets a string representation of the message.
     */
    public function __toString(): string
    {
        // Build envelope.
        $envelope = [];
        if ($this->from !== null) {
            $envelope['from'] = $this->from;
        }
        if ($this->replyTo !== null) {
            $envelope['reply_to'] = $this->replyTo;
        }
        if (count($this->to) > 0) {
            $envelope['to'] = implode(', ', $this->to);
        }
        if (count($this->cc) > 0) {
            $envelope['cc'] = implode(', ', $this->cc);
        }
        if ($this->subject !== null) {
            $envelope['subject'] = $this->subject;
        }

        // Build bodies.
        $bodies = [];
        $bodies[0]['type'] = TYPETEXT;
        $bodies[0]['subtype'] = $this->type;
        $bodies[0]['charset'] = 'UTF-8';
        $bodies[0]['contents.data'] = $this->content;

        return imap_mail_compose($envelope, $bodies);
    }

    /**
     * Add a recipient to the message.
     */
    public function addRecipient(
        null|string $address,
        null|string $name = null,
        RecipientType $type = RecipientType::TO,
    ): static {
        $mailbox = $name === null ? $address : "{$name} <{$address}>";
        $this->{$type->value}[] = $mailbox;
        return $this;
    }

    /**
     * Get the reply expected recipient (or the "Reply-To" header value).
     */
    public function getReplyRecipient(): null|string
    {
        return $this->replyTo;
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
     * @return array<string>
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
     * Get the message sender (or the "From" header value).
     */
    public function getSender(): null|string
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
     * Set the message body content.
     * 
     * Ensures each line has 78 characters or less.
     */
    public function setContent(string $content, bool $is_html = false): static
    {
        // Empty current content.
        $this->content = '';

        // Set main content subtype option.
        $this->type = $is_html ? 'html' : 'plain';

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
     * Set the message sender.
     */
    public function setSender(
        string $address,
        null|string $name = null,
        null|string $reply_to_address = null,
        null|string $reply_to_name = null,
    ): static {
        // Set sender mailbox.
        $this->from = $name === null ? $address : "{$name} <{$address}>";
        
        // Set Reply-To header value.
        if ($reply_to_address !== null) {
            $this->replyTo = $reply_to_name === null
                ? $reply_to_address
                : "{$reply_to_name} <{$reply_to_address}>";
        } else {
            $this->replyTo = null;
        }

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
