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

/**
 * Definition defining a pre-created instance of an object
 * 
 * Allowed formats: 
 * 
 * <b>Short</b>
 * Just a string specyfying the referenced id in the container:
 * Example:
 * <code>
 *    new  AliasDefinition($object);
 * </code>
 * 
 * <b>Full Declaration</b>
 * An associative array containing the following items:
 * <ul>
 *   <li><b>'type'</b>: Optional, but MUST be <code>'alias'</code> if specified<li>
 *   <li><b>'instance'</b>: The Object (required).</li>
 *   <li><b>'shared'</b>: Optional, but MUST be <code>true<code> if specified. (Default: <code>true</code></li>
 * </ul>
 * 
 * <code>
 *    new  AliasDefinition(
 *      ['type' => 'instance',
 *       'instance' => $object);
 * </code>
 * 
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
class InstanceDefinition extends AbstractDefinition
{
    public $_shared = true;
    
    public function configure(array $definition)
    {
        parent::configure($definition);
        if (!isset($this->func)) {
            throw InvalidConfigurationException::missingDefinitionArrayItem('instance', 'Instance Definition');
        }
    }
    
    public function setShared($shared)
    {
        if (!$shared) {
            throw new InvalidConfigurationException('Precreated objects cannot be defined non-shared');
        }
    }
    
    public function getConfigurationArray(): array
    {
        return ['type' => $this->getType(),
            'instance' => $this->getInstance()];
    }

    protected function doResolve(\Psr\Container\ContainerInterface $container): ?object
    {
        return $this->getInstance();
    }

}
