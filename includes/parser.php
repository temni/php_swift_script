<?php
class ArgvParserException extends Exception {}

/**
 * Class for parse command line arguments
 * Singleton
 * Used function for parser command line args form php.net
 * @author artur geraschenko
 */
class ArgvParser
{
    const ENCODING = 'UTF-8';
   
    /**
     * @var ArgvParser
     */
    private static $instance = null;

    /**
     * Saved parsed command line arguments
     * @var unknown_type
     */
    private $arguments = array();
   
    /**
     * @return ArgvParser
     */
    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Get executed file name
     */
    public function getExecuteFile()
    {
        return (string)$this->arguments['exec'];
    }
   
    /**
     * Get option from commad line arguments get key
     * @param string $key
     */
    public function getOption($key)
    {
        $key = (string)$key;
        $this->checkExistOption($key);
        return $this->arguments['options'][$key];
    }
   
    /**
     * Throw exception if option isn't exist
     * @param unknown_type $key
     */
    private function checkExistOption($key)
    {
        if (!$this->isExistOption($key))
            throw new ArgvParserException('Option not found');
    }
   
    /**
     * Get bool value is exist option
     * @param bool $key
     */
    public function isExistOption($key)
    {
        return isset($this->arguments['options'][$key]);
    }

    /**
     * Get bool value is exist flag
     * @param bool $key
     */
    public function isExistFlag($key)
    {
        return isset($this->arguments['flags'][$key]);
    }

    /**
     * Get all arguments from command line
     */
    public function getArguments()
    {
        return (array)$this->arguments['arguments'];
    }
   
    private function __clone(){}
    private function __construct()
    {
        $this->arguments = $this->parseEnvironmentArguments();
    }
   
    /**
     * Try to parser environment command line arguments
     */
    private function parseEnvironmentArguments()
    {
        $this->checkEnvironmentArguments();
        return $this->parseArguments($_SERVER['argv']);
    }
   
    /**
     * Throw exception if arguments not found is environment ($_SERVER)
     */
    private function checkEnvironmentArguments()
    {
        if (!isset($_SERVER['argc']) || !isset($_SERVER['argv']) || (int)$_SERVER['argc'] !== (int)sizeof($_SERVER['argv']))
            throw new ArgvParserException("Environment arguments not found");
    }
   
    /**
     * Get from php.net and modifed to general format
     * @url http://www.php.net/manual/en/features.commandline.php#86616
     * @param array $args
     * @return array
     */
    private function parseArguments(array $args)
    {   
        $ret = array(
            'exec'      => '',
            'options'   => array(),
            'flags'     => array(),
            'arguments' => array(),
        );
        $ret['exec'] = array_shift($args);
        while (($arg = array_shift($args)) != NULL)
        {
            if (mb_substr($arg, 0, 2, self::ENCODING) === '--')
            {
                $option = mb_substr($arg, 2, mb_strlen($arg) - 2, self::ENCODING);
                $opt = array();
                if (mb_strpos($option, '=', 0, self::ENCODING) !== FALSE)
                    list($opt['key'], $opt['value']) = explode('=', $option, 2);
                else
                    $opt = array('key' => $option, 'value' => null);
                $ret['options'][$opt['key']] = $opt['value'];
                continue;
            }
           
            if (mb_substr($arg, 0, 1, self::ENCODING) === '-')
            {
                for ($i = 1; isset($arg[$i]) ; $i++)
                    $ret['flags'][$arg[$i]] = true;
                continue;
            }
            $ret['arguments'][] = $arg;
        }
        return $ret;
    }
}
