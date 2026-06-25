<!-- impressum -->
<div class="em-info">
    <div class="em-infocard">
        <h3>{{ __($restorant->getConfig('impressum_title','')) }}</h3>
        <div style="color:var(--em-muted);font-size:14px;line-height:1.7;">
            <?php echo __($restorant->getConfig('impressum_value','')); ?>
        </div>
    </div>
</div>
