<?php

/**
 * Класс для работы с базой данных (PDO)
 *
 * @property \PDO $dbh
 *
 * @author		WebImperia Dev
 * @link		https://github.com/webimperia/oc-pdo
 * @since 0.0.1
 */
final class OC_PDO
{
    /**
     * Ссылка на соединение с БД
     *
     * @var \PDO
     */
    private $dbh;

    /**
     * Список параметров соединения
     *
     * @var array
     */
    private $options = array(
        'PDO::ATTR_ERRMODE' => PDO::ERRMODE_SILENT
    );

    /**
     * Число затронутых прошлой операцией рядов
     *
     * @var int
     */
    private $affectedRows = 0;

    /**
     * Данные для подключения к БД
     *
     * @var \stdClass
     */
    private $params = array();

    /**
     * Устанавливает параметры соединения и выполняет подключение к БД
     *
     * @param string $host Адрес сервера
     * @param string $user Имя пользователя
     * @param string $pass Пароль пользователя
     * @param string $name Имя базы данных
     * @param string $charset Кодировка соединения
     */
    public function __construct($host, $user, $pass, $name, $charset = 'utf8')
    {
        $this->params = new stdClass;

        # сохраняем данные подключения
        $this->params->host    = $host;
        $this->params->user    = $user;
        $this->params->pass    = $pass;
        $this->params->name    = $name;
        $this->params->charset = $charset;
        $this->params->connstr = "mysql:host={$host};dbname={$name};charset={$charset}";

        # добавляем параметры соединения
        $this->options['PDO::MYSQL_ATTR_INIT_COMMAND'] = "SET NAMES '{$charset}'";

        $this->connect();
    }

    /**
     * Выполняет подключение к БД
     */
    public function connect()
    {
        try {
            $this->dbh = new PDO($this->params->connstr, $this->params->user, $this->params->pass, $this->options);
            if (version_compare(PHP_VERSION, '5.3.6', '<=')) {
                $this->dbh->exec($this->options['PDO::MYSQL_ATTR_INIT_COMMAND']);
            }
        } catch (PDOException $exception) {
            trigger_error($exception->getMessage());
        }
    }

    /**
     * Выполняет запрос к БД
     *
     * @param string $sql
     * @return \stdClass
     */
    public function query($sql = null)
    {
        if ($this->dbh) {
            $data = new stdClass;
            $sth                = $this->dbh->query($sql);
            $this->affectedRows = $sth->rowCount();
            $data->rows         = $sth ? $sth->fetchAll() : array();
            $data->row          = isset($data->rows[0]) ? $data->rows[0] : null;
            $data->num_rows     = $this->affectedRows;
            return $data;
        }
        return null;
    }

    /**
     * Заключает строку в кавычки для использования в запросе
     *
     * @param mixed $string Экранируемая строка
     * @return string Возвращает экранированную строку, либо FALSE, если драйвер не поддерживает экранирование
     */
    public function escape($string = null)
    {
        return $this->dbh ? $this->dbh->quote($string) : null;
    }

    /**
     * Получает число затронутых прошлой операцией рядов
     *
     * @return int
     */
    public function countAffected()
    {
        return $this->affectedRows;
    }

    /**
     * Получает ID последней вставленной строки или последовательное значение
     *
     * @return int
     */
    public function getLastId()
    {
        return $this->dbh ? $this->dbh->lastInsertId() : 0;
    }

    /**
     * Получает имя драйвера
     *
     * @return string|null
     */
    public function getDriverName()
    {
        return $this->dbh ? $this->dbh->getAttribute(PDO::ATTR_DRIVER_NAME) : null;
    }

    /**
     * Получает информацию о версии клиентских библиотек, которые использует драйвер PDO
     *
     * @return string|null
     */
    public function getVersion()
    {
        return $this->dbh ? $this->dbh->getAttribute(PDO::ATTR_CLIENT_VERSION) : null;
    }

    /**
     * Закрытие соединения с базой данных
     */
    public function close()
    {
        $this->dbh = null;
    }

    public function __destruct()
    {
        $this->close();
    }

}
