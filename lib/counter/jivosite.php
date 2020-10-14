<?php


namespace VW\Analytics\Counter;


use VW\Analytics\Counter\abstractCounter;

class jivosite extends baseCounter
{

    protected $preconnectDomains = [
        'code.jivosite.com'
    ];


    public function getFooterCounter(): ?string
    {
      ob_start();?>
        <!-- BEGIN JIVOSITE CODE {literal} -->
<script type='text/javascript'>
        (function(){ var widget_id = '<?=$this->counterString?>';var d=document;var w=window;function l(){
            var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);}if(d.readyState=='complete'){l();}else{if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();</script>
<!-- {/literal} END JIVOSITE CODE -->
<?php
        return trim(ob_get_clean());
    }

    public function getFooterCounterLazy(): ?string
    {
ob_start();
?>
<script>
  lazyCounter('JivoSiteLoaded',3500,function(){
    document.jivositeloaded = 0;
    var widget_id = '<?=$this->counterString?>';
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = '//code.jivosite.com/script/widget/' + widget_id;
    var ss = document.getElementsByTagName('script')[0];
    ss.parentNode.insertBefore(s, ss);
  });
</script><?php
        return trim(ob_get_clean());
    }
}