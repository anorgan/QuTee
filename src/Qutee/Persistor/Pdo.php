<?php

namespace Qutee\Persistor;

use Qutee\Task;

/**
 * PDO persistor, use table with columns: name, data, priority
 *
 * MySQL 
 CREATE TABLE IF NOT EXISTS `queue` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `method_name` VARCHAR(255) NULL,
    `data` TEXT NULL,
    `priority` TINYINT NOT NULL,
    `unique_id` VARCHAR(32) NULL,
    `created_at` DATETIME NOT NULL,
    `is_taken` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`))
  ENGINE = InnoDB;

 *
 * @author anorgan
 */
class Pdo implements PersistorInterface
{
    /**
     *
     * @var array
     */
    private $_options = array();
    
    /**
     *
     * @var \PDO
     */
    private $_pdo;
    
    /**
     *
     * @var int
     */
    private static $_reconnects = 3;

    /**
     * 
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo = null)
    {
        $this->_pdo = $pdo;
    }

    /**
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     *
     * @param array $options
     *
     * @return Pdo
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;

        return $this;
    }

    /**
     * 
     * @param \Qutee\Task $task
     *
     * @return \Qutee\Persistor\Pdo
     */
    public function addTask(Task $task)
    {
        // Check if the task is unique and already exists
        if ($task->isUnique() && $this->_hasTaskByUniqueId($task->getUniqueId())) {
            return $this;
        }

        $statement = $this->_getPdo()->prepare(sprintf(' 
            INSERT INTO %s
            SET
                name        = :name,
                method_name = :method_name,
                data        = :data,
                priority    = :priority,
                unique_id   = :unique_id,
                created_at  = NOW()
        ', $this->_options['table_name']));

        $statement->execute(array(
            ':name'        => $task->getName(),
            ':method_name' => $task->getMethodName(),
            ':data'        => serialize($task),
            ':priority'    => $task->getPriority(),
            ':unique_id'   => $task->isUnique() ? $task->getUniqueId() : null,
        ));

        return $this;
    }

    /**
     * 
     * @param int $priority
     *
     * @return Task|null
     */
    public function getTask($priority = null)
    {
        $this->_getPdo()->exec('SET @ID = 0;');
        
        // Update first task that is not taken as taken, taking its ID
        $statement = $this->_getPdo()->prepare(sprintf('
            UPDATE
                %s
            SET
                id          = @ID := id,
                is_taken    = 1
            WHERE
                is_taken    = 0
                %s
            ORDER BY
                created_at ASC
            LIMIT 1
        ', $this->_options['table_name'], $priority !== null ? 'AND priority = :priority' : ''));
        $array = null;
        
        if ($priority !== null) {
            $array = array(':priority' => $priority);
        }

        $statement->execute($array);
        if ($statement->rowCount() === 0) {
            // No tasks
            return null;
        }

        // Now, get that task
        $statement  = $this->_getPdo()->prepare(sprintf('SELECT * FROM %s WHERE id = @ID', $this->_options['table_name']));
        $statement->execute();
        $taskData   = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$taskData) {
            return null;
        }

        return unserialize($taskData['data']);
    }

    /**
     * 
     * @param int $priority
     *
     * @return Task[]
     */
    public function getTasks($priority = null) 
    {
        if ($priority !== null) {
            $array = array(':priority' => $priority);
        } else {
            $array = null;
        }

        $statement  = $this->_getPdo()->query(sprintf('
            SELECT * FROM %s %s
        ', $this->_options['table_name'], $priority !== null ? 'WHERE priority = :priority' : ''));
        $statement->execute($array);
        $tasks  = $statement->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($tasks as $k => $data) {
            $tasks[$k]  = unserialize($data['data']);
        }

        return $tasks;
    }

    /**
     * Clear all tasks
     */
    public function clear()
    {
        $this->_getPdo()->exec(sprintf('DELETE FROM %s', $this->_options['table_name']));
    }

    /**
     * 
     * @param \PDO $pdo
     *
     * @return \Qutee\Persistor\Pdo
     */
    public function setPdo(\PDO $pdo) {
        $this->_pdo = $pdo;
        
        return $this;
    }

    /**
     * 
     * @return \PDO
     */
    protected function _getPdo()
    {
        if (null === $this->_pdo) {
            $dsn        = $this->_options['dsn'];
            $username   = $this->_options['username'];
            $password   = $this->_options['password'];

            $options    = array(
                \PDO::ATTR_EMULATE_PREPARES     => false, 
                \PDO::ATTR_ERRMODE              => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE   => \PDO::FETCH_ASSOC
            );

            if (isset($this->_options['options'])) {
                $options = $this->_options['options'] + $options;
            }

            $this->_pdo = new \PDO($dsn, $username, $password, $options);
        } else {
            $this->_testConnection($this->_pdo);
        }

        return $this->_pdo;
    }
    
    /**
     * Test connection, reconnect if needed
     *
     * @param \PDO $pdo
     *
     * @throws \Qutee\Persistor\PDOException
     */
    protected function _testConnection(\PDO &$pdo)
    {
        try {
            // Dummy query
            $pdo->query('SELECT 1');
        } catch (\PDOException $e) {
            // Mysql server has gone away or similar error
            self::$_reconnects--;

            if (self::$_reconnects <= 0) {
                // No more tests, throw error, reinstate reconnects
                self::$_reconnects = 3;
                throw $e;
            }

            $pdo = null;
            $this->_getPdo();
        }
    }

    /**
     * 
     * @param string $uniqueId
     * 
     * @return boolean
     */
    protected function _hasTaskByUniqueId($uniqueId)
    {
        $stmt = $this->_getPdo()
            ->prepare(sprintf('SELECT id FROM %s WHERE is_taken = 0 AND unique_id = ?', $this->_options['table_name']));
        $stmt->execute(array($uniqueId));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return !empty($rows);
    }
}
