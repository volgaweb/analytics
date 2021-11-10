<?php
/**
 * Settings of Volga Web Analytics module
 */

use Bitrix\Main\Localization\Loc;


$sModuleId = 'vw.analytics';
$sModuleInnerId = 'vw_analytics';
CModule::IncludeModule($sModuleId);
global $MESS;
Loc::loadLanguageFile(__FILE__);

$groups = [
    [
        'name' => Loc::getMessage('VW_ANALYTICS_TITLE_COUNTERS'),
        'options' => [
            ['id' => 'ymetrika', 'name' => Loc::getMessage('VW_ANALYTICS_COUNTER_YMETRIKA')],
            ['id' => 'gtm', 'name' => Loc::getMessage('VW_ANALYTICS_COUNTER_GTM')],
            ['id' => 'ga', 'name' => Loc::getMessage('VW_ANALYTICS_COUNTER_GA')],
            ['id' => 'facebook', 'name' => Loc::getMessage('VW_ANALYTICS_COUNTER_FACEBOOK')],
            ['id' => 'jivosite', 'name' => Loc::getMessage('VW_ANALYTICS_COUNTER_JIVOSITE')],
        ]
    ],
    [
        'name' => Loc::getMessage('VW_ANALYTICS_TITLE_YM_COUNTER'),
        'options' => [
            ['id' => 'ymetrika_webvisor', 'name' => Loc::getMessage('VW_ANALYTICS_YMETRIKA_WEBVISOR'), 'type'=>'checkbox','default'=>'Y'],
            ['id' => 'ymetrika_ecommerce', 'name' => Loc::getMessage('VW_ANALYTICS_YMETRIKA_ECOMMERCE'), 'type'=>'checkbox'],
            ['id' => 'ymetrika_ecommerce_layer', 'name' => Loc::getMessage('VW_ANALYTICS_YMETRIKA_ECOMMERCE_LAYER'),'default'=>'dataLayer'],
        ]
    ],
//    [
//        'name' => Loc::getMessage('VW_ANALYTICS_TITLE_FIELDS'),
//        'options' => [
//            ['id' => 'field_metrika', 'name' => Loc::getMessage('VW_ANALYTICS_FIELD_YMETRIKA'),'type'=>'checkbox'],
//            ['id' => 'field_page', 'name' => Loc::getMessage('VW_ANALYTICS_FIELD_PAGE'),'type'=>'checkbox'],
//            ['id' => 'field_ip', 'name' => Loc::getMessage('VW_ANALYTICS_FIELD_IP'),'type'=>'checkbox'],
//        ]
//    ]
];

if ($REQUEST_METHOD == 'POST' && $_POST['Update'] == 'Y') {
    $req = \Bitrix\Main\Context::getCurrent()->getRequest()->toArray();
    $cache = \Bitrix\Main\Data\Cache::createInstance();
    $cache_id = 'vw_counters';
    $cache_path = '/volgaw/counters/';
    $cache->clean($cache_id, $cache_path);
    foreach ($req[$sModuleInnerId] as $site => $saveOptions) {
        foreach ($saveOptions as $optionName => $optionValue) {
            \Bitrix\Main\Config\Option::set($sModuleId, $optionName, $optionValue, $site);
        }
    }

}

/**
 * Describe tabs
 */


$arSites = \Bitrix\Main\SiteTable::getList(['filter' => ['ACTIVE' => 'Y']])->fetchAll();
$arTabs = array();
foreach ($arSites as $key => $arSite) {
    $arOptions = [];
    foreach ($groups as &$g) {
        foreach ($g['options'] as &$o) {
            $o['value'] = \Bitrix\Main\Config\Option::get($sModuleId, $o['id'], $o['default']?:'', $arSite['LID']);
        }
    }
    $arOptions = $groups;
    $arTabs[] = array(
        "DIV" => "edit" . ($key + 1),
        "TAB" => GetMessage("VW_ANALYTICS_OPTIONS_SITE_TITLE", array("#SITE_NAME#" => $arSite["NAME"], "#SITE_ID#" => $arSite["LID"])),
        "ICON" => "settings",
        "TITLE" => GetMessage("MAIN_OPTIONS_TITLE"),
        "PAGE_TYPE" => "site_settings",
        "SITE_ID" => $arSite["LID"],
        "SITE_DIR" => $arSite["DIR"],
        "OPTIONS" => $arOptions
    );
}

/**
 * Init tabs
 */
$oTabControl = new CAdmintabControl('tabControl', $arTabs);
$oTabControl->Begin();

/**
 * Settings form
 */

?>
<form method="POST" enctype="multipart/form-data"
      action="<? echo $APPLICATION->GetCurPage() ?>?lang=<? echo LANG ?>&mid=<?= htmlspecialchars($sModuleId) ?>&mid_menu=1">
    <?= bitrix_sessid_post() ?>
    <?php
    foreach ($arTabs as $key => $arTab) {
        ?>
        <? $oTabControl->BeginNextTab(); ?>

        <?php
        foreach ($arTab['OPTIONS'] as $group) {
            ?>
          <tr class="heading">
            <td colspan="2"><?= $group['name'] ?></td>
          </tr>
            <?php foreach ($group['options'] as $option) {
                ?>
            <tr>
              <td width="40%" nowrap="" class="adm-detail-content-cell-l">
                <label for="<?= $option['id'] ?>_<?= $arTab['SITE_ID'] ?>"><? echo $option['name'] ?>:</label>
              </td>
              <td width="60%" class="adm-detail-content-cell-r">
                  <?php
                  switch ($option['type']){
                      case 'checkbox':
                          ?>
                        <input type="checkbox" name="<?= $sModuleInnerId ?>[<?= $arTab['SITE_ID'] ?>][<?= $option['id'] ?>]"
                               id="<?= $option['id'] ?>_<?= $arTab['SITE_ID'] ?>" <?=$option['value'] == 'Y'?'checked':''?>
                               value="Y"/><?php
                        break;
                      default:
                        ?>
                        <input type="text" name="<?= $sModuleInnerId ?>[<?= $arTab['SITE_ID'] ?>][<?= $option['id'] ?>]"
                               id="<?= $option['id'] ?>_<?= $arTab['SITE_ID'] ?>"
                               value="<?= $option['value'] ?>"/><?php
                        break;
                  }
                  ?>

              </td>
            </tr>
                <?php
            }
        }
        ?>
    <?php } ?>
    <?
    $oTabControl->Buttons();
    ?>
  <input type="submit" name="Update" value="<?php echo Loc::getMessage('VW_ANALYTICS_SAVE') ?>"/>
  <input type="reset" name="reset" value="<?php echo Loc::getMessage('VW_ANALYTICS_RESET') ?>"/>
  <input type="hidden" name="Update" value="Y"/>
    <?php $oTabControl->End(); ?>
</form>
