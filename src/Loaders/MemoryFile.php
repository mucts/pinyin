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
use MuCTS\Pinyin\Interfaces\DictLoader;

class MemoryFile implements DictLoader
{

    /**
     * Data directory.
     *
     * @var string
     */
    protected string $path;

    /**
     * Words segment name.
     *
     * @var string
     */
    protected string $segmentName = 'words_%s';

    /**
     * Segment files.
     *
     * @var array
     */
    protected array $segments = [];

    /**
     * Surname cache.
     *
     * @var array
     */
    protected array $surnames = [];

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;

        for ($i = 0; $i < 100; ++$i) {
            $segment = $path . '/' . sprintf($this->segmentName, $i);

            if (file_exists($segment)) {
                $this->segments[] = (array)include $segment;
            }
        }
    }

    /**
     * Load dict.
     *
     * @param Closure $callback
     */
    public function map(Closure $callback)
    {
        foreach ($this->segments as $dictionary) {
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
        if (empty($this->surnames)) {
            $surnames = $this->path . '/surnames';

            if (file_exists($surnames)) {
                $this->surnames = (array)include $surnames;
            }
        }

        $callback($this->surnames);
    }
}
