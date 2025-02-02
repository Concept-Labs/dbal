<?php
namespace Concept\DBAL\DML\Expression;

class KeywordEnum
    {
        public const DESCRIBE = 'DESCRIBE';
        public const EXPLAIN = 'EXPLAIN';
        public const RAW = 'RAW';
        public const LOCK = 'LOCK';
        public const WITH = 'WITH';
        public const SELECT = 'SELECT';
        public const INSERT = 'INSERT';
        public const REPLACE = 'REPLACE';
        public const IGNORE = 'IGNORE';
        public const DELAYED = 'DELAYED';
        public const COLUMNS = 'COLUMNS';
        public const ON_DUPLICATE = 'ON DUPLICATE KEY UPDATE';
        public const UPDATE = 'UPDATE';
        public const DELETE = 'DELETE';
        public const PARTITION_BY = 'PARTITION BY';
        public const FROM = 'FROM';
        public const WHERE = 'WHERE';
        public const SET = 'SET';
        public const VALUES = 'VALUES';
        public const RETURNING = 'RETURNING';
        public const INTO = 'INTO';
        public const CASE = 'CASE';
        public const WHEN = 'WHEN';
        public const THEN = 'THEN';
        public const ELSE = 'ELSE';
        public const END = 'END';
        public const AND = 'AND';
        public const OR = 'OR';
        public const NOT = 'NOT';
        public const NULL = 'NULL';
        public const IS = 'IS';
        public const LIKE = 'LIKE';
        public const IN = 'IN';
        public const BETWEEN = 'BETWEEN';
        public const EXISTS = 'EXISTS';
        public const ALL = 'ALL';
        public const ANY = 'ANY';
        public const ASC = 'ASC';
        public const DESC = 'DESC';
        public const NULLS_FIRST = 'NULLS FIRST';
        public const NULLS_LAST = 'NULLS LAST';

        public const ORDER = 'ORDER';
        public const BY = 'BY';
        public const ORDER_BY = 'ORDER BY';
        public const GROUP = 'GROUP';
        public const GROUP_BY = 'GROUP BY';
        public const HAVING = 'HAVING';
        public const LIMIT = 'LIMIT';
        public const OFFSET = 'OFFSET';
        public const AS = 'AS';
        public const JOIN = 'JOIN';
        public const INNER = 'INNER';
        public const INNER_JOIN = 'INNER JOIN';
        public const LEFT = 'LEFT';
        public const LEFT_JOIN = 'LEFT JOIN';
        public const RIGHT = 'RIGHT';
        public const RIGHT_JOIN = 'RIGHT JOIN';
        public const OUTER = 'OUTER';
        public const OUTER_JOIN = 'OUTER JOIN';
        public const CROSS = 'CROSS';
        public const CROSS_JOIN = 'CROSS JOIN';
        public const NATURAL = 'NATURAL';
        public const NATURAL_JOIN = 'NATURAL JOIN';
        public const ON = 'ON';
        public const USING = 'USING';
        public const UNION = 'UNION';
        public const UNION_ALL = 'UNION ALL';
        public const WINDOW = 'WINDOW';
        public const INTERSECT = 'INTERSECT';
        public const EXCEPT = 'EXCEPT';
        public const DISTINCT = 'DISTINCT';
        public const COUNT = 'COUNT';
        public const SUM = 'SUM';
        public const AVG = 'AVG';
        public const MIN = 'MIN';
        public const MAX = 'MAX';

        public const FOR_UPDATE = 'FOR UPDATE';
        public const LOCK_IN_SHARE_MODE = 'LOCK IN SHARE MODE';
        public const COMMENT = 'COMMENT';
    }