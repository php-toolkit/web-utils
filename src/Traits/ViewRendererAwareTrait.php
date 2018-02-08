<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-10-19
 * Time: 9:12
 */

namespace MyLib\Web\Traits;

use MyLib\Web\ViewRenderer;

/**
 * Trait ViewRendererAwareTrait
 * @package MyLib\Web\Traits
 */
trait ViewRendererAwareTrait
{
    /**
     * getRenderer
     * @return ViewRenderer
     */
    abstract public function getRenderer();

    /**
     * @param string $view
     * @return string
     */
    protected function resolveView(string $view)
    {
        return $view;
    }

    /*********************************************************************************
     * view method
     *********************************************************************************/

    /**
     * @param string $view
     * @param array $data
     * @param null|string $layout
     * @return string
     * @throws \Throwable
     */
    public function render(string $view, array $data = [], $layout = null)
    {
        return $this->getRenderer()->render($this->resolveView($view), $data, $layout);
    }

    /**
     * @param string $view
     * @param array $data
     * @return string
     * @throws \Throwable
     */
    public function renderPartial($view, array $data = [])
    {
        return $this->getRenderer()->fetch($this->resolveView($view), $data);
    }

    /**
     * @param string $string
     * @param array $data
     * @param null|string $layout
     * @return string
     * @throws \Throwable
     */
    public function renderContent($string, array $data = [], $layout = null)
    {
        return $this->getRenderer()->renderContent($string, $data, $layout);
    }

}
