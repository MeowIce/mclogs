<?php

class Id
{

    /**
     * @var string|null Full id generated by get() if null
     */
    private ?string $fullId = null;

    /**
     * @var string Id of the storage, one character long
     */
    private string $storageId;

    /**
     * @var string Id without storage part, used by storage class
     */
    private string $rawId;


    /**
     * Id constructor.
     *
     * If id is known, pass it here, if you want
     * to generate a new id, pass nothing
     *
     * @param string|null $fullId
     */
    public function __construct(string $fullId = null)
    {
        if ($fullId === null) {
            $this->regenerate();
        } else {
            $this->fullId = $fullId;
            $this->decode();
        }
    }

    /**
     * Regenerates the rawId to generate a new id
     *
     * @return string
     */
    public function regenerate(): string
    {
        $config = Config::Get("id");

        $rawId = "";
        for ($i = 0; $i < $config['length']; $i++) {
            $rawId .= $config['characters'][rand(0, strlen($config['characters']) - 1)];
        }

        $this->rawId = $rawId;
        $this->fullId = null;
        return $rawId;
    }

    /**
     * Set the storage id
     *
     * @param string $storageId
     * @return bool
     */
    public function setStorage(string $storageId): bool
    {
        $this->storageId = $storageId;
        return true;
    }

    /**
     * Get the storage id
     *
     * @return string
     */
    public function getStorage(): string
    {
        return $this->storageId;
    }

    /**
     * Get the raw id, used by storage
     *
     * @return string
     */
    public function getRaw(): string
    {
        return $this->rawId;
    }

    /**
     * Get the full id, will be generated from rawId and storageId if necessary
     *
     * @return string
     * @throws Exception
     */
    public function get(): string
    {
        $config = Config::Get("id");
        $chars = str_split($config['characters']);

        if ($this->fullId === null) {
            if ($this->rawId === null || $this->storageId === null) {
                throw new Exception("Raw and storage id cannot be empty to generate full id.");
            }

            $index = array_search($this->storageId, $chars);
            foreach (str_split($this->rawId) as $rawIdPart) {
                $index += array_search($rawIdPart, $chars);
            }

            $encodedStorageId = $chars[$index % count($chars)];
            $this->fullId = $encodedStorageId . $this->rawId;
        }

        return $this->fullId;
    }

    /**
     * Decode a full id to rawId and storageId
     *
     * @return bool
     */
    private function decode(): bool
    {
        $config = Config::Get("id");
        $chars = str_split($config['characters']);

        $this->rawId = substr($this->fullId, 1);
        $encodedStorageId = substr($this->fullId, 0, 1);

        $index = array_search($encodedStorageId, $chars) + strlen($this->rawId) * count($chars);
        foreach (str_split($this->rawId) as $rawIdPart) {
            $index -= array_search($rawIdPart, $chars);
        }

        $this->storageId = $chars[$index % count($chars)];

        return true;
    }

}