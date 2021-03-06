<?php

namespace Kanboard\Core\Plugin;

use Composer\Autoload\ClassLoader;
use DirectoryIterator;
use LogicException;
use Kanboard\Core\Tool;

/**
 * Plugin Loader
 *
 * @package Kanboard\Core\Plugin
 * @author  Frederic Guillot
 */
class Loader extends \Kanboard\Core\Base
{
    /**
     * Plugin instances
     *
     * @access protected
     * @var array
     */
    protected $plugins = array();

    /**
     * Get list of loaded plugins
     *
     * @access public
     * @return Base[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Scan plugin folder and load plugins
     *
     * @access public
     */
    public function scan()
    {
        if (file_exists(PLUGINS_DIR)) {
            $loader = new ClassLoader();
            $loader->addPsr4('Kanboard\Plugin\\', PLUGINS_DIR);
            $loader->register();

            $dir = new DirectoryIterator(PLUGINS_DIR);

            foreach ($dir as $fileInfo) {
                if ($fileInfo->isDir() && substr($fileInfo->getFilename(), 0, 1) !== '.') {
                    $pluginName = $fileInfo->getFilename();
                    $this->loadSchema($pluginName);
                    $this->initializePlugin($pluginName, $this->loadPlugin($pluginName));
                }
            }
        }
    }

    /**
     * Load plugin schema
     *
     * @access public
     * @param  string $pluginName
     */
    public function loadSchema($pluginName)
    {
        if (SchemaHandler::hasSchema($pluginName)) {
            $schemaHandler = new SchemaHandler($this->container);
            $schemaHandler->loadSchema($pluginName);
        }
    }

    /**
     * Load plugin
     *
     * @access public
     * @throws LogicException
     * @param  string $pluginName
     * @return Base
     */
    public function loadPlugin($pluginName)
    {
        $className = '\Kanboard\Plugin\\'.$pluginName.'\\Plugin';

        if (! class_exists($className)) {
            throw new LogicException('Unable to load this plugin class '.$className);
        }

        return new $className($this->container);
    }

    /**
     * Initialize plugin
     *
     * @access public
     * @param  string $pluginName
     * @param  Base   $plugin
     */
    public function initializePlugin($pluginName, Base $plugin)
    {
        if (method_exists($plugin, 'onStartup')) {
            $this->dispatcher->addListener('app.bootstrap', array($plugin, 'onStartup'));
        }

        Tool::buildDIC($this->container, $plugin->getClasses());
        Tool::buildDICHelpers($this->container, $plugin->getHelpers());

        $plugin->initialize();
        $this->plugins[$pluginName] = $plugin;
    }
}
