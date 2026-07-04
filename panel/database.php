<?php
/**
 * C2-Empyrean - JSON DB Engine
 */

class JsonDatabase
{
    private string $dataDir;
    private array $cache = [];

    public function __construct(?string $dataDir = null)
    {
        $this->dataDir = $dataDir ?: DATA_DIR;
        if (!is_dir($this->dataDir)) mkdir($this->dataDir, 0755, true);
    }

    private function getPath(string $collection): string
    {
        $collection = preg_replace('/[^a-zA-Z0-9_-]/', '', $collection);
        return $this->dataDir . '/' . $collection . '.json';
    }

    public function read(string $collection): array
    {
        if (isset($this->cache[$collection])) return $this->cache[$collection];
        $path = $this->getPath($collection);
        if (!file_exists($path)) {
            $this->cache[$collection] = [];
            return [];
        }
        $content = file_get_contents($path);
        if ($content === false) {
            $this->cache[$collection] = [];
            return [];
        }
        $data = json_decode($content, true);
        $this->cache[$collection] = is_array($data) ? $data : [];
        return $this->cache[$collection];
    }

    public function write(string $collection, array $data): bool
    {
        $this->cache[$collection] = $data;
        $path = $this->getPath($collection);
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
    }

    public function insert(string $collection, array $record): string
    {
        $data = $this->read($collection);
        $id = bin2hex(random_bytes(8));
        $record['_id'] = $id;
        $record['_created'] = time();
        $record['_updated'] = time();
        $data[] = $record;
        $this->write($collection, $data);
        return $id;
    }

    public function update(string $collection, string $id, array $updates): bool
    {
        $data = $this->read($collection);
        foreach ($data as &$record) {
            if (($record['_id'] ?? '') === $id) {
                foreach ($updates as $key => $value) {
                    $record[$key] = $value;
                }
                $record['_updated'] = time();
                return $this->write($collection, $data);
            }
        }
        return false;
    }

    public function delete(string $collection, string $id): bool
    {
        $data = $this->read($collection);
        foreach ($data as $i => $record) {
            if (($record['_id'] ?? '') === $id) {
                array_splice($data, $i, 1);
                return $this->write($collection, $data);
            }
        }
        return false;
    }

    public function findById(string $collection, string $id): ?array
    {
        $data = $this->read($collection);
        foreach ($data as $record) {
            if (($record['_id'] ?? '') === $id) return $record;
        }
        return null;
    }

    public function findWhere(string $collection, callable $callback): array
    {
        $data = $this->read($collection);
        return array_values(array_filter($data, $callback));
    }

    public function findOneWhere(string $collection, callable $callback): ?array
    {
        $data = $this->read($collection);
        foreach ($data as $record) {
            if ($callback($record)) return $record;
        }
        return null;
    }

    public function count(string $collection): int
    {
        return count($this->read($collection));
    }

    public function paginate(string $collection, int $page = 1, int $perPage = 50): array
    {
        $data = $this->read($collection);
        $total = count($data);
        $pages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($data, $offset, $perPage);
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => $pages,
        ];
    }

    public function clear(string $collection): bool
    {
        unset($this->cache[$collection]);
        return $this->write($collection, []);
    }
}
