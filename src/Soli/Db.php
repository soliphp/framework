<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use PDO;

/**
 * Db Wrapper
 */
class Db
{
    /**
     * 数据库连接实例
     *
     * @var \PDO
     */
    protected $connection = null;

    /**
     * 预处理
     *
     * @var \PDOStatement
     */
    protected $stmt = null;

    /**
     * 默认配置
     *
     * @var array
     */
    protected $defaultConfig = [
        'adapter'     => 'mysql',
        'host'        => 'localhost',
        'port'        => '3306',
        'dbname'      => '',
        'username'    => 'root',
        'password'    => '',
        'charset'     => 'utf8',
        'unix_socket' => '',
    ];

    /**
     * 外部传入的 MySQL 配置信息
     *
     * @var array
     */
    protected $config = [];

    /**
     * @param array|\ArrayAccess $config {
     *   @var string adapter
     *   @var string host
     *   @var int    port
     *   @var string unix_socket
     *   @var string dbname
     *   @var string username
     *   @var string password
     *   @var string charset
     * }
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->getConnection();
    }

    /**
     * 创建数据库连接
     *
     * @return \PDO
     */
    protected function getConnection()
    {
        // 关闭连接
        $this->close();

        $mergedConfig = $this->defaultConfig;
        foreach ($this->config as $key => $value) {
            $mergedConfig[$key] = $value;
        }
        $config = $mergedConfig;

        if ($config['unix_socket']) {
            $address = sprintf('unix_socket=%s', $config['unix_socket']);
        } else {
            $address = sprintf('host=%s;port=%s', $config['host'], $config['port']);
        }

        $adapter = strtolower($config['adapter']);
        $dsn = sprintf(
            $adapter . ':%s;dbname=%s;charset=%s',
            $address,
            $config['dbname'],
            $config['charset']
        );

        // PHP_VERSION >= 5.3.9
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT            => 1,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ];

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        if ($adapter == 'mysql') {
            $pdo->exec('SET NAMES ' . $pdo->quote($config['charset']));
        }

        $this->connection = $pdo;
        return $pdo;
    }

    /**
     * 返回最后插入行的 ID 或序列值，数据库需要将主键设置为自增
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * 返回 SQL 语句影响行数
     *
     * @return int
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * 执行一条 SQL 语句
     *
     * @param string $sql SQL语句
     * @param array  $binds 绑定数据
     * @param string $fetchMode column|row|all 返回的数据结果类型
     * @param int    $retries 连接失效时的重复次数
     * @return array|int|string
     *   插入数据返回插入数据的主键ID，更新/删除数据返回影响行数
     *   查询语句则根据 $fetchMode 返回对应类型的结果集
     */
    public function query($sql, array $binds = [], $fetchMode = 'all', $retries = 3)
    {
        try {
            return $this->executeInternal($sql, $binds, $fetchMode);
        } catch (\PDOException $e) {
            // 2006: MySQL server has gone away
            // 2013: Lost connection to MySQL server during query
            if ($retries > 0 && ($e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013)) {
                $this->getConnection();
                return $this->query($sql, $binds, $fetchMode, $retries - 1);
            } else {
                throw $e;
            }
        }
    }

    /**
     * 执行一条 SQL 语句（内部方法）
     *
     * @param string $sql SQL语句
     * @param array  $binds 绑定数据
     * @param string $fetchMode column|row|all 返回的数据结果类型
     * @return array|int|string
     *   插入数据返回插入数据的主键ID，更新/删除数据返回影响行数
     *   查询语句则根据 $fetchMode 返回对应类型的结果集
     */
    protected function executeInternal($sql, array $binds = [], $fetchMode = 'all')
    {
        // prepare -> binds -> execute
        $this->stmt = $this->connection->prepare($sql);
        $this->stmt->execute($binds);

        list($type) = explode(' ', $sql, 2);
        $type = strtoupper($type);

        // 返回相应操作类型的数据结果
        switch ($type) {
            case 'INSERT':
                return $this->lastInsertId();
            case 'DELETE':
                // no break
            case 'UPDATE':
                return $this->rowCount();
            default:
                // SELECT, USE, SHOW, DESCRIBE, EXPLAIN ...
                return $this->fetchMode($fetchMode);
        }
    }

    /**
     * 根据 fetchMode 获取相应的查询结果，这里的 fetchMode 不是 PDO::FETCH_* fetchStyle 常量
     *
     * @param string $fetchMode row|column|all
     * @return array|string
     */
    protected function fetchMode($fetchMode)
    {
        $fetchMode = strtoupper($fetchMode);
        switch ($fetchMode) {
            case 'ROW':
                // 获取一行数据
                return $this->stmt->fetch();
            case 'COLUMN':
                // 获取一个字段值
                return $this->stmt->fetchColumn();
            case 'ALL':
                // no break
            default:
                // 获取完整的查询结果
                return $this->stmt->fetchAll();
        }
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        $this->stmt = null;
        $this->connection = null;
    }

    // 事务

    /**
     * 开启事务，关闭自动提交
     *
     * @param int $retries 连接失效时的重复次数
     * @return bool
     */
    public function beginTrans($retries = 3)
    {
        try {
            return $this->connection->beginTransaction();
        } catch (\PDOException $e) {
            // 2006: MySQL server has gone away
            // 2013: Lost connection to MySQL server during query
            if ($retries > 0 && ($e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013)) {
                $this->getConnection();
                return $this->beginTrans($retries - 1);
            } else {
                throw $e;
            }
        }
    }

    /**
     * 提交更改，开启自动提交
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * 回滚更改，开启自动提交
     */
    public function rollBack()
    {
        $this->connection->rollBack();
    }

    /**
     * 检查是否在一个事务内
     *
     * @return bool
     */
    public function inTrans()
    {
        return $this->connection->inTransaction();
    }
}
