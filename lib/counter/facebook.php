<?php


namespace VW\Analytics\Counter;


use VW\Analytics\Counter\abstractCounter;

class facebook extends baseCounter implements abstractCounter
{

    protected $preconnectDomains = [
        'connect.facebook.net',
        'graph.facebook.com',
        'www.facebook.com'
    ];

    public function getHeaderCounter(): ?string
    {
        ob_start(); ?>
      <noscript><img alt="" height="1" width="1" style="display:none"
                     src="https://www.facebook.com/tr?id=<?= $this->counterString ?>&ev=PageView&noscript=1"
        /></noscript>
        <?php
        return trim(ob_get_clean());
    }

    public function getHeadCounter(): ?string
    {
        ob_start();
        ?>
      <script>!function (f, b, e, v, n, t, s) {
          if (f.fbq) return;
          n = f.fbq = function () {
            n.callMethod ?
              n.callMethod.apply(n, arguments) : n.queue.push(arguments)
          };
          if (!f._fbq) f._fbq = n;
          n.push = n;
          n.loaded = !0;
          n.version = '2.0';
          n.queue = [];
          t = b.createElement(e);
          t.async = !0;
          t.src = v;
          s = b.getElementsByTagName(e)[0];
          s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
          'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?=$this->counterString?>');
        fbq('track', 'PageView');</script><?php
        return trim(ob_get_clean());
    }

    public function getHeadCounterLazy(): ?string
    {
        ob_start();
        ?>
      <script>
        lazyCounter('FacebookLoaded', 3500, function () {
          !function (f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function () {
              n.callMethod ?
                n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
          }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
          fbq('init', '<?=$this->counterString?>');
          fbq('track', 'PageView');
        });
      </script>
        <?php
        return trim(ob_get_clean());
    }
}