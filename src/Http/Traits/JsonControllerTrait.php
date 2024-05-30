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

namespace Laucov\WebFwk\Http\Traits;

use Laucov\Http\Message\OutgoingResponse;
use Laucov\Http\Message\RequestInterface;
use Laucov\Http\Routing\Exceptions\HttpException;
use Laucov\Modeling\Entity\AbstractEntity;
use Laucov\Validation\Error;
use Laucov\WebFwk\Entities\Input;

/**
 * Provides methods to easily control JSON messages with a controller.
 * 
 * @property OutgoingResponse $response
 */
trait JsonControllerTrait
{
    /**
     * Get data from a JSON request in form of an entity.
     * 
     * Searches for a "data" key and parses it as the specified entity.
     * 
     * @template T of AbstractEntity
     * @param class-string<T> $entity Entity class name.
     * @return T
     */
    protected function getEntity(array $data, string $entity): mixed
    {
        // Instantiate.
        /** @var T */
        $object = new $entity();

        // Set data.
        $invalid = [];
        foreach ($data as $name => $value) {
            try {
                $object->$name = $value;
            } catch (\TypeError $error) {
                $property = new \ReflectionProperty($object, $name);
                $type = (string) $property->getType();
                $invalid[] = sprintf('"%s" (%s)', $name, $type);
            }
        }

        // Handle type errors.
        $invalid_count = count($invalid);
        if ($invalid_count > 0) {
            // Create message.
            $message = $this->findMessage(
                'error.invalid_entity_fields',
                [
                    'count' => $invalid_count,
                    'list' => implode(', ', array_slice($invalid, 0, -1)),
                    'last' => array_slice($invalid, -1)[0] ?? '',
                ],
            );
            // Set and throw response.
            $this->response->setStatus(422, 'Unprocessable Entity');
            $this->setJson([
                'messages' => [
                    ['content' => $message, 'type' => 'error'],
                ],
            ]);
            throw new HttpException($this->response);
        }

        // Validate.
        if (!$object->validate()) {
            // Format errors.
            $errors = [];
            foreach ($object->getErrorKeys() as $name) {
                $prop_errors = $object->getErrors($name);
                // Get error text from each rule.
                array_walk($prop_errors, function (Error &$error) {
                    $path = $error->message ?? "validation.{$error->rule}";
                    $error = $this->findMessage($path, $error->parameters);
                });
                $errors[$name] = $prop_errors;
            }
            // Set response and throw.
            $this->response->setStatus(422, 'Unprocessable Entity');
            $this->setJson(['errors' => $errors]);
            throw new HttpException($this->response);
        }

        return $object;
    }

    /**
     * Get the JSON data from the given request.
     * 
     * Automatically sets response data if the JSON is invalid.
     */
    protected function getJson(RequestInterface $request): Input
    {
        // Check Content-Type header.
        $type = $request->getHeaderLine('Content-Type');
        if ($type !== 'application/json') {
            // Inform that the MIME type is invalid.
            $this->response->setStatus(415, 'Unsupported Media Type');
            $this->setJson([
                'messages' => [
                    [
                        'content' => $this->findMessage(
                            'error.unsupported_mime_type',
                            [$type],
                        ),
                        'type' => 'error',
                    ],
                    [
                        'content' => $this->findMessage(
                            'info.supported_mime_types',
                            ['application/json'],
                        ),
                        'type' => 'info',
                    ],
                ],
            ]);
            throw new HttpException($this->response);
        }

        // Check data.
        $json = (string) $request->getBody();
        $data = json_decode($json, true);
        if (!is_array($data)) {
            // Inform that the JSON string is invalid.
            $this->response->setStatus(400, 'Bad Request');
            $this->setJson([
                'messages' => [
                    [
                        'content' => $this->findMessage('error.invalid_json'),
                        'type' => 'error',
                    ],
                ],
            ]);
            throw new HttpException($this->response);
        }

        // Format as an entity.
        $entity = new Input();
        foreach ($data as $key => $value) {
            try {
                // Set value.
                $entity->$key = $value;
            } catch (\TypeError $error) {
                // Create message.
                $property = new \ReflectionProperty($entity, $key);
                $type = (string) $property->getType();
                $message = $this->findMessage(
                    'error.invalid_input_field',
                    [sprintf('"%s" (%s)', $key, $type)],
                );
                // Set and throw response.
                $this->response->setStatus(422, 'Unprocessable Entity');
                $this->setJson([
                    'messages' => [
                        ['content' => $message, 'type' => 'error'],
                    ],
                ]);
                throw new HttpException($this->response);
            }
        }

        return $entity;
    }

    /**
     * Set the JSON of the given array as the response body.
     */
    protected function setJson(array $data): void
    {
        // Create JSON string.
        $json = json_encode($data);

        // Set the response body.
        $this->response
            ->setHeaderLine('Content-Type', 'application/json')
            ->setBody($json);
    }
}
