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

namespace Amylian\Container\Basic\Testing\Unit;

/**
 * Description of ContainerTest
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
class ContainerTest extends \PHPUnit\Framework\TestCase
{

    protected function createStandardTestContainer()
    {
        $result = new \Amylian\Container\Basic\Container(
                ['definitions' => [
                // Foo defined in short syntax
                \Amylian\Container\Basic\Testing\Misc\Foo::class => ['func' => function(\Psr\Container\ContainerInterface $container) {
                        return new \Amylian\Container\Basic\Testing\Misc\Foo();
                    }],
                // Bar defined as array
                \Amylian\Container\Basic\Testing\Misc\Bar::class => [
                    'func' => function(\Psr\Container\ContainerInterface $container) {
                        return new \Amylian\Container\Basic\Testing\Misc\Bar($container->get('foo'));
                    },
                    'shared' => false],
                // FooInterface is an Alias for Foo-Class (Short Syntax)
                \Amylian\Container\Basic\Testing\Misc\FooInterface::class => ['aliasOf' => \Amylian\Container\Basic\Testing\Misc\Foo::class],
                // BarInterface is an Alias for Bar-Class (Array Snytax)
                \Amylian\Container\Basic\Testing\Misc\BarInterface::class => [
                    'aliasOf' => \Amylian\Container\Basic\Testing\Misc\Bar::class],
                // foo-alias                             
                'foo' => ['aliasOf' => \Amylian\Container\Basic\Testing\Misc\FooInterface::class],
                'bar' => ['aliasOf' => \Amylian\Container\Basic\Testing\Misc\BarInterface::class, 'shared' => false,
                ],
                'fooShared' => [
                    'aliasOf' => 'foo',
                    'shared' => true,
                ]
            ]]
        );
        return $result;
    }
    
    protected function doesContainerGetReturnSameInstance(\Psr\Container\ContainerInterface $container, $id1, $id2 = null)
    {
        $id2 = $id2 ?? $id1;
        $i1 = $container->get($id1);
        $i2 = $container->get($id2 ?? $id1);
        return $i1 === $i2;
    }

    public function testCreateContainer()
    {
        $container = new \Amylian\Container\Basic\Container();
        $this->assertInstanceOf(\Amylian\Container\Basic\Container::class, $container);
    }

    public function testValidateOk()
    {
        $container = $this->createStandardTestContainer();
        $this->assertTrue($container->validate());
    }

    public function testFooIsSharedByClass()
    {
        $container = $this->createStandardTestContainer();
        $this->assertTrue($this->doesContainerGetReturnSameInstance($container, \Amylian\Container\Basic\Testing\Misc\Foo::class));
    }

    public function testFooNotSharedByInterface()
    {
        $container = $this->createStandardTestContainer();
        $this->assertTrue($this->doesContainerGetReturnSameInstance($container, \Amylian\Container\Basic\Testing\Misc\Foo::class));
    }

    public function testFooNotSharedByAlias()
    {
        $container = $this->createStandardTestContainer();
        $this->assertTrue($this->doesContainerGetReturnSameInstance($container, 'foo'));
    }

    public function testFooSharedIsShared()
    {
        $container = $this->createStandardTestContainer();
        $this->assertTrue($this->doesContainerGetReturnSameInstance($container, 'fooShared'));
    }
    
    public function testBarNotSharedIsNotSharedByClass()
    {
        $container = $this->createStandardTestContainer();
        $this->assertFalse($this->doesContainerGetReturnSameInstance($container, \Amylian\Container\Basic\Testing\Misc\Bar::class));
    }
    
    public function testBarNotSharedIsNotSharedByInterface()
    {
        $container = $this->createStandardTestContainer();
        $this->assertFalse($this->doesContainerGetReturnSameInstance($container, \Amylian\Container\Basic\Testing\Misc\BarInterface::class));
    }
    
    public function testBarNotSharedIsNotSharedByAlias()
    {
        $container = $this->createStandardTestContainer();
        $this->assertFalse($this->doesContainerGetReturnSameInstance($container,'bar'));
    }

}
