<?php


namespace VW\Analytics\Counter;


class ga extends baseCounter implements abstractCounter
{

    protected $preconnectDomains = [
        '//www.googletagmanager.com',
        '//www.google-analytics.com',
    ];

    public function getHeadCounter(): ?string
    {
        ob_start();
        ?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $this->counterString ?>"
                data-skip-moving></script>
        <script data-skip-moving>
          window.dataLayer = window.dataLayer || [];

          function gtag() {
            dataLayer.push(arguments);
          }

          gtag('js', new Date());

          gtag('config', '<?= $this->counterString ?>');
        </script>
        <?php
        return trim(ob_get_clean());
    }
}
