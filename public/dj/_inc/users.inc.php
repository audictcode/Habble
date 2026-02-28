<?php

class users {

    private $Database, $Session, $Salt;
    private $warningsSchema = null;
    private $columnCache = array();
    private $tableCache = array();

    function __construct($Database, $Session, $Salt) {

        $this->Database = $Database;
        $this->Session = $Session;
        $this->Salt = $Salt;

    } // END __construct

    function register($username, $password, $email, $habbo, $skype, $rank) {

        $usernameRaw = (string) $username;
        $username = $this->Database->Clean($usernameRaw);
        $password = (string) $password;
        $email = $this->Database->Clean($email);
        $habbo = $this->Database->Clean($habbo);
        $skype = $this->Database->Clean($skype);
        $rank = (int) $rank;

        if ($this->userExists($usernameRaw, '') == 1) {
            return 0;
        }

        $passwordHash = $this->Hash($password);
        $fields = array(
            "username='{$username}'",
            "password='{$passwordHash}'"
        );

        if ($this->columnExists('users', 'email')) {
            $fields[] = "email='{$email}'";
        }

        if ($this->columnExists('users', 'habbo')) {
            $fields[] = "habbo='{$habbo}'";
        }

        if ($this->columnExists('users', 'habbo_name')) {
            $fields[] = "habbo_name='{$habbo}'";
        }

        if ($this->columnExists('users', 'skype')) {
            $fields[] = "skype='{$skype}'";
        }

        if ($this->columnExists('users', 'rank')) {
            $fields[] = "rank='{$rank}'";
        }

        if ($this->columnExists('users', 'created_at')) {
            $fields[] = "created_at='" . date('Y-m-d H:i:s') . "'";
        }

        if ($this->columnExists('users', 'updated_at')) {
            $fields[] = "updated_at='" . date('Y-m-d H:i:s') . "'";
        }

        if ($this->Database->Query("insert into users set " . implode(', ', $fields))) {
            return 1;
        }

        return 0;

    } // END register

    function getWarnings2($username) {

        $user = $this->getUserByUsername($username);
        if (!is_array($user)) {
            return;
        }

        $userId = $this->getUserIdFromRow($user);
        if ($userId <= 0) {
            return;
        }

        $schema = $this->getWarningsSchema();
        if ($schema === 'none') {
            return;
        }

        if ($schema === 'legacy') {
            $sql = $this->Database->Query("select * from users_warnings where UserID='{$userId}' order by ID DESC");
        } else {
            if ($this->columnExists('users_warnings', 'warning_score')) {
                $sql = $this->Database->Query("select reason, warning_score as dj_warning_score from users_warnings where user_id='{$userId}' order by id desc");
            } else {
                $sql = $this->Database->Query("select reason, 1 as dj_warning_score from users_warnings where user_id='{$userId}' order by id desc");
            }
        }

        while ($rows = mysql_fetch_assoc($sql)) {
            $reason = '';
            $score = 1;

            if (isset($rows['Reason']) && $rows['Reason'] !== '') {
                $reason = $rows['Reason'];
            } elseif (isset($rows['reason']) && $rows['reason'] !== '') {
                $reason = $rows['reason'];
            }

            if (isset($rows['WarningScore'])) {
                $score = (int) $rows['WarningScore'];
            } elseif (isset($rows['warning_score'])) {
                $score = (int) $rows['warning_score'];
            } elseif (isset($rows['dj_warning_score'])) {
                $score = (int) $rows['dj_warning_score'];
            }

            $reason = htmlspecialchars((string) $reason, ENT_QUOTES, 'UTF-8');
            $score = htmlspecialchars((string) $score, ENT_QUOTES, 'UTF-8');

            echo <<<EOT



                <tr>
                    <td>{$reason}</td>
                    <td>{$score}</td>
                </tr>


EOT;
        }

    }

