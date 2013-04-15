<?php
namespace Orpheus;

class MCStatus {
    protected $status = array('online'     => false,
                              'version'    => null,
                              'motd'       => null,
                              'players'    => -1,
                              'maxPlayers' => -1);

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
        if($port === null) {
            $port = 25565;
        }

        $this->ip = $ip;
        $this->port = (int) $port;
    }

    /**
     *
     * Get the status of the server.
     *
     * @param bool $colorize Should we format the string?
     *
     * @return mixed
     *
     */
    public function getStatus($colorize = true) {
        $f = fsockopen($this->ip, $this->port, $errno, $errstr, 5);
        if($f === false) {
            return false;
        }

        fwrite($f, "\xFE\x01");
        $result = fread($f, 256);

        if(substr($result, 0, 1) != "\xff") {
            return false;
        } else {
            if(substr($result, 3, 5) == "\x00\xa7\x00\x31\x00"){
                $result = mb_convert_encoding(substr($result, 15), 'UTF-8', 'UCS-2');
                $result = explode("\x00", $result);
            }

            $motd = $colorize == true ? $this->formatString($result[count($result) - 3]) : preg_replace('/(§(\d))/', '', $result[count($result) - 3]);
            $this->status = array(
                'online'     => true,
                'version'    => $result[0],
                'motd'       => $motd,
                'players'    => (int) $result[count($result) - 2],
                'maxPlayers' => (int) $result[count($result) - 1]
            );
            return $this->status;
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
        preg_match_all('/(§([\d\w]))/', $string, $formats);

        $replacements = array(
            0 => '<span style="text-shadow:1px 1px 0px #000000; color: #000000;">',
            1 => '<span style="text-shadow:1px 1px 0px #00002A; color: #0000AA;">',
            2 => '<span style="text-shadow:1px 1px 0px #002A00; color: #00AA00;">',
            3 => '<span style="text-shadow:1px 1px 0px #002A2A; color: #00AAAA;">',
            4 => '<span style="text-shadow:1px 1px 0px #2A0000; color: #AA0000;">',
            5 => '<span style="text-shadow:1px 1px 0px #2A002A; color: #AA00AA;">',
            6 => '<span style="text-shadow:1px 1px 0px #2A2A00; color: #FFAA00;">',
            7 => '<span style="text-shadow:1px 1px 0px #2A2A2A; color: #AAAAAA;">',
            8 => '<span style="text-shadow:1px 1px 0px #151515; color: #555555;">',
            9 => '<span style="text-shadow:1px 1px 0px #15153F; color: #5555FF;">',
            'a' => '<span style="text-shadow:1px 1px 0px #153F15; color: #55FF55;">',
            'b' => '<span style="text-shadow:1px 1px 0px #153F3F; color: #55FFFF;">',
            'c' => '<span style="text-shadow:1px 1px 0px #3F1515; color: #FF5555;">',
            'd' => '<span style="text-shadow:1px 1px 0px #3F153F; color: #FF55FF;">',
            'e' => '<span style="text-shadow:1px 1px 0px #3F3F15; color: #FFFF55;">',
            'f' => '<span style="text-shadow:1px 1px 0px #3F3F3F; color: #FFFFFF;">'
        );

        $tags = 0;
        foreach($formats[1] as $key => $format) {
            $string = preg_replace('/' . $format . '/', $replacements[$key], $string);
            $tags++;
        }

        for($i = 0; $i < $tags; $i++) {
            $string .= '</span>';
        }

        return $string;
    }
}
?>
