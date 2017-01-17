<?
/**
* RC4 stream cipher routines implementation
*
* in PHP4 based on code written by Damien Miller <djm@mindrot.org>
*
* Usage:
* $key = "pear";
* $message = "PEAR rulez!";
*
* $rc4 = new Crypt_RC4;
* $rc4->key($key);
* echo "Original message: $message <br>\n";
* $rc4->crypt($message);
* echo "Encrypted message: $message <br>\n";
* $rc4->decrypt($message);
* echo "Decrypted message: $message <br>\n";
*
* @version $Revision: 1.6 $
* @access public
* @package Crypt
* @author Dave Mertens <dmertens@zyprexia.com>
 */
class Model_Payment_Extension_Ebscrypt {

    /**
    * Real programmers...
    * @var array
    */
    var $s= array();
    /**
    * Real programmers...
    * @var array
    */
    var $i= 0;
    /**
    * Real programmers...
    * @var array
    */
    var $j= 0;

    /**
    * Key holder
    * @var string
    */
    var $_key;

    /**
    * Constructor
    * Pass encryption key to key()
    *
    * @see    key() 
    * @param  string key    - Key which will be used for encryption
    * @return void
    * @access public
    */
    function Model_Payment_Extension_Ebscrypt($key = null) {
        if ($key != null) {
            $this->setKey($key);
        }
    }

    function setKey($key) {
        if (strlen($key) > 0)
            $this->_key = $key;
    }

    /**
    * Assign encryption key to class
    *
    * @param  string key	- Key which will be used for encryption
    * @return void
    * @access public    
    */
    function key(&$key) {
        $len= strlen($key);
        for ($this->i = 0; $this->i < 256; $this->i++) {
            $this->s[$this->i] = $this->i;
        }

        $this->j = 0;
        for ($this->i = 0; $this->i < 256; $this->i++) {
            $this->j = ($this->j + $this->s[$this->i] + ord($key[$this->i % $len])) % 256;
            $t = $this->s[$this->i];
            $this->s[$this->i] = $this->s[$this->j];
            $this->s[$this->j] = $t;
        }
        $this->i = $this->j = 0;
    }

    /**
    * Encrypt function
    *
    * @param  string paramstr 	- string that will encrypted
    * @return void
    * @access public    
    */
    function crypt(&$paramstr) {

        //Init key for every call, Bugfix 22316
        $this->key($this->_key);

        $len= strlen($paramstr);
        for ($c= 0; $c < $len; $c++) {
            $this->i = ($this->i + 1) % 256;
            $this->j = ($this->j + $this->s[$this->i]) % 256;
            $t = $this->s[$this->i];
            $this->s[$this->i] = $this->s[$this->j];
            $this->s[$this->j] = $t;

            $t = ($this->s[$this->i] + $this->s[$this->j]) % 256;

            $paramstr[$c] = chr(ord($paramstr[$c]) ^ $this->s[$t]);
        }
    }

    /**
    * Decrypt function
    *
    * @param  string paramstr 	- string that will decrypted
    * @return void
    * @access public    
    */
    function decrypt(&$paramstr) {
        //Decrypt is exactly the same as encrypting the string. Reuse (en)crypt code
        $this->crypt($paramstr);
    }


}	//end of RC4 class
?>