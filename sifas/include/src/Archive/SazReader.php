<?php
namespace EverISay\SIF\V1\AS\Archive;

use sekjun9878\RequestParser\RequestParser;

/**
 * Reads Fiddler session archive (*.saz) files.
 */
class SazReader {
    /**
     * @param string[] $filterPaths Array of interested API paths, like 'notice/fetchNotice'.
     */
    function __construct(
        string $fileName,
        private readonly array $filterPaths,
    ) {
        $this->zip = new \ZipArchive;
        $err = $this->zip->open($fileName, \ZipArchive::RDONLY);
        true === $err or throw new \Exception("Error when opening archive (code $err)");
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $entryName = $this->zip->getNameIndex($i);
            if (!preg_match('/^raw\/\d+_c.txt$/', $entryName)) continue;
            $requestRaw = $this->zip->getFromIndex($i);
            if (substr($requestRaw, 0, 8) == 'CONNECT ') continue;
            $parser = new RequestParser;
            $parser->addData($requestRaw);
            $request = $parser->exportRequestState();
            $path = $request['path'];
            $path = substr($path, strpos($path, '/', 1) + 1);
            if (!in_array($path, $filterPaths)) continue;
            $this->entryNames[$path][] = $entryName;
        }
    }

    private \ZipArchive $zip;
    /** @var string[] Request entry names indexed by API path. */
    private array $entryNames;

    public function get(string $path) {
        in_array($path, $this->filterPaths) or throw new \Exception("Path '$path' is not included in \$filterPaths of the constructor.");
        foreach ($this->entryNames[$path] ?? [] as $requestName) {
            $parser = new RequestParser;
            $parser->addData($this->zip->getFromName($requestName));
            $request = $parser->exportRequestState();
            $responseRaw = $this->zip->getFromName(str_replace('_c.txt', '_s.txt', $requestName));
            $responseBody = substr($responseRaw, strpos($responseRaw, "\r\n\r\n") + 4);
            yield new SazEntry($request['body'], $responseBody);
        }
    }
}
