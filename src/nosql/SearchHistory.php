<?php
// petit historique des recherches (fichier json)

class SearchHistory
{
    private $file;

    public function __construct($filePath = null)
    {
        // par defaut: /data/search_history.json
        $this->file = $filePath ?: (__DIR__ . '/../../data/search_history.json');
    }

    // ajoute une recherche (on garde les dernieres)
    public function add($from, $to, $date = '', $filters = [])
    {
        $list = $this->getAll();

        $item = [
            'from' => $from,
            'to' => $to,
            'date' => $date,
            'filters' => $filters,
            'at' => date('Y-m-d H:i:s'),
        ];

        array_unshift($list, $item);

        // on limite un peu sinon ca grossit
        $list = array_slice($list, 0, 50);

        $this->save($list);
    }

    public function getAll()
    {
        if (!file_exists($this->file)) return [];

        $json = @file_get_contents($this->file);
        if ($json === false || trim($json) === '') return [];

        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    private function save($list)
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        @file_put_contents($this->file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
}

