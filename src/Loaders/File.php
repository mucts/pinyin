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

namespace MuCTS\Pinyin\Loaders;

use Closure;
use MuCTS\Pinyin\Exceptions\PinyinException;
use MuCTS\Pinyin\Interfaces\DictLoader;
use MuCTS\Support\Arr;

class File implements DictLoader
{
    /**
     * Words segment name.
     *
     * @var string
     */
    protected string $segmentName = 'words_*';

    /**
     * Dict path.
     *
     * @var string
     */
    protected string $path;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Load dict.
     *
     * @param Closure $callback
     * @throws PinyinException
     */
    public function map(Closure $callback)
    {
        $segments = glob($this->path . '/' . $this->segmentName);
        if ($segments == false) {
            throw new PinyinException('CC-CEDICT dictionary data does not exist');
        }
        while (($segment = array_shift($segments))) {
            $dictionary = Arr::wrap(include $segment);
            $callback($dictionary);
        }
    }

    /**
     * Load surname dict.
     *
     * @param Closure $callback
     */
    public function mapSurname(Closure $callback)
    {
        $surnames = $this->path . '/surnames';

        if (file_exists($surnames)) {
            $dictionary = Arr::wrap(include $surnames);
            $callback($dictionary);
        }
    }
}
