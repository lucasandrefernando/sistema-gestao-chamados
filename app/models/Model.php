<?php

/**
 * Classe base para todos os modelos
 */
abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    /**
     * Construtor
     * 
     * @param string $table Nome da tabela
     */
    public function __construct($table = '')
    {
        $this->db = Database::getInstance()->getConnection();
        if (!empty($table)) {
            $this->table = $table;
        }
    }


    /**
     * Busca todos os registros que atendem a uma condição
     *
     * @param string $where Condição WHERE
     * @param array $params Parâmetros para a condição
     * @param string $orderBy Ordenação
     * @return array Registros encontrados
     */
    public function findAll($where = '', $params = [], $orderBy = '')
    {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }

        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um registro pelo ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    /**
     * Busca um registro por condição
     */
    public function findOne($where, $params = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$where} LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch();
    }

    /**
     * Insere um novo registro
     */
    public function create($data)
    {
        $fields = array_keys($data);
        $placeholders = array_map(function ($field) {
            return ":{$field}";
        }, $fields);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return $this->db->lastInsertId();
    }

    /**
     * Atualiza um registro
     */
    public function update($id, $data)
    {
        $fields = array_keys($data);
        $setClause = array_map(function ($field) {
            return "{$field} = :{$field}";
        }, $fields);

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " 
                WHERE {$this->primaryKey} = :id";

        $data['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Exclui um registro
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    /**
     * Conta registros
     */
    public function count($where = '', $params = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['total'];
    }

    /**
     * Executa uma consulta personalizada
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Inicia uma transação
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    /**
     * Confirma uma transação
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * Reverte uma transação
     */
    public function rollback()
    {
        return $this->db->rollBack();
    }

    /**
     * Executa uma consulta SQL personalizada e retorna todos os resultados
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parâmetros para a consulta
     * @return array Resultados da consulta
     */
    public function executeQuery($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executa uma consulta SQL personalizada e retorna um único resultado
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parâmetros para a consulta
     * @return array|false Resultado da consulta ou false se não encontrar
     */
    public function executeQuerySingle($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Executa uma consulta SQL de atualização (INSERT, UPDATE, DELETE)
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parâmetros para a consulta
     * @return int Número de linhas afetadas
     */
    public function executeUpdate($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Verifica se uma tabela existe no banco de dados
     * 
     * @param string $tableName Nome da tabela
     * @return bool True se a tabela existir, false caso contrário
     */
    public function tableExists($tableName)
    {
        try {
            $sql = "SELECT 1 FROM {$tableName} LIMIT 1";
            $this->db->query($sql);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Executa uma consulta SQL direta (para DDL como CREATE TABLE)
     * 
     * @param string $sql Consulta SQL
     * @return bool True se a consulta for executada com sucesso
     */
    public function executeRawQuery($sql)
    {
        try {
            return $this->db->exec($sql) !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Retorna a conexão com o banco de dados
     * 
     * @return PDO Conexão com o banco de dados
     */
    public function getDb()
    {
        return $this->db;
    }
}
