<?php

namespace ptejada\uFlex;

/**
 * Class handles a single cookie
 * Reads and writes the value of a cookie
 *
 * @package ptejada\uFlex
 */
class Cookie
{
    /** @var  Log - Log errors and report */
    public $log;

    private $name, $value, $lifetime, $path, $host;

    public function __construct($name, $value = '', $lifetime = 15, $path ='/', $host=null)
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
        }
        else
        {
            $this->setHost($host);
        }
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
     * Get the value of the cookie
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
     * Sets the value for
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Destroys the cookie
     * @return bool
     */
    public function destroy(){
        if (!is_null($this->getValue())) {
            if (!headers_sent()) {
                return setcookie(
                    $this->name,
                    '',
                    time() - 3600,
                    $this->path,
                    $this->host
                ); //Deletes Cookie
            }
            else
            {
                return false;
            }
        } else {
            // The cookie does not exists, there is nothing to destroy
            return true;
        }
    }
}
