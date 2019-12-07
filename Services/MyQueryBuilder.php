<?php


namespace Services\Db;

class MyQueryBuilder
{
    private $sql;
    private $params;
    /**
     *
     * @var \PDO
     */
    private $pdo;
    /**
     * @var \PDOStatement $content
     *
     */
    private $content;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function select()
    {
        $arg = func_get_args();
        $arg = implode(',', $arg);
        $sql = 'SELECT ' . $arg;
        $this->sql[] = $sql;
        return $this;
    }

    public function from()
    {
        $arg = func_get_args();
        $arg = implode(',', $arg);
        $sql = 'FROM ' . $arg;
        $this->sql[] = $sql;
        return $this;
    }

    public function insert()
    {
        $arg = func_get_args();
        $table = 'INSERT INTO ' . $arg[0];
        unset($arg[0]);
        $arg = implode(',', $arg);
        $columns = $table . '(' . $arg . ')';
        $this->sql[] = $columns;
        return $this;
    }

    public function delete()
    {
        $arg = func_get_args();
        $arg = implode(',', $arg);
        $sql = 'DELETE ' . $arg;
        $this->sql[] = $sql;
        return $this;
    }

    public function update()
    {
        $arg = func_get_args();
        $arg = implode(',', $arg);
        $sql = 'UPDATE ' . $arg;
        $this->sql[] = $sql;
        return $this;
    }

    public function where()
    {
        $arg = func_get_args();
        $arg = implode(',', $arg);
        $sql = 'WHERE ' . $arg;
        $this->sql[] = $sql;
        return $this;
    }

    public function orderBy()
    {
        $arg = func_get_args();
        $arg = implode(',', $arg);
        $sql = 'ORDER BY ' . $arg;
        $this->sql[] = $sql;
        return $this;
    }

    public function values()
    {
        $arg = func_get_args();
        $arg = implode(',', $arg);
        $sql = 'VALUES ' . '(' . $arg . ')';
        $this->sql[] = $sql;
        return $this;
    }

    public function set()
    {
        $arg = func_get_args();
        $arg = implode(',', $arg);
        $sql = 'SET ' . $arg;
        $this->sql[] = $sql;
        return $this;
    }

    public function bindParams($arg)
    {
        $this->params[] = $arg;
        return $this;
    }

    public function execute()
    {
        if (empty($this->params)) {
            $this->executeWithoutParams();
            return $this;
        } else {
            $this->executeWithParams();
            return $this;
        }
    }

    public function fetch($fetchMethod = null)
    {
        $result = $this->content->fetchAll($fetchMethod);
        return $result;
    }

    private function executeWithoutParams()
    {
        $sql = implode(' ', $this->sql);
        $sth = $this->pdo->query($sql);
        $this->content = $sth;
    }

    private function executeWithParams()
    {
        $params = $this->params[0];
        $sql = implode(' ', $this->sql);
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params);
        $this->content = $sth;
    }

}

### Пример
$dbOptions = (require __DIR__ . '/../settings.php')['db'];
$pdo = new \PDO(
    'mysql:host=' . $dbOptions['host'] . ';dbname=' . $dbOptions['dbname'],
    $dbOptions['user'],
    $dbOptions['password']
);

$pdo->exec('SET NAMES UTF8');

$newBuilder = new MyQueryBuilder($pdo);
//$result = $newBuilder->insert('categories', 'id', 'name')->values(':id', ':name')->bindParams(['id' => 200, 'name' => 'Петр'])->execute();
//$result = $newBuilder->select('*')->from('categories')->execute()->fetch(\PDO::FETCH_ASSOC);
var_dump($result);

