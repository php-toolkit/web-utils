<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/6/1 0001
 * Time: 21:19
 */

namespace Toolkit\Web\Util;

/**
 * Class Flash
 * @package Toolkit\Web\Util
 * @method normal(string $key, string $message, array $opts = [])
 * @method info(string $key, string $message, array $opts = [])
 * @method error(string $key, string $message, array $opts = [])
 * @method danger(string $key, string $message, array $opts = [])
 * @method success(string $key, string $message, array $opts = [])
 * @method warning(string $key, string $message, array $opts = [])
 * @method dark(string $key, string $message, array $opts = [])
 */
class Flash
{
    // use BootstrapStyleMessageTrait;

    // alert style
    const LIGHT = 'light';
    const DARK = 'dark';
    const INFO = 'info';
    const SUCCESS = 'success';
    const PRIMARY = 'primary';
    const WARN = 'warning';
    const WARNING = 'warning';
    const ERROR = 'danger';
    const DANGER = 'danger';
    const SECONDARY = 'secondary';

    const SESS_DATA_KEY = '_user_flash_data';

    const FLASH_STYLES = [
        'normal' => 'secondary',
        'default' => 'secondary',
        'primary' => 'primary',
        'secondary' => 'secondary',
        'success' => 'success',
        'error' => 'danger',
        'danger' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
        'light' => 'light',
        'dark' => 'dark'
    ];

    /**
     * @var string
     */
    private $storageKey = self::SESS_DATA_KEY;

    /**
     * Messages from previous request
     * @var string[]
     */
    protected $previous = [];

    /**
     * Message storage
     * @var null|array|\ArrayAccess
     */
    protected $storage;

    /**
     * Flash constructor.
     * @param null|array|\ArrayAccess $storage
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct(&$storage = null)
    {
        // Set storage
        if (\is_array($storage) || $storage instanceof \ArrayAccess) {
            $this->storage = &$storage;
        } elseif (null === $storage) {
            if ($_SESSION === null) {
                throw new \RuntimeException('Flash messages middleware failed. Session not found.');
            }

            $this->storage = &$_SESSION;
        } else {
            throw new \InvalidArgumentException('Flash messages storage must be an array or implement \ArrayAccess');
        }

        // load previous request messages
        if ($previous = $this->storage[$this->storageKey] ?? null) {
            $this->previous = $previous;
        }

        // clear old, init for current request
        $this->storage[$this->storageKey] = [];
    }

    /**
     * flash message
     * @param string $key
     * @param string $message
     * @param string $style
     * @param array $opts
     * [
     *  'title' => 'custom title',
     *  'closeable' => 1
     * ]
     */
    public function addMessage(string $key, string $message, string $style = 'info', array $opts = [])
    {
        if (!$key || !$message) {
            return;
        }

        $body = \array_merge([
            'closeable' => 1,
        ], $opts, [
            'type' => $style,
            'msg' => $message,
        ]);

        if (!isset($body['title'])) {
            $body['title'] = \ucfirst($style);
        }

        // add message
        $this->storage[$this->storageKey][$key] = \json_encode($body);
    }

    /**
     * flash old request data.
     * @param string $key
     * @param array $data
     */
    public function save(string $key, array $data)
    {
        $this->storage[$this->storageKey][$key] = \json_encode($data);
    }

    /**
     * @param string $key
     * @return null|array
     */
    public function get(string $key)
    {
        if ($json = $this->previous[$key] ?? null) {
            return \json_decode($json, true);
        }

        return null;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getMessage(string $key): string
    {
        if ($body = $this->get($key)) {
            return $body['msg'] ?? '';
        }

        return '';
    }

    /**
     * @param string $name style name
     * @param array $args
     * @return self
     * @throws \InvalidArgumentException
     */
    public function __call($name, array $args)
    {
        if ($style = self::FLASH_STYLES[$name] ?? null) {
            if (!isset($args[1])) {
                throw new \InvalidArgumentException('missing enough parameters');
            }

            $this->addMessage($args[0], $args[1], $style, isset($args[2]) ? (array)$args[2] : []);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->storage[$this->storageKey];
    }

    /**
     * @return string
     */
    public function getStorageKey(): string
    {
        return $this->storageKey;
    }

    /**
     * @param string $storageKey
     */
    public function setStorageKey(string $storageKey)
    {
        $this->storageKey = $storageKey;
    }
}
