<?php

namespace Services\Db;

use Exceptions\DbException;

class Db
{
    /** @var \PDO */
    private $pdo;
    private $result;
    private $transactionList;


    public function __construct()
    {
        $dbOptions = (require __DIR__ . '/../settings.php')['db'];
        try {
            $this->pdo = new \PDO(
                'mysql:host=' . $dbOptions['host'] . ';dbname=' . $dbOptions['dbname'],
                $dbOptions['user'],
                $dbOptions['password']
            );
            $this->pdo->setAttribute(\PDO::ERRMODE_EXCEPTION, \PDO::ATTR_ERRMODE);
            $this->pdo->exec('SET NAMES UTF8');

        } catch (\PDOException $e) {
            throw new DbException('Ошибка подключения к базе данных:' . $e->getMessage());
        }
    }

    public function query(string $sql, $params = [], string $className = 'stdClass'): ?array
    {
        $sth = $this->pdo->prepare($sql);
        $result = $sth->execute($params);

        if (false === $result) {
            return null;
        }
        return $sth->fetchAll(\PDO::FETCH_CLASS, $className);
    }

    public function transactionElement(string $sql, $params = [])
    {
        $this->transactionList[$sql] = $params;
        return $this;
    }

    public function startTransaction($fetchMethod)
    {
        try {
            $this->pdo->beginTransaction();
            foreach ($this->transactionList as $sql => $params) {
                $sth = $this->pdo->prepare($sql);
                $result = $sth->execute($params);
                if (false === $result) {
                    throw new \PDOException('Неверный запрос');
                }
                $this->result[$sql] = $sth->fetchAll($fetchMethod);
            }
            $this->pdo->commit();
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            echo $e->getMessage();
        }
        $this->result = array_filter($this->result);
        return $this->result;
    }
}

$builder = new QueryBuilder();
$builder->insert()->select()->where()->execute();
##### Пример
$db = new Db();
$result = $db->transactionElement('INSERT INTO `categories`(`id`, `name`) VALUES (:id,:name)', ['id' => 400, 'name' => 'idiot'])
    ->transactionElement('UPDATE `categories` SET `id`= :id,`name`=:name WHERE id = :id', ['id' => 160, 'name' => 'yes', 'id' => 13])
    ->startTransaction(\PDO::FETCH_ASSOC);



