<p align="center"><img src="https://images.mucts.com/image/exp_def_white.png" width="400"></p>
<p align="center">
    <a href="https://scrutinizer-ci.com/g/mucts/pinyin"><img src="https://scrutinizer-ci.com/g/mucts/pinyin/badges/build.png" alt="Build Status"></a>
    <a href="https://scrutinizer-ci.com/g/mucts/pinyin"><img src="https://scrutinizer-ci.com/g/mucts/pinyin/badges/code-intelligence.svg" alt="Code Intelligence Status"></a>
    <a href="https://scrutinizer-ci.com/g/mucts/pinyin"><img src="https://scrutinizer-ci.com/g/mucts/pinyin/badges/quality-score.png" alt="Scrutinizer Code Quality"></a>
    <a href="https://packagist.org/packages/mucts/pinyin"><img src="https://poser.pugx.org/mucts/pinyin/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/mucts/pinyin"><img src="https://poser.pugx.org/mucts/pinyin/v/stable.svg" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/mucts/pinyin"><img src="https://poser.pugx.org/mucts/pinyin/license.svg" alt="License"></a>
</p>

# Pinyin

> 基于 [CC-CEDICT](http://cc-cedict.org/wiki/) 词典的中文转拼音工具，更准确的支持多音字的汉字转拼音解决方案。

## 安装

使用 Composer 安装:

```
$ composer require mucts/pinyin
```

## 使用

可选转换方案：

- 内存型，适用于服务器内存空间较富余，优点：转换快
- 小内存型(默认)，适用于内存比较紧张的环境，优点：占用内存小，转换不如内存型快
- I/O型，适用于虚拟机，内存限制比较严格环境。优点：非常微小内存消耗。缺点：转换慢，不如内存型转换快,php >= 5.5

## 可用选项：

|      选项      | 描述                                                |
| -------------  | ---------------------------------------------------|
| `TONE`  | UNICODE 式音调：`měi hǎo`                    |
| `ASCII_TONE`  | 带数字式音调：  `mei3 hao3`                    |
| `NO_TONE`    |  无音调：`mei hao` | 
| `KEEP_NUMBER`    | 保留数字  | 
| `KEEP_ENGLISH`   | 保留英文   | 
| `KEEP_PUNCTUATION`   |  保留标点  | 
| `UMLAUT_V` | 使用 `v` 代替 `yu`, 例如：吕 `lyu` 将会转为 `lv` |

### 拼音数组

```php
use MuCTS\Pinyin\Pinyin;
use MuCTS\Pinyin\Loaders\MemoryFile;
use MuCTS\Pinyin\Loaders\GeneratorFile;

// 小内存型
$pinyin = new Pinyin(); // 默认
// 内存型
// $pinyin = new Pinyin(MemoryFile::class);
// I/O型
// $pinyin = new Pinyin(GeneratorFile::class);

$pinyin->convert('带着希望去旅行，比到达终点更美好');
// ["dai", "zhe", "xi", "wang", "qu", "lyu", "xing", "bi", "dao", "da", "zhong", "dian", "geng", "mei", "hao"]

$pinyin->convert('带着希望去旅行，比到达终点更美好', Pinyin::TONE);
// ["dài","zhe","xī","wàng","qù","lǚ","xíng","bǐ","dào","dá","zhōng","diǎn","gèng","měi","hǎo"]

$pinyin->convert('带着希望去旅行，比到达终点更美好', Pinyin::ASCII_TONE);
//["dai4","zhe","xi1","wang4","qu4","lyu3","xing2","bi3","dao4","da2","zhong1","dian3","geng4","mei3","hao3"]
```

- 小内存型: 将字典分片载入内存
- 内存型: 将所有字典预先载入内存
- I/O型: 不载入内存，将字典使用文件流打开逐行遍历并运用php5.5生成器(yield)特性分配单行内存


### 生成用于链接的拼音字符串

```php
$pinyin->permalink('带着希望去旅行'); // dai-zhe-xi-wang-qu-lyu-xing
$pinyin->permalink('带着希望去旅行', '.'); // dai.zhe.xi.wang.qu.lyu.xing
```

### 获取首字符字符串

```php
$pinyin->abbr('带着希望去旅行'); // dzxwqlx
$pinyin->abbr('带着希望去旅行', '-'); // d-z-x-w-q-l-x

$pinyin->abbr('你好2018！', Pinyin::KEEP_NUMBER); // nh2018
$pinyin->abbr('Happy New Year! 2018！', Pinyin::KEEP_ENGLISH); // HNY2018
```

### 翻译整段文字为拼音

将会保留中文字符：`，。 ！ ？ ： “ ” ‘ ’` 并替换为对应的英文符号。

```php
$pinyin->sentence('带着希望去旅行，比到达终点更美好！');
// dai zhe xi wang qu lyu xing, bi dao da zhong dian geng mei hao!

$pinyin->sentence('带着希望去旅行，比到达终点更美好！', Pinyin::TONE);
// dài zhe xī wàng qù lǚ xíng, bǐ dào dá zhōng diǎn gèng měi hǎo!
```

### 翻译姓名

姓名的姓的读音有些与普通字不一样，比如 ‘单’ 常见的音为 `dan`，而作为姓的时候读 `shan`。

```php
$pinyin->name('单某某'); // ['shan', 'mou', 'mou']
$pinyin->name('单某某', Pinyin::TONE); // ["shàn","mǒu","mǒu"]
```
