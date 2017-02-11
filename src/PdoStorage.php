<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2016 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Storage;

use PDO;

class PdoStorage implements StorageInterface
{
    protected $pdo;

    public function __construct(PDO $pdo, callable $notify)
    {
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
        $this->pdo = $pdo;
        $this->notify = $notify;
    }

    public function get(string $sql, array $params = null, bool $all = true)
    {
        $notify = $this->notify;
        $notify('start_query', ['sql' => $sql]);
        $stmt = $this->pdo->prepare($sql);
        // $params = $params ?? [];
        $i = 1;
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $stmt->bindValue(':' . $key, $value);
            } else {
                $stmt->bindValue($i++, $value);
            }
        }
        $stmt->execute();
        $result = $all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $notify('finish_query');

        return $result;
    }

    public function set(string $sql, array $params = null)
    {
        $notify = $this->notify;
        $notify('start_query', ['sql' => $sql]);
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $result = $stmt->execute();
        $stmt->closeCursor();
        $notify('finish_query');

        return $result;
    }
}
