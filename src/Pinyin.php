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

namespace MuCTS\Pinyin;


use MuCTS\Pinyin\Exceptions\InvalidArgumentException;
use MuCTS\Pinyin\Interfaces\DictLoader;
use MuCTS\Pinyin\Loaders\File;
use MuCTS\Support\Str;

class Pinyin
{
    public const DEFAULT = 4096;
    /** @var int UNICODE 式音调：měi hǎo */
    public const TONE = 2;
    /** @var int 无音调：mei hao */
    public const NO_TONE = 4;
    /** @var int 带数字式音调： mei3 hao3 */
    public const ASCII_TONE = 8;
    /** @var int 翻译姓名 */
    public const NAME = 16;
    /** @var int 保留数字 */
    public const KEEP_NUMBER = 32;
    /** @var int 保留英文 */
    public const KEEP_ENGLISH = 64;
    /** @var int 使用 v 代替 yu, 例如：吕 lyu 将会转为 lv */
    public const UMLAUT_V = 128;
    /** @var int  保留标点 */
    public const KEEP_PUNCTUATION = 256;
    /**
     * Dict loader.
     *
     * @var DictLoader|string
     */
    private $loader;
    /** @var string */
    private string $dataPath;

    /**
     * Punctuations map.
     *
     * @var array
     */
    protected array $punctuations = [
        '，' => ',',
        '。' => '.',
        '！' => '!',
        '？' => '?',
        '：' => ':',
        '“' => '"',
        '”' => '"',
        '‘' => "'",
        '’' => "'",
        '_' => '_',
    ];

    /**
     * Constructor.
     *
     * @param string|null $loader
     * @param string|null $path
     */
    public function __construct(?string $loader = null, ?string $path = null)
    {
        $this->setLoader($loader);
        $this->setDataPath($path);
    }

    /**
     * Convert string to pinyin.
     *
     * @param string $string
     * @param int $option
     *
     * @return array
     */
    public function convert(string $string, int $option = self::DEFAULT): array
    {
        $pinyin = $this->romanize($string, $option);

        return $this->splitWords($pinyin, $option);
    }

    /**
     * Convert string (person name) to pinyin.
     *
     * @param string $string
     * @param int $option
     *
     * @return array
     */
    public function name(string $string, int $option = self::NAME): array
    {
        $option = $option | self::NAME;

        $pinyin = $this->romanize($string, $option);

        return $this->splitWords($pinyin, $option);
    }

    /**
     * Return a pinyin permalink from string.
     *
     * @param string $string
     * @param string|int $delimiter
     * @param int $option
     *
     * @return string
     */
    public function permalink(string $string, $delimiter = '-', int $option = self::DEFAULT): string
    {
        if (is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, '-'];
        }

        if (!in_array($delimiter, ['_', '-', '.', ''], true)) {
            throw new InvalidArgumentException("Delimiter must be one of: '_', '-', '', '.'.");
        }

