<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-15 20:31:31
 * @modify date 2022-05-16 15:35:57
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;

if (!function_exists('saveOrUpdateConfig'))
{
    function saveOrUpdateConfig(string $confName, $data, $next = '')
    {
        $db = DB::getInstance();
        $checkConf = $db->prepare('select setting_value from setting where setting_name = ?');
        $checkConf->execute([$confName]);

        if ($checkConf->rowCount() < 1)
        {
            $process = $db->prepare('insert into setting set setting_name = ?, setting_value = ?');
            $process->execute([$confName, serialize($data)]);
        }
        else
        {
            $process = $db->prepare('update setting set setting_value = ? where setting_name = ?');
            $process->execute([serialize($data), $confName]);
        }

        $message = 'Config bas been saved';
        if (!$process) $message = 'Failed to save configuration';

        utility::jsToastr($process ? 'Success' : 'Error', $message, $process ? 'success' : 'error');
        if (is_callable($next)) $next();
        simbioRedirect($_SERVER['PHP_SELF'] . '?' . httpQuery());
    }
}

if (!function_exists('simbioRedirect'))
{
    function simbioRedirect($url, $callback = '')
    {
        $js = <<<HTML
            top.$('#mainContent').simbioAJAX('{$url}');
        HTML;

        if (is_callable($callback)) 
        {
            return $callback($js);
        }
        else
        {
            echo '<script>' . $js . '</script>';
        }
    }
}

if (!function_exists('httpQuery'))
{
    function httpQuery($query = [])
    {
        return http_build_query(array_unique(array_merge($_GET, $query)));
    }
}

if (!function_exists('isAlreadyBackup'))
{
    function isAlreadyBackup()
    {
        $db = DB::getInstance();
        $check = $db->prepare('select user_id from backup_log where substring(backup_time, 1,10) >= ?');
        $check->execute([date('Y-m-d')]);

        return (bool)$check->rowCount();
    }
}

if (!function_exists('setUpFile'))
{
    function setUpFile()
    {
        $fileMap = [
            MDLBS . 'circulation/circulation_base_lib.inc.php',
            MDLBS . 'circulation/loan_list.php',
            MDLBS . 'circulation/fines_list.php',
        ];

        foreach ($fileMap as $file) {
            $fileBackup = str_replace('.php', '.orig.php', $file);
            $fileCustom = __DIR__ . '/custom/' . basename($file);

            if (!file_exists($fileBackup))
            {
                // backup first
                copy($file, $fileBackup);

                // copying custom file
                copy($fileCustom, $file);
            }
        }
    }
}

if (!function_exists('rollBackFile'))
{
    function rollBackFile()
    {
        $fileMap = [
            MDLBS . 'circulation/circulation_base_lib.inc.php',
            MDLBS . 'circulation/loan_list.php',
            MDLBS . 'circulation/fines_list.php',
        ];

        foreach ($fileMap as $file) {
            $fileBackup = str_replace('.php', '.orig.php', $file);

            if (!file_exists($fileBackup))
            {
                // rollback first
                copy($fileBackup, $file);
            }
        }
    }
}

if (!function_exists('changeTableStructure'))
{
    function changeTableStructure(int $digit, int $decimal)
    {
        $db = DB::getInstance();
        $sql = <<<SQL
            ALTER TABLE `mst_member_type`
                CHANGE `fine_each_day` `fine_each_day` decimal({$digit},{$decimal}) NOT NULL DEFAULT '0' AFTER `reborrow_limit`;
            ALTER TABLE `mst_loan_rules`
                CHANGE `fine_each_day` `fine_each_day` decimal({$digit},{$decimal}) NOT NULL DEFAULT '0' AFTER `reborrow_limit`;
            ALTER TABLE `fines`
                CHANGE `debet` `debet` decimal({$digit},{$decimal}) NOT NULL DEFAULT '0' AFTER `member_id`,
                CHANGE `credit` `credit` decimal({$digit},{$decimal}) NOT NULL DEFAULT '0' AFTER `debet`;
        SQL;

        @$db->query($sql);
    }
}