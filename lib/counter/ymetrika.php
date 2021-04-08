<?php


namespace VW\Analytics\Counter;


use Bitrix\Main\Config\Option;
use CUtil;

class ymetrika extends baseCounter implements abstractCounter
{

    protected $preconnectDomains = ['mc.yandex.ru'];

    protected $options = [
        'clickmap' => true,
        'trackLinks' => true,
        'accurateTrackBounce' => true,
        'trackHash' => true,
    ];

    public function __construct(string $counterString)
    {
        parent::__construct($counterString);
        $optionWebvisor = Option::get('vw.analytics', 'ymetrika_webvisor');
        if ($optionWebvisor == 'Y') {
            $this->options['webvisor'] = true;
        }

        $optionEcommerce = Option::get('vw.analytics', 'ymetrika_ecommerce');
        $stringEcommerce = Option::get('vw.analytics', 'ymetrika_ecommerce_layer');
        if ($optionEcommerce == 'Y') {
            $this->options['ecommerce'] = $stringEcommerce;
        }
    }

    public function getHeaderCounter(): ?string
    {
        ob_start(); ?>
        <noscript>
            <div><img src="https://mc.yandex.ru/watch/<?= $this->counterString ?>" style="position:absolute; left:-9999px;" alt=""/></div>
        </noscript>
        <?php
        return trim(ob_get_clean());
    }

    public function getHeadCounter(): ?string
    {
        ob_start();
        ?>
        <script data-skip-moving>
          (function (m, e, t, r, i, k, a) {
            m[i] = m[i] || function () {
              (m[i].a = m[i].a || []).push(arguments)
            };
            m[i].l = 1 * new Date();
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
          })
          (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
          ym(<?= $this->counterString ?>, "init", <?= CUtil::PhpToJSObject($this->options) ?>);
        </script><?php
        return trim(ob_get_clean());
    }

    public function getHeadCounterLazy(): ?string
    {
        ob_start();
        ?>
        <script data-skip-moving>
          var mtrkStart = new Date().getTime();
          var metrikaTimer = 2000;
          lazyCounter('MetrikaLoaded', metrikaTimer, function () {
            (function (m, e, t, r, i, k, a) {
              m[i] = m[i] || function () {
                (m[i].a = m[i].a || []).push(arguments)
              };
              m[i].l = 1 * new Date();
              k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
            })
            (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
            var timeToLoad = new Date().getTime() - mtrkStart;
            var bounce = 10000 - timeToLoad + metrikaTimer;
            console.log('Показатель отказов начинается с ' + bounce);
            ym(<?= $this->counterString ?>, "init", <?= CUtil::PhpToJSObject($this->options) ?>);
          });
        </script><?php
        return ob_get_clean();
    }
}
