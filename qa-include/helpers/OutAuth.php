<?php

class OutAuth {

    private $_qa_connect = null;

    private $_id = false;
    private $_hash = false;

    private $_user_id;
    private $_refer = '/';

    /**
     * MSAuth constructor.
     * @param integer $id
     * @param string $hash
     */
    function __construct($id, $hash)
    {
        $this->_id = $id;
        $this->_hash = $hash;
        $this->_qa_connect = new mysqli(QA_MYSQL_HOSTNAME, QA_MYSQL_USERNAME, QA_MYSQL_PASSWORD, QA_MYSQL_DATABASE);
    }


    /**
     * @throws Exception
     */
    public function auth()
    {
        if ($this->_getSession()) {
            $this->_login();
        }

        header('Location:'.$this->_refer);
        exit;
    }


    /**
     * @return bool
     */
    private function _getSession()
    {
        if (!defined('OUT_URL_SESSION') or !defined('OUT_URL_KEY')) {
            return false;
        }

        $data = [
            'id' => $this->_id,
            'key' => sha1(OUT_URL_KEY),
            'hash' => $this->_hash,
            'ip' => sha1($_SERVER['REMOTE_ADDR']),
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, OUT_URL_SESSION);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $res = false;
        if ($result = curl_exec($curl)) {
            if ($arr = @json_decode($result, true) and isset($arr['user_id'])) {
                $this->_user_id = intval($arr['user_id']);
                $this->_refer = $arr['refer'];
                $res = true;
            } else {
                $file = QA_BASE_DIR.'/log/OutAuth_'.microtime(false).'.txt';
                file_put_contents($file, $this->_id.' : '.$this->_hash.' : '.$result);
            }
        }

        curl_close($curl);

        return $res;
    }


    private function _login()
    {
        try {
            $result = $this->_qa_connect->query("SELECT `userid`, `handle` FROM `qa_users` WHERE `out_user_id` = {$this->_user_id}", MYSQLI_USE_RESULT);
            $userObj = $result->fetch_object();
            mysqli_free_result($result);
        } catch (Exception $e) {
            return false;
        }

        if (!is_object($userObj) or !isset($userObj->handle) or !isset($userObj->userid)) {
            setcookie('out_user', dechex($this->_user_id), time()+3600);
            header('Location: /?r-auth=out_user');
            exit;
        }

        qa_set_logged_in_user($userObj->userid, $userObj->handle, true);

        return true;
    }

}