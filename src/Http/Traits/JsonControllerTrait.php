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
use Laucov\Modeling\Entity\CreationResult;
use Laucov\Modeling\Entity\TypeError;
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
        // Create entity.
        /** @var CreationResult */
        $result = $entity::createFromArray($data);

        // Check type errors.
        if (count($result->typeErrors) > 0) {
            $this->throwEntityTypeError($result->typeErrors);
        }

        // Validate.
        $entity = $result->entity;
        if (!$entity->validate()) {
            $this->throwValidationError($entity);
        }

        return $entity;
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
            $this->throwContentTypeError($type, ['application/json']);
        }

        // Check data.
        $json = (string) $request->getBody();
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $this->throwJsonError();
        }

        // Format as an entity.
        $result = Input::createFromArray($data);
        if (count($result->typeErrors) > 0) {
            $this->throwInputTypeError($result->typeErrors);
        }

        return $result->entity;
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

    /**
     * Throw an `HTTPException` based on Content-Type header error.
     * 
     * @param array<string> $expected
     * @throws HTTPException
     */
    protected function throwContentTypeError(string $type, array $allowed): void
    {
        // Create messages.
        $error_message = $this->findMessage(
            'error.unsupported_mime_type',
            [$type],
        );
        $info_message = $this->findMessage(
            'info.supported_mime_types',
            [implode(', ', $allowed)],
        );

        // Inform that the MIME type is invalid.
        $this->response->setStatus(415, 'Unsupported Media Type');
        $this->setJson([
            'messages' => [
                ['content' => $error_message, 'type' => 'error'],
                ['content' => $info_message, 'type' => 'info'],
            ],
        ]);

        throw new HttpException($this->response);
    }

    /**
     * Throw an `HTTPException` based on entity type errors.
     * 
     * @param array<TypeError> $type_errors
     * @throws HTTPException
     */
    protected function throwEntityTypeError(array $type_errors): void
    {
        // Create property labels.
        $labels = [];
        foreach ($type_errors as $e) {
            $labels[] = sprintf('"%s" (%s)', $e->name, $e->expected);
        }

        // Create message.
        $message = $this->findMessage(
            'error.invalid_entity_fields',
            [
                'count' => count($type_errors),
                'list' => implode(', ', array_slice($labels, 0, -1)),
                'last' => array_slice($labels, -1)[0] ?? '',
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

    /**
     * Throw an `HTTPException` based on input type errors.
     * 
     * @param array<TypeError> $type_errors
     * @throws HTTPException
     */
    protected function throwInputTypeError(array $type_errors): void
    {
        // Create property labels.
        $labels = [];
        foreach ($type_errors as $e) {
            $labels[] = sprintf('"%s" (%s)', $e->name, $e->expected);
        }

        // Create message.
        $message = $this->findMessage(
            'error.invalid_input_fields',
            [
                'count' => count($type_errors),
                'list' => implode(', ', array_slice($labels, 0, -1)),
                'last' => array_slice($labels, -1)[0] ?? '',
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

    /**
     * Throw an `HTTPException` based on entity validation errors.
     * 
     * @throws HTTPException
     */
    protected function throwValidationError(AbstractEntity $entity): void
    {
        // Format errors.
        $errors = [];
        foreach ($entity->getErrorKeys() as $name) {
            $prop_errors = $entity->getErrors($name);
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

    /**
     * Throw an `HTTPException` due to an invalid JSON string payload.
     * 
     * @throws HTTPException
     */
    protected function throwJsonError(): void
    {
        // Inform that the JSON string is invalid.
        $this->response->setStatus(400, 'Bad Request');
        $message = $this->findMessage('error.invalid_json');
        $this->setJson([
            'messages' => [
                ['content' => $message, 'type' => 'error'],
            ],
        ]);

        throw new HttpException($this->response);
    }
}
