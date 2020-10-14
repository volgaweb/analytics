<?php


namespace VW\Analytics\Counter;


use VW\Analytics\Counter\abstractCounter;

class roistat extends baseCounter implements abstractCounter
{

    protected $preconnectDomains = [
        'cloud.roistat.com',
        'collector.roistat.com',
    ];


    public function getHeadCounter(): ?string
    {
        ob_start();
        ?>
      <script>
        (function(w, d, s, h, id) {
          w.roistatProjectId = id; w.roistatHost = h;
          var p = d.location.protocol == "https:" ? "https://" : "http://";
          var u = /^.*roistat_visit=[^;]+(.*)?$/.test(d.cookie) ? "/dist/module.js" : "/api/site/1.0/"+id+"/init";
          var js = d.createElement(s); js.charset="UTF-8"; js.async = 1; js.src = p+h+u; var js2 = d.getElementsByTagName(s)[0]; js2.parentNode.insertBefore(js, js2);
        })(window, document, 'script', 'cloud.roistat.com', '<?= $this->counterString ?>');
      </script>
        <?php
        return trim(ob_get_clean());
    }

}