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

use MuCTS\Pinyin\Loaders\GeneratorFile;
use MuCTS\Pinyin\Pinyin;

/**
 * Generator syntax(yield) Dict File loader test.
 *
 * @author Seven Du <shiweidu@outlook.com>
 */
class GeneratorFileTest extends PinyinTestCase
{
    protected function setUp(): void
    {
        $this->pinyin = new Pinyin(GeneratorFile::class);
    }
}
