<?php
/**
 * This file is part of the mucts/pinyin.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @version 1.0
 * @author herry<yuandeng@aliyun.com>
 * @copyright © 2020 MuCTS.com All Rights Reserved.
 */

namespace MuCTS\Pinyin\Test;

use Closure;
use MuCTS\Pinyin\Interfaces\DictLoader;
use MuCTS\Pinyin\Pinyin;
use PHPUnit\Framework\TestCase;

class PinyinTest extends TestCase
{
    public function testLoaderSetter()
    {
        $pinyin = new Pinyin();

        $loader = new Mock();

        $pinyin->setLoader($loader);

        $this->assertSame($loader, $pinyin->getLoader());
        $this->assertSame('foo bar', $pinyin->sentence('你好'));
    }
}

/**
 * Mocker loader.
 */
class Mock implements DictLoader
{
    public function map(Closure $callback)
    {
        $dictionary = array(
                '你好' => "foo\tbar",
            );
        $callback($dictionary);
    }

    public function mapSurname(Closure $callback)
    {
        $dictionary = array(
                '单' => 'shan',
                '朴' => 'piao',
                '尉迟' => 'yu chi',
            );
        $callback($dictionary);
    }
}
