<?php


namespace VW\Analytics\Counter;


class gtm extends baseCounter implements abstractCounter
{

    protected $preconnectDomains = [
        'www.googletagmanager.com',
        'www.googleadservices.com',
        'googleads.g.doubleclick.net',
        'www.google.com',
        'www.google.ru',
        'bid.g.doubleclick.net',
    ];

    public function getHeaderCounter(): ?string
    {
        ob_start(); ?>
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id=<?= $this->counterString ?>" height="0" width="0"
                    style="display:none;visibility:hidden"></iframe>
        </noscript>
        <?php
        return trim(ob_get_clean());
    }

    public function getHeadCounter(): ?string
    {
        ob_start();
        ?>
        <script data-skip-moving>
          (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
              'gtm.start': new Date().getTime(),
              event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
              j = d.createElement(s),
              dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
              'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
          })(window, document, 'script', 'dataLayer', '<?= $this->counterString ?>');
        </script>
        <?php
        return trim(ob_get_clean());
    }
}
