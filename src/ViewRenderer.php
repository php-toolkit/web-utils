<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-08-30
 * Time: 17:31
 */

namespace Toolkit\Web;

use Toolkit\File\File;
use Toolkit\Web\Helper\HtmlHelper;
use Toolkit\Web\Traits\SimpleAssetsLoaderTrait;

/**
 * Class ViewRenderer
 *  Render PHP view scripts into a PSR-7 Response object
 * @package Toolkit\Web
 */
class ViewRenderer
{
    use SimpleAssetsLoaderTrait;

    /** @var string This is a temp var, it is dir path of current view file. */
    private $currentDir = '';

    /** @var string Views file path. */
    protected $viewsPath = '';

    /** @var null|string Default layout file. */
    protected $layout = '';

    /** @var array Attributes for the view */
    protected $attributes = [];

    /** @var string Default view suffix. */
    protected $suffix = 'php';

    /** @var array Allowed suffix list */
    protected $suffixes = ['php', 'tpl', 'phtml', 'html'];

    /**
     * in layout file '...<body>{__CONTENT__}</body>...'
     * @var string
     */
    protected $placeholder = '{__CONTENT__}';

    /** @var bool clear html blanks. */
    protected $minify = false;

    /**
     * constructor.
     * @param string $viewsPath
     * @param string $layout
     * @param array $attributes
     */
    public function __construct(string $viewsPath = null, string $layout = null, array $attributes = [])
    {
        $this->layout = $layout;
        $this->attributes = $attributes;

        $this->setViewsPath($this->viewsPath);
    }

    /********************************************************************************
     * render methods
     *******************************************************************************/

    /**
     * Render a view, if layout file is setting, will use it.
     * throws RuntimeException if view file does not exist
     * @param string $view
     * @param array $data extract data to view, cannot contain view as a key
     * @param string|null|false $layout Override default layout file.
     *  False - will disable use layout file
     * @return string
     * @throws \RuntimeException
     */
    public function render(string $view, array $data = [], $layout = null): string
    {
        $output = $this->fetch($view, $data);

        // False - will disable use layout file
        if ($layout === false) {
            return $output;
        }

        return $this->renderContent($output, $data, $layout);
    }

    /**
     * @param $view
     * @param array $data
     * @return string
     * @throws \RuntimeException
     */
    public function renderPartial(string $view, array $data = []): string
    {
        return $this->fetch($view, $data);
    }

    /**
     * @param string $content
     * @param array $data
     * @param string $layout override default layout file
     * @return string
     * @throws \RuntimeException
     */
    public function renderBody(string $content, array $data = [], string $layout = ''): string
    {
        return $this->renderContent($content, $data, $layout);
    }

    /**
     * @param string $content
     * @param array $data
     * @param string|null $layout override default layout file
     * @return string
     * @throws \RuntimeException
     */
    public function renderContent(string $content, array $data = [], string $layout = ''): string
    {
        // render layout
        if ($layout = $layout ?: $this->layout) {
            $mark = $this->placeholder;
            $main = $this->fetch($layout, $data);
            $content = (string)\str_replace($mark, $content, $main);
        }

        if ($this->minify) {
            $content = HtmlHelper::minify($content);
        }

        return $content;
    }

    /**
     * @param $view
     * @param array $data
     * @param bool $outputIt
     * @return string|null
     * @throws \RuntimeException
     */
    public function include(string $view, array $data = [], $outputIt = true): string
    {
        if ($view) {
            if (!$outputIt) {
                return $this->fetch($view, $data, false);
            }

            echo $this->fetch($view, $data, false);
        }

        return '';
    }

    /**
     * Renders a view and returns the result as a string
     * throws RuntimeException if $viewsPath . $view does not exist
     * @param string $view
     * @param array $data
     * @param bool $canRelative
     * @return string
     * @throws \RuntimeException
     */
    public function fetch(string $view, array $data = [], $canRelative = true): string
    {
        if (!$view) {
            return '';
        }

        $file = $this->getViewFile($view);

        if (!\is_file($file)) {
            throw new \RuntimeException("cannot render '$view' because the view file does not exist. File: $file");
        }

        $data = \array_merge($this->attributes, $data);

        if ($canRelative) {
            $this->currentDir = \dirname($file) . '/';
        }

        try {
            \ob_start();
            $this->protectedIncludeScope($file, $data);
            $output = \ob_get_clean();
        } catch (\Throwable $e) { // PHP 7+
            \ob_end_clean();
            throw new \RuntimeException("render view file [$file] is failure", -500, $e);
        }

        if ($canRelative) {
            $this->currentDir = '';
        }

        return $output;
    }

