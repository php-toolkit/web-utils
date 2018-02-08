<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018-01-05
 * Time: 14:32
 */

namespace MyLib\Web\Util;

use Swagger\Annotations\Info;
use Swagger\Annotations\Swagger;

/**
 * Class SwaggerJSON
 * @package MyLib\Web\Util
 */
class SwaggerJSON
{
    /** @var string Env name. like prod,test,dev */
    private $env;

    /** @var string */
    private $fileTpl = 'swagger-%s.json';

    /** @var array */
    private $settings = [
        // basic
        'host' => 'localhost',
        'basePath' => '/',
        'schemes' => ['http', 'https'],
        'consumes' => ['application/json'],
        'produces' => ['application/json'],

        // info
        'version' => '1.0.0',
        'title' => 'some text',
        'description' => '## MY App(`env: {@env}`)
 some message',
    ];

    /** @var array */
    private $envSettings = [
        'dev' => [
            'host' => 'api.dev',
            'basePath' => '/',
        ],
        'test' => [
            'host' => '172.19.49.177:8203',
            'basePath' => '/',
        ],
    ];

    /**
     * @var array Want to scan's dirs
     */
    private $scanDirs = [];

    /**
     * @param array $settings
     * @return SwaggerJSON
     */
    public static function make(array $settings = []): self
    {
        return new self($settings);
    }

    /**
     * SwaggerJSON constructor.
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        if (!class_exists(Swagger::class)) {
            throw new \RuntimeException("please install 'zircote/swagger-php' package by composer.");
        }

        if ($settings) {
            $this->setSettings($settings);
        }
    }

    /**
     * @param string|null $outputDir
     * @param bool $refresh
     * @return string
     */
    public function generate(string $outputDir = null, $refresh = false): string
    {
        if (!$this->env) {
            $this->env = APP_ENV;
        }

        $env = $this->env;
        $path = $outputDir ?: BASE_PATH;
        $name = sprintf($this->fileTpl, $env);
        $file = $path . '/' . $name;

        if (!$refresh && is_file($file)) {
            return $file;
        }

        /** @var Swagger $swg */
        $swg = \Swagger\scan($this->scanDirs);

        if (isset($this->envSettings[$env])) {
            $this->settings = array_merge($this->settings, $this->envSettings[$env]);
        }

        // basic
        $swg->host = $this->settings['host'];
        $swg->basePath = $this->settings['basePath'];
        $swg->schemes = $this->settings['schemes'];
        $swg->consumes = $this->settings['consumes'];
        $swg->produces = $this->settings['produces'];

        // info
        $info = new Info([]);
        $info->title = $this->settings['title'];
        $info->version = $this->settings['version'];
        $info->description = str_replace('{@env}', $env, $this->settings['description']);

        $swg->info = $info;

        file_put_contents($file, (string)$swg);

        return $file;
    }


    /**
     * @param string $env
     * @param array $info
     */
    public function setEnvSetting(string $env, array $info)
    {
        $this->envSettings[$env] = array_merge([
            'host' => 'localhost',
            'basePath' => '/',
        ], $info);
    }

    /**
     * @return array
     */
    public function getEnvSettings(): array
    {
        return $this->envSettings;
    }

    /**
     * @param array $envSettings
     */
    public function setEnvSettings(array $envSettings)
    {
        $this->envSettings = $envSettings;
    }

    /**
     * @return string
     */
    public function getEnv(): string
    {
        return $this->env;
    }

    /**
     * @param string $env
     */
    public function setEnv(string $env)
    {
        $this->env = $env;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        return $this->settings[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value)
    {
        $this->settings[$name] = $value;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings(array $settings)
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * @return array
     */
    public function getScanDirs(): array
    {
        return $this->scanDirs;
    }

    /**
     * @param array $scanDirs
     */
    public function setScanDirs(array $scanDirs)
    {
        $this->scanDirs = $scanDirs;
    }

    /**
     * @return string
     */
    public function getFileTpl(): string
    {
        return $this->fileTpl;
    }

    /**
     * @param string $fileTpl
     */
    public function setFileTpl(string $fileTpl)
    {
        $this->fileTpl = $fileTpl;
    }
}
