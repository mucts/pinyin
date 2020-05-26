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
use Generator;
use MuCTS\Pinyin\Interfaces\DictLoader;
use SplFileObject;

class GeneratorFile implements DictLoader
{
    /**
     * Words segment name.
     *
     * @var string
     */
    protected string $segmentName = 'words_*';

    /**
     * SplFileObjects.
     *
     * @var array
     */
    protected static array $handles = [];

    /**
     * surnames.
     *
     * @var mixed
     */
    protected static $surnamesHandle;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $segments = glob($path . '/' . $this->segmentName);
        while (($segment = array_shift($segments))) {
            array_push(static::$handles, $this->openFile($segment));
        }
        static::$surnamesHandle = $this->openFile($path . '/surnames');
    }

    /**
     * Construct a new file object.
     *
     * @param string $filename file path
     * @param string $mode file open mode
     *
     * @return SplFileObject
     */
    protected function openFile($filename, $mode = 'r')
    {
        return new SplFileObject($filename, $mode);
    }

    /**
     * get Generator syntax.
     *
     * @param array $handles SplFileObjects
     *
     * @return Generator
     */
    protected function getGenerator(array $handles)
    {
        foreach ($handles as $handle) {
            $handle->seek(0);
            while (false === $handle->eof()) {
                $string = str_replace(['\'', ' ', PHP_EOL, ','], '', $handle->fgets());

                if (false === strpos($string, '=>')) {
                    continue;
                }

                list($string, $pinyin) = explode('=>', $string);

                yield $string => $pinyin;
            }
        }
    }

    /**
     * Traverse the stream.
     *
     * @param Generator $generator
     * @param Closure $callback
     *
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function traversing(Generator $generator, Closure $callback)
    {
        foreach ($generator as $string => $pinyin) {
            $callback([$string => $pinyin]);
        }
    }

    /**
     * Load dict.
     *
     * @param Closure $callback
     */
    public function map(Closure $callback)
    {
        $this->traversing($this->getGenerator(static::$handles), $callback);
    }

    /**
     * Load surname dict.
     *
     * @param Closure $callback
     */
    public function mapSurname(Closure $callback)
    {
        $this->traversing($this->getGenerator([static::$surnamesHandle]), $callback);
    }
}
