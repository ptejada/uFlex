<?php

namespace ptejada\uFlex;

/**
 * Class handles a single cookie
 * Reads and writes the value of a cookie
 *
 * @package ptejada\uFlex
 * @author  Pablo Tejada <pablo@ptejada.com>
 */
class Cookie
{
    /** @var  Log - Log errors and report */
    public $log;

    /** @var  string The name of the cookie */
    private $name;
    /** @var  string The content of the cookie */
    private $value;
    /** @var  int The lifetime in days of the cookie */
    private $lifetime;
    /** @var  string The path of the cookie */
    private $path;
    /** @var  string The host for which the host belongs to */
    private $host;

    /**
     * Initializes a cookie
     *
     * @param string $name     The name of the cookie
     * @param string $value    _(optional)_ The content of the cookie
     * @param int    $lifetime _(optional)_ The lifetime in days of the cookie
     * @param string $path     _(optional)_ The URL path of the cookie
     * @param null   $host     _(optional)_ The host for which the host belongs to
     */
    public function __construct($name, $value = '', $lifetime = 15, $path = '/', $host = null)
    {
        $this->name = $name;

        //Defaults
        $this->value = $value;
        $this->setLifetime($lifetime);
        $this->setPath($path);
        if (!$host) {
            if (isset($_SERVER['SERVER_NAME'])) {
                $this->setHost($_SERVER['SERVER_NAME']);
            }
        } else {
            $this->setHost($host);
        }
    }

    /**
     * Set the lifetime of the cookie
     *
     * @param int $lifetime - The number of days to last
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
    }

    /**
     * Set the path of the cookie relative to the site domain
     *
     * @param string $path - The path of the cookie
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Set the host to add the cookie for
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Sends the cookie to the browser
     *
     * @return bool
     */
    public function add()
    {
        if (!headers_sent()) {
            // Set the cookie via PHP headers
            $added = setcookie(
                $this->name,
                $this->value,
                round(time() + 60 * 60 * 24 * $this->lifetime),
                $this->path,
                $this->host
            );
        } else {
            //Headers have been sent use JavaScript to set the cookie
            echo "<script>";
            echo '
              function setCookie(c_name,value,expiredays){
                var exdate=new Date();
                exdate.setDate(exdate.getDate()+expiredays);
                document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : "; expires="+exdate.toUTCString()) + "; domain="+ escape("' . $this->host . '") + "; path=" + escape("' . $this->path . '");
              }
            ';
            echo "setCookie('{$this->name}','{$this->value}',{$this->lifetime})";
            echo "</script>";
            $added = true;
        }

        return $added;
    }

    /**
     * Destroys the cookie
     *
     * @return bool
     */
    public function destroy()
    {
        if (!is_null($this->getValue())) {
            if (!headers_sent()) {
                return setcookie(
                    $this->name,
                    '',
                    time() - 3600,
                    $this->path,
                    $this->host
                ); //Deletes Cookie
            } else {
                return false;
            }
        } else {
            // The cookie does not exists, there is nothing to destroy
            return true;
        }
    }

    /**
     * Get the value of the cookie
     *
     * @return null|mixed - Returns null if the cookie does not exists
     */
    public function getValue()
    {
        if (isset($_COOKIE[$this->name])) {
            return $_COOKIE[$this->name];
        } else {
            return null;
        }
    }

    /**
     * Sets the value for
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
