<?php


namespace VW\Analytics;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Page\Asset;
use VW\Analytics\Counter\abstractCounter;
use VW\Main\Debug;
use VW\Main\Meta\DNSPrefetch;
use VW\Main\Optimize;

class Counter
{
    private $moduleID = 'vw.analytics';
    // список счетчиков для подключения
    private $list = ['gtm', 'ymetrika', 'ga', 'facebook', 'jivosite'];

    private $isLazyIncluded = false;

    private $counters;

    /**
     * @var static
     */
    public static $instance;

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }


    public static function includeHead($lazy = false)
    {
        // не подключаем если не подключен главный модуль
        if (Loader::includeModule('vw.main') && Optimize::isBot()) {
            return;
        }
        $instance = self::getInstance();
        if ($lazy) {
            echo $instance->includeJsLazyFunction();
        }
        // получаем все значения счетчиков
        $counters = $instance->getCounters($lazy);
        foreach ($counters['templates'] as $counter) {
            // отображаем только блок в head
            echo $counter['head'];
        }
        $instance->setPrefetch();
    }

    /**
     * установка link rel=dns-prefetch
     * @throws LoaderException
     */
    private function setPrefetch()
    {
        foreach ($this->counters['templates'] as $counter) {
            if (count($counter['prefetch'])) {
                if (Loader::includeModule('vw.main') && class_exists('VW\Main\Meta\DNSPrefetch')) {
                    foreach ($counter['prefetch'] as $domain) {
                        DNSPrefetch::getInstance()->add($domain);
                    }
                } else {
                    foreach ($counter['prefetch'] as $domain) {
                        Asset::getInstance()->addString('<link rel="dns-prefetch" href="' . $domain . '">');
                    }
                }
            }
        }
    }

    public static function includeHeader($lazy = false)
    {
        if (Loader::includeModule('vw.main') && Optimize::isBot()) {
            return;
        }
        $instance = self::getInstance();
        if ($lazy) {
            echo $instance->includeJsLazyFunction();
        }
        $counters = $instance->getCounters($lazy);

        foreach ($counters['templates'] as $counter) {
            echo $counter['header'];
        }
    }

    public static function includeFooter($lazy = false)
    {
        if (Loader::includeModule('vw.main') && Optimize::isBot()) {
            return;
        }
        $instance = self::getInstance();
        if ($lazy) {
            echo $instance->includeJsLazyFunction();
        }
        $counters = $instance->getCounters($lazy);
        foreach ($counters['templates'] as $counter) {
            echo $counter['footer'];
        }
    }

    private function getCounters($lazy = false)
    {
        $arParams["CACHE_TIME"] = IntVal(36000000000);
        $CACHE_ID = 'vw_counters_' . $lazy;
        $cache = Cache::createInstance();
        if (!$cache->initCache($arParams["CACHE_TIME"], $CACHE_ID, "/volgaw/counters/")) {
            $cache->startDataCache();
            $options = [];
            $templates = [];
            foreach ($this->list as $counter) {
                $value = Option::get($this->moduleID, $counter,'',SITE_ID);
                if (!empty($value)) {
                    $options[$counter] = $value;
                    if (class_exists("\\VW\\Analytics\\Counter\\" . $counter)) {
                        $classStr = "\\VW\\Analytics\\Counter\\" . $counter;
                        /**
                         * @var $instance abstractCounter
                         */
                        $instance = new $classStr($value);
                        $templates[$counter]['prefetch'] = $instance->getPrefetch();
                        if (!$lazy) {
                            $templates[$counter]['head'] = $instance->getHeadCounter();
                            $templates[$counter]['header'] = $instance->getHeaderCounter();
                            $templates[$counter]['footer'] = $instance->getFooterCounter();
                        } else {
                            $templates[$counter]['head'] = $instance->getHeadCounterLazy();
                            $templates[$counter]['header'] = $instance->getHeaderCounterLazy();
                            $templates[$counter]['footer'] = $instance->getFooterCounterLazy();
                        }
                    }
                }
            }

            $cache->endDataCache(
                array(
                    "options" => $options,
                    "templates" => $templates
                )
            );
        } else {
            /**
             * @var array $options
             * @var array $templates
             */
            extract($cache->getVars());
        }
        $this->counters = ['options' => $options, 'templates' => $templates];
        return $this->counters;
    }


    private function includeJsLazyFunction(): string
    {
        if (!$this->isLazyIncluded) {
            $this->isLazyIncluded = true;
            ob_start();
            ?>
            <script data-skip-moving>
              function lazyCounter(sessionName, timer, cb) {
                window.addEventListener('load', function () {
                  setTimeout(function () {
                    function ll() {
                      window.removeEventListener("scroll", ll, false);
                      window.removeEventListener("mousemove", ll, false);
                      window.removeEventListener("touchmove", ll, false);
                      window.removeEventListener("resize", ll, false);
                      if (document.readyState === 'complete') {
                        cb();
                      } else {
                        window.addEventListener('load', ll, false);
                      }
                      sessionStorage.setItem(sessionName, 1);
                    }

                    if (sessionStorage.getItem(sessionName) !== 1) {
                      window.addEventListener("scroll", ll, {
                        capture: false,
                        passive: true
                      });
                      window.addEventListener("mousemove", ll, {
                        capture: false,
                        passive: true
                      });
                      window.addEventListener("touchmove", ll, {
                        capture: false,
                        passive: true
                      });
                      window.addEventListener("resize", ll, {
                        capture: false,
                        passive: true
                      });
                    } else {
                      ll();
                    }
                  }, timer);
                });
              }
            </script>
            <?php
            return ob_get_clean();
        } else {
            return '';
        }
    }

    public function getList()
    {
        return $this->list;
    }

    public static function getCounter($counter)
    {
        $instance = self::getInstance();
        if (in_array($counter, $instance->list)) {
            $counters = $instance->getCounters();
            $optionsKeys = array_keys($counters['options']);
            if (in_array($counter, $optionsKeys)) {
                return $counters['options'][$counter];
            }
        }
        return null;
    }
}
