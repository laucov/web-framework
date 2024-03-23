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

namespace Laucov\WebFwk\Models;

use Laucov\WebFwk\Entities\User;
use Laucov\WebFwk\Modeling\AbstractModel;

/**
 * Provides and saves `User` entities.
 * 
 * @extends AbstractModel<User>
 */
class UserModel extends AbstractModel
{
    /**
     * Entity class name.
     */
    protected string $entityName = User::class;

    /**
     * Primary key column.
     */
    protected string $primaryKey = 'id';

    /**
     * Table name.
     */
    protected string $tableName = 'users';

    /**
     * Retrieve an user by its login name.
     */
    public function retrieveWithLogin(string $login): null|User
    {
        $this->table->filter('login', '=', $login);
        return $this->getEntity();
    }
}
