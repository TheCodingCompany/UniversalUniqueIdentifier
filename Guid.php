<?php

final class Guid
{
    /**
     * Holds our Unique ID
     * @var type 
     */
    protected $uuid = null;

    /**
     * Holds our node identifier
     * @var type 
     */
    protected $node_id = "";

    /**
     * Holds our Singleton instance
     * @var type 
     */
    private static $instance = [];

    /**
     * Return binary representation of UUID
     * @return type
     */
    public final function toBytes()
    {
        return hex2bin(str_replace("-", "", $this->uuid));
    }

    /**
     * Convert binary UUID to human readable string
     * @param binary $bin_data The binary UUID
     * @return string
     */
    public final function toString($bin_data = "")
    {
        $temp = bin2hex($bin_data);

        //Rebuild
        $this->uuid = mb_substr($temp, 0, 8, "ASCII") . //Node ID
            "-" . mb_substr($temp, 8, 8, "ASCII") . //Time mid + high
            "-" . mb_substr($temp, 16, 4, "ASCII") . //Version
            "-" . mb_substr($temp, 20, 8, "ASCII") . //Clock sequence
            "-" . mb_substr($temp, 28, 8, "ASCII"); //Node unique key

        //echo $this->uuid . "\r\n";
        //return $this;
        return $this->uuid;
    }

    /**
     * Create the UUID for a Node
     * @param string $node_id The 2 character node identifier
     * @example $this->getUUID("A10")
     * @return float Unique decimal identifier
     */
    public final function getUUId($node_id = "")
    {
        $this->uuid = "";

        //Fix length
        if (mb_strlen($node_id) < 3) {
            $node_id = $node_id[0] . "0" . $node_id[1] . $node_id[2];
        } elseif (mb_strlen($node_id, "ASCII") > 3) {
            throw new \Exception("A NodeID is max 3 characters long. A10 or Z32");
        }

        //Cluster protocol node identifier
        $this->uuid = str_pad(ord($node_id[0]), 3, 0, STR_PAD_LEFT); //Letter A - Z
        $this->uuid .= str_pad(ord($node_id[1]), 3, 0, STR_PAD_LEFT); //Number 0-9
        $this->uuid .= str_pad(ord($node_id[2]), 3, 0, STR_PAD_LEFT); //Number 0-9        

        $this->uuid = $this->uuid();
        return $this->uuid;
    }

    /**
     * Get the node identified from a uuid
     * @param type $uuid
     * @return type
     */
    public final function getNodeId($uuid = "")
    {
        $this->node_id = "";

        if (empty($uuid)) {
            $uuid = $this->uuid;
        }

        //Only gab first part
        list($uuid) = explode("-", $uuid);

        //UnHex to decimal values add 0
        $this->uuid = (string) str_pad(hexdec($uuid), 9, 0, STR_PAD_LEFT);

        //3 byte chars
        for ($i = 0; $i < mb_strlen($this->uuid, "ASCII"); $i += 3) {
            $char = intval((int) $this->uuid[$i] . (int) $this->uuid[$i + 1] . (int) $this->uuid[$i + 2]);
            $this->node_id .= chr($char);
        }

        return trim($this->node_id);
    }

    /**
     * Create RFC4 compliant UUID
     * @return uuid
     */
    private final function uuid()
    {
        return sprintf('%08x-%04x%04x-%04x-%04x%04x-%04x%04x',
            // 24 bits cluster_id
            $this->uuid,
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 32 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get the instance (Singleton Pattern)
     * @return type
     */
    public static final function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instance[$class]) || empty(self::$instance[$class])) {
            self::$instance[$class] = new static();
        }

        return static::$instance[$class];
    }

    /**
     * Disable construct new Uuid instance
     */
    private function __construct()
    {
        
    }

    private function __clone()
    {
        
    }

    private function __wakeup()
    {
        
    }
}
