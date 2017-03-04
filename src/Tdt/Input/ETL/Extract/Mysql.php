<?php

namespace Tdt\Input\ETL\Extract;

class Mysql extends AExtractor
{
    /**
     * The maximum amount of rows we're going to read
     * @var int
     */
    protected $max_rows = 100000;

    /**
     * Keep track of the total rows read
     * @var int
     */
    protected $rows_read = 0;

    /**
     * Keep track of the pages read
     * @var integer
     */
    protected $page = 0;

    /**
     * The results of a query
     * @var array
     */
    protected $results = [];

    /**
     * The connection to the database to query
     * @var DB
     */
    protected $db;

    /**
     * Keep track of the fact that the query contains a limit
     * statement or not
     * @var bool
     */
    protected $query_contains_limit;

    /**
     * Amount of rows to include in a response
     * @var integer
     */
    const PAGE_SIZE = 1000;

    protected function open()
    {
        // Connect to the database
        $db_config = array(
            'driver'    => 'mysql',
            'host'      => $this->extractor->host,
            'database'  => $this->extractor->database,
            'username'  => $this->extractor->username,
            'password'  => $this->extractor->password,
            'charset'   => 'utf8',
            'collation' => $this->extractor->collation,
        );

        // Configure a connection
        \Config::set('database.connections.tmp_mysql_conn', $db_config);

        // Make a database connection
        $this->db = \DB::connection('tmp_mysql_conn');

        $query = $this->extractor->query;

        // Decide on how many rows to extract, start by checking the limit
        // statement in the query, if not present perform a count and bound
        // the internal max_rows to a maximum

        $this->query_contains_limit = false;

        if (stripos($this->extractor->query, 'limit')) {
            $this->query_contains_limit = true;
        } else {
            // Get the total amount of records for the query for pagination
            preg_match('/select.*?(from.*)/msi', $query, $matches);

            if (empty($matches[1])) {
                throw new \Exception('Failed to make a count statement, make sure the SQL query is valid.');
            }

            $count_query = 'select count(*) as count ' . $matches[1];

            $count_result = $this->db->select($count_query);

            $total_rows = $count_result[0]->count;

            if ($total_rows < $this->max_rows) {
                $this->max_rows = $total_rows;
            }
        }
    }

    /**
     * Tells us if there are more chunks to retrieve
     * @return a boolean whether the end of the file has been reached or not
     */
    public function hasNext()
    {
        return $this->rows_read < $this->max_rows;
    }

    /**
     * Gives us the next chunk to process through our emlp
     * @return a chunk in a php array
     */
    public function pop()
    {
        $this->rows_read++;

        $result = [];

        try {
            $result = (array) array_shift($this->results);

            if (empty($result)) {
                $this->fetchData();

                if ($this->query_contains_limit) {
                    $this->max_rows = count($this->results);
                }

                $result = (array) array_shift($this->results);
            }
        } catch (\Exception $ex) {
            \Log::error('Something went wrong while fetching results from the MySQL query:');
            \Log::error($ex->getMessage());
        }

        return $result;
    }

    private function fetchData()
    {
        // Read the data
        $query = $this->extractor->query;

        if (! $this->query_contains_limit) {
            $offset = $this->page * self::PAGE_SIZE;
            $this->page++;

            $query .= ' limit ' . self::PAGE_SIZE;
            $query .= ' offset ' . $offset;
        }

        $this->results = $this->db->select($query);
    }

    /**
     * Finalization, closing a handle can be done here. This function is called from the destructor of this class
     */
    protected function close()
    {
        //
    }
}
