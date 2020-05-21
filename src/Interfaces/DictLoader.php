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

namespace MuCTS\Pinyin\Interfaces;

use Closure;

interface DictLoader
{
    /**
     * Load dict.
     *
     * <pre>
     * [
     *     '响应时间' => "[\t]xiǎng[\t]yìng[\t]shí[\t]jiān",
     *     '长篇连载' => '[\t]cháng[\t]piān[\t]lián[\t]zǎi',
     *     //...
     * ]
     * </pre>
     *
     * @param Closure $callback
     */
    public function map(Closure $callback);

    /**
     * Load surname dict.
     *
     * @param Closure $callback
     */
    public function mapSurname(Closure $callback);
}
