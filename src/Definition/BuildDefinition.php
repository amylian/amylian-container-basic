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
use Psr\Container\ContainerInterface;

/**
 * Description of CreateDefinition
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
class BuildDefinition extends AbstractDefinition
{
    /**
     * @var callable Function to call to build the object 
     */
    protected $_func;
    

    public static function supportsDefinition($definition): bool
    {
        $result = parent::supportsDefinition($definition);
        if (!$result) {
            $result = (is_callable($definition, true) || isset($definition['func']));
        }
        return $result;
    }
    
    
    public function getConfigurationArray(): array
    {
        $result = parent::getConfigurationArray();
        if (isset($this->_func)) {
            $result['func'] = $this->_func;
        }
    }

    /**
     * Sets the factory-function
     * @param callable $func
     */
    public function setFunc(callable $func)
    {
        $this->_func = $func;
    } 

    /**
     * Returns the factory function
     * @param type $func
     * @return type
     */
    public function getFunc()
    {
        return $this->_func;
    }

    /**
     * Sets the configuration
     * @param array|callable $definition
     * @throws InvalidConfigurationException
     */
    public function configure(array $definition)
    {
        parent::configure($definition);
        if (!$this->func) {
            throw InvalidConfigurationException::missingDefinitionArrayItem('func', 'Callback Definition');
        }
    }

    protected function doResolve(ContainerInterface $container): ?object
    {
        if (isset($this->_func)) {
            return call_user_func($this->_func, $container);
        } else {
            throw new InvalidConfigurationException('No build function set');
        }
    }

}