    function getWarnings($username) {

        $user = $this->getUserByUsername($username);
        if (!is_array($user)) {
            return 0;
        }

        $userId = $this->getUserIdFromRow($user);
        if ($userId <= 0) {
            return 0;
        }

        $schema = $this->getWarningsSchema();
        if ($schema === 'none') {
            return 0;
        }

        if ($schema === 'legacy') {
            if ($this->columnExists('users_warnings', 'WarningScore')) {
                $sql = $this->Database->Query("select coalesce(sum(WarningScore), 0) as total from users_warnings where UserID='{$userId}'");
            } else {
                $sql = $this->Database->Query("select count(*) as total from users_warnings where UserID='{$userId}'");
            }
        } else {
            if ($this->columnExists('users_warnings', 'warning_score')) {
                $sql = $this->Database->Query("select coalesce(sum(warning_score), 0) as total from users_warnings where user_id='{$userId}'");
            } else {
                $sql = $this->Database->Query("select count(*) as total from users_warnings where user_id='{$userId}'");
            }
        }

        $result = mysql_fetch_assoc($sql);
        if (!is_array($result) || !isset($result['total'])) {
            return 0;
        }

        return (int) $result['total'];

    } // end getWarnings

    function getRankID($username) {

        $user = $this->getUserByUsername($username);
        if (!is_array($user) || !isset($user['rank'])) {
            return 0;
        }

        return (int) $user['rank'];

    } // end getRankID

    function getHabbo($username) {

        $user = $this->getUserByUsername($username);
        if (!is_array($user)) {
            return $username;
        }

        $fallback = (isset($user['username']) ? $user['username'] : $username);
        $columns = array('habbo', 'habbo_name', 'name');
        foreach ($columns as $column) {
            if (isset($user[$column]) && trim((string) $user[$column]) !== '') {
                return $user[$column];
            }
        }

        return $fallback;

    }

    function getRankName() {

        if ($this->getRankID($this->Session->getSession("username")) == 1) {

            echo "<span style='font-weight: bold; color: #45629F;'>Radio DJ</span>";

        }

        if ($this->getRankID($this->Session->getSession("username")) == 2) {

            echo "<span style='font-weight: bold; color: #8450A7;'>Head DJ</span>";

        }

        if ($this->getRankID($this->Session->getSession("username")) == 3) {

            echo "<span style='font-weight: bold; color: #4B9D2D;'>Management</span>";

        }

        if ($this->getRankID($this->Session->getSession("username")) == 4) {

            echo "<span style='font-weight: bold; color: red;'>Administrator</span>";

        }

        if ($this->getRankID($this->Session->getSession("username")) > 4) {

            echo "<span style='font-weight: bold; color: red;'>Administrator+</span>";

        }

    }

    function logout() {

        session_destroy();

    }

    function deleteNews($id) {

        $id = $this->Database->Clean($id);

        if ($this->getRankID($this->Session->getSession("username")) >= 4) {

            $this->Database->Query("delete from home_news where ID='{$id}'");
            return 1;

        } else {

            return 0;
        }

    }

    function createNews($title, $content, $author) {

        $title = $this->Database->Clean($title);
        $content = $this->Database->Clean($content);
        $author = $this->Database->Clean($author);

        $date = date("d-m-Y");

        if ($this->Database->Query("insert into home_news set title='{$title}', content='{$content}', author='{$author}', date='{$date}'")) {

        } else {

            return 0;

        }

    } // createNews

    function showNews() {

        $sql = mysql_query("select * from home_news order by ID DESC");

        while ($rows = @mysql_fetch_assoc($sql)) {

            if ($this->getRankID($this->Session->getSession("username")) >= 4) {

                $delete = "<a href='index.php?deleteNews={$rows['ID']}' class='button button-basic-red'>DELETE</a>";

            } else {

                $delete = "";
            }

            echo <<< EOT


                <div class="row-fluid">
                    <div class="span12">
                        <div class="box">
                            <div class="box-head">
                                <i class="icon-reorder"></i>
                                <span>{$rows['title']}</span>
                            </div>
                            <div class="box-body">

                                {$rows['content']}

                                <br /><br />
                                <span style="float:right"><i>Author: DJ {$rows['author']} - {$rows['date']}</i> {$delete}</span>

                            </div>
                        </div>
                    </div>
                </div>

EOT;

            }

    }

