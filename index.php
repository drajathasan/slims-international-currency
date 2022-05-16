<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2022-05-15 19:32:50
 * @File name           : index.php
 */

defined('INDEX_AUTH') OR die('Direct access not allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
// set dependency
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require __DIR__ . '/helper.php';
// end dependency

// privileges checking
$can_read = utility::havePrivilege('system', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

// Get data
$currencySettings = $sysconf['currencySetting']??null;
$backupIsReady = isAlreadyBackup();

if (isset($_POST['saveData']))
{
    $data = ['code' => strtoupper($_POST['code']), 'maxDigit' => (int)$_POST['maxDigit'], 'decimal' => (int)$_POST['decimal']];
    saveOrUpdateConfig('currencySetting', $data, function() {
        setUpFile();
        changeTableStructure((int)$_POST['maxDigit'], (int)$_POST['decimal']);
    });
}

$page_title = 'Currency Settings';

/* Action Area */
?>
<div class="menuBox">
    <div class="menuBoxInner memberIcon">
        <div class="per_title">
            <h2><?php echo $page_title; ?></h2>
        </div>
        <?php 
        if (!$backupIsReady) echo '<div class="errorBox">You must backup your database before setup this plugin!</div>';
        ?>
    </div>
</div>
<?php
if (!$backupIsReady) 
{
    simbioRedirect(MWB . 'system/backup.php', function($js){
        echo <<<HTML
            <script>
                setTimeout(() => {
                    {$js}
                }, 5000);
            </script>
        HTML;
        exit;
    });
}

/* End Action Area */
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'post');
$form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

// Currency Code
$html  = '<input type="text" class="form-control col-2 text-uppercase" name="code" value="' . ($currencySettings['code']??'') . '"/>';
$html .= '<strong>for more currency code, visit <a href="https://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="blank">Alpha Currency Codes</a></strong>';
$form->addAnything('Currency Code', $html);

// Currency max number of money
$html  = '<input type="number" id="inputDigit" name="maxDigit" class="form-control col-2" value="' . ($currencySettings['maxDigit']??0) . '"/>';
$html .= '<strong>E.g <code id="maxDigit" style="font-size: 10pt">"5"</code> as max digit of money: <code id="instanceResult" style="font-size: 10pt">10000</code></strong>';
$form->addAnything('Max digit of money', $html);

// Currency decimal digit precision
$html  = '<input type="number" id="inputDecimal" name="decimal" class="form-control col-2" value="' . ($currencySettings['decimal']??0) . '"/>';
$html .= '<strong>E.g <code id="decimal" style="font-size: 10pt">"2"</code> decimal precision digit at money: <code id="instanceDecimal" style="font-size: 10pt">100,00</code></strong>';
$form->addAnything('Decimal digit precision', $html);

// print out the form object
echo $form->printOut();
?>
<script>
    $('#inputDigit').keyup(function(){
        let maxDigit = $('#maxDigit');
        let instanceResult = $('#instanceResult');
        let input = $(this).val();

        maxDigit.text(`"${input}"`);
        instanceResult.text(`1${'0'.repeat(parseInt(input) - 1)}`);
        $('#inputDecimal').trigger('keyup');
    });

    $('#inputDecimal').keyup(function(){
        let maxDigit = $('#inputDigit').val();
        let decimal = $('#decimal');
        let instanceDigital = $('#instanceDecimal');
        let input = $(this).val();

        decimal.text(`"${input}"`);
        instanceDigital.text(`1${'0'.repeat(parseInt(maxDigit) - 1 - parseInt(input))},${'0'.repeat(parseInt(input))}`);
    });
</script>