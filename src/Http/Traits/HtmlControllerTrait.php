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
use Laucov\WebFwk\Config\Display;
use Laucov\WebFwk\Providers\ConfigProvider;
use Laucov\WebFwk\Providers\ServiceProvider;

/**
 * Provides methods to easily control HTML responses with a controller.
 * 
 * @property ConfigProvider $config
 * @property OutgoingResponse $response
 * @property ServiceProvider $services
 */
trait HtmlControllerTrait
{
    /**
     * Set a view as the response body.
     */
    protected function setView(
        string $path,
        array $data = [],
    ): void {
        // Set aditional data.
        $display = $this->config->getConfig(Display::class);
        $data['app_author'] = $display->author;
        // @todo Set color mode in the SetUp prelude.
        $data['app_color_mode'] = $display->colorMode;
        $data['app_description'] = $display->description;
        $data['app_favicon_path'] = $display->faviconPath;
        $data['app_favicon_type'] = $display->faviconType;
        $data['app_title'] = $display->title;
        $data['lang'] = $this->services->lang();

        // Get the view HTML.
        $view = $this->services
            ->view()
            ->getView($path);
        $html = $view->get($data);

        // Set the response body.
        $this->response
            ->setStatus(200, 'OK')
            ->setHeaderLine('Content-Type', 'text/html')
            ->setBody($html);
    }
}
