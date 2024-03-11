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

namespace Laucov\WebFramework\Services;

use Laucov\Lang\MessageRepository;
use Laucov\WebFramework\Config\Language;
use Laucov\WebFramework\Services\Interfaces\ServiceInterface;

/**
 * Provides an interface to multi-language support.
 */
class LanguageService implements ServiceInterface
{
    /**
     * Currently selected locales.
     * 
     * @var array<string>
     */
    protected array $locales = [];

    /**
     * Message repository.
     */
    protected MessageRepository $repository;

    /**
     * Create the language service instance.
     */
    public function __construct(
        /**
         * Language configuration.
         */
        protected Language $config,
    ) {
        // Create and configure the repository.
        $this->repository = new MessageRepository();
        $this->update();
    }

    /**
     * Find and format a message.
     */
    public function findMessage(string $path, array $args): string
    {
        return $this->repository->findMessage($path, $args) ?? $path;
    }

    /**
     * Get the current locale in use.
     */
    public function getLocale(): string
    {
        // Get supported AND accepted locales.
        $locales = array_intersect(
            $this->locales,
            $this->config->supportedLocales,
        );

        // Return default locale if couldn't negociate.
        if (count($locales) < 1) {
            return $this->config->defaultLocale;
        }

        // Resolve redirections.
        $locale = $locales[array_key_first($locales)];
        while (array_key_exists($locale, $this->config->redirects)) {
            $locale = $this->config->redirects[$locale];
        }

        return $locale;
    }

    /**
     * Set the locale options to use.
     */
    public function setLocale(string ...$locales): static
    {
        $this->locales = $locales;
        $this->repository->setAcceptedLanguages(...$locales);

        return $this;
    }

    /**
     * Update the repository settings according to the config object.
     */
    protected function update(): static
    {
        // Set available languages.
        $this->repository->defaultLanguage = $this->config->defaultLocale;
        $this->repository->setSupportedLanguages(...$this->config->supportedLocales);

        // Set data and directories.
        foreach ($this->config->data as $locale => $data) {
            $this->repository->setLanguageData($locale, $data);
        }
        foreach ($this->config->directories as $dir) {
            $this->repository->addDirectory($dir);
        }

        // Set redirects.
        foreach ($this->config->redirects as $from => $to) {
            $this->repository->redirect($from, $to);
        }

        return $this;
    }
}