    /********************************************************************************
     * helper methods
     *******************************************************************************/

    /**
     * @param string $string
     * @return string
     */
    public function e($string): string
    {
        return \htmlspecialchars((string)$string);
    }

    /**
     * @param $view
     * @return string
     */
    public function getViewFile(string $view): string
    {
        $view = $this->formatView($view);

        if (File::isAbsPath($view)) {
            return $view;
        }

        // include file relative current file.
        $curDir = $this->currentDir;

        if ($curDir && \file_exists($curDir . $view)) {
            return $curDir . $view;
        }

        return $this->viewsPath . $view;
    }

    /**
     * @param string $file
     * @param array $data
     */
    protected function protectedIncludeScope(string $file, array $data)
    {
        \extract($data, \EXTR_OVERWRITE);
        include $file;
    }

    /**
     * format view, ensure view extension name
     * @param string $view
     * @return string
     */
    protected function formatView(string $view): string
    {
        $sfx = File::getSuffix($view, true);
        $ext = $this->suffix;

        if ($sfx === $ext || \in_array($sfx, $this->suffixes, true)) {
            return $view;
        }

        return $view . '.' . $ext;
    }

    /**
     * @param string $default
     * @return string
     */
    public function getTitle(string $default = ''): string
    {
        return $this->attributes['__pageTitle'] ?? $default;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->attributes['__pageTitle'] = $title;

        return $this;
    }

    /**
     * @param string|null $default
     * @return string|array
     */
    public function getKeywords(string $default = ''): string
    {
        return $this->attributes['__page.keywords'] ?? $default;
    }

    /**
     * @param string|null $default
     * @return string|array
     */
    public function getDescription(string $default = ''): string
    {
        return $this->attributes['__page.description'] ?? $default;
    }

    /**
     * @param string $keywords
     * @param string|null $description
     * @return $this
     */
    public function setPageInfo(string $keywords, string $description = null): self
    {
        $this->attributes['__page.keywords'] = $keywords;
        $this->attributes['__page.description'] = $description;

        return $this;
    }

    /**
     * reset attributes
     * @return self
     */
    public function resetAttributes(): self
    {
        $this->attributes = [];

        return $this;
    }

    /**
     * reset
     */
    public function reset()
    {
        $this->resetAttributes();
        $this->resetPageAssets();
    }

    /********************************************************************************
     * getter/setter methods
     *******************************************************************************/

    /**
     * Get the attributes for the renderer
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the attributes for the renderer
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Set an attribute
     * @param $key
     * @param $value
     * @return $this
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Add an attribute
     * @param $key
     * @param $value
     * @return $this
     */
    public function addAttribute(string $key, $value): self
    {
        if (!isset($this->attributes[$key])) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Retrieve an attribute
     * @param string $key
     * @param mixed $default
     * @return array|mixed
     */
    public function getAttribute(string $key, $default = null)
    {
        if (!isset($this->attributes[$key])) {
            return $default;
        }

        return $this->attributes[$key];
    }

    /**
     * Get the view path
     * @return string
     */
    public function getViewsPath(): string
    {
        return $this->viewsPath;
    }

    /**
     * Set the view path
     * @param string $viewsPath
     * @return $this
     */
    public function setViewsPath(string $viewsPath): self
    {
        if ($viewsPath) {
            $this->viewsPath = \rtrim($viewsPath, '/\\') . '/';
        }

        return $this;
    }

    /**
     * Get the layout file
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * Set the layout file
     * @param string $layout
     * @return $this
     */
    public function setLayout(string $layout): self
    {
        $this->layout = \rtrim($layout, '/\\');

        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     */
    public function setPlaceholder(string $placeholder)
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix(string $suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * @return bool
     */
    public function isMinify(): bool
    {
        return $this->minify;
    }

    /**
     * @param bool $minify
     */
    public function setMinify($minify)
    {
        $this->minify = (bool)$minify;
    }
}
