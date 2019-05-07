<?php

/*
 * BSD 3-Clause License
 * 
 * Copyright (c) 2019, Abexto - Helicon Software Development / Amylian Project
 *  
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * 
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * 
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 */

namespace Amylian\Container\Basic\Definition;

use Amylian\Container\Basic\Exception\InvalidConfigurationException;

/**
 * Definition an Alias
 * 
 * Allowed formats: 
 * 
 * <b>Short</b>
 * Just a string specyfying the referenced id in the container:
 * Example:
 * <code>
 *    new  AliasDefinition('TheReferencedId');
 * </code>
 * 
 * <b>Full Declaration</b>
 * An associative array containing the following items:
 * <ul>
 *   <li><b>'type'</b>: Optional, but MUST be <code>'alias'</code> if specified<li>
 *   <li><b>'of'</b>: Referenced Id (required).</li>
 *   <li><b>'shared'</b>: If <code>true</code>, getting always returns the same instance.
 *      As this is an alias pointing to another id, setting it to <code>false</code> does
 *      not guarantee a new instance at each request, but depends on the setting
 *      of the referenced id. (Default: false) 
 *   </li>
 * </ul>
 * 
 * <code>
 *    new  AliasDefinition(['aliasOf' => 'TheReferencedId']);
 * </code>
 * 
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
class AliasDefinition extends AbstractDefinition
{

    protected $_shared = false;
    protected $_aliasOf = null;

    public function setAliasOf($of)
    {
        $this->_aliasOf = $of;
    }

    public function getAliasOf()
    {
        return $this->_aliasOf;
    }
    
    public function configure(array $definition)
    {
        parent::configure($definition);
        if (!$this->_aliasOf) {
            throw InvalidConfigurationException::missingDefinitionArrayItem('aliasOf', 'Alias Definition');
        }
    }

    public static function supportsDefinition($definition): bool
    {
        $result = parent::supportsDefinition($definition);
        if (!$result) {
            $result = (is_string($definition) || isset($definition['aliasOf']));
        }
        return $result;
    }

    public function getConfigurationArray(): array
    {
        $result = parent::getConfigurationArray();
        return array_merge($result,
                [
                    'aliasOf' => $this->getAliasOf()
                ]
        );
    }

    protected function doResolve(\Psr\Container\ContainerInterface $container): ?object
    {
        return $container->get($this->_aliasOf);
    }

}
