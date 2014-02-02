<?php
namespace Orpheus;

class MCStatus {
    protected $status = array('online'     => false,
                              'server'     => 'null',
                              'hostname'   => 'null',
                              'numplayers' => 1,
                              'maxplayers' => 1);
    protected $socket = null, $challenge = null, $lastPacket = null;
    protected $compatibilityMode = false;

    /**
     *
     * Constructor.
     *
     * @param string $ip   The IP of the server.
     * @param int    $port The port of the server.
     *
     */
    public function __construct($ip = null, $port = 25565) {
        if($ip !== null) {
            $this->setServer($ip, $port);
        }
    }

    /**
     *
     * Set the IP and/or port of the server.
     *
     * @param string $ip   The IP of the server.
     * @param int    $port The port of the server.
     *
     */
    public function setServer($ip, $port = 25565) {
        if($port === null || !is_integer($port)) {
            $port = 25565;
        }

        $this->ip = $this->status['ip'] = $ip;
        $this->port = $this->status['port'] = (int) $port;
        if($this->port < 0 || $this->port > 65535) {
            throw new \Exception(__METHOD__ . ': Port range: 1-65535');
        }
    }

    /**
     *
     * Get the status of the server.
     *
     * @param bool $format Should we format the string?
     *
     * @return mixed
     *
     */
    public function getStatus($format = true) {
        $udp = $this->compatibilityMode === false ? 'udp://' : '';
        $start = microtime(true);
        $this->socket = @fsockopen($udp . $this->ip, $this->port, $errno, $errstr, 5);
        
        if(!$this->socket) {
            $this->status['online'] = false;
            return $this->status;
        }
        
        stream_set_timeout($this->socket, 5);

        if($this->compatibilityMode === false) {
            $challenge = $this->getChallenge();
            $end = microtime(true);
            $latency = floor(($end - $start) * 1000);
            try {
                $this->status = $this->write(0x00, $challenge . pack('c*', 0x00, 0x00, 0x00, 0x00));
            } catch(\Exception $e) {
            	$this->compatibilityMode = true;
            	return $this->getStatus($format);
            }
            $this->status = substr($this->status, 11);
            $this->status = explode("\x00\x00\x01player_\x00\x00", $this->status);
            $players = isset($this->status[1]) ? substr($this->status[1], 0, -2) : '';
            $players = $players == true ? explode("\x00", $players) : array();
            $this->status = explode("\x00", $this->status[0]);
            $array = array();

            foreach($this->status as $key => $s) {
                if($key % 2 == 0 && isset($this->status[$key + 1])) {
                    $array[$s] = $this->status[$key + 1];
                    switch($s) {
                        case 'hostname':
                            $array['hostname'] = $format === true ? $this->formatString($array['hostname']) : preg_replace('/(§(\d))/', '', $array['hostname']);
                            break;

                        case 'plugins':
                            $plugins = explode(': ', $array[$s]);
                            if(!isset($plugins[1])) {
                                $array['server'] = 'Vanilla';
                                break;
                            }
                            $serverVersion = $plugins[0];
                            $pluginList = explode('; ', $plugins[1]);
                            $array['server'] = $serverVersion;
                            $array['plugins'] = $pluginList;
                            break;
                    }
                }
            }

            $array['players'] = $players;
            $array['ip'] = $this->ip;
            $array['lat'] = $latency;
            $array['online'] = true;
            return $array;
        } else {
            fwrite($this->socket, "\xFE\x01");
            $end = microtime(true);
            $latency = floor(($end - $start) * 1000);

            $result = fread($this->socket, 2048);
            if(substr($result, 0, 1) != "\xff") {
                $this->status['online'] = false;
                return $this->status;
            } else {
                if(substr($result, 3, 5) == "\x00\xa7\x00\x31\x00"){
                    $result = mb_convert_encoding(substr($result, 15), 'UTF-8', 'UCS-2');
                }
                $result = explode("\x00", $result);

                $motd = $format == true ? $this->formatString($result[count($result) - 3]) : preg_replace('/(§(\d))/', '', $result[count($result) - 3]);
                $this->status = array(
                    'ip'         => $this->ip,
                    'port'       => $this->port,
                    'online'     => true,
                    'server'     => $result[0],
                    'hostname'   => $motd,
                    'numplayers' => (int) $result[count($result) - 2],
                    'maxplayers' => (int) $result[count($result) - 1],
                    'lat'        => $latency
                );
                return $this->status;
            }
        }
    }

    /**
     *
     * Colorizes the string.
     *
     * @param string $string The string to be formatted.
     *
     * @return string
     */
    protected function formatString($string) {
        if(!$this->detectUTF8($string)) {
            $string = mb_convert_encoding($string, 'UTF-8');
        } 
        preg_match_all('/(§([\d\w]))/', $string, $formats);
        $replacements = json_decode(file_get_contents(__DIR__ . '/formats.json'), true);
            
        $tags = 0;
        foreach($formats[1] as $key => $format) {
            $string = preg_replace('/' . $format . '/', '<span style="' . $replacements[$formats[2][$key]] . '">', $string);
            $tags++;
        }

        for($i = 0; $i < $tags; $i++) {
            $string .= '</span>';
        }

        return $string;
    }

    /**
     *
     * Get the challenge - For servers with "enable-query" set to true.
     *
     */
    protected function getChallenge() {
    	try {
        	$in = $this->write(0x09);
        	$this->challenge = pack('N', $in);
        	return $this->challenge;
        } catch(\Exception $e) {
        	$this->compatibilityMode = true;
        	return null;
        }
    }

    /**
     *
     * Write to the socket - for servers with "enable-query" set to true
     *
     * @return The response
     *
     */
    protected function write($packet, $data = '') {
        if(!$this->socket) {
            return false;
        }

        $packet = pack('c*', 0xFE, 0xFD, $packet, 0x01, 0x02, 0x03, 0x04) . $data;
        $this->lastPacket = $packet;
        $bufLen = strlen($packet);
        $fw = fwrite($this->socket, $packet, $bufLen);

        if($fw !== $bufLen) {
            throw new \Exception('Failure to send packet');
        }

        $data = fread($this->socket, 2048);
        if(!$data || strlen($data) < 5 || $data[0] != $this->lastPacket[2]) {
            throw new \Exception('Failure to read packet');
        }

        return substr($data, 5);
    }

    protected function detectUTF8($string) { // http://ca2.php.net/manual/en/function.mb-detect-encoding.php#68607 thanks guy!
            return preg_match('%(?:
            [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
            |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
            |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
            |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
            |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
            |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
            |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
            )+%xs', $string);
    }
}
?>