        return implode($delimiter, $this->convert($string, $option | self::KEEP_NUMBER | self::KEEP_ENGLISH));
    }

    /**
     * Return first letters.
     *
     * @param string $string
     * @param string|int $delimiter
     * @param int $option
     *
     * @return string
     */
    public function abbr(string $string, $delimiter = '', int $option = self::DEFAULT): string
    {
        if (is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, ''];
        }

        return implode($delimiter, array_map(function ($pinyin) {
            return is_numeric($pinyin) ? $pinyin : mb_substr($pinyin, 0, 1);
        }, $this->convert($string, $option)));
    }

    /**
     * Chinese phrase to pinyin.
     *
     * @param string $string
     * @param string|int $delimiter
     * @param int $option
     *
     * @return string
     */
    public function phrase(string $string, $delimiter = ' ', int $option = self::DEFAULT): string
    {
        if (is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, ' '];
        }

        return implode($delimiter, $this->convert($string, $option));
    }

    /**
     * Chinese to pinyin sentence.
     *
     * @param string|int $string
     * @param string|int $delimiter
     * @param int $option
     *
     * @return string
     */
    public function sentence($string, $delimiter = ' ', $option = self::NO_TONE)
    {
        if (is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, ' '];
        }

        return implode($delimiter, $this->convert($string, $option | self::KEEP_PUNCTUATION | self::KEEP_ENGLISH | self::KEEP_NUMBER));
    }

    /**
     * Loader setter.
     *
     * @param DictLoader|string|null $loader
     *
     * @return $this
     */
    public function setLoader($loader = null)
    {
        if (is_null($loader)) {
            $this->loader = File::class;
            return $this;
        }
        if ($loader instanceof DictLoader) {
            $this->loader = $loader;
            return $this;
        }
        if (is_string($loader)) {
            if (class_exists($loader) && in_array(DictLoader::class, class_implements($loader))) {
                $this->loader = $loader;
                return $this;
            }
            $loader = __NAMESPACE__ . '\\Loaders\\' . Str::studly($loader);
            if (class_exists($loader) && in_array(DictLoader::class, class_implements($loader))) {
                $this->loader = $loader;
                return $this;
            }
        }
        throw new InvalidArgumentException('This\'s not valid dict loader class.');
    }

    /**
     * Return dict loader,.
     *
     * @return DictLoader
     */
    public function getLoader(): DictLoader
    {
        if (!($this->loader instanceof DictLoader)) {
            $loaderName = $this->loader;
            $this->loader = new $loaderName($this->dataPath);
        }

        return $this->loader;
    }

    /**
     * Set data path
     *
     * @param string|null $path
     * @return $this
     */
    public function setDataPath(?string $path): self
    {
        $path = $path ?? dirname(__DIR__) . '/data/';
        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf('\'%s\' is not valid data path.', $path));
        }
        $this->dataPath = $path;
        return $this;
    }

    /**
     * Convert Chinese to pinyin.
     *
     * @param string $string
     * @param int $option
     *
     * @return string
     */
    protected function romanize(string $string, int $option = self::DEFAULT): string
    {
        $string = $this->prepare($string, $option);

        $dictLoader = $this->getLoader();

        if ($this->hasOption($option, self::NAME)) {
            $string = $this->convertSurname($string, $dictLoader);
        }

        $dictLoader->map(function ($dictionary) use (&$string) {
            $string = strtr($string, $dictionary);
        });

        return $string;
    }

    /**
     * Convert Chinese Surname to pinyin.
     *
     * @param string $string
     * @param DictLoader $dictLoader
     *
     * @return string
     */
    protected function convertSurname(string $string, DictLoader $dictLoader): string
    {
        $dictLoader->mapSurname(function ($dictionary) use (&$string) {
            foreach ($dictionary as $surname => $pinyin) {
                if (0 === strpos($string, $surname)) {
                    $string = $pinyin . mb_substr($string, mb_strlen($surname, 'UTF-8'), mb_strlen($string, 'UTF-8') - 1, 'UTF-8');

                    break;
                }
            }
        });

        return $string;
    }

    /**
     * Split pinyin string to words.
     *
     * @param string $pinyin
     * @param int $option
     *
     * @return array
     */
    protected function splitWords(string $pinyin, int $option): array
    {
        $split = preg_split('/\s+/i', $pinyin);
        if (!is_array($split)) {
            throw new InvalidArgumentException(sprintf('\'%s\' is not valid pinyin.', $pinyin));
        }
        $split = array_filter($split);

        if (!$this->hasOption($option, self::TONE)) {
            foreach ($split as $index => $pinyin) {
                $split[$index] = $this->formatTone($pinyin, $option);
            }
        }

        return array_values($split);
    }

    /**
     * @param int $option
     * @param int $check
     *
     * @return bool
     */
    public function hasOption(int $option, int $check): bool
    {
        return ($option & $check) === $check;
    }

    /**
     * Pre-process.
     *
     * @param string $string
     * @param int $option
     *
     * @return string
     */
    protected function prepare(string $string, int $option = self::DEFAULT): string
    {
        $string = preg_replace_callback('~[a-z0-9_-]+~i', function ($matches) {
            return "\t" . $matches[0];
        }, $string);

        $regex = ['\p{Han}', '\p{Z}', '\p{M}', "\t"];

        if ($this->hasOption($option, self::KEEP_NUMBER)) {
            array_push($regex, '0-9');
        }

        if ($this->hasOption($option, self::KEEP_ENGLISH)) {
            array_push($regex, 'a-zA-Z');
        }

        if ($this->hasOption($option, self::KEEP_PUNCTUATION)) {
            $punctuations = array_merge($this->punctuations, ["\t" => ' ', '  ' => ' ']);
            $string = trim(str_replace(array_keys($punctuations), $punctuations, $string));

            array_push($regex, preg_quote(implode(array_merge(array_keys($this->punctuations), $this->punctuations)), '~'));
        }

        return preg_replace(sprintf('~[^%s]~u', implode($regex)), '', $string);
    }

    /**
     * Format.
     *
     * @param string $pinyin
     * @param int $option
     *
     * @return string
     */
    protected function formatTone(string $pinyin, int $option = self::NO_TONE): string
    {
        $replacements = [
            'üē' => ['ue', 1], 'üé' => ['ue', 2], 'üě' => ['ue', 3], 'üè' => ['ue', 4],
            'ā' => ['a', 1], 'ē' => ['e', 1], 'ī' => ['i', 1], 'ō' => ['o', 1], 'ū' => ['u', 1], 'ǖ' => ['yu', 1],
            'á' => ['a', 2], 'é' => ['e', 2], 'í' => ['i', 2], 'ó' => ['o', 2], 'ú' => ['u', 2], 'ǘ' => ['yu', 2],
            'ǎ' => ['a', 3], 'ě' => ['e', 3], 'ǐ' => ['i', 3], 'ǒ' => ['o', 3], 'ǔ' => ['u', 3], 'ǚ' => ['yu', 3],
            'à' => ['a', 4], 'è' => ['e', 4], 'ì' => ['i', 4], 'ò' => ['o', 4], 'ù' => ['u', 4], 'ǜ' => ['yu', 4],
        ];

        foreach ($replacements as $unicode => $replacement) {
            if (false !== strpos($pinyin, $unicode)) {
                $umlaut = $replacement[0];

                if ($this->hasOption($option, self::UMLAUT_V) && 'yu' == $umlaut) {
                    $umlaut = 'v';
                }

                $pinyin = str_replace($unicode, $umlaut, $pinyin) . ($this->hasOption($option, self::ASCII_TONE) ? $replacement[1] : '');
            }
        }

        return $pinyin;
    }
}