    function login($username, $password) {

        $user = $this->getUserByUsername($username);
        if (!is_array($user)) {
            return 0;
        }

        if (isset($user['disabled']) && (int) $user['disabled'] === 1) {
            return 0;
        }

        $storedPassword = (isset($user['password']) ? (string) $user['password'] : '');
        if ($storedPassword === '') {
            return 0;
        }

        $password = (string) $password;
        $legacyHash = $this->Hash($password);

        if ($this->safeCompare($storedPassword, $legacyHash)) {
            return 1;
        }

        if ($this->looksLikeModernHash($storedPassword) && function_exists('password_verify') && password_verify($password, $storedPassword)) {
            return 1;
        }

        if ($this->safeCompare($storedPassword, $password)) {
            return 1;
        }

        return 0;

    }

    function Hash($password) {

        return md5((string) $password . $this->Salt);

    }

    function userExists($username, $password = '') {

        $user = $this->getUserByUsername($username);

        if (is_array($user)) {

            return 1;

        } else {

            return 0;
        }

    }

    function checkLogin() {

        if (!isset($_SESSION['username'])) {

            header('Location: index');
            exit;
            return 0;

        } else {

            return 1;

        }

    }

    private function getUserByUsername($username) {

        $username = $this->Database->Clean($username);
        $sql = $this->Database->Query("select * from users where username='{$username}' limit 1");
        $row = mysql_fetch_assoc($sql);

        if (is_array($row)) {
            return $row;
        }

        return null;

    }

    private function getUserIdFromRow($row) {

        if (is_array($row)) {
            if (isset($row['id'])) {
                return (int) $row['id'];
            }

            if (isset($row['ID'])) {
                return (int) $row['ID'];
            }
        }

        return 0;

    }

    private function getWarningsSchema() {

        if ($this->warningsSchema !== null) {
            return $this->warningsSchema;
        }

        if (!$this->tableExists('users_warnings')) {
            $this->warningsSchema = 'none';
            return $this->warningsSchema;
        }

        if ($this->columnExists('users_warnings', 'user_id')) {
            $this->warningsSchema = 'new';
            return $this->warningsSchema;
        }

        if ($this->columnExists('users_warnings', 'UserID')) {
            $this->warningsSchema = 'legacy';
            return $this->warningsSchema;
        }

        $this->warningsSchema = 'none';
        return $this->warningsSchema;

    }

    private function tableExists($table) {

        if (!preg_match('/^[a-zA-Z0-9_]+$/', (string) $table)) {
            return false;
        }

        $cacheKey = strtolower((string) $table);
        if (isset($this->tableCache[$cacheKey])) {
            return $this->tableCache[$cacheKey];
        }

        $tableEscaped = mysql_real_escape_string((string) $table);
        $result = @mysql_query("SHOW TABLES LIKE '{$tableEscaped}'");
        $exists = ($result && mysql_num_rows($result) > 0);
        $this->tableCache[$cacheKey] = $exists;

        return $exists;

    }

    private function columnExists($table, $column) {

        if (!preg_match('/^[a-zA-Z0-9_]+$/', (string) $table)) {
            return false;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', (string) $column)) {
            return false;
        }

        $cacheKey = strtolower((string) $table) . '.' . strtolower((string) $column);
        if (isset($this->columnCache[$cacheKey])) {
            return $this->columnCache[$cacheKey];
        }

        $tableEscaped = mysql_real_escape_string((string) $table);
        $columnEscaped = mysql_real_escape_string((string) $column);
        $result = @mysql_query("SHOW COLUMNS FROM `{$tableEscaped}` LIKE '{$columnEscaped}'");
        $exists = ($result && mysql_num_rows($result) > 0);
        $this->columnCache[$cacheKey] = $exists;

        return $exists;

    }

    private function safeCompare($left, $right) {

        $left = (string) $left;
        $right = (string) $right;

        if (function_exists('hash_equals')) {
            return hash_equals($left, $right);
        }

        return ($left === $right);

    }

    private function looksLikeModernHash($hash) {

        $hash = (string) $hash;
        return (strpos($hash, '$2y$') === 0
            || strpos($hash, '$2a$') === 0
            || strpos($hash, '$2b$') === 0
            || strpos($hash, '$argon2i$') === 0
            || strpos($hash, '$argon2id$') === 0);

    }
}

?>
