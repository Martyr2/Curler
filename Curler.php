<?php

class Curler {
    private $ch;
    private $defaults = [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POST => 0,
        CURLOPT_FOLLOWLOCATION => 1
    ];

    public function __construct(array $options = []) {
        $this->ch = curl_init();

        $this->setOptions($options);
    }

    public function __destruct() {
        curl_close($this->ch);
    }


    public function get(string $url) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, 0);

        return $this->execute();
    }

    public function post(string $url, array $fields = []) {
        $options = [URLOPT_URL => $url,
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => http_build_query($fields)];

        $this->setOptions($options);

        return $this->execute();
    }

    public function put(string $url, array $fields = []) {
        $options = [CURLOPT_URL => $url,
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                    CURLOPT_HTTPHEADER => ['Content-Length: ' . strlen(http_build_query($fields))],
                    CURLOPT_POSTFIELDS => http_build_query($fields)];

        $this->setOptions($options);

        return $this->execute();
    }

    public function delete(string $url) {
        $options = [CURLOPT_URL => $url,
                    CURLOPT_POST => 0,
                    CURLOPT_CUSTOMREQUEST => 'DELETE',
                    CURLOPT_HTTPHEADER => ['Content-Length: 0']];

        $this->setOptions($options);

        return $this->execute();
    }

    public function setOptions(array $options = []) {
        curl_reset($this->ch);

        $result = curl_setopt_array($this->ch, $this->defaults + $options);

        if ($result === false) {
            curl_reset($this->ch);
            curl_setopt_array($this->ch, $this->defaults);
            throw new InvalidArgumentException('Invalid arguments detected.');
        }
    }

    private function execute() {
        if (!$result = curl_exec($this->ch)) {
            throw new LogicException(curl_error($this->ch));
        }

        $curlInfo = curl_getinfo($this->ch);

        if ($curlInfo === false) {
            throw new Exception('There was a problem getting cURL session info.');
        }

        $stdClass = new stdClass();
        $stdClass->code = (isset($curlInfo['http_code'])) ? $curlInfo['http_code'] : 500;
        $stdClass->response = $result;

        return $stdClass;
    }
}
